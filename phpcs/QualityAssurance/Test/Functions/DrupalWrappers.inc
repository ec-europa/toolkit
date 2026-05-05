<?php

/**
 * @file
 * Drupal needs this blank file.
 */

/**
 * This is a function with forbidden functions.
 */
function _example_fail_forbidden_functions() {
  // Forbidden non drupal wrapped functions.
  $register_shutdown_function = register_shutdown_function('function');
  $set_time_limit = set_time_limit(5);
  $xml_parser_create = xml_parser_create(NULL);
  $basename = basename('string');
  $chmod = chmod('string');
  $dirname = dirname('string');
  $mkdir = mkdir('string');
  $move_uploaded_file = move_uploaded_file('string');
  $realpath = realpath('string');
  $rmdir = rmdir('string');
  $tempnam = tempnam('string', 'string');
  $unlink = unlink('string');
  $lcfirst = lcfirst('string');
  $ucwords = ucwords('string');
  $http_build_query = http_build_query([]);
  $parse_url = parse_url('string');
}