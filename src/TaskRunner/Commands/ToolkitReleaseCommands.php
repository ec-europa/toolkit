<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;

class ToolkitReleaseCommands extends AbstractCommands
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
    private string $releaseBranch = 'release/9.x';

    /**
     * Holds the base url for the Pull requests.
     *
     * @var string
     */
    private string $repo = 'https://github.com/ec-europa/toolkit';

    /**
     * Write the specified version string into needed places.
     *
     * @param string $version
     *   The version to set.
     *
     * @command toolkit:version-write
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
        $tasks[] = $this->taskReplaceInFile('src/Toolkit.php')
            ->regex("#VERSION = '[^']*'#")
            ->to("VERSION = '" . $version . "'");

        if (!file_exists('phpdoc.dist.xml')) {
            $io->warning('Could not find the file phpdoc.dist.xml, ignoring.');
        } else {
            $tasks[] = $this->taskReplaceInFile('phpdoc.dist.xml')
                ->regex('#<version number="[^"]*">#')
                ->to('<version number="' . $version . '">');
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
     * @param array $options
     *   Command options.
     *
     * @command toolkit:changelog-write
     *
     * @option show-name If set, the name of the user will be added.
     * @option show-pr   If set, the PR number and link will be added.
     * @option full-link If set, the link to the full changelog will be added.
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
        $changelog_latest = $this->getLatestChangelogVersion();
        $is_first_log = empty($changelog_latest);
        if ($is_first_log && empty($changelog_latest)) {
            $io->error("You must provide a 'from' value, could not find latest version in the $this->changelog file.");
            return ResultData::EXITCODE_ERROR;
        }
        if (empty($from)) {
            $from = $changelog_latest;
        }
        if (!$is_first_log && !Semver::satisfies($version, '>' . $from)) {
            $io->error("The given version $version do not satisfies the version $from found in the $this->changelog file.");
            return ResultData::EXITCODE_ERROR;
        }

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

            $pr = '';
            $message = $data[0];
            $name = $data[1];
            $email = $data[2];
            // Extract PR from the message.
            if (preg_match('#(.+) (\(\#[0-9]+\))$#', $message, $matches)) {
                $message = $matches[1] ?? '';
                $pr = isset($matches[2]) ? trim($matches[2], '(#)') : '';
            }
            // Try to get username from email.
            if (preg_match('#^[0-9]+\+(.+)@users.noreply.github.com$#', $email, $matches)) {
                $name = '@' . $matches[1] ?? '';
            }

            $log = '  - ' . trim($message, '.') . '.';
            if ($options['show-name'] === true) {
                $log .= ' by ' . $name;
            }
            if ($options['show-pr'] === true) {
                $log .= " in $this->repo/pull/$pr";
            }
            $changelog[] = $log;
        }

        if ($options['full-link'] === true) {
            $changelog[] = '';
            $changelog[] = "**Full Changelog**: $this->repo/compare/$from...$version";
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
     * @hidden
     */
    public function toolkitPrepareRelease(string $version)
    {
        $runner_bin = $this->getBin('run');
        return $this->collectionBuilder()->addTaskList([
            $this->taskExec($runner_bin)->args(['toolkit:version-write', $version]),
            $this->taskExec($runner_bin)->args(['toolkit:changelog-write', $version]),
            $this->taskExec($runner_bin)->arg('toolkit:generate-commands-list'),
            $this->taskExec($runner_bin)->arg('toolkit:generate-documentation'),
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
        return !empty($match[1]) ? $match[1] : '';
    }

}
