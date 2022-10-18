<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Task\Command;

use Robo\Collection\CollectionBuilder;
use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Exception\TaskException;
use Robo\Task\BaseTask;

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
        'process' => ['required' => ['source', 'destination']],
        'append' => ['required' => ['file', 'text']],
        'run' => ['required' => 'command'],
        'process-php' => ['required' => ['source', 'destination'], 'defaults' => 'override'],
        'exec' => ['required' => 'command'],
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
                return $taskExec;

            case 'process-php':
                // If we don't override destination file simply exit here.
                if (!$task['override'] && file_exists($task['destination'])) {
                    return $this->collectionBuilder();
                }

                // Copy source file to destination before processing it.
                $tasks[] = $this->collectionBuilder()->taskFilesystemStack()
                    ->copy($task['source'], $task['destination'], true);

                // Map dynamic task type to actual task callback.
                $map = [
                    'append' => 'taskAppendConfiguration',
                    'prepend' => 'taskPrependConfiguration',
                    'write' => 'taskWriteConfiguration',
                ];

                if (!isset($map[$task['type']])) {
                    $message = "'process-php' task type '{$task['type']}' is not supported, valid values are: 'append', 'prepend' and 'write'.";
                    throw new TaskException($this, $message);
                }
                $method = $map[$task['type']];

                // Add selected process task and return collection.
                $tasks[] = $this->{$method}($task['destination'], $this->getConfig())
                    ->setConfigKey($task['config']);

                return $this->collectionBuilder()->addTaskList($tasks);

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
                return $taskExec;

            default:
                throw new TaskException($this, "Task '{$task['task']}' is not supported.");
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
     * @see $this->availableTasks
     * @see $this->paramDefaultValue()
     *
     * @throws TaskException
     */
    private function validateAndEnsureParameters(&$task)
    {
        $availableTasks = $this->availableTasks;

        if (is_string($task)) {
            $task = ['task' => 'exec', 'command' => $task];
            $message = "A command must have a 'task' to execute, use: %s";
            $this->printTaskWarning(sprintf($message, json_encode($task)));
        }
        if (!isset($task['task']) || !isset($availableTasks[$task['task']])) {
            throw new TaskException(
                $this,
                "Task '" . ($task['task'] ?? '') . "' is not supported."
            );
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
        $message = "The parameter '%s' is required for task '%s' in configuration command.";
        throw new TaskException($this, sprintf($message, $task, $param));
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
        ];
        return $defaults[$key] ?? '';
    }
}
