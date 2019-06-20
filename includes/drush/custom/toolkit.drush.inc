<?php

/**
 * @file
 * Check contributed modules information.
 */

use Drush\Log\LogLevel;

/**
 * Implements hook_drush_command().
 *
 * Returns an associative array describing drush command(s).
 *
 * @see drush_parse_command()
 */
function toolkit_drush_command() {

  $items = array();

  $items['toolkit-check-modules-authorized-security'] = array(
    'description' => "Gives a list of non authorised modules and/or security updates.",
    'aliases' => array('tk-cmas'),
  );
  $items['toolkit-check-modules-download-attribute'] = array(
    'description' => "Checks a make file for incorrect download tag, revision, branch or refspec.",
    'aliases' => array('tk-cmda'),
    'args' => array(
      'make' => 'The make file to validate.'
    ),
  );
  $items['toolkit-check-modules-minimum-version'] = array(
    'description' => "Gives a list of modules no complying with the minimum version allowed.",
    'aliases' => array('tk-cmmv'),
  );
  $items['toolkit-check-modules-restricted'] = array(
    'description' => "Search for modules that can't be overridden.",
    'aliases' => array('tk-cmr'),
  );
  $items['toolkit-check-modules-unused'] = [
    'description' => 'Checks for non-used modules in certain path crossreferenced with a makefile.',
    'args' => [
      'path' => 'The path in which the module lives, "sites" by default',
      'make' => 'The make file which to cross reference against the disabled modules, relative to drupal root. By default ../resources/site.make.',
    ],
    'aliases' => ['tk-cmu'],
  ];

  return $items;
}

/**
 * Function to return the list of modules not approved, or
 * modules with an existing security update.
 */
function drush_toolkit_check_modules_authorized_security($project_id = NULL) {
  // Get list of all modules in the project.
  $modules = checkProjectModules();
  // Get the module reviews list.
  $d7ModulesList = getModulesList();
  // Instantiate arrays.
  $modulesName = [];
  $modulesArray = [];

  if (!empty($modules) && !empty($d7ModulesList)) {
    if ($project_id !== NULL) {
      variable_set('project_id', $project_id);
    }
    // Check update module status.
    $updateStatus = system_get_info('module', 'update');
    // Enable 'Update Manager' module if it's disabled.
    if (empty($updateStatus)) {
      $updateModule = array('update');
      module_enable($updateModule, FALSE);
      $status = FALSE;
    }
    else {
      $status = TRUE;
    }
  
    foreach ($d7ModulesList as $module) {
      // Get list of modules authorised for all projects.
      if ($module['restricted_us'] == '0') {
        $modulesName[] = $module['name'];
        $modulesArray[] = $module;
      }
      // Get list of restricted modules.
      if ($module['restricted_us'] != '0' && $module['restricted_us'] != '1') {
        $restrictedByProject = explode(',', $module['restricted_us']);
  
        foreach ($restrictedByProject as $project) {
  
          // Check the project Id and add the restricted modules by project (if is
          // the case) to the list of authorised modules.
          if ($project_id !== NULL && $project_id == $project) {
            array_push($modulesName, $module['name']);
            array_push($modulesArray, $module);
          }
        }
      }
    }
  
    foreach ($modules as $module => $moduleId) {
  
      $modulePath = drupal_get_path('module', $moduleId);
      $moduleInfo = drupal_parse_info_file($modulePath . '/' . $moduleId . '.info');
  
      if (strpos($modulePath, 'sites/') !== false &&
        !empty($moduleInfo['version']) && $moduleInfo['project'] == $moduleId) {
  
        if (!in_array($moduleId, $modulesName)) {
          drush_log('The use of the module ' . $moduleId . ' is not authorised by the QA team.', LogLevel::ERROR);
        }
  
        // Check for security updates.
        module_load_include('inc', 'update', 'update.report');
        $availableUpdates = update_get_available(TRUE);
        $moduleAvailableUpdates = update_calculate_project_data($availableUpdates);
  
        if (isset($moduleAvailableUpdates[$moduleId]['security updates'])) {
          $modulePath = drupal_get_path('module', $moduleAvailableUpdates[$moduleId]['name']);
          drush_log('The module ' . $moduleAvailableUpdates[$moduleId]['name'] . ' with version ' . $moduleAvailableUpdates[$moduleId]['existing_version'] . ' has a security update! Update to ' . $moduleAvailableUpdates[$moduleId]['recommended'], LogLevel::ERROR);
        }
      }
    }
    // Turn off again 'Update Manager' module, in case was initially disabled.
    if ($status != TRUE) {
      $updateModule = array('update');
      module_disable($updateModule, FALSE);
      unset($status);
    }
    // Delete variable 'project_id'.
    if ($project_id !== NULL) {
      variable_del('project_id');
    }
  }
}

/**
 * Function to validate a make files download attributes. Incorrect usage of the
 * download attribute has proven to download incorrect versions.
 */
