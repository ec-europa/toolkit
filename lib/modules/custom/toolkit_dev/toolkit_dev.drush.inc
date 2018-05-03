<?php

/**
 * @file
 * Drush commands for the module.
 */

/**
 * Implements hook_drush_command().
 */
function toolkit_dev_drush_command() {
  $items = [];

  $items['features-revert-site'] = [
    'description' => 'Revert all enabled feature modules in your site folder.',
    'options' => [
      'force' => "Force revert even if Features assumes components' state are default.",
    ],
    'drupal dependencies' => ['features'],
    'aliases' => ['fr-site', 'frsi'],
  ];

  return $items;
}

/**
 * Implements hook_drush_help().
 */
function toolkit_dev_drush_help($section) {
  switch ($section) {
    case 'drush:features-revert-site':
      return dt("Revert all enabled feature modules on your site that are located in the active site folder.");
  }
  return '';
}

/**
 * Revert all enabled features to their definitions in code.
 */
function drush_toolkit_dev_features_revert_site() {
  // Before we can trigger other commands, we need to set our strict value to 0.
  drush_set_option('strict', FALSE);

  // Load required files.
  module_load_include('inc', 'features', 'features.export');
  
  $features_to_revert = array();
  
  $result = db_query('SELECT s.name FROM {system} s WHERE s.status = 1 AND s.filename LIKE :sitepath AND CAST( s.info AS CHAR(10000) CHARACTER SET utf8) LIKE :featurepath', array(':sitepath' => db_like('sites/') . '%', ':featurepath' => '%' . db_like('\"features\"') . '%') );
  foreach ($result as $feature) {
    $features_to_revert[] = $feature->name;
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