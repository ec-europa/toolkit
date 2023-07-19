<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ToolkitCommands.
 */
class InstallCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/install.yml';
    }

    /**
     * Install a clean website.
     *
     * @param array $options
     *   Command options.
     *
     * @command toolkit:install-clean
     *
     * @option config-file The path to the config file.
     *
     * @aliases tk-iclean
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     */
    public function toolkitInstallClean(array $options = [
        'config-file' => InputOption::VALUE_REQUIRED,
    ])
    {
        $runner_bin = $this->getBin('run');
        $task = $this->taskExec($runner_bin)->arg('drupal:site-install');
        if (!empty($options['config-file']) && file_exists($options['config-file'])) {
            $task->option('existing-config');
        }

        // Build and return task collection.
        return $this->collectionBuilder()->addTask($task);
    }

    /**
     * Install a clone website.
     *
     * @param array $options
     *   Command options.
     *
     * @command toolkit:install-clone
     *
     * @option dumpfile The dump file name.
     *
     * @aliases tk-iclone
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     */
    public function toolkitInstallClone(array $options = [
        'dumpfile' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = [];
        $runner_bin = $this->getBin('run');

        $tasks[] = $this->taskExec($runner_bin)
            ->arg('toolkit:install-dump')
            ->option('dumpfile', $options['dumpfile'], '=');
        $tasks[] = $this->taskExec($runner_bin)->arg('toolkit:run-deploy');

        // Collect and execute list of commands set on local runner.yml.
        if (!empty($commands = $this->getConfig()->get('toolkit.install.clone.commands'))) {
            $tasks[] = $this->taskExecute($commands);
        }

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run deployment sequence.
     *
     * This command will check for a file that holds the deployment sequence. If
     * it is available it will run the commands defined in the yaml file under the
     * selected key. If not we will run a standard set of deployment commands.
     *
     * @param array $options
     *   Command options.
     *
     * @command toolkit:run-deploy
     *
     * @option sequence-file The file that holds the deployment sequence.
     * @option sequence-key  The key under which the commands are defined.
     * @option config-file   The config file that triggers the config import.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     */
    public function toolkitRunDeploy(array $options = [
        'sequence-file' => InputOption::VALUE_REQUIRED,
        'sequence-key' => InputOption::VALUE_REQUIRED,
        'config-file' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = [];

        $has_config = file_exists($options['config-file']);
        $has_sequence = file_exists($options['sequence-file']);

        if ($has_sequence) {
            $content = Yaml::parseFile($options['sequence-file']);
            $sequence = $content[$options['sequence-key']] ?? [];
            if (!empty($sequence)) {
                $sequence = $sequence['default'] ?? $sequence;
                $this->say("Running custom deploy sequence '{$options['sequence-key']}' from sequence file '{$options['sequence-file']}'.");
                foreach ($sequence as $command) {
                    // Only execute strings. Opts.yml also supports append and
                    // default array to append or override the default commands.
                    // @see: https://webgate.ec.europa.eu/fpfis/wikis/display/MULTISITE/NE+Pipelines#NEPipelines-DeploymentOverrides
                    // @see: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-23137
                    if (is_string($command)) {
                        $tasks[] = $this->taskExec($command);
                    }
                }
                return $this->collectionBuilder()->addTaskList($tasks);
            }
            $this->say("Sequence key '{$options['sequence-key']}' does not contain commands, running default set of deployment commands.");
        } else {
            $this->say("Sequence file '{$options['sequence-file']}' does not exist, running default set of deployment commands.");
        }

        // Default deployment sequence.
        $drush_bin = $this->getBin('drush');
        $tasks[] = $this->taskExec($drush_bin)->arg('cache:rebuild');
        $tasks[] = $this->taskExec($drush_bin)->args(['state:set', 'system.maintenance_mode', 1])
            ->option('input-format', 'integer', '=')
            ->rawArg('-y');
        $tasks[] = $this->taskExec($drush_bin)->arg('updatedb')->option('no-post-updates')->rawArg('-y');
        $tasks[] = $this->taskExec($drush_bin)->arg('updatedb')->rawArg('-y');
        if ($has_config) {
            $tasks[] = $this->taskExec($drush_bin)->arg('config:import')->rawArg('-y');
        }
        $tasks[] = $this->taskExec($drush_bin)->args(['state:set', 'system.maintenance_mode', 0])
            ->option('input-format', 'integer', '=')
            ->rawArg('-y');
        $tasks[] = $this->taskExec($drush_bin)->arg('cache:rebuild');

        return $this->collectionBuilder()->addTaskList($tasks);
    }

}
