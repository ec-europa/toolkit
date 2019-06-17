<?php

/**
 * @file
 * Drush commands for the module.
 */

/**
 * Implements hook_drush_command().
 */
function custom_drush_command() {
  $items = [];

  $items['features-revert-site'] = [
    'description' => 'Revert all enabled feature modules in your site folder.',
    'options' => [
      'force' => "Force revert even if Features assumes components' state are default.",
    ],
    'drupal dependencies' => ['features'],
    'aliases' => ['fr-site', 'frsi'],
  ];

  $items['check-unused-modules'] = [
    'description' => 'Checks for non-used modules in certain path crossreferenced with a makefile.',
    'args' => [
      'path' => 'The path in which the module lives, "sites" by default',
      'make' => 'The make file which to cross reference against the disabled modules, relative to drupal root. By default ../resources/site.make.',
    ],
    'aliases' => ['ch-unmo', 'chun'],
  ];

  $items['watchdog-smoketest'] = [
    'description' => 'Crawl site and report watchdog errors and notifications back to user.',
    //'drupal dependencies' => ['dblog'],
    'aliases' => ['wd-smoke', 'wdsm'],
    'options' => ['type' => "'Either 'drush', 'backstop' or 'report'. Default is 'drush'."],
    'outputformat' => [
      'default' => 'json',
      'pipe-format' => 'var_export',
      'field-labels' => array('wid' => 'ID', 'type' => 'Type', 'message' => 'Message', 'severity' => 'Severity', 'location' => 'Location', 'hostname' => 'Hostname', 'date' => 'Date', 'username' => 'Username'),
      'fields-default' => array('type', 'severity', 'location', 'message'),
      'column-widths' => array('type' => 8, 'severity' => 8),
      'output-data-type' => 'format-json',
    ],
  ];

  return $items;
}

/**
 * Implements hook_drush_help().
 */
function custom_drush_help($section) {
  switch ($section) {
    case 'drush:features-revert-site':
      return dt("Revert all enabled feature modules on your site that are located in the active site folder.");
      break;
    case 'drush:watchdog-smoketest':
      return dt("Crawl site and report watchdog errors and notifications back to user.");
      break;
  }
  return '';
}

/**
 * Revert all enabled features to their definitions in code.
 */
function drush_custom_features_revert_site() {
  // Before we can trigger other commands, we need to set our strict value to 0.
  drush_set_option('strict', FALSE);

  // Load required files.
  module_load_include('inc', 'features', 'features.export');
  
  $features = features_get_info('feature');
  $features_to_revert = array();
  
  foreach ($features as $feature) {
    if ($feature->status == 1 && (substr($feature->filename, 0, 6) === "sites/")) {
      $features_to_revert[] = $feature->name;
    }
  }

  if (!empty($features_to_revert)) {
    $dt_args = ['!modules' => implode(', ', $features_to_revert)];
    drush_print(dt('The following modules will be reverted: !modules', $dt_args));
    // Confirm that the user would like to continue. If not, simply abort.
    if (!drush_confirm(dt('Do you really want to continue?'))) {
      drush_user_abort('Aborting.');
    }
    drush_invoke('features-revert', $features_to_revert);
  }
  else {
    drush_log(dt('Current state already matches defaults, aborting.'), 'ok');
  }
}

/**
 * Checks for non-used modules in certain path crossreferenced with a makefile.
 */
function drush_custom_check_unused_modules($path = 'sites', $make = '../resources/site.make') {
  // If referenced make file does not exist, trow a warning.
  if (!file_exists($make)) {
    drush_log(dt('Make file @make does not exist. Showing all disabled modules in @path.', array('@make' => $make, '@path' => $path)), 'warning');
  }
  else {
    $make_file = drupal_parse_info_file($make);
  }

  // Get all modules.
  $modules = drupal_system_listing("/\\.module\$/", $path, 'name', 0);
  system_get_files_database($modules, 'module');

  foreach ($modules as $module) {
    // If no make file was provided or empty, show all disabled modules in path.
    if ($module->status === '0' && empty($make_file)) {
      drush_log(dt('Module @name is not enabled.', array('@name' => $module->name)), 'warning');
    }
    // Otherwise show only disabled modules that are present in the make file.
    elseif ($module->status === '0' && !empty($make_file['projects'])) {
      if (in_array($module->name, array_keys($make_file['projects']))) {
        drush_log(dt('Module @name is not enabled.', array('@name' => $module->name)), 'warning');
      }
    }
  }
}


/**
 * Revert all enabled features to their definitions in code.
 */
