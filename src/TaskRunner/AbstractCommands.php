<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner;

use EcEuropa\Toolkit\Toolkit;
use Robo\Common\ConfigAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Exception\TaskException;
use Robo\Robo;
use Robo\Tasks;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * Class AbstractCommands.
 */
abstract class AbstractCommands extends Tasks implements ConfigAwareInterface
{
    use ConfigAwareTrait;
    use \EcEuropa\Toolkit\Task\File\Tasks;
    use \EcEuropa\Toolkit\Task\Command\Tasks;

    /**
     * Path to YAML configuration file containing command defaults.
     *
     * Command classes should implement this method.
     *
     * @return string
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/base.yml';
    }

    /**
     * Command initialization.
     *
     * @param ConsoleCommandEvent $event
     *
     * @hook pre-command-event *
     */
    public function initializeRuntimeConfiguration(ConsoleCommandEvent $event)
    {
        Robo::loadConfiguration([$this->getConfigurationFile()], $this->getConfig());
    }

    /**
     * Return the path to given bin.
     *
     * @param string $name
     *   The bin to look for.
     *
     * @return string
     *   The bin path.
     *
     * @throws TaskException
     */
    protected function getBin($name)
    {
        $filename = $this->getConfig()->get('runner.bin_dir') . '/' . $name;
        if (!file_exists($filename) && !$this->isSimulating()) {
            throw new TaskException($this, "Executable '$filename' was not found.");
        }

        return $filename;
    }

    /**
     * Check if current command is being executed with option simulate.
     *
     * @return bool
     *   True if using --simulate, false otherwise.
     */
    protected function isSimulating()
    {
        return (bool) $this->input()->getOption('simulate');
    }
}
