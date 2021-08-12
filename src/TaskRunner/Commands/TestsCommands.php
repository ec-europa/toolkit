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
     * @aliases tp
     */
    public function toolkitPhpcs()
    {
        $tasks = [];
        $grumphpFile = './grumphp.yml.dist';
        $qaConventionsFile = 'vendor/ec-europa/qa-automation/dist/qa-conventions.yml';
        $qaConventionsArray = (array) Yaml::parse(file_get_contents($qaConventionsFile));
        $containsQaConventions = $required_files_error = $forbidden_files_error = false;

        if (file_exists($grumphpFile)) {
            $grumphpArray = (array) Yaml::parse(file_get_contents($grumphpFile));
            if (isset($grumphpArray['imports'])) {
                foreach ($grumphpArray['imports'] as $import) {
                    if (isset($import['resource']) && $import['resource'] === $qaConventionsFile) {
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

        // Check for required files.
        if (isset($qaConventionsArray['parameters']['tasks.phpcs.files.required'])) {
            $required_files = $qaConventionsArray['parameters']['tasks.phpcs.files.required'];

            // Check for project level overrides.
            if (isset($grumphpArray['parameters']['tasks.phpcs.files.required'])) {
                $required_files = $grumphpArray['parameters']['tasks.phpcs.files.required'];
            }

            if (!empty($required_files)) {
                foreach ($required_files as $required_file) {
                    if (!file_exists($required_file)) {
                        $this->say("The file '{$required_file}' is required.");
                        $required_files_error = true;
                    }
                }
                if ($required_files_error) {
                    echo "Please provide the file(s) and resume your task.\n";
                }
            }
        }

        // Check for forbidden files.
        if (isset($qaConventionsArray['parameters']['tasks.phpcs.files.forbidden'])) {
            $forbidden_files = $qaConventionsArray['parameters']['tasks.phpcs.files.forbidden'];

            // Check for project level overrides.
            if (isset($grumphpArray['parameters']['tasks.phpcs.files.forbidden'])) {
                $forbidden_files = $grumphpArray['parameters']['tasks.phpcs.files.forbidden'];
            }

            if (!empty($forbidden_files)) {
                foreach ($forbidden_files as $required_file) {
                    if (file_exists($required_file)) {
                        $this->say("The file '{$required_file}' is forbidden.");
                        $forbidden_files_error = true;
                    }
                }
                if ($forbidden_files_error) {
                    echo "Please remove the file(s) and resume your task.\n";
                }
            }
        }

        if ($required_files_error || $forbidden_files_error) {
            return new ResultData(1);
        }

        if ($containsQaConventions) {
            $grumphp_bin = $this->getConfig()->get('runner.bin_dir') . '/grumphp';
            $tasks[] = $this->taskExec($grumphp_bin . ' run');
        } else {
            $this->say('All Drupal projects in the ec-europa namespace need to use Quality Assurance provided standards.');
            $this->say("Your configuration has to import the resource {$qaConventionsFile}.");
            $this->say('For more information visit: https://github.com/ec-europa/toolkit/blob/release/4.x/docs/testing-project.md#phpcs-testing');
            $this->say('Add the following lines to your grumphp.yml.dist:');
            echo "\nimports:\n  - { resource: {$qaConventionsFile} }\n\n";
            return new ResultData(1);
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run Behat tests.
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
    ])
    {
        $tasks = [];

        $this->taskProcessConfigFile($options['from'], $options['to'])->run();

        $behat_bin = $this->getConfig()->get('runner.bin_dir') . '/behat';
        $result = $this->taskExec($behat_bin . ' --dry-run')
            ->silent(true)
            ->printOutput(false)
            ->run()
            ->getMessage();

        $tasks[] = strpos(trim($result), 'No scenarios') !== 0
        ? $this->taskExec($behat_bin . ' --strict')
        : $this->taskExec($behat_bin);

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
}
