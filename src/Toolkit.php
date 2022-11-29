<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit;

use Robo\Robo;

/**
 * Provides default Toolkit class.
 */
final class Toolkit
{
    /**
     * Constant holding the current version.
     */
    public const VERSION = '9.1.1';

    /**
     * Returns the Toolkit root.
     *
     * @return string
     *   The Toolkit root.
     */
    public static function getToolkitRoot(): string
    {
        return realpath(__DIR__ . '/../');
    }

    /**
     * Returns the Project root.
     *
     * @return string
     *   The Project root.
     */
    public static function getProjectRoot(): string
    {
        return realpath(__DIR__ . '/../../../../');
    }

    /**
     * Returns whether is running in CI/CD environment.
     *
     * @return bool
     *   True if running in CI/CD, false otherwise.
     */
    public static function isCiCd(): bool
    {
        return !empty(getenv('CI'));
    }

    /**
     * Returns the ASDA user.
     *
     * @return string
     *   The ASDA user.
     */
    public static function getAsdaUser(): string
    {
        $user = getenv('ASDA_USER');
        return !empty($user) && $user !== '${env.ASDA_USER}' ? $user : '';
    }

    /**
     * Returns the ASDA password.
     *
     * @return string
     *   The ASDA password.
     */
    public static function getAsdaPass(): string
    {
        $pass = getenv('ASDA_PASSWORD');
        return !empty($pass) && $pass !== '${env.ASDA_PASSWORD}' ? $pass : '';
    }

    /**
     * Returns the NEXTCLOUD user.
     *
     * @return string
     *   The NEXTCLOUD user.
     */
    public static function getNextcloudUser(): string
    {
        $user = getenv('NEXTCLOUD_USER');
        return !empty($user) && $user !== '${env.NEXTCLOUD_USER}' ? $user : '';
    }

    /**
     * Returns the NEXTCLOUD password.
     *
     * @return string
     *   The NEXTCLOUD password.
     */
    public static function getNextcloudPass(): string
    {
        $pass = getenv('NEXTCLOUD_PASS');
        return !empty($pass) && $pass !== '${env.NEXTCLOUD_PASS}' ? $pass : '';
    }

    /**
     * Remove un-existing folders from given array.
     *
     * @param array $files
     *   The folders to check.
     */
    public static function filterFolders(array &$files)
    {
        $files = array_filter($files, function ($folder) {
            return file_exists($folder);
        });
    }

    /**
     * If given content is a string, it will be exploded by given separator.
     *
     * @param mixed $data
     *   If the data is a string it will be exploded by comma.
     * @param string $sep
     *   The separator to explode the string.
     */
    public static function ensureArray(mixed &$data, string $sep = ',')
    {
        if (is_string($data)) {
            $data = array_map('trim', explode($sep, $data));
        }
    }

    /**
     * Return the current Robo version.
     *
     * @return string
     *   A string with the Robo version, empty string if could not find the version,
     */
    public static function getRoboVersion()
    {
        $version = '';
        if (defined('Robo::VERSION')) {
            $version = constant('Robo::VERSION');
        } elseif (method_exists(Robo::class, 'version')) {
            $version = Robo::version();
        }
        return $version;
    }

}
