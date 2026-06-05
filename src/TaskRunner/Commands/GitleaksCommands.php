<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use EcEuropa\Toolkit\Website;
use Robo\Contract\VerbosityThresholdInterface;
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
    protected $io;

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
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:run-gitleaks
     *
     * @option tag             The release tag of Gitleaks.
     * @option sha256          SHA-256 checksum of the release archive.
     * @option os              The current OS version.
     * @option options         The options to use when executing gitleaks command.
     * @option skip-dist       Skip the creation of distribution.
     * @option ignore-file     Path to .leaksignore file (default ".leaksignore").
     * @option report-to-file  Save the findings to a file.
     *
     * @return int
     *   Return 1 if there're findings, 0 if no issues detected.
     *
     * @aliases tk-gitleaks
     */
    public function toolkitRunGitleaks(ConsoleIO $io, array $options = [
        'tag' => InputOption::VALUE_REQUIRED,
        'sha256' => InputOption::VALUE_REQUIRED,
        'os' => InputOption::VALUE_REQUIRED,
        'options' => InputOption::VALUE_REQUIRED,
        'skip-dist' => InputOption::VALUE_NONE,
        'ignore-file' => InputOption::VALUE_REQUIRED,
        'report-to-file' => InputOption::VALUE_NONE,
    ])
    {
        $this->io = $io;
        $repo = $this->getConfig()->get('gitleaks.repo');
        if (!$this->download($repo, $options['tag'], $options['os'], $options['sha256'])) {
            $io->error('Fail to download Gitleaks binary.');
            return ResultData::EXITCODE_ERROR;
        }

        $command = $this->prepareCommand($options);

        $reportFile = 'gitleaks-report.json';
        $dist = $this->getConfigValue('toolkit.build.dist.root');

        // Prepare the distribution and scan it.
        if ($options['skip-dist'] === true) {
            if (!is_dir($dist)) {
                $io->error("Attempt to use --skip-dist while '$dist' is not found.");
                // Consider to force build-dist instead of fail.
                return ResultData::EXITCODE_ERROR;
            }
            $io->writeln('Skip create distribution.');
        } else {
            $this->_exec($this->getBin('run') . ' toolkit:build-dist');
        }

        $io->say($command);
        $this->taskExec($command)->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)->run();

        if (!file_exists($reportFile) || empty($reportFileContent = file_get_contents($reportFile))) {
            $io->error('Could not generate the report file.');
            return ResultData::EXITCODE_ERROR;
        }

        $findings = json_decode($reportFileContent, true);

        if (empty($findings)) {
            $this->printReport(['found' => 0, 'ignored' => 0]);
            return ResultData::EXITCODE_OK;
        }

        $report = [
            'found' => count($findings),
            'ignored' => $this->processFindings($findings, $options),
        ];

        // Delete the report file if report-to-file is not used.
        if (empty($options['report-to-file'])) {
            unlink($reportFile);
        } else {
            file_put_contents($reportFile, json_encode($findings, JSON_PRETTY_PRINT));
        }

        $this->printReport($report, $findings);
        return ResultData::EXITCODE_OK;
    }

    /**
     * Prepares the full gitleaks command including the options.
     *
     * @param array<mixed> $options
     *   The command options.
     *
     * @return string
     *   The final command to execute.
     */
    private function prepareCommand(array $options): string
    {
        $reportFile = 'gitleaks-report.json';
        $dist = $this->getConfigValue('toolkit.build.dist.root');
        $optionsExploded = array_filter(explode(' ', $options['options'] ?? []));

        $command = "detect --source=$dist";

        // Detect newer versions of gitleaks and adapt command and options.
        // @see https://github.com/gitleaks/gitleaks?tab=readme-ov-file#commands
        if (Semver::satisfies($options['tag'], '>=8.19.0')) {
            // Remove the --no-git option if present.
            if (($index = array_search('--no-git', $optionsExploded)) !== false) {
                unset($optionsExploded[$index]);
            }
            // Change the command from 'detect' to 'directory' and remove
            // --source option.
            $command = "directory $dist";
        }

        // Report to a file.
        $optionsExploded[] = "--report-path=$reportFile";

        return $this->getBin('gitleaks') . ' ' . $command . ' ' . implode(' ', $optionsExploded);
    }

    /**
     * Returns the ignores to use from website and project.
     *
     * @param array<mixed> $options
     *   The command options.
     *
     * @return string[]
     *   The ignores to use.
     */
    private function getIgnores(array $options): array
    {
        $ignores = Website::gitleaksIgnores() ?: [];
        // Add the project ignores.
        if (file_exists($options['ignore-file'])) {
            $projectIgnores = file($options['ignore-file'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $ignores = array_merge($ignores, $projectIgnores);
        }
        return $ignores;
    }

    /**
     * Process the findings and returns the number of ignored.
     *
     * @param array<mixed> $findings
     *   The gitleaks findings.
     * @param array<mixed> $options
     *   The command options.
     *
     * @return int
     *   The number of ignored findings.
     */
    private function processFindings(array &$findings, array $options): int
    {
        $ignored = 0;
        $ignores = $this->getIgnores($options);
        $dist = $this->getConfigValue('toolkit.build.dist.root');
        foreach ($findings as $index => &$finding) {
            // Remove dist part from the paths.
            $finding['File'] = ltrim(str_replace("$dist/", '', $finding['File']), '/');
            $finding['Fingerprint'] = ltrim(str_replace("$dist/", '', $finding['Fingerprint']), '/');
            // Build the fingerprint using the relative path to the file,
            // the secret found and the rule id.
            $finding['ToolkitFingerprint'] = hash('sha256', "{$finding['File']}:{$finding['Secret']}:{$finding['RuleID']}");
            if (empty($ignores)) {
                continue;
            }
            if (in_array($finding['ToolkitFingerprint'], $ignores) || in_array($finding['Fingerprint'], $ignores)) {
                unset($findings[$index]);
                $ignored++;
            }
        }

        return $ignored;
    }

    /**
     * Prints the report information.
     *
     * @param array<mixed> $report
     *   The report to print.
     * @param array<mixed> $findings
     *   The gitleaks findings to print.
     */
    private function printReport(array $report, array $findings = []): void
    {
        if (!empty($findings)) {
            $fields = $this->getConfigValue('gitleaks.fields', []);
            $this->io->title('Findings:');
            foreach ($findings as $item) {
                $this->io->title($item['Description']);
                $maxLen = max(array_map(fn ($f) => strlen("$f:") + 2, $fields));
                foreach ($fields as $field) {
                    if (isset($item[$field])) {
                        $this->io->writeln(sprintf("%-{$maxLen}s %s", "$field:", $item[$field]));
                    }
                }
                $this->io->newLine();
            }
        }

        $this->io->definitionList(
            ['Findings Found' => $report['found']],
            ['Findings Ignored' => $report['ignored']],
            ['Findings Active' => $report['found'] - $report['ignored']],
        );
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
     * @param string $sha256
     *   Expected SHA-256 checksum of the downloaded archive.
     */
    private function download(string $repo, string $tag, string $os, string $sha256): bool
    {
        if (file_exists($this->getBinPath('gitleaks')) || $this->isSimulating()) {
            $this->writeln('Skip download, binary was found.');
            return true;
        }
        $link = "$repo/releases/download/v$tag/gitleaks_{$tag}_$os.tar.gz";
        $this->writeln("Downloading from $link");
        $tmp = 'gitleaks_tmp';
        if ($file = file_get_contents($link)) {
            if (!$this->isChecksumValid($file, $sha256)) {
                $this->writeln('Invalid SHA-256 checksum for downloaded Gitleaks archive.');
                return false;
            }
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

    /**
     * Validate the archive SHA-256 checksum.
     *
     * @param string $file
     *   Archive contents.
     * @param string $sha256
     *   Expected SHA-256 checksum.
     */
    private function isChecksumValid(string $file, string $sha256): bool
    {
        return hash_equals(strtolower($sha256), strtolower(hash('sha256', $file)));
    }

}
