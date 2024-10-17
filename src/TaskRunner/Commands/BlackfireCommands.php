<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use EcEuropa\Toolkit\Website;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Commands to interact with the Blackfire.
 */
class BlackfireCommands extends AbstractCommands
{

    /**
     * Run Blackfire.
     *
     * @command toolkit:run-blackfire
     *
     * @option endpoint The endpoint to use to connect to QA Website.
     *
     * @aliases tk-bfire, tbf
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function toolkitRunBlackfire(ConsoleIO $io, array $options = [
        'endpoint' => InputOption::VALUE_REQUIRED,
    ])
    {
        if (!empty($options['endpoint'])) {
            Website::setUrl($options['endpoint']);
        }
        $baseUrl = $this->getConfig()->get('drupal.base_url');
        $projectId = $this->getConfig()->get('toolkit.project_id');
        $problems = [];
        if (!getenv('BLACKFIRE_SERVER_ID') || !getenv('BLACKFIRE_SERVER_TOKEN')) {
            $problems[] = 'Missing environment variables: BLACKFIRE_SERVER_ID, BLACKFIRE_SERVER_TOKEN, skipping.';
        }
        if (!getenv('BLACKFIRE_CLIENT_ID') || !getenv('BLACKFIRE_CLIENT_TOKEN')) {
            $problems[] = 'Missing environment variables: BLACKFIRE_CLIENT_ID, BLACKFIRE_CLIENT_TOKEN, skipping.';
        }

        // Confirm that blackfire is properly installed.
        $test = $this->taskExec('which blackfire')->silent(true)
            ->run()->getMessage();
        if (strpos($test, 'not found') !== false) {
            $problems[] = 'The Blackfire is not installed, skipping.';
        }

        // Make sure that the blackfire agent is properly configured.
        $config = $this->taskExec('cat /etc/blackfire/agent | grep server-id=')
            ->silent(true)->run()->getMessage();
        if ($config === 'server-id=') {
            $this->taskExec('blackfire agent:config')->run();
            $this->taskExec('service blackfire-agent restart')->run();
        }

        if (!empty($problems)) {
            $io->say("Problems found:\n" . implode("\n", $problems));
            return new ResultData(0);
        }

        $command = "blackfire --json curl $baseUrl";

        // Get the list of pages to check and prevent duplicates.
        $pages = $this->getConfig()->get('toolkit.test.blackfire.pages');
        $pages = array_unique($pages);

        // Limit the pages up to 10 items.
        $pages = array_slice((array) $pages, 0, 10);
        foreach ($pages as $page) {
            $io->say("Checking page: {$baseUrl}{$page}");

            $raw = $this->taskExec($command . $page)
                ->silent(true)->run()->getMessage();
            $result = json_decode($raw, true);

            if (empty($result['_links']['graph_url']['href'])) {
                $io->say('Something went wrong, please contact the QA team.');
                return new ResultData(0);
            }

            $data = [];
            $data['graph'] = $result['_links']['graph_url']['href'];
            $data['timeline'] = $result['_links']['timeline_url']['href'];
            $data['recommendation'] = $data['graph'] . '?settings%5BtabPane%5D=recommendations';
            $data['cpu_time'] = $result['envelope']['cpu'] . 'ms';
            $data['wall_time'] = $result['envelope']['wt'] . 'ms';
            $data['io_wait'] = $result['envelope']['io'] . 'ms';
            $data['memory'] = ToolCommands::formatBytes($result['envelope']['pmu']);
            $data['sql'] = sprintf(
                "%sms %srq",
                $result['arguments']['io.db.query']['*']['wt'],
                $result['arguments']['io.db.query']['*']['ct']
            );
            $data['network'] = sprintf(
                '%s %s %s',
                !empty($result['envelope']['nw']) ? ToolCommands::formatBytes($result['envelope']['nw']) : 'n/a',
                !empty($result['envelope']['nw_in']) ? ToolCommands::formatBytes($result['envelope']['nw_in']) : 'n/a',
                !empty($result['envelope']['nw_out']) ? ToolCommands::formatBytes($result['envelope']['nw_out']) : 'n/a'
            );

            // Print the relevant information.
            $msg = sprintf(
                "Memory:\t\t%s\nWall Time:\t%s\nI/O Wait:\t%s\nCPU Time:\t%s\nNetwork:\t%s\nSQL:\t\t%s",
                $data['memory'],
                $data['wall_time'],
                $data['io_wait'],
                $data['cpu_time'],
                $data['network'],
                $data['sql']
            );
            $io->writeln($msg);

            // Handle repo name.
            if (empty($repo = getenv('DRONE_REPO'))) {
                $repo = getenv('CI_PROJECT_NAME');
            }
            if (empty($ciUrl = getenv('DRONE_BUILD_LINK'))) {
                $ciUrl = getenv('CI_PIPELINE_URL');
            }

            // Send payload to QA website.
            $url = Website::url();
            if (empty($auth = Website::apiAuth())) {
                $io->writeln('Failed to connect to the endpoint. Required env var QA_API_AUTH_TOKEN.');
                return new ResultData(0);
            }
            if (!empty($repo)) {
                $commit = !empty(getenv('DRONE_COMMIT')) ? getenv('DRONE_COMMIT') : '';
                $link = !empty(getenv('DRONE_PULL_REQUEST')) ? getenv('DRONE_PULL_REQUEST') : '';
                $pullRequest = !empty(getenv('DRONE_COMMIT_LINK')) ? getenv('DRONE_COMMIT_LINK') : '';
                $payload = [
                    '_links' => [
                        'type' => [
                            'href' => $url . '/rest/type/node/blackfire',
                        ],
                    ],
                    'type' => [['target_id' => 'blackfire']],
                    'title' => [['value' => "Profiling: $projectId"]],
                    'body' => [['value' => $raw]],
                    'field_blackfire_repository' => [['value' => $repo]],
                    'field_blackfire_page' => [['value' => $page]],
                    'field_blackfire_ci_cd_url' => [['value' => $ciUrl]],
                    'field_blackfire_graph_url' => [['value' => $data['graph']]],
                    'field_blackfire_timeline_url' => [['value' => $data['timeline']]],
                    // cspell:ignore recomendations
                    'field_blackfire_recomendations' => [['value' => $data['recommendation']]],
                    'field_blackfire_memory' => [['value' => $data['memory']]],
                    'field_blackfire_wall_time' => [['value' => $data['wall_time']]],
                    'field_blackfire_io_wait' => [['value' => $data['io_wait']]],
                    'field_blackfire_cpu_time' => [['value' => $data['cpu_time']]],
                    'field_blackfire_network' => [['value' => $data['network']]],
                    'field_blackfire_sql' => [['value' => $data['sql']]],
                    'field_blackfire_commit_hash' => [['value' => $commit]],
                    'field_blackfire_commit_link' => [['value' => $link]],
                    'field_blackfire_pr' => [['value' => $pullRequest]],
                ];
                $response = Website::post($payload, $auth);
                if (!empty($response) && $response === '201') {
                    $io->writeln("Payload sent to QA website: $response");
                } else {
                    $io->writeln('Fail to send the payload, HTTP code: ' . $response);
                }
                $io->writeln('');
            }
        }

        return new ResultData(0);
    }

    /**
     * Copy the needed resources to run Behat with Blackfire.
     *
     * @command toolkit:setup-blackfire-behat
     */
    public function setupBlackfireBehat()
    {
        // Check requirement if blackfire/php-sdk exist.
        if (!class_exists('Blackfire\Client')) {
            $this->say('Please install blackfire/php-sdk before continue.');
            return 0;
        }

        $from = $this->getConfig()->get('toolkit.test.behat.from');
        $blackfireDir = Toolkit::getToolkitRoot() . '/resources/Blackfire';
        $parseBehatYml = Yaml::parseFile($from);
        if (isset($parseBehatYml['blackfire'])) {
            $this->say('Blackfire profile was found, skipping.');
        } else {
            // Append the Blackfire profile to the behat.yml file.
            $this->taskWriteToFile($from)->append(true)
                ->line('# Toolkit auto-generated profile for Blackfire.')
                ->text(file_get_contents("$blackfireDir/blackfire.behat.yml"))
                ->line('# End Toolkit.')
                ->run();
        }

        // Add the test feature to the tests folder.
        if (file_exists('tests/features/blackfire.feature')) {
            $this->say('Blackfire test feature was found, skipping.');
        } else {
            $this->_copy("$blackfireDir/blackfire.feature", 'tests/features/blackfire.feature');
        }

        // Add the Blackfire Context to the Context folder.
        if (file_exists('tests/Behat/BlackfireMinkContext.php')) {
            $this->say('Blackfire Mink context was found, skipping.');
        } else {
            $this->_copy("$blackfireDir/BlackfireMinkContext.php", 'tests/Behat/BlackfireMinkContext.php');
        }

        return 0;
    }

}
