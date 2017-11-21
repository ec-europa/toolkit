<?php

/**
 * @file
 * Contains \GenericContext.
 */

namespace Drupal\nexteuropa\Context\Generic;

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
   * Build a list of dynamic URLS based in the database and test the HTTP code.
   *
   * @Given the page contents have the correct code
   */
  public function thePageContentsHaveTheCorrectCode() {
    $pages = $this->generateUrls();
    foreach ($pages as $page) {
      try {
        if (strpos($page, '%') !== false) {
          // Skip all path that contains arguments.
        }
        else {
          $this->visitPath($page);
          echo "\033[0;32m(200)\t" . $page . "\033[0m\n";
        }
      }
      catch (Exception $e) {
        echo "\033[0;33m(404)\t" . $page . "\033[0m\n";
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
        $paths[] = 'node/add/' . str_ireplace('_', '-', $node_type->type);
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
