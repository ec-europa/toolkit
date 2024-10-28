<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;

/**
 * Commands to interact with the axe-scan.
 *
 * @see https://github.com/ttsukagoshi/axe-scan
 * @see https://github.com/puppeteer/puppeteer
 * @see https://www.deque.com/axe/core-documentation/api-documentation/
 */
class AxeCommands extends AbstractCommands
{

    /**
     * A list of dependencies for axe-scan.
     *
     * @var array|string[]
     */
    private array $dependencies = [
        // cspell:disable
        'libnss3-tools',
        'libatk1.0-0',
        'libatk-bridge2.0-0',
        'libdrm2',
        'libxcomposite1',
        'libxdamage1',
        'libxfixes3',
        'libxrandr2',
        'libgbm1',
        'libxkbcommon-x11-0',
        'libpangocairo-1.0-0',
        'libasound2',
        'fonts-liberation',
        'libgcc1',
        'libxss1',
        'libgtk-3-0',
        'libx11-xcb1',
        'libxcursor1',
        'xdg-utils',
        // cspell:enable
    ];

    /**
     * Run the axe-scan.
     *
     * @command toolkit:run-axe-scan
     *
     * @aliases tk-axe
     */
    public function toolkitRunAxeScan()
    {
        $tasks = [];

        $tasks[] = $this->taskExec($this->getBin('run'))->arg('toolkit:setup-axe-scan');

        $config = $this->getConfigValue('toolkit.axe-scan');
        $exec = $this->taskExec($this->getNodeBinPath('axe-scan'))
            ->arg('run')
            ->option('file', $config['file-path']);

        if (!empty($config['allow-list']) && file_exists($config['allow-list'])) {
            $exec->option('allowlist', $config['allow-list']);
        }
        if (!empty($config['result-file'])) {
            $exec->rawArg('> ' . $config['result-file']);
        }
        $tasks[] = $exec;

        if (!empty($config['run-summary'])) {
            $tasks[] = $this->taskExec($this->getBin('run'))->arg('toolkit:run-axe-scan-summary');
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run the axe-scan summary.
     *
     * @command toolkit:run-axe-scan-summary
     *
     * @aliases tk-axe-sum
     */
    public function toolkitRunAxeScanSummary()
    {
        $config = $this->getConfigValue('toolkit.axe-scan');

        $exec = $this->taskExec($this->getNodeBinPath('axe-scan'))
            ->arg('summary');

        if (!empty($config['allow-list']) && file_exists($config['allow-list'])) {
            $exec->option('allowlist', $config['allow-list']);
        }
        if (!empty($config['summary-result-file'])) {
            $exec->rawArg('> ' . $config['summary-result-file']);
        }
        return $this->collectionBuilder()->addTask($exec);
    }

    /**
     * Make sure axe-scan is installed and properly configured.
     *
     * @command toolkit:setup-axe-scan
     */
    public function toolkitSetupAxeScan()
    {
        $tasks = [];

        // Install dependencies if the bin is not present.
        if (!file_exists($this->getNodeBinPath('axe-scan'))) {
            $tasks[] = $this->taskExecStack()
                ->exec('npm -v || npm i npm')
                ->exec('[ -f package.json ] || npm init -y --scope')
                ->exec('npm list axe-scan && npm update axe-scan || npm install axe-scan -y');
        }

        // Install linux dependencies.
        $tasks[] = $this->taskExec($this->getBin('run'))
            ->arg('toolkit:install-dependencies')
            ->option('packages', implode(',', $this->dependencies));

        $config = $this->getConfigValue('toolkit.axe-scan');

        // Generate the URLs file.
        $baseUrl = $this->getConfigValue('drupal.base_url');
        $urls = array_map(function ($url) use ($baseUrl) {
            return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
        }, $config['urls']);
        $tasks[] = $this->taskWriteToFile($config['file-path'])
            ->text(implode(PHP_EOL, $urls));

        // Generate the config file.
        $data = [
            'axeCoreTags' => $config['core-tags'],
            'resultTypes' => $config['result-types'],
            'filePath' => $config['file-path'],
            'locale' => $config['locale'],
        ];
        $tasks[] = $this->taskWriteToFile($config['config'])
            ->text(json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL);

        // Apply temporary patch to axe-scan when starting puppeteer to have the
        // option --no-sandbox, this avoids the error: Running as root without
        // --no-sandbox is not supported.
        $tasks[] = $this->collectionBuilder()->addCode(function () {
            $files = [
                'node_modules/axe-scan/build/src/commands/run.js',
                'node_modules/axe-scan/build/src/commands/summary.js',
            ];
            $from = 'const browser = await puppeteer.launch();';
            // cspell:ignore setuid
            $args = '["--no-sandbox", "--disable-setuid-sandbox", "--single-process", "--disable-impl-side-painting", "--disable-gpu-sandbox", "--disable-accelerated-2d-canvas", "--disable-accelerated-jpeg-decoding", "--disable-dev-shm-usage"]';
            $to = 'const browser = await puppeteer.launch({args: ' . $args . '});';
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $this->taskReplaceInFile($file)->from($from)->to($to)->run();
                }
            }
        });

        // Make sure puppeteer is installed.
        $tasks[] = $this->collectionBuilder()->addCode(function () {
            if (file_exists('node_modules/puppeteer/install.mjs')) {
                $this->_exec('node node_modules/puppeteer/install.mjs');
            }
        });

        return $this->collectionBuilder()->addTaskList($tasks);
    }

}
