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
        $grumpPackages = ['phpro/grumphp', 'phpro/grumphp-shim'];
        // Iterate through packages and check if they are installed.
        foreach ($grumpPackages as $grumpPackage) {
            if (ToolCommands::isPackageInstalled($grumpPackage)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validate when using phpstan/extension-installer the include should not be used, otherwise, include is required.
     */
    public static function validatePhpStan(): bool
    {
        $file = 'phpstan.neon';

        // Skip if the project does not have the config file.
        if (!file_exists($file)) {
            return true;
        }

        $config = Yaml::parseFile($file);
        // If project is using phpstan/extension-installer then should not manually include extensions.
        if (ToolCommands::isPackageInstalled('phpstan/extension-installer')) {
            return empty($config['includes']);
        }

        // If project is not using phpstan/extension-installer then should manually include extensions.
        return !empty($config['includes']);
    }

}
