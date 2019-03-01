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
            // Symlink runner binary.
            $this->taskFilesystemStack()
                ->stopOnFail()
                ->mkdir(getcwd() . '/' . $workingDir . '/vendor/bin')
                ->symlink(getcwd() . '/vendor/bin/run', getcwd() . '/' . $workingDir . '/vendor/bin/run'),
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
            $this->taskExec('vendor/bin/run')
                ->dir(getcwd() . '/' . $workingDir)
                ->arg('drupal:drush-setup'),
            // TEMP HACK.
            $this->taskFilesystemStack()
                ->stopOnFail()
                ->symlink(getcwd() . '/resources/drush/drush/8/commands', getcwd() . '/' . $workingDir . '/web/drush/commands'),
        );

        return $this->collectionBuilder()->addTaskList($taskCollection);
    }

    /**
     * @command toolkit:setup
     */
    public function toolkitSetup()
    {
        $templatePath = getcwd() . "/resources/drupal/drupal/8/";
        $this->_copyDir($templatePath, '../../../');
    }
 
    /**
     * @command toolkit:reset-template
     */
    public function toolkitResetTemplate()
    {

    }

    /**
     * @command toolkit:install-clone
     */
    public function toolkitInstallClone()
    {
    }

    /**
     * @command toolkit:test-run-phpcs
     */
    public function toolkitTestRunPhpcs()
    {
    }

    /**
     * @command toolkit:test-run-behat
     */
    public function toolkitTestRunBehat()
    {
    }

    /**
     * @command drupal:enable-all
     *
     * @option disable  Comma seperated list of modules to disable.
     *
     * @param array $options
     */
    private function drupalEnableAll(array $options = [
      'exclude' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $drupalRoot = $this->getConfig()->get('drupal.root');
        $drupalVersion = $this->getConfig()->get('drupal.version');
        $drupalProfile = $this->getConfig()->get('drupal.site.profile');
        $enableallExclude = !empty($options['exclude']) ? explode(',', $options['exclude']) : $this->getConfig()->get('drupal.site.enable_all_exclude');
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
    private function drupalGenerateData()
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
    private function drupalDrushSmoke()
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
        $taskCollection = array();
        $templateLocation = str_replace('.', '/', $templateName);
        $templateProjectLocation = substr($templateLocation, 0, strrpos($templateLocation, '/'));

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
                if (isset($composer['extra']['patches'][$replacement])) {
                    unset($composer['extra']['patches'][$replacement]);
                }
            }
        }
        $composerJson = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $taskCollection[] = $this->taskWriteToFile("template/composer.json")->text($composerJson);

        // Generate runner.yml for template.
        preg_match_all('/[^\$]\{(.*?)\}/', file_get_contents("vendor/ec-europa/toolkit/resources/$templateProjectLocation/runner.yml"), $matches);
        $tokens = $matches[1];
        $runner = $this->taskWriteToFile("vendor/ec-europa/toolkit/resources/$templateLocation/runner.yml")
            ->textFromFile("resources/$templateProjectLocation/runner.yml");
        foreach($tokens as $token) {
            $value = $this->getConfig()->get("templates.$templateName.$token");
            if (is_array($value)) {
                $value = !empty($value) ? ' "' . implode('", "', $value) . '" ' : '';
            }
            $runner->place($token, $value);
        }
        $taskCollection[] = $runner;
        
        $taskCollection[] = $this->taskRsync()->fromPath("vendor/ec-europa/toolkit/resources/$templateLocation/")->toPath('template/')->recursive()->option('copy-links');

        return $this->collectionBuilder()->addTaskList($taskCollection);
    }

    /**
     * @command drupal:grumphp
     */
    public function drupalGrumphp()
    {
        // Run grumphp.
        return $this->taskExec("./vendor/bin/grumphp run")->run();
    }

    /**
     * @command drupal:platform-rsync
     */
    private function drupalPlatformRsync()
    {
        // Rsync platform to root.
        $drupalRoot = $this->getConfig()->get('drupal.root');
        $composer = json_decode(file_get_contents('composer.json'), true);
        if (isset($composer['extra']['installer-paths']['vendor/drupal/drupal/'])) {
            return $this->taskRsync()->fromPath('vendor/drupal/drupal/')->toPath("$drupalRoot/")->recursive()->option('copy-links')->run();
        }
    }

    /**
     * @command drupal:make-to-composer
     * 
     * @option make-file    Location of make file to merge into template.
     *
     * @param array $options
     */
    private function drupalMakeToComposer(array $options = [
      'make-file' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $makeFile = !empty($options['make-file']) ? $options['make-file'] : 'resources/site.make';
        $composerFile = 'template/composer.json';
        if (file_exists($makeFile) && file_exists($composerFile)) {

            $composer = file_get_contents($composerFile);
            $this->taskExec("./vendor/bin/drush cc drush")->run();
            $composerMake = json_decode($this->taskExec('./vendor/bin/drush m2c ' . $makeFile)->printOutput(false)->run()->getMessage(), true);
            $composerMain = json_decode($composer, true);
            $newComposer = array_merge_recursive($composerMain, $composerMake);
            $newComposerJson = json_encode($newComposer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            return $this->taskWriteToFile("template/composer.json")->text($newComposerJson)->run();
        }
    }
}