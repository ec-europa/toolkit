<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use NuvoleWeb\Robo\Task as NuvoleWebTasks;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Traits as TaskRunnerTraits;
use Symfony\Component\Console\Input\InputOption;

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

        $tasks[] = $this->taskExec('./vendor/bin/grumphp run --config=./vendor/ec-europa/qa-automation/dist/qa-conventions.yml');

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
