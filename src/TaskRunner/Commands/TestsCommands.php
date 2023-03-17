<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Exception\AbortTasksException;
use Robo\Exception\TaskException;
use Robo\ResultData;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Class TestsCommands.
 */
class TestsCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/test.yml';
    }

    /**
     * Setup PHP code sniffer.
     *
     * Check configurations at config/default.yml - 'toolkit.test.phpcs'.
     *
     * @command toolkit:setup-phpcs
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
        if ($config->get('toolkit.test.phpcs.show_sniffs') === true) {
            $element = $phpcs_xml->createElement('arg');
            $element->setAttribute('value', 's');
            $root->appendChild($element);
        }
        // Handle the files.
        $root->appendChild($phpcs_xml->createComment(' Files to check. '));
        if (!empty($files = $config->get('toolkit.test.phpcs.files'))) {
            $files = is_string($files) ? explode(',', $files) : $files;
            Toolkit::filterFolders($files);
            foreach ($files as $file) {
                $root->appendChild($phpcs_xml->createElement('file', $file));
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
     * @aliases tk-phpcs
     *
     * @see toolkitRunPhpcs()
     */
    public function toolkitTestPhpcs()
    {
        $mode = $this->getConfig()->get('toolkit.test.phpcs.mode', 'phpcs');
        if ($mode === 'grumphp') {
            $this->say('Executing PHPcs within GrumPHP.');
            return $this->toolkitRunGrumphp();
        } else {
            $this->say('Executing PHPcs.');
            return $this->toolkitRunPhpcs();
        }
    }

    /**
     * Run PHPMD.
     *
     * Check configurations at config/default.yml - 'toolkit.test.phpmd'.
     *
     * @command toolkit:test-phpmd
     *
     * @option config          The config file.
     * @option format          The format to use.
     * @option ignore_patterns An array with ignore patterns.
     * @option triggered_by    An array with extensions to check.
     * @option files           An array with paths to check.
     *
     * @aliases tk-phpmd
     */
    public function toolkitTestPhpmd(array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'format' => InputOption::VALUE_REQUIRED,
        'ignore_patterns' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'triggered_by' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'files' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
    ])
    {
        $tasks = $execOptions = [];
        Toolkit::ensureArray($options['files']);
        Toolkit::ensureArray($options['ignore_patterns']);
        Toolkit::ensureArray($options['triggered_by']);

        if (!file_exists($options['config'])) {
            $this->output->writeln('Could not find the ruleset file, the default will be created in the project root.');
            $tasks[] = $this->taskFilesystemStack()
                ->copy(Toolkit::getToolkitRoot() . '/resources/phpmd.xml', $options['config']);
        }

        if (!empty($options['ignore_patterns'])) {
            Toolkit::filterFolders($options['ignore_patterns']);
            $execOptions['exclude'] = implode(',', $options['ignore_patterns']);
        }
        if (!empty($options['triggered_by'])) {
            $execOptions['suffixes'] = implode(',', $options['triggered_by']);
        }

        Toolkit::filterFolders($options['files']);
        $files = implode(',', $options['files']);

        $tasks[] = $this->taskExec($this->getBin('phpmd'))
            ->args([$files, $options['format'], $options['config']])
            ->options($execOptions);

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run PHP code sniffer within GrumPHP.
     *
     * @throws TaskException
     *
     * @deprecated
     */
    protected function toolkitRunGrumphp()
    {
        $bin = $this->getBin('grumphp');
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
            return $this->taskExec("$bin run")->run();
        } else {
            $this->say('All Drupal projects in the ec-europa namespace need to use Quality Assurance provided standards.');
            $this->say('Your configuration has to import the resource vendor/ec-europa/qa-automation/dist/qa-conventions.yml.');
            $this->say('For more information visit: https://github.com/ec-europa/toolkit/blob/release/4.x/docs/testing-project.md#phpcs-testing');
            $this->say('Add the following lines to your grumphp.yml.dist:');
            echo "\nimports:\n  - { resource: vendor/ec-europa/qa-automation/dist/qa-conventions.yml }\n\n";
            return new ResultData(1);
        }
    }

    /**
     * Run PHP code sniffer.
     *
     * Check configurations at config/default.yml - 'toolkit.test.phpcs'.
     */
    protected function toolkitRunPhpcs()
    {
        $config = $this->getConfig();
        $phpcs_bin = $this->getBin('phpcs');
        $config_file = $config->get('toolkit.test.phpcs.config');

        $this->toolkitCheckPhpcsRequirements();

        $options = '';
        if ($config->get('toolkit.test.phpcs.ignore_annotations') === true) {
            $options .= ' --ignore-annotations';
        }
        return $this->taskExec("$phpcs_bin --standard=$config_file$options")
            ->run();
    }

    /**
     * Make sure that the config file exists and configuration is correct.
     *
     * @command toolkit:check-phpcs-requirements
     */
    public function toolkitCheckPhpcsRequirements()
    {
        $config_file = $this->getConfig()->get('toolkit.test.phpcs.config');
        if (!file_exists($config_file)) {
            $this->say('Calling toolkit:setup-phpcs.');
            $this->toolkitSetupPhpcs();
        }

        // Skip standards check for Toolkit.
        if (getcwd() === Toolkit::getToolkitRoot()) {
            return;
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
            throw new AbortTasksException("The following standards are missing, please add them to the configuration file '$config_file'.\n" . implode("\n", $diff));
        }
    }

    /**
     * Run PHPStan.
     *
     * Check configurations at config/default.yml - 'toolkit.test.phpstan'.
     *
     * @command toolkit:test-phpstan
     *
     * @option config       The path to the config file.
     * @option level        The level of rule options.
     * @option files        The files to check.
     * @option memory-limit The PHP memory limit.
     * @option options      Extra options for the command without -- (only options with no value).
     *
     * @aliases tk-phpstan
     *
     * @usage --memory-limit='4G' --options='debug'
     */
    public function toolkitTestPhpstan(array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'level' => InputOption::VALUE_REQUIRED,
        'files' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'memory-limit' => InputOption::VALUE_OPTIONAL,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
        Toolkit::ensureArray($options['files']);
        Toolkit::filterFolders($options['files']);

        $config = $this->getConfig();
        $ignoreErrors = $config->get('toolkit.test.phpstan.ignore_errors');
        $includes = $config->get('toolkit.test.phpstan.includes');
        Toolkit::filterFolders($includes);
        $ignores = $config->get('toolkit.test.phpstan.ignores');
        Toolkit::filterFolders($ignores);
        if (!$options['memory-limit']) {
            $options['memory-limit'] = ini_get('memory_limit');
        }
        $tasks = [];

        // If the config file is not found, generate a new one.
        if (!file_exists($options['config'])) {
            $config_content = [
                'includes' => $includes,
                'parameters' => [
                    'level' => $options['level'],
                    'paths' => array_values($options['files']),
                    'excludePaths' => $ignores,
                    'ignoreErrors' => $ignoreErrors,
                ],
            ];
            if (file_exists($config->get('drupal.root'))) {
                $config_content['parameters']['drupal']['drupal_root'] = getcwd() . '/' . $config->get('drupal.root');
            }
            $tasks[] = $this->taskWriteToFile($options['config'])
                ->text(Yaml::dump($config_content, 10, 2));
        }

        $exec = $this->taskExec($this->getBin('phpstan'))
            ->arg('analyse')
            ->options([
                'memory-limit' => $options['memory-limit'],
                'configuration' => $options['config']
            ], '=');

        if (!empty($options['options'])) {
            $extraOptions = array_fill_keys(explode(' ', $options['options']), null);
            $exec->options($extraOptions);
        }

        $tasks[] = $exec;
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run Behat tests.
     *
     * Check configurations at config/default.yml - 'toolkit.test.behat'.
     * Accept commands to run before and/or after the Behat tests.
     *
     * @command toolkit:test-behat
     *
     * @aliases tb, tk-behat
     *
     * @option from     The dist config file (behat.yml.dist).
     * @option to       The destination config file (behat.yml).
     * @option profile  The profile to execute.
     * @option suite    The suite to execute, default runs all suites of profile.
     * @option options  Extra options for the command without -- (only options with no value).
     *
     * @usage --profile='prod' --options='strict stop-on-failure'
     */
    public function toolkitTestBehat(array $options = [
        'from' => InputOption::VALUE_OPTIONAL,
        'to' => InputOption::VALUE_OPTIONAL,
        'profile' => InputOption::VALUE_OPTIONAL,
        'suite' => InputOption::VALUE_OPTIONAL,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $tasks = [];

        if (Toolkit::isCiCd()) {
            $this->taskExec($this->getBin('run') . ' toolkit:install-dependencies')->run();
        }

        $behatBin = $this->getBin('behat');
        $defaultProfile = $this->getConfig()->get('toolkit.test.behat.profile');
        $execOpts = [
            'profile' => !empty($options['profile']) ? $options['profile'] : $defaultProfile,
        ];

        if (!empty($options['suite'])) {
            $execOpts['suite'] = $options['suite'];
        }
        if (!empty($options['options'])) {
            $extraOptions = array_fill_keys(explode(' ', $options['options']), null);
            $execOpts = array_merge($execOpts, $extraOptions);
        }

        // Execute a list of commands to run before tests.
        if ($commands = $this->getConfig()->get('toolkit.test.behat.commands.before')) {
            $tasks[] = $this->taskExecute($commands);
        }

        $this->taskProcess($options['from'], $options['to'])->run();

        $result = $this->taskExec($behatBin)->options($execOpts + ['dry-run' => null], '=')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        if (str_contains(trim($result), 'No scenarios')) {
            $this->say("No Scenarios found for profile {$execOpts['profile']}, please create at least one Scenario.");
            return new ResultData(1);
        }

        $tasks[] = $this->taskExec($behatBin)->options($execOpts, '=');

        // Execute a list of commands to run after tests.
        if ($commands = $this->getConfig()->get('toolkit.test.behat.commands.after')) {
            $tasks[] = $this->taskExecute($commands);
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run PHPUnit tests.
     *
     * Check configurations at config/default.yml - 'toolkit.test.phpunit'.
     * Accept commands to run before and/or after the PHPUnit tests.
     *
     * @command toolkit:test-phpunit
     *
     * @aliases tp, tk-phpunit
     *
     * @option execution The execution type (default or parallel).
     * @option from      The dist config file (phpunit.xml.dist).
     * @option to        The destination config file (phpunit.xml).
     * @option testsuite Filter which testsuite to run.
     * @option group     Only runs tests from the specified group(s).
     * @option covers    Only runs tests annotated with "@covers <name>".
     * @option uses      Only runs tests annotated with "@uses <name>".
     * @option filter    Filter which tests to run.
     * @option options   Extra options for the command without -- (only options with no value).
     * @option printer   If set, use printer defined in config toolkit.test.phpunit.printer.
     *
     * @usage --options='stop-on-error process-isolation do-not-cache-result'
     * @usage --group=Example
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function toolkitTestPhpunit(array $options = [
        'execution' => InputOption::VALUE_REQUIRED,
        'from' => InputOption::VALUE_REQUIRED,
        'to' => InputOption::VALUE_REQUIRED,
        'testsuite' => InputOption::VALUE_REQUIRED,
        'group' => InputOption::VALUE_REQUIRED,
        'covers' => InputOption::VALUE_REQUIRED,
        'uses' => InputOption::VALUE_REQUIRED,
        'filter' => InputOption::VALUE_REQUIRED,
        'options' => InputOption::VALUE_REQUIRED,
        'printer' => InputOption::VALUE_NONE,
    ])
    {
        if (!file_exists($options['to'])) {
            if (!file_exists($options['from'])) {
                $this->say('PHUnit configuration not found, skipping.');
                return $this->collectionBuilder()->addTaskList([]);
            }
            $this->taskProcess($options['from'], $options['to'])->run();
        }
        $tasks = [];
        $phpunitConfig = $this->getConfig()->get('toolkit.test.phpunit');

        // Execute a list of commands to run before tests.
        if (!empty($phpunitConfig['commands']['before'])) {
            $tasks[] = $this->taskExecute($phpunitConfig['commands']['before']);
        }

        if ($options['execution'] == 'parallel') {
            $tasks[] = $this->toolkitTestPhpunitParallelTask($options);
        } else {
            $task = $this->taskExec($this->getBin('phpunit'));
            if (!empty($options['options'])) {
                $options['options'] = str_replace('--', '', $options['options']);
                $opts = array_fill_keys(explode(' ', $options['options']), null);
                $task->options($opts, '=');
            }
            foreach (['testsuite', 'group', 'covers', 'uses', 'filter'] as $item) {
                if ($options[$item]) {
                    $task->option($item, $options[$item], '=');
                }
            }
            if ($options['printer'] === true) {
                $task->option('printer', $phpunitConfig['printer'], '=');
            }
            $tasks[] = $task;
        }

        // Execute a list of commands to run after tests.
        if (!empty($phpunitConfig['commands']['after'])) {
            $tasks[] = $this->taskExecute($phpunitConfig['commands']['after']);
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Returns the task to execute PHPUnit in parallel.
     *
     * @param array $options
     *   The options passed to the command test-phpunit.
     */
    private function toolkitTestPhpunitParallelTask(array $options)
    {
        $phpunitBin = $this->getBin('phpunit');
        $result = $this->taskExec($phpunitBin)->option('list-suites')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        preg_match_all('/ - (.+)/', $result, $matches);
        $parallel = $this->taskParallelExec()->printOutput();
        if (!empty($matches[1])) {
            $opts = ' ';
            if (!empty($options['options'])) {
                $options['options'] = str_replace('--', '', $options['options']);
                $opts .= '--' . implode(' --', explode(' ', $options['options']));
            }
            foreach (['testsuite', 'group', 'covers', 'uses', 'filter'] as $item) {
                if ($options[$item]) {
                    $opts .= " --$item=$options[$item]";
                }
            }
            if ($options['printer'] === true) {
                $opts .= " --printer=" . $this->getConfig()->get('toolkit.test.phpunit.printer');
            }
            foreach ($matches[1] as $suite) {
                if (strlen($suite) > 2) {
                    $parallel->process("$phpunitBin --testsuite='$suite'$opts");
                }
            }
        } else {
            $this->writeln('No items found.');
        }
        return $parallel;
    }

    /**
     * Run PHP code autofixing.
     *
     * @command toolkit:run-phpcbf
     *
     * @aliases tk-phpcbf
     */
    public function toolkitRunPhpcbf()
    {
        $phpcbf_bin = $this->getBin('phpcbf');
        $config_file = $this->getConfig()->get('toolkit.test.phpcs.config');
        $this->toolkitCheckPhpcsRequirements();
        return $this->taskExec("$phpcbf_bin --standard=$config_file")->run();
    }

}
