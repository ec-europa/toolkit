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
  if (!is_dir($directory) && !@drupal_mkdir($directory, NULL, TRUE)) {
    drush_log("The directory " . $directory . " does not exist and could not be created.");
    watchdog('file system', 'The directory %directory does not exist and could not be created.', array('%directory' => $directory), WATCHDOG_ERROR);
  }
  if (is_dir($directory) && !is_writable($directory) && !@drupal_chmod($directory)) {
    drush_log("The directory " . $directory . " exists but is not writable and could not be made writable.");
    watchdog('file system', 'The directory %directory exists but is not writable and could not be made writable.', array('%directory' => $directory), WATCHDOG_ERROR);
  }
  elseif (is_dir($directory)) {
    $public_dir = variable_get('file_public_path', conf_path() . '/files');
    $private = $directory == $public_dir ? FALSE : TRUE;
    $htaccess_path =  $directory . '/.htaccess';
    $htaccess_lines = file_htaccess_lines($private);
    if (file_put_contents($htaccess_path, $htaccess_lines)) {
      @drupal_chmod($htaccess_path, 0444);
    }
    else {
      drush_log("Security warning: Couldn't write .htaccess file. Please create a .htaccess file in your " . $directory . " directory.");
      $variables = array('%directory' => $directory, '!htaccess' => '<br />' . nl2br(check_plain($htaccess_lines)));
      watchdog('security', "Security warning: Couldn't write .htaccess file. Please create a .htaccess file in your %directory directory which contains the following lines: <code>!htaccess</code>", $variables, WATCHDOG_ERROR);
    }
  }
}
