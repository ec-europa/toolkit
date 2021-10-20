<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Composer\Semver\Semver;
use Symfony\Component\Console\Input\InputOption;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use Symfony\Component\Yaml\Yaml;

/**
 * Generic tools.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ToolCommands extends AbstractCommands
{

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return __DIR__ . '/../../../config/commands/tool.yml';
    }

    /**
     * Disable aggregation and clear cache.
     *
     * @command toolkit:disable-drupal-cache
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     */
    public function disableDrupalCache()
    {
        $tasks = [];

        $drush_bin = $this->getConfig()->get('runner.bin_dir') . '/drush';
        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec($drush_bin . ' -y config-set system.performance css.preprocess 0')
            ->exec($drush_bin . ' -y config-set system.performance js.preprocess 0')
            ->exec($drush_bin . ' cache:rebuild');

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Display toolkit notifications.
     *
     * @command toolkit:notifications
     *
     * @option endpoint The endpoint for the notifications
     */
    public function displayNotifications(array $options = [
        'endpoint' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $endpointUrl = $options['endpoint'];

        if (isset($endpointUrl)) {
            $result = self::getQaEndpointContent($endpointUrl);
            $data = json_decode($result, true);
            foreach ($data as $notification) {
                $this->io()->warning($notification['title'] . PHP_EOL . $notification['notification']);
            }
        }//end if
    }

    /**
     * Check composer.json for components that are not whitelisted/blacklisted.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @command toolkit:component-check
     *
     * @option endpoint The endpoint for the components whitelist/blacklist
     * @option blocker  Whether the command should exit with errorstatus
     */
    public function componentCheck(array $options = [
        'endpoint' => InputOption::VALUE_REQUIRED,
        'blocker' => InputOption::VALUE_REQUIRED,
        'test-command' => false,
    ])
    {
        // Currently undocumented in this class. Because I don't know how to
        // provide such a property to one single function other than naming the
        // failed property exactly for this function.
        $this->componentCheckFailed = false;
        $blocker = $options['blocker'];
        $endpointUrl = $options['endpoint'];
        $composerLock = file_get_contents('composer.lock') ? json_decode(file_get_contents('composer.lock'), true) : false;

        if (empty($basicAuth = getenv('QA_API_BASIC_AUTH'))) {
            $this->io()->warning('Missing ENV var QA_API_BASIC_AUTH.');
            return $blocker ? 1 : 0;
        }

        if (isset($endpointUrl) && isset($composerLock['packages'])) {
            $result = self::getQaEndpointContent($endpointUrl, $basicAuth);
            $data = json_decode($result, true);
            $modules = array_filter(array_combine(array_column($data, 'name'), $data));

            // To test this command execute it with the --test-command option:
            // ./vendor/bin/run toolkit:component-check --test-command --endpoint="https://webgate.ec.europa.eu/fpfis/qa/api/v1/package-reviews?version=8.x"
            // Then we provide an array in the packages that fails on each type
            // of validation.
            if ($options['test-command']) {
                $composerLock['packages'] = [
                    // Lines below should trow a warning.
                    ['type' => 'drupal-module', 'version' => '1.0', 'name' => 'drupal/unreviewed'],
                    ['type' => 'drupal-module', 'version' => '1.0', 'name' => 'drupal/devel'],
                    ['type' => 'drupal-module', 'version' => '1.0-alpha1', 'name' => 'drupal/xmlsitemap'],
                    // Allowed for single project jrc-k4p, otherwise trows warning.
                    ['type' => 'drupal-module', 'version' => '1.0', 'name' => 'drupal/active_facet_pills'],
                    // Allowed dev version if the Drupal version meets the
                    // conflict version constraints.
                    [
                        'version' => 'dev-1.x',
                        'type' => 'drupal-module',
                        'name' => 'drupal/views_bulk_operations',
                        'extra' => [
                            'drupal' => [
                                'version' => '8.x-3.4+15-dev',
                            ],
                        ],
                    ],
                ];
            }

            // Loop over the packages.
            foreach ($composerLock['packages'] as $package) {
                // Check if it's a drupal package.
                // NOTE: Currently only supports drupal packages :(.
                if (substr($package['name'], 0, 7) === 'drupal/') {
                    $this->validateComponent($package, $modules);
                }
            }

            // If the validation fail, return according to the blocker.
            if ($this->componentCheckFailed) {
                $msg = 'Failed the components check. Please contact the QA team.';
                $msg .= "\nSee the list of packages at https://webgate.ec.europa.eu/fpfis/qa/package-reviews.";
                $this->io()->warning($msg);
                return $blocker ? 1 : 0;
            }

            // Give feedback if no problems found.
            $this->io()->success('Components checked, nothing to report.');
        }//end if
    }

    /**
     * Helper function to validate the component.
     *
     * @param array $package The package to validate.
     * @param array $modules The modules list.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return void
     */
    protected function validateComponent($package, $modules)
    {
        $packageName = $package['name'];
        $hasBeenQaEd = isset($modules[$packageName]);
        $wasRejected = isset($modules[$packageName]['restricted_use']) && $modules[$packageName]['restricted_use'] !== '0';
        $wasNotRejected = isset($modules[$packageName]['restricted_use']) && $modules[$packageName]['restricted_use'] === '0';
        $packageVersion = isset($package['extra']['drupal']['version']) ? explode('+', str_replace('8.x-', '', $package['extra']['drupal']['version']))[0] : $package['version'];

        // Exclude invalid.
        $packageVersion = in_array($packageVersion, $this->getConfig()->get("toolkit.invalid-versions")) ?  $package['version'] : $packageVersion;

        // Only validate module components for this time.
        if (isset($package['type']) && $package['type'] === 'drupal-module') {
            // If module was not reviewed yet.
            if (!$hasBeenQaEd) {
                $this->say("Package $packageName:$packageVersion has not been reviewed by QA.");
                $this->componentCheckFailed = true;
            }

            // If module was rejected.
            if ($hasBeenQaEd && $wasRejected) {
                $projectId = $this->getConfig()->get("toolkit.project_id");
                $allowedInProject = in_array($projectId, array_map('trim', explode(',', $modules[$packageName]['restricted_use'])));
                // If module was not allowed in project.
                if (!$allowedInProject) {
                    $this->say("Package $packageName:$packageVersion has been rejected by QA.");
                    $this->componentCheckFailed = true;
                }
            }

            if ($wasNotRejected) {
                # Once all projects are using Toolkit >=4.1.0, the 'version' key
                # may be removed from the endpoint: /api/v1/package-reviews.
                $constraints = [ 'whitelist' => false, 'blacklist' => true ];
                foreach ($constraints as $constraint => $result) {
                    $constraintValue = !empty($modules[$packageName][$constraint]) ? $modules[$packageName][$constraint] : null;

                    if (!is_null($constraintValue) && Semver::satisfies($packageVersion, $constraintValue) === $result) {
                        $this->say("Package $packageName:$packageVersion does not meet the $constraint version constraint: $constraintValue.");
                        $this->componentCheckFailed = true;
                    }
                }
            }
        }
    }

    /**
     * Curl function to access endpoint with or without authentication.
     *
     * This function is made publicly available as a static function for other
     * projects to call. Then we have to maintain less code.
     *
     * @SuppressWarnings(PHPMD.MissingImport)
     *
     * @param string $url The QA endpoint url.
     * @param string $basicAuth The basic auth.
     *
     * @return string
     */
    public static function getQaEndpointContent(string $url, string $basicAuth = ''): string
    {
        $content = '';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if ($basicAuth !== '') {
            $header = ['Authorization: Basic ' . $basicAuth];
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        $result = curl_exec($curl);

        if ($result !== false) {
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            switch ($statusCode) {
                // Upon success set the content to be returned.
                case 200:
                    $content = $result;
                    break;
                // Upon other status codes.
                default:
                    if ($basicAuth === '') {
                        throw new \Exception(sprintf('Curl request to endpoint "%s" returned a %u.', $url, $statusCode));
                    }
                    // If we tried with authentication, retry without.
                    $content = self::getQaEndpointContent($url);
            }
        }
        if ($result === false) {
            throw new \Exception(sprintf('Curl request to endpoint "%s" failed.', $url));
        }
        curl_close($curl);

        return $content;
    }

    /**
     * Check project compatibility for Drupal 9 upgrade.
     *
     * Note: The project configuration should be updated.
     *
     * @command toolkit:d9-compatibility
     *
     */
    public function d9Compatibility()
    {
        // Build task collection.
        $collection = $this->collectionBuilder();

        // Check if 'upgrade_status' module is already on the project.
        $checkPackage = $this->taskExecStack()
            ->silent(true)
            ->exec('composer show drupal/upgrade_status -q')
            ->stopOnFail()
            ->run();
        // The project already requires this package.
        $this->say("Note: The project configuration should be updated before running this command.");

        if ($checkPackage->wasSuccessful()) {
            $this->say("The module 'upgrade_status' already makes part of the project.");

            if (file_exists('config/sync/core.extension.yml')) {
                $parseConfigFile = Yaml::parseFile('config/sync/core.extension.yml');
                // If it's not enable, enable, analise and remove.
                if (!isset($parseConfigFile['module']['upgrade_status'])) {
                    $collection->taskExecStack()
                        ->silent(true)
                        ->exec('drush en upgrade_status');
                    // Analise all packages/projects (contrib and custom).
                    $collection->taskExecStack()
                        ->exec('drush upgrade_status:analyze --all');
                    // Uninstall module after analisys.
                    $collection->taskExecStack()
                        ->silent(true)
                        ->exec('drush pm:uninstall upgrade_status');
                } else {
                    // Module already installed - just perform analisys.
                    $collection->taskExecStack()
                        ->exec('drush upgrade_status:analyze --all');
                }
            }
            $collection->run();
        } else {
            // If the project don't require this package
            // perform the following actions:
            // Install and enable package.
            // Analise.
            // Uninstall and remove package.
            $this->say("'Package drupal/upgrade_status not found' - Installing required package");
            $collection->taskComposerRequire()
                ->silent(true)
                ->dependency('drupal/upgrade_status', '^2.0')
                ->dev();
            $collection->taskExecStack()
                ->silent(true)
                ->exec('drush en upgrade_status');
            $collection->taskExecStack()
                ->exec('drush upgrade_status:analyze --all');
            $collection->taskExecStack()
                ->silent(true)
                ->exec('drush pm:uninstall upgrade_status');
            $collection->taskExecStack()
                ->silent(true)
                ->exec('composer remove drupal/upgrade_status --dev');
            $collection->run();
        }
        return 0;
    }

    /**
     * Check if composer.lock exists on the project root folder.
     *
     * @command toolkit:complock-check
     *
     */
    public function composerLockCheck()
    {
        if (!file_exists('composer.lock')) {
            $this->io()->error("Failed to detect a 'composer.lock' file on root folder.");
            return 1;
        } else {
            $this->say("Detected 'composer.lock' file - Ok.");
            // If the check is ok return '0'.
            return 0;
        }
    }

    /**
     * Check project's .opts.yml file for forbidden commands.
     *
     * @command toolkit:opts-review
     *
     */
    public function optsReview()
    {
        if (file_exists('.opts.yml')) {
            $parseOptsFile = Yaml::parseFile('.opts.yml');
            // List of commands to prevent the use.
            $forbiddenCommands = [
                'drush sql:conf',
                'drush sql-conf',
                'drush sql:connect',
                'drush sql-connect',
                'drush sql-connect',
                'drush sql:create',
                'drush sql-create',
                'drush sql:drop',
                'drush sql-drop',
                'drush sql:cli',
                'drush sql-cli',
                'drush sqlc',
                'drush sql:query',
                'drush sql-query',
                'drush sqlq',
                'drush sql:dump',
                'drush sql-dump',
                'drush sql:sanitize',
                'drush sql-sanitize',
                'drush sqlsan',
                'drush sql:sync',
                'drush sql-sync',
                'drush en',
                'drush pm-enable',
                'drush pm:disable',
                'drush dis',
                'drush pm-disable',
                'drush user:login',
                'drush uli',
                'drush user-login',
                'drush user:information',
                'drush uinf',
                'drush user-information',
                'drush user:block',
                'drush ublk',
                'drush user-block',
                'drush user:unblock',
                'drush uublk',
                'drush user-unblock',
                'drush user:role:add',
                'drush urol',
                'drush user-add-role',
                'drush user:role:remove',
                'drush urrol',
                'drush user-remove-role',
                'drush user:create',
                'drush ucrt',
                'drush user-create',
                'drush user:cancel',
                'drush ucan',
                'drush user-cancel',
                'drush user:password',
                'drush upwd',
                'drush user-password',
            ];
            $reviewOk = true;
            foreach ($parseOptsFile['upgrade_commands'] as $command) {
                foreach ($forbiddenCommands as $forbiddenCommand) {
                    if (strpos($command, $forbiddenCommand) !== false) {
                        $this->say("The command '$command' is not allowed. Please remove it from 'upgrade_commands' section.");
                        $reviewOk = false;
                    }
                }
            }
            if ($reviewOk == false) {
                $this->io()->error("Failed the '.opts.yml' file review. Please contact the QA team.");
                return 1;
            } else {
                $this->say("Review 'opts.yml' file - Ok.");
                // If the review is ok return '0'.
                return 0;
            }
        }
    }
}
