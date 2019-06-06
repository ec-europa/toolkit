<?php

/**
 * @file
 * Drush commands for the module.
 */

/**
 * Implements hook_drush_command().
 */
function diffy_drush_command() {
  $items = [];

  // Command to refresh Diffy token based on the env DIFFY_API_KEY variable.
  $items['diffy-refresh-token'] = [
    'description' => 'Refresh token for diffy key.',
    'aliases' => ['diffy-rt'],
  ];

  // Command to take project snapshot for a certain environment and base url.
  $items['diffy-project-snapshot'] = [
    'description' => 'Request screenshots for a project.',
    'arguments' => [
      'projectId' => 'The project ID for which to take screenshots.'
    ],
    'options' => [
      'environment' => "The environment for which to take screenshots.",
      'baseUrl' => "The base url of the website for which to take screenshots.",
      'wait' => "The time to wait between ping for snapshot results. Defaults to 30. Set to 0 to skip wait."
    ],
    'aliases' => ['diffy-ps'],
  ];

  // Command to compare two different environments.
  $items['diffy-project-compare'] = [
    'description' => 'Request comparison for a project.',
    'arguments' => [
      'projectId' => 'The project ID for which to compare environments.'
    ],
    'options' => [
      'environments' => "The environments to compare. Available environments are 'prod-stage', 'prod-dev', 'stage-dev', 'custom', 'baseline-stage', 'baseline-prod', 'baseline-dev'.",
    ],
    'aliases' => ['diffy-pc'],
  ];

  // Command to create diff between two snapshots.
  $items['diffy-project-diff'] = [
    'description' => 'Request diff for a project.',
    'arguments' => [
      'projectId' => 'The project ID for which to take a diff.'
    ],
    'options' => [
      'snapshot1' => "First snapshot to compare.",
      'snapshot2' => "Second snapshot to compare."
    ],
    'aliases' => ['diffy-pd'],
  ];

  // Command to set the project baseline to a certain snapshot id.
  $items['diffy-project-baseline'] = [
    'description' => 'Request screenshots for a project.',
    'arguments' => [
      'projectId' => 'The project ID for which to set the baseline.',
      'screenshotsId' => 'The screenshots ID for which to set the baseline.'
    ],
    'aliases' => ['diffy-pb'],
  ];

  return $items;
}

/**
 * Request a token based on the key given.
 */
function drush_diffy_refresh_token() {
  $key = getenv("DIFFY_API_KEY");
  
  if (empty($key)) {
    return drush_set_error(dt('No key provided, can not request token.'));
  }
  else {
    $ch = curl_init('https://app.diffy.website/api/auth/key');
    $payload = json_encode(array( "key"=> $key));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $token = json_decode($result)->token;
    
    if ($result !== false) {
      $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if ($statusCode === 200) {
        drush_invoke_process('@self', 'variable-set', array('diffy_token', $token), array('exact' => TRUE), array('integrate' => FALSE));
        drush_log(dt('Diffy token refreshed.'), 'ok');
      }
      else {
        return drush_set_error(dt('Curl request failed with statuscode @statuscode.', array('@statuscode' => $statusCode)));
      }
    }
    curl_close($ch);
  }
}

/**
 * Request a token based on the key given.
 */
function drush_diffy_project_snapshot($projectId = '') {
  
  $projectId = _get_diffy_project_id($projectId);
  if (!empty($projectId)) {
    $diffy_token = drush_invoke_process('@self', 'variable-get', array('diffy_token'), array(), array('integrate' => FALSE));
    $token = $diffy_token['object']['diffy_token'];
    $environment = drush_get_option('environment');
    $baseUrl = drush_get_option('baseUrl');
    $wait = drush_get_option('wait') ? drush_get_option('wait') : 30;
    $payload = [];
    if (isset($environment)) {
      $payload['environment'] = $environment;
    }
    if (isset($baseUrl)) {
      $payload['baseUrl'] = $baseUrl;
    }
    $ch = curl_init("https://app.diffy.website/api/projects/$projectId/screenshots");
    $payload = json_encode($payload);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization: Bearer ' . $token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);

    if ($result !== false) {
      $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if ($statusCode === 200) {
        $diffyPreviousSnapshot = drush_invoke_process('@self', 'variable-get', array('diffy_last_snapshot'), array(), array('integrate' => FALSE));
        $previousSnapshot = $diffyPreviousSnapshot['object']['diffy_last_snapshot'];
        drush_invoke_process('@self', 'variable-set', array('diffy_prev_snapshot', $previousSnapshot), array('exact' => TRUE), array('integrate' => FALSE));
        drush_invoke_process('@self', 'variable-set', array('diffy_last_snapshot', $result), array('exact' => TRUE), array('integrate' => FALSE));
        drush_log(dt('Snapshot created: @url.', array('@url' => 'https://app.diffy.website/#/snapshots/' . $result)), 'ok');
        _wait_for_snapshot($result, $wait);
      }
      else {
        return drush_set_error(dt('Curl request failed with statuscode @statuscode.', array('@statuscode' => $statusCode)));
      }
    }
    curl_close($ch);
  }
}

/**
 * Request a comparison between environments.
 */
