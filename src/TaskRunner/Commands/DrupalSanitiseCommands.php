<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

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
     * Checks if the User fields are being sanitised.
     *
     * Validates the presence of the SanitizeUserTableCommands command and
     * ensure that the fields are not being ignored in the opts.yml file with the
     * options --sanitize-password and --sanitize-email.
     */
    public static function areUserFieldsSanitised(): bool
    {
        // Fail if the command is not found.
        if (!method_exists('\Drush\Drupal\Commands\sql\SanitizeUserTableCommands', 'sanitize')) {
            return false;
        }
        // Check if the mail and pass fields are being ignored.
        if (empty($opts = ToolCommands::parseOptsYml())) {
            return true;
        }
        // By default, the fields are sanitised, skip if not options are defined.
        if (empty($opts['dump_options'][0]['SANITIZE_OPTS'])) {
            return true;
        }
        $value = $opts['dump_options'][0]['SANITIZE_OPTS'];
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
        return method_exists('\Drush\Drupal\Commands\sql\SanitizeCommentsCommands', 'sanitize');
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
