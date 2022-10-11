<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Robo;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides commands to build a site for development and a production artifact.
 */
class BuildCommands extends AbstractCommands
{
    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/build.yml';
    }

    /**
     * Build the distribution package.
     *
     * This will create the distribution package intended to be deployed.
     * The folder structure will match the following:
     *
     * - ./dist
     * - ./dist/composer.json
     * - ./dist/composer.lock
     * - ./dist/manifest.json
     * - ./dist/config
     * - ./dist/vendor
     * - ./dist/web
     * - ./dist/web/VERSION.txt
     *
     * @param array $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @command toolkit:build-dist
     *
     * @aliases tk-bdist
     *
     * @option root      Drupal root.
     * @option dist-root Distribution package root.
     * @option keep      Comma separated list of files and folders to keep.
     * @option tag       (deprecated) Version tag for manifest.
     * @option sha       (deprecated) Commit hash for manifest.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function buildDist(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'dist-root' => InputOption::VALUE_REQUIRED,
        'keep' => InputOption::VALUE_REQUIRED,
        'tag' => InputOption::VALUE_OPTIONAL,
        'sha' => InputOption::VALUE_OPTIONAL,
        'remove' => InputOption::VALUE_REQUIRED,
    ])
    {
        if ($options['tag']) {
            @trigger_error('Passing the --tag option is deprecated in ec-europa/toolkit:4.1.0 and is removed from ec-europa/toolkit:5.0.0. The tag is automatically computed.', E_USER_DEPRECATED);
        }
        if ($options['sha']) {
            @trigger_error('Passing the --sha option is deprecated in ec-europa/toolkit:4.1.0 and is removed from ec-europa/toolkit:5.0.0. The commit SHA is automatically computed.', E_USER_DEPRECATED);
        }

        $tasks = [];

        // Delete and (re)create the dist folder.
        $tasks[] = $this->taskFilesystemStack()
            ->remove($options['dist-root'])
            ->mkdir($options['dist-root']);

        // Copy all (tracked) files to the dist folder.
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec('git archive HEAD | tar -x -C ' . $options['dist-root']);

        // Run production-friendly "composer install" packages.
        $tasks[] = $this->taskComposerInstall('composer')
            ->env('COMPOSER_MIRROR_PATH_REPOS', 1)
            ->workingDir($options['dist-root'])
            ->optimizeAutoloader()
            ->noDev();

        // Setup the site.
        $runner_bin = $this->getBin('run');
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec($runner_bin . ' drupal:permissions-setup --root=' . $options['dist-root'] . '/' . $options['root'])
            ->exec($runner_bin . ' drupal:settings-setup --root=' . $options['dist-root'] . '/' . $options['root']);

        // Clean up non-required files.
        $keep = '! -name "' . $options['dist-root'] . '" ! -name "' . implode('" ! -name "', explode(',', $options['keep'])) . '"';
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec("find {$options['dist-root']} -maxdepth 1 $keep -exec rm -rf {} +");

        // Prepare sha and tag variables.
        $tag = $options['tag'] ?? $this->getGitTag();
        $hash = $options['sha'] ?? $this->getGitCommitHash();

        // Write manifest.json and VERSION.txt files.
        $drupal_profile = '';
        $config = $this->getConfig();
        $config_file = $config->get('toolkit.clean.config_file');
        if (file_exists($config_file) && ($yml = Yaml::parseFile($config_file))) {
            if (!empty($yml['profile'])) {
                $drupal_profile = $yml['profile'];
            }
        }
        $tasks[] = $this->taskWriteToFile($options['dist-root'] . '/manifest.json')
            ->text(json_encode([
                'version' => $tag,
                'sha' => $hash,
                'environment' => ToolCommands::getDeploymentEnvironment(),
                'project_id' => $config->get('toolkit.project_id'),
                'drupal_version' => ToolCommands::getPackagePropertyFromComposer('drupal/core'),
                'drupal_profile' => $drupal_profile,
                'php_version' => phpversion(),
                'toolkit_version' => ToolCommands::getPackagePropertyFromComposer('ec-europa/toolkit'),
                'date' => date('Y-m-d H:i:s'),
            ]));
        $tasks[] = $this->taskWriteToFile($options['dist-root'] . '/' . $options['root'] . '/VERSION.txt')
            ->text($tag);

        // Copy and process drush.yml file.
        if (file_exists('resources/Drush/drush.yml.dist')) {
            $tasks[] = $this->taskFilesystemStack()
                ->copy('resources/Drush/drush.yml.dist', $options['dist-root'] . '/web/sites/all/drush/drush.yml');
        }

        // Collect and execute list of commands set on local runner.yml.
        $commands = $this->getConfig()->get('toolkit.build.dist.commands');
        if (!empty($commands)) {
            $tasks[] = $this->taskExecute($commands);
        }

        // Remove 'unwanted' files from distribution.
        $remove = '-name "' . implode('" -o -name "', explode(',', $options['remove'])) . '"';
        $tasks[] = $this->taskExecStack()
            ->exec("find dist -maxdepth 3 -type f \( $remove \) -exec rm -rf {} +");

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Build site for local development.
     *
     * @param array $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @command toolkit:build-dev
     *
     * @aliases tk-bdev
     *
     * @option root Drupal root.
     */
    public function buildDev(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = [];
        $root = $options['root'];

        // Run site setup.
        $runner_bin = $this->getBin('run');
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec("$runner_bin toolkit:install-dependencies")
            ->exec("$runner_bin drupal:settings-setup --root=$root");

        // Double check presence of required folders.
        $folders = [
            'public_folder' => $root . '/sites/default/files',
            'private_folder' => getenv('DRUPAL_PRIVATE_FILE_SYSTEM') !== false ? $root . '/' . getenv('DRUPAL_PRIVATE_FILE_SYSTEM') : $root . '/sites/default/private_files',
            'temp_folder' => getenv('DRUPAL_FILE_TEMP_PATH') !== false ? getenv('DRUPAL_FILE_TEMP_PATH') : '/tmp',
        ];

        foreach ($folders as $folder) {
            if (!is_dir($folder)) {
                // Create folder and set permissions.
                // Permissions for files folders taken from:
                // https://www.drupal.org/node/244924#linux-servers
                $tasks[] = $this->taskExecStack()
                    ->stopOnFail()
                    ->exec("mkdir -p $folder")
                    ->exec("chmod ug=rwx,o= $folder");
            }
        }

        if (file_exists('resources/Drush/drush.yml.dist')) {
            $tasks[] = $this->taskFilesystemStack()
                ->copy('resources/Drush/drush.yml.dist', $root . '/sites/all/drush/drush.yml');
        }

        // Collect and execute list of commands set on local runner.yml.
        $commands = $this->getConfig()->get('toolkit.build.dev.commands');
        if (!empty($commands)) {
            $tasks[] = $this->taskExecute($commands);
        }

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Build site for local development from scratch with a clean git.
     *
     * @param array $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @command toolkit:build-dev-reset
     *
     * @option root Drupal root.
     * @option yes  Skip the question.
     */
    public function buildDevReset(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'yes' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $tasks = [];
        $answer = true;
        $question = 'Are you sure you want to proceed? This action cleans up your git repository of any tracked AND untracked files AND folders!';
        if (!$options['yes']) {
            $answer = $this->confirm($question, false);
        }
        if ($answer) {
            // Clean git.
            $tasks[] = $this->taskGitStack()
                ->stopOnFail()
                ->exec('clean -fdx --exclude=vendor/ec-europa/toolkit');
            // Run composer install.
            $tasks[] = $this->taskComposerInstall('composer');
            // Run toolkit:build-dev.
            $tasks[] = $this->taskExecStack()
                ->stopOnFail()
                ->exec($this->getBin('run') . ' toolkit:build-dev --root=' . $options['root']);
        }

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Returns the current Git tag.
     *
     * @return string
     *   Current Git tag.
     */
    protected function getGitTag(): string
    {
        return trim(Robo::getContainer()->get('repository')->run('describe', ['--tags']));
    }

    /**
     * Returns the current Git commit hash.
     *
     * @return string
     *   Current Git hash.
     */
    protected function getGitCommitHash(): string
    {
        return Robo::getContainer()->get('repository')->getHead()->getCommitHash();
    }

    /**
     * Build theme assets (Css and Js).
     *
     * @param array $options
     *   Additional options for the command.
     *
     * @command toolkit:build-assets
     *
     * @option default-theme theme where to build assets.
     * @option validate or validate=fix to check or fix scss files.
     *
     * @aliases tba, tk-assets
     *
     * @return \Robo\Result|int
     *   The collection builder.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function buildAssets(array $options = [
        'default-theme' => InputOption::VALUE_REQUIRED,
        'build-npm-packages' => InputOption::VALUE_OPTIONAL,
        'build-npm-mode' => InputOption::VALUE_OPTIONAL,
        'validate' => InputOption::VALUE_OPTIONAL,
        'theme-task-runner' => InputOption::VALUE_OPTIONAL,
    ])
    {
        if (!empty($options['default-theme'])) {
            // No parameter sent, check for configuration.
            if (file_exists('config/sync/system.theme.yml')) {
                $parseSystemTheme = Yaml::parseFile('config/sync/system.theme.yml');
                $options['default-theme'] = $parseSystemTheme['default'];
            }
        }

        // No theme available.
        if (empty($options['default-theme'])) {
            $this->say("The default-theme couldn't be found in the project. Skipping build.");
            return 0;
        }

        // Search Theme.
        $finder = new Finder();
        $finder->directories()
            ->in($this->getConfig()->get('toolkit.build.custom-code-folder'))
            ->name($options['default-theme']);

        if ($finder->hasResults()) {
            foreach ($finder as $directory) {
                $theme_dir = $directory->getRealPath();
            }

            // Build task collection.
            $collection = $this->collectionBuilder();

            // Option to process validation test only.
            if (in_array($options['validate'], ['check', 'fix'])) {
                $fix = $options['validate'] === 'fix' ? '--fix' : '';
                $collection->taskExecStack()
                    ->exec('npm i -D stylelint stylelint-config-standard stylelint-config-sass-guidelines')
                    ->exec('npx stylelint ' . $fix . ' "' . $theme_dir . '/**/*.{css,scss,sass}" --config ./vendor/ec-europa/toolkit/config/stylelint/.stylelintrc.json')
                    ->stopOnFail();
                // Run and return task collection.
                return $collection->run();
            } else {
                // Build task collection.
                $collection = $this->collectionBuilder();

                if ($options['theme-task-runner'] == 'gulp') {
                    $taskRunnerConfigFile = 'gulpfile.js';
                    $this->io()->warning("'Gulp' is being deprecated - use 'Grunt' instead!");
                } elseif ($options['theme-task-runner'] == 'grunt') {
                    $taskRunnerConfigFile = 'Gruntfile.js';
                    $collection = $this->collectionBuilder();
                    $collection->taskExecStack()
                        ->dir($theme_dir)
                        ->exec('sudo apt-get update')
                        ->exec('sudo apt-get install ruby-sass -y')
                        ->stopOnFail();
                } else {
                    $themeTaskRunner = $options['theme-task-runner'];
                    $this->say("$themeTaskRunner is not a supported 'theme-task-runner'. The supported plugins are 'gulp' and 'grunt' (Recommended).");
                    return;
                }

                // Check if 'theme-task-runner' file exists.
                // Create a new one from source if doesn't exist.
                $files = scandir($theme_dir);
                if (!in_array($taskRunnerConfigFile, $files)) {
                    $dir = Toolkit::getToolkitRoot() . '/resources/assets';
                    $collection->taskExecStack()
                        ->exec("cp $dir/$taskRunnerConfigFile $theme_dir/$taskRunnerConfigFile")
                        ->stopOnFail();
                }

                $collection->taskExecStack()
                    ->dir($theme_dir)
                    ->exec('npm init -y --scope')
                    ->exec('npm install ' . $options['build-npm-packages'] . ' --save-dev')
                    ->exec('./node_modules/.bin/' . $options['theme-task-runner'])
                    ->stopOnFail();

                // Run and return task collection.
                return $collection->run();
            }
        } else {
            $this->say("The theme " . $options['default-theme'] . "  couldn't be found on the lib/ folder.");
            return 0;
        }
    }
}
