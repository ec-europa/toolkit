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
        $grump_packages = ['phpro/grumphp', 'phpro/grumphp-shim'];
        // Iterate through packages and check if they are installed.
        foreach ($grump_packages as $grump_package) {
            if (ToolCommands::isPackageInstalled($grump_package)) {
                return true;
            }
        }
        return false;
    }

    /**
     * If project is using phpstan/extension-installer then should not manually include extensions.
     */
    public static function validatePhpStan(): bool
    {
        $file = 'phpstan.neon';
        // Stop if the config file do not exist or the package is not installed.
        if (!file_exists($file) || !ToolCommands::isPackageInstalled('phpstan/extension-installer')) {
            return true;
        }
        // Load the config file and check for the includes.
        $config = Yaml::parseFile($file);
        return empty($config['includes']);
    }

}
