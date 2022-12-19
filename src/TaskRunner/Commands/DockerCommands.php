<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use EcEuropa\Toolkit\Website;
use Exception;
use Robo\ResultData;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides commands to update docker-compose.yml based on project's configurations.
 */
final class DockerCommands extends AbstractCommands
{
    private const OPTS_YML_FILE = '.opts.yml';
    private const DC_YML_FILE = 'docker-compose.yml';
    private const DC_YML_FILE_PREVIOUS = 'docker-compose.yml.prev';
    private const DEV_SUFFIX = '-dev';

    /**
     * Update docker-compose.yml file based on project's configurations.
     *
     * @return int
     *
     * @command docker:refresh-configuration
     *
     * @throws Exception
     */
    public function dockerRefreshConfiguration(): int
    {
        $projectId = $this->getConfig()->get('toolkit.project_id');
        if (empty($projectId)) {
            $this->writeln('The configuration toolkit.project_id value is not valid.');
            return ResultData::EXITCODE_ERROR;
        }

        $dockerCompose = self::DC_YML_FILE;
        if (!file_exists($dockerCompose)) {
            $this->writeln("The file $dockerCompose was not found, creating it.");
            $this->copyDockerComposeDefaultToProject();
        }

        $dcContent = Yaml::parseFile(self::DC_YML_FILE);
        $dcServiceImages = $this->getServicesImagesFromDockerCompose($dcContent);

        $projectInfo = $this->getWebsiteProjectInformation($projectId);

        $websiteRequirements = $this->getWebsiteRequirements();
        $requirementsServiceImage = $this->getServicesVersionsFromRequirements($websiteRequirements['defaults']);

        $requirements = array_merge($requirementsServiceImage, $projectInfo);

        $optsFileContent = file_exists(self::OPTS_YML_FILE)
            ? Yaml::parseFile(self::OPTS_YML_FILE)
            : [];

        $finalImages = $this->getFinalImages($requirements, $websiteRequirements, $optsFileContent);
        $warningMessages = $this->getWarningMessages($optsFileContent, $websiteRequirements);

        ksort($dcServiceImages);
        ksort($finalImages);

        if ($dcServiceImages === $finalImages) {
            $this->writeln("$dockerCompose file is already updated.");

            return ResultData::EXITCODE_OK;
        }

        $this->backupDockerComposeFile();
        $this->writeWarningMessages($warningMessages);
        $this->updateDockerComposeFile($dcContent, $finalImages);

        return ResultData::EXITCODE_OK;
    }

    /**
     * Copy ./resources/docker/default.yml file to docker-compose.yml inside project root directory
     *
     * @return void
     */
    private function copyDockerComposeDefaultToProject(): void
    {
        $dockerComposeDefault = Toolkit::getToolkitRoot() . '/resources/docker/default.yml';

        $this->taskFilesystemStack()
            ->copy($dockerComposeDefault, $this->getWorkingDir() . '/' . self::DC_YML_FILE)
            ->run();
    }

    /**
     * Get array of services with images and versions from docker-compose.yml
     *
     * @param array $dcContent
     *
     * @return array
     */
    private function getServicesImagesFromDockerCompose(array $dcContent): array
    {
        $servicesImages = [];
        foreach ($dcContent['services'] as $service => $data) {
            $servicesImages[$service] = $data['image'];
        }

        return $servicesImages;
    }

    /**
     * Returns the Project's php service version information from the endpoint.
     *
     * @param string $projectId
     *
     * @return array|string[]
     * @throws Exception
     */
    private function getWebsiteProjectInformation(string $projectId): array
    {
        $data = Website::projectInformation($projectId);
        if (!$data) {
            $this->writeln('Failed to connect to the endpoint. Required env var QA_API_BASIC_AUTH.'); // @todo isto
            return [];
        }

        return [
            'php_version' => $data['php_version'],
        ];
    }

    /**
     * Returns the toolkit requirements from the endpoint.
     *
     * @return array
     * @throws Exception
     */
    private function getWebsiteRequirements(): array
    {
        $data = Website::requirements();
        if (empty($data)) {
            throw new Exception('Failed to connect to the endpoint. Required env var QA_API_BASIC_AUTH.');
        }

        // In future the endpoint for 'toolkit-requirements' will be changed to deliver the 'defaults' and 'requirements'
        return [
            'defaults' => [
                'php_version' => [
                    'image' => 'fpfis/httpd-php',
                    'version' => '8.1',
                    'service' => 'web',
                ],
                'mysql_version' => [
                    'image' => 'percona/percona-server',
                    'version' => '5.7',
                    'service' => 'mysql',
                ],
                'selenium_version' => [
                    'image' => 'selenium/standalone-chrome',
                    'version' => '4.1.3-20220405',
                    'service' => 'selenium',
                ],
                'solr_version' => [
                    'image' => 'solr',
                    'version' => '8',
                    'service' => 'solr',
                ],
            ],
            'requirements' => [
                'php_version' => $data['php_version'],
            ]
        ];
    }

