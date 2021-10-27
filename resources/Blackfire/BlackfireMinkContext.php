<?php

// phpcs:ignoreFile

declare(strict_types=1);

namespace OpenEuropa\Site\Tests\Behat;

use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\MinkContext as BehatMinkContext;
use Blackfire\Bridge\Behat\Context\BlackfireContextTrait;

/**
 * Extends Behat MinkContext and add steps from Drupal MinkContext.
 *
 * @see \Drupal\DrupalExtension\Context\MinkContext
 */
class BlackfireMinkContext extends BehatMinkContext {

  use BlackfireContextTrait;

  /**
   * Visit a given path, and additionally check for HTTP response code 200.
   *
   * @Given I am at :path
   * @When I visit :path
   *
   * @throws UnsupportedDriverActionException
   */
  public function assertAtPath($path) {
    $this->getSession()->visit($this->locatePath($path));
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * @Then I (should )see the text :text
   */
  public function assertTextVisible($text) {
    // Use the Mink Extension step definition.
    $this->assertPageContainsText($text);
  }

  /**
   * @Then I should not see the text :text
   */
  public function assertNotTextVisible($text) {
    // Use the Mink Extension step definition.
    $this->assertPageNotContainsText($text);
  }

  /**
   * @Then I should get a :code HTTP response
   */
  public function assertHttpResponse($code) {
    // Use the Mink Extension step definition.
    $this->assertResponseStatus($code);
  }

  /**
   * @Then I should not get a :code HTTP response
   */
  public function assertNotHttpResponse($code) {
    // Use the Mink Extension step definition.
    $this->assertResponseStatusIsNot($code);
  }

  /**
   * Presses button with specified id|name|title|alt|value.
   *
   * @When I press the :button button
   */
  public function pressButton($button)
  {
    // Wait for any open autocomplete boxes to finish closing.  They block
    // form-submission if they are still open.
    // Use a step 'I press the "Esc" key in the "LABEL" field' to close
    // autocomplete suggestion boxes with Mink.  "Click" events on the
    // autocomplete suggestion do not work.
    try {
      $this->getSession()->wait(1000, 'typeof(jQuery)=="undefined" || jQuery("#autocomplete").length === 0');
    } catch (UnsupportedDriverActionException $e) {
      // The jQuery probably failed because the driver does not support
      // javascript.  That is okay, because if the driver does not support
      // javascript, it does not support autocomplete boxes either.
    }

    $button = $this->fixStepArgument($button);
    $this->getSession()->getPage()->pressButton($button);
  }

  /**
   * @Then I (should )see the heading :heading
   */
  public function assertHeading($heading)
  {
    $element = $this->getSession()->getPage();
    foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $tag) {
      $results = $element->findAll('css', $tag);
      foreach ($results as $result) {
        if ($result->getText() == $heading) {
          return;
        }
      }
    }
    throw new \Exception(sprintf("The text '%s' was not found in any heading on the page %s", $heading, $this->getSession()->getCurrentUrl()));
  }

  /**
   * @When I follow/click :link in the :region( region)
   *
   * @throws \Exception
   *   If region or link within it cannot be found.
   */
  public function assertRegionLinkFollow($link, $region)
  {
    $regionObj = $this->getRegion($region);

    // Find the link within the region
    $linkObj = $regionObj->findLink($link);
    if (empty($linkObj)) {
      throw new \Exception(sprintf('The link "%s" was not found in the region "%s" on the page %s', $link, $region, $this->getSession()->getCurrentUrl()));
    }
    $linkObj->click();
  }

  /**
   * This is the example provided by Blackfire docs.
   *
   * @Given I am on ":landingPage" landing page
   * @When I go to ":landingPage" landing page
   */
  public function iAmOnLandingPage($landingPage)
  {
    $this->disableProfiling();
    $this->visitPath("/$landingPage");

    // You may re-enable profiling and visit other pages
    $this->enableProfiling();
    $this->visitPath('/foo/bar');
  }
}
