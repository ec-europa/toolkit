<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Symfony\Component\Console\Input\InputOption;

class SymlinkProjectCommands extends AbstractCommands
{

    /**
     * A mapping of Drupal project type to directory.
     *
     * @var string[]
     */
    protected $types = [
        'drupal-module' => 'modules/custom',
        'drupal-theme' => 'themes/custom',
        'drupal-profile' => 'profiles/custom',
    ];

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/symlink-project.yml';
    }

    /**
     * Validate command drupal:symlink-project.
     *
     * @hook validate drupal:symlink-project
     *
     * @throws \Exception
     *   Thrown when some configuration is missing or is wrong.
     */
    public function symlinkProjectValidate(): void
    {
        $composer = $this->getComposer();
        // Check if the project name is present.
        if (empty($composer['name'])) {
            throw new \Exception('Could not find the project name in the composer.json file.');
        }
        // Check if the project_id is valid.
        $project = explode('/', $composer['name']);
        if (empty($project[1]) || !is_array($project)) {
            throw new \Exception('Could not find the project id in the composer.json file.');
        }
        // Check if the project type is valid.
        if (empty($composer['type']) || !isset($this->types[$composer['type']])) {
            throw new \Exception("The project type '{$composer['type']}' is not valid.");
        }
    }

    /**
     * Symlink project as module, theme or profile in the proper directory.
     *
     * @param array $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @command drupal:symlink-project
     *
     * @option root   Drupal root.
     * @option ignore List of files to ignore.
     */
    public function symlinkProject(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'ignore' => InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
    ])
    {
        Toolkit::ensureArray($options['ignore']);
        $workingDir = $this->getConfig()->get('runner.working_dir');
        $composer = $this->getComposer();
        $projectId = explode('/', $composer['name'])[1];
        $projectType = $this->types[$composer['type']];
        $destination = $workingDir . '/' . $options['root'] . '/' . $projectType . '/' . $projectId;
        $task = $this->taskFilesystemStack();

        // Clean up project's folder.
        $task->remove($destination)->mkdir($destination);

        // Gather files to symlink excluding the ignores.
        $ignore = array_merge([$options['root']], $options['ignore']);
        $symlinks = $this->scanDir($workingDir, $ignore);

        foreach ($symlinks as $symlink) {
            $task->symlink($workingDir . '/' . $symlink, $destination . '/' . $symlink);
        }

        return $this->collectionBuilder()->addTask($task);
    }

    private function getComposer()
    {
        $composer = $this->getWorkingDir() . '/composer.json';
        if (!file_exists($composer)) {
            throw new \Exception("The $composer was not found.");
        }
        return json_decode(file_get_contents($composer), true);
    }

    private function scanDir(string $directory, array $ignore = []): array
    {
        $ignore = array_merge(['.', '..', '.git'], $ignore);
        return array_diff(scandir($directory), $ignore);
    }

}
