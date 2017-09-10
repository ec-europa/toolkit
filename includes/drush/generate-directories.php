<?php

/**
 * @file
 * Script used by drush to create files directories.
 */

// Include the install.inc to use the function drupal_rewrite_settings().
if (!function_exists('drupal_mkdir')) {
  include 'includes/file.inc';
}

// Directories to create.
$directories = array(
  variable_get('file_temporary_path', conf_path() . '/tmp'),
  variable_get('file_private_path', conf_path() . '/files/private_files'),
  variable_get('file_public_path', conf_path() . '/files') . '/css_injector',
  variable_get('file_public_path', conf_path() . '/files') . '/js_injector',
  variable_get('file_public_path', conf_path() . '/files'),
);

foreach ($directories as $directory) {
  if (!is_dir($directory) && !drupal_mkdir($directory, NULL, TRUE)) {
    watchdog('file system', 'The directory %directory does not exist and could not be created.', array('%directory' => $directory), WATCHDOG_ERROR);
  }
  if (is_dir($directory) && !is_writable($directory) && !drupal_chmod($directory)) {
    watchdog('file system', 'The directory %directory exists but is not writable and could not be made writable.', array('%directory' => $directory), WATCHDOG_ERROR);
  }
  elseif (is_dir($directory)) {
    $public_dir = variable_get('file_public_path', conf_path() . '/files');
    if ($directory == $public_dir) {
      // Create public .htaccess file.
      file_create_htaccess($directory, FALSE);
    }
    else {
      // Create private .htaccess file.
      file_create_htaccess($directory);
    }
  }
}
