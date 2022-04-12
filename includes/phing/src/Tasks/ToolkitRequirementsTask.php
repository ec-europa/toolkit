<?php

namespace Phing\Toolkit\Tasks;

use Composer\Semver\Semver;

require_once 'phing/Task.php';

/**
 * A Phing task to fetch and check the requirements from the endpoint.
 *
 * @category BuildSystem
 * @package  DrupalToolkit
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/toolkit/blob/master/includes/phing/src/Tasks/ToolkitRequirementsTask.php
 */
class ToolkitRequirementsTask extends \Task
{
  /**
   * Endpoint url to be used as base url.
   *
   * @var string
   */
  protected $endpoint;

  /**
   * The composer.lock path.
   *
   * @var string
   */
  protected $composer;

  /**
   * The drupal bootstrap path.
   *
   * @var string
   */
  protected $bootstrap;

  /**
   * Sets the endpoint url.
   *
   * @param string $endpointUrl
   *   The endpoint url.
   */
  public function setEndpoint($endpoint)
  {
    $this->endpoint = $endpoint;
  }

  /**
   * Sets the composer.lock path.
   *
   * @param string $composer
   *   The path.
   */
  public function setComposer($composer)
  {
    $this->composer = $composer;
  }

  /**
   * Sets the bootstrap path.
   *
   * @param string $bootstrap
   *   The path.
   */
  public function setBootstrap($bootstrap)
  {
    $this->bootstrap = $bootstrap;
  }

  /**
   * Check toolkit requirements.
   */
  public function main() 
  {
    $this->checkRequirements();
    $php_check = $toolkit_check = $drupal_check = $endpoint_check = $nextcloud_check = $asda_check = 'FAIL';
    $php_version = $toolkit_version = $drupal_version = '';

    // Get session token.
    $options = [
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_HEADER         => false,  // don't return headers
      CURLOPT_FOLLOWLOCATION => true,   // follow redirects
      CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
      CURLOPT_ENCODING       => '',     // handle compressed
      CURLOPT_USERAGENT      => 'Quality Assurance pipeline', // name of client
      CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
    ];
    $ch = curl_init("$this->endpoint/session/token");
    curl_setopt_array($ch, $options);
    $token = curl_exec($ch);
    curl_close($ch);
    if (empty($token)) {
      $this->fail('Could not get session token.');
    }

    // Get requirements.
    $ch = curl_init("$this->endpoint/api/v1/toolkit-requirements");
    $header = [
      'Authorization: Basic ' . getenv('QA_API_BASIC_AUTH'),
      "X-CSRF-Token: $token",
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    if (empty($result)) {
      $this->fail('Fail to get data from endpoint.');
    } else {
      $endpoint_check = 'OK';
      $data = json_decode($result, true);
      if (empty($data) || !isset($data['toolkit'])) {
        $this->fail('Invalid data from endpoint.');
      }

      // Handle PHP version.
      $php_version = phpversion();
      $isValid = version_compare($php_version, $data['php_version']);
      $php_check = ($isValid >= 0) ? 'OK' : 'FAIL';

      // Handle Toolkit version.
      if (!($toolkit_version = $this->getPackagePropertyFromComposer('ec-europa/toolkit'))) {
        $toolkit_check = 'FAIL (not found)';
      } else {
        $toolkit_check = Semver::satisfies($toolkit_version, $data['toolkit']) ? 'OK' : 'FAIL';
      }
      // Handle Drupal version.
      if (!($drupal_version = $this->getDrupalVersion())) {
        $drupal_check = 'FAIL (not found)';
      } else {
        $drupal_check = Semver::satisfies($drupal_version, $data['drupal']) ? 'OK' : 'FAIL';
      }
    }

    // Handle ASDA.
    if (!empty(getenv('ASDA_USER')) && !empty(getenv('ASDA_PASSWORD'))) {
      $asda_check = 'OK';
    } else {
      $asda_check .= ' (Missing environment variable(s):';
      $asda_check .= empty(getenv('ASDA_USER')) ? ' ASDA_USER' : '';
      $asda_check .= empty(getenv('ASDA_PASSWORD')) ? ' ASDA_PASSWORD' : '';
      $asda_check .= ')';
    }
    // Handle NEXTCLOUD.
    if (!empty(getenv('NEXTCLOUD_USER')) && !empty(getenv('NEXTCLOUD_PASS'))) {
      $nextcloud_check = 'OK';
    } else {
      $nextcloud_check .= ' (Missing environment variable(s):';
      $nextcloud_check .= empty(getenv('NEXTCLOUD_USER')) ? ' NEXTCLOUD_USER' : '';
      $nextcloud_check .= empty(getenv('NEXTCLOUD_PASS')) ? ' NEXTCLOUD_PASS' : '';
      $nextcloud_check .= ')';
    }

    echo sprintf(
      "Required checks:
=============================
Checking PHP version: %s (%s)
Checking Toolkit version: %s (%s)
Checking Drupal version: %s (%s)

Optional checks:
=============================
Checking QA Endpoint access: %s
Checking ASDA configuration: %s
Checking NEXTCLOUD configuration: %s\n",
      $php_check,
      $php_version,
      $toolkit_check,
      $toolkit_version,
      $drupal_check,
      $drupal_version,
      $endpoint_check,
      $asda_check,
      $nextcloud_check
    );

    if ($php_check !== 'OK' || $toolkit_check !== 'OK' || $drupal_check !== 'OK') {
      $this->fail('');
    }
  }

  /**
   * Checks if all properties are present.
   *
   * @throws \BuildException
   *   Thrown when a required property is not present.
   */
  protected function checkRequirements() 
  {
    $required_properties = ['endpoint', 'composer', 'bootstrap'];
    foreach ($required_properties as $required_property) {
      if (empty($this->$required_property)) {
        $this->fail("Missing required property '$required_property'.");
      }
    }

    if (empty(getenv('QA_API_BASIC_AUTH'))) {
      $this->fail("Missing required env var 'QA_API_BASIC_AUTH'.");
    }

    if (!file_exists($this->composer)) {
      $this->fail("Could not find the file '$this->composer'.");
    }

    if (!file_exists($this->bootstrap)) {
      $this->fail("Could not find the file '$this->bootstrap'.");
    }
  }

  /**
   * Helper to get the drupal core version.
   *
   * @return false|string
   *   String with drupal version, false if fail.
   */
  private function getDrupalVersion()
  {
    include_once $this->bootstrap;
    return !empty(VERSION) ? VERSION : false;
  }

  /**
   * Helper to return a property from a package in the composer.lock file.
   *
   * @param $package
   *   The package to search.
   * @param $prop
   *   The property to return. Default to 'version'.
   *
   * @return false|mixed
   *   The property value, false if not found.
   */
  private function getPackagePropertyFromComposer($package, $prop = 'version')
  {
    $composer = json_decode(file_get_contents($this->composer), true);
    if ($composer) {
      $type = 'packages-dev';
      $index = array_search($package, array_column($composer[$type], 'name'));
      if ($index === false) {
        $type = 'packages';
        $index = array_search($package, array_column($composer[$type], 'name'));
      }
      if ($index !== false && isset($composer[$type][$index][$prop])) {
        return $composer[$type][$index][$prop];
      }
    }
    return false;
  }

  /**
   * Helper to foce task to fail.
   *
   * @param $message
   *   The message to show.
   *
   * @return mixed
   */
  private function fail($message)
  {
    throw new \BuildException($message);
  }

}
