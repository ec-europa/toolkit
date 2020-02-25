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
    'arguments' => [
       'project-id' => 'The project_id for which exceptions could have been made.',
     ],
     'options' => [
       'blocker' => 'Whether or not to block the project (0/1). Defaults to 1.',
     ],
  );
  $items['toolkit-check-modules-download-attribute'] = array(
    'description' => "Checks a make file for incorrect download tag, revision, branch or refspec.",
    'aliases' => array('tk-cmda'),
    'arguments' => array(
      'make' => 'The make file to validate.'
    ),
    'options' => [
      'blocker' => 'Whether or not to block the project (0/1). Defaults to 1.',
    ],
  );
  $items['toolkit-check-modules-minimum-version'] = array(
    'description' => "Gives a list of modules no complying with the minimum version allowed.",
    'aliases' => array('tk-cmmv'),
    'options' => [
      'blocker' => 'Whether or not to block the project (0/1). Defaults to 1.',
    ],
  );
  $items['toolkit-check-modules-restricted'] = array(
    'description' => "Search for modules that can't be overridden.",
    'aliases' => array('tk-cmr'),
    'options' => [
      'blocker' => 'Whether or not to block the project (0/1). Defaults to 1.',
    ],
  );
  $items['toolkit-check-modules-unused'] = [
    'description' => 'Checks for non-used modules in certain path crossreferenced with a makefile.',
    'aliases' => ['tk-cmu'],
    'arguments' => [
      'path' => 'The path in which the module lives, "sites" by default',
      'make' => 'The make file which to cross reference against the disabled modules, relative to drupal root. By default ../resources/site.make.',
    ],
    'options' => [
      'blocker' => 'Whether or not to block the project (0/1). Defaults to 1.',
    ],
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

  // Blocking options.
  $failed = false;
  $blocker = (int) drush_get_option('blocker', 1);

  if (!empty($modules) && !empty($d7ModulesList)) {
    if ($project_id !== NULL) {
      variable_set('project_id', $project_id);
    }
    // Enable 'Update Manager' module if it's disabled.
    if (!module_exists('update')) {
      module_enable(array('update'), FALSE);
      $status = FALSE;
    }
    else {
      $status = TRUE;
    }

    // If modules defined in devel.make add to authorised modules list.
    $develMakeFile = '../resources/devel.make';
    $fpfisModules = array('gelf_log', 'redis', 'skippy_cookie');
    $develModules = array();
    if (file_exists($develMakeFile)) {
      $devel = drupal_parse_info_file($develMakeFile);
      if (isset($devel['projects'])) {
        $develModules += array_keys($devel['projects']);
      }
    }
    $allowedModules = array_merge($develModules, $fpfisModules);
    $modulesName += $allowedModules;
  
    foreach ($d7ModulesList as $module) {
      // Get list of modules authorised for all projects.
      if ($module['restricted_use'] == '0') {
        $modulesName[] = $module['name'];
        $modulesArray[] = $module;
      }
      // Get list of restricted modules.
      if ($module['restricted_use'] != '0' && $module['restricted_use'] != '1') {
        $restrictedByProject = explode(',', $module['restricted_use']);
  
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
          $failed = true;
        }
  
        // Check for security updates.
        module_load_include('inc', 'update', 'update.report');
        $availableUpdates = update_get_available(TRUE);
        $moduleAvailableUpdates = update_calculate_project_data($availableUpdates);
  
        if (isset($moduleAvailableUpdates[$moduleId]['security updates']) && !in_array($moduleId, $allowedModules)) {
          $modulePath = drupal_get_path('module', $moduleAvailableUpdates[$moduleId]['name']);
          drush_log('The module ' . $moduleAvailableUpdates[$moduleId]['name'] . ' with version ' . $moduleAvailableUpdates[$moduleId]['existing_version'] . ' has a security update! Update to ' . $moduleAvailableUpdates[$moduleId]['recommended'], LogLevel::ERROR);
          $failed = true;
        }
      }
    }
    // Turn off again 'Update Manager' module, in case was initially disabled.
    if ($status != TRUE) {
      module_disable(array('update'), FALSE);
      unset($status);
    }
    // Delete variable 'project_id'.
    if ($project_id !== NULL) {
      variable_del('project_id');
    }
  }
  drushExitWithStatus($failed, $blocker);
}

/**
 * Function to validate a make files download attributes. Incorrect usage of the
 * download attribute has proven to download incorrect versions.
 */
