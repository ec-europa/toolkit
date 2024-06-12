<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit;

use Composer\InstalledVersions;

/**
 * Provides default Toolkit class.
 */
final class Toolkit
{
    /**
     * Constant holding the current version.
     */
    public const VERSION = '10.12.4';

    /**
     * The Toolkit repository.
     */
    public const REPOSITORY = 'ec-europa/toolkit';

    /**
     * The Toolkit composer plugin repository.
     */
    public const PLUGIN = 'ec-europa/toolkit-composer-plugin';

    /**
     * Returns the Toolkit root.
     *
     * @return string
     *   The Toolkit root.
     */
    public static function getToolkitRoot(): string
    {
        return realpath(InstalledVersions::getInstallPath(self::REPOSITORY));
    }

    /**
     * Returns the Project root.
     *
     * @return string
     *   The Project root.
     */
    public static function getProjectRoot(): string
    {
        return realpath(InstalledVersions::getRootPackage()['install_path']);
    }

    /**
     * Returns whether is running in CI/CD environment.
     *
     * @return bool
     *   True if running in CI/CD, false otherwise.
     */
    public static function isCiCd(): bool
    {
        $ci = getenv('CI');
        return !empty($ci) && ($ci === 'true' || $ci === 'drone');
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
        $files = array_values(array_filter($files, function ($folder) {
            return file_exists($folder);
        }));
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

}
