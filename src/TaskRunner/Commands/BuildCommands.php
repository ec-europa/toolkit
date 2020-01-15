<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
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
     * @option tag       Version tag for manifest.
     * @option hash      Commit hash for manifest.
     * @option root      Drupal root.
     * @option dist-root Distribution package root.
     * @option keep      Files and folders to keep.
     */
    public function buildDist(array $options = [
        'tag' => InputOption::VALUE_OPTIONAL,
        'sha' => InputOption::VALUE_OPTIONAL,
        'root' => InputOption::VALUE_REQUIRED,
        'dist-root' => InputOption::VALUE_REQUIRED,
        'keep' => InputOption::VALUE_REQUIRED,
    ])
    {
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
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec('./vendor/bin/run drupal:permissions-setup --root=' . $options['dist-root'] . '/' . $options['root'])
            ->exec('./vendor/bin/run drupal:settings-setup --root=' . $options['dist-root'] . '/' . $options['root']);

        // Clean up non-required files.
        $keep = '! -name "' . implode('" ! -name "', explode(',', $options['keep'])) . '"';
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec('find ' . $options['dist-root'] . ' -maxdepth 1 ' . $keep . ' -exec rm -rf {} +');

        // Prepare sha and tag variables.
        $sha = !empty($options['sha']) ? ['sha' => $options['sha']] : [];
        $tag = !empty($options['tag']) ? ['version' => $options['tag']] : ['version' => 'latest'];

        // Write version tag in manifest.json and VERSION.txt.
        $tasks[] = $this->taskWriteToFile($options['dist-root'] . '/manifest.json')->text(
            json_encode(array_merge($tag, $sha), JSON_PRETTY_PRINT)
        );
        $tasks[] = $this->taskWriteToFile($options['dist-root'] . '/' . $options['root'] . '/VERSION.txt')->text($tag['version']);

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

        // Run site setup.
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec('./vendor/bin/run drupal:settings-setup --root=' . $options['root']);

        // Collect and execute list of commands set on local runner.yml.
        $commands = $this->getConfig()->get("toolkit.build.dev.commands");
        if (!empty($commands)) {
            $tasks[] = $this->taskCollectionFactory($commands);
        }

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }
}
