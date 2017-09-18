<?php

namespace Phing\Ssk\Tasks;

require_once 'phing/Task.php';

use BuildException;
use Project;

/**
 * A Phing task to generate a configuration file for PHP CodeSniffer.
 */
class PhpCodeSnifferConfigurationTask extends \Task
{

    /**
     * The path to the configuration file to generate.
     *
     * @var string
     */
    private $configFile = '';

    /**
     * The extensions to scan.
     *
     * @var array
     */
    private $extensions = array();

    /**
     * The list of files and folders to scan.
     *
     * @var array
     */
    private $files = array();

    /**
     * The path to the global configuration file to generate.
     *
     * @var string
     */
    private $globalConfig = '';

    /**
     * The list of patterns to ignore.
     *
     * @var array
     */
    private $ignorePatterns = array();

    /**
     * Whether or not to pass with warnings.
     *
     * @var bool
     */
    private $passWarnings = false;

    /**
     * The reports format to return.
     *
     * @var array()
     */
    private $reports = array();

    /**
     * Whether or not to show progress.
     *
     * @var bool
     */
    private $showProgress = false;

    /**
     * Whether or not to show sniff codes in the report.
     *
     * @var bool
     */
    private $showSniffCodes = false;

    /**
     * The coding standards to use.
     *
     * @var array
     */
    private $standards = array();

    /**
     * The install paths of standards.
     *
     * @var string
     */
    private $installedPaths = '';


