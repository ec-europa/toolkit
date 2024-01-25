<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Callbacks;

use EcEuropa\Toolkit\TaskRunner\Commands\ToolCommands;

/**
 * Callbacks for forbidden files.
 */
class ForbiddenFilesConditions
{

    /**
     * If grumphp package is not present in a project, then grumphp config file must not be present.
     */
    public static function grumpPhpisNotInstalled()
    {
        $version = ToolCommands::getPackagePropertyFromComposer('phpro/grumphp');
        if (empty($version)) {
            return true;
        }
        return false;
    }

}