    /**
     * Converts from semantic version to "major.minor" version
     *
     * @param string $version
     *
     * @return string
     */
    private function extractMajorMinorVersion(string $version): string
    {
        preg_match('/^(\d+)\.(\d+)/', $version, $matches);

        return $matches[1] . '.' . $matches[2];
    }

    /**
     * Backup current docker-compose.yml to docker-compose.yml.prev
     *
     * @return void
     */
    private function backupDockerComposeFile(): void
    {
        $root = $this->getWorkingDir();
        $this->writeln("<info>Backup docker-compose.yml file to docker-compose.yml.prev in $root</info>");
        $this->taskFilesystemStack()
            ->copy(self::DC_YML_FILE, self::DC_YML_FILE_PREVIOUS)
            ->run();
    }

    /**
     * Update docker-compose.yml
     *
     * @param array $dcContent
     * @param array $images
     *
     * @return void
     */
    private function updateDockerComposeFile(array $dcContent, array $images): void
    {
        $root = $this->getWorkingDir();
        $this->output->writeln("<info>Updating docker-compose.yml file in $root</info>");

        foreach ($images as $service => $image) {
            if (empty($dcContent['services'][$service])) {
                $dcContent['services'][$service] = $this->getServiceDetailsFromResources($service);
            }

            $dcContent['services'][$service]['image'] = $image;
        }

        $yaml = str_ireplace(' null', '', Yaml::dump($dcContent, 10, 2, Yaml::DUMP_OBJECT_AS_MAP));

        file_put_contents(self::DC_YML_FILE, $yaml);

        $this->writeln("docker-compose.yml file updated with success.");
    }

    /**
     * Return the details for a service from ./resources/docker
     *
     * @param string $serviceName
     *
     * @return array
     */
    private function getServiceDetailsFromResources(string $serviceName): array
    {
        $dockerServiceConfig = $this->getConfig()->get('docker.services.' . $serviceName);
        $fileName = Toolkit::getToolkitRoot() . $dockerServiceConfig['resource'];
        return Yaml::parseFile($fileName);
    }

    /**
     * Get final services names with versions from requirements
     *
     * @param array $content
     *
     * @return array
     */
    private function getServicesVersionsFromRequirements(array $content): array
    {
        $services = [];
        foreach ($content as $service => $data) {
            $services[$service] = $data['version'];
        }

        return $services;
    }

    /**
     * Write all available warning messages
     *
     * @param array $messages
     *
     * @return void
     */
    private function writeWarningMessages(array $messages): void
    {
        foreach ($messages as $message) {
            $this->writeln($message);
        }
    }

    /**
     * Get service image with version
     *
     * @param string $service
     * @param string $image
     * @param string $version
     *
     * @return string
     */
    private function getServiceImage(string $service, string $image, string $version): string
    {
        $finalVersion = $service === 'php_version'
            ? $this->extractMajorMinorVersion($version) . self::DEV_SUFFIX
            : $version;

        return $image . ':' . $finalVersion;
    }

    /**
     * Get final images to update docker-compose.yml
     *
     * @param $requirements
     * @param $websiteRequirements
     * @param $optsFileContent
     *
     * @return array
     */
    private function getFinalImages($requirements, $websiteRequirements, $optsFileContent): array
    {
        $finalImages = [];
        foreach ($requirements as $service => $version) {
            $defaultService = $websiteRequirements['defaults'][$service]['service'];
            $dockerServiceConfig = $this->getConfig()->get('docker.services.' . $defaultService);

            $isDefaultService = filter_var($dockerServiceConfig['default'], FILTER_VALIDATE_BOOLEAN);
            $isServiceExistOnOptsFile = !empty($optsFileContent) && !empty($optsFileContent[$service]);

            if (!$isDefaultService && !$isServiceExistOnOptsFile) {
                continue;
            }

            $finalVersion = $isServiceExistOnOptsFile ? $optsFileContent[$service] : $version;
            $image = $websiteRequirements['defaults'][$service]['image'];
            $finalImages[$defaultService] = $this->getServiceImage($service, $image, $finalVersion);
        }

        return $finalImages;
    }

    /**
     * Get warning messages for versions on .opts.yml that not respect the minimum requirements
     *
     * @param $websiteRequirements
     * @param $optsFileContent
     *
     * @return array
     */
    private function getWarningMessages($optsFileContent, $websiteRequirements): array
    {
        $warningMessages = [];
        foreach ($optsFileContent as $service => $version) {
            $minRequiredVersion = $websiteRequirements['requirements'][$service] ?? null;
            if ($minRequiredVersion === null) {
                continue;
            }

            if (version_compare($version, $minRequiredVersion, '<')) {
                $warningMessages[] = "The $service=$version version is non-compliant or outdated with our requirements.";
            }
        }

        return $warningMessages;
    }

}
