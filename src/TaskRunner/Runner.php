<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner;

use Composer\Autoload\ClassLoader;
use Consolidation\Config\Loader\ConfigProcessor;
use Consolidation\Config\Util\ConfigOverlay;
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
        'toolkit.test.lint.yaml.pattern',
        'toolkit.test.lint.yaml.include',
        'toolkit.test.lint.yaml.exclude',
        'toolkit.test.lint.php.extensions',
        'toolkit.test.lint.php.exclude',
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
     * Execute the application.
     *
     * @return int
     *   The status code.
     */
    public function run()
    {
        $classes = $this->discoverCommandClasses();

        $this->runner->registerCommandClasses($this->application, $classes);

        return $this->runner->execute($_SERVER['argv'], self::APPLICATION_NAME, '0.0.1', $this->output);
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
        $this->application = new Application(self::APPLICATION_NAME, '0.0.1');
        $this->application->setAutoExit(false);
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
        $current_config = Robo::createConfiguration([realpath($config_file)]);

        // Allow some configurations to be overridden. If a given property is
        // defined on a project level it will replace the default values
        // instead of merge.
        $context = $default_config->getContext(ConfigOverlay::DEFAULT_CONTEXT);
        foreach ($this->overrides as $override) {
            if ($value = $current_config->get($override)) {
                $context->set($override, $value);
            }
        }

        // Re-build configuration.
        $processor = new ConfigProcessor();
        $default_config->addContext(ConfigOverlay::DEFAULT_CONTEXT, $context);
        $processor->add($default_config->export());
        $processor->add($current_config->export());

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
        // Here we use createDefaultContainer() because is not possible to set the $ouput when using createContainer().
        $this->container = Robo::createDefaultContainer(
            $this->input,
            $this->output,
            $this->application,
            $this->config,
            $this->classLoader
        );
        $this->container->add('config', $this->config);

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
            ->setContainer($this->container)
            ->setConfigurationFilename(Toolkit::getToolkitRoot() . '/config/default.yml')
            ->setSelfUpdateRepository(self::REPOSITORY);
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
}
