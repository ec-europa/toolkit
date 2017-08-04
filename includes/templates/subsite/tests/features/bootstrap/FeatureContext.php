<?php

/**
 * @file
 * Contains \FeatureContext.
 */

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Contains generic step definitions.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Checks that a 403 Access Denied error occurred.
   *
   * @Then I should get an access denied error
   */
  public function assertAccessDenied() {
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Checks that the given select field has the options listed in the table.
   *
   * @Then I should have the following options for :select:
   */
  public function assertSelectOptions($select, TableNode $options) {
    // Retrieve the specified field.
    if (!$field = $this->getSession()->getPage()->findField($select)) {
      throw new ExpectationException("Field '$select' not found.", $this->getSession());
    }

    // Check that the specified field is a <select> field.
    $this->assertElementType($field, 'select');

    // Retrieve the options table from the test scenario and flatten it.
    $expected_options = $options->getColumnsHash();
    array_walk($expected_options, function (&$value) {
      $value = reset($value);
    });

    // Retrieve the actual options that are shown in the page.
    $actual_options = $field->findAll('css', 'option');

    // Convert into a flat list of option text strings.
    array_walk($actual_options, function (&$value) {
      $value = $value->getText();
    });

    // Check that all expected options are present.
    foreach ($expected_options as $expected_option) {
      if (!in_array($expected_option, $actual_options)) {
        throw new ExpectationException("Option '$expected_option' is missing from select list '$select'.", $this->getSession());
      }
    }
  }

  /**
   * Checks that the given select field doesn't have the listed options.
   *
   * @Then I should not have the following options for :select:
   */
  public function assertNoSelectOptions($select, TableNode $options) {
    // Retrieve the specified field.
    if (!$field = $this->getSession()->getPage()->findField($select)) {
      throw new ExpectationException("Field '$select' not found.", $this->getSession());
    }

    // Check that the specified field is a <select> field.
    $this->assertElementType($field, 'select');

    // Retrieve the options table from the test scenario and flatten it.
    $expected_options = $options->getColumnsHash();
    array_walk($expected_options, function (&$value) {
      $value = reset($value);
    });

    // Retrieve the actual options that are shown in the page.
    $actual_options = $field->findAll('css', 'option');

    // Convert into a flat list of option text strings.
    array_walk($actual_options, function (&$value) {
      $value = $value->getText();
    });

    // Check that none of the expected options are present.
    foreach ($expected_options as $expected_option) {
      if (in_array($expected_option, $actual_options)) {
        throw new ExpectationException("Option '$expected_option' is unexpectedly found in select list '$select'.", $this->getSession());
      }
    }
  }

  /**
   * Checks that the given element is of the given type.
   *
   * @param NodeElement $element
   *   The element to check.
   * @param string $type
   *   The expected type.
   *
   * @throws ExpectationException
   *   Thrown when the given element is not of the expected type.
   */
  public function assertElementType(NodeElement $element, $type) {
    if ($element->getTagName() !== $type) {
      throw new ExpectationException("The element is not a '$type'' field.", $this->getSession());
    }
  }

  /**
   * Prepare for PHP errors log.
   *
   * @BeforeScenario
   */
  public static function preparePhpErrors(BeforeScenarioScope $scope) {
    // Clear out the watchdog table at the beginning of each test scenario.
    db_truncate('watchdog')->execute();
  }
  /**
   * Check for PHP errors log.
   *
   * @param AfterStepScope $scope
   *    AfterStep hook scope object.
   *
   * @throws \Exception
   *    Print out descriptive error message by throwing an exception.
   *
   * @AfterStep
   */
  public static function checkPhpErrors(AfterStepScope $scope) {
    // Find any PHP errors at the end of the suite
    // and output them as an exception.
    $log = db_select('watchdog', 'w')
      ->fields('w')
      ->condition('w.type', 'php', '=')
      ->execute()
      ->fetchAll();
    if (!empty($log)) {
      $errors = count($log);
      $step_text = $scope->getStep()->getText();
      $step_line = $scope->getStep()->getLine();
      $feature_title = $scope->getFeature()->getTitle();
      $feature_file = $scope->getFeature()->getFile();
      $message = "$errors PHP errors were logged to the watchdog\n";
      $message .= "Feature: '$feature_title' on '$feature_file' line $step_line\n";
      $message .= "Step: '$step_text'\n";
      $message .= "Errors:\n";
      $message .= "----------\n";
      foreach ($log as $error) {
        $error->variables = unserialize($error->variables);
        $date = date('Y-m-d H:i:sP', $error->timestamp);
        $message .= sprintf("Message: %s: %s in %s (line %s of %s).\n", $error->variables['%type'], $error->variables['!message'], $error->variables['%function'], $error->variables['%line'], $error->variables['%file']);
        $message .= "Location: $error->location\n";
        $message .= "Referer: $error->referer\n";
        $message .= "Date/Time: $date\n\n";
      }
      $message .= "----------\n";
      throw new \Exception($message);
    }
  }

}
