<?php

namespace Phing\Ssk\Tasks;

require_once 'phing/Task.php';

use BuildException;
use DirectoryScanner;
use PhingFile;
use Properties;
use ArrayIterator;
use RegexIterator;

class SymlinkPropertyContentTask extends RelativeSymlinkTask
{
    /**
     * If this is true, then errors generated during file output will become
     * build errors, and if false, then such errors will be logged, but not
     * thrown.
     * @var boolean
     */
    private $failonerror = true;

    /** @var string $prefix */
    private $prefix = '';

    /** @var string $regex */
    private $regex = '';

    /** @var string  */
    private $originDir = '';

      /** @var string  */
    private $targetDir = '';

    /**
     * If true, the task will fail if an error occurs writing the properties
     * file, otherwise errors are just logged.
     *
     * @param  failonerror <tt>true</tt> if IO exceptions are reported as build
     *      exceptions, or <tt>false</tt> if IO exceptions are ignored.
     */
    public function setFailOnError($failonerror)
    {
        $this->failonerror = $failonerror;
    }


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
     */
    public function setPrefix($prefix)
    {
        if ($prefix != null && strlen($prefix) != 0) {
            $this->prefix = $prefix;
        }
    }

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
     */
    public function setRegex($regex)
    {
        if ($regex != null && strlen($regex) != 0) {
            $this->regex = $regex;
        }
    }

    /**
     * Sets the path to the directory of which to create symlinks to.
     *
     * @param string $originDir
     *   The path to the directory in which to create symlinks to
     */
    public function setOriginDir($originDir) {
      $this->originDir = $originDir;
    }

    /**
     * Sets the path to the directory in which to put the symlinks
     *
     * @param string $targetDir
     *   The path to the directory in which to put the symlinks
     */
    public function setTargetDir($targetDir) {
      $this->targetDir = $targetDir;
    }

    /**
     * getter for _link
     *
     * @throws BuildException
     * @return string
     */
    public function getLink()
    {
      if ($this->targetDir === null) {
        throw new BuildException('Targetdir not set');
      }
      return $this->targetDir;
    }

  /**
   * Generates an array of directories / files to be linked
   * If _filesets is empty, returns getTarget()
   *
   * @throws BuildException
   * @return array|string
   */
    protected function getMap() {

      if ($this->prefix != null && $this->regex != null) {
          throw new BuildException("Please specify either prefix or regex, but not both", $this->getLocation());
      }

      if (empty($this->targetDir)) {
        throw new BuildException("Please specify the target directory to put the symlinks in.", $this->getLocation());
      }

      //copy the properties file
      $allProps = $this->getProject()->getProperties();

      ksort($allProps);
      $props = new Properties();

      if ($this->regex !== '') {
        $a = new ArrayIterator($allProps);
        $i = new RegexIterator($a, $this->regex, RegexIterator::MATCH, RegexIterator::USE_KEY);
        $allProps = iterator_to_array($i);
      }
      if ($this->prefix !== '') {
        $a = new ArrayIterator($allProps);
        $i = new RegexIterator(
          $a,
          '~^' . preg_quote($this->prefix, '~') . '.*~',
          RegexIterator::MATCH,
          RegexIterator::USE_KEY
        );
        $allProps = iterator_to_array($i);
      }

      $targets = array();
      foreach ($allProps as $name => $value) {
        $dir = new PhingFile($value);
        if ($dir->exists()) {
          $subdirectories = preg_grep('~' . preg_quote($value) . '~', array_values($allProps));
          if (count($subdirectories) == 1) {
            $directoryToCreate =  str_replace($this->originDir, $this->targetDir, $value);

            $ds = new DirectoryScanner();
            $ds->setBasedir($dir);
            $ds->setIncludes("*");
            $ds->scan();

            $dsIncludedDirectories = (array) $ds->getIncludedDirectories();
            $dsIncludedFiles = (array) $ds->getIncludedFiles();

            $fsTargets = array_merge(
              $dsIncludedDirectories,
              $dsIncludedFiles
            );
            // Add each target to the map
            foreach ($fsTargets as $target) {
              if (!empty($target)) {
                $targets[$directoryToCreate][] = array(
                  'target' => $dir . DIRECTORY_SEPARATOR . $target,
                  'link' => $directoryToCreate . DIRECTORY_SEPARATOR . $target,
                );
              }
            }
          }
        }
      }
      return $targets;
    }

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
    }

    protected function symlink($targetPath, $link){
      parent::symlink($targetPath, $link);
    }
  
    protected function makeDirectory($dir) {
      $dir = new PhingFile($dir);
      $relativePath = str_replace($this->getProject()->getBaseDir(), "", $dir->getAbsolutePath());
      if ($dir === null) {
        throw new BuildException("dir attribute is required", $this->getLocation());
      }
      if ($dir->isFile()) {
        throw new BuildException("Unable to create directory as a file already exists with that name: ." . $relativePath(
          ));
      }
      if (!$dir->exists()) {
        $result = $dir->mkdirs(0777 - umask());
        if (!$result) {
          if ($dir->exists()) {
            $this->log("A different process or task has already created ." . $relativePath);
            return;
          }
          $msg = "Directory " . $dir->getAbsolutePath(
            ) . " creation was not successful for an unknown reason";
          throw new BuildException($msg, $this->getLocation());
        }
        $this->log("Created dir: ." . $relativePath);
      }
      else {
        $this->log("Directory exists: ." . $relativePath);
      }
    }

    /**
     * @param Exception $exception
     * @param string $message
     * @param int $level
     * @throws BuildException
     */
    private function failOnErrorAction(Exception $exception = null, $message = '', $level = Project::MSG_INFO)
    {
        if ($this->failonerror) {
            throw new BuildException(
                $exception !== null ? $exception : $message,
                $this->getLocation()
            );
        } else {
            $this->log(
                $exception !== null && $message === ''
                    ? $exception->getMessage()
                    : $message,
                $level
            );
        }
    }
}
