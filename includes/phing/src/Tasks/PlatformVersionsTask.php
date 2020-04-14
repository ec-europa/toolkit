<?php

/**
 * Discover latest major versions of NE Platform.
 *
 * PHP Version 5 and 7
 *
 * @category BuildSystem
 * @package  DrupalToolkit
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/toolkit/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */

namespace Phing\Toolkit\Tasks;

use Project;
use Symfony\Component\Finder\Finder;

require_once 'phing/Task.php';

/**
 * A Phing task to generate an aliases.drushrc.php file.
 *
 * @category BuildSystem
 * @package  DrupalToolkit
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/toolkit/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */
class PlatformVersionsTask extends \Task
{

    /**
     * The major version of the current project.
     *
     * @var string
     */
    private $_majorVersion = '';

    /**
     * The version specified in the project props.
     *
     * @var string
     */
    private $_packageVersion = '';

    /**
     * The version that will be used to download the package.
     *
     * @var string
     */
    private $versionprop = '';

    /**
     * The latest version available for this package.
     *
     * @var string
     */
    private $latestprop = '';

    /**
     * Wether or not to download prereleases.
     *
     * @var bool
     */
    private $_preRelease = FALSE;
 
    /**
     * Check github repository and retrieve all latest major versions.
     *
     * @return void
     */
    public function main()
    {
        // Check if all required data is present.
        $this->checkRequirements();

        // Log to screen the current value.
        $this->log(
            "The platform package version is set to " . $this->_packageVersion . ".",
            Project::MSG_INFO
        );

        // Get latest version of Platform.
        $resp = $this->callGithubReleases('https://api.github.com/repos/ec-europa/platform-dev/releases/latest');
        $latest_version = $resp->tag_name;

        // Check if user has provided the latest version.
        if ($latest_version === $this->_packageVersion) {
            $this->setVersionProp($latest_version);
        }
        else {
            // Get latest version of NE Platform.
            $resp = $this->callGithubReleases('https://api.github.com/repos/ec-europa/platform-dev/releases');
            foreach($resp as $object) {
                // Skip drafts and prereleases.
                if ($object->draft == false && $object->prerelease == false) {
                    $versions[$object->published_at] = $object->tag_name;
                }
                if ($this->_prerelease == true && $object->prerelease == true) {
                    $versions[$object->published_at] = $object->tag_name;
                }
            }
            ksort($versions);

            // Check if end-user is providing the exact version, if not just get the latest
            // for the major provided.
            if (in_array($this->_packageVersion, $versions)) {
                $this->setVersionProp($this->_packageVersion);
            }
            else {
                foreach($versions as $version) {
                    $temporaryGroups[substr_compare($version, $this->_majorVersion, 0, 3)] = $version;
                }

                foreach($temporaryGroups as $version) {
                    $majors[substr($version, 0, 3)] = $version;
                }

                $this->setVersionProp($majors[$this->_majorVersion]);
            }
        }

        $this->log(
            "The build will be performed with version " . $this->versionprop . ".",
            Project::MSG_INFO
        );

        // Check if the project is using the latest version, if not let user know.
        $shortPackageVersionSet = substr($this->_packageVersion, 0, 3);
        $shortPackageVersionLatest = substr($latest_version, 0, 3);

        if ($shortPackageVersionSet != $shortPackageVersionLatest) {
            $latest_version = $majors[$shortPackageVersionSet];
        }

        $this->setLatestProp($latest_version);

        if ($this->versionprop != $latest_version) {
            $this->log(
                "Please upgrade your project recommended version " . $latest_version  . ".",
                Project::MSG_WARN
            );
        }

        $this->getProject()->setNewProperty('platform.package.version.latest', $latest_version);
        $this->getProject()->setNewProperty('platform.package.version.current', $this->versionprop);

    }//end main()

    /**
     * Checks if all properties required for generating the aliases file are
     * present.
     *
     * @throws \BuildException
     *   Thrown when a required property is not present.
     *
     * @return void
     */
    protected function checkRequirements()
    {
        $required_properties = array('_packageVersion');
        foreach ($required_properties as $required_property) {
            if (empty($this->$required_property)) {
                throw new \BuildException(
                    "Missing required property '$required_property'."
                );
            }
        }
    }//end checkRequirements()

    /**
     * Sets the current package version.
     *
     * @param string $packageVersion The current project version.
     *
     * @return void
     */
    public function setPackageVersion($packageVersion)
    {
        $this->_packageVersion = $packageVersion;
        $this->_majorVersion = substr($packageVersion, 0, 3);
    }//end setPackageVersion()

    /**
     * Sets the package version to be downloaded.
     *
     * @param string $versionprop to be used by the project to download.
     *
     * @return string
     */
    public function setVersionProp($versionprop)
    {
        $this->versionprop = $versionprop;
    }//end setVersionProp()

    /**
     * Sets the latest package version available.
     *
     * @param string $latestprop available for the project.
     *
     * @return string
     */
    public function setLatestProp($latestprop)
    {
        $this->latestprop = $latestprop;
    }//end setLatestProp()

    /**
     * Sets the prerelease property.
     *
     * @param bool $preRelease available for the project.
     *
     * @return bool
     */
    public function setPreRelease($preRelease)
    {
        $this->_prerelease = $preRelease;
    }//end setPreRelease()
     
    private function callGithubReleases($url)
    {
        // Get latest version of NE Platform.
        $curl = curl_init();
        $github_api_token = getenv('GITHUB_API_TOKEN');
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'EC Toolkit request'
        ]);
        if (!empty($github_api_token)) {
            $request_headers = array(
                'Authorization: token ' . $github_api_token
            );
            curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
        }
        $resp = json_decode(curl_exec($curl));
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($http_code !== 200) {
            $this->log(
                $resp->message,
                Project::MSG_WARN
            );
            if (empty($github_api_token)) {
                // Send user a message that callbacks are limited to 60 an hour
                // without GITHUB_API_TOKEN environment variable defined.
                $this->log(
                    "Please set your GITHUB_API_TOKEN environment variable. This will increase your callback limit.",
                    Project::MSG_WARN
                );
            }
            throw new \BuildException(
                "Failed to retrieve versions from GitHub API."
            );
        }

        return $resp;
    }

}//end class
