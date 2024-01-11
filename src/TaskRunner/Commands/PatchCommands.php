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

    protected $options;

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
     * @option package      List patches for given package.
     * @option composer     The composer.json relative path.
     * @option dependencies Look for patches defined by dependencies.
     *
     * @aliases tk-pl
     */
    public function toolkitPatchList(ConsoleIO $io, array $options = [
        'package' => InputOption::VALUE_REQUIRED,
        'composer' => InputOption::VALUE_REQUIRED,
        'dependencies' => InputOption::VALUE_NONE,
    ])
    {
        $this->io = $io;
        $this->options = $options;
        $io->writeln($this->getPatches());
        return ResultData::EXITCODE_OK;
    }

    /**
     * Download remote patches into a local directory.
     *
     * @command toolkit:patch-download
     *
     * @option dir          The destination directory to save the patches.
     * @option package      Download patches for given package.
     * @option composer     The composer.json file.
     * @option dependencies Look for patches defined by dependencies.
     *
     * @aliases tk-pd
     *
     * phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch
     */
    public function toolkitPatchDownload(ConsoleIO $io, array $options = [
        'dir' => InputOption::VALUE_REQUIRED,
        'package' => InputOption::VALUE_REQUIRED,
        'composer' => InputOption::VALUE_REQUIRED,
        'dependencies' => InputOption::VALUE_NONE,
    ])
    {
        $this->io = $io;
        $this->options = $options;
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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getPatches(): array
    {
        // Notify when using cweagans/composer-patches in version 2 and
        // not using option to include patches from dependencies.
        $fromDependencies = (bool) $this->options['dependencies'];
        if ($fromDependencies !== true && $this->isComposerPatchesVersion2()) {
            $this->io->warning('When using cweagans/composer-patches in version 2 is advised to use --dependencies option.');
        }

        $composer = $this->getJson($this->options['composer']);
        $patches = $composer['extra']['patches'] ?? [];
        if ($fromDependencies === true && !empty($depPatches = $this->getDependenciesPatches())) {
            $patches = array_merge($patches, $depPatches);
        }
        // Check if there's any patch.
        if (empty($patches)) {
            $this->writeln("The section 'extra.patches' is empty.");
            return [];
        }

        $downloads = [];
        if (!empty($this->options['package'])) {
            if (empty($patches[$this->options['package']])) {
                $this->writeln("The given package '{$this->options['package']}' does not contain any patch.");
                return [];
            }
            $patches = [$this->options['package'] => $composer['extra']['patches'][$this->options['package']]];
        }

        foreach ($patches as $packagePatches) {
            foreach ($packagePatches as $patch) {
                // Make sure it is a remote patch.
                if (is_string($patch) && str_starts_with($patch, 'http')) {
                    $downloads[] = $patch;
                }
            }
        }

        return $downloads;
    }

    /**
     * Check if project is using the version 2 of package cweagans/composer-patches.
     */
    private function isComposerPatchesVersion2(): bool
    {
        $version = ToolCommands::getPackagePropertyFromComposer('cweagans/composer-patches');
        return !empty($version) && str_starts_with($version, '2.');
    }

    /**
     * Returns the patches from dependencies.
     */
    private function getDependenciesPatches(): array
    {
        $patches = [];
        $composer = $this->getJson('composer.lock');
        foreach (['packages', 'packages-dev'] as $packages) {
            if (!isset($composer[$packages])) {
                continue;
            }
            foreach ($composer[$packages] as $package) {
                if (!empty($package['extra']['patches'])) {
                    foreach ($package['extra']['patches'] as $packageName => $packagePatches) {
                        $patches[$packageName] = $packagePatches;
                    }
                }
            }
        }

        return $patches;
    }

}
