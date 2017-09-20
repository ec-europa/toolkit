<?php

/**
 * PHP CodeSniffer Configuration task.
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

require_once 'phing/Task.php';

use BuildException;
use Project;

/**
 * A Phing task to generate a configuration file for PHP CodeSniffer.
 *
 * @category Documentation
 * @package  SSK
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/ssk/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */
class PhpCodeSnifferConfigurationTask extends \Task
{

    /**
     * The path to the configuration file to generate.
     *
     * @var string
     */
    private $_configFile = '';

    /**
     * The extensions to scan.
     *
     * @var array
     */
    private $_extensions = array();

    /**
     * The list of files and folders to scan.
     *
     * @var array
     */
    private $_files = array();

    /**
     * The path to the global configuration file to generate.
     *
     * @var string
     */
    private $_globalConfig = '';

    /**
     * The list of patterns to ignore.
     *
     * @var array
     */
    private $_ignorePatterns = array();

    /**
     * Whether or not to pass with warnings.
     *
     * @var bool
     */
    private $_passWarnings = false;

    /**
     * The reports format to return.
     *
     * @var array()
     */
    private $_reports = array();

    /**
     * Whether or not to show progress.
     *
     * @var bool
     */
    private $_showProgress = false;

    /**
     * Whether or not to show sniff codes in the report.
     *
     * @var bool
     */
    private $_showSniffCodes = false;

    /**
     * The coding standards to use.
     *
     * @var array
     */
    private $_standards = array();

    /**
     * The install paths of standards.
     *
     * @var string
     */
    private $_installedPaths = '';


    /**
     * Configures PHP CodeSniffer.
     *
     * @return void
     */
    public function main()
    {
        // Check if all required data is present.
        $this->checkRequirements();

        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        // Create the root 'ruleset' element.
        $root_element = $document->createElement('ruleset');
        $root_element->setAttribute('name', 'NextEuropa_default');
        $document->appendChild($root_element);

        // Add the description.
        $element = $document->createElement('description', 'Default PHP CodeSniffer configuration for NextEuropa subsites.');
        $root_element->appendChild($element);

        // Add the coding standards.
        foreach ($this->_standards as $standard) {
            $installedPaths = explode(',', $this->_installedPaths);
            if (substr($standard, -4) === '.xml') {
                if (file_exists($standard)) {
                    $element = $document->createElement('rule');
                    $element->setAttribute('ref', $standard);
                    $root_element->appendChild($element);
                }
            } else {
                foreach ($installedPaths as $installedPath) {
                    $ruleset = $installedPath."/".$standard."/ruleset.xml";
                    if (file_exists($ruleset)) {
                        $element = $document->createElement('rule');
                        $element->setAttribute('ref', $ruleset);
                        $root_element->appendChild($element);
                    }
                }
            }
        }

        // Add the files to check.
        foreach ($this->_files as $file) {
            $element = $document->createElement('file', $file);
            $root_element->appendChild($element);
        }

        // Add file extensions.
        if (!empty($this->_extensions)) {
            $extensions = implode(',', $this->_extensions);
            $this->appendArgument($document, $root_element, $extensions, 'extensions');
        }

        // Add ignore patterns.
        foreach ($this->_ignorePatterns as $pattern) {
            $element = $document->createElement('exclude-pattern', $pattern);
            $root_element->appendChild($element);
        }

        // Add the requested Reports..
        foreach ($this->_reports as $report) {
            // Add the report type.
            $this->appendArgument($document, $root_element, $report, 'report');
        }

        // Add the shorthand options.
        $shorthand_options = array(
                              'p' => 'showProgress',
                              's' => 'showSniffCodes',
                             );

        $options = array_filter(
            $shorthand_options,
            function ($value) {
                return $this->$value;
            }
        );

        if (!empty($options)) {
            $this->appendArgument($document, $root_element, implode('', array_flip($options)));
        }

        // Save the file.
        $configSaved = file_put_contents($this->_configFile, $document->saveXML());

        // If a global configuration file is passed, update this too.
        if (!empty($this->_globalConfig)) {
            $ignore_warnings_on_exit = $this->_passWarnings ? 1 : 0;
            $global_config           = <<<PHP
<?php
 \$phpCodeSnifferConfig = array (
  'default_standard' => '$this->_configFile',
  'ignore_warnings_on_exit' => '$ignore_warnings_on_exit',
);
PHP;
            $globalConfigSaved       = file_put_contents($this->_globalConfig, $global_config);

            if ($configSaved || $globalConfigSaved) {
                if ($configSaved) {
                    $this->setTaskName("config");
                    $this->log("Updating: ".$this->_configFile, Project::MSG_INFO);
                } else {
                    throw new BuildException("Was unable to update: ".$this->_configFile, $this->getLocation());
                }

                if ($globalConfigSaved) {
                    $this->setTaskName("config");
                    $this->log("Updating: ".$this->_globalConfig, Project::MSG_INFO);
                } else {
                    throw new BuildException("Was unable to update .".$this->_configFile, $this->getLocation());
                }
            }
        }//end if

    }//end main()


