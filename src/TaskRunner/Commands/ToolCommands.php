<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use Symfony\Component\Console\Input\InputOption;
use OpenEuropa\TaskRunner\Commands\AbstractCommands;

/**
 * Generic tools.
 */
class ToolCommands extends AbstractCommands
{

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

        $tasks[] = $this->taskExecStack()
            ->stopOnFail()
            ->exec('./vendor/bin/drush -y config-set system.performance css.preprocess 0')
            ->exec('./vendor/bin/drush -y config-set system.performance js.preprocess 0')
            ->exec('./vendor/bin/drush cache:rebuild');

        // Build and return task collection.
        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Display toolkit notifications.
     *
     * @command toolkit:notifications
     *
     * @option endpoint-url The endpoint for the notifications
     */
    public function displayNotifications(array $options = [
        'endpoint-url' => InputOption::VALUE_OPTIONAL,
    ])
    {
        $endpointUrl = isset($options['endpoint-url']) ? $options['endpoint-url'] : $this->getConfig()->get("toolkit.notifications_endpoint");

        if (isset($endpointUrl)) {
            $result = $this->getQaEndpointContent($endpointUrl);
            $data = json_decode($result, true);
            foreach ($data as $notification) {
                $this->io()->warning($notification['title'] . PHP_EOL . $notification['notification']);
            }
        }//end if
    }

    /**
     * Check composer.json for components that are not whitelisted.
     *
     * @command toolkit:whitelist-components
     *
     * @option endpoint-url The endpoint for the notifications
     */
    public function whitelistComponents(array $options = [
        'endpoint-url' => InputOption::VALUE_OPTIONAL,
        'test-command' => false,
    ])
    {
        $endpointUrl = isset($options['endpoint-url']) ? $options['endpoint-url'] : $this->getConfig()->get("toolkit.whitelistings_endpoint");
        $basicAuth = getenv('QA_API_BASIC_AUTH') !== false ? getenv('QA_API_BASIC_AUTH') : '';
        $composerLock = file_get_contents('composer.lock') ? json_decode(file_get_contents('composer.lock'), true) : false;

        if (isset($endpointUrl) && isset($composerLock['packages'])) {
            $result = $this->getQaEndpointContent($endpointUrl, $basicAuth);
            $data = json_decode($result, true);
            $modules = array_filter(array_combine(array_column($data, 'name'), $data));

            // To test this command execute it with the --test-command option:
            // ./vendor/bin/run toolkit:whitelist-components --test-command --endpoint-url="https://webgate.ec.europa.eu/fpfis/qa/api/v1/package-reviews?version=8.x"
            // Then we provide an array in the packages that fails on each type
            // of validation.
            if ($options['test-command']) {
                $composerLock['packages'] = [
                    ['version' => '1.0', 'name' => 'drupal/not_reviewed_yet'],
                    ['version' => '1.0', 'name' => 'drupal/devel'],
                    ['version' => '1.0', 'name' => 'drupal/allowed_formats'],
                    ['version' => '1.0', 'name' => 'drupal/active_facet_pills'],
                ];
            }

            // Loop over the require section.
            foreach ($composerLock['packages'] as $package) {
                // Check if it's a drupal package.
                // NOTE: Currently only supports drupal pagackages :(.
                if (substr($package['name'], 0, 7) === 'drupal/') {
                    $this->validateComponent($package, $modules);
                }
            }
            // TODO: Check for security updates.
            // Only works on Drupal site codebases.
            //$this->_exec('./vendor/bin/drush pm:security');
        }//end if
    }

    /**
     * Helper function to validate the component.
     *
     * @param array $package The package to validate.
     * @param array $modules The modules list.
     *
     * @return void
     */
    protected function validateComponent($package, $modules)
    {
        $name = $package['name'];
        $packageName = str_replace('drupal/', '', $name);
        $hasBeenQaEd = isset($modules[$packageName]);
        $wasRejected = isset($modules[$packageName]['restricted_use']) && $modules[$packageName]['restricted_use'] !== '0';
        $wasNotRejected = isset($modules[$packageName]['restricted_use']) && $modules[$packageName]['restricted_use'] === '0';

        // If module was not reviewed yet.
        if (!$hasBeenQaEd) {
            $this->io()->warning('The package ' . $name . ' has not been approved by QA. Please request a review.');
        }

        // If module was rejected.
        if ($hasBeenQaEd && $wasRejected) {
            $projectId = $this->getConfig()->get("toolkit.project_id");
            $allowedInProject = in_array($projectId, array_map('trim', explode(',', $modules[$packageName]['restricted_use'])));
            // If module was not allowed in project.
            if (!$allowedInProject) {
                $this->io()->warning('The package ' . $name . ' has been rejected by QA. Please remove the package or request an exception with QA.');
            }
        }

        // If module was approved check the minimum required version.
        if ($wasNotRejected) {
            $moduleVersion = str_replace('8.x-', '', $modules[$packageName]['version']);
            $versionCompare = version_compare($package['version'], $moduleVersion);
            if ($versionCompare === -1) {
                $this->io()->warning('The minimum required version for package ' . $name . ' is ' . $moduleVersion . '. Please update your package.');
            }
        }
    }

    /**
     * Curl function to access endpoint with or without authentication.
     *
     * @SuppressWarnings(PHPMD.MissingImport)
     *
     * @param string $url The QA endpoint url.
     * @param string $basicAuth The basic auth.
     *
     * @return string
     */
    public function getQaEndpointContent(string $url, string $basicAuth = ''): string
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
                    $content = $this->getQaEndpointContent($url);
            }
        }
        if ($result === false) {
            throw new \Exception(sprintf('Curl request to endpoint "%s" failed.', $url));
        }
        curl_close($curl);

        return $content;
    }
}
