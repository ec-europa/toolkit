<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use Symfony\Component\Console\Input\InputOption;

/**
 * Provides commands to build a site for development and a production artifact.
 */
class BuildCommands extends AbstractCommands {

  use TaskRunnerTasks\CollectionFactory\loadTasks;

  /**
   * {@inheritdoc}
   */
  public function getConfigurationFile() {
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
   */
  public function buildDist(array $options = [
    'tag' => InputOption::VALUE_OPTIONAL,
    'sha' => InputOption::VALUE_OPTIONAL,
    'root' => InputOption::VALUE_REQUIRED,
    'dist-root' => InputOption::VALUE_REQUIRED,
  ]) {
    $tasks = [];
    $tmpDir = $this->getConfig()->get("toolkit.tmp_folder");
    $prepDir = $tmpDir . '/dist/prep';

    // Create temp folder to prepare dist build in.
    $tasks[] = $this->taskFilesystemStack()
      ->remove($prepDir)
      ->mkdir($prepDir);

    // Rsync the codebase to the tmp folder.
    $tasks[] = $this->taskRsync()
      ->fromPath('./')
      ->toPath($prepDir)
      ->exclude([$tmpDir, 'vendor'])
      ->excludeVcs()
      ->recursive();

    // Run production-friendly "composer install" packages.
    $tasks[] = $this->taskComposerInstall('composer')
      ->workingDir($prepDir)
      ->optimizeAutoloader()
      ->noDev();

    // Setup the site.
    $tasks[] = $this->taskExecStack()
      ->stopOnFail()
      ->exec('./vendor/bin/run drupal:permissions-setup --root=' . $prepDir . '/' . $options['root'])
      ->exec('./vendor/bin/run drupal:settings-setup --root=' . $prepDir . '/' . $options['root']);

    // Create dist folder to rsyn prep folder into.
    $tasks[] = $this->taskFilesystemStack()
      ->remove($options['dist-root'])
      ->mkdir($options['dist-root']);

    // Rsync the tmp folder to the dist folder.
    $tasks[] = $this->taskRsync()
      ->fromPath($prepDir . '/')
      ->toPath($options['dist-root'])
      ->includeFilter([
        'composer.*',
        'config/***',
        'vendor/***',
        $options['root'] . '/***',
      ])
      ->exclude('*')
      ->recursive()
      ->args('-aL');

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
  ]) {
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
