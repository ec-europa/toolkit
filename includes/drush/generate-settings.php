<?php

/**
 * @file
 * Script used by drush to re-generate settings.php.
 */

// Include the install.inc to use the function drupal_rewrite_settings().
if (!function_exists('drupal_rewrite_settings')) {
  include 'includes/install.inc';
}

// Setup the database settings array.
$settings['databases'] = array(
  'value' => array(
    'default' => array(
      'default' => array(
        'driver' => '%%db.type%%',
        'database' => '%%db.name%%',
        'username' => '%%db.user%%',
        'password' => '%%db.password%%',
        'host' => '%%db.host%%',
        'port' => '%%db.port%%',
        'prefix' => '',
      ),
    ),
  ),
);

// Build variables array.
$variables = array(
  'error_level' => '%%error_level%%',
  'views_ui_show_sql_query' => '%%views_ui_show_sql_query%%',
  'views_ui_show_performance_statistics' => '%%views_ui_show_performance_statistics%%',
  'views_show_additional_queries' => '%%views_show_additional_queries%%',
  'stage_file_proxy_origin' => '%%stage_file_proxy_origin%%',
  'stage_file_proxy_origin_dir' => '%%stage_file_proxy_origin_dir%%',
  'stage_file_proxy_hotlink' => '%%stage_file_proxy_hotlink%%',
  'file_public_path' => '%%file_public_path%%',
  'file_private_path' => '%%file_private_path%%',
  'file_temporary_path' => '%%file_temporary_path%%',
  'https' => 1,
);

// Setup individual development variables.
foreach ($variables as $key => $value) {
  $settings['conf[\'' . $key . '\']'] = array(
    'required' => TRUE,
    'value' => is_numeric($value) ? (int) $value : $value,
  );
}

// Rewrite the settings.php file with our array.
drupal_rewrite_settings($settings);
