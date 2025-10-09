<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use Robo\Collection\CollectionBuilder;
use Symfony\Component\Console\Input\InputOption;

/**
 * Moodle commands to setup, build and test a Moodle website.
 */
class MoodleCommands extends AbstractCommands
{

    /**
     * Comment ending the Toolkit settings block.
     *
     * @var string
     */
    protected string $blockEnd = '// End Toolkit settings block.';

    /**
     * Comment starting the Toolkit settings block.
     *
     * @var string
     */
    protected string $blockStart = '// Start Toolkit settings block.';

    /**
     * Generate Moodle config.php file in compliance with Toolkit conventions.
     *
     * This command will:
     *
     * - Copy "config-dist.php" to "config.php", which will be overridden
     *   if existing
     * - Add database and config directory settings using environment variables
     *
     * You can specify additional config.php portions in your local
     * runner.yml.dist as shown below:
     *
     * > moodle:
     * >   additional_settings: |
     * >   $CFG->dbhost = getenv('MOODLE_DB_HOST');
     * >   $CFG->dbname = getenv('MOODLE_DB_NAME');
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @command moodle:generate-config
     *
     * @option root                   Drupal root.
     * @option skip-permissions-setup Drupal skip permissions setup.
     */
    public function moodleGenerateConfig(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'skip-permissions-setup' => false,
    ]): CollectionBuilder
    {
        // Get config-dist.php and config.php paths.
        $configDefaultPath = $options['root'] . '/config-dist.php';
        $configPath = $options['root'] . '/config.php';
        $tasks = [];

        // Copy config-dist.php on config.php, if the latter does not exist.
        if (!file_exists($configPath)) {
            $tasks[] = $this->taskFilesystemStack()
                ->copy($configDefaultPath, $configPath);
        }

        // Remove Toolkit settings block, if any.
        $tasks[] = $this->taskReplaceInFile($configPath)
            ->regex($this->getSettingsBlockRegex())
            ->to('');

        // Remove "require" statements, if any.
        $tasks[] = $this->taskReplaceInFile($configPath)
            ->regex($this->getRequireStatementsRegex())
            ->to('');

        // Append Toolkit settings block to config.php file.
        $tasks[] = $this->taskWriteToFile($configPath)
            ->append()
            ->text($this->getToolkitSettingsBlock());

        // Set necessary permissions on the config.php.
        if (!$options['skip-permissions-setup']) {
            $tasks[] = $this->taskFilesystemStack()->chmod($configPath, octdec('644'));
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Remove settings block from given content.
     *
     * @return string
     *   Content without setting block.
     */
    protected function getSettingsBlockRegex(): string
    {
        return '/^\n' . preg_quote($this->blockStart, '/') . '.*?' . preg_quote($this->blockEnd, '/') . '\n/sm';
    }

    /**
     * Remove require statements from given content.
     *
     * @return string
     *   Content without require statements.
     */
    protected function getRequireStatementsRegex(): string
    {
        return '/require_once\s*\(.*\).*$/m';
    }

    /**
     * Helper function to update config.php file.
     *
     * @return string
     *   Database configuration to be attached to Drupal config.php.
     */
    protected function getToolkitSettingsBlock(): string
    {
        $additionalSettings = $this->getConfig()->get('moodle.additional_settings', '');
        $additionalSettings = trim($additionalSettings);

        return <<<EOF

{$this->blockStart}

{$additionalSettings}

// Load environment development override configuration, if available.
// Keep this code block at the end of this file to take full effect.
if (file_exists(\$CFG->dirroot . '/config.override.php')) {
  include \$CFG->dirroot . '/config.override.php';
}

{$this->blockEnd}

EOF;
    }

}
