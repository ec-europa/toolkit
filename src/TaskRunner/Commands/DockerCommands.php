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
 * Provides commands to download and install dump files.
 */
final class DockerCommands extends AbstractCommands
{
    private const MAP_SERVICES_TO_VERSIONS = [
        'php_version' => 'web',
        'mysql_version' => 'mysql',
        'selenium_version' => 'selenium',
        'solr_version' => 'solr',
    ];

    private const OPTS_YML_FILE = '.opts.yml';
    private const DC_YML_FILE = 'docker-compose.yml';
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

        $dockerComposeContent = file_exists(self::OPTS_YML_FILE)
            ? $this->getDcContentUpdateFromOptsFile()
            : $this->getDcContentUpdateFromRequirements($projectId);

        if ($dockerComposeContent !== null) {
            $this->updateDockerComposeFile($dockerComposeContent);

            return ResultData::EXITCODE_OK;
        }

        $this->writeln("$dockerCompose file is already updated.");

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
            ->copy($dockerComposeDefault, $this->getWorkingDir() . '/docker-compose.yml')
            ->run();
    }

    private function updateDockerComposeFile(object $dcContent): void
    {
        $root = $this->getWorkingDir();
        $this->output->writeln("<info>Updating docker-compose.yml file in $root</info>");

        $yaml = str_ireplace(' null', '', Yaml::dump($dcContent, 10, 2, Yaml::DUMP_OBJECT_AS_MAP));

        $this->taskFilesystemStack()
            ->copy(self::DC_YML_FILE, 'docker-compose.yml.prev')
            ->run();

        file_put_contents(self::DC_YML_FILE, $yaml);

        $this->writeln("docker-compose.yml file updated with success.");
    }

    /**
     * @param string $projectId
     * @return array
     * @throws Exception
     */
    private function getProjectInformation(string $projectId): array
    {
        $data = Website::projectInformation($projectId);
        if (!$data) {
            $this->writeln('Failed to connect to the endpoint. Required env var QA_API_BASIC_AUTH.');
            return [];
        }

        return [
            'php_version' => $this->extractMajorMinorVersion($data['php_version']) . self::DEV_SUFFIX,
        ];
    }

    private function getWebsiteRequirements(): array
    {
        $data = Website::requirements();
        if (empty($data)) {
            $this->writeln('Failed to connect to the endpoint. Required env var QA_API_BASIC_AUTH.');
            return [];
        }
        // atenção, no opts yaml tem que permitir o 'minimum'

        // Apenas usa este default se nao existir no .opts.yml
        // In future the endpoint for 'toolkit-requirements' will be changed to deliver the 'defaults' and 'requirements'
        $data = [
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

        $data['defaults']['php_version']['version'] = $data['defaults']['php_version']['version'] . self::DEV_SUFFIX;
        $data['requirements']['php_version'] = $data['requirements']['php_version'] . self::DEV_SUFFIX;

        return $data;
    }

    /**
     * @param  string $projectId
     * @return array
     * @throws Exception
     */
    private function getAllServicesVersionRequirements(string $projectId): array
    {
        $websiteRequirements = $this->getWebsiteRequirements();
        $projectInfo = $this->getProjectInformation($projectId);

        return !empty($projectInfo)
            ? array_merge($websiteRequirements['defaults'], $projectInfo)
            : $websiteRequirements;
    }

    /**
     * @param  string $version
     * @return string
     */
    private function extractMajorMinorVersion(string $version): string
    {
        preg_match('/^(\d+)\.(\d+)/', $version, $matches);

        return $matches[1] . '.' . $matches[2];
    }

    /**
     * @param string $projectId
     * @param array $options
     * @return object|null
     * @throws Exception
     */
    private function getDcContentUpdateFromRequirements(string $projectId): ?object
    {
        // Update using:
        //  - Versions currently on the client production (from endpoint only php_version)
        //  - Requirements Quality Assurance minimum versions (min 8)
        //
        //  Note: Always use the newer version from requirements or projects production version

        $this->writeln("The file .opts.yml was not found.");
        $this->writeln("docker-compose.yml will be updated using versions on the client production, requirements or default values on Quality Assurance.");

        $dcContent = Yaml::parseFile(self::DC_YML_FILE, Yaml::PARSE_OBJECT_FOR_MAP);
        $requirementVersions = $this->getAllServicesVersionRequirements($projectId);

        $isServiceImagesUpdated = false;
        foreach ($requirementVersions as $key => $version) {
            if (!isset(self::MAP_SERVICES_TO_VERSIONS[$key])) {
                continue;
            }

            $serviceName = $options[self::MAP_SERVICES_TO_VERSIONS[$key]];
            $serviceImage = $dcContent->services->{$serviceName}->image ?? null;
            if ($serviceImage === null) {
                continue;
            }

            $dcServiceVersion = explode(':', (string) $serviceImage);

            if (version_compare($dcServiceVersion[1], $version, '<')) {
                $dcContent->services->{$serviceName}->image =
                    substr_replace($serviceImage, ':' . $version, strpos($serviceImage, ':'));

                $isServiceImagesUpdated = true;
            }
        }

        return $isServiceImagesUpdated ? $dcContent : null;
    }

    /**
     * @return object|null
     */
    private function getDcContentUpdateFromOptsFile(): ?object
    {
        // If a version is provided in .opts.yml then this one must be used.
        // A message should be displayed if the versions are non-compliant or outdated.

        $dcContent = Yaml::parseFile(self::DC_YML_FILE, Yaml::PARSE_OBJECT_FOR_MAP);
        $opts = Yaml::parseFile(self::OPTS_YML_FILE, Yaml::PARSE_OBJECT_FOR_MAP);

        $websiteRequirements = $this->getWebsiteRequirements();
        $isServiceImagesUpdated = false;
        foreach ($websiteRequirements['defaults'] as $key => $default) {
            $defaultServiceName = $default['service'];
            // $dcServiceImage = $dcContent->services->{$defaultServiceName}->image ?? null;
            $dcDefaultService = $dcContent->services->{$defaultServiceName} ?? null;
            if ($dcDefaultService === null) {
                if ($this->addServiceToDcContent($default, $dcContent)) {
                    $isServiceImagesUpdated = true;
                }

                continue;
            }

            if (!isset($opts->{$key})) {
                continue;
            }

            $dcServiceImage = $dcContent->services->{$defaultServiceName}->image;

            $optsServiceVersion = $key === 'php_version' ? $opts->{$key} . self::DEV_SUFFIX : $opts->{$key};
            $optsServiceImage = $default['image'] . ':' . $optsServiceVersion;

            if ($dcServiceImage !== $optsServiceImage) {
                $requirements = $websiteRequirements['requirements'][$key] ?? null;
                if ($requirements !== null && version_compare($optsServiceVersion, $requirements, '<')) {
                    $this->writeln("The $key={$opts->{$key}} version is non-compliant or outdated with our requirements.");
                }

                $dcContent->services->{$defaultServiceName}->image = $optsServiceImage;

                $isServiceImagesUpdated = true;
            }
        }

        return $isServiceImagesUpdated ? $dcContent : null;
    }

    private function addServiceToDcContent(array $defaultWebsiteRequirements, object &$dcContent): bool
    {
        $defaultServiceConfig = $this->getConfig()->get('docker.default_services.' . $defaultWebsiteRequirements['service'] . '.resource');
        if ($defaultServiceConfig === null) {
            return false;
        }

        $fileName = Toolkit::getToolkitRoot() . $defaultServiceConfig;
        $service = Yaml::parseFile($fileName, Yaml::PARSE_OBJECT_FOR_MAP);
        $service->image = $this->updateServiceWithLatestImage($defaultWebsiteRequirements);

        $dcContent->services->{$defaultWebsiteRequirements['service']} = $service;

        return true;
    }

    private function updateServiceWithLatestImage(array $defaultWebsiteRequirements): string
    {
        return $defaultWebsiteRequirements['image'] . ':' . $defaultWebsiteRequirements['version'];
    }

}
