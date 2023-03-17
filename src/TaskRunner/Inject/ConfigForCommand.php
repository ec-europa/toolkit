<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Inject;

use Consolidation\Config\ConfigInterface;
use Consolidation\Config\Util\ConfigFallback;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This class is based on \Consolidation\Config\Inject\ConfigForCommand.
 *
 * Will make sure that if an option is marked as array, it will be converted into an array.
 */
class ConfigForCommand implements EventSubscriberInterface
{
    /**
     * The config.
     *
     * @var \Consolidation\Config\ConfigInterface
     */
    protected $config;

    /**
     * The application.
     *
     * @var \Symfony\Component\Console\Application|null
     */
    protected $application;

    /**
     * Construct new ConfigForCommand.
     *
     * @param ConfigInterface $config
     *   The configuration.
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Set application.
     *
     * @param Application $application
     *   The application.
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [ConsoleEvents::COMMAND => 'injectConfiguration'];
    }

    /**
     * Before a Console command runs, inject configuration settings
     * for this command into the default value of the options of
     * this command.
     *
     * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
     *   The current event.
     */
    public function injectConfiguration(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        $this->injectConfigurationForGlobalOptions($event->getInput());
        $this->injectConfigurationForCommand($command, $event->getInput());

        $targetOfHelpCommand = $this->getHelpCommandTarget($command, $event->getInput());
        if ($targetOfHelpCommand) {
            $this->injectConfigurationForCommand($targetOfHelpCommand, $event->getInput());
        }
    }

    /**
     * Inject configurations for global options.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *   The current input.
     */
    protected function injectConfigurationForGlobalOptions($input)
    {
        if (!$this->application) {
            return;
        }

        $configGroup = new ConfigFallback($this->config, 'options');

        $definition = $this->application->getDefinition();
        $options = $definition->getOptions();

        $this->injectConfigGroupIntoOptions($configGroup, $options, $input);
    }

    /**
     * Inject configuration for command.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     *   The command to configure.
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *   The current input.
     */
    protected function injectConfigurationForCommand($command, $input)
    {
        $commandName = $command->getName();
        $commandName = str_replace(':', '.', $commandName);
        $configGroup = new ConfigFallback($this->config, $commandName, 'command.', '.options.');

        $definition = $command->getDefinition();
        $options = $definition->getOptions();

        $this->injectConfigGroupIntoOptions($configGroup, $options, $input);
    }

    /**
     * Inject configurations for options.
     *
     * @param \Consolidation\Config\Util\ConfigGroup $configGroup
     *   The current config group.
     * @param array $options
     *   The options.
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *   The input.
     */
    protected function injectConfigGroupIntoOptions($configGroup, $options, $input)
    {
        foreach ($options as $option => $inputOption) {
            $key = str_replace('.', '-', $option);
            $value = $configGroup->get($key);
            if ($value !== null) {
                if (is_bool($value) && ($value == true)) {
                    $input->setOption($key, $value);
                } elseif ($inputOption->acceptValue()) {
                    if ($this->isArray($inputOption)) {
                        $inputOption->setDefault($this->explodeArray($value));
                    } else {
                        $inputOption->setDefault($value);
                    }
                }
            }
        }
    }

    /**
     * Get the help command.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     *   The command to get the help from.
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *   The current input.
     *
     * @return false|\Symfony\Component\Console\Command\Command
     *   The command.
     */
    protected function getHelpCommandTarget($command, $input)
    {
        if (($command->getName() != 'help') || (!isset($this->application))) {
            return false;
        }

        $this->fixInputForSymfony2($command, $input);

        // Symfony Console helpfully swaps 'command_name' and 'command'
        // depending on whether the user entered `help foo` or `--help foo`.
        // One of these is always `help`, and the other is the command we
        // are actually interested in.
        $nameOfCommandToDescribe = $input->getArgument('command_name');
        if ($nameOfCommandToDescribe == 'help') {
            $nameOfCommandToDescribe = $input->getArgument('command');
        }
        return $this->application->find($nameOfCommandToDescribe);
    }

    /**
     * Fix arguments for help command.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     *   The command.
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *   The input.
     */
    protected function fixInputForSymfony2($command, $input)
    {
        // Symfony 3.x prepares $input for us; Symfony 2.x, on the other
        // hand, passes it in prior to binding with the command definition,
        // so we have to go to a little extra work.  It may be inadvisable
        // to do these steps for commands other than 'help'.
        if (!$input->hasArgument('command_name')) {
            $command->ignoreValidationErrors();
            $command->mergeApplicationDefinition();
            $input->bind($command->getDefinition());
        }
    }

    /**
     * Explode a string to array.
     *
     * @param mixed $value
     *   The value to explode.
     *
     * @return array
     *   The exploded values.
     */
    private function explodeArray(mixed $value)
    {
        return array_map('trim', explode(',', $value));
    }

    /**
     * Check whether the option is marked as VALUE_IS_ARRAY.
     *
     * @param $inputOption
     *   The option being checked.
     *
     * @return bool
     *   Whether is an array or not.
     */
    private function isArray($inputOption)
    {
        return InputOption::VALUE_IS_ARRAY === (InputOption::VALUE_IS_ARRAY & $inputOption->getDefault());
    }

}
