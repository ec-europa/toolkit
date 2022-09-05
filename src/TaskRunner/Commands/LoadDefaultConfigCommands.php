<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Consolidation\Config\Loader\ConfigProcessor;
use Consolidation\Config\Util\ConfigOverlay;
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
     * Configurations that can be replaced by a project.
     *
     * @var string[]
     */
    private $overrides = [
        'toolkit.build.dist.keep',
        'toolkit.build.dist.keep',
        'toolkit.test.phpcs.standards',
        'toolkit.test.phpcs.ignore_patterns',
        'toolkit.test.phpcs.triggered_by',
        'toolkit.test.phpcs.files',
        'toolkit.test.phpmd.ignore_patterns',
        'toolkit.test.phpmd.triggered_by',
        'toolkit.test.phpmd.files',
        'toolkit.test.lint.yaml.pattern',
        'toolkit.test.lint.yaml.includeexclude',
        'toolkit.test.lint.yaml.exclude',
        'toolkit.test.lint.php.extensions',
        'toolkit.test.lint.php.exclude',
    ];

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

        // Allow some configurations to be overridden. If a given property is
        // defined on a project level it will replace the default values
        // instead of merge.
        $context = $default_config->getContext(ConfigOverlay::DEFAULT_CONTEXT);
        foreach ($this->overrides as $override) {
            if ($value = $config->get($override)) {
                $context->set($override, $value);
            }
        }

        // Re-build configuration.
        $processor = new ConfigProcessor();
        $default_config->addContext(ConfigOverlay::DEFAULT_CONTEXT, $context);
        $processor->add($default_config->export());
        $processor->add($config->export());

        // Import newly built configuration.
        $config->import($processor->export());
    }
}
