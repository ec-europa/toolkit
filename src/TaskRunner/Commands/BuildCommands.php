<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use Robo\Robo;
use Symfony\Component\Console\Input\InputOption;

/**
 * Provides commands to build a site for development and a production artifact.
 */
class BuildCommands extends AbstractCommands
{

    use TaskRunnerTasks\CollectionFactory\loadTasks;

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return __DIR__ . '/../../../config/commands/build.yml';
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
     * - ./dist/web
     * - ./dist/vendor
     * - ./dist/config
     *
     * @param array $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @command toolkit:build-dist
     *
     * @option root      Drupal root.
     * @option dist-root Distribution package root.
     * @option keep      Comma separated list of files and folders to keep.
     * @option tag       (deprecated) Version tag for manifest.
     * @option sha       (deprecated) Commit hash for manifest.
     */
    public function buildDist(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'dist-root' => InputOption::VALUE_REQUIRED,
        'keep' => InputOption::VALUE_REQUIRED,
        'tag' => InputOption::VALUE_OPTIONAL,
        'sha' => InputOption::VALUE_OPTIONAL,
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
        $runner_bin = $this->getConfig()->get('runner.bin_dir') . '/run';
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec($runner_bin . ' drupal:permissions-setup --root=' . $options['dist-root'] . '/' . $options['root'])
            ->exec($runner_bin . ' drupal:settings-setup --root=' . $options['dist-root'] . '/' . $options['root']);

        // Clean up non-required files.
        $keep = '! -name "' . $options['dist-root'] . '" ! -name "' . implode('" ! -name "', explode(',', $options['keep'])) . '"';
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec('find ' . $options['dist-root'] . ' -maxdepth 1 ' . $keep . ' -exec rm -rf {} +');

        // Prepare sha and tag variables.
        $tag = $options['tag'] ?? $this->getGitTag();
        $hash = $options['sha'] ?? $this->getGitCommitHash();

        // Write version tag in manifest.json and VERSION.txt.
        $tasks[] = $this->taskWriteToFile($options['dist-root'] . '/manifest.json')->text(
            json_encode(['version' => $tag, 'sha' => $hash], JSON_PRETTY_PRINT)
        );
        $tasks[] = $this->taskWriteToFile($options['dist-root'] . '/' . $options['root'] . '/VERSION.txt')->text($tag);

        // Collect and execute list of commands set on local runner.yml.
        $commands = $this->getConfig()->get("toolkit.build.dist.commands");
        if (!empty($commands)) {
            $tasks[] = $this->taskCollectionFactory($commands);
        }

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
     * @option root Drupal root.
     */
    public function buildDev(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = [];
        $root = $options['root'];

        // Run site setup.
        $runner_bin = $this->getConfig()->get('runner.bin_dir') . '/run';
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec($runner_bin . ' drupal:settings-setup --root=' . $root);

        // Collect and execute list of commands set on local runner.yml.
        $commands = $this->getConfig()->get("toolkit.build.dev.commands");
        if (!empty($commands)) {
            $tasks[] = $this->taskCollectionFactory($commands);
        }

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
     */
    public function buildDevReset(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = [];

        $question = 'Are you sure you want to proceed? This action cleans up your git repository of any tracked AND untracked files AND folders!';
        if ($this->confirm($question, false)) {
            // Clean git.
            $tasks[] = $this->taskGitStack()
                ->stopOnFail()
                ->exec('clean -fdx --exclude=vendor/ec-europa/toolkit');
            // Run composer install.
            $tasks[] = $this->taskComposerInstall('composer');
            // Run toolkit:build-dev.
            $runner_bin = $this->getConfig()->get('runner.bin_dir') . '/run';
            $tasks[] = $this->taskExecStack()
                ->stopOnFail()
                ->exec($runner_bin . ' toolkit:build-dev --root=' . $options['root']);
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
     * Compile Css and Js.
     *
     * @param array $options
     *   Additional options for the command.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   The collection builder.
     *
     * @command toolkit:compile-css-js
     */
    public function compileCssJs(array $options = [
      'theme-dir' => InputOption::VALUE_REQUIRED,
    ]) {
          $this->_deleteDir([$options['theme-dir'] . '/assets']);

          $this->_copy('vendor/ec-europa/toolkit/src/gulp/gulpfile.js', $options['theme-dir'] . '/gulpfile.js');

          $this->taskExecStack()
            ->stopOnFail()
            ->dir($options['theme-dir'])
            ->exec('npm init -y')
            ->exec('npm install gulp gulp-sass gulp-concat gulp-minify-css gulp-minify --save-dev')
            ->exec('./node_modules/.bin/gulp minify-scss_to_css')
            ->exec('./node_modules/.bin/gulp minify-js')
            ->run();
    }

}
