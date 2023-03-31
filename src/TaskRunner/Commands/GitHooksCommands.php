<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use DOMDocument;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;

/**
 * Provides commands to interact with git hooks.
 *
 * @SuppressWarnings("unused")
 */
class GitHooksCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/git-hooks.yml';
    }

    /**
     * Enable the git hooks defined in the configuration or in given option.
     *
     * @param array $options
     *   Command options.
     *
     * @return int
     *   The exit code.
     *
     * @command toolkit:hooks-enable
     *
     * @option hooks The hooks to enable (default: toolkit.hooks.active)
     *
     * @usage --hooks=pre-push
     */
    public function hooksEnable(ConsoleIO $io, array $options = [
        'hooks' => InputOption::VALUE_REQUIRED,
    ])
    {
        $config = $this->getConfig()->get('toolkit.hooks');
        $return = ResultData::EXITCODE_OK;

        $hooks = !empty($options['hooks']) ? explode(',', $options['hooks']) : $config['active'];
        if (empty($hooks)) {
            $io->say('No active hooks to enable, run toolkit:hooks-list to list the available hooks.');
            return $return;
        }
        $available_hooks = $this->getAvailableHooks();

        // Validate hooks.
        foreach ($hooks as $hook) {
            if (!isset($available_hooks[$hook])) {
                $io->say("The hook '$hook' was not found.");
                $return = ResultData::EXITCODE_ERROR;
            }
        }
        if ($return === ResultData::EXITCODE_ERROR) {
            return $return;
        }

        $dir = $this->getWorkingDir();
        foreach ($hooks as $hook) {
            $copy = $this->_copy($available_hooks[$hook], $dir . '/.git/hooks/' . $hook);
            if ($copy->getExitCode() === ResultData::EXITCODE_OK) {
                $this->_chmod($dir . '/.git/hooks/' . $hook, 0755);
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
     * @return int
     *   The exit code.
     *
     * @command toolkit:hooks-disable
     *
     * @option hooks The hooks to disable (default: toolkit.git.hooks)
     */
    public function hooksDisable(ConsoleIO $io, array $options = [
        'hooks' => InputOption::VALUE_REQUIRED,
    ])
    {
        $config = $this->getConfig()->get('toolkit.hooks');

        $hooks = !empty($options['hooks']) ? explode(',', $options['hooks']) : $config['active'];
        if (empty($hooks)) {
            $io->say('No active hooks to disable, run toolkit:hooks-delete-all to delete them all.');
            return ResultData::EXITCODE_OK;
        }

        $dir = $this->getWorkingDir();
        foreach ($hooks as $hook) {
            if (!file_exists($dir . '/.git/hooks/' . $hook)) {
                $io->say("The hook '$hook' was not found, skipping.");
                continue;
            }
            $this->_remove($dir . '/.git/hooks/' . $hook);
        }

        return ResultData::EXITCODE_OK;
    }

    /**
     * Remove all existing hooks, this will ignore active hooks list.
     *
     * @command toolkit:hooks-delete-all
     */
    public function hooksDeleteAll(ConsoleIO $io)
    {
        $directory = $this->getWorkingDir() . '/.git/hooks';
        $files = scandir($directory);
        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            // Ignore sample files.
            if (str_ends_with($file, '.sample')) {
                continue;
            }
            if (unlink($directory . '/' . $file)) {
                $io->say("The hook $file was deleted.");
            }
        }
    }

    /**
     * List available hooks and its status.
     *
     * @command toolkit:hooks-list
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function hooksList(ConsoleIO $io)
    {
        $rows = [];
        $git_hooks_dir = $this->getWorkingDir() . '/.git/hooks';
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
                (sha1_file($file_path) !== sha1_file($git_hooks_dir . '/' . $hook) ||
                    filesize($file_path) !== filesize($git_hooks_dir . '/' . $hook))
            ) {
                $needs_update = true;
            }
            $hook_origin = str_contains($file_path, 'ec-europa/toolkit') ? 'toolkit' : $project_id;
            $rows[] = [
                "$hook ($hook_origin)",
                $is_active ? 'Yes' : 'No',
                $is_enabled ? 'Yes' : 'No',
                $needs_update ? 'Yes' : 'No',
            ];
        }

        $table = new Table($io);
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
     * @return int
     *   The exit code.
     *
     * @command toolkit:hooks-run
     */
    // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassAfterLastUsed
    public function hooksRun(ConsoleIO $io, string $hook, $arg1 = '', $arg2 = '', $arg3 = '')
    {
        if (empty($hook)) {
            $io->say('Please provide a hook to run.');
            return ResultData::EXITCODE_ERROR;
        }

        // Make sure the hook is enabled.
        $enabled_hooks = $this->getHookFiles($this->getWorkingDir() . '/.git/hooks');
        if (!isset($enabled_hooks[$hook])) {
            $io->say("The hook '$hook' does not exist or is not enabled.");
            return ResultData::EXITCODE_ERROR;
        }

        $method = $this->convertHookToMethod($hook);

        // Check if the method exists in other classes that are instance
        // of this. The first to be found is used.
        foreach (get_declared_classes() as $class) {
            if (get_parent_class($class) === self::class && method_exists($class, $method)) {
                return (new $class())->$method();
            }
        }

        if (!method_exists($this, $method)) {
            $io->say("The hook '$hook' does not have the corresponding method '$method'.");
            return ResultData::EXITCODE_ERROR;
        }

        return $this->$method();
    }

    /**
     * Hook: Executes the PHPcs against the modified files.
     */
    public function runPreCommit()
    {
        $phpcs = $this->getBin('phpcs');
        $config_file = $this->getConfig()->get('toolkit.test.phpcs.config');

        // Get the modified files, returns a list with a file per line.
        $diff = $this->taskExec('git')
            ->arg('diff')
            ->options([
                'diff-filter' => 'M',
                'name-only' => null,
                'cached' => null,
            ], '=')
            ->silent(true)->run()->getOutputData();
        // The output is empty if there's nothing to be committed.
        if (empty($diff)) {
            return ResultData::EXITCODE_OK;
        }
        $diff = explode(PHP_EOL, $diff);

        // If a config file exists, PHPcs will automatically load it and use
        // the files in there, the workaround here is to regenerate the config
        // file with the modified files.
        if (file_exists($config_file)) {
            $dom = new DOMDocument();
            $dom->load($config_file);
            $root = $dom->firstChild;
            // Backup the config file and replace the files in it.
            rename($config_file, $config_file . '.backup');

            // Remove files.
            $files = $root->getElementsByTagName('file');
            $len = $files->length;
            for ($i = 0; $i < $len; $i++) {
                $file = $files->item(0);
                $file->parentNode->removeChild($file);
            }

            // Add diff files.
            foreach ($diff as $item) {
                $dom->firstChild->appendChild($dom->createElement('file', $item));
            }

            $this->taskWriteToFile($config_file)->text($dom->saveXML())->run();
        } else {
            // Setup the ruleset with the files to check.
            $files_setup = implode(',', $diff);
            $this->taskExec($this->getBin('run'))
                ->arg('toolkit:setup-phpcs')
                ->rawArg("-Dtoolkit.test.phpcs.files=$files_setup")
                ->run();
        }
        // Execute the command.
        $result = $this->taskExec($phpcs)->option('standard', $config_file, '=')->run();

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
    public function runPrepareCommitMsg()
    {
        $io = new ConsoleIO($this->input(), $this->output());
        $args = $this->input()->getArguments();
        // The arg1 is the file that contains the commit message.
        // NOTE: Do not use the arg2 because it is not updated when new
        // message is typed to the commit.
        if (!file_exists($args['arg1'])) {
            $io->error("File '{$args['arg1']}' not found.");
            return ResultData::EXITCODE_ERROR;
        }
        $message = trim(file_get_contents($args['arg1']));
        $config = $this->getConfig()->get('toolkit.hooks');
        $conditions = $config['prepare-commit-msg']['conditions'];
        $problems = [];
        foreach ($conditions as $condition) {
            preg_match($condition['regex'], $message, $matches);
            if (empty($matches)) {
                $problems[] = $condition['message'];
            }
        }
        if (!empty($problems)) {
            $io->error(array_merge(['The commit message validation failed with the following problems:'], $problems));
            if (!empty($config['prepare-commit-msg']['example'])) {
                $io->say("Example: {$config['prepare-commit-msg']['example']}");
            }
            return ResultData::EXITCODE_ERROR;
        }

        return ResultData::EXITCODE_OK;
    }

    /**
     * Hook: Executes the pre-push commands.
     */
    public function runPrePush()
    {
        $exit = 0;
        $runner_bin = $this->getBin('run');
        $commands = $this->getConfig()->get('toolkit.hooks.pre-push.commands');
        foreach ($commands as $test) {
            $result = $this->taskExec($runner_bin)->arg($test)->run();
            $exit += $result->getExitCode();
        }
        return $exit;
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
        $toolkit_hooks = $this->getHookFiles(Toolkit::getToolkitRoot() . "/$dir");
        $project_hooks = $this->getHookFiles($this->getWorkingDir() . "/$dir");
        return array_merge($toolkit_hooks, $project_hooks);
    }

    /**
     * Return the hooks present in given directory.
     *
     * @param string $directory
     *   The directory to check for hooks.
     *
     * @return array
     *   An array keyed by hook name and path as value, false if do not exist.
     */
    private function getHookFiles(string $directory)
    {
        if (empty($directory)) {
            return [];
        }
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
     * @param string $hook
     *   The hook name to convert.
     *
     * @return false|string
     *   The converted hook name, i.e: the pre-push becomes
     *   runPrePush, FALSE if empty hook provided.
     */
    private function convertHookToMethod(string $hook)
    {
        if (empty($hook)) {
            return false;
        }
        $method = 'run';
        $exploded = explode('-', $hook);
        foreach ($exploded as $item) {
            $method .= ucfirst($item);
        }
        return $method;
    }

}
