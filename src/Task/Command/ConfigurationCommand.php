<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Task\Command;

use EcEuropa\Toolkit\Task\File\ReplaceBlock;
use Robo\Collection\CollectionBuilder;
use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Exception\TaskException;
use Robo\Task\BaseTask;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Process\Process;

/**
 * Execute the tasks from a Configuration command.
 */
class ConfigurationCommand extends BaseTask implements BuilderAwareInterface
{
    use BuilderAwareTrait;

    /**
     * An array with tasks to execute.
     *
     * @var array
     */
    protected array $tasks;

    /**
     * Contains the available tasks and configuration.
     *
     * Each task defines all the needed parameters to execute,
     * the required to ensure they are present, and the defaults
     * to ensure they have the default value.
     *
     * @var array
     */
    protected array $availableTasks = [
        'mkdir' => ['required' => 'dir', 'defaults' => 'mode'],
        'touch' => ['required' => 'file', 'defaults' => ['time', 'atime']],
        'copy' => ['required' => ['from', 'to'], 'defaults' => 'force'],
        'copy-dir' => ['required' => ['from', 'to']],
        'rename' => ['required' => ['from', 'to'], 'defaults' => 'force'],
        'chmod' => [
            'required' => ['file', 'permissions'],
            'defaults' => ['umask', 'recursive'],
        ],
        'chgrp' => [
            'required' => ['file', 'group'],
            'defaults' => 'recursive',
        ],
        'chown' => ['required' => ['file', 'user'], 'defaults' => 'recursive'],
        'remove' => ['required' => 'file'],
        'symlink' => ['required' => ['from', 'to']],
        'mirror' => ['required' => ['from', 'to']],
        'process' => ['required' => ['source'], 'defaults' => 'destination'],
        'append' => ['required' => ['file', 'text']],
        'run' => ['required' => 'command'],
//        'process-php' => ['required' => ['source', 'destination'], 'defaults' => 'override'],
        'exec' => ['required' => 'command'],
        'replace-block' => [
            'required' => ['filename', 'start', 'end'],
            'defaults' => ['content', 'excludeStartEnd'],
        ],
    ];

