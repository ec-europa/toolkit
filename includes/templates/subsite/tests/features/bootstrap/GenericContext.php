<?php

/**
 * @file
 * Contains \GenericContext.
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
class GenericContext extends RawDrupalContext implements SnippetAcceptingContext {

  const LOG_MODULE = 'dblog';
  static private $handleLogModule = FALSE;

  /**
   * Enable database logging before any testing.
   *
   * @BeforeSuite
   */
  public static function prepare() {
    if (!module_exists(self::LOG_MODULE)) {
      module_enable([self::LOG_MODULE], FALSE);
      self::$handleLogModule = TRUE;
    }
  }

  /**
   * Proceed with cleanup after testing.
   *
   * @AfterSuite
   */
  public static function cleanup() {
    if (module_exists(self::LOG_MODULE) && self::$handleLogModule) {
      module_disable([self::LOG_MODULE], FALSE);
    }
  }

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

  /**
   * Build a list of dynamic URLS based in the database and test the HTTP code.
   *
   * @Given the page contents have the correct code
   */
  public function thePageContentsHaveTheCorrectCode() {
    $pages = $this->generateUrls();
    $message = '';
    foreach ($pages as $page) {
      try {
        $this->visitPath($page);
        $statusCode = 200;
        $this->assertSession()->statusCodeEquals($statusCode);
        echo "(" . $statusCode . ")\t" . $page . " \n";
      }
      catch (Exception $e) {
        throw new LogicException(sprintf('The page "%s" does not exist.', $page));
      }
    }
  }

  /**
   * Generate the list of URL's to be used.
   *
   * @return array
   *   List of URL's to test.
   */
  private function generateUrls() {
    $paths = [
      '/',
    ];

    $paths = $this->generateUrlsByContentTypes($paths);
    $paths = $this->generateUrlsByTaxonomies($paths);
    $paths = $this->generateUrlsByViews($paths);
    $paths = $this->generateUrlsByPageManager($paths);

    return $paths;
  }

  /**
   * Generate the list of URL's based in the Content-types configuration.
   *
   * @return array
   *   List of URL's to test.
   */
  private function generateUrlsByContentTypes($paths) {
    $node_types = db_select('node_type', 'nt')
      ->fields('nt', ['type', 'name'])
      ->condition('nt.disabled', '0', '=')
      ->execute()
      ->fetchAll();

    if (!empty($node_types)) {
      foreach ($node_types as $node_type) {
        $types[] = $node_type->type;
        $paths[] = 'node/add/' . $node_type->type;
      }
    }

    // Look for content in database.
    $nodes = db_select('node', 'n')
      ->fields('n', array('nid', 'type'))
      ->condition('n.type', $types, 'IN')
      ->groupBy('n.type')
      ->condition('status', 0, '>')
      ->execute()
      ->fetchAll();

    if (!empty($nodes)) {
      foreach ($nodes as $node) {
        $paths[] = drupal_get_path_alias('node/' . $node->nid);
        $paths[] = drupal_get_path_alias('node/' . $node->nid . '/edit');
      }
    }

    return $paths;
  }

  /**
   * Generate the list of URL's based in the Taxonomy configuration.
   *
   * @return array
   *   List of URL's to test.
   */
  private function generateUrlsByTaxonomies($paths) {
    if (module_exists('taxonomy')) {
      $taxonomies = db_select('taxonomy_term_data', 'ttd')
        ->fields('ttd', array('tid'))
        ->groupBy('ttd.vid')
        ->execute()
        ->fetchAll();

      if (!empty($taxonomies)) {
        foreach ($taxonomies as $taxonomy) {
          $paths[] = drupal_get_path_alias('taxonomy/term/' . $taxonomy->tid);
        }
      }
    }

    return $paths;
  }

  /**
   * Generate the list of URL's based in the Search module.
   *
   * @return array
   *   List of URL's to test.
   */
  private function generateUrlsBySearch($paths) {
    if (module_exists('search')) {
      $paths[] = 'search';
    }
    return $paths;
  }

  /**
   * Generate the list of URL's based in the Views configuration.
   *
   * @return array
   *   List of URL's to test.
   */
  private function generateUrlsByViews($paths) {
    if (module_exists('views')) {
      $all_views = views_get_all_views();
      foreach ($all_views as $view) {
        foreach ($view->display as $display) {
          if ($display->display_plugin == 'page') {
            $isDisabled = $view->disabled;
            if (!$isDisabled) {
              $paths[] = $display->display_options['path'];
            }
          }
        }
      }
    }
    return $paths;
  }

  /**
   * Generate the list of URL's based in the Page Manager configuration.
   *
   * @return array
   *   List of URL's to test.
   */
  private function generateUrlsByPageManager($paths) {
    if (module_exists('page_manager')) {
      $pages = db_select('page_manager_pages', 'pmp')
        ->fields('pmp', array('path'))
        ->execute()
        ->fetchAll();

      if (!empty($pages)) {
        foreach ($pages as $page) {
          $paths[] = $page->path;
        }
      }
    }
    return $paths;
  }

}
