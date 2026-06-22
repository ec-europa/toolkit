<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use EcEuropa\Toolkit\Mock;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;

/**
 * Provides commands to generate Toolkit release.
 */
class ReleaseCommands extends AbstractCommands
{

    /**
     * The changelog file.
     *
     * @var string
     */
    private string $changelog = 'CHANGELOG.md';

    /**
     * The release branch.
     *
     * @var string
     */
    private string $releaseBranch = 'release/10.x';

    /**
     * Write the specified version string into needed places.
     *
     * @param string $version
     *   The version to set.
     *
     * @command toolkit:version-write
     *
     * @return int|\Robo\Collection\CollectionBuilder
     *   The toolkit version write task status.
     *
     * @hidden
     */
    public function toolkitVersionWrite(ConsoleIO $io, string $version)
    {
        if (empty($version) || !Semver::satisfies($version, '>0.0.0')) {
            $io->error('You must provide a valid version as first argument.');
            return ResultData::EXITCODE_ERROR;
        }
        if (!file_exists('src/Toolkit.php')) {
            $io->error('Could not find the file src/Toolkit.php.');
            return ResultData::EXITCODE_ERROR;
        }
        $tasks = [];
        // Replace the version in the Toolkit class file.
        $tasks[] = $this->taskReplaceInFile('src/Toolkit.php')
            ->regex("#VERSION = '[^']*'#")
            ->to("VERSION = '" . $version . "'");

        // Replace the version in the phpdoc file.
        if (!file_exists('phpdoc.dist.xml')) {
            $io->warning('Could not find the file phpdoc.dist.xml, ignoring.');
        } else {
            $tasks[] = $this->taskReplaceInFile('phpdoc.dist.xml')
                ->regex('#<version number="[^"]*">#')
                ->to('<version number="' . $version . '">');
        }

        // Replace the version in the tests files.
        if (!file_exists('tests/fixtures/commands/tool.yml')) {
            $io->warning('Could not find the file tests/fixtures/commands/tool.yml, ignoring.');
        } else {
            $tasks[] = $this->taskReplaceInFile('tests/fixtures/commands/tool.yml')
                ->regex('#Toolkit version   OK \([0-9.]+\)#')
                ->to("Toolkit version   OK ($version)");
            $major = explode('.', $version)[0];
            $tasks[] = $this->taskReplaceInFile('tests/fixtures/commands/tool.yml')
                ->regex('#Current version: [0-9.]+#')
                ->to("Current version: $major");
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Write the latest tag from toolkit-mock to the Mock class.
     *
     * @command toolkit:update-mock-default-tag
     *
     * @return int|\Robo\Collection\CollectionBuilder
     *   The toolkit Update default mock tag task status.
     *
     * @hidden
     */
    public function toolkitUpdateDefaultMockTag(ConsoleIO $io)
    {
        $config = $this->getConfig()->get('gitlab');
        if (empty($token = $config['token'])) {
            $io->error('Missing env var GITLAB_API_TOKEN.');
            return ResultData::EXITCODE_ERROR;
        }
        $mockFile = 'src/Mock.php';
        if (!file_exists($mockFile)) {
            $io->error("Could not find the file $mockFile.");
            return ResultData::EXITCODE_ERROR;
        }

        $context = stream_context_create(['http' => ['header' => "Authorization: Bearer $token"]]);
        $latestTag = file_get_contents($config['endpoints']['mock_tags'], false, $context);
        if (empty($latestTag)) {
            $io->error('Failed to get response from GitLab.');
            return ResultData::EXITCODE_ERROR;
        }
        $latestTag = json_decode($latestTag, true);
        $latestTag = $latestTag[0]['name'] ?? false;
        if (empty($latestTag)) {
            $io->error('Failed read the latest tag.');
            return ResultData::EXITCODE_ERROR;
        }

        $tasks = [];
        $tasks[] = $this->taskReplaceInFile($mockFile)
            ->regex("#\\\$defaultTag = '[^']*'#")
            ->to("\\\$defaultTag = '" . $latestTag . "'");

        // Replace the mock version in specific test files.
        $files = [
            'tests/fixtures/commands/component-check.yml',
            'tests/fixtures/commands/notifications.yml',
            'tests/fixtures/commands/gitleaks.yml',
        ];
        foreach ($files as $file) {
            if (file_exists($file)) {
                $tasks[] = $this->taskReplaceInFile($file)
                    ->from('.toolkit-mock/' . Mock::tag() . '/api')
                    ->to(".toolkit-mock/$latestTag/api");
            }
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Write the release changelog to the CHANGELOG.md file.
     *
     * @param string $version
     *   The version to set.
     * @param string $from
     *   The version to set.
     * @param array<mixed> $options
     *   Command options.
     *
     * @command toolkit:changelog-write
     *
     * @option show-name If set, the name of the user will be added.
     * @option show-pr   If set, the PR number and link will be added.
     * @option full-link If set, the link to the full changelog will be added.
     *
     * @return int|\Robo\Result
     *   The exit code.
     *
     * @hidden
     */
    public function toolkitChangelogWrite(ConsoleIO $io, string $version, string $from = '', array $options = [
        'show-name' => InputOption::VALUE_NONE,
        'show-pr' => InputOption::VALUE_NONE,
        'full-link' => InputOption::VALUE_NONE,
    ])
    {
        // Make sure a version is given.
        if (empty($version) || !Semver::satisfies($version, '>0.0.0')) {
            $io->error('You must provide a valid version as first argument.');
            return ResultData::EXITCODE_ERROR;
        }
        // Get the latest version from the changelog.
        $changelogLatest = $this->getLatestChangelogVersion();
        if (empty($changelogLatest)) {
            $io->error("You must provide a 'from' value, could not find latest version in the $this->changelog file.");
            return ResultData::EXITCODE_ERROR;
        }
        if (empty($from)) {
            $from = $changelogLatest;
        }
        if (!Semver::satisfies($version, '>' . $from)) {
            if (Semver::satisfies($version, '=' . $from)) {
                $io->warning('Changelog file is already in the given version.');
                return ResultData::EXITCODE_OK;
            }
            $io->error("The given version $version do not satisfies the version $from found in the $this->changelog file.");
            return ResultData::EXITCODE_ERROR;
        }

        $changelog = $this->prepareChangelog($from, $options);

        if ($options['full-link'] === true) {
            $changelog[] = '';
            $repo = Toolkit::REPOSITORY;
            $changelog[] = "**Full Changelog**: https://github.com/$repo/compare/$from...$version";
        }

        $body = implode(PHP_EOL, $changelog) . PHP_EOL;
        // Write the changelog.
        return $this->taskChangelog()
            ->setHeader("# Toolkit change log\n\n")
            ->version($version)
            ->setBody("## Version $version\n$body\n")
            ->run();
    }

    /**
     * Prepare a release for Toolkit.
     *
     * @param string $version
     *   The version to set.
     *
     * @command toolkit:prepare-release
     *
     * @return \Robo\Collection\CollectionBuilder
     *   The toolkit prepare release task status.
     *
     * @hidden
     */
    public function toolkitPrepareRelease(string $version)
    {
        $runnerBin = $this->getBin('run');
        return $this->collectionBuilder()->addTaskList([
            $this->taskExec($runnerBin)->args(['toolkit:version-write', $version]),
            $this->taskExec($runnerBin)->args(['toolkit:changelog-write', $version]),
            $this->taskExec($runnerBin)->arg('toolkit:generate-commands-list'),
            $this->taskExec($runnerBin)->arg('toolkit:generate-documentation'),
            $this->taskExec($runnerBin)->arg('toolkit:update-mock-default-tag'),
        ]);
    }

    /**
     * Reads the changelog file and returns the latest version.
     *
     * @return string
     *   The latest version or empty string if not found.
     */
    private function getLatestChangelogVersion()
    {
        if (!file_exists($this->changelog)) {
            return '';
        }
        $content = file_get_contents($this->changelog);
        preg_match('/## Version (.*)/', $content, $match);
        $version = !empty($match[1]) ? $match[1] : '';
        // Check if the changelog version contains two versions on it.
        if (strpos($version, ' | ')) {
            $version = explode(' | ', $version)[1];
        }
        return $version;
    }

    /**
     * Get the git log and process it.
     *
     * @param string $from
     *   The row to process.
     * @param array<mixed> $options
     *   Command options.
     *
     * @return array<mixed>
     *   An array containing the changelog.
     */
    private function prepareChangelog(string $from, array $options)
    {
        // Add working directory as safe.
        $this->taskExec('git config --global --add safe.directory ' . $this->getWorkingDir())->run();
        // Get git log.
        $result = $this->taskExec('git')
            ->arg('log')
            ->arg($from . '...' . $this->releaseBranch)
            ->options([
                'pretty' => '%s##%an##%ae',
                'reverse' => null,
            ], '=')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()
            ->getMessage();

        $changelog = [];
        foreach (explode(PHP_EOL, $result) as $item) {
            $data = explode('##', $item);
            if (empty($data[0]) || empty($data[1]) || empty($data[2])) {
                continue;
            }
            $changelog[] = $this->prepareChangelogRow($data, $options);
        }

        return $changelog;
    }

    /**
     * Prepare the changelog row.
     *
     * @param array<mixed> $data
     *   The row to process.
     * @param array<mixed> $options
     *   Command options.
     *
     * @return string
     *   The prepared row.
     */
    private function prepareChangelogRow(array $data, array $options)
    {
        $message = $data[0];
        $name = $data[1];
        $email = $data[2];
        // Extract PR from the message.
        $pr = '';
        if (preg_match('#(.+) (\(\#[0-9]+\))$#', $message, $matches)) {
            $message = $matches[1];
            $pr = trim($matches[2], '(#)');
        }
        // Try to get username from email.
        if (preg_match('#^[0-9]+\+(.+)@users.noreply.github.com$#', $email, $matches)) {
            $name = '@' . $matches[1];
        }

        $log = '  - ' . trim($message, '.') . '.';
        if ($options['show-name'] === true) {
            $log .= " by $name";
        }
        if ($options['show-pr'] === true) {
            $log .= ' in https://github.com/' . Toolkit::REPOSITORY . "/pull/$pr";
        }

        return $log;
    }

}
