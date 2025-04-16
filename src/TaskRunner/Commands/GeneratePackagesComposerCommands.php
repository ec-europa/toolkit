<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides command to generate composer.json files for local packages.
 */
class GeneratePackagesComposerCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/generate-packages-composer.yml';
    }

    /**
     * Generate composer.json files for each local package.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder|int
     *   Collection builder.
     *
     * @command toolkit:generate-packages-composer
     *
     * @option custom-code-folder     The location of the custom code.
     * @option skip-project-composer  Skip update of the project composer.json.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function toolkitGeneratePackagesComposer(ConsoleIO $io, array $options = [
        'custom-code-folder' => InputOption::VALUE_REQUIRED,
        'skip-project-composer' => InputOption::VALUE_NONE,
    ])
    {
        $customCodeFolder = trim($options['custom-code-folder'], '/');
        if (!is_dir($customCodeFolder)) {
            $io->error("The folder $customCodeFolder does not exist.");
            return ResultData::EXITCODE_ERROR;
        }

        $files = (new Finder())->files()
            ->name('*.info.yml')
            ->in($customCodeFolder)
            ->depth('< 4')
            ->sortByName();
        if (empty($files->count())) {
            $io->warning("The folder $customCodeFolder does not contain any package.");
            return ResultData::EXITCODE_OK;
        }

        $owner = $this->getProjectOwner();
        $results = [];
        foreach ($files as $file) {
            // Skip if the composer.json is already present.
            if (file_exists($file->getPath() . '/composer.json')) {
                continue;
            }
            $content = Yaml::parse($file->getContents());
            $results[] = [
                'path' => $file->getPath(),
                'name' => $owner . '/' . basename($file->getPath()),
                'type' => 'drupal-custom-' . ($content['type'] ?? 'module'),
                'description' => $content['description'] ?? '',
                'minimum-stability' => 'dev',
                'prefer-stable' => true,
                'require' => [
                    'drupal/core' => $content['core_version_requirement'] ?? '^10 || ^11',
                ],
            ];
        }

        if (empty($results)) {
            $io->writeln('No packages found that needs composer.json file.');
            return ResultData::EXITCODE_OK;
        }

        $rows = [];
        foreach ($results as $result) {
            $rows[] = [$result['type'], $result['name'], $result['path']];
        }
        $io->table(['Type', 'Name', 'Location'], $rows);
        if ($io->confirm('The following packages were found, do you want to proceed with composer.json creation?')) {
            $tasks = [];
            foreach ($results as $result) {
                $path = $result['path'];
                unset($result['path']);
                $tasks[] = $this
                    ->taskWriteToFile("$path/composer.json")
                    ->text(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
            }
            if (empty($options['skip-project-composer'])) {
                $composer = $this->getJson('composer.json', false);
                if (!empty($composer)) {
                    if (!isset($composer['repositories'])) {
                        $composer['repositories'] = [];
                    }
                    $packages = array_column($results, 'name');
                    $path = str_replace($customCodeFolder . '/', '', $results[0]['path']);
                    $levels = str_repeat('/*', count(explode('/', $path)));
                    $localRepoExists = $composerChanged = false;
                    foreach ($composer['repositories'] as $repository) {
                        if (!isset($repository['type']) || !isset($repository['url'])) {
                            continue;
                        }
                        if ($repository['type'] === 'path' && $repository['url'] === $customCodeFolder . $levels) {
                            $localRepoExists = true;
                            // Update existing repositories.
                            if (!isset($repository['options']['versions'])) {
                                $repository['options']['versions'] = [];
                            }
                            foreach ($results as $result) {
                                if (!isset($repository['options']['versions'][$result['name']])) {
                                    $composerChanged = true;
                                    $repository['options']['versions'][$result['name']] = '1.0.0';
                                }
                            }
                            break;
                        }
                    }
                    if (!$localRepoExists) {
                        $composerChanged = true;
                        $composer['repositories'][] = [
                            'type' => 'path',
                            'url' => $customCodeFolder . $levels,
                            'options' => [
                                'versions' => array_combine($packages, array_fill(0, count($results), '1.0.0')),
                            ],
                        ];
                    }

                    if ($composerChanged) {
                        $tasks[] = $this
                            ->taskWriteToFile('composer.json')
                            ->text(json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                        $tasks[] = $this->taskExec('composer require --no-update ' . implode(':^1.0.0 ', $packages) . ':^1.0.0')
                            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG);
                    } else {
                        $io->writeln('Nothing to update on the Project composer.json.');
                    }
                }
            }
            return $this->collectionBuilder()->addTaskList($tasks);
        }

        return ResultData::EXITCODE_OK;
    }

    /**
     * Returns the project owner.
     *
     * Try to get it from environment variable or composer.json, fallback to digit.
     *
     * @return string
     *   The project owner, i.e: digit.
     */
    private function getProjectOwner(): string
    {
        // The TOOLKIT_PROJECT_ID pattern is: <owner>-<project_id>.
        if (!empty($projectId = getenv('TOOLKIT_PROJECT_ID'))) {
            $exploded = explode('-', $projectId);
        } else {
            $composerJson = $this->getJson('composer.json', false);
            // The composer pattern on the name is: <owner>/<project_id>.
            if (!empty($composerJson['name'])) {
                $exploded = explode('/', $composerJson['name']);
            }
        }
        // Make sure the pattern is correct.
        if (!empty($exploded[0]) && !empty($exploded[1])) {
            return strtolower($exploded[0]);
        }

        return 'digit';
    }

}
