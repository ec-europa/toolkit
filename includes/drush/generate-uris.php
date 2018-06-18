<?php

/**
 * @file
 * Script used by drush to create list of uris for backtrac.
 */

$file = drush_shift();
$paths = [
  url(variable_get('site_frontpage', 'node')),
];
$paths = generateUrlsByContentTypes($paths);
$paths = generateUrlsByTaxonomies($paths);
$paths = generateUrlsByViews($paths);
$paths = generateUrlsByPageManager($paths);
$count = count($paths);

if (($count > 1) && !empty($file) && file_exists(dirname($file))) {
  if (file_put_contents($file, implode(PHP_EOL, $paths))) {
    drush_log('Wrote list of ' . $count . ' uri\'s to : ' . $file, 'success');
  }
}


/**
 * Generate the list of URL's based in the Content-types configuration.
 *
 * @return array
 *   List of URL's to test.
 */
function generateUrlsByContentTypes($paths)
{
  $node_types = db_select('node_type', 'nt')
    ->fields('nt', ['type', 'name'])
    ->condition('nt.disabled', '0', '=')
    ->execute()
    ->fetchAll();
  if (!empty($node_types)) {
    foreach ($node_types as $node_type) {
      $types[] = $node_type->type;
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
      $path = 'node/' . $node->nid;
      if (drupal_valid_path($path)) {
        $paths[] = url($path);
      }
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
function generateUrlsByTaxonomies($paths)
{
  if (module_exists('taxonomy')) {
    $taxonomies = db_select('taxonomy_term_data', 'ttd')
      ->fields('ttd', array('tid'))
      ->groupBy('ttd.vid')
      ->execute()
      ->fetchAll();
    if (!empty($taxonomies)) {
      foreach ($taxonomies as $taxonomy) {
        $path = 'taxonomy/term/' . $taxonomy->tid;
        if (drupal_valid_path($path)) {
          $paths[] = url($path);
        }
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
function generateUrlsBySearch($paths)
{
  if (module_exists('search') && drupal_valid_path('search')) {
    $paths[] = url('search');
  }
  return $paths;
}

/**
 * Generate the list of URL's based in the Views configuration.
 *
 * @return array
 *   List of URL's to test.
 */
function generateUrlsByViews($paths)
{
  if (module_exists('views')) {
    $all_views = views_get_all_views();
    foreach ($all_views as $view) {
      if (empty($view->disabled)) {
        foreach ($view->display as $display) {
          if ($display->display_plugin == 'page') {
            if (drupal_valid_path($display->display_options['path'])) {
              $paths[] = url($display->display_options['path']);
            }
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
function generateUrlsByPageManager($paths)
{
  if (module_exists('page_manager')) {
    $pages = db_select('page_manager_pages', 'pmp')
      ->fields('pmp', array('path'))
      ->execute()
      ->fetchAll();
    if (!empty($pages)) {
      foreach ($pages as $page) {
        $path = $page->path;
        if (drupal_valid_path($path)) {
          $paths[] = url($path);
        }
      }
    }
  }
  return $paths;
}