function drush_toolkit_check_modules_download_attribute($make = '../resources/site.make') {
  if (!file_exists($make)) {
    drush_log(dt('Make file @make does not exist. Skipping download attributes check.', array('@make' => $make)), 'warning');
  }
  else {
    $make_file = drupal_parse_info_file($make);
    $attributes = array(
      'tag',
      'revision',
      'branch',
      'refspec'
    );
  
    // Check projects for incorrect attributes.
    if (isset($make_file['projects'])) {
      foreach ($make_file['projects'] as $name => $project) {
        $attributes_at_wrong_level = array_intersect(array_keys($project), $attributes);
        if (!empty($attributes_at_wrong_level)) {
          drush_set_error(dt('Attribute @attributes defined on "project" level for @name. This should be at "download" level.', array('@attributes' => implode(', ', $attributes_at_wrong_level), '@name' => $name)));
        }
      }
    }

    // Check libraries for incorrect attributes.
    if (isset($make_file['libraries'])) {
      foreach ($make_file['libraries'] as $name => $project) {
        $attributes_at_wrong_level = array_intersect(array_keys($project), $attributes);
        if (!empty($attributes_at_wrong_level)) {
          drush_set_error(dt('Attribute @attributes defined on "library" level for @name. This should be at "download" level.', array('@attributes' => implode(', ', $attributes_at_wrong_level), '@name' => $name)));
        }
      }
    }
  }
}

/**
 * Function to return the list of modules with a version lower then
 * the minimum version authorised.
 */
function drush_toolkit_check_modules_minimum_version($project_id = NULL) {
  // Get list of all modules in the project.
  $modules = checkProjectModules();
  // Get the module reviews list.
  $d7ModulesList = getModulesList();
  // Instantiate arrays.
  $modulesName = [];
  $modulesArray = [];

  if (!empty($modules) && !empty($d7ModulesList)) {
    if ($project_id !== NULL) {
      variable_set('project_id', $project_id);
    }
  
    // Get list of modules authorised for all projects.
    foreach ($d7ModulesList as $module) {
      if ($module['restricted_us'] == '0') {
        $modulesName[] = $module['name'];
        $modulesArray[] = $module;
      }
      // Get list of restricted modules.
      if ($module['restricted_us'] != '0' && $module['restricted_us'] != '1') {
        $restrictedByProject = explode(',', $module['restricted_us']);
  
        foreach ($restrictedByProject as $project) {
  
          // Check the project Id and add the restricted modules by project (if is
          // the case) to the list of authorised modules.
          if ($project_id !== NULL && $project_id == $project) {
            array_push($modulesName, $module['name']);
            array_push($modulesArray, $module);
          }
        }
      }
    }
  
    foreach ($modules as $module => $moduleId) {
      $modulePath = drupal_get_path('module', $moduleId);
      $moduleInfo = drupal_parse_info_file($modulePath . '/' . $moduleId . '.info');
  
      if (strpos($modulePath, 'sites/') !== false &&
        !empty($moduleInfo['version']) && $moduleInfo['project'] == $moduleId) {
  
        // Compare actual module version with the minimum version authorised.
        $moduleName = $moduleInfo['project'];
        $getMinVersion = searchForVersion($moduleName, $modulesArray);
        $versionCompare = version_compare($moduleInfo['version'], $getMinVersion);
  
        if ($versionCompare == -1) {
          drush_log('The module ' . $moduleId . ' needs to be updated from ' . $moduleInfo['version'] . ' to ' . $getMinVersion, LogLevel::WARNING);
        }
      }
    }
    // Delete variable 'project_id'.
    if ($project_id !== NULL) {
      variable_del('project_id');
    }
  }
}

/**
 * Callback for searching for overridden modules.
 */
function drush_toolkit_check_modules_restricted() {

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

/**
 * Checks for non-used modules in certain path crossreferenced with a makefile.
 */
function drush_toolkit_check_modules_unused($path = 'sites', $make = '../resources/site.make') {
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


// Helper function to get the minimum accepted module version.
function searchForVersion($moduleName, $modulesArray) {
  foreach ($modulesArray as $module) {
    if ($module['name'] === $moduleName) {
      return $module['version'];
    }
  }
}

// Helper function to get the list of authorised modules.
function getModulesList() {
  // Get list of authorised modules.
  $url = 'https://raw.githubusercontent.com/ec-europa/qa-tests/components/module_list.json';
  $github_api_token = getenv('GITHUB_API_TOKEN');

  if (!empty($github_api_token)) {
    $request_headers = array(
      'Authorization: token ' . $github_api_token
    );
  
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);
  
    // If request did not fail.
    if ($result !== false) {
      // Request was ok? check response code.
      $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if ($statusCode == 200) {
        $d7ModulesList = json_decode($result, true);
      }
      else {
        drush_set_error(dt('Curl request failed with error code @statusCode.', array('@statusCode' => $statusCode)));
      }
    }
    return $d7ModulesList;
  }
  else {
    drush_log(dt('GITHUB_API_TOKEN not set. Not executing command.'), 'warning');
  }
}

// Helper function to discover all existing modules in project,
// that are enabled or disabled.
function checkProjectModules() {
  $moduleList = drupal_system_listing("/\\.module\$/", "modules", 'name', 0);
  $modules = [];

  foreach ($moduleList as $module) {
    $modules[] = $module->name;
  }
  // Exclude obsolete module file 'views_export.module' from the list.
  $modules = array_diff($modules, array('views_export'));
  
  return $modules;
}
