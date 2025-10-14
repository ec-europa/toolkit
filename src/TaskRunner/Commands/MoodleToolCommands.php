<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use EcEuropa\Toolkit\JunitXmlGenerator;
use EcEuropa\Toolkit\Toolkit;
use EcEuropa\Toolkit\Website;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generic tools.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MoodleToolCommands extends ToolCommands
{

    /**
     * Check the Toolkit Requirements.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command moodle:requirements
     *
     * @option endpoint The endpoint to use to connect to QA Website.
     * @option junit    Whether to export results as junit.
     *
     * @aliases md-req
     *
     * @return int
     *   The toolkit requirements command status.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function toolkitRequirements(ConsoleIO $io, array $options = [
        'endpoint' => InputOption::VALUE_REQUIRED,
    ])
    {
        $this->say("Checking Toolkit requirements:\n");

        $junitFile = 'junit-requirements.xml';
        if ($this->isJunit()) {
            JunitXmlGenerator::addTestSuite('Requirements');
            JunitXmlGenerator::addTestCase('Requirements', 'Endpoint connection');
            JunitXmlGenerator::addTestCase('Requirements', 'NEXTCLOUD configuration');
            JunitXmlGenerator::addTestCase('Requirements', 'PHP version');
            JunitXmlGenerator::addTestCase('Requirements', 'Toolkit version');
        }

        if (!empty($options['endpoint'])) {
            Website::setUrl($options['endpoint']);
        }
        $data = Website::requirements();
        if (empty($data)) {
            $message = 'Failed to connect to the endpoint ' . Website::url() . '/api/v1/toolkit-requirements';
            $io->error($message);
            if ($this->isJunit()) {
                JunitXmlGenerator::addResult('Requirements', 'Endpoint connection', $message);
                JunitXmlGenerator::generate($junitFile);
            }
            return 1;
        }
        if (!isset($data['toolkit'])) {
            $message = 'Invalid data returned from the endpoint.';
            $this->writeln($message);
            if ($this->isJunit()) {
                JunitXmlGenerator::addResult('Requirements', 'Endpoint connection', $message);
                JunitXmlGenerator::generate($junitFile);
            }
            return 1;
        }
        $endpointCheck = 'OK';

        // Handle PHP version.
        $phpVersion = phpversion();
        $isValid = version_compare($phpVersion, $data['php_version']);
        $phpCheck = ($isValid >= 0) ? 'OK' : 'FAIL';
        if ($this->isJunit() && $phpCheck === 'FAIL') {
            JunitXmlGenerator::addResult('Requirements', 'PHP version', $phpCheck);
        }

        // Handle Toolkit version.
        if (!($toolkitVersion = self::getPackagePropertyFromComposer('ec-europa/toolkit', 'version', 'packages-dev'))) {
            $toolkitCheck = 'FAIL (not found)';
        } else {
            $toolkitCheck = Semver::satisfies($toolkitVersion, $data['toolkit']) ? 'OK' : 'FAIL';
        }
        if ($this->isJunit() && str_starts_with($toolkitCheck, 'FAIL')) {
            JunitXmlGenerator::addResult('Requirements', 'Toolkit version', $toolkitCheck);
        }

        // Check if node_install enable in the .opts.yml.
        $parseOptsFile = self::parseOptsYml();
        $nodeCheck = 'OK';
        $nodeVersion = '';
        // Check if npm_install property enabled.
        if (!empty($parseOptsFile['npm_install'])) {
            if ($this->isJunit()) {
                JunitXmlGenerator::addTestCase('Requirements', 'Node version');
            }
            // Check node version running.
            $exec = $this->taskExec('node --version')
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
                ->run();
            $nodeVersion = rtrim($exec->getMessage());
            if (empty($nodeVersion)) {
                $nodeCheck = 'FAIL (not found)';
            } else {
                $nodeVersion = trim($nodeVersion, 'v');
                $nodeCheck = Semver::satisfies($nodeVersion, $data['node']) ? 'OK' : 'FAIL';
            }

            if ($this->isJunit() && str_starts_with($nodeCheck, 'FAIL')) {
                JunitXmlGenerator::addResult('Requirements', 'Node version', $nodeCheck);
            }
        }

        // Handle NEXTCLOUD.
        $ncUser = Toolkit::getNextcloudUser();
        $ncPass = Toolkit::getNextcloudPass();
        if (!empty($ncUser) && !empty($ncPass)) {
            $nextcloudCheck = 'OK';
        } else {
            $nextcloudCheck = 'FAIL (Missing environment variable(s):';
            $nextcloudCheck .= empty($ncUser) ? ' NEXTCLOUD_USER' : '';
            $nextcloudCheck .= empty($ncPass) ? ' NEXTCLOUD_PASS' : '';
            $nextcloudCheck .= ')';
            if ($this->isJunit()) {
                JunitXmlGenerator::addResult('Requirements', 'NEXTCLOUD configuration', $nextcloudCheck);
            }
        }

        $io->title('Checking connections:');
        $io->definitionList(
            ['QA Endpoint access' => $endpointCheck],
            ['NEXTCLOUD configuration' => $nextcloudCheck],
        );
        $toolkitExtra = '';
        if ($toolkitVersion && $latestToolkit = self::getPackageLatestVersion(Toolkit::REPOSITORY)) {
            if (!Semver::satisfies($toolkitVersion, $latestToolkit)) {
                $toolkitExtra = " <comment>($latestToolkit available)</>";
            }
        }

        $io->title('Required checks:');
        $headers = ['PHP version', 'Toolkit version'];
        $rows = [
            "$phpCheck ($phpVersion)",
            "$toolkitCheck" . (!empty($toolkitVersion) ? " ($toolkitVersion)" : '') . (!empty($toolkitExtra) ? $toolkitExtra : ''),
        ];
        if (!empty($parseOptsFile['npm_install'])) {
            $headers[] = 'Node version';
            $rows[] = $nodeCheck . (!empty($nodeVersion) ? " ($nodeVersion)" : '');
        }
        $io->horizontalTable($headers, [$rows]);

        if ($this->isJunit()) {
            JunitXmlGenerator::generate($junitFile);
        }

        if ($phpCheck !== 'OK' || $toolkitCheck !== 'OK' || $nodeCheck !== 'OK') {
            return 1;
        }
        return 0;
    }

}
