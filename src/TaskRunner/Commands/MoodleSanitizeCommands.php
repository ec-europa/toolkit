<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;

/**
 * Provides commands to check sanitization fields.
 */
class MoodleSanitizeCommands extends AbstractCommands
{

    /**
     * Run Sanitization.
     *
     * Sanitize the database by removing or obfuscating user data.
     *
     * @command moodle:sanitize
     *
     * @aliases md-sanitize ms
     *
     * @return int
     *   The moodle sanitize task status.
     */
    public function moodleSanitize(ConsoleIO $io): int
    {
        // Root config variable.
        $root = $this->getConfig()->get('drupal.root');

        define('CLI_SCRIPT', true);
        require_once "$root/config.php";

        try {
            $io->title('Database sanitization');
            self::sanitizeUsers();
            self::sanitizeUserInfo();
            self::sanitizeMessages();
            self::sanitizeLogs();

            $io->success('Moodle DB sanitized successfully!');
        } catch (\Throwable $e) {
            $io->error('Sanitization failed: ' . $e->getMessage() . PHP_EOL);
            return ResultData::EXITCODE_ERROR;
        }

        return ResultData::EXITCODE_OK;
    }

    /**
     * Sanitize users table.
     */
    protected function sanitizeUsers(): void
    {
        global $DB;
        $this->writeln('Sanitizing user...');
        $DB->execute(
            "UPDATE {user}
                SET password = 'not cached',
                    email = CONCAT('user', id, '@example.com'),
                    firstname = CONCAT('firstname', id),
                    lastname = CONCAT('lastname', id),
                    city = CONCAT('City', id),
                    country = CASE WHEN MOD(id, 2) = 0 THEN 'BE' ELSE 'LX' END,
                    address = CONCAT('Street ', id, ' example'),
                    institution = CONCAT('Institution ', id),
                    department = CONCAT('Dept ', MOD(id, 10)),
                    emailstop = 1,
                    firstaccess = 0,
                    lastlogin = 0,
                    currentlogin = 0,
                    picture = 0,
                    description = '',
                    lastip = '',
                    phone1 = CONCAT('+100', id),
                    phone2 = '',
                    idnumber = '',
                    imagealt = NULL,
                    lastnamephonetic = NULL,
                    firstnamephonetic = NULL,
                    middlename = NULL,
                    alternatename = NULL,
                    moodlenetprofile = NULL"
        );
    }

    /**
     * Sanitize all logs tables.
     */
    protected function sanitizeLogs(): void
    {
        global $DB;
        $this->writeln('Truncate logs tables...');
        $DB->execute('TRUNCATE TABLE {analytics_models_log}');
        $DB->execute('TRUNCATE TABLE {bigbluebuttonbn_logs}');
        $DB->execute('TRUNCATE TABLE {logstore_standard_log}');
        $DB->execute('TRUNCATE TABLE {job_error_logs}');
        $DB->execute('TRUNCATE TABLE {job_logs}');
        $DB->execute('TRUNCATE TABLE {config_log}');
        $DB->execute('TRUNCATE TABLE {log}');
        $DB->execute('TRUNCATE TABLE {log_display}');
        $DB->execute('TRUNCATE TABLE {log_queries}');
        $DB->execute('TRUNCATE TABLE {mnet_log}');
        $DB->execute('TRUNCATE TABLE {portfolio_log}');
        $DB->execute('TRUNCATE TABLE {task_log}');
        $DB->execute('TRUNCATE TABLE {upgrade_log}');
        $DB->execute('TRUNCATE TABLE {backup_logs}');
    }

    /**
     * Sanitize user info data table.
     */
    protected function sanitizeUserInfo(): void
    {
        global $DB;
        $this->writeln('Truncate user_info_data table...');
        $DB->execute('TRUNCATE TABLE {user_info_data}');
    }

    /**
     * Sanitize messages tables.
     */
    protected function sanitizeMessages(): void
    {
        global $DB;
        $this->writeln('Truncate messages tables...');
        $DB->execute('TRUNCATE TABLE {messages}');
        $DB->execute('TRUNCATE TABLE {message}');
        $DB->execute('TRUNCATE TABLE {message_read}');
    }

}
