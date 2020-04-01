<?php

/**
 * Documentation generator.
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

// use Phing\Toolkit\Tasks\PhingHelpTask;
use Project;
use Symfony\Component\Finder\Finder;

require_once 'phing/Task.php';

/**
 * Generate documentation about custom targets provided by toolkit.
 *
 * @category BuildSystem
 * @package  DrupalToolkit
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/toolkit/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */
class DocGeneratorTask extends \Task
{
    private $_buildFile      = '';
    protected $targets       = [];
    protected $buildList     = [];
    protected $stickyTargets = [
      'build-platform',
      'build-subsite-dev',
      'install-clean',
      'install-clone',
      'test-run-phpcs',
      'test-run-behat',
    ];

    public $markup = 'No results found.';

    /**
     * Inherits documentation.
     *
     * @return void
     */
    public function main()
    {
        $this->checkRequirements();
        $this->getBuildList($this->_buildFile);
        $this->parseTargets();
        $this->buildMarkup();
        $this->buildMarkupFull();
    }//end main()

    /**
    * Checks target requirements.
    *
    * @throws \BuildException
    *   Thrown when a required property is not present.
    *
    * @return void
    */
    protected function checkRequirements()
    {
        $required_properties = array('_buildFile');
        foreach ($required_properties as $required_property) {
            if (empty($this->$required_property)) {
                throw new \BuildException(
                    "Missing required property '" . $required_property . "'."
                );
            }
        }
    }

    /**
     * Sets the Phing file to generate docs for.
     *
     * @param string $buildfile The Phing directory to generate docs for.
     *
     * @return void
     */
    public function setBuildFile($buildfile)
    {
        $this->_buildFile = $buildfile;
    }

    /**
     * Generate markup.
     *
     * @return void
     */
    protected function buildMarkup()
    {
        $output = "# Toolkit Phing Targets\n";
        $output .= "This is the list of targets provided by toolkit, please note that this is a auto-generated/partial list, you can check the full list [here](targets-list.md).\n\n";

        $targetsCount = 0;
        foreach ($this->targets as $target) {
            if  (
                isset($target['description'])
                && count(explode(" ", $target['description'])) > 1
                && $target['type'] == 'build'
            ) {

                $detail = $target['description'] ."\n";

                $detail .= "\n##### Example:\n";
                $detail .= "`toolkit\phing " . $target['name'] . "`\n";

                if (isset($target['properties']) && count($target['properties']) > 0) {
                    $detail .= "\n##### Properties:\n";
                    foreach ($target['properties'] as $property) {
                        $detail .= "* " . $property['name'] . "\n";
                    }
                }

                if (isset($target['dependencies']) && count($target['dependencies']) > 0) {
                    $detail .= "\n##### Dependencies: \n";
                    foreach ($target['dependencies'] as $callback) {
                        $detail .= "* " . $callback . "\n";
                    }
                }

                $output .= "\n\n<details><p><summary>" . $target['name'] . "</summary></p>\n";
                if (isset($detail)) {
                  $output .= "\n" . $detail;
                }
                $output .= "\n</details>";

                $targetsCount++;
            }
        }

        $output .= "\n\nThis is a partial list, please check the full list [here](targets-list.md).";

        $this->markup = $output;
        $this->exportMarkup('targets.md');
        echo "Generated documentation for " . $targetsCount . " targets in the main list.\n";
    }

    /**
     * Generate full list markup.
     *
     * @return void
     */
    protected function buildMarkupFull()
    {
        $output = "# Toolkit Phing Targets\n";
        $output .= "This is the list of targets provided by toolkit, please note that this is a auto-generated list.\n\n";

        $targetsCount = 0;
        foreach ($this->targets as $target) {

            $detail = $target['description'] ."\n";

            $detail .= "\n##### Example:\n";
            $detail .= "`toolkit\phing " . $target['name'] . "`\n";

            if (isset($target['properties']) && count($target['properties']) > 0) {
                $detail .= "\n##### Properties:\n";
                foreach ($target['properties'] as $property) {
                  $detail .= "* " . $property['name'] . "\n";
                }
            }
            if (isset($target['dependencies']) && count($target['dependencies']) > 0) {
                $detail .= "\n##### Dependencies: \n";
                foreach ($target['dependencies'] as $callback) {
                  $detail .= "* " . $callback . "\n";
                }
            }

            $output .= "<details><p><summary>" . $target['name'] . "</summary></p>\n";
            if (isset($detail)) {
              $output .= $detail;
            }
            $output .= "\n</details>\n";

            $targetsCount++;
        }

        $this->markup = $output;
        $this->exportMarkup('targets-list.md');
        echo "Generated documentation for " . $targetsCount . " targets in the full list.\n";
    }

