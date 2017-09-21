<?php

/**
 * A Phing help.
 *
 * PHP Version 5 and 7
 *
 * @category BuildSystem
 * @package  DrupalToolkit
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/toolkit/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */

namespace Phing\Ssk\Tasks;

require_once 'phing/Task.php';

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Output\ConsoleOutput;
use Target;

/**
 * A Phing help.
 *
 * @category BuildSystem
 * @package  DrupalToolkit
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/toolkit/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */
class PhingHelpTask extends \Task
{
    /**
     * The location of the build file to generate docs for.
     *
     * @var string
     */
    private $_buildfile = '';

    /**
     * The location of the build file to generate docs for.
     *
     * @var string
     */
    private $_buildlist = '';

    /**
     * An array of help targets that are generated.
     *
     * @var array
     */
    protected $helpTargets = array();


    /**
     * Init this task by creating new instance of the phing task and
     * configuring it's by calling its own init method.
     *
     * @return void
     */
    public function init()
    {
        if (empty($this->getOwningTarget()->getName())) {
            $project       = $this->getProject();
            $buildFileRoot = $project->getProperty('phing.file');
            $buildList     = $this->getBuildList($buildFileRoot);
            $targets       = array();

            foreach ($buildList as $buildFile => $info) {
                if (is_file($buildFile)) {
                    $target = new Target();
                    $target->setName('help-'.$info['name']);

                    $task = clone $this;
                    $task->setBuildFile($buildFile);
                    $task->setOwningTarget($target);

                    $target->addTask($task);
                    $this->project->addTarget('help-'.$info['name'], $target);

                    $targets[$buildFileRoot][] = array(
                        'name'        => $target->getName(),
                        'visibility'  => 'hidden',
                        'description' => $buildList[$buildFile]['description'],
                     );
                }

                if ($buildFile === $buildFileRoot) {
                    $project->setDefaultTarget($target->getName());
                }
            }//end foreach

            $this->project->helpTargets = $targets;
        }//end if

    }//end init()


    /**
     *  Hand off the work to the phing task of ours, after setting it up
     *
     * @throws BuildException on validation failure or if the target didn't
     *  execute.
     *
     * @return void
     */
    public function main()
    {
        $buildFileRoot = $this->getProject()->getProperty('phing.file');
        $buildFile     = $this->_buildFile;
        $buildList     = $this->getBuildList($buildFileRoot);
        $parents       = array();
        $targets       = array();

        if (is_file($this->_buildFile)
            && !empty($this->getOwningTarget()->getName())
        ) {
            foreach ($buildList as $buildFile => $buildInfo) {
                if ($this->_buildFile === $buildFile
                    || in_array($buildInfo['parent'], $parents)
                ) {
                    $parents[] = $buildFile;
                    $targets   = array_merge(
                        $targets,
                        $this->getBuildTargets($buildFile)
                    );
                }
            }

            $targets = array_merge($targets, $this->project->helpTargets);
            $this->printBuildTargets($targets, $buildFile, $buildList);
        }

    }//end main()

    /**
     * Print Build Targets
     *
     * @param array $targets   Targets
     * @param array $buildFile Build file name
     * @param array $buildList Build list
     *
     * @return void
     */
    protected function printBuildTargets($targets, $buildFile, $buildList)
    {
        $output = new ConsoleOutput();
        $table  = new Table($output);
        $table->setHeaders(
            array(
                array(
                    'Target name',
                    'Visibility',
                    'Description',
                ),
            ),
            array(
                new TableCell(
                    $buildList[$buildFile]['name'],
                    array('colspan' => 3)
                )
            )
        );
        foreach ($targets as $file => $targets) {
            $table->addRow(new TableSeparator());
            $table->addRow(
                array(
                    new TableCell(
                        $buildList[$file]['name'],
                        array('colspan' => 3)
                    )
                )
            );
            $table->addRow(new TableSeparator());
            $table->addRows($targets);
        }

        $table->render();

    }//end printBuildTargets()


    /**
     * Helper function to get the targets out of file.
     *
     * @param string $importFile File to import
     *
     * @return array
     */
    protected function getBuildTargets($importFile)
    {

        $targets = array();

        // Replace tokens.
        if (preg_match_all('/\$\{(.*?)\}/s', $importFile, $matches)) {
            foreach ($matches[0] as $key => $match) {
                $tokenText  = $this->getProject()->getProperty(
                    $matches[1][$key]
                );
                $importFile = str_replace($match, $tokenText, $importFile);
            }
        }

        $importFileXml = simplexml_load_file($importFile);

        foreach ($importFileXml->xpath('//target') as $target) {
            $targetName        = (string) $target->attributes()->name;
            $targetVisibility  = (string) $target->attributes()->hidden == 'true' ? 'hidden' : 'visible';
            $targetDescription = (string) $target->attributes()->description;

            $targets[$importFile][] = array(
                'name'        => $targetName,
                'visibility'  => $targetVisibility,
                'description' => $targetDescription,
            );
        }

        return $targets;

    }//end getBuildTargets()


    /**
     * Helper function to get the full list of buildfiles through imports.
     *
     * @param string $buildFile Build file
     * @param int    $level     Level
     * @param string $parent    Parent
     * @param array  $buildList Build list
     *
     * @return array
     */
    public function getBuildList(
        $buildFile,
        $level = 0,
        $parent = '',
        &$buildList = array()
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

                    PhingHelpTask::getBuildList(
                        $importFile,
                        ($level + 1),
                        $buildFile,
                        $buildList
                    );
                }
            }//end if

            return $buildList;
        }//end if

    }//end getBuildList()


    /**
     * Sets the Phing file for which to generate help commands.
     *
     * @param string $buildfile The Phing file for which to generate help commands.
     *
     * @return void
     */
    public function setBuildFile($buildfile)
    {
        $this->_buildFile = $buildfile;
    }//end setBuildFile()


    /**
     * Sets the build list for which we can ask for help.
     *
     * @param string $buildlist The build list for which we can ask for help.
     *
     * @return void
     */
    public function setBuildList($buildlist)
    {
        $this->_buildList = $buildlist;
    }//end setBuildList()


    /**
     * Sets the help targets for the project.
     *
     * @param array $helpTargets The help targets for the project.
     *
     * @return void
     */
    public function setHelpTargets($helpTargets)
    {
        $this->helpTargets = $helpTargets;
    }//end setHelpTargets()


    /**
     * Gets the help targets for the project.
     *
     * @return void
     */
    public function getHelpTargets()
    {
        return $this->helpTargets;
    }//end getHelpTargets()

}//end class
