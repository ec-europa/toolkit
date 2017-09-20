<?php

/**
 * Drush make class.
 *
 * PHP Version 5 and 7
 *
 * @category Documentation
 * @package  SSK
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/ssk/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */

namespace Phing\Ssk\Tasks;

use Symfony\Component\Yaml\Dumper;

require_once 'phing/Task.php';

/**
 * A Phing task to generate a Drush make file.
 *
 * @category Documentation
 * @package  SSK
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/ssk/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */
class DrushMakeFileTask extends \Task
{

    /**
     * The path to the makefile to generate.
     *
     * @var string
     */
    private $_makeFile = '';

    /**
     * The version of Drupal core that this makefile supports.
     *
     * @var string
     */
    private $_coreVersion = '';

    /**
     * The Drush make API version to use. Defaults to 2.
     *
     * @var int
     */
    private $_apiVersion = 2;

    /**
     * The projects to download.
     *
     * @var array
     */
    private $_projects = [];

    /**
     * The default directory to install projects in.
     *
     * @var string
     */
    private $_defaultProjectDir = '';


    /**
     * Generates a Drush make file.
     *
     * @return void
     */
    public function main()
    {
        // Check if all required data is present.
        $this->checkRequirements();

        // Add required properties.
        $contents = [
                     'core' => $this->_coreVersion,
                     'api'  => $this->_apiVersion,
                    ];

        // Add projects.
        foreach ($this->_projects as $project) {
            $contents['projects'][$project]['version'] = null;
        }

        // Add default location for projects.
        if (!empty($this->_defaultProjectDir)) {
            $contents['defaults']['projects']['subdir'] = $this->_defaultProjectDir;
        }

        // Save the makefile.
        $dumper = new Dumper();
        file_put_contents($this->_makeFile, $dumper->dump($contents, 4));

    }//end main()


    /**
     * Checks if all properties required for generating the makefile are present.
     *
     * @throws \BuildException
     *   Thrown when a required property is not present.
     *
     * @return void
     */
    protected function checkRequirements()
    {
        $required_properties = array(
                                '_apiVersion',
                                '_coreVersion',
                                '_makeFile',
                               );
        foreach ($required_properties as $required_property) {
            if (empty($this->$required_property)) {
                throw new \BuildException("Missing required property '$required_property'.");
            }
        }

    }//end checkRequirements()


    /**
     * Sets the path to the makefile to generate.
     *
     * @param string $makeFile The path to the makefile to generate.
     *
     * @return void
     */
    public function setMakeFile($makeFile)
    {
        $this->_makeFile = $makeFile;
    }//end setMakeFile()


    /**
     * Sets the Drupal core version.
     *
     * @param string $coreVersion The Drupal core version. For example '8.x'.
     *
     * @return void
     */
    public function setCoreVersion($coreVersion)
    {
        $this->_coreVersion = $coreVersion;
    }//end setCoreVersion()


    /**
     * Sets the Drush make API version.
     *
     * @param string $apiVersion The Drush make API version.
     *
     * @return void
     */
    public function setApiVersion($apiVersion)
    {
        $this->_apiVersion = $apiVersion;
    }//end setApiVersion()


    /**
     * Sets the list of projects to download.
     *
     * @param string $projects A string containing a list of projects, delimited
     *                         by spaces, commas or semicolons.
     *
     * @return void
     */
    public function setProjects($projects)
    {
        $this->_projects = [];
        $token           = ' ,;';
        $project         = strtok($projects, $token);

        while ($project !== false) {
            $this->_projects[] = $project;
            $project           = strtok($token);
        }

    }//end setProjects()


    /**
     * Sets the default projects directory.
     *
     * @param string $defaultProjectDir The Drupal core version. For example '8.x'.
     *
     * @return void
     */
    public function setDefaultProjectDir($defaultProjectDir)
    {
        $this->_defaultProjectDir = $defaultProjectDir;

    }//end setDefaultProjectDir()


}//end class