    /**
     * Export results to .md file inside docs folder.
     *
     * @param string $filename Name of the file to store information.
     *
     * @return void
     */
    protected function exportMarkup($filename)
    {
        $file = $this->getProject()->getProperty('project.basedir') . "/docs/" . $filename;
        file_put_contents($file, $this->markup);
    }

    /**
    * Helper function to get the full list of files through imports.
    *
    * @param string $buildFile Build file
    * @param int    $level     Level
    * @param string $parent    Parent
    * @param array  $buildList Build list
    *
    * @return array
    *
    * @todo: use the one provided by helper class.
    */
    protected function getBuildList($buildFile, $level = 0, $parent = '', &$buildList = array()
    ) {

        if (is_file($buildFile)) {
            $buildFileXml = simplexml_load_file($buildFile);
            if ($buildFileName = $buildFileXml->xpath('//project/@name')[0]) {
                $buildList[$buildFile] = array(
                    'level'       => $level,
                    'parent'      => $parent,
                    'name'        => (string) $buildFileName,
                    'description' => (string) $buildFileXml->xpath(
                        '//project/@description'
                    )[0],
                );

                foreach ($buildFileXml->xpath('//import[@file]') as $import) {
                    $importFile = (string) $import->attributes()->file;

                    // Replace tokens.
                    if (preg_match_all('/\$\{(.*?)\}/s', $importFile, $matches)) {
                        foreach ($matches[0] as $key => $match) {
                            if (is_object($this->getProject())) {
                                $tokenText  = $this->getProject()->getProperty(
                                    $matches[1][$key]
                                );
                                $importFile = str_replace(
                                    $match,
                                    $tokenText,
                                    $importFile
                                );
                            }
                        }
                    }

                    $this->getBuildList($importFile, ($level + 1), $buildFile, $buildList);
                }
            }//end if

            $this->buildList = $buildList;
        }//end if

    }//end getBuildList()

    /**
    * Parse .xml files and build target array.
    *
    * @return void
    */
    protected function parseTargets()
    {
        $targetsArray      = array();
        $wrapperTargets    = array();
        $buildTargets      = array();
        $callbackTargets   = array();
        $deprecatedTargets = array();
        $helperTargets     = array();

        $buildList = $this->buildList;
        foreach ($buildList as $buildFile => $info) {
            $xml = simplexml_load_file($buildFile);

            foreach ($xml->xpath('//target') as $target) {
                $targetName        = (string) $target->attributes()->name;
                $targetVisibility  = (string) $target->attributes()->hidden == 'true' ? 'hidden' : 'visible';
                $targetDescription = (string) $target->attributes()->description;

                $targetArray = array(
                    'name'        => $targetName,
                    'description' => $targetDescription,
                    'visibility'  => $targetVisibility,
                    'buildfile'   => $buildFile,
                );

                if (strpos($targetName, "build-") === 0) {
                    $targetDependenciesString = (string) $target->xpath(
                        './@depends'
                    )[0];
                    $targetDependencies       = explode(
                        ',',
                        str_replace(
                            ' ',
                            '',
                            $targetDependenciesString
                        )
                    );
                    $targetDependencies = array_filter($targetDependencies);
                    sort($targetDependencies);
                    $callbackTargets = array_merge(
                        $callbackTargets,
                        array_values($targetDependencies)
                    );
                    $targetArray += array(
                        'dependencies' => $targetDependencies,
                        'type'         => 'build',
                    );
                    if (count($targetDependencies) > 1) {
                        $targetArray['type'] = 'build';
                        $buildTargets[]   = $targetName;
                    }
                }

                if (count($target->xpath('./replacedby')) == 1) {
                    $replacedBy = (string) $target->xpath(
                        './replacedby[1]/@target'
                    )[0];
                    $deprecatedTargets[] = $targetName;
                    $targetArray         = array_merge(
                        $targetArray,
                        array(
                            'type'        => 'deprecated',
                            'description' => $replacedBy,
                        )
                    );
                }

                $callbackTargets = array_unique($callbackTargets);
                $targetsArray[]  = $targetArray;
            }//end foreach
        }//end foreach

        foreach ($targetsArray as $key => $targetArray) {
            if (in_array($targetArray['name'], $callbackTargets)
                && !in_array($targetArray['name'], $buildTargets)
            ) {
                $targetsArray[$key]['type'] = 'callback';
            } elseif (!isset($targetArray['type'])) {
                $targetsArray[$key]['type'] = 'helper';
            }
        }
        sort($targetsArray);
        $this->targets = $targetsArray;
    }

}//end class
