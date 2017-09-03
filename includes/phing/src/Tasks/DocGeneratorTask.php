<?php

namespace Phing\Ssk\Tasks;

use Project;
use Symfony\Component\Finder\Finder;

require_once 'phing/Task.php';

/**
 * A Phing task to generate an aliases.drushrc.php file.
 */
class DocGeneratorTask extends \Task {

  /**
   * Generates an aliases.drushrc.php file.
   *
   * Either generates a file for:
   *  - all sites in the sites directory.
   *  - a single site to be added to the aliases file (appending).
   */
  public function main() {

    ini_set('xdebug.var_display_max_depth', 5);

    $project = $this->getProject();
    $basedir = $project->getBasedir();
    $targets = $project->getTargets();
    var_dump(count($targets));

    $buildFiles = new Finder();
    $buildFiles
      ->files()
      ->name('*.xml')
      ->in($basedir . '/vendor/ec-europa/ssk/includes/phing');

    $targetsArray = array();
    $wrapperTargets = array();
    $playbookTargets = array();
    $callbackTargets = array();
    $helperTargets = array();

    foreach ($buildFiles as $buildFile) {
      $xml = simplexml_load_file($buildFile->getRealPath());
      $buildFilePath = $buildFile->getRelativePathname();

      foreach ($xml->xpath('//target') as $target) {

        $targetName = (string)$target->attributes()->name;
        $targetDescription = (string)$target->attributes()->description;

        $targetsArray[$targetName] = array(
          'description' => $targetDescription,
          'buildfile' => $buildFilePath,
        );

        if (isset($target->attributes()->depends) && substr($targetName, -9) === '-playbook') {
          $targetDependenciesString = (string)$target->xpath('./@depends')[0];
          $targetDependencies = explode(',', str_replace(' ', '', $targetDependenciesString));
          $callbackTargets = array_merge($callbackTargets, $targetDependencies);
          $targetsArray[$targetName] += array(
            'type' => 'playbook',
            'dependencies' => $targetDependencies,
          );
          $playbookTargets[$targetName] = $targetsArray[$targetName];
        }

        if (count($target->xpath('./phingcall')) == 1 && count($target->xpath('./phingcall[1]/property')) > 0) {
          $props = array();
          $phingCallTarget = (string)$target->xpath('./phingcall[1]/@target')[0];
          if (substr($phingCallTarget, 0, -9) === $targetName) {
            foreach ($target->xpath('./phingcall[1]/property') as $property) {
              $propName = (string)$property->attributes()->name;
              $propValue = (string)$property->attributes()->value;
              $props[] = array(
                'name' => $propName,
                'value' => $propValue,
                'description' => 'Description',
              );
            }
            $targetsArray[$targetName] += array(
              'properties' => $props,
              'phingcall' => $phingCallTarget,
            );
            $wrapperTargets[$targetName] = $targetsArray[$targetName];
          }
        }

        $callbackTargets = array_unique($callbackTargets);

      }
    }

    foreach ($buildFiles as $buildFile) {
      $xml = simplexml_load_file($buildFile->getRealPath());
      $buildFilePath = $buildFile->getRelativePathname();

      foreach ($xml->xpath('//target') as $target) {

        $targetName = (string)$target->attributes()->name;
        $targetDescription = (string)$target->attributes()->description;

        $targetsArray[$targetName] = array(
          'description' => $targetDescription,
          'buildfile' => $buildFilePath,
        );

        if (in_array($targetName, $callbackTargets)) {
          $targetsArray[$targetName] += array(
            'type' => 'callback',
          );
          $callbackTargets[$targetName] = $targetsArray[$targetName];
        }
        else {
          $targetsArray[$targetName] += array(
            'type' => 'helper',
          );
          $helperTargets[$targetName] = $targetsArray[$targetName];
        }
      }
    }
    $this->wrapperTargetTable($wrapperTargets, $playbookTargets, $callbackTargets);
//   var_dump($callbackTargets);
  }
  
