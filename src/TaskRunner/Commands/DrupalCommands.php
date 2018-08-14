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
     * @command toolkit:setup-template
     *
     * @option template Template id.
     *
     * @param array $options
     */
    public function toolkitSetupTemplate(array $options = [
      'template' => InputOption::VALUE_REQUIRED,
    ])
    {
        $template = $options['template'];
        $workingDir = 'resources/drupal/' . $template;
        $drupalVersion = $this->getConfig()->get("templates.$template.version");
        $drupalProfile = $this->getConfig()->get("templates.$template.profile");

        $taskCollection = array(
            $this->taskComposerInstall()
                ->workingDir($workingDir)
                ->option('no-suggest')
                ->ansi(),
            $this->taskGitStack()
                ->stopOnFail()
                ->dir($workingDir)
                ->exec('init'),
            $this->taskFilesystemStack()
                ->stopOnFail()
                ->remove(getcwd() . '/template')
                ->remove(getcwd() . '/build')
                ->symlink(getcwd() . '/' . $workingDir . '/web', 'build')
                ->symlink(getcwd() . '/' . $workingDir, 'template'),
            $this->taskWriteToFile('resources/drupal/' . $template . '/runner.yml')
                ->textFromFile('resources/drupal/runner.yml')
                ->place('version', $drupalVersion)
                ->place('profile', $drupalProfile),
        );

        return $this->collectionBuilder()->addTaskList($taskCollection);
    }

    /**
     * @command drupal:install
     */
    public function drupalInstall()
    {
        $drupalRoot = $this->getConfig()->get('drupal.root');
        $drupalSite = $this->getConfig()->get('drupal.site.sites_subdir');
        $drupalProfile = $this->getConfig()->get('drupal.site.profile');
        $sitePath = $drupalRoot . '/sites/' . $drupalSite;

        $taskCollection = array(
            $this->taskFilesystemStack()->stopOnFail()
                ->copy($sitePath . '/default.settings.php', $sitePath . '/settings.php', true)
                ->copy('resources/settings.local.php', $sitePath . '/settings.local.php', true)
                ->mkdir($sitePath . '/files/private_files'),
            $this->taskExecStack()->stopOnFail()
                ->exec('while ! mysqladmin ping --user=root -h mysql --password="" --silent; do echo Waiting for mysql; sleep 3; done')
                ->exec('/usr/bin/env PHP_OPTIONS="-d sendmail_path=`which true`" ./vendor/bin/drush -r web si --db-url=mysql://root:@mysql:3306/drupal ' . $drupalProfile . ' -y --color=1')
                ->exec("vendor/bin/drush -r web cron")
            );

        return $this->collectionBuilder()->addTaskList($taskCollection);
    }

    /**
     * @command drupal:enable-all
     *
     * @option disable  Comma seperated list of modules to disable.
     *
     * @param array $options
     */
    public function drupalEnableAll(array $options = [
      'disable' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $drupalRoot = $this->getConfig()->get('drupal.root');
        $drupalVersion = $this->getConfig()->get('drupal.version');
        $disable = explode(',', $options['disable']);
        $systemList = array_keys(json_decode($this->taskExec('./vendor/bin/drush -r ' . $drupalRoot . ' pm-list --format=json')->printOutput(false)->run()->getMessage(), true));
        $enabled = array_keys(json_decode($this->taskExec('./vendor/bin/drush -r ' . $drupalRoot . ' pm-list --format=json --status=enabled')->printOutput(false)->run()->getMessage(), true));
        $disabled = array_diff($systemList, $enabled);

        $enableModules = implode(',', array_diff($disabled, $disable));
        $disableModules = implode(',', $disable);

        $taskCollection = array(
            $this->taskExec("vendor/bin/drush -r $drupalRoot pm-enable $enableModules -y"),
        );
        
        $taskCollection[] = $drupalVersion == 7 ?
            $this->taskExec("vendor/bin/drush -r $drupalRoot pm-disable $disableModules -y") :
            $this->taskExec("vendor/bin/drush -r $drupalRoot pm-uninstall $disableModules -y");

        return $this->collectionBuilder()->addTaskList($taskCollection);
    }

    /**
     * @command drupal:generate-data
     */
    public function drupalGenerateData()
    {
        $drupalRoot = $this->getConfig()->get('drupal.root');
        $contentTypes =$this->taskExec('./vendor/bin/drush -r ' . $drupalRoot . ' sqlq "select GROUP_CONCAT(type) from node_type where name <> \'poll\';"')->printOutput(false)->run()->getMessage();
        $vocabularyName = $this->taskExec('./vendor/bin/drush -r ' . $drupalRoot . ' sqlq "select name from taxonomy_vocabulary limit 1;"')->printOutput(false)->run()->getMessage();


        if ($this->taskExec("vendor/bin/drush -r $drupalRoot en devel_generate -y --color=1")->run()) {
            // Generate a vocabulary if needed.
            if (!empty($vocabularyName) || $this->taskExec("vendor/bin/drush -r $drupalRoot generate-vocabs 1 --color=1")->run()) {
                $vocabularyName = $this->taskExec('./vendor/bin/drush -r ' . $drupalRoot . ' sqlq "select name from taxonomy_vocabulary limit 1;"')->printOutput(false)->run()->getMessage();
            }
            // Generate other data.
            $taskCollection = array(
                $this->taskExec("vendor/bin/drush -r $drupalRoot generate-users 50 --kill --pass=password --color=1"),
                $this->taskExec("vendor/bin/drush -r $drupalRoot generate-terms $vocabularyName 50 --kill --color=1"),
                $this->taskExec("vendor/bin/drush -r $drupalRoot generate-content 50 3 --kill --types=$contentTypes --kill --color=1"),
                $this->taskExec("vendor/bin/drush -r $drupalRoot generate-menus 2 50 --kill --color=1")
            );
            return $this->collectionBuilder()->addTaskList($taskCollection);
        }
    }

    /**
     * @command drupal:core-behat
     */
    public function drupalCoreBehat()
    {
        $drupalRoot = $this->getConfig()->get('drupal.root');
        $drupalVersion = $this->getConfig()->get('drupal.version');
        $drupalSite = $this->getConfig()->get('drupal.site.sites_subdir');
        $sitePath = $drupalRoot . '/sites/' . $drupalSite;

        $this->taskFilesystemStack()->stopOnFail()
            ->mkdir('web/sites/all/modules/contrib')
            ->symlink(getcwd() . '/vendor/drupal/drupal-extension/fixtures/drupal' . $drupalVersion . '/modules/behat_test', getcwd() . '/web/sites/all/modules/contrib/behat_test')
            ->copy('behat.yml', 'vendor/drupal/drupal-extension/behat.yml', true)
            ->run();
        if ($this->taskExec("vendor/bin/drush -r web en locale behat_test -y --color=1")->run()) {
            return $this->taskExec('./vendor/bin/behat -c vendor/drupal/drupal-extension/behat.yml --colors -v')->run();
        }
    }
}