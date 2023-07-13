<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;

class GitleaksCommands extends AbstractCommands
{

    protected string $repo;
    protected string $tag;
    protected string $os;

    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/gitleaks.yml';
    }

    /**
     * Executes the Gitleaks.
     *
     * @command toolkit:run-gitleaks
     *
     * @option tag     The release tag of Gitleaks.
     * @option os      The current OS version.
     * @option options The options to use when executing gitleaks command.
     *
     * @aliases tk-gitleaks
     */
    public function toolkitRunGitleaks(ConsoleIO $io, array $options = [
        'tag' => InputOption::VALUE_REQUIRED,
        'os' => InputOption::VALUE_REQUIRED,
        'options' => InputOption::VALUE_REQUIRED,
    ])
    {
        $repo = $this->getConfig()->get('gitleaks.repo');
        if (!$this->download($repo, $options['tag'], $options['os'])) {
            $io->error('Fail to download Gitleaks binary.');
            return ResultData::EXITCODE_ERROR;
        }

        $command = $this->getBin('gitleaks') . ' detect ' . $options['options'];
        return $this->taskExec($command);
    }

    /**
     * Download the Gitleaks binary from the GitHub releases page.
     *
     * @param string $repo
     *   The Gitleaks repo url.
     * @param string $tag
     *   The release tag to download.
     * @param string $os
     *   The Operating system to use to download.
     */
    private function download(string $repo, string $tag, string $os): bool
    {
        $link = "$repo/releases/download/v$tag/gitleaks_{$tag}_$os.tar.gz";
        $this->writeln("Downloading from $link");
        if (file_exists($this->getBinPath('gitleaks')) || $this->isSimulating()) {
            return true;
        }
        $tmp = 'gitleaks_tmp';
        if ($file = file_get_contents($link)) {
            if (!file_exists($tmp)) {
                $this->_mkdir($tmp);
            }
            if (file_put_contents("$tmp/gitleaks.tar.gz", $file)) {
                $this->taskExtract("$tmp/gitleaks.tar.gz")->to("$tmp/gitleaks")->run();
                if (file_exists("$tmp/gitleaks/gitleaks")) {
                    $this->_copy("$tmp/gitleaks/gitleaks", $this->getBinPath('gitleaks'));
                    if (file_exists($this->getBinPath('gitleaks'))) {
                        $this->_deleteDir($tmp);
                        return true;
                    }
                }
            }
        }
        return false;
    }

}
