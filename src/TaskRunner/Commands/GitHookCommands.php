<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\Toolkit;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use Robo\ResultData;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;

/**
 * Provides commands to build a site for development and a production artifact.
 */
class GitHookCommands extends AbstractCommands
{
    use TaskRunnerTasks\CollectionFactory\loadTasks;

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . 'config/commands/githooks.yml';
    }

    /**
     * Enable the git hooks defined in the configuration or in given option.
     *
     * @param array $options
     *   Command options.
     *
     * @return \Robo\ResultData
     *   The exit code.
     *
     * @command toolkit:hooks-enable
     *
     * @option hooks    The hooks to enable (default: toolkit.hooks.active)
     *
     * @usage --hooks=pre-push
     */
    public function hooksEnable(array $options = [
        'hooks' => InputOption::VALUE_REQUIRED,
    ])
    {
        $config = $this->getConfig()->get('toolkit.hooks');
        $return = ResultData::EXITCODE_OK;

        $hooks = !empty($options['hooks']) ? explode(',', $options['hooks']) : $config['active'];
        if (empty($hooks)) {
            $this->io()->say('No active hooks to enable, run toolkit:hooks-list to list the available hooks.');
            return $return;
        }
        $available_hooks = $this->getAvailableHooks();

        // Validate hooks.
        foreach ($hooks as $hook) {
            if (!isset($available_hooks[$hook])) {
                $this->io()->say("The hook '$hook' was not found.");
                $return = ResultData::EXITCODE_ERROR;
            }
        }
        if ($return === ResultData::EXITCODE_ERROR) {
            return $return;
        }

        foreach ($hooks as $hook) {
            if ($this->_copy($available_hooks[$hook], Toolkit::getProjectRoot() . '.git/hooks/' . $hook)) {
                $this->_chmod(Toolkit::getProjectRoot() . '.git/hooks/' . $hook, 0755);
            }
        }

        return $return;
    }

    /**
     * Disable the git hooks.
     *
     * @param array $options
     *   Command options.
     *
     * @return \Robo\ResultData
     *   The exit code.
     *
     * @command toolkit:hooks-disable
     *
     * @option hooks    The hooks to disable (default: toolkit.git.hooks)
     */
    public function hooksDisable(array $options = [
        'hooks' => InputOption::VALUE_REQUIRED,
    ])
    {
        $config = $this->getConfig()->get('toolkit.hooks');
        $return = ResultData::EXITCODE_OK;

        $hooks = !empty($options['hooks']) ? explode(',', $options['hooks']) : $config['active'];
        if (empty($hooks)) {
            $this->io()->say('No active hooks to disable, run toolkit:hooks-delete-all to delete them all.');
            return $return;
        }

        foreach ($hooks as $hook) {
            if (!file_exists(Toolkit::getProjectRoot() . '.git/hooks/' . $hook)) {
                $this->io()->say("The hook '$hook' was not found, skipping.");
                continue;
            }
            $this->_remove(Toolkit::getProjectRoot() . '.git/hooks/' . $hook);
        }

        return $return;
    }

    /**
     * Remove all existing hooks, this will ignore active hooks list.
     *
     * @command toolkit:hooks-delete-all
     */
    public function hooksDeleteAll()
    {
        $directory = Toolkit::getProjectRoot() . '.git/hooks';
        $files = scandir($directory);
        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            // Ignore sample files.
            if (substr($file, -7) === '.sample') {
                continue;
            }
            if (unlink($directory . '/' . $file)) {
                $this->io()->say("The hook $file was deleted.");
            }
        }
    }

    /**
     * List available hooks and its status.
     *
     * @command toolkit:hooks-list
     */
    public function hooksList()
    {
        $rows = [];
        $git_hooks_dir = Toolkit::getProjectRoot() . '.git/hooks';
        $config = $this->getConfig()->get('toolkit.hooks');
        $project_id = $this->getConfig()->get('toolkit.project_id');
        $hooks = $this->getAvailableHooks();
        foreach ($hooks as $hook => $file_path) {
            $is_active = $is_enabled = $needs_update = false;
            if (in_array($hook, $config['active'])) {
                $is_active = true;
            }
            if (file_exists($git_hooks_dir . '/' . $hook)) {
                $is_enabled = true;
            }
            if (
                $is_enabled &&
                (
                    sha1_file($file_path) !== sha1_file($git_hooks_dir . '/' . $hook) ||
                    filesize($file_path) !== filesize($git_hooks_dir . '/' . $hook)
                )
            ) {
                $needs_update = true;
            }
            $hook_origin = strpos($file_path, Toolkit::getProjectRoot()) !== false ? $project_id : 'toolkit';
            $rows[] = [
                "$hook ($hook_origin)",
                $is_active ? 'Yes' : 'No',
                $is_enabled ? 'Yes' : 'No',
                $needs_update ? 'Yes' : 'No',
            ];
        }

        $table = new Table($this->io());
        $table
            ->setHeaders([
                'Hook',
                'Active by config',
                'Hook exists',
                'Modified file',
            ])
            ->setRows($rows)->render();
    }

    /**
     * Run a specific hook.
     *
     * @param string $hook
     *   The hook to run.
     * @param string $arg1
     *   The first argument of the given hook.
     * @param string $arg2
     *   The second argument of the given hook.
     * @param string $arg3
     *   The third argument of the given hook.
     *
     * @return \Robo\ResultData
     *   Collection builder.
     *
     * @command toolkit:hooks-run
     */
    public function hooksRun(string $hook, $arg1 = '', $arg2 = '', $arg3 = '')
    {
        $this->io()->say("Receive request for hook: $hook");
        if (empty($hook)) {
            $this->io()->say('Please provide a hook to run.');
            return ResultData::EXITCODE_ERROR;
        }

        // Make sure the hook is enabled.
        $enabled_hooks = $this->getHookFiles(Toolkit::getProjectRoot() . '.git/hooks');
        if (!isset($enabled_hooks[$hook])) {
            $this->io()->say("The hook '$hook' does not exist or is not enabled.");
            return ResultData::EXITCODE_ERROR;
        }

        $method = $this->convertHookToMethod($hook);

        // Check if the method exists in other classes that are instance
        // of this. The first to the found is used.
        foreach (get_declared_classes() as $class) {
            if ($class instanceof $this && method_exists($class, $method)) {
                return (new $class())->$method();
            }
        }

        if (!method_exists($this, $method)) {
            $this->io()->say("The hook '$hook' does not have the corresponding method '$method'.");
            return ResultData::EXITCODE_ERROR;
        }

        return $this->$method();
    }

    /**
     * Hook: Executes the PHPcs against the modified files.
     */
    private function runPreCommit()
    {
        $phpcs = $this->getBin('phpcs');
        $config_file = $this->getConfig()->get('toolkit.test.phpcs.config');

        // Get the modified files, returns a list with a file per line.
        $diff = $this->taskExec('git diff --diff-filter=M --name-only --cached')
            ->silent(true)->run()->getOutputData();
        // The output is empty if there's nothing to be committed.
        if (empty($diff)) {
            return ResultData::EXITCODE_OK;
        }
        $diff = explode(PHP_EOL, $diff);

        // If a config file exists, PHPcs will automatically load it and use
        // the files in there, the workaround here is to regenerate the config
        // file with the modified files and save a backup of the existing
        // config to restore later.
        if (file_exists($config_file)) {
            rename($config_file, $config_file . '.backup');
        }
        // Setup the ruleset with the files to check.
        $files_setup = implode(',', $diff);
        $this->taskExec($this->getBin('run') . " toolkit:setup-phpcs -Dtoolkit.test.phpcs.files=$files_setup")
            ->run();

        // Execute the command.
        $files = "'" . implode("' '", $diff) . "'";
        $result = $this->taskExec("$phpcs --standard=$config_file")->run();

        // Restore the config file if it existed, otherwise remove the
        // generated config.
        if (file_exists($config_file . '.backup')) {
            rename($config_file . '.backup', $config_file);
        } else {
            $this->_remove($config_file);
        }

        return $result->getExitCode();
    }

    /**
     * Hook: Executes the prepare-commit-msg conditions.
     */
    private function runPrepareCommitMsg()
    {
        $args = $this->input()->getArguments();
        // The arg1 is the file that contains the commit message.
        // NOTE: Do not use the arg2 because it is not updated when new
        // message is typed to the commit.
        $message = trim(file_get_contents($args['arg1']));
        $config = $this->getConfig()->get('toolkit.hooks');
        $conditions = $config['prepare-commit-msg']['conditions'];
        $return = ResultData::EXITCODE_OK;
        $problems = [];
        foreach ($conditions as $condition) {
            preg_match($condition['regex'], $message, $matches);
            if (empty($matches)) {
                $problems[] = $condition['message'];
            }
        }
        if (!empty($problems)) {
            $this->io()
                ->say('The commit message validation failed with the following problems:');
            foreach ($problems as $problem) {
                echo $problem . PHP_EOL;
            }
            if (!empty($config['prepare-commit-msg']['example'])) {
                $this->io()
                    ->say("Example: {$config['prepare-commit-msg']['example']}");
            }
            return ResultData::EXITCODE_ERROR;
        }

        return ResultData::EXITCODE_OK;
    }

    /**
     * Hook: Executes the pre-push commands.
     */
    private function runPrePush()
    {
        $config = $this->getConfig()->get('toolkit.hooks');
        $runner_bin = $this->getBin('run');
        foreach ($config['pre-push']['commands'] as $test) {
            $result = $this->taskExec($runner_bin . ' ' . $test);
            var_dump($result);
        }
        return ResultData::EXITCODE_ERROR;
    }

    /**
     * Return the available hooks from Toolkit and Project.
     *
     * @return array
     *   A list of available hooks.
     */
    private function getAvailableHooks()
    {
        $dir = trim($this->getConfig()->get('toolkit.hooks.dir'), '/');
        $toolkit_hooks = $this->getHookFiles(Toolkit::getToolkitRoot() . $dir);
        $project_hooks = $this->getHookFiles(Toolkit::getProjectRoot() . $dir);
        return array_merge($toolkit_hooks, $project_hooks);
    }

    /**
     * Return the hooks present in given directory.
     *
     * @param string $directory
     *   The directory to check for hooks.
     *
     * @return array
     *   An array keyed by hook name and path as value.
     */
    private function getHookFiles(string $directory)
    {
        $directory = rtrim($directory, '/');
        if (!file_exists($directory)) {
            return [];
        }
        $hooks = [];
        $files = scandir($directory);
        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            $hooks[$file] = $directory . '/' . $file;
        }
        return $hooks;
    }

    /**
     * Converts a hook name to method name.
     *
     * @param $hook
     *   The hook name to convert.
     *
     * @return string
     *   The converted hook name, i.e: the pre-push becomes runPrePush.
     */
    private function convertHookToMethod($hook)
    {
        $method = 'run';
        $exploded = explode('-', $hook);
        foreach ($exploded as $item) {
            $method .= ucfirst($item);
        }
        return $method;
    }

}
