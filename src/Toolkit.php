<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit;

/**
 * Provides default Toolkit class.
 */
final class Toolkit
{
    /**
     * Constant holding the current version.
     */
    public const VERSION = '9.1.0';

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
     * Returns the QA base url.
     *
     * @return string
     *   The base url.
     */
    public static function getQaWebsiteUrl(): string
    {
        $url = getenv('QA_WEBSITE_URL');
        return !empty($url) ? $url : 'https://webgate.ec.europa.eu/fpfis/qa';
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
}
