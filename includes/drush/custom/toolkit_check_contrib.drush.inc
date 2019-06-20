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
function toolkit_check_contrib_drush_command() {

  $items = array();

  $items['toolkit-contrib-authorised-security'] = array(
    'description' => "Gives a list of non authorised modules and/or security updates.",
    'aliases' => array('tk-contrib-as'),
  );
  $items['toolkit-contrib-update'] = array(
    'description' => "Gives a list of modules no complying with the minimum version allowed.",
    'aliases' => array('tk-contrib-up'),
  );

  return $items;
}

/**
 * Function to return the list of modules not approved, or
 * modules with an existing security update.
 */
function drush_toolkit_check_contrib_toolkit_contrib_authorised_security($project_id = NULL) {
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
 * Function to return the list of modules with a version lower then
 * the minimum version authorised.
 */
function drush_toolkit_check_contrib_toolkit_contrib_update($project_id = NULL) {
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
