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
     * Write the specified version string back into the Toolkit.php file.
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
        return $this->taskReplaceInFile(Toolkit::getToolkitRoot() . '/src/Toolkit.php')
            ->regex("#VERSION = '[^']*'#")
            ->regex("#VERSION = '[^']*'#")
            ->to("VERSION = '" . $version . "'")
            ->run();
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
            ->option('pretty', '  - %s', '=')
            ->option('no-merges')
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
     * Create a release for Toolkit.
     *
     * @param string $version
     *   The version to set.
     *
     * @command toolkit:release
     *
     * @hidden
     */
    public function toolkitRelease(string $version)
    {
        $runner_bin = $this->getBin('run');
        $this->taskExec($runner_bin)->args(['toolkit:version-write', $version])
            ->run();
        $this->taskExec($runner_bin)->args(['toolkit:changelog-write', $version])
            ->run();

        $answer = $this->ask("The changelog and version were updated, please validate.\nDo you want to commit and push the changes? (yes/no) [no]");
        if (!in_array($answer, ['y', 'yes'])) {
            $this->writeln('Stopping here.');
            return;
        }

        $this->taskGitStack()->stopOnFail()
            ->add('-A')
            ->commit("Prepare release $version")
            ->push('origin', $this->releaseBranch)
            ->run();

        $answer = $this->ask('The changelog was pushed, do you want to create and push the tag? (yes/no) [no]');
        if (!in_array($answer, ['y', 'yes'])) {
            $this->writeln('Stopping here.');
            return;
        }

        $this->taskGitStack()->stopOnFail()
            ->tag($version)
            ->push('origin', $version);
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
