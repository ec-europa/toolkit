<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\Drupal8Commands;
use Symfony\Component\Console\Input\InputOption;

/**
 * Drupal commands to setup and install a Drupal 8 site.
 */
class DrupalCommands extends Drupal8Commands
{

    /**
     * Comment ending the Toolkit settings block.
     *
     * @var string
     */
    protected $blockEnd = '// End Toolkit settings block.';

    /**
     * Comment starting the Toolkit settings block.
     *
     * @var string
     */
    protected $blockStart = '// Start Toolkit settings block.';

    /**
     * Setup Drupal settings.php file in compliance with Toolkit conventions.
     *
     * This command will:
     *
     * - Copy "default.settings.php" to "settings.php", which will be overridden
     *   if existing
     * - Add database and config directory settings using environment variables
     * - Append to "settings.php" an include operation for a
     *   "settings.override.php"
     *   file
     *
     * You can specify additional settings.php portions in your local
     * runner.yml.dist/runner.yml  as shown below:
     *
     * > drupal:
     * >   additional_settings: |
     * >     $config['cas.settings']['server']['hostname'] = getenv('CAS_HOSTNAME');
     * >     $config['cas.settings']['server']['port'] = getenv('CAS_PORT');
     *
     * Sometimes, during development and testing, some projects are using
     * specific settings settings to help development or to mock services during
     * testing. Additional development/testing settings can be added by setting
     * a drupal.additional_dev_settings in your local runner.yml.dist/runner.yml
     * as shown below. Note that the command should be called with the --dev
     * option in order to add the development/testing settings additions:
     *
     * > drupal:
     * >   additional_dev_settings: |
     * >     $settings['extension_discovery_scan_tests'] = TRUE;
     * >     $settings['skip_permissions_hardening'] = TRUE;
     *
     * You can specify additional service parameters in your local
     * runner.yml.dist/runner.yml as shown below:
     *
     * > drupal:
     * >   service_parameters:
     * >     session.storage.options:
     * >       cookie_domain: '.europa.eu'
     *
     * The settings override file name cannot be changed, changing the
     * "drupal.site.settings_override_file" property will have no effect.
     *
     * @param array $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @command drupal:settings-setup
     *
     * @option root                     Drupal root.
     * @option sites-subdir             Drupal site subdirectory.
     * @option force                    Drupal force generation of a new
     *                                  settings.php.
     * @option skip-permissions-setup   Drupal skip permissions setup.
     * @option dev                      Adds development environment specific
     *                                  code in settings.php.
     */
    public function settingsSetup(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'sites-subdir' => InputOption::VALUE_REQUIRED,
        'force' => false,
        'skip-permissions-setup' => false,
        'dev' => false,
    ])
    {
        // Get default.settings.php and settings.php paths.
        $settings_default_path = $options['root'] . '/sites/default/default.settings.php';
        $settings_path = $options['root'] . '/sites/' . $options['sites-subdir'] . '/settings.php';

        $collection = [];

        // Copy default.settings.php on settings.php, if the latter does not exists.
        if (!file_exists($settings_path)) {
            $collection[] = $this->taskFilesystemStack()
                ->copy($settings_default_path, $settings_path);
        }

        // Remove Toolkit settings block, if any.
        $collection[] = $this->taskReplaceInFile($settings_path)
            ->regex($this->getSettingsBlockRegex())
            ->to('');

        // Append Toolkit settings block to settings.php file.
        $collection[] = $this->taskWriteToFile($settings_path)
            ->append()
            ->text($this->getToolkitSettingsBlock($options['dev']));

        // Set necessary permissions on the default folder.
        if (!$options['skip-permissions-setup']) {
            $collection[] = $this->permissionsSetup($options);
        }

        return $this->collectionBuilder()->addTaskList($collection);
    }

    /**
     * Remove settings block from given content.
     *
     * @return string
     *   Content without setting block.
     */
    protected function getSettingsBlockRegex()
    {
        return '/^\n' . preg_quote($this->blockStart, '/') . '.*?' . preg_quote($this->blockEnd, '/') . '\n/sm';
    }

    /**
     * Helper function to update settings.php file.
     *
     * @param bool $dev_mode
     *   If the settings block is build in development mode.
     *
     * @return string
     *   Database configuration to be attached to Drupal settings.php.
     */
    protected function getToolkitSettingsBlock(bool $dev_mode = false): string
    {
        $additionalSettings = $this->getConfig()->get('drupal.additional_settings', '');
        $additionalSettings = trim($additionalSettings);

        $additionalDevSettings = '';
        if ($dev_mode && $this->getConfig()->has('drupal.additional_dev_settings')) {
            $additionalDevSettings = $this->getConfig()->get('drupal.additional_dev_settings', '');
            $additionalDevSettings = "\n\n" . trim($additionalDevSettings);
        }

        $hashSalt = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(random_bytes(55)));

        return <<< EOF

{$this->blockStart}

\$databases['default']['default'] = array (
  'database' => getenv('DRUPAL_DATABASE_NAME'),
  'username' => getenv('DRUPAL_DATABASE_USERNAME'),
  'password' => getenv('DRUPAL_DATABASE_PASSWORD'),
  'prefix' => getenv('DRUPAL_DATABASE_PREFIX'),
  'host' => getenv('DRUPAL_DATABASE_HOST'),
  'port' => getenv('DRUPAL_DATABASE_PORT'),
  'namespace' => 'Drupal\\\\Core\\\\Database\\\\Driver\\\\mysql',
  'driver' => 'mysql',
);

// Location of the site configuration files, relative to the site root.
\$config_directories['sync'] = '../config/sync';

\$settings['hash_salt'] = getenv('DRUPAL_HASH_SALT') !== FALSE ? getenv('DRUPAL_HASH_SALT') : '{$hashSalt}';
\$settings['file_private_path'] =  getenv('DRUPAL_PRIVATE_FILE_SYSTEM') !== FALSE ? getenv('DRUPAL_PRIVATE_FILE_SYSTEM') : 'sites/default/private_files';
\$settings['file_temp_path'] = getenv('DRUPAL_FILE_TEMP_PATH') !== FALSE ? getenv('DRUPAL_FILE_TEMP_PATH') : '/tmp';

{$additionalSettings}{$additionalDevSettings}

// Load environment development override configuration, if available.
// Keep this code block at the end of this file to take full effect.
if (file_exists(\$app_root . '/' . \$site_path . '/settings.override.php')) {
  include \$app_root . '/' . \$site_path . '/settings.override.php';
}

{$this->blockEnd}

EOF;
    }
}
