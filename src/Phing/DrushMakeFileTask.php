<?php

namespace NextEuropa\Phing;

use Symfony\Component\Yaml\Dumper;

require_once 'phing/Task.php';

/**
 * A Phing task to generate a Drush make file.
 */
class DrushMakeFileTask extends \Task {

  /**
   * The path to the makefile to generate.
   *
   * @var string
   */
  private $makeFile = '';

  /**
   * The version of Drupal core that this makefile supports.
   *
   * @var string
   */
  private $coreVersion = '';

  /**
   * The Drush make API version to use. Defaults to 2.
   *
   * @var int
   */
  private $apiVersion = 2;

  /**
   * The projects to download.
   *
   * @var array
   */
  private $projects = [];

  /**
   * The default directory to install projects in.
   *
   * @var string
   */
  private $defaultProjectDir = '';

  /**
   * Generates a Drush make file.
   */
  public function main() {
    // Check if all required data is present.
    $this->checkRequirements();

    // Add required properties.
    $contents = [
      'core' => $this->coreVersion,
      'api' => $this->apiVersion,
    ];

    // Add projects.
    foreach ($this->projects as $project) {
      $contents['projects'][$project]['version'] = NULL;
    }

    // Add default location for projects.
    if (!empty($this->defaultProjectDir)) {
      $contents['defaults']['projects']['subdir'] = $this->defaultProjectDir;
    }

    // Save the makefile.
    $dumper = new Dumper();
    file_put_contents($this->makeFile, $dumper->dump($contents, 4));
  }

  /**
   * Checks if all properties required for generating the makefile are present.
   *
   * @throws \BuildException
   *   Thrown when a required property is not present.
   */
  protected function checkRequirements() {
    $required_properties = array('apiVersion', 'coreVersion', 'makeFile');
    foreach ($required_properties as $required_property) {
      if (empty($this->$required_property)) {
        throw new \BuildException("Missing required property '$required_property'.");
      }
    }
  }

  /**
   * Sets the path to the makefile to generate.
   *
   * @param string $makeFile
   *   The path to the makefile to generate.
   */
  public function setMakeFile($makeFile) {
    $this->makeFile = $makeFile;
  }

  /**
   * Sets the Drupal core version.
   *
   * @param string $coreVersion
   *   The Drupal core version. For example '8.x'.
   */
  public function setCoreVersion($coreVersion) {
    $this->coreVersion = $coreVersion;
  }

  /**
   * Sets the Drush make API version.
   *
   * @param string $apiVersion
   *   The Drush make API version.
   */
  public function setApiVersion($apiVersion) {
    $this->apiVersion = $apiVersion;
  }

  /**
   * Sets the list of projects to download.
   *
   * @param string $projects
   *   A string containing a list of projects, delimited by spaces, commas or
   *   semicolons.
   */
  public function setProjects($projects) {
    $this->projects = [];
    $token = ' ,;';
    $project = strtok($projects, $token);
    while ($project !== FALSE) {
      $this->projects[] = $project;
      $project = strtok($token);
    }
  }

  /**
   * Sets the default projects directory.
   *
   * @param string $defaultProjectDir
   *   The Drupal core version. For example '8.x'.
   */
  public function setDefaultProjectDir($defaultProjectDir) {
    $this->defaultProjectDir = $defaultProjectDir;
  }

}
