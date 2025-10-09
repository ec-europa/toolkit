<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\JunitXmlGenerator;
use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command class for moodle:code-review and moodle:run-tests.
 */
class MoodleCodeReviewCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/code-review.yml';
    }

    /**
     * Execute all or specific tools for static testing.
     *
     * If no option is given, all the tests will be executed.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command moodle:code-review
     *
     * @option phpcs          Execute the command toolkit:test-phpcs.
     * @option phpstan        Execute the command toolkit:test-phpstan.
     * @option phpmd          Execute the command toolkit:test-phpmd.
     * @option opts-review    Execute the command toolkit:opts-review.
     * @option lint-php       Execute the command toolkit:lint-php.
     * @option lint-yaml      Execute the command toolkit:lint-yaml.
     * @option lint-js        Execute the command toolkit:lint-js.
     * @option lint-css       Execute the command toolkit:lint-css.
     * @option lint-cspell    Execute the command toolkit:lint-cspell.
     * @option lint-behat     Execute the command toolkit:lint-behat.
     * @option complock-check Execute the command toolkit:complock-check.
     * @option requirements   Execute the command toolkit:requirements.
     * @option junit          Whether to export results as junit.
     *
     * @return \Robo\ResultData
     *   The toolkit code-review command status.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function toolkitCodeReview(ConsoleIO $io, array $options = [
        'phpcs' => InputOption::VALUE_NONE,
        'phpstan' => InputOption::VALUE_NONE,
        'phpmd' => InputOption::VALUE_NONE,
        'opts-review' => InputOption::VALUE_NONE,
        'lint-php' => InputOption::VALUE_NONE,
        'lint-yaml' => InputOption::VALUE_NONE,
        'lint-js' => InputOption::VALUE_NONE,
        'lint-css' => InputOption::VALUE_NONE,
        'lint-cspell' => InputOption::VALUE_NONE,
        'lint-behat' => InputOption::VALUE_NONE,
        'complock-check' => InputOption::VALUE_NONE,
        'requirements' => InputOption::VALUE_NONE,
    ])
    {
        $tasks = [
            'PHPcs' => ['cmd' => 'tk-phpcs', 'exec' => $options['phpcs'] === true, 'result' => []],
            'PHPStan' => ['cmd' => 'tk-phpstan', 'exec' => $options['phpstan'] === true, 'result' => []],
            'PHPMD' => ['cmd' => 'tk-phpmd', 'exec' => $options['phpmd'] === true, 'result' => []],
            'Opts review' => ['cmd' => 'tk-opts-review', 'exec' => $options['opts-review'] === true, 'result' => []],
            'Lint PHP' => ['cmd' => 'tk-php', 'exec' => $options['lint-php'] === true, 'result' => []],
            'Lint YAML' => ['cmd' => 'tk-yaml', 'exec' => $options['lint-yaml'] === true, 'result' => []],
            'Lint JS' => ['cmd' => 'tk-js', 'exec' => $options['lint-js'] === true, 'result' => []],
            'Lint CSS' => ['cmd' => 'tk-css', 'exec' => $options['lint-css'] === true, 'result' => []],
            'Lint CSpell' => ['cmd' => 'tk-cspell', 'exec' => $options['lint-cspell'] === true, 'result' => []],
            'Lint Behat' => ['cmd' => 'tk-lbehat', 'exec' => $options['lint-behat'] === true, 'result' => []],
            'Composer lock' => ['cmd' => 'toolkit:complock-check', 'exec' => $options['complock-check'] === true, 'result' => []],
            'Requirements' => ['cmd' => 'tk-req', 'exec' => $options['requirements'] === true, 'result' => []],
        ];
        $exit = 0;
        $runAll = false;
        // If no option is given, run all commands.
        if (empty(array_filter(array_column($tasks, 'exec')))) {
            $runAll = true;
        }
        $run = $this->getBin('run');
        foreach ($tasks as $name => &$task) {
            if ($this->isJunit()) {
                JunitXmlGenerator::addTestCase('Code Review', $name);
            }
            if ($runAll || $task['exec']) {
                $code = $this->taskExec($run)->arg($task['cmd'])->run()->getExitCode();
                $task['result'] = [$name => $code > 0 ? 'failed' : 'passed'];
                $exit += $code;
                $io->newLine(2);

                if ($code > 0 && $this->isJunit()) {
                    JunitXmlGenerator::addResult('Code Review', $name, 'Failed');
                }
            } else {
                $task['result'] = [$name => 'skip'];
            }
        }
        $io->title('Results:');
        $io->definitionList(
            $tasks['PHPcs']['result'],
            $tasks['PHPStan']['result'],
            $tasks['PHPMD']['result'],
            $tasks['Opts review']['result'],
            $tasks['Lint PHP']['result'],
            $tasks['Lint YAML']['result'],
            $tasks['Lint JS']['result'],
            $tasks['Lint CSS']['result'],
            $tasks['Lint CSpell']['result'],
            $tasks['Lint Behat']['result'],
            $tasks['Composer lock']['result'],
            $tasks['Requirements']['result'],
        );

        if ($this->isJunit()) {
            JunitXmlGenerator::generate('junit-code-review.xml');
        }

        return new ResultData($exit);
    }

    /**
     * Execute all or specific tools for testing.
     *
     * If no option is given, all the tests will be executed.
     *
     * @param array<mixed> $options
     *   Command options.
     *
     * @command moodle:run-tests
     *
     * @option phpunit    Execute the command toolkit:test-phpunit.
     * @option behat      Execute the command toolkit:run-behat.
     * @option blackfire  Execute the command toolkit:run-blackfire.
     * @option components Execute the command toolkit:component-check.
     * @option junit      Whether to export results as junit.
     *
     * @return \Robo\ResultData
     *   The toolkit run-tests command status.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function toolkitRunTests(ConsoleIO $io, array $options = [
        'phpunit' => InputOption::VALUE_NONE,
        'behat' => InputOption::VALUE_NONE,
        'blackfire' => InputOption::VALUE_NONE,
        'components' => InputOption::VALUE_NONE,
    ])
    {
        $tasks = [
            'PHPUnit' => ['cmd' => 'tk-phpunit', 'exec' => $options['phpunit'] === true, 'result' => []],
            'Behat' => ['cmd' => 'tk-behat', 'exec' => $options['behat'] === true, 'result' => []],
            'Blackfire' => ['cmd' => 'tk-bfire', 'exec' => $options['blackfire'] === true, 'result' => []],
            'Components' => ['cmd' => 'md-components', 'exec' => $options['components'] === true, 'result' => []],
        ];
        $exit = 0;
        $runAll = false;
        // If no option is given, run all commands.
        if (empty(array_filter(array_column($tasks, 'exec')))) {
            $runAll = true;
        }
        $run = $this->getBin('run');
        foreach ($tasks as $name => &$task) {
            if ($this->isJunit()) {
                JunitXmlGenerator::addTestCase('Run Tests', $name);
            }
            if ($runAll || $task['exec']) {
                $code = $this->taskExec($run)->arg($task['cmd'])->run()->getExitCode();
                $task['result'] = [$name => $code > 0 ? 'failed' : 'passed'];
                $exit += $code;
                $io->newLine(2);

                if ($code > 0 && $this->isJunit()) {
                    JunitXmlGenerator::addResult('Run Tests', $name, 'Failed');
                }
            } else {
                $task['result'] = [$name => 'skip'];
            }
        }
        $io->title('Results:');
        $io->definitionList(
            $tasks['PHPUnit']['result'],
            $tasks['Behat']['result'],
            $tasks['Blackfire']['result'],
            $tasks['Components']['result'],
        );

        if ($this->isJunit()) {
            JunitXmlGenerator::generate('junit-run-tests.xml');
        }

        return new ResultData($exit);
    }

}
