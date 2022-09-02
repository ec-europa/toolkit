<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Consolidation\Config\Config;
use Consolidation\Config\Loader\ConfigProcessor;
use Consolidation\Config\Loader\YamlConfigLoader;
use Consolidation\Config\Util\ConfigOverlay;
//use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use Robo\Common\ConfigAwareTrait;
use Robo\Common\IO;
use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\LoadAllTasks;
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
     * @hook pre-command-event *
     */
    public function loadDefaultConfig(ConsoleCommandEvent $event)
    {
        // Load Toolkit default configuration.
        $default_config = Robo::createConfiguration([$this->getDefaultConfigurationFile()]);

        // Get Task Runner configuration.
        $config = $this->getConfig();

        // Re-build configuration.
        $processor = new ConfigProcessor();
        $merged = $this->overrideConfigurations($default_config->export(), $config->export());
        $processor->add($merged);

        // Import newly built configuration.
        $config->import($processor->export());
    }

    /**
     * Override and merge second $arr2 into the $arr1.
     *
     * @param $arr1
     *   The base array.
     * @param $arr2
     *   The array to be added to the $arr1.
     *
     * @return array
     *   Returns the $arr1 containning the $arr2.
     */
    private function overrideConfigurations($arr1, $arr2)
    {
        foreach ($arr2 as $search => $replace) {
            if (!isset($arr1[$search])) {
                $arr1[$search] = $replace;
                continue;
            }
            if (is_array($replace)) {
                foreach ($replace as $key => $item) {
                    if (is_array($item)) {
                        foreach ($item as $j => $value) {
                            if (is_array($value)) {
                                foreach ($value as $value_key => $val) {
                                    $arr1[$search][$key][$j][$value_key] = $val;
                                }
                            } else {
                                $arr1[$search][$key][$j] = $value;
                            }
                        }
                    } else {
                        $arr1[$search][$key] = $item;
                    }
                }
            }
        }

        return $arr1;
    }
}
