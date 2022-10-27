<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Drupal commands to setup and install a Drupal 8 site.
 */
class DrupalCommands extends AbstractCommands
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
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/drupal.yml';
    }

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
     * runner.yml.dist/runner.yml
     * as shown below:
     *
     * > drupal:
     * >   additional_settings: |
     * >   $config['cas.settings']['server']['hostname'] = getenv('CAS_HOSTNAME'),
     * >   $config['cas.settings']['server']['port'] = getenv('CAS_PORT');
     *
     * You can specify additional service parameters in your local
     * runner.yml.dist/runner.yml
     * as shown below:
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
     */
    public function drupalSettingsSetup(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'sites-subdir' => InputOption::VALUE_REQUIRED,
        'force' => false,
        'skip-permissions-setup' => false,
    ])
    {
        // Get default.settings.php and settings.php paths.
        $settings_default_path = $options['root'] . '/sites/default/default.settings.php';
        $settings_path = $options['root'] . '/sites/' . $options['sites-subdir'] . '/settings.php';
        $tasks = [];

        // Copy default.settings.php on settings.php, if the latter does not exist.
        if (!file_exists($settings_path)) {
            $tasks[] = $this->taskFilesystemStack()
                ->copy($settings_default_path, $settings_path);
        }

        // Remove Toolkit settings block, if any.
        $tasks[] = $this->taskReplaceInFile($settings_path)
            ->regex($this->getSettingsBlockRegex())
            ->to('');

        // Append Toolkit settings block to settings.php file.
        $tasks[] = $this->taskWriteToFile($settings_path)
            ->append()
            ->text($this->getToolkitSettingsBlock());

        // Set necessary permissions on the default folder.
        if (!$options['skip-permissions-setup']) {
            $tasks[] = $this->drupalPermissionsSetup($options);
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Setup Drupal permissions.
     *
     * This command will set the necessary permissions on the default folder.
     *
     * @param array $options
     *
     * @command drupal:permissions-setup
     *
     * @option root                     Drupal root.
     * @option sites-subdir             Drupal site subdirectory.
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function drupalPermissionsSetup(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'sites-subdir' => InputOption::VALUE_REQUIRED,
    ])
    {
        $subdirPath = $options['root'] . '/sites/' . $options['sites-subdir'];

        $tasks = [
            // Note that the chmod() method takes decimal values.
            $this->taskFilesystemStack()->chmod($subdirPath, octdec('775'), 0000, true),
        ];

        if (file_exists($subdirPath . '/settings.php')) {
            // Note that the chmod() method takes decimal values.
            $tasks[] = $this->taskFilesystemStack()->chmod($subdirPath . '/settings.php', octdec('664'));
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Validate command drupal:site-install.
     *
     * @param CommandData $commandData
     *
     * @hook validate drupal:site-install
     *
     * @throws \Exception
     *   Thrown when the settings file or its containing folder does not exist
     *   or is not writeable.
     */
    public function drupalSiteInstallValidate(CommandData $commandData)
    {
        $input = $commandData->input();

        // Validate if permissions will be set up.
        if (!$input->hasOption('skip-permissions-setup') || !$input->getOption('skip-permissions-setup')) {
            return;
        }

        $siteDirectory = implode('/', [
            getcwd(),
            $input->getOption('root'),
            'sites',
            $input->getOption('sites-subdir'),
        ]);

        // Check if required files/folders exist and they are writable.
        $requiredFiles = [$siteDirectory, $siteDirectory . '/settings.php'];
        foreach ($requiredFiles as $requiredFile) {
            if (file_exists($requiredFile) && !is_writable($requiredFile)) {
                $message = 'The file/folder %s must be writable for installation to continue.';
                throw new \Exception(sprintf($message, $requiredFile));
            }
        }
    }

    /**
     * Write Drush configuration file at "${drupal.root}/drush/drush.yml".
     *
     * Configuration file contents can be customized by editing "drupal.drush"
     * values in your local runner.yml.dist/runner.yml, as shown below:
     *
     * > drupal:
     * >   drush:
     * >     options:
     * >       ignored-directories: "${drupal.root}"
     * >       uri: "${drupal.base_url}"
     *
     * @param array $options
     *
     * @command drupal:drush-setup
     *
     * @option root         Drupal root.
     * @option config-dir   Directory where to store Drush 9 configuration file.
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function drupalDrushSetup(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'config-dir' => InputOption::VALUE_REQUIRED,
    ])
    {
        $config = $this->getConfig();
        $yaml = Yaml::dump($config->get('drupal.drush'));
        return $this->collectionBuilder()->addTask(
            $this->taskWriteToFile($options['config-dir'] . '/drush.yml')->text($yaml)
        );
    }

    /**
     * Install target site.
     *
     * This command will install a target Drupal site using configuration values
     * provided in local runner.yml.dist/runner.yml files.
     *
     * @param array $options
     *
     * @command drupal:site-install
     *
     * @option root                   Drupal root.
     * @option site-name              Site name.
     * @option site-mail              Site mail.
     * @option site-profile           Installation profile
     * @option site-update            Whereas to enable the update module or not.
     * @option site-locale            Default site locale.
     * @option account-name           Admin account name.
     * @option account-password       Admin account password.
     * @option account-mail           Admin email.
     * @option database-scheme        Database scheme.
     * @option database-host          Database host.
     * @option database-port          Database port.
     * @option database-name          Database name.
     * @option database-user          Database username.
     * @option database-password      Database password.
     * @option sites-subdir           Sites sub-directory.
     * @option existing-config        Whether existing config should be imported during installation.
     * @option skip-permissions-setup Whether to skip making the settings file and folder writable during installation.
     *
     * @aliases drupal:si,dsi
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function drupalSiteInstall(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'base-url' => InputOption::VALUE_REQUIRED,
        'site-name' => InputOption::VALUE_REQUIRED,
        'site-mail' => InputOption::VALUE_REQUIRED,
        'site-profile' => InputOption::VALUE_REQUIRED,
        'site-update' => InputOption::VALUE_REQUIRED,
        'site-locale' => InputOption::VALUE_REQUIRED,
        'account-name' => InputOption::VALUE_REQUIRED,
        'account-password' => InputOption::VALUE_REQUIRED,
        'account-mail' => InputOption::VALUE_REQUIRED,
        'database-type' => InputOption::VALUE_REQUIRED,
        'database-scheme' => InputOption::VALUE_REQUIRED,
        'database-user' => InputOption::VALUE_REQUIRED,
        'database-password' => InputOption::VALUE_REQUIRED,
        'database-host' => InputOption::VALUE_REQUIRED,
        'database-port' => InputOption::VALUE_REQUIRED,
        'database-name' => InputOption::VALUE_REQUIRED,
        'sites-subdir' => InputOption::VALUE_REQUIRED,
        'config-dir' => InputOption::VALUE_REQUIRED,
        'existing-config' => false,
        'skip-permissions-setup' => false,
    ])
    {
        $exec_args = [
            'site:install',
            $options['site-profile'],
        ];
        $exec_options = [
            'root' => getcwd() . '/' . $options['root'] . '/',
            'site-name' => $options['site-name'],
            'site-mail' => $options['site-mail'],
            'locale' => $options['site-locale'],
            'account-mail' => $options['account-mail'],
            'account-name' => $options['account-name'],
            'account-pass' => $options['account-password'],
            'sites-subdir' => $options['sites-subdir'],
        ];

        if (!empty($db_url = $this->getConfig()->get('drupal.site.generate_db_url'))) {
            $db_array = [
                'scheme' => $options['database-scheme'],
                'user' => $options['database-user'],
                'pass' => $options['database-password'],
                'host' => $options['database-host'],
                'port' => $options['database-port'],
                'path' => $options['database-name'],
            ];
            $exec_options['db-url'] = http_build_url($db_url, $db_array);
        }

        if ($options['existing-config']) {
            $exec_options['existing-config'] = null;
        }

        $tasks = [
            $this->drupalSitePreInstall($options),
        ];
        if (!$options['skip-permissions-setup']) {
            $tasks[] = $this->drupalPermissionsSetup($options);
        }

        $tasks[] = $this->taskExec($this->getBin('drush'))
            ->args($exec_args)
            ->options($exec_options, '=')
            ->option('-y');

        $tasks[] = $this->drupalSitePostInstall($options);

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Process pre and post install string-only commands by replacing given tokens.
     *
     * @param array $commands
     *   List of commands.
     * @param array $tokens
     *   Replacement key-value tokens.
     */
    protected function processPrePostInstallCommands(array &$commands, array $tokens)
    {
        foreach ($commands as $key => $value) {
            if (is_string($value)) {
                $commands[$key] = str_replace(array_keys($tokens), array_values($tokens), $value);
            }
        }
    }

    /**
     * Run Drupal pre-install commands.
     *
     * Commands have to be listed under the "drupal.pre_install" property in
     * your local runner.yml.dist/runner.yml files, as shown below:
     *
     * > drupal:
     * >   ...
     * >   pre_install:
     * >     - { task: "symlink", from: "../libraries", to: "${drupal.root}/libraries" }
     * >     - { task: "process", source: "behat.yml.dist", destination: "behat.yml" }
     *
     * Pre-install commands are automatically executed before installing the site
     * when running "drupal:site-install".
     *
     * @command drupal:site-pre-install
     *
     * @option root
     *   The Drupal root. All occurrences of "!root" in the pre-install
     *   string-only commands will be substituted with this value.
     *
     * @return \Robo\Contract\TaskInterface
     */
    public function drupalSitePreInstall(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = $this->getConfig()->get('drupal.pre_install', []);
        $this->processPrePostInstallCommands($tasks, [
            '!root' => $options['root'],
        ]);
        return $this->taskExecute($tasks);
    }

    /**
     * Run Drupal post-install commands.
     *
     * Commands have to be listed under the "drupal.post_install" property in
     * your local runner.yml.dist/runner.yml files, as shown below:
     *
     * > drupal:
     * >   ...
     * >   post_install:
     * >     - "./vendor/bin/drush en views -y"
     * >     - { task: "process", source: "behat.yml.dist", destination: "behat.yml" }
     *
     * Post-install commands are automatically executed after installing the site
     * when running "drupal:site-install".
     *
     * @command drupal:site-post-install
     *
     * @option root
     *   The Drupal root. All occurrences of "!root" in the post-install
     *   string-only commands will be substituted with this value.
     *
     * @return \Robo\Contract\TaskInterface
     */
    public function drupalSitePostInstall(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = $this->getConfig()->get('drupal.post_install', []);
        $this->processPrePostInstallCommands($tasks, [
            '!root' => $options['root'],
        ]);
        return $this->taskExecute($tasks);
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
     * @return string
     *   Database configuration to be attached to Drupal settings.php.
     */
    protected function getToolkitSettingsBlock()
    {
        $additionalSettings = $this->getConfig()->get('drupal.additional_settings', '');
        $additionalSettings = trim($additionalSettings);
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
  'namespace' => getenv('DRUPAL_DATABASE_DRIVER') !== FALSE ? 'Drupal\\\\Core\\\\Database\\\\Driver\\\\' . getenv('DRUPAL_DATABASE_DRIVER') : 'Drupal\\\\Core\\\\Database\\\\Driver\\\\mysql',
  'driver' => getenv('DRUPAL_DATABASE_DRIVER') !== FALSE ? getenv('DRUPAL_DATABASE_DRIVER') : 'mysql',
);

// Location of the site configuration files, relative to the site root.
\$settings['config_sync_directory'] = '../config/sync';

\$settings['hash_salt'] = getenv('DRUPAL_HASH_SALT') !== FALSE ? getenv('DRUPAL_HASH_SALT') : '{$hashSalt}';
\$settings['file_private_path'] =  getenv('DRUPAL_PRIVATE_FILE_SYSTEM') !== FALSE ? getenv('DRUPAL_PRIVATE_FILE_SYSTEM') : 'sites/default/private_files';
\$settings['file_temp_path'] = getenv('DRUPAL_FILE_TEMP_PATH') !== FALSE ? getenv('DRUPAL_FILE_TEMP_PATH') : '/tmp';

{$additionalSettings}

// Load environment development override configuration, if available.
// Keep this code block at the end of this file to take full effect.
if (file_exists(\$app_root . '/' . \$site_path . '/settings.override.php')) {
  include \$app_root . '/' . \$site_path . '/settings.override.php';
}

{$this->blockEnd}

EOF;
    }

}
