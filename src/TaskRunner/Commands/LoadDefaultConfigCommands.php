<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Consolidation\Config\Loader\ConfigProcessor;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use Robo\Robo;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * Default configuration loader.
 *
 * This class does not expose any commands as it is used to load default
 * Toolkit configuration using Annotated Commands hooks.
 *
 * @see https://github.com/consolidation/annotated-command#command-event-hook
 */
class LoadDefaultConfigCommands extends AbstractCommands
{
    /**
     * Path to YAML configuration file containing command defaults.
     *
     * Command classes should implement this method.
     *
     * @return string
     *   The path of the default configuration file.
     */
    public function getDefaultConfigurationFile()
    {
        return __DIR__ . '/../../../config/default.yml';
    }

    /**
     * Load default Toolkit configuration.
     *
     * The Task Runner does not allow providing default configuration for
     * commands. In this hook we load Toolkit default configuration and re-apply
     * it to the Task Runner one.
     *
     * @param ConsoleCommandEvent $event
     *   Event of the console command event.
     *
     * @hook pre-command-event
     */
    public function loadDefaultConfig(ConsoleCommandEvent $event)
    {
        // Load Toolkit default configuration.
        $default_config = Robo::createConfiguration([$this->getDefaultConfigurationFile()]);

        // Get Task Runner configuration.
        $config = $this->getConfig();

        // Re-build configuration.
        $processor = new ConfigProcessor();
        $processor->add($default_config->export());
        $processor->add($config->export());

        // Import newly built configuration.
        $config->import($processor->export());
    }
}
