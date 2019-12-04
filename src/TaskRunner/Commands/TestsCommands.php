<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use NuvoleWeb\Robo\Task as NuvoleWebTasks;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Traits as TaskRunnerTraits;
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
        
        if ($containsQaConventions) {
            $tasks[] = $this->taskExec('./vendor/bin/grumphp run');
        } else {
            $this->say('All Drupal projects in the ec-europa namespace need to use Quality Assurance provided standards.');
            $this->say('Your configuration has to import the resource vendor/ec-europa/qa-automation/dist/qa-conventions.yml.');
            $this->say('Add the following lines to your grumphp.yml.dist:');
            echo "\nimports:\n  - { resource: vendor/ec-europa/qa-automation/dist/qa-conventions.yml }\n\n";
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

        $result = $this->taskExec('./vendor/bin/behat --dry-run')
            ->silent(true)
            ->printOutput(false)
            ->run()
            ->getMessage();

        $tasks[] = strpos(trim($result), 'No scenarios') !== 0 ?
        $this->taskExec('./vendor/bin/behat --strict') :
        $this->taskExec('./vendor/bin/behat');

        return $this->collectionBuilder()->addTaskList($tasks);
    }
}
