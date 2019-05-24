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
  variable_get('file_temporary_path', conf_path() . '/files/tmp'),
  variable_get('file_private_path', conf_path() . '/files/private_files'),
  variable_get('file_public_path', conf_path() . '/files'),
);

foreach ($directories as $directory) {
  // Check if directory exists.
  if ($directory && !is_dir($directory)) {
    // Let mkdir() recursively create directories and use the default directory
    // permissions.
    if (@drupal_mkdir($directory, NULL, TRUE)) {
      // @codingStandardsIgnoreLine
      @chmod($directory, 0775);
    }
  }
}
