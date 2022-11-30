<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use EcEuropa\Toolkit\Website;
use Robo\Exception\TaskException;
use Robo\ResultData;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Commands to lint the source code and interact with ESLint.
 */
class LintCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/lint.yml';
    }

    /**
     * Setup the ESLint configurations and dependencies.
     *
     * Check configurations at config/default.yml - 'toolkit.lint.eslint'.
     *
     * @command toolkit:setup-eslint
     *
     * @option config      The eslint config file.
     * @option ignores     The patterns to ignore.
     * @option drupal-root The drupal root.
     * @option packages    The npm packages to install.
     * @option force       If true, the config file will be deleted.
     *
     * @return int
     */
    public function toolkitSetupEslint(array $options = [
        'config' => InputOption::VALUE_OPTIONAL,
        'ignores' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'drupal-root' => InputOption::VALUE_OPTIONAL,
        'packages' => InputOption::VALUE_OPTIONAL,
        'force' => false,
    ])
    {
        $actions = false;
        $config = $options['config'];
        if ($options['force'] && file_exists($config)) {
            $actions = true;
            $this->taskExec('rm')->arg($config)->run();
        }

        // Create a package.json if it doesn't exist.
        if (!file_exists('package.json')) {
            $actions = true;
            $this->taskExec('npm ini -y')->run();
            $this->taskExec("npm install --save-dev {$options['packages']} -y")->run();
        }

        // Check if the binary exists.
        try {
            $this->getNodeBin('eslint');
        } catch (TaskException $e) {
            $actions = true;
            $this->taskExec('npm install')->run();
        }

        if (!file_exists($config)) {
            $actions = true;
            $this->generateEslintConfigurations($config, $options);
        }

        // Ignore all yaml files for prettier.
        if (!file_exists('.prettierignore')) {
            $actions = true;
            $this->taskWriteToFile('.prettierignore')->text('*.yml')->run();
        }

        if (!$actions) {
            $this->say('No actions needed.');
        }

        return ResultData::EXITCODE_OK;
    }

    /**
     * Generate configurations for ESLint.
     *
     * @param string $config
     *   The path for the configuration file.
     * @param array $options
     *   The options passed to the command.
     */
    private function generateEslintConfigurations(string $config, array $options)
    {
        $data = [
            'ignorePatterns' => $options['ignores'],
            // The docker-compose file makes use of
            // empty mappings in env variables.
            'overrides' => [
                [
                    'files' => ['docker-compose*.yml'],
                    'rules' => ['yml/no-empty-mapping-value' => 'off'],
                ],
            ],
        ];

        // Check if we have a Drupal environment.
        $drupal_core = './' . $options['drupal-root'] . '/core';
        if (file_exists($drupal_core)) {
            // Add the drupal core eslint if it exists.
            $drupal_eslint = './' . $options['drupal-root'] . '/core/.eslintrc.json';
            if (file_exists($drupal_eslint)) {
                $data['extends'] = $drupal_eslint;
            }

            // Copy the prettier configurations from Drupal or fallback to defaults.
            $prettier = './' . $options['drupal-root'] . '/core/.prettierrc.json';
            $prettier = file_exists($prettier)
                ? json_decode(file_get_contents($prettier), true)
                : ['singleQuote' => true, 'printWidth' => 80, 'semi' => true, 'trailingComma' => 'all'];
            $data['rules'] = [
                'prettier/prettier' => ['error', $prettier],
            ];
        }

        $this->collectionBuilder()->addCode(function () use ($config, $data) {
            $this->output()->writeln(" <fg=white;bg=cyan;options=bold>[File\Write]</> Writing to $config.<info></>");
            file_put_contents($config, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        })->run();
    }

    /**
     * Run lint YAML.
     *
     * Check configurations at config/default.yml - 'toolkit.lint.eslint'.
     *
     * @command toolkit:lint-yaml
     *
     * @option config     The eslint config file.
     * @option extensions The extensions to check.
     * @option options    Extra options for the command without -- (only options with no value).
     *
     * @aliases tly, tk-yaml
     *
     * @usage --extensions='.yml' --options='fix no-eslintrc'
     */
    public function toolkitLintYaml(array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'extensions' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
        return $this->toolkitRunEsLint($options['config'], $options['extensions'], $options['options']);
    }

    /**
     * Run lint JS.
     *
     * Check configurations at config/default.yml - 'toolkit.lint.eslint'.
     *
     * @command toolkit:lint-js
     *
     * @option config     The eslint config file.
     * @option extensions The extensions to check.
     * @option options    Extra options for the command without -- (only options with no value).
     *
     * @aliases tljs, tk-js
     *
     * @usage --extensions='.js' --options='fix no-eslintrc'
     */
    public function toolkitLintJs(array $options = [
        'config' => InputOption::VALUE_REQUIRED,
        'extensions' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
        return $this->toolkitRunEsLint($options['config'], $options['extensions'], $options['options']);
    }

    /**
     * Execute the eslint.
     *
     * @param string $config
     *   The eslint config file.
     * @param array $extensions
     *   The extensions to check.
     * @param string $options
     *   Extra options for the command.
     *
     * @see toolkitLintYaml()
     * @see toolkitLintJs()
     */
    private function toolkitRunEsLint(string $config, array $extensions, string $options)
    {
        $tasks = [];

        $tasks[] = $this->taskExec($this->getBin('run'))->arg('toolkit:setup-eslint');

        $opts = [
            'config' => $config,
            'ext' => implode(',', $extensions),
        ];

        if (!empty($options)) {
            $extra = array_fill_keys(explode(' ', $options), null);
            $opts = array_merge($opts, $extra);
        }

        $tasks[] = $this->taskExec($this->getNodeBinPath('eslint'))->options($opts)->arg('.');

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Run lint PHP.
     *
     * Check configurations at config/default.yml - 'toolkit.lint.php'.
     *
     * @command toolkit:lint-php
     *
     * @option exclude    The eslint config file.
     * @option extensions The extensions to check.
     * @option options    Extra options for the command without -- (only options with no value).
     *
     * @aliases tlp, tk-php
     */
    public function toolkitLintPhp(array $options = [
        'extensions' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'exclude' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'options' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $task = $this->taskExec($this->getBin('parallel-lint'));
        foreach ($options['exclude'] as $exclude) {
            $task->option('exclude', $exclude);
        }
        if ($options['extensions']) {
            $task->option('-e', implode(',', $options['extensions']));
        }
        if (!empty($options['options'])) {
            $opts = explode(' ', $options['options']);
            foreach ($opts as $opt) {
                $task->option($opt);
            }
        }

        return $this->collectionBuilder()->addTask($task->rawArg('.'));
    }

}
