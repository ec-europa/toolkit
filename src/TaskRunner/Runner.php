<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner;

use Composer\Autoload\ClassLoader;
use Dflydev\DotAccessData\Data;
use EcEuropa\Toolkit\TaskRunner\Commands\ConfigurationCommands;
use EcEuropa\Toolkit\TaskRunner\Inject\ConfigForCommand;
use EcEuropa\Toolkit\Toolkit;
use Grasmash\Expander\Expander;
use League\Container\Container;
use League\Container\DefinitionContainerInterface;
use Robo\Application;
use Robo\ClassDiscovery\RelativeNamespaceDiscovery;
use Robo\Common\ConfigAwareTrait;
use Robo\Config\Config;
use Robo\Robo;
use Robo\Runner as RoboRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Toolkit Runner.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Runner
{
    use ConfigAwareTrait;

    public const APPLICATION_NAME = 'Toolkit Runner';
    public const REPOSITORY = 'ec-europa/toolkit';

    /**
     * The input.
     *
     * @var InputInterface
     */
    private $input;

    /**
     * The output.
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * The autoloader class.
     *
     * @var ClassLoader
     */
    private $classLoader;

    /**
     * The Robo Runner.
     *
     * @var RoboRunner
     */
    private $runner;

    /**
     * The container.
     *
     * @var Container|DefinitionContainerInterface|null
     */
    private $container;

    /**
     * The Robo Application.
     *
     * @var Application
     */
    private $application;

    /**
     * The current working directory.
     *
     * @var mixed
     */
    private $workingDir;

    /**
     * The loaded command classes.
     *
     * @var array
     */
    private array $commandClasses;

    /**
     * Initialize the Toolkit Runner.
     *
     * @param ClassLoader $classLoader
     *   The autoload file.
     * @param InputInterface $input
     *   The input from CLI arguments.
     * @param OutputInterface $output
     *   The CLI output.
     */
    public function __construct(ClassLoader $classLoader, InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->classLoader = $classLoader;
        $this->config = new Config();

        $this->workingDir = $this->getWorkingDir();
        chdir($this->workingDir);

        // Create application.
        $this
            ->prepareApplication()
            ->prepareContainer()
            ->prepareRunner();
    }

    /**
     * Execute the Runner.
     *
     * @return int
     *   The status code.
     */
    public function run()
    {
        $this->commandClasses = $this->discoverCommandClasses();
        $this->runner->registerCommandClasses($this->application, $this->commandClasses);
        $this->prepareConfigurations();
        $this->registerConfigurationCommands();
        return $this->runner->run($this->input, $this->output, $this->application);
    }

    /**
     * Discover Command classes.
     *
     * @return array|string[]
     *   An array with the Command classes.
     */
    private function discoverCommandClasses()
    {
        return (new RelativeNamespaceDiscovery($this->classLoader))
            ->setRelativeNamespace('TaskRunner\Commands')
            ->setSearchPattern('/.*Commands\.php$/')
            ->getClasses();
    }

    /**
     * Returns the current working directory.
     *
     * @return string
     */
    private function getWorkingDir()
    {
        return (string) $this->input->getParameterOption('--working-dir', getcwd());
    }

    /**
     * Create and prepare the Application.
     *
     * @return $this
     */
    private function prepareApplication()
    {
        $this->application = Robo::createDefaultApplication(self::APPLICATION_NAME, Toolkit::VERSION);
        $this->application->getDefinition()
            ->addOption(new InputOption(
                '--working-dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Working directory, defaults to current working directory.',
                $this->workingDir
            ));

        return $this;
    }

    /**
     * Recursively merge config files.
     *
     * @param array $files
     *   The file paths to fetch the configs.
     * @param array|null $config
     *   The given, the new configs will be merged.
     */
    private function parseConfigFiles(array $files, array $config = null): array
    {
        $config = $config ?? [];
        foreach ($files as $file) {
            $content = Yaml::parseFile($file);
            if (!empty($content) && is_array($content)) {
                $config = array_replace_recursive($config, $content);
            }
        }
        return $config;
    }

    /**
     * Create the configurations and process overrides.
     *
     * @return $this
     */
    private function prepareConfigurations()
    {
        $workingDir = realpath($this->workingDir);
        // Load the Toolkit default configurations.
        $config = $this->parseConfigFiles([Toolkit::getToolkitRoot() . '/config/default.yml']);
        $config['runner']['working_dir'] = $workingDir;
        $tkConfigDir = Toolkit::getToolkitRoot() . '/' . $config['runner']['config_dir'];
        $files = $this->getConfigDirFilesPaths($tkConfigDir);
        $config = $this->parseConfigFiles($files, $config);
        $overrides = $config['overrides'];

        // Get the command options configurations from loaded command classes.
        foreach ($this->commandClasses as $commandClass) {
            $f = $this->runner->getContainer()->get("{$commandClass}Commands")->getConfigurationFile() ?? '';
            $config = $this->parseConfigFiles([$f], $config);
        }

        // Save the project configurations separately to allow the overrides.
        $projectConfig = [];
        // Load the Project configuration.
        if (file_exists($workingDir . '/runner.yml.dist')) {
            $projectConfig = $this->parseConfigFiles([$workingDir . '/runner.yml.dist'], $projectConfig);
        }

        // Check if the project has dynamic configs.
        $projectConfigDir = $workingDir . '/' . ($projectConfig['runner']['config_dir'] ?? $config['runner']['config_dir']);
        if ($tkConfigDir !== $projectConfigDir) {
            if (!empty($files = $this->getConfigDirFilesPaths($projectConfigDir))) {
                $projectConfig = $this->parseConfigFiles($files, $projectConfig);
            }
        }

        // Usually the runner.yml is used for local development and is not committed
        // to the repo, load it as last so it can override values properly.
        if (file_exists($workingDir . '/runner.yml')) {
            $projectConfig = $this->parseConfigFiles([$workingDir . '/runner.yml'], $projectConfig);
        }

        // Merge the toolkit and project configurations.
        $config = array_replace_recursive($config, $projectConfig);

        $expander = new Expander();
        $result = $expander->expandArrayProperties($config);
        $this->config->replace($result);

        // Allow some configurations to be overridden. If a given property is
        // defined on a project level it will replace the default values
        // instead of merge.
        $projectConfigLoaded = new Data($projectConfig);
        foreach ($overrides as $override) {
            if ($value = $projectConfigLoaded->get($override, null)) {
                $this->config->setDefault($override, $value);
            }
        }

        return $this;
    }

    /**
     * Prepare the container with the configurations.
     *
     * @return $this
     */
    private function prepareContainer()
    {
        $this->container = new Container();
        $this->container->defaultToShared();

        Robo::configureContainer($this->container, $this->application, $this->config, $this->input, $this->output, $this->classLoader);
        $this->container->extend('injectConfigEventListener')
            ->setConcrete(ConfigForCommand::class);

        $this->container->get('commandFactory')
            ->setIncludeAllPublicMethods(false);

        Robo::finalizeContainer($this->container);

        return $this;
    }

    /**
     * Create and configure the Robo runner.
     *
     * @return $this
     */
    private function prepareRunner()
    {
        // Passing an array as RoboClass will avoid Robo from processing a RoboFile.
        $this->runner = new RoboRunner(['']);
        $this->runner
            ->setClassLoader($this->classLoader)
            ->setConfigurationFilename(Toolkit::getToolkitRoot() . '/config/default.yml')
            ->setSelfUpdateRepository(self::REPOSITORY)
            ->setContainer($this->container);
        return $this;
    }

    /**
     * Register commands in the runner.yml under 'commands:'.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function registerConfigurationCommands()
    {
        if (!$commands = $this->getConfig()->get('commands')) {
            return;
        }

        /* @var \Consolidation\AnnotatedCommand\AnnotatedCommandFactory $commandFactory */
        $commandFactory = Robo::getContainer()->get('commandFactory');
        $this->runner->registerCommandClass($this->application, ConfigurationCommands::class);
        $commandClass = Robo::getContainer()->get(ConfigurationCommands::class . "Commands");

        foreach ($commands as $name => $tasks) {
            $aliases = [];
            // This command has been already registered as an annotated command.
            if ($this->application->has($name)) {
                $registeredCommand = $this->application->get($name);
                $aliases = $registeredCommand->getAliases();
                // The dynamic command overrides an alias rather than a
                // registered command main name. Get the command main name.
                if (in_array($name, $aliases, true)) {
                    $name = $registeredCommand->getName();
                }
            }

            $commandInfo = $commandFactory->createCommandInfo($commandClass, 'execute');

            $command = $commandFactory->createCommand($commandInfo, $commandClass)
                ->setName($name);
            if (isset($tasks['aliases']) || !empty($aliases)) {
                $aliases = array_filter(array_merge(
                    $aliases,
                    array_map('trim', explode(',', $tasks['aliases'] ?? ''))
                ));
                $command->setAliases($aliases);
            }
            if (isset($tasks['description'])) {
                $command->setDescription($tasks['description']);
            }
            if (isset($tasks['help'])) {
                $command->setHelp($tasks['help']);
            }
            if (isset($tasks['hidden'])) {
                $command->setHidden((bool) $tasks['hidden']);
            }
            if (isset($tasks['usage'])) {
                foreach ((array) $tasks['usage'] as $usage) {
                    $command->addUsage($usage);
                }
            }

            $this->application->add($command);
        }
    }

    /**
     * Get runner config directory files.
     *
     * @param string $runnerConfigDir
     *
     * @return string[]
     */
    private function getConfigDirFilesPaths(string $runnerConfigDir): array
    {
        if ($paths = glob($runnerConfigDir . '/*.yml')) {
            return $paths;
        }
        return [];
    }

}