  protected function wrapperTargetTable($wrapperTargets, $playbookTargets, $callbackTargets) {
    $output = '';
    foreach ($wrapperTargets as $targetName => $wrapperTarget) {
      $output .= "### " . $targetName . "\n";
      $output .= "<table>\n";
      $output .= "    <thead>\n";
      $output .= "        <tr align=\"left\">\n";
      $output .= "            <th>Description</th>\n";
      $output .= "            <th width=\"100%\">" . $wrapperTarget['description'] . "<img src=\"https://cdn0.iconfinder.com/data/icons/octicons/1024/checklist-20.png\" align=\"right\" /></th>\n";
      $output .= "        </tr>\n";
      $output .= "    </thead>\n";
      $output .= "    <tbody>\n";
      $output .= "        <tr>\n";
      $output .= "            <td colspan=\"2\">\n";
      $output .= "                <details><summary>Properties</summary>\n";
      $output .= "                <table width=\"100%\">\n";
      $output .= "                    <thead>\n";
      $output .= "                        <tr align=\"left\">\n";
      $output .= "                            <th nowrap>Property</th>\n";
      $output .= "                            <th nowrap>Value</th>\n";
      $output .= "                            <th width='\100%\"'>Description</th>\n";
      $output .= "                        </tr>\n";
      $output .= "                    </thead>\n";
      $output .= "                    <tbody>\n";
      foreach ($wrapperTarget['properties'] as $property) {
        $output .= "                        <tr>\n";
        $output .= "                            <td nowrap>" . $property['name'] . "</td>\n";
        $output .= "                            <td nowrap>" . $property['value'] . "</td>\n";
        $output .= "                            <td>" . $property['description'] . "</td>\n";
        $output .= "                        </tr>\n";
      }
      $output .= "                    </tbody>\n";
      $output .= "                </table>\n";
      $output .= "                </details>\n";
      $output .= "            </td>\n";
      $output .= "        </tr>\n";
      $output .= "        <tr>\n";
      $output .= "            <td colspan=\"2\">\n";
      $output .= "                <details><summary>Playbook</summary>\n";
      $output .= "                <table width=\"100%\">\n";
      $output .= "                    <thead>\n";
      $output .= "                        <tr align=\"left\">\n";
      $output .= "                            <th>Callback target</th>\n";
      $output .= "                            <th>Buildfile</th>\n";
      $output .= "                            <th width=\"100%\">Description</th>\n";
      $output .= "                        </tr>\n";
      $output .= "                    </thead>\n";
      $output .= "                    <tbody>\n";
      foreach ($playbookTargets[$targetName . '-playbook']['dependencies'] as $callback) {
        $output .= "                        <tr>\n";
        $output .= "                            <td nowrap>" . $callback . "</td>\n";
        $output .= "                            <td nowrap>" . str_replace('build/', './', $callbackTargets[$callback]['buildfile']) . "</td>\n";
        $output .= "                            <td>" . $callbackTargets[$callback]['description'] . "</td>\n";
        $output .= "                        </tr>\n";
      }
      $output .= "                    </tbody>\n";
      $output .= "                </table>\n";
      $output .= "                </details>\n";
      $output .= "            </td>\n";
      $output .= "        </tr>\n";
      $output .= "    </tbody>\n";
      $output .= "</table>\n\n";
    }
    file_put_contents('/home/verbral/github/ec-europa/subsite/structure.md', $output);
  }

  /**
   * Checks if all properties required for generating the aliases file are present.
   *
   * @throws \BuildException
   *   Thrown when a required property is not present.
   */
  protected function checkRequirements() {
    $required_properties = array('phingDir');
    foreach ($required_properties as $required_property) {
      if (empty($this->$required_property)) {
        throw new \BuildException("Missing required property '$required_property'.");
      }
    }
  }
  /**
   * Sets the Phing directory to generate docs for.
   *
   * @param string $phingdir
   *   The Phing directory to generate docs for.
   */
  public function setPhingdir($phingdir) {
    $this->phingDir = $phingdir;
  }
}