    /**
     * Constructs a new Process task.
     *
     * @param array $tasks
     *   The Command Tasks.
     */
    public function __construct(array $tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * Run the command tasks.
     */
    public function run()
    {
        $collection = $this->collectionBuilder();

        foreach ($this->getTasks() as $task) {
            $collection->addTask($this->taskExecute($task));
        }

        return $collection->run();
    }

    /**
     * Execute single task.
     *
     * @param $task
     *   The task to execute.
     *
     * @return CollectionBuilder
     *
     * @throws TaskException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function taskExecute($task)
    {
        $this->validateAndEnsureParameters($task);
        switch ($task['task']) {
            case 'mkdir':
                return $this->collectionBuilder()->taskFilesystemStack()
                    ->mkdir($task['dir'], $task['mode']);

            case 'touch':
                return $this->collectionBuilder()->taskFilesystemStack()
                    ->touch($task['file'], $task['time'], $task['atime']);

            case 'copy':
                if (is_dir($task['from'])) {
                    return $this->collectionBuilder()->taskCopyDir([$task['from'] => $task['to']]);
                }
                return $this->collectionBuilder()->taskFilesystemStack()
                    ->copy($task['from'], $task['to'], $task['force']);

            case 'copy-dir':
                return $this->collectionBuilder()->taskCopyDir([$task['from'] => $task['to']]);

            case 'chmod':
                return $this->collectionBuilder()->taskFilesystemStack()
                    ->chmod($task['file'], $task['permissions'], $task['umask'], $task['recursive']);

            case 'chgrp':
                return $this->collectionBuilder()->taskFilesystemStack()
                    ->chgrp($task['file'], $task['group'], $task['recursive']);

            case 'chown':
                return $this->collectionBuilder()->taskFilesystemStack()
                    ->chown($task['file'], $task['user'], $task['recursive']);

            case 'remove':
                return $this->collectionBuilder()->taskFilesystemStack()
                    ->remove($task['file']);

            case 'rename':
                return $this->collectionBuilder()->taskFilesystemStack()
                    ->rename($task['from'], $task['to'], $task['force']);

            case 'symlink':
                return $this->collectionBuilder()->taskFilesystemStack()
                    ->symlink($task['from'], $task['to']);

            case 'mirror':
                return $this->collectionBuilder()->taskFilesystemStack()
                    ->mirror($task['from'], $task['to']);

            case 'process':
                return $this->collectionBuilder()->taskProcess($task['source'], $task['destination']);

            case 'append':
                return $this->collectionBuilder()->addTaskList([
                    $this->collectionBuilder()->taskWriteToFile($task['file'])
                        ->append()->text($task['text']),
                    $this->collectionBuilder()->taskProcess($task['file'], $task['file']),
                ]);

            case 'run':
                /* @var \Robo\Task\Base\Exec $taskExec */
                $taskExec = $this->collectionBuilder()
                    ->taskExec($this->getConfig()->get('runner.bin_dir') . '/run')
                    ->arg($task['command']);
                if (!empty($task['arguments'])) {
                    $taskExec->args($task['arguments']);
                }
                if (!empty($task['options'])) {
                    $taskExec->options($task['options'], '=');
                }
                $this->prepareOutput($taskExec);
                return $taskExec;

            case 'exec':
                /* @var \Robo\Task\Base\Exec $taskExec */
                $taskExec = $this->collectionBuilder()->taskExec($task['command']);
                if (!empty($task['arguments'])) {
                    $taskExec->args($task['arguments']);
                }
                if (!empty($task['options'])) {
                    $taskExec->options($task['options']);
                }
                if (!empty($task['dir'])) {
                    $taskExec->dir($task['dir']);
                }
                $this->prepareOutput($taskExec);
                return $taskExec;

            case 'replace-block':
                /* @var ReplaceBlock $task */
                $replaceBlock = $this->collectionBuilder()
                    ->taskReplaceBlock($task['filename'])
                    ->start($task['start']);
                if (!empty($task['end'])) {
                    $replaceBlock->end($task['end']);
                }
                if (!empty($task['content'])) {
                    $replaceBlock->content($task['content']);
                }
                if ($task['excludeStartEnd']) {
                    $replaceBlock->excludeStartEnd();
                }
                return $replaceBlock;

            default:
                $this->throwInvalidTaskException($task['task'] ?? '');
        }
    }

    /**
     * Return the current tasks.
     *
     * @return array
     *   The tasks.
     */
    public function getTasks()
    {
        return $this->tasks['tasks'] ?? $this->tasks;
    }

    /**
     * Validate and ensure the parameter are met.
     *
     * @param $task
     *   The task being executed.
     *
     * @see \EcEuropa\Toolkit\Task\Command\ConfigurationCommand::availableTasks
     * @see \EcEuropa\Toolkit\Task\Command\ConfigurationCommand::paramDefaultValue()
     *
     * @throws TaskException
     */
    private function validateAndEnsureParameters(&$task)
    {
        $availableTasks = $this->availableTasks;

        if (is_string($task)) {
            $task = ['task' => 'exec', 'command' => $task];
            $message = 'A command must have a "task" to execute, use: %s';
            $this->printTaskWarning(sprintf($message, json_encode($task)));
        }
        if (!isset($task['task']) || !isset($availableTasks[$task['task']])) {
            $this->throwInvalidTaskException($task['task'] ?? '');
        }
        foreach ((array) $availableTasks[$task['task']]['required'] as $required) {
            if (empty($task[$required])) {
                $this->throwParamException($task['task'], $required);
            }
        }
        if (isset($availableTasks[$task['task']]['defaults'])) {
            foreach ((array) $availableTasks[$task['task']]['defaults'] as $default) {
                $task[$default] = $task[$default] ?? $this->paramDefaultValue($default);
            }
        }
    }

    /**
     * Prepares the Output of a taskExec.
     *
     * @param $taskExec
     *   The task exec being executed.
     */
    private function prepareOutput($taskExec)
    {
        $taskExec->interactive(Process::isTtySupported());
        if ($this->output() instanceof NullOutput) {
            $taskExec->printOutput(false);
        }
    }

    /**
     * Report missing parameter, this stops the execution.
     *
     * @param string $param
     *   The missing parameter.
     * @param string $task
     *   The task being checked.
     *
     * @throws TaskException
     */
    private function throwParamException(string $param, string $task)
    {
        $message = 'The parameter "%s" is required for task "%s" in configuration command.';
        throw new TaskException($this, sprintf($message, $task, $param));
    }

    /**
     * Report missing parameter, this stops the execution.
     *
     * @param string $task
     *   The task being checked.
     *
     * @throws TaskException
     */
    private function throwInvalidTaskException(string $task)
    {
        $message = 'Task "%s" is not supported.';
        throw new TaskException($this, sprintf($message, $task));
    }

    /**
     * The default parameter values, return all or given parameter default value.
     *
     * @param string $key
     *   The parameter name.
     *
     * @return string|bool|mixed
     *   The default value for given name.
     */
    private function paramDefaultValue(string $key)
    {
        $defaults = [
            'atime' => time(),
            'force' => false,
            'mode' => 0777,
            'override' => false,
            'recursive' => false,
            'time' => time(),
            'umask' => 0000,
            'excludeStartEnd' => false,
        ];
        return $defaults[$key] ?? '';
    }

}
