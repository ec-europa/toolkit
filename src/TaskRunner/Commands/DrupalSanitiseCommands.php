<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Autoload\ClassLoader;
use Composer\ClassMapGenerator\ClassMapGenerator;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;

/**
 * Provides commands to check sanitisation fields.
 */
class DrupalSanitiseCommands extends AbstractCommands
{

    /**
     * The Symfony Input Output.
     *
     * @var \Robo\Symfony\ConsoleIO
     */
    protected $io;

    /**
     * Command to check fields for sanitisation.
     *
     * @param array $options
     *   Command options.
     *
     * @command drupal:check-sanitisation-fields
     *
     * @option types    The field types to check.
     * @option keywords The keywords to look into field names.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function drupalCheckSanitisationFields(ConsoleIO $io, array $options = [
        'types' => InputOption::VALUE_REQUIRED,
        'keywords' => InputOption::VALUE_REQUIRED,
    ]): int
    {
        $this->io = $io;
        if (!$this->isWebsiteInstalled()) {
            $io->writeln('Website not installed, skipping.');
            return ResultData::EXITCODE_OK;
        }
        $command = "\Drupal::service('entity_field.manager')->getFieldMap()";
        $result = $this->taskExec($this->getBin('drush') . ' eval "echo json_encode(' . $command . ')"')
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run()->getMessage();
        if (empty($result)) {
            $io->writeln('No fields found, skipping.');
            return ResultData::EXITCODE_OK;
        }

        $fieldNames = $fieldTypes = [];
        $map = json_decode($result, true);
        Toolkit::ensureArray($options['types']);
        Toolkit::ensureArray($options['keywords']);
        $keywordsRegex = implode('|', $options['keywords']);

        // Skip the user fields if the drush User sanitize is present and no skips are present.
        if ($this->areUserFieldsSanitised()) {
            unset($map['user']);
        }
        // Skip the comment fields if the drush Comments sanitize is present.
        if ($this->areCommentFieldsSanitised()) {
            unset($map['comment']);
        }

        foreach ($map as $entityType => $fields) {
            foreach ($fields as $fieldName => $definition) {
                // Completely ignore the boolean type of fields as they do not contain any relevant data.
                if (empty($definition['type']) || $definition['type'] === 'boolean') {
                    continue;
                }
                if (in_array($definition['type'], $options['types'])) {
                    $fieldTypes[] = "$entityType-$fieldName ({$definition['type']})";
                }
                if (preg_match('/' . $keywordsRegex . '/', $fieldName) > 0) {
                    $fieldNames[] = "$entityType-$fieldName ({$definition['type']})";
                }
            }
        }

        $io->title('Field types that should be sanitised:');
        $io->listing($fieldTypes);
        $io->title('Fields that should be sanitised by their name:');
        $io->listing($fieldNames);
        return ResultData::EXITCODE_OK;
    }

    /**
     * Command to check existence of Sanitisation classes.
     *
     * @command drupal:check-sanitisation-classes
     */
    public function drupalCheckSanitisationClasses(ConsoleIO $io)
    {
        $interface = '\Drush\Drupal\Commands\sql\SanitizePluginInterface';
        if (!interface_exists($interface)) {
            $io->warning("Interface class $interface was not found, skipping.");
            return ResultData::EXITCODE_OK;
        }
        $src = $this->getConfig()->get('toolkit.build.custom-code-folder');
        if (!file_exists($src)) {
            $io->warning("The directory $src could not be found, skipping.");
            return ResultData::EXITCODE_OK;
        }

        $sanitiseClasses = [];
        $this->registerCustomClasses($src);
        $map = ClassMapGenerator::createMap($src);
        foreach (array_keys($map) as $class) {
            // Check if the class implements the SanitizePluginInterface.
            // Ignore errors thrown by some classes due to missing dependencies,
            // Toolkit requires Drush and the drush commands classes do not throw any error.
            try {
                if (is_a($class, $interface, true)) {
                    $sanitiseClasses[] = $class;
                }
                // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
            } catch (\Error $error) {
                // Do nothing.
            }
        }
        if (empty($sanitiseClasses)) {
            $io->error("Could not find any class implementing the interface $interface.");
            return ResultData::EXITCODE_ERROR;
        }

        $io->title('Classes for Sanitisation:');
        $io->listing($sanitiseClasses);

        return ResultData::EXITCODE_OK;
    }

    /**
     * Register all classes in custom code directory folder that are not registered.
     *
     * This assumes the Drupal namespace \Drupal\[module].
     *
     * @param string $directory
     *   The directory to search for classes.
     */
    private function registerCustomClasses(string $directory)
    {
        $registered = [];
        $map = ClassMapGenerator::createMap($directory);
        $loader = new ClassLoader();
        foreach ($map as $class => $path) {
            // Ignore if the class namespace do not start with Drupal or
            // if is already registered.
            if (!str_starts_with($class, 'Drupal\\') || class_exists($class)) {
                continue;
            }
            $namespaceExploded = explode('\\', $class);
            $namespacePrefix = $namespaceExploded[0] . '\\' . $namespaceExploded[1] . '\\';
            // Skip if we already registered this namespace, we only need to
            // register one namespace per module, avoid to register all classes
            // inside the same namespace.
            if (in_array($namespacePrefix, $registered)) {
                continue;
            }
            $modulePath = strstr($path, $namespaceExploded[1], true) . $namespaceExploded[1];
            $loader->addPsr4($namespacePrefix, $modulePath . '/src');
            $registered[] = $namespacePrefix;
        }
        $loader->register();
    }

    /**
     * Checks if the User fields are being sanitised.
     *
     * Validates the presence of the SanitizeUserTableCommands command and
     * ensure that the fields are not being ignored in the opts.yml file with the
     * options --sanitize-password and --sanitize-email.
     */
    protected function areUserFieldsSanitised(): bool
    {
        // Fail if the command is not found.
        if (!method_exists('\Drush\Drupal\Commands\sql\SanitizeUserTableCommands', 'sanitize')) {
            return false;
        }
        // Check if the mail and pass fields are being ignored.
        $opts = ToolCommands::parseOptsYml();
        // By default, the fields are sanitised, skip if not options are defined.
        if (empty($value = $opts['dump_options'][0]['SANITIZE_OPTS'])) {
            return true;
        }

        $return = true;
        if (preg_match($this->optionPattern('sanitize-password'), $value)) {
            $this->io->warning('Detected usage of --sanitize-password=no in .opts.yml file.');
            $return = false;
        }
        if (preg_match($this->optionPattern('sanitize-email'), $value)) {
            $this->io->warning('Detected usage of --sanitize-email=no in .opts.yml file.');
            $return = false;
        }
        return $return;
    }

    /**
     * Checks if the Comment fields are being sanitised.
     *
     * Validates the presence of the SanitizeCommentsCommands command.
     */
    protected function areCommentFieldsSanitised(): bool
    {
        return method_exists('\Drush\Drupal\Commands\sql\SanitizeCommentsCommands', 'sanitize');
    }

    /**
     * Prepare regex to check if option exists.
     *
     * @param string $option
     *   The option name to check, i.e: sanitize-email.
     */
    protected function optionPattern(string $option): string
    {
        return '/(--' . $option . '?( |=|="|=\')no)/';
    }

}
