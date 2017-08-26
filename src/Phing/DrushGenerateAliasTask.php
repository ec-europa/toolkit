<?php

namespace NextEuropa\Phing;

use Project;
use Symfony\Component\Finder\Finder;

require_once 'phing/Task.php';

/**
 * A Phing task to generate a Drush make file.
 */
class DrushGenerateAliasTask extends \Task {

  /**
   * The name of the alias to generate.
   *
   * @var string
   */
  private $aliasName = '';

  /**
   * The uri of the alias to generate.
   *
   * @var string
   */
  private $aliasUri = '';

  /**
   * The root directory of the website.
   *
   * @var string
   */
  private $siteRoot = '';

  /**
   * The directory to save the aliases in.
   *
   * @var string
   */
  private $drushDir = '/sites/all/drush';

  /**
   * Generates a Drush make file.
   */
  public function main() {
    // Check if all required data is present.
    $this->checkRequirements();

    $drushDir = $this->drushDir == '/sites/all/drush' ? $this->siteRoot . $this->drushDir : $this->drushDir;
    $aliasesFile = $drushDir . '/aliases.drushrc.php';

    $aliases = array(
      'default' => array(
        'uri' => 'default',
        'root' => $this->siteRoot
      )
    );

    if (empty($this->aliasName)) {

      $sites = new Finder();
      $sites
        ->directories()
        ->depth('== 0')
        ->exclude('all')
        ->in($this->siteRoot . '/sites');

      foreach ($sites as $site) {
        $aliases[$site->getBasename()] = array(
          'uri' => $site->getBasename(),
          'root' => $aliases['default']['root'],
        );
      }
    }
    else {
      $aliases += $this->loadAliases($aliasesFile);
      $aliases[$this->aliasName] = array(
        'uri' => $this->aliasName,
        'root' => $aliases['default']['root'],
      );
    }

    $aliasesArray = "<?php \n\n" . var_export($aliases, true) . ";";

    if (file_put_contents($aliasesFile, $aliasesArray)) {
      $this->log("Succesfully wrote aliases to file '" . $aliasesFile . "'", Project::MSG_INFO);
    }
    else {
      $this->log("Was unable to write aliases to file '" . $aliasesFile . "'", Project::MSG_WARN);
    }
  }

  /**
   * Checks if all properties required for generating the makefile are present.
   *
   * @throws \BuildException
   *   Thrown when a required property is not present.
   */
  protected function checkRequirements() {
    $required_properties = array('siteRoot');
    foreach ($required_properties as $required_property) {
      if (empty($this->$required_property)) {
        throw new \BuildException("Missing required property '$required_property'.");
      }
    }
  }

  protected function loadAliases($aliasesFile) {
    if (is_file($aliasesFile)) {
      return include $aliasesFile;
    }
    return array();
  }

  /**
   * Sets the name of the alias to set.
   *
   * @param string $aliasName
   *   The name of the alias to set.
   */
  public function setAliasName($aliasName) {
    $this->aliasName = $aliasName;
  }

  /**
   * Sets the uri of tha alias to set.
   *
   * @param string $aliasUri
   *   The uri of the alias to set.
   */
  public function setAliasUri($aliasUri) {
    $this->aliasUri = $aliasUri;
  }

  /**
   * Sets the root of the Drupal site.
   *
   * @param string $siteRoot
   *   The root of the Drupal site.
   */
  public function setSiteRoot($siteRoot) {
    $this->siteRoot = $siteRoot;
  }

  /**
   * Sets the diurectory of drush to place the aliases in.
   *
   * @param string $drushDir
   *   The Drush directory to place the aliases in.
   */
  public function setDrushDir($drushDir) {
    $this->drushDir = $drushDir;
  }

}
