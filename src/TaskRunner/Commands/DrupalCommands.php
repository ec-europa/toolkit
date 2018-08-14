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

        $this->taskComposerInstall()->ansi()->option('no-suggest')->run();
        $this->taskGitStack()->stopOnFail()->exec('init')->run();
        $this->taskFilesystemStack()->stopOnFail()
            ->copy($sitePath . '/default.settings.php', $sitePath . '/settings.php', true)
            ->copy('resources/settings.local.php', $sitePath . '/settings.local.php', true)
            ->mkdir($sitePath . '/files/private_files')
            ->run();
        $this->taskExecStack()->stopOnFail()
            ->exec('while ! mysqladmin ping --user=root -h mysql --password="" --silent; do echo Waiting for mysql; sleep 3; done')
            ->exec('/usr/bin/env PHP_OPTIONS="-d sendmail_path=`which true`" ./vendor/bin/drush -r web si --db-url=mysql://root:@mysql:3306/drupal ' . $drupalProfile . ' -y --color=1')
            ->exec("vendor/bin/drush -r web cron")->run();
    }

    /**
     * @command drupal:enable-all
     *
     * @option disabled Comma seperated list of modules to disable.
     *
     * @param array $options
     */
    public function drupalEnableAll(array $options = [
      'disabled' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $drupalRoot = $this->getConfig()->get('drupal.root');
        $enabled = explode(PHP_EOL, $this->taskExec('./vendor/bin/drush -r ' . $drupalRoot . ' sqlq "select name from system where status=0 and name not like \'%_test%\';"')->printOutput(false)->run()->getMessage());
        $disabled = explode(',', $options['disabled']);
        $enableModules = implode(',', array_diff($enabled, $disabled));
        $disableModules = implode(',', array_diff($disabled, $enabled));

        $this->taskExec("vendor/bin/drush -r $drupalRoot en $enableModules -y")->run();
        $this->taskExec("vendor/bin/drush -r $drupalRoot dis $disableModules -y")->run();
    }

    /**
     * @command drupal:generate-data
     *
     * @option disabled Comma seperated list of modules to disable.
     *
     * @param array $options
     */
    public function drupalGenerateData(array $options = [
      'disabled' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $drupalRoot = $this->getConfig()->get('drupal.root');
        $contentTypes =$this->taskExec('./vendor/bin/drush -r ' . $drupalRoot . ' sqlq "select GROUP_CONCAT(type) from node_type where name <> \'poll\';"')->printOutput(false)->run()->getMessage();


        if ($this->taskExec("vendor/bin/drush -r $drupalRoot en devel_generate -y --color=1")->run()) {
            $this->taskExec("vendor/bin/drush -r $drupalRoot generate-users 50 --kill --pass=password --color=1")->run();
            $this->taskExec("vendor/bin/drush -r $drupalRoot generate-vocabs 1 --kill --color=1")->run();
            $vocabularyName =$this->taskExec('./vendor/bin/drush -r ' . $drupalRoot . ' sqlq "select name from taxonomy_vocabulary limit 1;"')->printOutput(false)->run()->getMessage();
            $this->taskExec("vendor/bin/drush -r $drupalRoot generate-terms $vocabularyName 50 --kill --color=1")->run();
            $this->taskExec("vendor/bin/drush -r $drupalRoot generate-content 50 3 --kill --types=$contentTypes --kill --color=1")->run();
            $this->taskExec("vendor/bin/drush -r $drupalRoot generate-menus 2 50 --kill --color=1")->run();
        }
    }

    /**
     * @command drupal:core-behat
     *
     * @option disabled Comma seperated list of modules to disable.
     *
     * @param array $options
     */
    public function drupalCoreBehat(array $options = [
      'disabled' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $drupalRoot = $this->getConfig()->get('drupal.root');
        $drupalSite = $this->getConfig()->get('drupal.site.sites_subdir');
        $sitePath = $drupalRoot . '/sites/' . $drupalSite;

        $this->taskFilesystemStack()->stopOnFail()
            ->mkdir('web/sites/all/modules/contrib')
            ->symlink(getcwd() . '/vendor/drupal/drupal-extension/fixtures/drupal7/modules/behat_test', getcwd() . '/web/sites/all/modules/contrib/behat_test')
            ->copy('behat.yml', 'vendor/drupal/drupal-extension/behat.yml', true)
            ->run();
        if ($this->taskExec("vendor/bin/drush -r web en locale behat_test -y --color=1")->run()) {
            $this->taskExec('./vendor/bin/behat -c vendor/drupal/drupal-extension/behat.yml --colors -v')->run();
        }
    }
}