<?php

declare(strict_types=1);

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
                    if (isset($import['resource']) && $import['resource'] === 'vendor/ec-europa/qa-automation/dist/qa-conventions.compatible.yml') {
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
     * behat:
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
     * @command toolkit:test-behat
     *
     * @aliases tb
     *
     * @option from   From behat.yml.dist config file.
     * @option to     To behat.yml config file.
     */
    public function toolkitBehat(array $options = [
        'from' => InputOption::VALUE_OPTIONAL,
        'to' => InputOption::VALUE_OPTIONAL,
        'suite' => 'default'
    ])
    {
        $tasks = [];

        // Execute a list of commands to run before tests.
        if ($commands = $this->getConfig()->get('behat.commands.before')) {
            $tasks[] = $this->taskCollectionFactory($commands);
        }

        $this->taskProcessConfigFile($options['from'], $options['to'])->run();

        $behat_bin = $this->getConfig()->get('runner.bin_dir') . '/behat';
        $result = $this->taskExec($behat_bin . ' --dry-run --suite=' . $options['suite'])
            ->silent(true)
            ->printOutput(false)
            ->run()
            ->getMessage();

        $tasks[] = strpos(trim($result), 'No scenarios') !== 0
        ? $this->taskExec($behat_bin . ' --strict --suite=' . $options['suite'])
        : $this->taskExec($behat_bin . ' --suite=' . $options['suite']);

        // Execute a list of commands to run after tests.
        if ($commands = $this->getConfig()->get('behat.commands.after')) {
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
        $task = $this->taskExec("./vendor/bin/yaml-lint -q $arg");
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
}