    /**
     * Appends an argument element to the XML document.
     *
     * This will append an XML element in the following format:
     * <arg name="name" value="value" />
     *
     * @param \DOMDocument $document The document that will contain the argument
     *                               to append.
     * @param \DOMElement  $element  The parent element of the argument to append.
     * @param string       $value    The argument value.
     * @param string       $name     Optional argument name.
     *
     * @return void
     */
    protected function appendArgument(\DOMDocument $document, \DOMElement $element, $value, $name = '')
    {
        $argument = $document->createElement('arg');
        if (!empty($name)) {
            $argument->setAttribute('name', $name);
        }

        if (!empty($value)) {
            $argument->setAttribute('value', $value);
        }

        $element->appendChild($argument);

    }//end appendArgument()


    /**
     * Checks if all properties required for generating the config are present.
     *
     * @throws \BuildException Thrown when a required property is not present.
     *
     * @return void
     */
    protected function checkRequirements()
    {
        $required_properties = array(
                                '_configFile',
                                '_files',
                                '_standards',
                               );
        foreach ($required_properties as $required_property) {
            if (empty($this->$required_property)) {
                throw new \BuildException("Missing required property '$required_property'.");
            }
        }

    }//end checkRequirements()


    /**
     * Sets the path to the configuration file to generate.
     *
     * @param string $configFile The path to the configuration file to generate.
     *
     * @return void
     */
    public function setConfigFile($configFile)
    {
        $this->_configFile = $configFile;

    }//end setConfigFile()


    /**
     * Sets the file extensions to scan.
     *
     * @param string $extensions A string containing file extensions, delimited
     *                           by spaces, commas or semicolons.
     *
     * @return void
     */
    public function setExtensions($extensions)
    {
        $this->_extensions = array();
        $token            = ' ,;';
        $extension        = strtok($extensions, $token);
        while ($extension !== false) {
            $this->_extensions[] = $extension;
            $extension          = strtok($token);
        }

    }//end setExtensions()


    /**
     * Sets the list of files and folders to scan.
     *
     * @param string $files A list of paths, delimited by spaces, commas or
     *                      semicolons.
     *
     * @return void
     */
    public function setFiles($files)
    {
        $this->_files = array();
        $token       = ' ,;';
        $file        = strtok($files, $token);
        while ($file !== false) {
            $this->_files[] = $file;
            $file          = strtok($token);
        }

    }//end setFiles()


    /**
     * Sets the path to the global configuration file to generate.
     *
     * @param string $globalConfig The path to the global configuration file
     *                             to generate.
     *
     * @return void
     */
    public function setGlobalConfig($globalConfig)
    {
        $this->_globalConfig = $globalConfig;

    }//end setGlobalConfig()


    /**
     * Sets the installed_paths configuration..
     *
     * @param string $installedPaths The paths in which the standards are installed.
     *
     * @return void
     */
    public function setInstalledPaths($installedPaths)
    {
        $this->_installedPaths = $installedPaths;

    }//end setInstalledPaths()


    /**
     * Sets the list of patterns to ignore.
     *
     * @param string $ignorePatterns The list of patterns, delimited by spaces,
     *                               commas or semicolons.
     *
     * @return void
     */
    public function setIgnorePatterns($ignorePatterns)
    {
        $this->_ignorePatterns = array();
        $token   = ' ,;';
        $pattern = strtok($ignorePatterns, $token);
        while ($pattern !== false) {
            $this->_ignorePatterns[] = $pattern;
            $pattern = strtok($token);
        }

    }//end setIgnorePatterns()


    /**
     * Sets whether or not to pass with warnings.
     *
     * @param bool $passWarnings Whether or not to pass with warnings.
     *
     * @return void
     */
    public function setPassWarnings($passWarnings)
    {
        $this->_passWarnings = (bool) $passWarnings;

    }//end setPassWarnings()


    /**
     * Sets the report formats to use.
     *
     * @param string $reports A list of report types, delimited by spaces,
     *                        commas or semicolons.
     *
     * @return void
     */
    public function setReports($reports)
    {
        $this->_reports = array();
        $token          = ' ,;';
        $report         = strtok($reports, $token);

        while ($report !== false) {
            $this->_reports[] = $reports;
            $report           = strtok($token);
        }

    }//end setReports()


    /**
     * Sets whether or not to show progress.
     *
     * @param bool $showProgress Whether or not to show progress.
     *
     * @return void
     */
    public function setShowProgress($showProgress)
    {
        $this->_showProgress = (bool) $showProgress;

    }//end setShowProgress()


    /**
     * Sets whether or not to show sniff codes in the report.
     *
     * @param bool $showSniffCodes Whether or not to show sniff codes.
     *
     * @return void
     */
    public function setShowSniffCodes($showSniffCodes)
    {
        $this->_showSniffCodes = (bool) $showSniffCodes;

    }//end setShowSniffCodes()


    /**
     * Sets the coding standards to use.
     *
     * @param string $standards A list of paths, delimited by spaces, commas or
     *                          semicolons.
     *
     * @return void
     */
    public function setStandards($standards)
    {
        $this->_standards = array();
        $token            = ' ,;';
        $standard         = strtok($standards, $token);

        while ($standard !== false) {
            $this->_standards[] = $standard;
            $standard           = strtok($token);
        }

    }//end setStandards()

}//end class
