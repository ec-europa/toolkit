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
   * The location of the build file to generate docs for.
   *
   * @var string
   */
  private $buildfile = '';

  private function getBuildList($buildFile, &$buildList = array()) {

    if ($xml = simplexml_load_file($buildFile)) {
      $importFiles = array();

      foreach ($xml->xpath('//import[@file]') as $import) {
        $importFile = (string)$import->attributes()->file;
        // Replace tokens.
        if (preg_match_all('/\$\{(.*?)\}/s', $importFile, $matches)) {
          foreach ($matches[0] as $key => $match) {
            $tokenText = $this->getProject()->getProperty($matches[1][$key]);
            $importFile = str_replace($match, $tokenText, $importFile);
           }
        }
        $importFiles[] = $importFile;
      }
      array_walk_recursive($buildList,
        function (&$v, $k, $u) {
          if ($v == $u[0]) {
            $v = array($u[0] => $u[1]);
          }
        },
        [$buildFile, $importFiles]);

      foreach($importFiles as $importFile) {
        $this->getBuildList($importFile, $buildList);
      }
    }
  }

  /**
   * Generates an aliases.drushrc.php file.
   *
   * Either generates a file for:
   *  - all sites in the sites directory.
   *  - a single site to be added to the aliases file (appending).
   */
  public function main() {

    ini_set('xdebug.var_display_max_depth', 10);

    $project = $this->getProject();
    $basedir = $project->getBasedir();
    $buildFile = $this->buildFile;
    $buildList = array($buildFile);
    $this->getBuildList($buildFile, $buildList);

    $buildFiles = new Finder();
    $buildFiles
      ->files()
      ->name('*.xml')
      ->in($basedir . '/vendor/ec-europa/ssk/includes/phing');

    $targetsArray = array();
    $wrapperTargets = array();
    $playbookTargets = array();
    $callbackTargets = array();
    $deprecatedTargets = array();
    $helperTargets = array();

    foreach ($buildFiles as $buildFile) {
      $buildFilePath = $buildFile->getRealPath();
      $xml = simplexml_load_file($buildFilePath);

      foreach ($xml->xpath('//target') as $target) {

        $targetName = (string)$target->attributes()->name;
        $targetVisibility = (string)$target->attributes()->hidden == 'true' ? 'hidden' : 'visible';
        $targetDescription = (string)$target->attributes()->description;

        $targetArray = array(
          'name' => $targetName,
          'description' => $targetDescription,
          'visibility' => $targetVisibility,
          'buildfile' => $buildFilePath,
        );

        if (isset($target->attributes()->depends)) {
          $targetDependenciesString = (string)$target->xpath('./@depends')[0];
          $targetDependencies = explode(',', str_replace(' ', '', $targetDependenciesString));
          $callbackTargets = array_merge($callbackTargets, $targetDependencies);
          $targetArray += array(
            'dependencies' => $targetDependencies,
            'type' => 'playbook',
          );
          if (count($targetDependencies) > 1) {
            $targetArray['type'] = 'playbook';
            $playbookTargets[] = $targetArray;
          }
        }

        if (count($target->xpath('./replacedby')) == 1) {
          $replacedBy = (string)$target->xpath('./replacedby[1]/@target')[0];
          $deprecatedTargets[] = $targetName;
          $targetArray = array_merge($targetArray, array(
            'type' => 'deprecated',
            'description' => $replacedBy,
          ));
        }

        $callbackTargets = array_unique($callbackTargets);
        $targetsArray[] = $targetArray;

      }
    }

    foreach ($targetsArray as $key => $targetArray) {
      if (in_array($targetArray['name'], $callbackTargets)) {
        $targetsArray[$key]['type'] = 'callback';
      }
      elseif (!isset($targetArray['type'])) {
        $targetsArray[$key]['type'] = 'helper';
      }
    }
    $this->wrapperTargetTable($wrapperTargets, $playbookTargets, $callbackTargets);

    $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($buildList), \RecursiveIteratorIterator::SELF_FIRST);
    $output = '';
    for($iterator; $iterator->valid(); $iterator->next())
    {
      $depth = ($iterator->getDepth() + 1) / 2;
      if (is_file($iterator->key())) {
        $buildFile = $iterator->key();
        $xml = simplexml_load_file($buildFile);
        $targets = array_filter($targetsArray, function($v, $k) use ($buildFile) {
          return $v['buildfile'] === $buildFile;
        }, ARRAY_FILTER_USE_BOTH);
        $projectName = ucfirst((string) $xml->xpath('//project/@name')[0]);
        $output .= str_repeat('#', $depth) . ' ' . $projectName . "\n";
        if (!empty($projectName) && count($targets) > 1){
          $output .= "<table>\n";
          $output .= "    <thead>\n";
          $output .= "        <tr align=\"left\">\n";
          $output .= "            <th nowrap>Target type</th>\n";
          $output .= "            <th nowrap>Name</th>\n";
          $output .= "            <th nowrap>Description</th>\n";
          $output .= "        </tr>\n";
          $output .= "    </thead>\n";
          $output .= "    <tbody>\n";
          foreach ($targets as $targetName => $target) {
            $output .= "        <tr>\n";
            $output .= "            <td nowrap>\n";
            if ($target['visibility'] === 'visible') {
              $output .= "                <img src=\"https://cdn0.iconfinder.com/data/icons/octicons/1024/eye-16.png\" align=\"left\" alt=\"visible\" />\n";
            }
            else {
              $output .= "                <img src=\"https://cdn0.iconfinder.com/data/icons/octicons/1024/gist-secret-20.png\" align=\"left\" alt=\"hidden\" />\n";
            }
            switch ($target['type']) {
              case 'wrapper':
                $output .= "                <img src=\"https://cdn0.iconfinder.com/data/icons/octicons/1024/star-20.png\" align=\"left\" alt=\"wrapper\" />\n";
                break;
              case 'playbook':
                $output .= "                <img src=\"https://cdn0.iconfinder.com/data/icons/octicons/1024/three-bars-20.png\" align=\"left\" alt=\"playbook\" />\n";
                break;
              case 'deprecated':
                $output .= "                <img src=\"https://cdn0.iconfinder.com/data/icons/octicons/1024/trashcan-20.png\" align=\"left\" alt=\"deprecated\" />\n";
                break;
              case 'helper':
                $output .= "                <img src=\"https://cdn0.iconfinder.com/data/icons/octicons/1024/tools-16.png\" align=\"left\" alt=\"helper\" />\n";
                break;
              case 'callback':
                $output .= "                <img src=\"https://cdn0.iconfinder.com/data/icons/octicons/1024/zap-20.png\" align=\"left\" alt=\"callback\" />\n";
                break;
            }
            $output .= "            </td>\n";
            $output .= "            <td nowrap>" . $target['name'] . "</td>\n";
            $output .= "            <td width=\"80%\">" . $target['description'] . "</td>\n";
            $output .= "        </tr>\n";
          }
          $output .= "    </tbody>\n";
          $output .= "</table>\n\n";

        }
      }
    }
    echo "$output";
//   var_dump($targetsArray);
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
   * Sets the Phing file to generate docs for.
   *
   * @param string $buildfile
   *   The Phing directory to generate docs for.
   */
  public function setBuildFile($buildfile) {
    $this->buildFile = $buildfile;
  }
}
