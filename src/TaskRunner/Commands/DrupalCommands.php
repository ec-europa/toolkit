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
     * @command toolkit:install-template
     */
    public function toolkitInstallTemplate()
    {
        $workingDir = 'template';
        $taskCollection = array(
            // Run composer install.
            $this->taskComposerInstall()
                ->workingDir($workingDir)
                ->option('no-suggest')
                ->ansi(),
            // Initialize git for grumphp.
            $this->taskGitStack()
                ->stopOnFail()
                ->dir($workingDir)
                ->exec('init'),
            // Setup drush base url.
            $this->taskExec('../vendor/bin/run')
                ->dir(getcwd() . '/' . $workingDir)
                ->arg('drupal:drush-setup'),
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
      'exclude' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $drupalRoot = $this->getConfig()->get('drupal.root');
        $drupalVersion = $this->getConfig()->get('drupal.version');
        $drupalProfile = $this->getConfig()->get('drupal.site.profile');
        $enableallExclude = !empty($options['exclude']) ? explode(',', $options['exclude']) : explode(',', $this->getConfig()->get('drupal.site.enable_all_exclude'));
        $systemList = substr($drupalProfile, 0, 9 ) === "multisite" ?
            explode(',', $this->taskExec('./vendor/bin/drush -r ' . $drupalRoot . ' eval "echo implode(\',\', array_keys(feature_set_get_featuresets(NULL)));"')->printOutput(false)->run()->getMessage()) :
            array_keys(json_decode($this->taskExec('./vendor/bin/drush -r ' . $drupalRoot . ' pm-list --format=json --color=1')->printOutput(false)->run()->getMessage(), true));
        // Remove example modules from list.
        foreach($systemList as $key => $value) {
            if(strpos($value, '_example') !== false) {
                unset($systemList[$key]);
            }
        }
        $enabled = array_keys(json_decode($this->taskExec('./vendor/bin/drush -r ' . $drupalRoot . ' pm-list --format=json --status=enabled --color=1')->printOutput(false)->run()->getMessage(), true));
        $disabled = array_diff($systemList, $enabled);

        $enableModules = array_diff($disabled, $enableallExclude);
        $disableModules = $enableallExclude;
        $taskCollection = array();
        $solrModules = array('apachesolr', 'wiki_core', 'wiki_standard');

        if (!empty(array_intersect($solrModules, $enabled))) {
            $taskCollection[] = $this->taskExec("vendor/bin/drush -r $drupalRoot solr-set-env-url  http://solr:8983/solr/d7_apachesolr -y --color=1");
        }
        elseif(!empty(array_intersect($solrModules, $enableModules))) {
            $taskCollection[] = $this->taskExec("vendor/bin/drush -r $drupalRoot en apachesolr -y --color=1");
            $taskCollection[] = $this->taskExec("vendor/bin/drush -r $drupalRoot solr-set-env-url  http://solr:8983/solr/d7_apachesolr -y --color=1");
            $enableModules = array_diff($enableModules, array('apachesolr'));
        }

        $taskCollection[] = $this->taskExec("vendor/bin/drush -r $drupalRoot pm-enable " . implode(',', $enableModules) . " -y --color=1");
        
        $taskCollection[] = $drupalVersion == 7 ?
            $this->taskExec("vendor/bin/drush -r $drupalRoot pm-disable " . implode(',', $disableModules) . " -y --color=1") :
            $this->taskExec("vendor/bin/drush -r $drupalRoot pm-uninstall " . implode(',', $disableModules) . " -y --color=1");

        return $this->collectionBuilder()->addTaskList($taskCollection);
    }

    /**
     * @command drupal:generate-data
     */
    public function drupalGenerateData()
    {
        $drupalRoot = $this->getConfig()->get('drupal.root');
        $drupalVersion = $this->getConfig()->get('drupal.version');
        if ($this->taskExec("vendor/bin/drush -r $drupalRoot en devel_generate -y --color=1")->run()) {
            // Generate a vocabulary if needed.
            if ($this->taskExec("vendor/bin/drush -r $drupalRoot generate-vocabs 1 --color=1")->run()) {
                $vocabularyName = $drupalVersion == 7 ?
                    $this->taskExec('./vendor/bin/drush -r ' . $drupalRoot . ' sqlq "select name from taxonomy_vocabulary limit 1;"')->printOutput(false)->run()->getMessage() :
                    $this->taskExec('./vendor/bin/drush -r ' . $drupalRoot . ' eval "echo array_keys(\Drupal\taxonomy\Entity\Vocabulary::loadMultiple())[0];"')->printOutput(false)->run()->getMessage();
            }
            $contentTypes = $drupalVersion == 7 ?
                $this->taskExec('./vendor/bin/drush -r ' . $drupalRoot . ' sqlq "select GROUP_CONCAT(type) from node_type where name <> \'poll\';"')->printOutput(false)->run()->getMessage() :
                $this->taskExec('./vendor/bin/drush -r ' . $drupalRoot . ' eval "echo implode(\',\', array_keys(\Drupal\node\Entity\NodeType::loadMultiple()));"')->printOutput(false)->run()->getMessage();
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
        $drupalProfile = $this->getConfig()->get('drupal.site.profile');
        $drupalVersion = $this->getConfig()->get('drupal.version');
        $drupalSite = $this->getConfig()->get('drupal.site.sites_subdir');
        $sitePath = $drupalRoot . '/sites/' . $drupalSite;

        if ($drupalProfile == 'standard') {
            $this->taskFilesystemStack()->stopOnFail()
                ->mkdir('web/sites/all/modules/contrib')
                ->symlink(getcwd() . '/vendor/drupal/drupal-extension/fixtures/drupal' . $drupalVersion . '/modules/behat_test', getcwd() . '/web/sites/all/modules/contrib/behat_test')
                ->copy('behat.yml', 'vendor/drupal/drupal-extension/behat.yml', true)
                ->run();
            if ($this->taskExec("vendor/bin/drush -r web en locale behat_test -y --color=1")->run()) {
                return $this->taskExec('./vendor/bin/behat -c vendor/drupal/drupal-extension/behat.yml --colors -v')->run();
            }
        }
        else {
            $this->say("Skipping Drupal Core Behat tests. Only available on 'standard' profile.");
        }
    }

    /**
     * @command drupal:drush-smoke
     */
    public function drupalDrushSmoke()
    {
        $drupalRoot = $this->getConfig()->get('drupal.root');
        $drupalVersion = $this->getConfig()->get('drupal.version');
        $drupalSite = $this->getConfig()->get('drupal.site.sites_subdir');
        $sitePath = $drupalRoot . '/sites/' . $drupalSite;

        if ($drupalVersion == 7 && $this->taskExec("vendor/bin/drush -r $drupalRoot en dblog -y --color=1")->run()) {
            return $this->taskExec("vendor/bin/drush -r $drupalRoot watchdog-smoketest --color=1");
        }
        else {
            return $this->say("Skipping Drupal Drush Smoke tests. Only available for Drupal 7 at the moment.");
        }
    }

    /**
     * @command toolkit:build-template
     * 
     * @option template     Template name in format of 'vendor.project.version';
     *
     * @param array $options
     */
    public function toolkitBuildTemplate(array $options = [
      'template' => InputOption::VALUE_REQUIRED,
    ])
    {
        $this->taskCleanDir(['template'])->run();
        $templateName = $options['template'];
        $templateLocation = str_replace('.', '/', $templateName);
        $version = explode('.', $templateName)[2];
        $templateProjectLocation = substr($templateLocation, 0, strrpos($templateLocation, '/'));
        $profile = $this->getConfig()->get("templates.$templateName.profile.name");
        $enableAllExclude = implode(',', $this->getConfig()->get("templates.$templateName.profile.enable_all_exclude"));

        $taskCollection = array(
            // Generate runner.yml for template.
            $this->taskWriteToFile("resources/$templateLocation/runner.yml")
                ->textFromFile("resources/$templateProjectLocation/runner.yml")
                ->place('version', $version)
                ->place('profile', $profile)
                ->place('enable_all_exclude', $enableAllExclude),
        );

        $template = $this->getConfig()->get("templates.$templateName");
        $composer = $this->getConfig()->get("composer.$templateName");
        // Eventually will need to be made recursive.
        if (isset($template['composer'])) {
            $composer = array_merge_recursive($this->getConfig()->get('composer.' . $template['composer']), $composer);
        }
        // Make sure replacements are removed.
        if (isset($composer['replace'])) {
            foreach ($composer['replace'] as $replacement => $replacement_version) {
                if (isset($composer['require'][$replacement])) {
                    unset($composer['require'][$replacement]);
                }
                if (isset($composer['require-dev'][$replacement])) {
                    unset($composer['require-dev'][$replacement]);
                }
            }
        }
        $composerJson = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $taskCollection[] = $this->taskWriteToFile("resources/$templateLocation/composer.json")->text($composerJson);
        $taskCollection[] = $this->taskRsync()->fromPath("resources/$templateLocation/")->toPath('template/')->recursive()->option('copy-links');

        return $this->collectionBuilder()->addTaskList($taskCollection);
    }
}