function drush_toolkit_check_modules_download_attribute($make = '../resources/site.make') {

  // Blocking options.
  $failed = false;
  $blocker = (int) drush_get_option('blocker', 1);

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
          drush_set_error('SITE_MAKE_ERROR', dt('Attribute @attributes defined on "project" level for @name. This should be at "download" level.', array('@attributes' => implode(', ', $attributes_at_wrong_level), '@name' => $name)));
          $failed = true;
        }
      }
    }

    // Check libraries for incorrect attributes.
    if (isset($make_file['libraries'])) {
      foreach ($make_file['libraries'] as $name => $project) {
        $attributes_at_wrong_level = array_intersect(array_keys($project), $attributes);
        if (!empty($attributes_at_wrong_level)) {
          drush_set_error('SITE_MAKE_ERROR', dt('Attribute @attributes defined on "library" level for @name. This should be at "download" level.', array('@attributes' => implode(', ', $attributes_at_wrong_level), '@name' => $name)));
          $failed = true;
        }
      }
    }
  }
  drushExitWithStatus($failed, $blocker);
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

  // Blocking options.
  $failed = false;
  $blocker = (int) drush_get_option('blocker', 1);

  if (!empty($modules) && !empty($d7ModulesList)) {
    if ($project_id !== NULL) {
      variable_set('project_id', $project_id);
    }
  
    // Get list of modules authorised for all projects.
    foreach ($d7ModulesList as $module) {
      if ($module['restricted_use'] == '0') {
        $modulesName[] = $module['name'];
        $modulesArray[] = $module;
      }
      // Get list of restricted modules.
      if ($module['restricted_use'] != '0' && $module['restricted_use'] != '1') {
        $restrictedByProject = explode(',', $module['restricted_use']);
  
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
      $moduleProject = isset($moduleInfo['project']) ? $moduleInfo['project'] : '';
  
      if (strpos($modulePath, 'sites/') !== false &&
        !empty($moduleInfo['version']) && $moduleProject == $moduleId) {
  
        // Compare actual module version with the minimum version authorised.
        $getMinVersion = searchForVersion($moduleProject, $modulesArray);
        $versionCompare = version_compare($moduleInfo['version'], $getMinVersion);
  
        if ($versionCompare == -1) {
          drush_log('The module ' . $moduleId . ' needs to be updated from ' . $moduleInfo['version'] . ' to ' . $getMinVersion, LogLevel::WARNING);
          $failed = true;
        }
      }
    }
    // Delete variable 'project_id'.
    if ($project_id !== NULL) {
      variable_del('project_id');
    }
  }
  drushExitWithStatus($failed, $blocker);
}

/**
 * Callback for searching for overridden modules.
 */
function drush_toolkit_check_modules_restricted() {

  // Blocking options.
  $failed = false;
  $blocker = (int) drush_get_option('blocker', 1);

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
      $failed = true;
    }
  }
  drushExitWithStatus($failed, $blocker);
}

/**
 * Checks for non-used modules in certain path crossreferenced with a makefile.
 */
function drush_toolkit_check_modules_unused($path = 'sites', $make = '../resources/site.make') {

  // Blocking options.
  $failed = false;
  $blocker = (int) drush_get_option('blocker', 1);

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
      $failed = true;
    }
    // Otherwise show only disabled modules that are present in the make file.
    elseif ($module->status === '0' && !empty($make_file['projects'])) {
      if (in_array($module->name, array_keys($make_file['projects']))) {
        drush_log(dt('Module @name is not enabled.', array('@name' => $module->name)), 'warning');
        $failed = true;
      }
    }
  }
  drushExitWithStatus($failed, $blocker);
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
  $d7ModulesList = array();
  $url = 'https://webgate.ec.europa.eu/fpfis/qa/api/v1/package-reviews?version=7.x';
  $basicAuth = getenv('QA_API_BASIC_AUTH') !== FALSE ? getenv('QA_API_BASIC_AUTH') : '';
  $content = getQaEndpointContent($url, $basicAuth);
  if ($content !== '') {
    $d7ModulesList = json_decode($content, true);
  }
  return $d7ModulesList;
}

/**
 * Curl function to access endpoint with or without authentication.
 *
 *
 * @param string $url The QA endpoint url.
 * @param string $basicAuth The basic auth.
 *
 * @return string
 */
function getQaEndpointContent($url, $basicAuth = '') {
	$content = '';
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	if ($basicAuth !== '') {
		$header = ['Authorization: Basic ' . $basicAuth];
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	}
	$result = curl_exec($curl);

	if ($result !== false) {
		$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		switch ($statusCode) {
			// Upon success set the content to be returned.
			case 200:
				$content = $result;
				break;
			// Upon other status codes.
			default:
				if ($basicAuth === '') {
					return drush_set_error('QA_API_BASIC_AUTH', dt('Curl request to endpoint @endpoint returned a @statusCode.', array('@endpoint' => $url, '@statusCode' => $statusCode)));
				}
				// If we tried with authentication, retry without.
				$content = getQaEndpointContent($url);
		}
	}
	if ($result === false) {
		return drush_set_error('QA_API_BASIC_AUTH', dt('Curl request to endpoint @endpoint failed.', array('@endpoint' => $url)));
	}
	curl_close($curl);

	return $content;
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

// Helper function to exit Drush with status.
function drushExitWithStatus($failed, $blocker) {
  drush_set_context('DRUSH_EXECUTION_COMPLETED', TRUE);
  ($blocker == true && $failed == true) ? exit(1) : exit(0);
}
