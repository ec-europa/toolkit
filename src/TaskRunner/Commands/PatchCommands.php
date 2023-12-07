<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;

/**
 * Commands for patch download and list.
 *
 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassAfterLastUsed
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PatchCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/patch.yml';
    }

    /**
     * Lists remote patches from the root composer.json.
     *
     * @command toolkit:patch-list
     *
     * @option package  List patches for given package.
     * @option composer The composer.json relative path.
     *
     * @aliases tk-pl
     */
    public function toolkitPatchList(ConsoleIO $io, array $options = [
        'package' => InputOption::VALUE_REQUIRED,
        'composer' => InputOption::VALUE_REQUIRED,
    ])
    {
        $io->writeln($this->getPatches());
        return ResultData::EXITCODE_OK;
    }

    /**
     * Download remote patches into a local directory.
     *
     * @command toolkit:patch-download
     *
     * @option dir      The destination directory to save the patches.
     * @option package  Download patches for given package.
     * @option composer The composer.json file.
     *
     * @aliases tk-pd
     *
     * phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch
     */
    public function toolkitPatchDownload(ConsoleIO $io, array $options = [
        'dir' => InputOption::VALUE_REQUIRED,
        'package' => InputOption::VALUE_REQUIRED,
        'composer' => InputOption::VALUE_REQUIRED,
    ])
    {
        if (empty($downloads = $this->getPatches())) {
            $this->writeln('Nothing to download.');
            return ResultData::EXITCODE_OK;
        }

        $tasks = [];
        $dir = $this->getWorkingDir() . '/' . trim($options['dir'], '/');
        if (!file_exists($dir)) {
            $tasks[] = $this->taskFilesystemStack()->mkdir($dir);
        }

        foreach ($downloads as $download) {
            $tasks[] = $this->collectionBuilder()->addCode(function () use ($io, $dir, $download) {
                $io->writeln($download);
                // Avoid downloads in simulation mode.
                if ($this->isSimulating()) {
                    return;
                }
                $filename = basename($download);
                try {
                    file_put_contents("$dir/$filename", file_get_contents($download));
                } catch (\Exception $exception) {
                    // Do nothing if the patch fails to download.
                }
            });
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Returns the patches to be downloaded.
     */
    private function getPatches(): array
    {
        $composer = $this->getComposerJson();
        $patches = $composer['extra']['patches'] ?? [];
        // Check if there's any patch.
        if (empty($patches)) {
            $this->writeln("The section 'extra.patches' is empty.");
            return [];
        }

        $downloads = [];
        $package = $this->input()->getOption('package');
        if (!empty($package)) {
            if (empty($patches[$package])) {
                $this->writeln("The given package '$package' does not contain any patch.");
                return [];
            }
            $patches = [$package => $composer['extra']['patches'][$package]];
        }

        foreach ($patches as $packagePatches) {
            foreach ($packagePatches as $patch) {
                // Make sure it is a remote patch.
                if (str_starts_with($patch, 'http')) {
                    $downloads[] = $patch;
                }
            }
        }

        return $downloads;
    }

}
