<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Symfony\ConsoleIO;
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
        $runnerBin = $this->getBin('run');
        $task = $this->taskExec($runnerBin)->arg('drupal:site-install');
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
    public function toolkitInstallClone(ConsoleIO $io, array $options = [
        'dumpfile' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = [];
        $runnerBin = $this->getBin('run');

        $commands = $this->getConfig()->get('toolkit.install.clone.commands', []);
        $beforeCommands = $commands['before'] ?? [];
        $afterCommands = $commands['after'] ?? [];
        unset($commands['before'], $commands['after']);

        // Execute commands configured to run before main tasks.
        if ($beforeCommands) {
            $tasks[] = $this->taskExecute($beforeCommands);
        }

        $tasks[] = $this->taskExec($runnerBin)
            ->arg('toolkit:install-dump')
            ->option('dumpfile', $options['dumpfile'], '=');
        $tasks[] = $this->taskExec($runnerBin)->arg('toolkit:run-deploy');

        // Execute commands configured to run after main tasks.
        if ($afterCommands) {
            $tasks[] = $this->taskExecute($afterCommands);
        }

        if ($commands) {
            $io->warning('Using the config ${toolkit.install.clone.commands} is deprecated, please update to ${toolkit.install.clone.commands.after}.');
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
     *
     * @aliases tk-deploy
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     */
    public function toolkitRunDeploy(array $options = [
        'sequence-file' => InputOption::VALUE_REQUIRED,
        'sequence-key' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = [];
        if (file_exists($options['sequence-file'])) {
            $content = Yaml::parseFile($options['sequence-file']);
            $sequence = $content[$options['sequence-key']] ?? [];
            if (!empty($sequence)) {
                $commands = $sequence['default'] ?? $sequence;
                $this->say("Running custom deploy sequence '{$options['sequence-key']}' from sequence file '{$options['sequence-file']}'.");

                // Append extra commands if requested.
                $env = getenv('FPFIS_ENVIRONMENT');
                if (!empty($env) && !empty($sequence['append'][$env])) {
                    $commands = array_merge($commands, $sequence['append'][$env]);
                }
                foreach ($commands as $command) {
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
        $drushBin = $this->getBin('drush');
        $tasks[] = $this->taskExec($drushBin)->arg('deploy')->rawArg('-y');

        return $this->collectionBuilder()->addTaskList($tasks);
    }

}
