<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner;

use Composer\Autoload\ClassLoader;
use Consolidation\Config\Loader\ConfigProcessor;
use Consolidation\Config\Util\ConfigOverlay;
use EcEuropa\Toolkit\TaskRunner\Commands\ConfigurationCommands;
use EcEuropa\Toolkit\TaskRunner\Inject\ConfigForCommand;
use EcEuropa\Toolkit\Toolkit;
use League\Container\Container;
use Psr\Container\ContainerInterface;
use Robo\Application;
use Robo\ClassDiscovery\RelativeNamespaceDiscovery;
use Robo\Common\ConfigAwareTrait;
use Robo\Config\Config;
use Robo\Robo;
use Robo\Runner as RoboRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Toolkit Runner.
 *
 * @@SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var \Robo\Runner
     */
    private $runner;

    /**
     * The container.
     *
     * @var Container|ContainerInterface|null
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
     * Configurations that can be replaced by a project.
     *
     * @var string[]
     */
    private $overrides = [
        'toolkit.build.dist.keep',
        'toolkit.test.phpcs.standards',
        'toolkit.test.phpcs.ignore_patterns',
        'toolkit.test.phpcs.triggered_by',
        'toolkit.test.phpcs.files',
        'toolkit.test.phpmd.ignore_patterns',
        'toolkit.test.phpmd.triggered_by',
        'toolkit.test.phpmd.files',
        'toolkit.test.phpstan.files',
        'toolkit.lint.eslint.ignores',
        'toolkit.lint.eslint.extensions_yaml',
        'toolkit.lint.eslint.extensions_js',
        'toolkit.lint.php.extensions',
        'toolkit.lint.php.exclude',
        'toolkit.hooks.active',
        'toolkit.hooks.prepare-commit-msg.conditions',
        'toolkit.hooks.pre-push.commands',
    ];

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
            ->prepareConfigurations()
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
        $classes = $this->discoverCommandClasses();
        $this->runner->registerCommandClasses($this->application, $classes);

        $this->registerConfigurationCommands();

        return $this->runner->run($this->input, $this->output, $this->application);
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
        $this->application = Robo::createDefaultApplication(self::APPLICATION_NAME, '0.0.1');
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
     * Create the configurations and process overrides.
     *
     * @return $this
     */
    private function prepareConfigurations()
    {
        $working_dir = realpath($this->workingDir);
        // Load Toolkit default configuration.
        $default_config = Robo::createConfiguration([Toolkit::getToolkitRoot() . '/config/default.yml']);
        $default_config->set('runner.working_dir', $working_dir);

        $config_file = '';
        if (file_exists($working_dir . '/runner.yml')) {
            $config_file = $working_dir . '/runner.yml';
        } elseif (file_exists($working_dir . '/runner.yml.dist')) {
            $config_file = $working_dir . '/runner.yml.dist';
        }

        // Re-build configuration.
        $processor = new ConfigProcessor();
        $context = $default_config->getContext(ConfigOverlay::DEFAULT_CONTEXT);
        $default_config->addContext(ConfigOverlay::DEFAULT_CONTEXT, $context);
        $processor->add($default_config->export());

        if (!empty($config_file)) {
            // Allow some configurations to be overridden. If a given property is
            // defined on a project level it will replace the default values
            // instead of merge.
            $current_config = Robo::createConfiguration([realpath($config_file)]);
            foreach ($this->overrides as $override) {
                if ($value = $current_config->get($override)) {
                    $context->set($override, $value);
                }
            }
            $processor->add($current_config->export());
        }

        // Import newly built configuration.
        $this->config->replace($processor->export());

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
            ->setRelativePluginNamespace('TaskRunner')
            ->setClassLoader($this->classLoader)
            ->setConfigurationFilename(Toolkit::getToolkitRoot() . '/config/default.yml')
            ->setSelfUpdateRepository(self::REPOSITORY)
            ->setContainer($this->container);
        return $this;
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
            $commandInfo->addAnnotation('tasks', $tasks['tasks'] ?? $tasks);

            $command = $commandFactory->createCommand($commandInfo, $commandClass)
                ->setName($name);
            // Check for options if the 'tasks' property exists.
            if (isset($tasks['tasks'])) {
                if (isset($tasks['aliases']) || !empty($aliases)) {
                    $aliases = array_merge(
                        $aliases,
                        array_map('trim', explode(',', $tasks['aliases'] ?? []))
                    );
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
            }

            $this->application->add($command);
        }
    }

}
