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
     *
     * @return \EcEuropa\Toolkit\Task\File\Process|\Robo\Collection\CollectionBuilder
     */
    protected function taskProcess(string $source, string $destination = '')
    {
        return $this->task(Process::class, $source, $destination);
    }

    /**
     * Replace block in a file.
     *
     * @param string $filename
     *   The file to process.
     *
     * @return \EcEuropa\Toolkit\Task\File\ReplaceBlock|\Robo\Collection\CollectionBuilder
     */
    protected function taskReplaceBlock(string $filename = '')
    {
        return $this->task(ReplaceBlock::class, $filename);
    }

}
