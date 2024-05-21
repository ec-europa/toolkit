<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Task\File;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Result;
use Robo\Task\BaseTask;

/**
 * Process a source file to its destination replacing tokens.
 *
 * ``` php
 * <?php
 * $this->taskProcess('behat.yml')->run();
 * $this->taskProcess('behat.yml.dist', 'behat.yml')->run();
 * ?>
 * ```
 */
class Process extends BaseTask implements BuilderAwareInterface
{
    use BuilderAwareTrait;

    /**
     * Source file.
     *
     * @var string
     */
    protected string $source;

    /**
     * Destination file.
     *
     * @var string
     */
    protected string $destination;

    /**
     * The content from the source.
     *
     * @var string
     */
    protected string $content;

    /**
     * Constructs a new Process task.
     *
     * @param string $source
     * @param string $destination
     */
    public function __construct(string $source, string $destination = '')
    {
        $this->source = $source;
        $this->destination = $destination;
        if (empty($this->destination)) {
            $this->destination = $source;
        }
    }

    /**
     * Get the content from the source.
     */
    protected function loadContent()
    {
        if (!file_exists($this->source)) {
            return false;
        }
        $this->content = file_get_contents($this->source);
        return true;
    }

    /**
     * Return the tokens found in the content.
     *
     * @return array
     *   An array with the tokens found in the content.
     */
    protected function extractTokens()
    {
        preg_match_all('/\${(([A-Za-z]([A-Za-z0-9_\-]+)?\.?)+)}/', $this->content, $matches);
        if (!empty($matches[0]) && is_array($matches[0])) {
            return array_combine($matches[0], $matches[1]);
        }
        return [];
    }

    /**
     * Process the content by replacing the tokens with the values in config.
     *
     * @return array|string[]
     *   The tokens.
     */
    protected function processTokens()
    {
        $config = $this->getConfig();
        return array_map(function ($key) use ($config) {
            $value = $config->get($key);
            if (is_array($value)) {
                $array = iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($value)));
                return implode(',', $array);
            }
            return $value;
        }, $this->extractTokens());
    }

    /**
     * Execute the task.
     *
     * @return \Robo\Result
     *   The result of the task.
     */
    public function run()
    {
        if (!$this->loadContent()) {
            return Result::error($this, sprintf('File %s does not exist.', $this->source));
        }

        if ($this->source !== $this->destination) {
            $this->printTaskInfo('Creating {filename}', ['filename' => $this->destination]);
            $this->collectionBuilder()->taskFilesystemStack()
                ->copy($this->source, $this->destination, true)->run();
        }

        $tokens = $this->processTokens();
        $result = $this->collectionBuilder()->taskReplaceInFile($this->destination)
            ->from(array_keys($tokens))->to(array_values($tokens))->run();

        return new Result($this, $result->getExitCode(), $result->getMessage());
    }

}
