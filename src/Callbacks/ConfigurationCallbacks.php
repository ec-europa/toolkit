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
     * If project is using phpstan/extension-installer then should not manually include extensions.
     */
    public static function validatePhpStan(): bool
    {
        $file = 'phpstan.neon';
        // Load the config file.
        $config = Yaml::parseFile($file);
        // Get if package is installed.
        $package = ToolCommands::isPackageInstalled('phpstan/extension-installer');
        // If packaged installed the phpstan.neon file should not have the includes section.
        if ($package && empty($config['includes'])) {
            return true;
        }
        // If packaged not installed the phpstan.neon file should have the includes section.
        if (!$package && !empty($config['includes'])) {
            return true;
        }
        // Other cases will fail.
        return false;
    }

}
