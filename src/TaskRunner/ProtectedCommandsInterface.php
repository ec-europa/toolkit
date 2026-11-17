<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner;

/**
 * Interface for class commands to implement and provide protected commands.
 */
interface ProtectedCommandsInterface
{

    /**
     * Returns a list of command names that must not be replaced by projects.
     *
     * @return string[]
     *   A list of commands.
     */
    public static function getProtectedCommands(): array;

}
