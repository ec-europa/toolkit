<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Unit;

use EcEuropa\Toolkit\Tests\AbstractTest;
use Symfony\Component\Process\Process;

/**
 * Unit test to ensure Drupal core upgrade to version 11.
 *
 * @group drupal-core-11-upgrade
 */
class DrupalCore11UpgradeTest extends AbstractTest
{

    /**
     * Test method GitHooksCommands::convertHookToMethod().
     */
    public function testConvertHookToMethod()
    {
        // Run this test only for php >=8.3.
        if (!version_compare(phpversion(), '8.3', '>=')) {
            $this->markTestSkipped('Skipping test, php version is not >=8.3.');
        }

        // Copy the toolkit composer.json file.
        $this->fs->copy('composer.json', $this->getSandboxFilepath('composer.json'));

        $options = '--dry-run --no-interaction --no-ansi --no-progress';
        $command = 'composer require drupal/core:^11.2.2 drupal/core-dev:^11.2.2 drupal/core-composer-scaffold:^11.2.2';
        $process = Process::fromShellCommandline($command . ' ' . $options);
        $process->setWorkingDirectory($this->getSandboxRoot())->enableOutput()->run();
        if ($process->getExitCode()) {
            throw new \Exception($process->getErrorOutput());
        }
        // For some reason the output goes to the ErrorOutput.
        $output = !empty($process->getOutput()) ? $process->getOutput() : $process->getErrorOutput();

        $this->assertStringContainsString('Installing drupal/core', $output);
        $this->assertStringNotContainsString('Installation failed', $output);
    }

}