function drush_diffy_project_compare($projectId = '') {
  $projectId = _get_diffy_project_id($projectId);
  if (!empty($projectId)) {
    $diffy_token = drush_invoke_process('@self', 'variable-get', array('diffy_token'), array(), array('integrate' => FALSE));
    $token = $diffy_token['object']['diffy_token'];
    $environments = drush_get_option('environments');
    $payload = [];
    if (isset($environments)) {
      $payload['environments'] = $environments;
    }
    $ch = curl_init("https://app.diffy.website/api/projects/$projectId/compare");
    $payload = json_encode($payload);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization: Bearer ' . $token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);

    if ($result !== false) {
      $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if ($statusCode === 200) {
        drush_log(dt('Diff created: @url.', array('@url' => 'https://app.diffy.website/#/diffs/' . $result)), 'ok');
      }
      else {
        return drush_set_error(dt('Curl request failed with statuscode @statuscode.', array('@statuscode' => $statusCode)));
      }
    }
    curl_close($ch);
  }
}

/**
 * Request a diff between snapshots.
 */
function drush_diffy_project_diff($projectId = '') {
  $projectId = _get_diffy_project_id($projectId);
  if (!empty($projectId)) {
    $diffy_token = drush_invoke_process('@self', 'variable-get', array('diffy_token'), array(), array('integrate' => FALSE));
    $token = $diffy_token['object']['diffy_token'];
    $snapshot1 = drush_get_option('snapshot1');
    $snapshot2 = drush_get_option('snapshot2');
    $payload = [];
    if (!empty($snapshot1)) {
      $payload['snapshot1'] = $snapshot1;
    }
    else {
      $diffyPreviousSnapshot = drush_invoke_process('@self', 'variable-get', array('diffy_prev_snapshot'), array(), array('integrate' => FALSE));
      $payload['snapshot1'] = isset($diffyPreviousSnapshot['object']['diffy_prev_snapshot']) ? $diffyPreviousSnapshot['object']['diffy_prev_snapshot'] : '';
    }
    if (!empty($snapshot2)) {
      $payload['snapshot2'] = $snapshot2;
    }
    else {
      $diffyLastSnapshot = drush_invoke_process('@self', 'variable-get', array('diffy_last_snapshot'), array(), array('integrate' => FALSE));
      $payload['snapshot2'] = isset($diffyLastSnapshot['object']['diffy_last_snapshot']) ? $diffyLastSnapshot['object']['diffy_last_snapshot'] : '';
    }
    $ch = curl_init("https://app.diffy.website/api/projects/$projectId/diffs");
    $payload = json_encode($payload);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization: Bearer ' . $token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);

    if ($result !== false) {
      $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if ($statusCode === 200) {
        drush_log(dt('Diff created: @url.', array('@url' => 'https://app.diffy.website/#/diffs/' . $result)), 'ok');
      }
      else {
        return drush_set_error(dt('Curl request failed with statuscode @statuscode.', array('@statuscode' => $statusCode)));
      }
    }
    curl_close($ch);
  }
}

/**
 * Set the baseline for a project with chosen snapshot.
 */
function drush_diffy_project_baseline($projectId = '', $snapshotId = '') {
  $projectId = _get_diffy_project_id($projectId);
  if (!empty($projectId)) {
    $diffy_token = drush_invoke_process('@self', 'variable-get', array('diffy_token'), array(), array('integrate' => FALSE));
    $token = $diffy_token['object']['diffy_token'];
    if (empty($snapshotId)) {
      $diffyLastSnapshot = drush_invoke_process('@self', 'variable-get', array('diffy_last_snapshot'), array(), array('integrate' => FALSE));
      $snapshotId = isset($diffyLastSnapshot['object']['diffy_last_snapshot']) ? $diffyLastSnapshot['object']['diffy_last_snapshot'] : '';
    }
    $ch = curl_init("https://app.diffy.website/api/projects/$projectId/set-base-line-set/$snapshotId");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization: Bearer ' . $token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);

    if ($result !== false) {
      $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if ($statusCode === 200) {
        drush_log(dt('Snapshot @number set as baseline.', array('@number' => $snapshotId)), 'ok');
      }
      else {
        return drush_set_error(dt('Curl request failed with statuscode @statuscode.', array('@statuscode' => $statusCode)));
      }
    }
    curl_close($ch);
  }
}

function _get_diffy_project_id($projectId) {
  if (empty($projectId)) {
    $diffyProjectId = drush_invoke_process('@self', 'variable-get', array('diffy_project_id'), array(), array('integrate' => FALSE));
    $projectId = isset($diffyProjectId['object']['diffy_project_id']) ? $diffyProjectId['object']['diffy_project_id'] : '';
  }
  if (empty($projectId)) {
    return drush_set_error(dt('No project id provided, can not make API callback.'));
  }
  
  return $projectId;
}

function _wait_for_snapshot($snapshotId, $wait) {
  if ($wait > 0) {
    $diffy_token = drush_invoke_process('@self', 'variable-get', array('diffy_token'), array(), array('integrate' => FALSE));
    $token = $diffy_token['object']['diffy_token'];
    $ch = curl_init("https://app.diffy.website/api/snapshots/$snapshotId");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization: Bearer ' . $token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = json_decode(curl_exec($ch));
  
    if ($result->state < 2) {
      drush_log(dt('Snapshot in progress: @results of @items.', array('@results' => $result->status->results, '@items' => $result->status->items)), 'ok');
      sleep($wait);
      _wait_for_snapshot($snapshotId, $wait);
    }
    else {
      drush_log(dt('Snapshot is finished: @results of @items.', array('@results' => $result->status->results, '@items' => $result->status->items)), 'ok');
    }
  }
}