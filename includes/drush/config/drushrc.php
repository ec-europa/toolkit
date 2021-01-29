<?php

/**
 * @file
 * Custom overrides.
 */

// Specify the base_url that should be used when generating links.
$virtualHost = getenv('VIRTUAL_HOST');
if (!empty($virtualHost)) {
  $availableHosts = explode(",", $virtualHost);
  $options['l'] = end($availableHosts);
}

// Specify your Drupal core base directory.
$documentRoot = getenv('DOCUMENT_ROOT');
if (!empty($documentRoot)) {
  $options['r'] = getenv('DOCUMENT_ROOT');
}
