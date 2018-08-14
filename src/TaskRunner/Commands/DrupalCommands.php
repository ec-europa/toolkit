<?php

namespace Eceuropa\Toolkit\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use Consolidation\AnnotatedCommand\CommandData;
use NuvoleWeb\Robo\Task as NuvoleWebTasks;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Traits as TaskRunnerTraits;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DrupalCommands
 *
 * @package Eceuropa\Toolkit\TaskRunner\Commands
 */
class DrupalCommands extends AbstractCommands implements FilesystemAwareInterface
{
    use TaskRunnerTraits\ConfigurationTokensTrait;
    use TaskRunnerTraits\FilesystemAwareTrait;
    use TaskRunnerTasks\CollectionFactory\loadTasks;
    use NuvoleWebTasks\Config\loadTasks;

    /**
     * @command project:install
     *
     * @option template Template id.
     *
     * @param array $options
     */
    public function projectInstall(array $options = [
      'template' => InputOption::VALUE_REQUIRED,
    ])
    {
        $drupalRoot = $this->getConfig()->get('drupal.root');
        $drupalSite = $this->getConfig()->get('drupal.site.sites_subdir');
        $drupalProfile = $this->getConfig()->get('drupal.site.profile');
        $sitePath = $drupalRoot . '/sites/' . $drupalSite;

        $this->taskComposerInstall()->ansi()->run();
        $this->taskGitStack()->stopOnFail()->exec('init');
        $this->taskFilesystemStack()
            ->copy($sitePath . '/default.settings.php', $sitePath . '/settings.php', true)
            ->copy('resources/settings.local.php', $sitePath . '/settings.local.php', true)
            ->mkdir($sitePath . '/files/private_files')
            ->run();
        $this->taskExec('while ! mysqladmin ping --user=root -h mysql --password="" --silent; do echo Waiting for mysql; sleep 3; done')->run();
        $this->taskExec('/usr/bin/env PHP_OPTIONS="-d sendmail_path=`which true`" ./vendor/bin/drush -r web si --db-url=mysql://root:@mysql:3306/drupal ' . $drupalProfile . ' -y --color=1')->run();
    }
}