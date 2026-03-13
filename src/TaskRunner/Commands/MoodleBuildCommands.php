<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Collection\CollectionBuilder;
use Symfony\Component\Console\Input\InputOption;

/**
 * Moodle commands to setup, build and test a Moodle website.
 */
class MoodleBuildCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/build.yml';
    }

    /**
     * Build site for local development.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @throws \Robo\Exception\TaskException
     *
     * @command toolkit:build-dev
     *
     * @option root Moodle root.
     *
     * @aliases tk-bdev
     */
    public function moodleBuildDev(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
    ]): CollectionBuilder
    {
        $tasks = [];
        $config = $this->getConfig();
        $dataRoot = $config->get('moodle.data_root');
        if (!is_dir($dataRoot)) {
            // Create the moodledata folder.
            $tasks[] = $this->taskFilesystemStack()
                ->mkdir($dataRoot);
        }

        // Protect moodledata directory.
        $tasks[] = $this->getHtaccessTask($dataRoot);

        // Give moodledata permissions.
        $tasks[] = $this->taskFilesystemStack()
            ->chmod($dataRoot, octdec('777'));

        // Setup the site.
        $runnerBin = $this->getBin('run');
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec("$runnerBin toolkit:install-dependencies")
            ->exec($runnerBin . ' moodle:generate-config --root=' . $options['root']);

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
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
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @throws \Robo\Exception\TaskException
     *
     * @command toolkit:build-dist
     *
     * @option tag Version tag for manifest.
     * @option sha Commit hash for manifest.
     *
     * @aliases tk-bdist
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function buildDist(array $options = [
        'tag' => InputOption::VALUE_OPTIONAL,
        'sha' => InputOption::VALUE_OPTIONAL,
    ]): CollectionBuilder
    {
        $tasks = [];

        // Config variables.
        $config = $this->getConfig();
        $root = $config->get('drupal.root');
        $distRoot = $config->get('toolkit.build.dist.root');
        $requiredResources = $config->get('toolkit.build.dist.keep');
        $unwantedResources = $config->get('toolkit.build.dist.remove');

        // Delete and (re)create the dist folder.
        $tasks[] = $this->taskFilesystemStack()
            ->remove($distRoot)
            ->mkdir($distRoot);

        // Copy all (tracked) files to the dist folder.
        $tasks[] = $this->taskExec('git archive HEAD | tar -x -C ' . $distRoot);

        // Run production-friendly "composer install" packages.
        $tasks[] = $this->taskComposerInstall('composer')
            ->ignorePlatformRequirements()
            ->env('COMPOSER_MIRROR_PATH_REPOS', '1')
            ->workingDir($distRoot)
            ->optimizeAutoloader()
            ->noDev();

        // Setup the site.
        $runnerBin = $this->getBin('run');
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec($runnerBin . ' moodle:generate-config --root=' . $distRoot . '/' . $root);

        // Clean up non-required files.
        $keep = '! -name "' . $distRoot . '" ! -name "' . implode('" ! -name "', $requiredResources) . '"';
        $tasks[] = $this->taskExec("find $distRoot -maxdepth 1 $keep -exec rm -rf {} +");

        // Prepare sha and tag variables.
        $tag = !empty($options['tag']) ? $options['tag'] : '';
        $hash = !empty($options['sha']) ? $options['sha'] : '';

        // Write manifest.json file.
        $tasks[] = $this->taskWriteToFile($distRoot . '/manifest.json')
            ->text(json_encode([
                'project_id' => $config->get('toolkit.project_id'),
                'moodle_version' => ToolCommands::getPackagePropertyFromComposer('moodle/moodle'),
                'php_version' => phpversion(),
                'toolkit_version' => ToolCommands::getPackagePropertyFromComposer('ec-europa/toolkit'),
                'environment' => ToolCommands::getDeploymentEnvironment(),
                'date' => date('Y-m-d H:i:s'),
                'version' => $tag,
                'sha' => $hash,
            ]));

        // Collect and execute list of commands set on local runner.yml.
        $commands = $config->get('toolkit.build.dist.commands');
        if (!empty($commands)) {
            $tasks[] = $this->taskExecute($commands);
        }

        // Remove 'unwanted' resources from distribution.
        $remove = '-name "' . implode('" -o -name "', $unwantedResources) . '"';
        $tasks[] = $this->taskExec("find $distRoot -maxdepth 5 \( $remove \) -exec rm -rf {} +");

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Returns the task for adding htaccess file.
     *
     * @param string $root
     *   The moodledata root where the .htaccess file is.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   The htaccess file status.
     */
    protected function getHtaccessTask(string $root): CollectionBuilder
    {
        // Protect moodle data directory.
        return $this->collectionBuilder()->addTask($this->taskWriteToFile("$root/.htaccess")->text('Require all denied'));
    }

}
