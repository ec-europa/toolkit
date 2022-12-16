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
     * @var object|null
     */
    private ?object $optsFileContent = null;
    /**
     * @var array|string[]
     */
    private array $projectInfo = [];

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

        $this->optsFileContent = file_exists(self::OPTS_YML_FILE)
            ? Yaml::parseFile(self::OPTS_YML_FILE, Yaml::PARSE_OBJECT_FOR_MAP)
            : null;

        $this->projectInfo = $this->getWebsiteProjectInformation($projectId);

        $dockerComposeContent = $this->getDcContentUpdateFromOptsFile();

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
            ->copy($dockerComposeDefault, $this->getWorkingDir() . '/' . self::DC_YML_FILE)
            ->run();
    }

    private function updateDockerComposeFile(object $dcContent): void
    {
        $root = $this->getWorkingDir();
        $this->output->writeln("<info>Updating docker-compose.yml file in $root</info>");

        $yaml = str_ireplace(' null', '', Yaml::dump($dcContent, 10, 2, Yaml::DUMP_OBJECT_AS_MAP));

        $this->taskFilesystemStack()
            ->copy(self::DC_YML_FILE, self::DC_YML_FILE_PREVIOUS)
            ->run();

        file_put_contents(self::DC_YML_FILE, $yaml);

        $this->writeln("docker-compose.yml file updated with success.");
    }

    /**
     * @param string $projectId
     * @return array|string[]
     * @throws Exception
     */
    private function getWebsiteProjectInformation(string $projectId): array
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

    /**
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
     * @param  string $version
     * @return string
     */
    private function extractMajorMinorVersion(string $version): string
    {
        preg_match('/^(\d+)\.(\d+)/', $version, $matches);

        return $matches[1] . '.' . $matches[2];
    }

    /**
     * @return object|null
     * @throws Exception
     */
    private function getDcContentUpdateFromOptsFile(): ?object
    {
        $dcContent = Yaml::parseFile(self::DC_YML_FILE, Yaml::PARSE_OBJECT_FOR_MAP);

        $websiteRequirements = $this->getWebsiteRequirements();
        $isServiceImagesUpdated = false;
        foreach ($websiteRequirements['defaults'] as $key => $default) {
            $defaultServiceName = $default['service'];
            $requiredVersion = $websiteRequirements['requirements'][$key] ?? null;
            $dcDefaultService = $dcContent->services->{$defaultServiceName} ?? null;
            if ($dcDefaultService === null) {
                if ($this->addServiceToDcContent($key, $default, $requiredVersion, $dcContent)) {
                    $isServiceImagesUpdated = true;
                }

                continue;
            }

            $dcServiceImage = $dcContent->services->{$defaultServiceName}->image;
            $serviceImage = $this->updateServiceImage($key, $default, $requiredVersion);

            if ($dcServiceImage !== $serviceImage) {
                $dcContent->services->{$defaultServiceName}->image = $serviceImage;

                $isServiceImagesUpdated = true;
            }
        }

        return $isServiceImagesUpdated ? $dcContent : null;
    }

    /**
     * @param string $websiteRequirementKey
     * @param array $defaultWebsiteRequirement
     * @param string|null $requiredVersion
     * @param object $dcContent
     * @return bool
     */
    private function addServiceToDcContent(
        string $websiteRequirementKey,
        array $defaultWebsiteRequirement,
        ?string $requiredVersion,
        object $dcContent,
    ): bool {
        $service = $defaultWebsiteRequirement['service'];
        $dockerServiceConfig = $this->getConfig()->get('docker.services.' . $service);

        $isDefaultService = filter_var($dockerServiceConfig['default'], FILTER_VALIDATE_BOOLEAN);
        $isServiceExistOnOptsFile = isset($this->optsFileContent) && isset($this->optsFileContent->{$websiteRequirementKey});

        if (!$isDefaultService && !$isServiceExistOnOptsFile) {
            return false;
        }

        $fileName = Toolkit::getToolkitRoot() . $dockerServiceConfig['resource'];
        $serviceContent = Yaml::parseFile($fileName, Yaml::PARSE_OBJECT_FOR_MAP);
        $serviceContent->image = $this->updateServiceImage($websiteRequirementKey, $defaultWebsiteRequirement, $requiredVersion);

        $dcContent->services->{$service} = $serviceContent;

        return true;
    }

    /**
     * @param string $serviceImage
     * @param string $serviceVersion
     * @return string
     */
    private function updateServiceWithLatestImage(string $serviceImage, string $serviceVersion): string
    {
        return $serviceImage . ':' . $serviceVersion;
    }

    /**
     * @param string $websiteRequirementKey
     * @param array $defaultWebsiteRequirement
     * @param string|null $requiredVersion
     * @return string
     */
    private function updateServiceImage(
        string $websiteRequirementKey,
        array $defaultWebsiteRequirement,
        ?string $requiredVersion
    ): string {
        if (isset($this->optsFileContent) && isset($this->optsFileContent->{$websiteRequirementKey})) {
            $optsServiceVersion = $this->optsFileContent->{$websiteRequirementKey};
            $version = $websiteRequirementKey === 'php_version'
                ? $optsServiceVersion . self::DEV_SUFFIX
                : $optsServiceVersion;

            if (isset($requiredVersion) && version_compare($optsServiceVersion, $requiredVersion, '<')) {
                $this->writeln("The $websiteRequirementKey=$optsServiceVersion version is non-compliant or outdated with our requirements.");
            }
        } elseif (!empty($this->projectInfo) && isset($this->projectInfo[$websiteRequirementKey])) {
            $version = $this->projectInfo[$websiteRequirementKey];
        } else {
            $version = $defaultWebsiteRequirement['version'];
        }

        return $this->updateServiceWithLatestImage($defaultWebsiteRequirement['image'], (string) $version);
    }

}
