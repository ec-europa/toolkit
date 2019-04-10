<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use NuvoleWeb\Robo\Task as NuvoleWebTasks;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Traits as TaskRunnerTraits;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ToolCommands.
 */
class ToolCommands extends AbstractCommands implements FilesystemAwareInterface {
  use NuvoleWebTasks\Config\loadTasks;
  use TaskRunnerTasks\CollectionFactory\loadTasks;
  use TaskRunnerTraits\ConfigurationTokensTrait;
  use TaskRunnerTraits\FilesystemAwareTrait;

  /**
   * {@inheritdoc}
   */
  public function getConfigurationFile() {
    return __DIR__ . '/../../../config/commands/tool.yml';
  }

  /**
   * Disable aggregation and clear cache.
   *
   * @param array $options
   *   Command options.
   *
   * @command toolkit:disable-drupal-cache
   *
   * @return \Robo\Collection\CollectionBuilder
   *   Collection builder.
   */
  public function disableDrupalCache(array $options = [
    'uri' => InputOption::VALUE_REQUIRED,
  ]) {
    $tasks = [];

    $this->taskExecStack()
      ->stopOnFail()
      ->exec('./vendor/bin/drush --uri=' . $options['uri'] . ' -y config-set system.performance css.preprocess 0')
      ->exec('./vendor/bin/drush --uri=' . $options['uri'] . ' -y config-set system.performance js.preprocess 0')
      ->exec('./vendor/bin/drush --uri=' . $options['uri'] . ' -y cache:rebuild');

    // Build and return task collection.
    return $this->collectionBuilder()->addTaskList($tasks);
  }

}
