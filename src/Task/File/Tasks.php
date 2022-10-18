<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Task\File;

/**
 * Robo task to Replace configs/tokens in file.
 *
 * phpcs:disable Generic.NamingConventions.TraitNameSuffix.Missing
 */
trait Tasks
{
    /**
     * Process the file.
     *
     * @param string $source
     *   The source file to process.
     * @param string $destination
     *   The destination file.
     */
    protected function taskProcess(string $source, string $destination)
    {
        return $this->task(Process::class, $source, $destination);
    }
}
