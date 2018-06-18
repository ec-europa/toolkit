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
        'prefix' => '%%db.prefix%%',
      ),
    ),
  ),
);

// Set update free access.
$settings['update_free_access'] = array(
    'required' => TRUE,
    'value' => %%update_free_access%%,
);

// Set drupal hash salt.
$settings['drupal_hash_salt'] = array(
    'required' => TRUE,
    'value' => '%%drupal_hash_salt%%',
);

// Set base url.
$settings['base_url'] = array(
  'required' => TRUE,
  'value' => '%%base_url%%',
);

// Rewrite the settings.php file with our array.
drupal_rewrite_settings($settings);
