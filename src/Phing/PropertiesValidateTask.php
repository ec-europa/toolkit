<?php

namespace NextEuropa\Phing;

require_once 'phing/Task.php';

use BuildException;
use FileParserFactory;
use PhingFile;
use Project;
use Properties;

class PropertiesValidateTask extends \Task
{
  /**
   * The source file to check.
   * @var PhingFile
   */
  protected $source = null;

  /**
   * File containing required properties.
   * @var PhingFile
   */
  private $required = null;

  /**
   * File containing forbidden properties.
   * @var PhingFile
   */
  private $forbidden = null;

  /**
   * If this is true, then errors generated during file output will become
   * build errors, and if false, then such errors will be logged, but not
   * thrown.
   * @var boolean
   */
  private $haltonerror = true;

  /**
   * Sets the input file.
   *
   * @param string|PhingFile $source the input file
   */
  public function setSource($source)
  {
    if (is_string($source)) {
      $this->source = new PhingFile($source);
    } else {
      $this->source = $source;
    }
  }

  /**
   *  Set a file to get the required properties.
   *
   * @param string|PhingFile $required file to compare with
   */
  public function setRequired($required)
  {
    if (is_string($required)) {
      $this->required = new PhingFile($required);
    } else {
      $this->required = $required;
    }
  }

  /**
   *  Set a file to get the forbidden properties.
   *
   * @param string|PhingFile $forbidden file to compare with
   */
  public function setForbidden($forbidden)
  {
    if (is_string($forbidden)) {
      $this->forbidden = new PhingFile($forbidden);
    } else {
      $this->forbidden = $forbidden;
    }
  }


  /**
   * If true, the task will fail if an error occurs writing the properties
   * file, otherwise errors are just logged.
   *
   * @param  haltonerror <tt>true</tt> if IO exceptions are reported as build
   *      exceptions, or <tt>false</tt> if IO exceptions are ignored.
   */
  public function setHaltOnError($haltonerror)
  {
    $this->haltonerror = $haltonerror;
  }

  /**
   * Whether to log returned output as MSG_INFO instead of MSG_VERBOSE
   * @var boolean
   */
  protected $logOutput = false;

  /**
   * Whether to log returned output as MSG_INFO instead of MSG_VERBOSE
   *
   * @param boolean $logOutput If output shall be logged visibly
   *
   * @return void
   */
  public function setLogoutput($logOutput)
  {
    $this->logOutput = (bool) $logOutput;
  }

  /**
   *  Run the task.
   *
   * @throws BuildException  trouble, probably file IO
   */
  public function main()
  {
    //copy the properties file
    $allProperties = array();
    $warninglevel = $this->logOutput ? Project::MSG_WARN : Project::MSG_VERBOSE;
    $errorlevel = $this->logOutput ? Project::MSG_ERR : Project::MSG_VERBOSE;

    /* load properties from file if specified, otherwise use Phing's properties */
    if ($this->source != null) {
      // add phing properties
      $allProperties = $this->checkLoadProperties($this->source, "Required properties file");

      if ($this->required == null && $this->forbidden == null) {
        $message = "You must define either a required or forbidden properties file.";
        $this->haltOnErrorAction(null, $message, $errorlevel);
      }
      else {
        if ($this->required != null) {
          $requiredProperties = $this->checkLoadProperties($this->required, "Required properties file");
          $intersect = array_intersect_key($allProperties, $requiredProperties);
          if (count($intersect) != count($requiredProperties)) {
            $missing = array_diff_key($requiredProperties, $intersect);
            $missing_props = array_keys($missing);
            $this->log("Your properties file " . $this->source->getName() . " is missing required properties:", $warninglevel);
            foreach ($missing_props as $missing_prop) {
              $this->log("=> " . $missing_prop, $warninglevel);
            }
            $message = "Properties missing from " . $this->source->getName() . ".";
            $this->haltOnErrorAction(null, $message, $errorlevel);
          }
        }
        if ($this->forbidden != null) {
          $forbiddenProperties = $this->checkLoadProperties($this->forbidden, "Forbidden properties file");
          $forbidden_props = array_intersect_key($forbiddenProperties, $allProperties);
          if (count($intersect) > 0) {
            $this->log("Your properties file " . $this->source->getName() . " contains forbidden properties.", $warninglevel);
            foreach ($forbidden_props as $forbidden_prop) {
              $this->log("=> " . key($missing_prop), $warninglevel);
            }
            $message = "Forbidden properties found in " . $this->source->getName() . ".";
            $this->haltOnErrorAction(null, $message, Project::MSG_ERR);
          }
        }
      }
    }
    elseif ($this->source == null) {
      $message = "You must define a source properties file to check.";
      $this->haltOnErrorAction(null, $message, Project::MSG_ERR);
    }
  }

  /**
   * @param Exception $exception
   * @param string $message
   * @param int $level
   * @throws BuildException
   */
  private function haltOnErrorAction(Exception $exception = null, $message = '', $level = Project::MSG_INFO)
  {
    if ($this->haltonerror) {
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

  /**
   *  Check if we can load the file and return the properties.
   *
   * @param  phingFile $propertiesFile
   * @throws BuildException
   *
   * @return array
   */
  protected function checkLoadProperties($propertiesFile, $propertyType)
  {

    if ($propertiesFile->exists() && $propertiesFile->isDirectory()) {
      $message = $propertyType . " is a directory!";
      $this->haltOnErrorAction(null, $message, Project::MSG_ERR);
      return;
    }

    if ($propertiesFile->exists() && !$propertiesFile->canRead()) {
      $message = "Can not read from the specified " . $propertyType . "!";
      $this->haltOnErrorAction(null, $message, Project::MSG_ERR);
      return;
    }

    $fileParserFactory = new FileParserFactory();
    $fileParser = $fileParserFactory->createParser($propertiesFile->getFileExtension());
    $properties = new Properties(null, $fileParser);
    $properties->load($propertiesFile);

    return $properties->getProperties();
  }
}
