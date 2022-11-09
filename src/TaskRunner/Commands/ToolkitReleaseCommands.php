<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Contract\VerbosityThresholdInterface;

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
     * Write the specified version string into needed places.
     *
     * @param string $version
     *   The version to set.
     *
     * @command toolkit:version-write
     *
     * @hidden
     */
    public function toolkitVersionWrite(string $version)
    {
        $tasks = [];
        $tasks[] = $this->taskReplaceInFile(Toolkit::getToolkitRoot() . '/src/Toolkit.php')
            ->regex("#VERSION = '[^']*'#")
            ->to("VERSION = '" . $version . "'");

        $tasks[] = $this->taskReplaceInFile(Toolkit::getToolkitRoot() . '/phpdoc.dist.xml')
            ->regex('#<version number="[^"]*">#')
            ->to('<version number="' . $version . '">');

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Write the release changelog to the CHANGELOG.md file.
     *
     * @param string $version
     *   The version to set.
     *
     * @command toolkit:changelog-write
     *
     * @hidden
     */
    public function toolkitChangelogWrite(string $version)
    {
        // Get the latest release.
        if (empty($latest_version = $this->getLatestChangelogVersion())) {
            $this->writeln("Could not find latest version in the $this->changelog file.");
            return;
        }

        // Get git log.
        $result = $this->taskExec('git')
            ->arg('log')
            ->arg($latest_version . '...' . $this->releaseBranch)
            ->option('pretty', '  - %s %an %cn', '=')
            ->option('merges')
            ->option('reverse')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()
            ->getMessage();

        // Write the changelog.
        $this->taskChangelog()
            ->setHeader("# Toolkit change log\n\n")
            ->version($version)
            ->setBody("## Version $version\n$result\n")
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
        $content = file_get_contents($this->changelog);
        preg_match('/## Version (.*)/', $content, $match);
        return !empty($match[1]) ? $match[1] : '';
    }

}
