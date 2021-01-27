<?php

/**
 * @file
 * Custom overrides.
 */

// Specify the base_url that should be used when generating links.
if (isset(getenv('VIRTUAL_HOST'))) {
  $options['l'] = end(explode(",", getenv('VIRTUAL_HOST')));
}

// Specify your Drupal core base directory.
if (isset(getenv('DOCUMENT_ROOT'))) {
  $options['r'] = getenv('DOCUMENT_ROOT');
}
