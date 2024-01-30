<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Callbacks;

use EcEuropa\Toolkit\TaskRunner\Commands\ToolCommands;
use Symfony\Component\Yaml\Yaml;

/**
 * Class containing configuration check callbacks.
 */
class ConfigurationCallbacks
{

    /**
     * If grumphp package is not present in a project, then grumphp config file must not be present.
     */
    public static function validateGrumPhp(): bool
    {
        // Stop if the config file do not exist.
        if (!file_exists('grumphp.yml.dist')) {
            return true;
        }
        return !empty(ToolCommands::getPackagePropertyFromComposer('phpro/grumphp'));
    }

    /**
     * If project is using phpstan/extension-installer then should not manually include extensions.
     */
    public static function validatePhpStan(): bool
    {
        $file = 'phpstan.neon';
        // Stop if the config file do not exist or the package is not installed.
        if (!file_exists($file) || !ToolCommands::getPackagePropertyFromComposer('phpstan/extension-installer')) {
            return true;
        }
        // Load the config file and check for the includes.
        $config = Yaml::parseFile($file);
        return empty($config['includes']);
    }

}
