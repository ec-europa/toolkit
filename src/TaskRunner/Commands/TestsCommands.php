<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use NuvoleWeb\Robo\Task as NuvoleWebTasks;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Traits as TaskRunnerTraits;
use Robo\ResultData;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class TestsCommands.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class TestsCommands extends AbstractCommands implements FilesystemAwareInterface
{
    use NuvoleWebTasks\Config\loadTasks;
    use TaskRunnerTasks\CollectionFactory\loadTasks;
    use TaskRunnerTraits\ConfigurationTokensTrait;
    use TaskRunnerTraits\FilesystemAwareTrait;
    use \OpenEuropa\TaskRunner\Tasks\ProcessConfigFile\loadTasks;

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return __DIR__ . '/../../../config/commands/test.yml';
    }

    /**
     * Run PHP code review.
     *
     * @command toolkit:test-phpcs
     *
     * @SuppressWarnings(PHPMD)
     *
     * @aliases tp
     */
    public function toolkitPhpcs()
    {
        $tasks = [];
        $grumphpFile = './grumphp.yml.dist';
        $containsQaConventions = false;

        if (file_exists($grumphpFile)) {
            $grumphpArray = (array) Yaml::parse(file_get_contents($grumphpFile));
            if (isset($grumphpArray['imports'])) {
                foreach ($grumphpArray['imports'] as $import) {
                    if (isset($import['resource']) && $import['resource'] === 'vendor/ec-europa/qa-automation/dist/qa-conventions.yml') {
                        $containsQaConventions = true;
                    }
                }
            }
        }

        $composerFile = './composer.json';
        if (file_exists($composerFile)) {
            $composerArray = json_decode(file_get_contents($composerFile), true);
            if (isset($composerArray['extra']['grumphp']['config-default-path'])) {
                $configDefaultPath = $composerArray['extra']['grumphp']['config-default-path'];
                $this->say('You should remove the following from your composer.json extra array:');
                echo "\n\"grumphp\": {\n    \"config-default-path\": \"$configDefaultPath\"\n}\n\n";
            }
        }

        if ($containsQaConventions) {
            $grumphp_bin = $this->getConfig()->get('runner.bin_dir') . '/grumphp';
            $tasks[] = $this->taskExec($grumphp_bin . ' run');
        } else {
            $this->say('All Drupal projects in the ec-europa namespace need to use Quality Assurance provided standards.');
            $this->say('Your configuration has to import the resource vendor/ec-europa/qa-automation/dist/qa-conventions.yml.');
            $this->say('For more information visit: https://github.com/ec-europa/toolkit/blob/release/4.x/docs/testing-project.md#phpcs-testing');
            $this->say('Add the following lines to your grumphp.yml.dist:');
            echo "\nimports:\n  - { resource: vendor/ec-europa/qa-automation/dist/qa-conventions.yml }\n\n";
            return new ResultData(1);
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run Behat tests.
     *
     * Additional commands could run before and/or after the Behat tests. Such
     * commands should be described in configuration files in this way:
     * @code
     * toolkit:
     *   test:
     *     behat:
     *       profile: "default"
     *       commands:
     *         before:
     *           - task: exec
     *             command: ls -la
     *           - ...
     *         after:
     *           - task: exec
     *             command: whoami
     *           - ...
     * @endcode
     *
     * @command toolkit:test-behat
     *
     * @aliases tb
     *
     * @option from     From behat.yml.dist config file.
     * @option to       To behat.yml config file.
     * @option profile  The profile to execute, default profile is executed if none is provided.
     * @option suite    The suite to execute, default runs all suites of profile.
     */
    public function toolkitBehat(array $options = [
        'from' => InputOption::VALUE_OPTIONAL,
        'to' => InputOption::VALUE_OPTIONAL,
        'profile' => InputOption::VALUE_OPTIONAL,
        'suite' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $tasks = [];
        $behatBin = $this->getConfig()->get('runner.bin_dir') . '/behat';
        $defaultProfile = $this->getConfig()->get('toolkit.test.behat.profile');

        $profile = (!empty($options['profile'])) ? $options['profile'] : $defaultProfile;
        $suite = (!empty($options['suite'])) ? $options['suite'] : '';
        $suiteParameter = ($suite) ? ' --suite=' . $suite : '';

        // Execute a list of commands to run before tests.
        if ($commands = $this->getConfig()->get('toolkit.test.behat.commands.before')) {
            $tasks[] = $this->taskCollectionFactory($commands);
        }

        $this->taskProcessConfigFile($options['from'], $options['to'])->run();

        $result = $this->taskExec($behatBin . " --dry-run --profile=" . $profile . " " . $suiteParameter)
            ->silent(true)->run()->getMessage();

        if (strpos(trim($result), 'No scenarios') !== false) {
            $this->say("No Scenarios found for --profile=" . $profile . " " . $suiteParameter . ", please create at least one Scenario.");
            return new ResultData(1);
        }

        $tasks[] = $this->taskExec($behatBin . " --profile=" . $profile . " " . $suiteParameter);

        // Execute a list of commands to run after tests.
        if ($commands = $this->getConfig()->get('toolkit.test.behat.commands.after')) {
            $tasks[] = $this->taskCollectionFactory($commands);
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run PHPUnit tests.
     *
     * Additional commands could run before and/or after the PHPUnit tests. Such
     * commands should be described in configuration files in this way:
     * @code
     * phpunit:
     *   commands:
     *     before:
     *       - task: exec
     *         command: ls -la
     *       - ...
     *     after:
     *       - task: exec
     *         command: whoami
     *       - ...
     * @endcode
     *
     * @command toolkit:test-phpunit
     *
     * @aliases tp
     *
     * @option from   From phpunit.xml.dist config file.
     * @option to     To phpunit.xml config file.
     */
    public function toolkitPhpUnit(array $options = [
        'from' => InputOption::VALUE_OPTIONAL,
        'to' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $tasks = [];

        if (file_exists($options['from'])) {
            $this->taskProcessConfigFile($options['from'], $options['to'])->run();
        }

        if (!file_exists($options['to'])) {
            $this->say('PHUnit configuration not found, skipping.');
            return $this->collectionBuilder()->addTaskList($tasks);
        }

        // Execute a list of commands to run before tests.
        if ($commands = $this->getConfig()->get('phpunit.commands.before')) {
            $tasks[] = $this->taskCollectionFactory($commands);
        }

        $execution_mode = $this->getConfig()->get('toolkit.test.phpunit.execution');
        $phpunit_bin = $this->getConfig()->get('runner.bin_dir') . '/phpunit';

        if ($execution_mode == 'parallel') {
            $result = $this->taskExec($phpunit_bin . ' --list-suites')
                ->silent(true)
                ->printOutput(false)
                ->run()
                ->getMessage();

            $suites = preg_grep('/^( - [\w\-]+)/', explode("\n", $result));

            $tasks[] = $parallel = $this->taskParallelExec();
            foreach ($suites as $suite) {
                $suite = str_replace('- ', '', trim($suite));
                if (strlen($suite) > 2) {
                    $parallel->process($phpunit_bin . ' --testsuite=' . $suite);
                }
            }
        } else {
            $tasks[] = $this->taskExec($phpunit_bin);
        }

        // Execute a list of commands to run after tests.
        if ($commands = $this->getConfig()->get('phpunit.commands.after')) {
            $tasks[] = $this->taskCollectionFactory($commands);
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run PHP code autofixing.
     *
     * @command toolkit:run-phpcbf
     *
     * @SuppressWarnings(PHPMD)
     *
     * @option test-path  directory or file path to be autofixed by phpcbf.
     *
     * @aliases tpb
     */
    public function toolkitPhpcbf(array $options = [
        'test-path' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = [];
        $grumphpFile = './grumphp.yml.dist';
        $test_path = $options['test-path'];

        if (file_exists($grumphpFile)) {
            $grumphpArray = (array) Yaml::parse(file_get_contents($grumphpFile));
            // Extensions extraction.
            if (isset($grumphpArray['parameters']['tasks.phpcs.triggered_by'])) {
                $extensions = implode(',', array_values($grumphpArray['parameters']['tasks.phpcs.triggered_by']));
            }
            // Standards extraction.
            if (isset($grumphpArray['imports'])) {
                foreach ($grumphpArray['imports'] as $import) {
                    if (isset($import['resource'])) {
                        $qaConventionsFile = './' . $import['resource'];
                    }
                }
            }
        }
        if (file_exists($qaConventionsFile)) {
            $qaConventionsArray = (array) Yaml::parse(file_get_contents($qaConventionsFile));
            if (isset($qaConventionsArray['parameters']['tasks.phpcs.standard'])) {
                foreach ($qaConventionsArray['parameters']['tasks.phpcs.standard'] as $standard) {
                    $standards[] = $standard;
                }
                $standards = implode(',', array_values($standards));
            }
        }
        if (isset($extensions) && isset($standards)) {
            $tasks[] = $this->taskExec('./vendor/bin/phpcbf -s --standard=' . $standards . ' --extensions=' . $extensions . ' ' . $test_path);
        }
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run lint YAML.
     *
     * Override the default include and exclude patterns in configuration files:
     * @code
     * toolkit:
     *   lint:
     *     yaml:
     *       pattern: [ '*.yml', '*.yaml', '*.yml.dist', '*.yaml.dist' ]
     *       include: [ 'lib/' ]
     *       exclude: [ 'vendor/', 'web/', 'node_modules/' ]
     * @endcode
     *
     * @command toolkit:lint-yaml
     *
     * @aliases tly
     */
    public function toolkitLintYaml()
    {
        $pattern = $this->getConfig()->get('toolkit.lint.yaml.pattern');
        $include = $this->getConfig()->get('toolkit.lint.yaml.include');
        $exclude = $this->getConfig()->get('toolkit.lint.yaml.exclude');

        $this->say('Pattern: ' . implode(', ', $pattern));
        $this->say('Include: ' . implode(', ', $include));
        $this->say('Exclude: ' . implode(', ', $exclude));

        $finder = (new Finder())
            ->files()->followLinks()
            ->ignoreVCS(false)
            ->ignoreDotFiles(false)
            ->name($pattern)
            ->notPath($exclude)->in($include);

        // Get the yml files in the root of the project.
        $root_finder = (new Finder())
            ->files()->followLinks()
            ->ignoreVCS(false)
            ->ignoreDotFiles(false)
            ->name($pattern)->in('.')->depth(0);

        $files = array_merge(
            array_keys(iterator_to_array($finder)),
            array_keys(iterator_to_array($root_finder))
        );
        $this->say('Found ' . count($files) . ' files to lint.');

        // Prepare arguments.
        $arg = implode(' ', $files);
        $task = $this->taskExec("./vendor/bin/yaml-lint -q $arg")
            ->printMetadata(false);

        return $this->collectionBuilder()->addTaskList([$task]);
    }

    /**
     * Run lint PHP.
     *
     * Override the default include and exclude patterns in configuration files:
     * @code
     * toolkit:
     *   lint:
     *     php:
     *       extensions: [ 'php', 'module', 'inc', 'theme', 'install' ]
     *       exclude: [ 'vendor/', 'web/' ]
     * @endcode
     *
     * @command toolkit:lint-php
     *
     * @aliases tlp
     */
    public function toolkitLintPhp()
    {
        $excludes = $this->getConfig()->get('toolkit.lint.php.exclude', []);
        $extensions = $this->getConfig()->get('toolkit.lint.php.extensions');

        $this->say('Extensions: ' . implode(', ', $extensions));
        $this->say('Exclude: ' . implode(', ', $excludes));

        $opts = [];
        foreach ($excludes as $exclude) {
            $opts[] = "--exclude $exclude";
        }

        if ($extensions) {
            $opts[] = '-e ' . implode(',', $extensions);
        }
        // Prepare options.
        $opts_string = implode(' ', $opts);
        $task = $this->taskExec("./vendor/bin/parallel-lint $opts_string .");
        return $this->collectionBuilder()->addTaskList([$task]);
    }

    /**
     * Run Blackfire.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @command toolkit:run-blackfire
     *
     * @aliases tbf
     */
    public function toolkitBlackfire()
    {
        $base_url = $this->getConfig()->get('drupal.base_url');
        $project_id = $this->getConfig()->get('toolkit.project_id');
        $bf_client_id = getenv('BLACKFIRE_CLIENT_ID');
        $bf_client_token = getenv('BLACKFIRE_CLIENT_TOKEN');

        if (!getenv('BLACKFIRE_SERVER_ID') || !getenv('BLACKFIRE_SERVER_TOKEN')) {
            $this->say('The blackfire server is not properly configured, please contact QA team.');
            return new ResultData(0);
        }

        if (empty($bf_client_id) || empty($bf_client_token)) {
            $this->say('You must set the following environment variables: BLACKFIRE_CLIENT_ID, BLACKFIRE_CLIENT_TOKEN, skipping.');
            return new ResultData(0);
        }

        // Confirm that blackfire is properly installed.
        $test = $this->taskExec('which blackfire')->silent(true)
            ->run()->getMessage();
        if (strpos($test, 'not found') !== false) {
            $this->say('The Blackfire is not installed, please contact QA team.');
            return new ResultData(0);
        }

        // Make sure that the blackfire agent is properly configured.
        $config = $this->taskExec('cat /etc/blackfire/agent | grep server-id=')
            ->silent(true)->run()->getMessage();
        if ($config === 'server-id=') {
            $this->taskExec('blackfire agent:config')->run();
            $this->taskExec('sudo service blackfire-agent restart')->run();
        }

        $command = "blackfire --json curl $base_url";

        // Execute a list of commands to run after tests.
        $pages = $this->getConfig()->get('toolkit.test.blackfire.pages');
        // Limit the pages up to 10 items.
        $pages = array_slice((array) $pages, 0, 10);
        foreach ($pages as $page) {
            $this->say("Checking page: {$base_url}{$page}");

            $raw = $this->taskExec($command . $page)
                ->silent(true)->run()->getMessage();
            $result = json_decode($raw, true);

            if (empty($result['_links']['graph_url']['href'])) {
                $this->say('Something went wrong, please contact the QA team.');
                return new ResultData(0);
            }

            $data = [];
            $data['graph'] = $result['_links']['graph_url']['href'];
            $data['timeline'] = $result['_links']['timeline_url']['href'];
            $data['recommendation'] = $data['graph'] . '?settings%5BtabPane%5D=recommendations';
            $data['cpu_time'] = $result['envelope']['cpu'] . 'ms';
            $data['wall_time'] = $result['envelope']['wt'] . 'ms';
            $data['io_wait'] = $result['envelope']['io'] . 'ms';
            $data['memory'] = $this->formatBytes($result['envelope']['pmu']);
            $data['sql'] = sprintf(
                "%sms %srq",
                $result['arguments']['io.db.query']['*']['wt'],
                $result['arguments']['io.db.query']['*']['ct']
            );
            $data['network'] = sprintf(
                '%s %s %s',
                !empty($result['envelope']['nw']) ? $this->formatBytes($result['envelope']['nw']) : 'n/a',
                !empty($result['envelope']['nw_in']) ? $this->formatBytes($result['envelope']['nw_in']) : 'n/a',
                !empty($result['envelope']['nw_out']) ? $this->formatBytes($result['envelope']['nw_out']) : 'n/a'
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
            $this->writeln($msg);

            // Handle repo name.
            if (empty($repo = getenv('DRONE_REPO'))) {
                $repo = getenv('CI_PROJECT_NAME');
            }
            if (empty($ci_url = getenv('DRONE_BUILD_LINK'))) {
                $ci_url = getenv('CI_PIPELINE_URL');
            }
            // Send payload to QA website.
            if (!empty($repo)) {
                $payload = [
                    '_links' => [
                        'type' => [
                            'href' => getenv('QA_WEBSITE_URL') . '/rest/type/node/blackfire',
                        ],
                    ],
                    'status' => [['value' => 0]],
                    'type' => [['target_id' => 'blackfire']],
                    'title' => [['value' => "Profiling: $project_id"]],
                    'body' => [['value' => $raw]],
                    'field_blackfire_repository' => [['value' => $repo]],
                    'field_blackfire_page' => [['value' => $page]],
                    'field_blackfire_ci_cd_url' => [['value' => $ci_url]],
                    'field_blackfire_graph_url' => [['value' => $data['graph']]],
                    'field_blackfire_timeline_url' => [['value' => $data['timeline']]],
                    'field_blackfire_recomendations' => [['value' => $data['recommendation']]],
                    'field_blackfire_memory' => [['value' => $data['memory']]],
                    'field_blackfire_wall_time' => [['value' => $data['wall_time']]],
                    'field_blackfire_io_wait' => [['value' => $data['io_wait']]],
                    'field_blackfire_cpu_time' => [['value' => $data['cpu_time']]],
                    'field_blackfire_network' => [['value' => $data['network']]],
                    'field_blackfire_sql' => [['value' => $data['sql']]],
                ];
                if ($playload_response = ToolCommands::postQaContent($payload)) {
                    $this->writeln("Payload sent to QA website: $playload_response");
                } else {
                    $this->writeln('Fail to send the payload.');
                }
                $this->writeln('');
            }
        }

        return new ResultData(0);
    }

    /**
     * Helper to convert bytes to human readable unit.
     *
     * @param int $bytes
     *   The bytes to convert.
     * @param int $precision
     *   The precision for the convertion.
     *
     * @return string
     *   The converted value.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . $units[$pow];
    }
}
