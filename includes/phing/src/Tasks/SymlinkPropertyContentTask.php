<?php

/**
 * Symlink property content task.
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
use DirectoryScanner;
use PhingFile;
use Properties;
use ArrayIterator;
use RegexIterator;

/**
 * Symlink property content task.
 *
 * @category Documentation
 * @package  SSK
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/ssk/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */
class SymlinkPropertyContentTask extends RelativeSymlinkTask
{
    /**
     * If this is true, then errors generated during file output will become
     * build errors, and if false, then such errors will be logged, but not
     * thrown.
     *
     * @var boolean
     */
    private $_failonerror = true;

    /**
     * Prefix to be used
     *
     * @var string $prefix
     */
    private $_prefix = '';

    /**
     * Regular expression
     *
     * @var string $regex
     */
    private $_regex = '';

    /**
     * String to be tested
     *
     * @var string
     */
    private $_originDir = '';

    /**
     * Target dir
     *
     * @var string
     */
    private $_targetDir = '';

    /**
     * If true, the task will fail if an error occurs writing the properties
     * file, otherwise errors are just logged.
     *
     * @param bool $failonerror <tt>true</tt> if IO exceptions are reported as
     *                          build exceptions, or <tt>false</tt> if IO
     *                          exceptions are ignored.
     *
     * @return void
     */
    public function setFailOnError($failonerror)
    {
        $this->_failonerror = $failonerror;

    }//end setFailOnError()


    /**
     *  If the prefix is set, then only properties which start with this
     *  prefix string will be recorded. If regex is not set and  if this
     *  is never set, or it is set to an empty string or <tt>null</tt>,
     *  then all properties will be recorded. <P>
     *
     *  For example, if the attribute is set as:
     *    <PRE>&lt;echoproperties  prefix="phing." /&gt;</PRE>
     *  then the property "phing.home" will be recorded, but "phing-example"
     *  will not.
     *
     * @param string $prefix The new prefix value
     *
     * @return void
     */
    public function setPrefix($prefix)
    {
        if ($prefix != null && strlen($prefix) != 0) {
            $this->_prefix = $prefix;
        }

    }//end setPrefix()


    /**
     *  If the regex is set, then only properties whose names match it
     *  will be recorded.  If prefix is not set and if this is never set,
     *  or it is set to an empty string or <tt>null</tt>, then all
     *  properties will be recorded.<P>
     *
     *  For example, if the attribute is set as:
     *    <PRE>&lt;echoproperties  prefix=".*phing.*" /&gt;</PRE>
     *  then the properties "phing.home" and "user.phing" will be recorded,
     *  but "phing-example" will not.
     *
     * @param string $regex The new regex value
     *
     * @return void
     */
    public function setRegex($regex)
    {
        if ($regex != null && strlen($regex) != 0) {
            $this->_regex = $regex;
        }

    }//end setRegex()


    /**
     * Sets the path to the directory of which to create symlinks to.
     *
     * @param string $originDir The path to the directory in which to create
     *                          symlinks to
     *
     * @return void
     */
    public function setOriginDir($originDir)
    {
        $this->_originDir = $originDir;

    }//end setOriginDir()


    /**
     * Sets the path to the directory in which to put the symlinks
     *
     * @param string $targetDir The path to the directory in which to put the
     *                          symlinks
     *
     * @return void
     */
    public function setTargetDir($targetDir)
    {
        $this->_targetDir = $targetDir;

    }//end setTargetDir()


    /**
     * Getter for _link
     *
     * @throws BuildException
     *
     * @return string
     */
    public function getLink()
    {
        if ($this->_targetDir === null) {
            throw new BuildException('Targetdir not set');
        }

        return $this->_targetDir;

    }//end getLink()


