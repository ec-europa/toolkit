<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use EcEuropa\Toolkit\Website;
use Robo\ResultData;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides commands to update docker-compose.yml based on project's configurations.
 */
final class DockerCommands extends AbstractCommands
{
    private const OPTS_YML_FILE = '.opts.yml';
    private const DC_YML_FILE_PREVIOUS = 'docker-compose.yml.prev';
    private const DEV_SUFFIX = '-dev';

    /**
     * Update docker-compose.yml file based on project's configurations.
     *
     * This command allows developers to update the docker-compose.yml file taking in consideration:
     * - The service versions on the .opts.yml file (php_version, mysql_version, selenium_version, solr_version).
     * - The service versions currently on client's production.
     * - Quality Assurance minimum version requirements and defaults (https://digit-dqa.fpfis.tech.ec.europa.eu/requirements).
     *
     * Notes:
     *  If a version is provided in .opts.yml, this one must be used (a warning message is displayed if the versions are non-compliant or outdated with Quality Assurance requirements)
     *  If no opts.yml is provided, the newer version from QA requirements or project production version will be used.
     *  In case of some information is not available, the images will be updated based on Quality Assurance default values.
     *
     * @return int
     *
     * @command docker:refresh-configuration
     *
     * @aliases dk-rc
     *
     * @throws \Exception
     */
    public function dockerRefreshConfiguration(): int
    {
        $projectId = $this->getConfig()->get('toolkit.project_id');
        if (empty($projectId)) {
            $this->writeln('<error>The configuration toolkit.project_id value is not valid.</error>');
            return ResultData::EXITCODE_ERROR;
        }

        $dcContent = $this->getDockerComposeYml('say');
        if (empty($dcContent)) {
            $this->copyDockerComposeDefaultToProject();
        }

        $dcServiceImages = $this->getServicesImagesFromDockerCompose($dcContent);

        $websiteRequirements = $this->getWebsiteRequirements();
        $requirementsServiceImage = $this->getServicesVersionsFromRequirements($websiteRequirements['defaults']);
        $projectInfo = $this->getWebsiteProjectInformation($projectId);

        $requirements = array_merge($requirementsServiceImage, $projectInfo);

        $optsFileContent = file_exists(self::OPTS_YML_FILE)
            ? Yaml::parseFile(self::OPTS_YML_FILE)
            : [];

        $finalServicesImages = $this->getFinalImages($requirements, $websiteRequirements, $optsFileContent);
        $warningMessages = $this->getWarningMessages($optsFileContent, $websiteRequirements);

        ksort($dcServiceImages);
        ksort($finalServicesImages);

        if ($dcServiceImages === $finalServicesImages) {
            $this->writeWarningMessages($warningMessages);
            $this->writeln("<info>" . self::DC_YML_FILE . " file is already updated.</info>");

            return ResultData::EXITCODE_OK;
        }

        $this->backupDockerComposeFile();
        $this->updateDockerComposeFile($dcContent, $finalServicesImages);
        $this->writeWarningMessages($warningMessages);
        $this->writeln("<info>" . self::DC_YML_FILE . " file updated with success.</info>");

        return ResultData::EXITCODE_OK;
    }

    /**
     * Copy ./resources/docker/default.yml file to docker-compose.yml inside project root directory
     *
     * @return void
     */
    private function copyDockerComposeDefaultToProject(): void
    {
        $dockerComposeDefault = Toolkit::getToolkitRoot() . $this->getConfig()->get('docker.resource.default');

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
     * @throws \Exception
     */
    private function getWebsiteProjectInformation(string $projectId): array
    {
        $data = Website::projectInformation($projectId);
        if (!$data) {
            $this->writeln('Failed to connect to the endpoint. Required env var QA_API_AUTH_TOKEN.');
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
     * @throws \Exception
     */
    private function getWebsiteRequirements(): array
    {
        $data = Website::requirements();
        if (empty($data)) {
            throw new \Exception('Failed to connect to the endpoint. Required env var QA_API_AUTH_TOKEN.');
        }

        return $data;
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
        if (strlen($version) < 3) {
            return $version;
        }

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
        $this->say('Backup ' . self::DC_YML_FILE . ' file to ' . self::DC_YML_FILE_PREVIOUS . ' in ' . $this->getWorkingDir());
        $this->taskFilesystemStack()
            ->copy(self::DC_YML_FILE, self::DC_YML_FILE_PREVIOUS)
            ->run();
    }

    /**
     * Update docker-compose.yml
     *
     * @param array $dcContent
     * @param array $finalServicesImages
     *
     * @return void
     */
    private function updateDockerComposeFile(array $dcContent, array $finalServicesImages): void
    {
        $this->say('Updating ' . self::DC_YML_FILE . ' file in ' . $this->getWorkingDir());

        $dcContent['services'] = $this->removeUnusedDcServices($dcContent['services'], $finalServicesImages);

        foreach ($finalServicesImages as $service => $image) {
            if (empty($dcContent['services'][$service])) {
                $dcContent['services'][$service] = $this->getServiceDetailsFromResources($service);
            }

            $dcContent['services'][$service]['image'] = $image;
        }

        $yaml = str_ireplace(' null', '', Yaml::dump($dcContent, 10, 2, Yaml::DUMP_OBJECT_AS_MAP));

        file_put_contents(self::DC_YML_FILE, $yaml);
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
            $finalImages[$defaultService] = $this->getServiceImage($service, $image, (string) $finalVersion);
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

            if (version_compare((string) $version, (string) $minRequiredVersion, '<')) {
                $warningMessages[] = "<error>The $service=$version version is non-compliant or outdated with our requirements.</error>";
            }
        }

        return $warningMessages;
    }

    /**
     * Remove services that do not exist in project info, requirements or .opts.yml
     *
     * @param array $dcServices
     * @param array $finalServicesImages
     *
     * @return array
     */
    private function removeUnusedDcServices(array $dcServices, array $finalServicesImages): array
    {
        $dcServicesNames = array_keys($dcServices);
        $finalServicesNames = array_keys($finalServicesImages);
        $servicesToRemove = array_diff($dcServicesNames, $finalServicesNames);
        foreach ($servicesToRemove as $service) {
            unset($dcServices[$service]);
        }

        return $dcServices;
    }

}
