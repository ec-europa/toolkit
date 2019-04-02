<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Consolidation\Config\Loader\ConfigProcessor;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use Robo\Robo;

/**
 * Default configuration loader.
 *
 * This class does not expose any commands as it is used to load default
 * Toolkit configuration using Annotated Commands hooks.
 *
 * @see https://github.com/consolidation/annotated-command#command-event-hook
 */
class LoadDefaultConfigCommands extends AbstractCommands {

  /**
   * Load default Toolkit configuration.
   *
   * The Task Runner does not allow to provide default configuration for
   * commands. In this hook we load Toolkit default configuration and re-apply
   * it to the Task Runner one.
   *
   * @hook pre-command-event *
   */
  public function loadDefaultConfig() {
    // Load Toolkit default configuration.
    $path = __DIR__ . '/../../../config/default.yml';
    $default_config = Robo::createConfiguration([$path]);

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
