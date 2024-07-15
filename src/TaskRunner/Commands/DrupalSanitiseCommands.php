<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Autoload\ClassLoader;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
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
        'types' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'keywords' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'types-ignore' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
    ]): int
    {
        $this->io = $io;
        // Add some fields for testing purposes.
        if ($this->isSimulating()) {
            $map = $this->testMap();
        } else {
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
            $map = json_decode($result, true);
        }

        $fieldNames = $fieldTypes = [];
        $keywordsRegex = implode('|', $options['keywords']);

        // Skip the user fields if the drush User sanitize is present and no skips are used.
        if ($this->areUserFieldsSanitised()) {
            unset($map['user']);
        }
        // Skip the comment fields if the drush Comments sanitize is present.
        if ($this->areCommentFieldsSanitised()) {
            unset($map['comment']);
        }

        foreach ($map as $entityType => $fields) {
            foreach ($fields as $fieldName => $definition) {
                // Ignore specific types of fields as they do not contain any relevant data.
                if (empty($definition['type']) || in_array($definition['type'], $options['types-ignore'])) {
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
        $interface = $this->getDrushSanitizeInterface();
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
        $map = $this->createClassMap($src);
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
     * Checks if the User fields are being sanitised.
     *
     * Validates the presence of the SanitizeUserTableCommands command and
     * ensure that the fields are not being ignored in the opts.yml file with the
     * options --sanitize-password and --sanitize-email.
     */
    public static function areUserFieldsSanitised(): bool
    {
        // Fail if the command is not found.
        if (
            // Drush <=12. @phpstan-ignore function.impossibleType
            !method_exists('\Drush\Drupal\Commands\sql\SanitizeUserTableCommands', 'sanitize')
            // Drush 13. @phpstan-ignore function.impossibleType
            && !method_exists('\Drush\Commands\sql\sanitize\SanitizeUserTableCommands', 'sanitize')
        ) {
            return false;
        }
        // Check if the mail and pass fields are being ignored.
        if (empty($opts = ToolCommands::parseOptsYml())) {
            return true;
        }
        // By default, the fields are sanitised, skip if not options are defined.
        if (empty($opts['dump_options']['SANITIZE_OPTS'])) {
            return true;
        }
        $value = $opts['dump_options']['SANITIZE_OPTS'];
        if (preg_match(self::optionPattern('sanitize-password'), $value)) {
            return false;
        }
        if (preg_match(self::optionPattern('sanitize-email'), $value)) {
            return false;
        }
        if (str_contains($value, 'ignored-roles')) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the Comment fields are being sanitised.
     *
     * Validates the presence of the SanitizeCommentsCommands command.
     */
    public static function areCommentFieldsSanitised(): bool
    {
        // Drush <=12. @phpstan-ignore function.impossibleType
        return method_exists('\Drush\Drupal\Commands\sql\SanitizeCommentsCommands', 'sanitize')
            // Drush 13. @phpstan-ignore function.impossibleType
            || method_exists('\Drush\Commands\sql\sanitize\SanitizeCommentsCommands', 'sanitize');
    }

    /**
     * Prepare regex to check if option exists with value "no".
     *
     * @param string $option
     *   The option name to check, i.e: sanitize-email.
     */
    public static function optionPattern(string $option): string
    {
        return '/(--' . $option . '?( |=|="|=\')no)/';
    }

    /**
     * Returns the Drush Sanitize Interface namespace depending on the current version.
     */
    private function getDrushSanitizeInterface(): string
    {
        // Get the current drush version.
        $version = ToolCommands::getPackagePropertyFromComposer('drush/drush');
        if (str_starts_with($version ?: '', '13')) {
            return 'Drush\Commands\sql\sanitize\SanitizePluginInterface';
        }
        return 'Drush\Drupal\Commands\sql\SanitizePluginInterface';
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
        $map = $this->createClassMap($directory);
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
     * Generates a class map for given directory.
     *
     * Only consider classes inside the src folder.
     *
     * @param string $directory
     *   The directory to scan, usually 'lib'.
     */
    private function createClassMap(string $directory): array
    {
        $classes = [];
        $iterator = new \RecursiveDirectoryIterator($directory);
        foreach (new \RecursiveIteratorIterator($iterator) as $file) {
            if (!$file->isDir() && $file->getExtension() === 'php' && strpos($file->getPathname(), 'src')) {
                $filePath = $file->getPathname();
                $className = str_replace('.php', '', $file->getFilename());
                $ns = dirname(substr($filePath, (strpos($filePath, 'src') + 4)));
                $module = basename(strstr($filePath, 'src', true));
                $namespace = 'Drupal\\' . $module . '\\' . $ns;
                $classes[$namespace . '\\' . $className] = realpath($filePath);
            }
        }
        return $classes;
    }

    /**
     * Returns a static field map for test purposes.
     */
    private function testMap(): array
    {
        return [
            'user' => [
                'location' => ['type' => 'address'],
                'password' => ['type' => 'password'],
                'email' => ['type' => 'email'],
            ],
            'node' => [
                'field_test_email' => ['type' => 'boolean'],
                'field_test_name' => ['type' => 'string'],
                'field_test_token' => ['type' => 'string'],
                'field_test_auth' => ['type' => 'boolean'],
                'field_test_new' => ['type' => 'string'],
                'field_test_custom_type' => ['type' => 'custom_type'],
            ],
        ];
    }

}
