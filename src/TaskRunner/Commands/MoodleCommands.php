<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\JunitXmlGenerator;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Collection\CollectionBuilder;
use Symfony\Component\Console\Input\InputOption;

/**
 * Moodle commands to setup, build and test a Moodle website.
 */
class MoodleCommands extends AbstractCommands
{

    /**
     * Comment ending the Toolkit settings block.
     *
     * @var string
     */
    protected string $blockEnd = '// End Toolkit settings block.';

    /**
     * Comment starting the Toolkit settings block.
     *
     * @var string
     */
    protected string $blockStart = '// Start Toolkit settings block.';

    /**
     * Generate Moodle config.php file in compliance with Toolkit conventions.
     *
     * This command will:
     *
     * - Copy "config-dist.php" to "config.php", which will be overridden
     *   if existing
     * - Add database and config directory settings using environment variables
     *
     * You can specify additional config.php portions in your local
     * runner.yml.dist as shown below:
     *
     * > moodle:
     * >   additional_settings: |
     * >   $CFG->dbhost = getenv('MOODLE_DB_HOST');
     * >   $CFG->dbname = getenv('MOODLE_DB_NAME');
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @command moodle:generate-config
     *
     * @option root                   Drupal root.
     * @option skip-permissions-setup Drupal skip permissions setup.
     */
    public function moodleGenerateConfig(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'skip-permissions-setup' => false,
    ]): CollectionBuilder
    {
        // Get config-dist.php and config.php paths.
        $configDefaultPath = $options['root'] . '/config-dist.php';
        $configPath = $options['root'] . '/config.php';
        $tasks = [];

        // Copy config-dist.php on config.php, if the latter does not exist.
        if (!file_exists($configPath)) {
            $tasks[] = $this->taskFilesystemStack()
                ->copy($configDefaultPath, $configPath);
        }

        // Remove Toolkit settings block, if any.
        $tasks[] = $this->taskReplaceInFile($configPath)
            ->regex($this->getSettingsBlockRegex())
            ->to('');

        // Remove "require" statements, if any.
        $tasks[] = $this->taskReplaceInFile($configPath)
            ->regex($this->getRequireStatementsRegex())
            ->to('');

        // Append Toolkit settings block to config.php file.
        $tasks[] = $this->taskWriteToFile($configPath)
            ->append()
            ->text($this->getToolkitSettingsBlock());

        // Set necessary permissions on the config.php.
        if (!$options['skip-permissions-setup']) {
            $tasks[] = $this->taskFilesystemStack()->chmod($configPath, octdec('644'));
        }

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
     * @command moodle:build-dist
     *
     * @option tag Version tag for manifest.
     * @option sha Commit hash for manifest.
     *
     * @aliases md-bdist
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function moodleBuildDist(array $options = [
        'tag' => InputOption::VALUE_OPTIONAL,
        'sha' => InputOption::VALUE_OPTIONAL,
    ]): CollectionBuilder
    {
        $tasks = [];

        // Config variables.
        $config = $this->getConfig();
        $root = $config->get('drupal.root');
        $distRoot = $config->get('toolkit.build.dist.root');
        $dataRoot = $config->get('moodle.data_root');
        $requiredResources = $config->get('toolkit.build.dist.keep');
        $unwantedResources = $config->get('toolkit.build.dist.remove');

        // Delete and (re)create the dist folder.
        $tasks[] = $this->taskFilesystemStack()
            ->remove($distRoot)
            ->mkdir($distRoot)
            ->mkdir("$distRoot/$dataRoot");

        // Protect moodle data directory.
        $tasks[] = $this->getHtaccessTask("$distRoot/$dataRoot");

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
     * @command moodle:build-dev
     *
     * @option root Moodle root.
     *
     * @aliases md-bdev
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
     * Run Behat init.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command moodle:behat-init
     *
     * @option parallel      Number of parallel behat run to initialise.
     * @option maxruns       Max parallel processes to be executed at one time.
     * @option fromrun       Execute run starting from (Used for parallel runs on different vms).
     * @option torun         Execute run till (Used for parallel runs on different vms).
     * @option optimize-runs Execute run till (Used for parallel runs on different vms).
     *
     * @aliases md-behat-init, mbi
     *
     * @usage --parallel=2
     *
     * @return int
     *   The check moodle behat init status.
     */
    public function moodleBehatInit(array $options = [
        'parallel' => InputOption::VALUE_OPTIONAL,
        'maxruns' => InputOption::VALUE_OPTIONAL,
        'fromrun' => InputOption::VALUE_OPTIONAL,
        'torun' => InputOption::VALUE_OPTIONAL,
        'optimize-runs' => InputOption::VALUE_OPTIONAL,
    ])
    {
        // Config variables.
        $config = $this->getConfig();
        $root = $config->get('drupal.root');
        $behatConfigValues = $config->get('moodle.test.behat');
        $behatDataRoot = $behatConfigValues['data_root'];
        $behatFailDumps = $behatConfigValues['fail_dumps'];

        $execOpts = [];
        foreach (['parallel', 'maxruns', 'fromrun', 'torun', 'optimize-runs'] as $item) {
            $itemValue = $options[$item] ?? $behatConfigValues[$item] ?? null;
            if ($itemValue) {
                $execOpts[$item] = $itemValue;
            }
        }

        $tasks = [];

        // Create the behat data folder.
        if (!is_dir($behatDataRoot)) {
            $tasks[] = $this->taskFilesystemStack()
                ->mkdir($behatDataRoot);
        }

        // Create the behat fail dumps folder.
        if (!is_dir($behatFailDumps)) {
            $tasks[] = $this->taskFilesystemStack()
                ->mkdir($behatFailDumps);
        }

        // Set behat data and fail dumps permissions.
        $tasks[] = $this->taskFilesystemStack()
            ->chmod($behatDataRoot, octdec('777'))
            ->chmod($behatFailDumps, octdec('777'));

        // Run behat init script.
        $tasks[] = $this->taskExec("php $root/admin/tool/behat/cli/init.php")->options($execOpts, '=');

        $result = $this->collectionBuilder()->addTaskList($tasks)->run();

        return $result->getExitCode();
    }

    /**
     * Run Behat tests.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command moodle:behat
     *
     * @option feature    Only execute specified feature file (Absolute path of feature file).
     * @option tags       Only execute specified tags from feature files. If tags not provided it will use the 'moodle.behat.tags' config.
     * @option suite      Specified theme scenarios will be executed.
     * @option replace    Replace args string with run process number, useful for output.
     * @option fromrun    Execute run starting from (Used for parallel runs on different vms).
     * @option torun      Execute run till (Used for parallel runs on different vms)
     * @option rerun      Re-run scenarios that failed during last execution.
     * @option auto-rerun Automatically re-run scenarios that failed during last execution.
     * @option junit      Whether to export results as junit.
     *
     * @aliases md-behat, mb
     *
     * @usage --auto-rerun --tags="@javascript"
     *
     * @return int|\Robo\ResultData
     *   The check moodle test behat status.
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function moodleTestBehat(array $options = [
        'feature' => InputOption::VALUE_OPTIONAL,
        'tags' => InputOption::VALUE_OPTIONAL,
        'suite' => InputOption::VALUE_OPTIONAL,
        'replace' => InputOption::VALUE_OPTIONAL,
        'fromrun' => InputOption::VALUE_OPTIONAL,
        'torun' => InputOption::VALUE_OPTIONAL,
    ])
    {
        // Config variables.
        $config = $this->getConfig();
        $root = $config->get('drupal.root');
        $behatConfigValues = $config->get('moodle.test.behat');

        $execOpts = [];
        foreach (['feature', 'tags', 'suite', 'replace', 'fromrun', 'torun', 'rerun', 'auto-rerun'] as $item) {
            $itemValue = $options[$item] ?? $behatConfigValues[$item] ?? null;
            if ($itemValue) {
                $execOpts[$item] = in_array($item, ['rerun', 'auto-rerun'])
                    ? null
                    : $itemValue;
            }
        }

        $exec = $this->taskExec("php $root/admin/tool/behat/cli/run.php")->options($execOpts, '=');

        if ($this->isJunit()) {
            $cwd = getcwd();
            $exec->option('format', 'moodle_progress', '=');
            $exec->option('out', 'std', '=');
            $exec->option('format', 'junit', '=');
            $exec->option('out', "$cwd/junit-export/behat-xml", '=');
        }

        return $exec->run()->getExitCode();
    }

    /**
     * Run PHPUnit init test environment.
     *
     * @command moodle:phpunit-init
     *
     * @aliases md-phpunit-init, mpui
     *
     * @return int
     *   The check moodle PHPUnit init status.
     *
     * @throws \Robo\Exception\TaskException
     */
    public function moodlePhpUnitInit()
    {
        // Root config variable.
        $root = $this->getConfig()->get('drupal.root');

        $exec = $this->taskExecStack()
            ->stopOnFail()
            // en_AU locale must be installed on the server.
            // @see: https://moodledev.io/general/development/tools/phpunit#initialisation-of-test-environment
            ->exec('locale-gen en_AU.UTF-8')
            ->exec("php $root/admin/tool/phpunit/cli/init.php");

        return $exec->run()->getExitCode();
    }

    /**
     * Run Behat tests.
     *
     * Check configurations at config/default.yml - 'toolkit.test.phpunit'.
     * Accept commands to run before and/or after the PHPUnit tests.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command moodle:phpunit
     *
     * @option testsuite Filter which testsuite to run.
     * @option group     Only runs tests from the specified group(s).
     * @option covers    Only runs tests annotated with "@covers <name>".
     * @option uses      Only runs tests annotated with "@uses <name>".
     * @option filter    Filter which tests to run.
     * @option options   Extra options for the command without -- (only options with no value).
     * @option junit     Whether to export results as junit.
     *
     * @aliases md-phpunit mpu
     *
     * @usage --options='stop-on-error process-isolation do-not-cache-result'
     * @usage --group=Example
     *
     * @return \Robo\Collection\CollectionBuilder
     *   The moodle phpunit task status.
     *
     * @throws \Robo\Exception\TaskException
     */
    public function moodleTestPhpUnit(array $options = [
        'testsuite' => InputOption::VALUE_OPTIONAL,
        'group' => InputOption::VALUE_OPTIONAL,
        'covers' => InputOption::VALUE_OPTIONAL,
        'uses' => InputOption::VALUE_OPTIONAL,
        'filter' => InputOption::VALUE_OPTIONAL,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
        // Config variables.
        $config = $this->getConfig();
        $root = $config->get('drupal.root');
        $phpUnitConfigValues = $config->get('moodle.test.phpunit');

        $task = $this->taskExec($this->getBin('phpunit'))->option('configuration', "$root/phpunit.xml");
        if (!empty($options['options'])) {
            $options['options'] = str_replace('--', '', $options['options']);
            $opts = array_fill_keys(explode(' ', $options['options']), null);
            $task->options($opts, '=');
        }
        foreach (['testsuite', 'group', 'covers', 'uses', 'filter'] as $item) {
            $itemValue = $options[$item] ?? $phpUnitConfigValues[$item] ?? null;
            if ($itemValue) {
                $task->option($item, $itemValue, '=');
            }
        }
        if ($this->isJunit()) {
            $task->option('log-junit', JunitXmlGenerator::$dir . '/junit-phpunit.xml');
        }

        return $this->collectionBuilder()->addTask($task);
    }

    /**
     * Run Plugin validator.
     *
     * Light validation on the plugin file structure and code. Validation can be plugin specific.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command moodle:plugin-validator
     *
     * @option plugins Path to the plugins to be validated. Multiple values must be separated by comma.
     *
     * @aliases md-plugin-validator mpv
     *
     * @usage --plugins=local/plugin_a,local/plugin_b
     *
     * @return int
     *   The moodle plugin validator task status.
     */
    public function moodlePluginValidator(array $options = [
        'plugins' => InputOption::VALUE_OPTIONAL,
    ]): int
    {
        return $this->runMoodlePluginTask($options, 'validate');
    }

    /**
     * Run Plugin savepoints.
     *
     * Validates the plugin’s database upgrade script is written correctly according to Moodle’s upgrade process.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command moodle:plugin-savepoints
     *
     * @option plugins Path to the plugins to be validated. Multiple values must be separated by comma.
     *
     * @aliases md-plugin-savepoints mps
     *
     * @usage --plugins=local/plugin_a,local/plugin_b
     *
     * @return int
     *   The moodle plugin savepoints task status.
     */
    public function moodlePluginSavepoints(array $options = [
        'plugins' => InputOption::VALUE_OPTIONAL,
    ]): int
    {
        return $this->runMoodlePluginTask($options, 'savepoints', false);
    }

    /**
     * Executes Moodle plugin tasks.
     *
     * @param array<mixed> $options
     *   Command options.
     * @param string $command
     *   The moodle-plugin-ci command to run (e.g., 'validate', 'savepoints').
     * @param bool $includeRoot
     *   Whether to include the root path in the command.
     *
     * @return int
     *   The exit code of the task collection.
     */
    protected function runMoodlePluginTask(array $options, string $command, bool $includeRoot = true): int
    {
        $config = $this->getConfig();
        $root = $config->get('drupal.root');
        $plugins = $options['plugins'] ?? $config->get('moodle.test.validator.plugins');
        Toolkit::ensureArray($plugins);

        $binPath = $this->getBinPath('moodle-plugin-ci');
        $tasks = [];

        foreach ($plugins as $plugin) {
            $path = $includeRoot
                ? "$binPath $command -m ./$root ./$root/$plugin"
                : "$binPath $command -- ./$root/$plugin";
            $tasks[] = $this->taskExec($path);
        }

        $result = $this->collectionBuilder()->addTaskList($tasks)->run();
        return $result->getExitCode();
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

    /**
     * Remove settings block from given content.
     *
     * @return string
     *   Content without setting block.
     */
    protected function getSettingsBlockRegex(): string
    {
        return '/^\n' . preg_quote($this->blockStart, '/') . '.*?' . preg_quote($this->blockEnd, '/') . '\n/sm';
    }

    /**
     * Remove require statements from given content.
     *
     * @return string
     *   Content without require statements.
     */
    protected function getRequireStatementsRegex(): string
    {
        return '/require_once\s*\(.*\).*$/m';
    }

    /**
     * Helper function to update config.php file.
     *
     * @return string
     *   Database configuration to be attached to Drupal config.php.
     */
    protected function getToolkitSettingsBlock(): string
    {
        $additionalSettings = $this->getConfig()->get('moodle.additional_settings', '');
        $additionalSettings = trim($additionalSettings);

        return <<<EOF

{$this->blockStart}

{$additionalSettings}

// Load environment development override configuration, if available.
// Keep this code block at the end of this file to take full effect.
if (file_exists(\$CFG->dirroot . '/config.override.php')) {
  include \$CFG->dirroot . '/config.override.php';
}

{$this->blockEnd}

EOF;
    }

    /**
     * Check whether Junit option is being used, or env var is set.
     */
    protected function isJunit(): bool
    {
        $option = $this->input()->hasOption('junit') && !empty($this->input()->getOption('junit'));
        $env = !empty(getenv('CI_JUNIT'));
        return $option || $env;
    }

}