function drush_custom_watchdog_smoketest() {
  $type = drush_get_option('type', 'drush');

  // Get cookie for root user.
  global $user;
  global $base_url;
  $user = user_load(1);
  $session_id = session_id();
  $session_name = session_name();
  // Did not find other way to login through cli
  // $uli = user_pass_reset_url($user);
  // $request = drupal_http_request($uli);
  _drupal_session_write($session_id, '');
  $options = [
    'headers' => [
      'Cookie' => $session_name . '=' . $session_id,
    ],
  ];

  // Always add frontpage.
  $paths = [
    '/admin/config',
  ];

  // Gather secondary urls.
  $paths = generateUrlsByContentTypes($paths);
  $paths = generateUrlsByTaxonomies($paths);
  $paths = generateUrlsByViews($paths);
  $paths = generateUrlsByPageManager($paths);

  // Generate cookie for backstop usage.
  if ($type == 'backstop') {
    $backstop_json = file_get_contents('../backstop.json');
    $backstop_settings = json_decode($backstop_json, true);
    $backstop_cookies = array(
      array(
        'domain' => str_replace(array('http://', 'https://', ':8080'), '', $base_url),
        'path' => '/',
        'name' => $session_name,
        'value' => $session_id,
        'expirationDate' => 1798790400,
        'hostOnly' => false,
        'httpOnly' => false,
        'secure' => false,
        'session' => false,
        'sameSite' => 'no_restriction'
      ),
    );
    file_put_contents('../backstop_data/engine_scripts/cookies.json', json_encode($backstop_cookies, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
  }

  $count_paths = count($paths);
  if ($count_paths >= 1) {
    if ($type == 'backstop') {
        $backstop_scenarios = array();
        foreach($paths as $path) {
            $normal_path = ltrim(drupal_get_normal_path($path), '/');
            $item = menu_get_item($normal_path);
            $url = url($path,  array('absolute' => TRUE));
            if(isset($item['title']) && !empty($item['title'])){
                $title = $item['title'];
            } else {
                $path_title = ltrim(strstr(str_replace(array('-', '/'), ' ', $path), '_', true));
                $title = ucwords($path_title);
            }
            $backstop_scenarios[] = array(
              'url' => $url,
              'label' => $title,
              'cookiePath' => 'backstop_data/engine_scripts/cookies.json',
              'readyEvent' => '',
              'readySelector' => '',
              'delay' => 0,
              'hideSelectors' => array (),
              'removeSelectors' => array (),
              'hoverSelector' => '',
              'clickSelector' => '',
              'postInteractionWait' => 0,
              'selectors' => array (),
              'selectorExpansion' => true,
              'expect' => 0,
              'misMatchThreshold' => 1,
              'requireSameDimensions' => true,
            );
        }
        $backstop_settings['scenarios'] = $backstop_scenarios;
        file_put_contents('../backstop.json', json_encode($backstop_settings, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
    }

    if ($type == 'drush') {
        $last_wid = db_query('SELECT MAX(wid) FROM {watchdog}')->fetchField();
        drush_log(dt('Visiting !count_paths paths as admin to check for watchdog messages.', array('!count_paths' => $count_paths)), 'ok');
        foreach($paths as $path) {
            $url = url($path,  array('absolute' => TRUE));
            $visit = drupal_http_request($url, $options);
        }
    }
  }

  if (in_array($type, array('drush', 'report'))) {
      _drupal_session_destroy($session_id);
      $watchdog_messages = db_select('watchdog', 'wd')
        ->fields('wd')
        ->condition('wid', $last_wid, '>')
        ->condition('severity', 5, '<')
        ->execute()
        ->fetchAllAssoc('wid');

      $table = array();
      foreach ($watchdog_messages as $result) {
        $row = core_watchdog_format_result($result, TRUE);
        $table[$row->wid] = $row;
      }

      $count_watchdog = count($watchdog_messages);
      if ($count_watchdog >= 1) {
        drush_log(dt('Generated !count_watchdog watchdog entries of severity less than 5.', array('!count_watchdog' => $count_watchdog)), 'warning');
        return $table;
      }
      else {
        drush_log(dt('Generated !count_watchdog watchdog entries.', array('!count_watchdog' => $count_watchdog)), 'ok');
      }
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
  if (is_array($types) && count($types) > 0) {
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

      // Load all views displays to check for missing handlers.
      if (module_exists('devel')) {

        $views_debug = variable_get('views_devel_output', FALSE);
        $views_region = variable_get('views_devel_region', 'watchdog');
        variable_set('views_devel_output', TRUE);
        variable_set('views_devel_region', 'message');

        $displays = array_keys($view->display);
        foreach($displays as $display) {
           $view->execute($display);
           $view = $view->clone_view();
        }

        variable_set('views_devel_output', $views_debug);
        variable_set('views_devel_region', $views_region);
      }

      // Get the page views to visit later for detecting other watchdog entries.
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