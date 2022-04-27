<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use NuvoleWeb\Robo\Task as NuvoleWebTasks;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Tasks\ProcessConfigFile\loadTasks;
use OpenEuropa\TaskRunner\Traits as TaskRunnerTraits;
use Robo\ResultData;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class TestsCommands.
 */
class TestsCommands extends AbstractCommands implements FilesystemAwareInterface
{
    use NuvoleWebTasks\Config\loadTasks;
    use TaskRunnerTasks\CollectionFactory\loadTasks;
    use TaskRunnerTraits\ConfigurationTokensTrait;
    use TaskRunnerTraits\FilesystemAwareTrait;
    use loadTasks;

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return __DIR__ . '/../../../config/commands/test.yml';
    }

    /**
     * Setup PHP code sniffer for standalone execution.
     *
     * @command toolkit:setup-phpcs
     */
    public function toolkitSetupPhpcs()
    {
        $config = $this->getConfig();
        $config_file = $config->get('toolkit.test.phpcs.config');
        if (file_exists($config_file)) {
            $this->taskExec('rm')->arg($config_file)->run();
        }

        $phpcs_xml = new \DOMDocument('1.0', 'UTF-8');
        $phpcs_xml->formatOutput = true;
        // Root element.
        $root = $phpcs_xml->createElement('ruleset');
        $root->setAttribute('name', 'QA');
        $phpcs_xml->appendChild($root);
        $root->appendChild($phpcs_xml->createElement('description', 'QA PHPcs Ruleset'));

        // Handle standards.
        $root->appendChild($phpcs_xml->createComment(' Standards. '));
        if (!empty($standards = $config->get('toolkit.test.phpcs.standards'))) {
            foreach ($standards as $standard) {
                $element = $phpcs_xml->createElement('rule');
                $element->setAttribute('ref', $standard);
                $root->appendChild($element);
            }
        }
        $root->appendChild($phpcs_xml->createComment(' Arguments. '));
        // Handle file extensions.
        if (!empty($extensions = $config->get('toolkit.test.phpcs.triggered_by'))) {
            $element = $phpcs_xml->createElement('arg');
            $element->setAttribute('name', 'extensions');
            $element->setAttribute('value', implode(',', array_values($extensions)));
            $root->appendChild($element);
        }
        // Handle argument report.
        $element = $phpcs_xml->createElement('arg');
        $element->setAttribute('name', 'report');
        $element->setAttribute('value', 'full');
        $root->appendChild($element);
        // Handle argument color.
        $element = $phpcs_xml->createElement('arg');
        $element->setAttribute('name', 'colors');
        $root->appendChild($element);
        // Handle argument progress.
        $element = $phpcs_xml->createElement('arg');
        $element->setAttribute('value', 'p');
        $root->appendChild($element);
        // Handle show sniffs.
        if (!empty($config->get('toolkit.test.phpcs.show_sniffs'))) {
            $element = $phpcs_xml->createElement('arg');
            $element->setAttribute('value', 's');
            $root->appendChild($element);
        }
        // Handle the files.
        $root->appendChild($phpcs_xml->createComment(' Files to check. '));
        if (!empty($files = $config->get('toolkit.test.phpcs.files'))) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $root->appendChild($phpcs_xml->createElement('file', $file));
                } else {
                    $this->writeln("The path '$file' was not found, ignoring.");
                }
            }
        } else {
            $root->appendChild($phpcs_xml->createElement('file', '.'));
        }
        // Handle exclude patterns.
        $root->appendChild($phpcs_xml->createComment(' Exclude patterns. '));
        if (!empty($ignores = $config->get('toolkit.test.phpcs.ignore_patterns'))) {
            foreach ($ignores as $ignore) {
                $root->appendChild($phpcs_xml->createElement('exclude-pattern', $ignore));
            }
        }

        $root->appendChild($phpcs_xml->createComment(' Add your custom rules after this line. '));
        $this->taskWriteToFile($config_file)
            ->text($phpcs_xml->saveXML())->run();
    }

    /**
     * Run PHP code sniffer.
     *
     * @command toolkit:test-phpcs
     *
     * @aliases tp
     */
    public function toolkitPhpcs()
    {
        $mode = $this->getConfig()->get('toolkit.test.phpcs.mode', 'grumphp');
        if ($mode === 'grumphp') {
            $this->say('Executing PHPcs within GrumPHP.');
            return $this->runGrumphp();
        } else {
            $this->say('Executing PHPcs in standalone mode.');
            return $this->runPhpcs();
        }
    }

    /**
     * Run PHP code sniffer within GrumPHP.
     */
    public function runGrumphp()
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
            $tasks[] = $this->taskExec($this->getBin('grumphp') . ' run');
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
     * Run PHP code sniffer in standalone mode.
     *
     * @code
     * toolkit:
     *   test:
     *     phpcs:
     *       mode: grumphp || phpcs
     *       config: phpcs.xml
     *       ignore_annotations: 0
     *       show_sniffs: 0
     *       standards:
     *         - ./vendor/drupal/coder/coder_sniffer/Drupal
     *         - ./vendor/drupal/coder/coder_sniffer/DrupalPractice
     *         - ./vendor/ec-europa/qa-automation/phpcs/QualityAssurance
     *       ignore_patterns:
     *         - vendor/
     *         - web/
     *         - node_modules/
     *       triggered_by:
     *         - php
     *         - module
     *         - inc
     *         - theme
     *         - install
     *         - yml
     *       files:
     *         - ./lib
     * @endcode
     */
    public function runPhpcs()
    {
        $config = $this->getConfig();
        $phpcs_bin = $this->getBin('phpcs');
        $config_file = $config->get('toolkit.test.phpcs.config');

        $this->checkPhpCsRequirements();

        $options = '';
        if (!empty($config->get('toolkit.test.phpcs.ignore_annotations'))) {
            $options .= ' --ignore-annotations';
        }
        return $this->taskExec("$phpcs_bin --standard=$config_file$options")
            ->run();
    }

    /**
     * Make sure that the config file exists and configuration is correct.
     *
     * @return \Robo\ResultData|void
     *   No return if all is ok, return 1 if fails.
     */
    private function checkPhpCsRequirements()
    {
        $config_file = $this->getConfig()->get('toolkit.test.phpcs.config');
        if (!file_exists($config_file)) {
            $this->say('Calling toolkit:setup-phpcs.');
            $this->toolkitSetupPhpcs();
        }

        // Make sure the required standards are present.
        $standards = [
            './vendor/drupal/coder/coder_sniffer/Drupal',
            './vendor/drupal/coder/coder_sniffer/DrupalPractice',
            './vendor/ec-europa/qa-automation/phpcs/QualityAssurance',
        ];
        $rules = [];
        $data = simplexml_load_file($config_file);
        foreach ($data->rule as $item) {
            if (isset($item['ref'])) {
                $rules[] = (string) $item['ref'];
            }
        }
        if ($diff = array_diff($standards, $rules)) {
            $this->say("The following standards are missing, please add them to the configuration file '$config_file'.\n" . implode("\n", $diff));
            exit;
        }
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
     * @option profile  The profile to execute.
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
        $behatBin = $this->getBin('behat');
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
     *   options: '--log-junit report.xml'
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
        $options = $this->getConfig()->get('toolkit.test.phpunit.options');
        $phpunit_bin = $this->getBin('phpunit');

        if ($execution_mode == 'parallel') {
            $result = $this->taskExec("$phpunit_bin --list-suites")
                ->silent(true)
                ->printOutput(false)
                ->run()
                ->getMessage();

            $suites = preg_grep('/^( - [\w\-]+)/', explode("\n", $result));

            $tasks[] = $parallel = $this->taskParallelExec();
            foreach ($suites as $suite) {
                $suite = str_replace('- ', '', trim($suite));
                if (strlen($suite) > 2) {
                    $parallel->process("$phpunit_bin --testsuite=$suite $options");
                }
            }
        } else {
            $tasks[] = $this->taskExec("$phpunit_bin $options");
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
     * Run PHP code autofixing in standalone mode.
     *
     * @command toolkit:run-phpcbf-standalone
     */
    public function toolkitPhpcbfStandalone()
    {
        $phpcbf_bin = $this->getBin('phpcbf');
        $config_file = $this->getConfig()->get('toolkit.test.phpcs.config');
        $this->checkPhpCsRequirements();
        return $this->collectionBuilder()->addTaskList([
            $this->taskExec("$phpcbf_bin --standard=$config_file"),
        ]);
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
        $tasks = [];
        $pattern = $this->getConfig()->get('toolkit.lint.yaml.pattern');
        $includes = $this->getConfig()->get('toolkit.lint.yaml.include');
        $excludes = $this->getConfig()->get('toolkit.lint.yaml.exclude');

        $this->say('Pattern: ' . implode(', ', $pattern));
        $this->say('Include: ' . implode(', ', $includes));
        $this->say('Exclude: ' . implode(', ', $excludes));

        $finder = (new Finder())
            ->files()->followLinks()
            ->ignoreVCS(false)
            ->ignoreDotFiles(false);
        foreach ($pattern as $name) {
            $finder->name($name);
        }
        foreach ($includes as $include) {
            $finder->in($include);
        }
        foreach ($excludes as $exclude) {
            $finder->notPath($exclude);
        }

        // Get the yml files in the root of the project.
        $root_finder = (new Finder())
            ->files()->followLinks()
            ->ignoreVCS(false)
            ->ignoreDotFiles(false)
            ->in('.')->depth(0);
        foreach ($pattern as $name) {
            $root_finder->name($name);
        }

        $finder_files = array_merge(
            array_keys(iterator_to_array($finder)),
            array_keys(iterator_to_array($root_finder))
        );

        $this->say('Found ' . count($finder_files) . ' files to lint.');
        $chunk = array_chunk($finder_files, 600);
        foreach ($chunk as $files) {
            echo 'Processing ' . count($files) . ' files.' . PHP_EOL;
            // Prepare arguments.
            $arg = implode(' ', $files);
            $tasks[] = $this->taskExec("./vendor/bin/yaml-lint -q $arg")
                ->printMetadata(false);
        }

        return $this->collectionBuilder()->addTaskList($tasks);
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
     * @command toolkit:run-blackfire
     *
     * @aliases tbf
     */
    public function toolkitBlackfire()
    {
        $base_url = $this->getConfig()->get('drupal.base_url');
        $project_id = $this->getConfig()->get('toolkit.project_id');
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
            $this->say("Problems found:\n" . implode("\n", $problems));
            return new ResultData(0);
        }

        $command = "blackfire --json curl $base_url";

        // Get the list of pages to check and prevent duplicates.
        $pages = $this->getConfig()->get('toolkit.test.blackfire.pages');
        $pages = array_unique($pages);

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
            if (empty($url = getenv('QA_WEBSITE_URL'))) {
                $url = 'https://webgate.ec.europa.eu/fpfis/qa';
            }
            if (!empty($repo)) {
                $payload = [
                    '_links' => ['type' => [
                        'href' => $url . '/rest/type/node/blackfire',
                    ]],
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
                    'field_blackfire_commit_hash' => [['value' => getenv('DRONE_COMMIT') ?? '']],
                    'field_blackfire_commit_link' => [['value' => getenv('DRONE_PULL_REQUEST') ?? '']],
                    'field_blackfire_pr' => [['value' => getenv('DRONE_COMMIT_LINK') ?? '']],
                ];
                $payload_response = ToolCommands::postQaContent($payload);
                if (!empty($payload_response) && $payload_response === '201') {
                    $this->writeln("Payload sent to QA website: $payload_response");
                } else {
                    $this->writeln('Fail to send the payload, HTTP code: ' . $payload_response);
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
