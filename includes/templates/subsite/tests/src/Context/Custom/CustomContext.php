<?php

/**
 * @file
 * Contains \FeatureContext.
 */

namespace Drupal\nexteuropa\Context\Custom;

use Behat\Behat\Context\Context;

/**
 * Defines application features from the specific context.
 */
class CustomContext implements Context {

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
  }

}