    /**
     * Generates an array of directories / files to be linked
     * If _filesets is empty, returns getTarget()
     *
     * @throws BuildException
     *
     * @return array|string
     */
    protected function getMap()
    {

        if ($this->_prefix != null && $this->_regex != null) {
            throw new BuildException("Please specify either prefix or regex, but not both", $this->getLocation());
        }

        if (empty($this->_targetDir)) {
            throw new BuildException("Please specify the target directory to put the symlinks in.", $this->getLocation());
        }

        // copy the properties file
        $allProps = $this->getProject()->getProperties();

        ksort($allProps);
        $props = new Properties();

        if ($this->_regex !== '') {
            $a        = new ArrayIterator($allProps);
            $i        = new RegexIterator($a, $this->_regex, RegexIterator::MATCH, RegexIterator::USE_KEY);
            $allProps = iterator_to_array($i);
        }

        if ($this->_prefix !== '') {
            $a        = new ArrayIterator($allProps);
            $i        = new RegexIterator(
                $a,
                '~^'.preg_quote($this->_prefix, '~').'.*~',
                RegexIterator::MATCH,
                RegexIterator::USE_KEY
            );
            $allProps = iterator_to_array($i);
        }

        $targets = array();
        foreach ($allProps as $name => $value) {
            $dir = new PhingFile($value);
            if ($dir->isFile()) {
                $filename = basename($value);
                $targets[$this->_originDir][] = array(
                    'target' => $this->_originDir.DIRECTORY_SEPARATOR.$filename,
                    'link'   => $this->_targetDir.DIRECTORY_SEPARATOR.$filename,
                );
            }

            if ($dir->isDirectory()) {
                $subdirectories = preg_grep('~'.preg_quote($value).'~', array_values($allProps));
                if (count($subdirectories) == 1) {
                    $directoryToCreate = str_replace(
                        $this->_originDir,
                        $this->_targetDir,
                        $value
                    );

                    $ds = new DirectoryScanner();
                    $ds->setBasedir($dir);
                    $ds->setIncludes("*");
                    $ds->scan();

                    $dsIncludedDirectories = (array) $ds->getIncludedDirectories();
                    $dsIncludedFiles       = (array) $ds->getIncludedFiles();

                    $fsTargets = array_merge(
                        $dsIncludedDirectories,
                        $dsIncludedFiles
                    );
                    // Add each target to the map
                    foreach ($fsTargets as $target) {
                        if (!empty($target)) {
                            $targets[$directoryToCreate][] = array(
                                'target' => $dir.DIRECTORY_SEPARATOR.$target,
                                'link'   => $directoryToCreate.DIRECTORY_SEPARATOR.$target,
                            );
                        }
                    }
                }//end if
            }//end if
        }//end foreach

        return $targets;

    }//end getMap()


    /**
     * Main entry point for task
     *
     * @return bool
     */
    public function main()
    {
        $this->setTaskName('spc');
        $map = $this->getMap();
        // Multiple symlinks
        foreach ($map as $directory => $symlinks) {
            $this->makeDirectory($directory);
            foreach ($symlinks as  $targetName => $symlink) {
                $this->symlink($symlink['target'], $symlink['link']);
            }
        }

        return true;
    }//end main()

    /**
     * Create symlink for a specified target and text.
     *
     * @param string $targetPath Target of symlink
     * @param string $link       Symlink
     *
     * @return void
     */
    protected function symlink($targetPath, $link)
    {
        parent::symlink($targetPath, $link);
    }//end symlink()


    /**
     * Create a directory
     *
     * @param string $dir Directory to be created
     *
     * @return void
     */
    protected function makeDirectory($dir)
    {
        $dir          = new PhingFile($dir);
        $relativePath = str_replace($this->getProject()->getBaseDir(), "", $dir->getAbsolutePath());
        if ($dir === null) {
            throw new BuildException(
                "dir attribute is required",
                $this->getLocation()
            );
        }

        if ($dir->isFile()) {
            throw new BuildException(
                "Unable to create directory as a file already exists with that name: .".$relativePath);
        }

        if (!$dir->exists()) {
            $result = $dir->mkdirs(0777 - umask());
            if (!$result) {
                if ($dir->exists()) {
                    $this->log("A different process or task has already created .".$relativePath);
                    return;
                }

                $msg = "Directory ".$dir->getAbsolutePath(
                )." creation was not successful for an unknown reason";
                throw new BuildException($msg, $this->getLocation());
            }

            $this->log("Created dir: .".$relativePath);
        } else {
            $this->log("Directory exists: .".$relativePath);
        }
    }//end makeDirectory()


    /**
     * Throw exception if error found.
     *
     * @param Exception $exception Exception to throw
     * @param string    $message   Message to display
     * @param int       $level     Exception level
     *
     * @throws BuildException
     *
     * @return void
     */
    private function _failOnErrorAction(Exception $exception = null, $message = '', $level = Project::MSG_INFO)
    {
        if ($this->_failonerror) {
            throw new BuildException(
                $exception !== null ? $exception : $message,
                $this->getLocation()
            );
        } else {
            $this->log(
                $exception !== null && $message === '' ? $exception->getMessage() : $message,
                $level
            );
        }
    }//end failOnErrorAction()

}//end class
