<?php

namespace Phing\Ssk\Tasks;

use BuildException;
use Project;

require_once 'phing/Task.php';

/**
 * A Phing task to check for projects not covered by Drupal's security advisory.
 */
class DrushMakeSecureTask extends \Task {

  /**
   * The path to the makefile to check for insecure projects.
   *
   * @var string
   */
  private $makeFile = '';

  /**
   * The type of coverage to fail on
   *
   * @var array
   */
  private $failOn = [];

  /**
   * Generates a Drush make file.
   */
  public function main() {
    // Check if all required data is present.
    $this->checkRequirements();

    // Get the make file content.
    $makeFileContents = file_get_contents($this->makeFile);
    $make = $this->drupalParseInfoFormat($makeFileContents);
    $found = FALSE;

    // Loop over all projects per type.
    foreach ($make as $type => $projects) {
      if (in_array($type, array('projects', 'libraries'))) {
        foreach ($projects as $project => $contents) {
          if (!isset($contents['download']['url'])) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.drupal.org/api-d7/node.xml?field_project_machine_name=" . $project);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            curl_close($ch);

            $data = simplexml_load_string($result);
            $advisory = $data->node->field_security_advisory_coverage;

            if ($advisory == 'revoked') {
              $this->log('This project is not covered by Drupal’s security advisory policy: ' . $project, Project::MSG_ERR);
              $failed = in_array('revoked', $this->failOn) ? TRUE : FALSE;
              $found = TRUE;

            }
            if ($advisory == 'not-covered') {
              $this->log('This project is not covered by Drupal’s security advisory policy: ' . $project, Project::MSG_WARN);
              $failed = in_array('not-covered', $this->failOn) ? TRUE : FALSE;
              $found = TRUE;
            }
          }
        }
      }
    }
    if (!$found) {
      $this->log('No insecure modules found..', Project::MSG_INFO);
    }
    if ($failed) {
      throw new BuildException(
        'Insecure module detected.'
      );
    }
  }

  /**
   * Parses data in Drupal's .info format.
   *
   * @param string $data
   *   The contents of the file.
   */
  public function drupalParseInfoFormat($data)
  {
    $info = array();
    // @codingStandardsIgnoreLine
    if (preg_match_all('@^\s*((?:[^=;\[\]]|\[[^\[\]]*\])+?)\s*=\s*(?:("(?:[^"]|(?<=\\\\)")*")|(\'(?:[^\']|(?<=\\\\)\')*\')|([^\r\n]*?))\s*$@msx', $data, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        // Fetch the key and value string.
        $i = 0;
        foreach (array('key', 'value1', 'value2', 'value3') as $var) {
          $$var = isset($match[++$i]) ? $match[$i] : '';
        }
        $value = stripslashes(substr($value1, 1, -1)) . stripslashes(substr($value2, 1, -1)) . $value3;

        // Parse array syntax.
        $keys = preg_split('/\]?\[/', rtrim($key, ']'));
        $last = array_pop($keys);
        $parent = &$info;

        // Create nested arrays.
        foreach ($keys as $key) {
          if ($key == '') {
            $key = count($parent);
          }
          if (!isset($parent[$key]) || !is_array($parent[$key])) {
            $parent[$key] = array();
          }
          $parent = &$parent[$key];
        }

        // Handle PHP constants.
        if (preg_match('/^\w+$/i', $value) && defined($value)) {
          $value = constant($value);
        }

        // Insert actual value.
        if ($last == '') {
          $last = count($parent);
        }
        $parent[$last] = $value;
      }
    }

    return $info;
  }

  /**
   * Checks if all properties required for generating the makefile are present.
   *
   * @throws \BuildException
   *   Thrown when a required property is not present.
   */
  protected function checkRequirements() {
    $required_properties = array('failOn', 'makeFile');
    foreach ($required_properties as $required_property) {
      if (empty($this->$required_property)) {
        throw new \BuildException("Missing required property '$required_property'.");
      }
    }
  }

  /**
   * Sets the path to the makefile to check.
   *
   * @param string $makeFile
   *   The path to the makefile to check.
   */
  public function setMakeFile($makeFile) {
    $this->makeFile = $makeFile;
  }

  /**
   * Fail on insecure projects. Can be revoked or not-covered.
   *
   * @param $failOn
   */
  public function setFailOn($failOn) {
    $this->failOn = [];
    $token = ' ,;';
    $fail = strtok($failOn, $token);
    while ($fail !== FALSE) {
      $this->failOn[] = $fail;
      $fail = strtok($token);
    }
  }
}
