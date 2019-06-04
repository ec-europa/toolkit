<?php

/**
 * @file
 * Drush integration to check modules can't be overridden.
 */

 use Drush\Log\LogLevel;

/**
 * Implements hook_drush_command().
 *
 * Returns an associative array describing drush command(s).
 *
 * @see drush_parse_command()
 */
function platform_overridden_drush_command() {
  $items = array();

  $items['platform-overridden-modules'] = array(
    'description' => "Search for modules that can't be overridden.",
    'aliases' => array('overridden-modules'),
  );

  return $items;
}

/**
 * Callback for searching for overridden modules.
 */
function drush_platform_overridden_modules() {

  // Array with list of the modules forbiden to be overridden.
  $forbiddenToOverride = array('chr');
  // Get list of all modules on the project.
  $moduleList = drupal_system_listing("/\\.module\$/", "modules", 'name', 0);
  foreach ($moduleList as $module) {
    // Check if a module is in the list and not using the default version
    // provided.
    if (in_array($module->name, $forbiddenToOverride) &&
      strpos($module->uri, 'profiles/') === FALSE) {
      drush_log("The module " . $module->name . " is not allowed to be overridden.", LogLevel::ERROR);
    }
  }
}
