<?php

namespace EcEuropa\Toolkit\Helpers;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;

class ProjectInfo extends AbstractCommands
{
    /**
     * Helper function to get enabled Drupal modules.
     */
    public function GetEnabledDrupalModules() {
        $enabledModules = [];
        $result = $this->taskExec($this->getBin('drush') . ' pm-list --fields=status --format=json')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        $projPackages = json_decode($result, true);
        if (!empty($projPackages)) {
            $enabledModules = array_keys(array_filter($projPackages, function ($item) {
                return $item['status'] === 'Enabled';
            }));
        }
        return $enabledModules;
    }
}