    /**
     * Configures PHP CodeSniffer.
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
        foreach ($this->standards as $standard) {
            $installedPaths = explode(',', $this->installedPaths);
            if (substr($standard, -4) === '.xml') {
                if (file_exists($standard)) {
                    $element = $document->createElement('rule');
                    $element->setAttribute('ref', $standard);
                    $root_element->appendChild($element);
                }
            }
            else {
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
        foreach ($this->files as $file) {
            $element = $document->createElement('file', $file);
            $root_element->appendChild($element);
        }

        // Add file extensions.
        if (!empty($this->extensions)) {
            $extensions = implode(',', $this->extensions);
            $this->appendArgument($document, $root_element, $extensions, 'extensions');
        }

        // Add ignore patterns.
        foreach ($this->ignorePatterns as $pattern) {
            $element = $document->createElement('exclude-pattern', $pattern);
            $root_element->appendChild($element);
        }

        // Add the requested Reports..
        foreach ($this->reports as $report) {
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
        $configSaved = file_put_contents($this->configFile, $document->saveXML());

        // If a global configuration file is passed, update this too.
        if (!empty($this->globalConfig)) {
            $ignore_warnings_on_exit = $this->passWarnings ? 1 : 0;
            $global_config           = <<<PHP
<?php
 \$phpCodeSnifferConfig = array (
  'default_standard' => '$this->configFile',
  'ignore_warnings_on_exit' => '$ignore_warnings_on_exit',
);
PHP;
            $globalConfigSaved       = file_put_contents($this->globalConfig, $global_config);

            if ($configSaved || $globalConfigSaved) {
                if ($configSaved) {
                    $this->setTaskName("config");
                    $this->log("Updating: ".$this->configFile, Project::MSG_INFO);
                }
                else {
                    throw new BuildException("Was unable to update: ".$this->configFile, $this->getLocation());
                }

                if ($globalConfigSaved) {
                    $this->setTaskName("config");
                    $this->log("Updating: ".$this->globalConfig, Project::MSG_INFO);
                }
                else {
                    throw new BuildException("Was unable to update .".$this->configFile, $this->getLocation());
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
     * @param \DOMDocument $document
     *   The document that will contain the argument to append.
     * @param \DOMElement  $element
     *   The parent element of the argument to append.
     * @param string       $value
     *   The argument value.
     * @param string       $name
     *   Optional argument name.
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
     * @throws \BuildException
     *   Thrown when a required property is not present.
     */
    protected function checkRequirements()
    {
        $required_properties = array(
                                'configFile',
                                'files',
                                'standards',
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
     * @param string $configFile
     *   The path to the configuration file to generate.
     */
    public function setConfigFile($configFile)
    {
        $this->configFile = $configFile;

    }//end setConfigFile()


    /**
     * Sets the file extensions to scan.
     *
     * @param string $extensions
     *   A string containing file extensions, delimited by spaces, commas or
     *   semicolons.
     */
    public function setExtensions($extensions)
    {
        $this->extensions = array();
        $token            = ' ,;';
        $extension        = strtok($extensions, $token);
        while ($extension !== false) {
            $this->extensions[] = $extension;
            $extension          = strtok($token);
        }

    }//end setExtensions()


    /**
     * Sets the list of files and folders to scan.
     *
     * @param string $files
     *   A list of paths, delimited by spaces, commas or semicolons.
     */
    public function setFiles($files)
    {
        $this->files = array();
        $token       = ' ,;';
        $file        = strtok($files, $token);
        while ($file !== false) {
            $this->files[] = $file;
            $file          = strtok($token);
        }

    }//end setFiles()


    /**
     * Sets the path to the global configuration file to generate.
     *
     * @param string $globalConfig
     *   The path to the global configuration file to generate.
     */
    public function setGlobalConfig($globalConfig)
    {
        $this->globalConfig = $globalConfig;

    }//end setGlobalConfig()


    /**
     * Sets the installed_paths configuration..
     *
     * @param string $installedPaths
     *   The paths in which the standards are installed..
     */
    public function setInstalledPaths($installedPaths)
    {
        $this->installedPaths = $installedPaths;

    }//end setInstalledPaths()


    /**
     * Sets the list of patterns to ignore.
     *
     * @param string $ignorePatterns
     *   The list of patterns, delimited by spaces, commas or semicolons.
     */
    public function setIgnorePatterns($ignorePatterns)
    {
        $this->ignorePatterns = array();
        $token   = ' ,;';
        $pattern = strtok($ignorePatterns, $token);
        while ($pattern !== false) {
            $this->ignorePatterns[] = $pattern;
            $pattern = strtok($token);
        }

    }//end setIgnorePatterns()


    /**
     * Sets whether or not to pass with warnings.
     *
     * @param bool $passWarnings
     *   Whether or not to pass with warnings.
     */
    public function setPassWarnings($passWarnings)
    {
        $this->passWarnings = (bool) $passWarnings;

    }//end setPassWarnings()


    /**
     * Sets the report formats to use.
     *
     * @param string $reports
     *   A list of report types, delimited by spaces, commas or semicolons.
     */
    public function setReports($reports)
    {
        $this->reports = array();
        $token         = ' ,;';
        $report        = strtok($reports, $token);
        while ($report !== false) {
            $this->reports[] = $reports;
            $report          = strtok($token);
        }

    }//end setReports()


    /**
     * Sets whether or not to show progress.
     *
     * @param bool $showProgress
     *   Whether or not to show progress.
     */
    public function setShowProgress($showProgress)
    {
        $this->showProgress = (bool) $showProgress;

    }//end setShowProgress()


    /**
     * Sets whether or not to show sniff codes in the report.
     *
     * @param bool $showSniffCodes
     *   Whether or not to show sniff codes.
     */
    public function setShowSniffCodes($showSniffCodes)
    {
        $this->showSniffCodes = (bool) $showSniffCodes;

    }//end setShowSniffCodes()


    /**
     * Sets the coding standards to use.
     *
     * @param string $standards
     *   A list of paths, delimited by spaces, commas or semicolons.
     */
    public function setStandards($standards)
    {
        $this->standards = array();
        $token           = ' ,;';
        $standard        = strtok($standards, $token);
        while ($standard !== false) {
            $this->standards[] = $standard;
            $standard          = strtok($token);
        }

    }//end setStandards()


}//end class
