<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;

/**
 * Provides command to interact with GitLeaks.
 */
class GitleaksCommands extends AbstractCommands
{

    protected string $repo;
    protected string $tag;
    protected string $os;

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/gitleaks.yml';
    }

    /**
     * Executes the Gitleaks.
     *
     * When used with --update, builds a distribution package (if not already
     * present) and scans only production code (excluding require-dev
     * dependencies). Without --update, scans the current directory as-is.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:run-gitleaks
     *
     * @option tag            The release tag of Gitleaks.
     * @option os             The current OS version.
     * @option options        The options to use when executing gitleaks command.
     * @option config-file    The path to the gitleaks TOML config file.
     * @option update         Regenerate the leaksignore file with findings.
     * @option container-root The container root path for fingerprint normalization.
     * @option leaksignore    The path to the leaksignore file.
     *
     * @return int|\Robo\Collection\CollectionBuilder
     *   The object collection builder or integer if failed.
     *
     * @aliases tk-gitleaks
     */
    public function toolkitRunGitleaks(ConsoleIO $io, array $options = [
        'tag' => InputOption::VALUE_REQUIRED,
        'os' => InputOption::VALUE_REQUIRED,
        'options' => InputOption::VALUE_REQUIRED,
        'config-file' => InputOption::VALUE_REQUIRED,
        'update' => false,
        'container-root' => InputOption::VALUE_REQUIRED,
        'leaksignore' => InputOption::VALUE_REQUIRED,
    ])
    {
        $repo = $this->getConfig()->get('gitleaks.repo');
        if (!$this->download($repo, $options['tag'], $options['os'])) {
            $io->error('Fail to download Gitleaks binary.');
            return ResultData::EXITCODE_ERROR;
        }

        $command = 'detect';
        $optionsExploded = array_filter(explode(' ', $options['options']));

        // Resolve gitleaks config file: project-level, then toolkit default.
        $configFile = $this->resolveConfigFile($options['config-file']);
        if ($configFile !== null) {
            $optionsExploded[] = '--config=' . $configFile;
        }

        // Detect newer versions of gitleaks and adapt command and options.
        // @see https://github.com/gitleaks/gitleaks?tab=readme-ov-file#commands
        if (Semver::satisfies($options['tag'], '>=8.19.0')) {
            // Remove the --no-git option if present.
            if (($index = array_search('--no-git', $optionsExploded)) !== false) {
                unset($optionsExploded[$index]);
            }
            // Change the command from 'detect' to 'directory'.
            $command = 'directory';
        }

        $options['options'] = implode(' ', $optionsExploded);

        // Without --update, run gitleaks normally and return.
        if (empty($options['update'])) {
            return $this->taskExec($this->getBin('gitleaks') . ' ' . $command . ' ' . $options['options']);
        }

        // With --update, scan the dist directory for production code only.
        $distRoot = $this->getConfig()->get('toolkit.build.dist.root');
        if (!is_dir($distRoot) || (new \FilesystemIterator($distRoot))->valid() === false) {
            $io->error(sprintf(
                'The dist directory "%s" is empty or does not exist. Run "toolkit:build-dist" first.',
                $distRoot
            ));
            return ResultData::EXITCODE_ERROR;
        }

        // Point gitleaks at the dist directory.
        if ($command === 'directory') {
            $command .= ' ' . $distRoot;
        } else {
            $options['options'] .= ' --source=' . $distRoot;
        }

        // Regenerate the leaksignore file.
        return $this->updateLeaksignore($io, $command, $options, $distRoot);
    }

    /**
     * Regenerate the leaksignore file from gitleaks findings.
     *
     * @param \Robo\Symfony\ConsoleIO $io
     *   The console IO.
     * @param string $command
     *   The gitleaks command (detect or directory).
     * @param array<mixed> $options
     *   The command options.
     * @param string $distRoot
     *   The distribution root directory.
     *
     * @return int
     *   The exit code.
     */
    private function updateLeaksignore(ConsoleIO $io, string $command, array $options, string $distRoot): int
    {
        $reportPath = sys_get_temp_dir() . '/gitleaks-report.json';
        $leaksignorePath = $options['leaksignore'];
        $containerRoot = rtrim($options['container-root'], '/') . '/';
        $distPrefix = rtrim($distRoot, '/') . '/';

        // Clear leaksignore before scan to avoid stale entries.
        file_put_contents($leaksignorePath, '');

        // Run gitleaks with JSON report output.
        $fullCommand = sprintf(
            '%s %s %s --report-format json --report-path %s',
            $this->getBinPath('gitleaks'),
            $command,
            $options['options'],
            $reportPath
        );
        $io->writeln("Executing: $fullCommand");
        $this->taskExec($fullCommand)->run();

        // Extract fingerprints from report.
        $entries = [];
        if (file_exists($reportPath)) {
            $findings = json_decode(file_get_contents($reportPath), true);
            @unlink($reportPath);
            if (!empty($findings)) {
                foreach ($findings as $finding) {
                    if (!empty($finding['Fingerprint'])) {
                        $entries[] = $finding['Fingerprint'];
                    }
                }
            }
        }

        // Strip dist root prefix from fingerprints so paths match the
        // deployed structure (e.g. dist/vendor/... becomes vendor/...).
        $entries = array_map(static function (string $entry) use ($distPrefix): string {
            if (str_starts_with($entry, $distPrefix)) {
                return substr($entry, strlen($distPrefix));
            }
            return $entry;
        }, $entries);

        // Normalize paths: prefix relative paths with container root.
        $entries = array_map(static function (string $entry) use ($containerRoot): string {
            if (!str_starts_with($entry, '/')) {
                return $containerRoot . $entry;
            }
            return $entry;
        }, $entries);

        // Deduplicate.
        $entries = array_unique($entries);

        // Group entries by rule ID (fingerprint format: filepath:ruleID:line).
        $groups = [];
        foreach ($entries as $entry) {
            $parts = explode(':', $entry);
            $ruleId = count($parts) >= 3 ? $parts[count($parts) - 2] : 'unknown';
            $groups[$ruleId][] = $entry;
        }

        // Sort groups by rule name, entries by natural order within each group.
        ksort($groups, SORT_STRING);
        $lines = [];
        foreach ($groups as $ruleId => $groupEntries) {
            sort($groupEntries, SORT_NATURAL);
            if (!empty($lines)) {
                $lines[] = '';
            }
            $separator = str_repeat('#', strlen($ruleId) + 4);
            $lines[] = $separator;
            $lines[] = "# $ruleId";
            $lines[] = $separator;
            array_push($lines, ...$groupEntries);
        }

        // Write leaksignore.
        file_put_contents($leaksignorePath, implode("\n", $lines) . "\n");

        $io->success(sprintf(
            'Generated %s: %d entries in %d groups.',
            $leaksignorePath,
            count($entries),
            count($groups)
        ));

        return ResultData::EXITCODE_OK;
    }

    /**
     * Resolve the gitleaks TOML config file path.
     *
     * Priority: explicit option > project .gitleaks.toml.
     *
     * @param string $configFile
     *   The config file option value.
     *
     * @return string|null
     *   The resolved config file path, or null if none found.
     */
    private function resolveConfigFile(string $configFile): ?string
    {
        // Explicit path provided via option or runner.yml.
        if (!empty($configFile) && file_exists($configFile)) {
            return $configFile;
        }

        // Project-level .gitleaks.toml.
        if (file_exists('.gitleaks.toml')) {
            return '.gitleaks.toml';
        }

        return null;
    }

    /**
     * Download the Gitleaks binary from the GitHub releases page.
     *
     * @param string $repo
     *   The Gitleaks repo url.
     * @param string $tag
     *   The release tag to download.
     * @param string $os
     *   The Operating system to use to download.
     */
    private function download(string $repo, string $tag, string $os): bool
    {
        $link = "$repo/releases/download/v$tag/gitleaks_{$tag}_$os.tar.gz";
        $this->writeln("Downloading from $link");
        if (file_exists($this->getBinPath('gitleaks')) || $this->isSimulating()) {
            return true;
        }
        $tmp = 'gitleaks_tmp';
        if ($file = file_get_contents($link)) {
            if (!file_exists($tmp)) {
                $this->_mkdir($tmp);
            }
            if (file_put_contents("$tmp/gitleaks.tar.gz", $file)) {
                $this->taskExtract("$tmp/gitleaks.tar.gz")->to("$tmp/gitleaks")->run();
                if (file_exists("$tmp/gitleaks/gitleaks")) {
                    $this->_copy("$tmp/gitleaks/gitleaks", $this->getBinPath('gitleaks'));
                    if (file_exists($this->getBinPath('gitleaks'))) {
                        $this->_deleteDir($tmp);
                        return true;
                    }
                }
            }
        }
        return false;
    }

}
