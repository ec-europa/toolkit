<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;

/**
 * Provides a setup command for user escalation script.
 */
class UserEscalationCommands extends AbstractCommands
{

    /**
     * Perform the setup of the user escalation script.
     *
     * @command toolkit:setup-user-escalation
     *
     * @hidden true
     *
     * @return int|\Robo\Collection\CollectionBuilder
     *   The tasks to execute, or exit code.
     */
    public function toolkitSetupUserEscalation(ConsoleIO $io)
    {
        $config = $this->getConfig()->get('gitlab');
        if (empty($token = $config['token'])) {
            $io->error('Missing env var GITLAB_API_TOKEN.');
            return ResultData::EXITCODE_ERROR;
        }

        $context = stream_context_create(['http' => ['header' => "Authorization: Bearer $token"]]);
        $content = file_get_contents($config['endpoints']['user_escalation'], false, $context);
        if (empty($content)) {
            $io->error('Failed to get the script from GitLab.');
            return ResultData::EXITCODE_ERROR;
        }
        $dist = $this->getConfigValue('toolkit.build.dist.root');
        $web = $this->getConfigValue('drupal.root');
        $task = $this
            ->taskWriteToFile("$dist/$web/qa-api.php")
            ->text($content);
        return $this->collectionBuilder()->addTask($task);
    }

}
