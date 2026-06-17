<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner;

use EcEuropa\Toolkit\Toolkit;
use Robo\Common\ConfigAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Exception\TaskException;
use Robo\Tasks;

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
     *   Configuration file path.
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/base.yml';
    }

    /**
     * Validate and return the path to given bin.
     *
     * @param string $name
     *   The bin to look for.
     *
     * @return string
     *   The bin path.
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function getBin(string $name): string
    {
        $bin = $this->getBinPath($name);
        if (!file_exists($bin) && !$this->isSimulating()) {
            throw new TaskException($this, "Executable '$bin' was not found.");
        }

        return $bin;
    }

    /**
     * Return the path to given bin.
     *
     * @return string
     *   The path to given binary.
     */
    protected function getBinPath(string $name): string
    {
        // If looking for run binany, check in the root for it
        // so toolkit don't have to copy it into vendor/bin.
        if ($name === 'run' && file_exists($name)) {
            return $name;
        }
        return $this->getConfigValue('runner.bin_dir') . '/' . $name;
    }

    /**
     * Validate and return the path to given bin from node packages.
     *
     * @param string $name
     *   The bin to look for.
     *
     * @return string
     *   The bin path.
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function getNodeBin(string $name): string
    {
        $bin = $this->getNodeBinPath($name);
        if (!file_exists($bin) && !$this->isSimulating()) {
            throw new TaskException($this, "Executable '$bin' was not found.");
        }

        return $bin;
    }

    /**
     * Return the path to given bin from node packages.
     *
     * @return string
     *   The path to given binary.
     */
    protected function getNodeBinPath(string $name): string
    {
        return $this->getConfigValue('runner.bin_node_dir') . '/' . $name;
    }

    /**
     * Check if current command is being executed with option simulate.
     *
     * @return bool
     *   True if using --simulate, false otherwise.
     */
    protected function isSimulating(): bool
    {
        return (bool) $this->input()->getOption('simulate');
    }

    /**
     * Return the configuration value.
     *
     * @param string $key
     *   The config item to look up.
     * @param mixed|null $default
     *   Default configuration value for the key item if neither has value nor exists.
     *
     * @return mixed|null
     *   The configuration value.
     */
    protected function getConfigValue($key, $default = null)
    {
        if (!$this->getConfig()) {
            return $default;
        }
        return $this->getConfig()->get($key, $default);
    }

    /**
     * Returns the current working directory.
     *
     * @return string
     *   The current working directory.
     */
    public function getWorkingDir(): string
    {
        return (string) $this->input->getParameterOption('--working-dir', getcwd());
    }

    /**
     * Returns the JSON parsed content.
     *
     * @param bool $throwException
     *   If the file is not found.
     *
     * @return array<mixed>
     *   Json parsed content.
     */
    public function getJson(string $filename, bool $throwException = true): array
    {
        $file = $this->getWorkingDir() . '/' . $filename;
        if (!file_exists($file)) {
            if ($throwException) {
                throw new \Exception("The '$file' was not found.");
            } else {
                return [];
            }
        }
        return (array) json_decode(file_get_contents($file), true);
    }

    /**
     * Check if the website is installed.
     */
    public function isWebsiteInstalled(): bool
    {
        $drushBin = $this->getBin('drush');
        $result = $this->taskExec($drushBin . ' status --format=json')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        if (empty($result)) {
            return false;
        }
        $status = json_decode($result, true);
        return !empty($status['bootstrap']) && $status['bootstrap'] === 'Successful';
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
