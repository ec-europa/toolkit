<?php

namespace EcEuropa\Toolkit\Callbacks;

use EcEuropa\Toolkit\TaskRunner\Commands\ToolCommands;

class ForbiddenFilesConditions {
    public static function grumpPhpisNotInstalled() {
        // If grumphp package is not present in a project, then grumphp config file must not be present.
        $version = ToolCommands::getPackagePropertyFromComposer('phpro/grumphp');
        if (empty($version)) {
            return TRUE;
        }
        return FALSE;
    }
}
