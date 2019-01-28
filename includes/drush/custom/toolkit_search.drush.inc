<?php

/**
 * @file
 * Search module drush integration.
 */

/**
 * Implements hook_drush_command().
 *
 * Returns an associative array describing drush command(s).
 *
 * @see drush_parse_command()
 */
function toolkit_search_drush_command() {
  $items = array();

  $items['toolkit-search-search-api-set-solr-server-url'] = array(
    'description' => "Set search api server url.",
    'aliases' => array('toolkit-search-ssurl'),
    'arguments' => array(
      'scheme' => dt('The HTTP protocol to use for sending queries.'),
      'host' => dt('The host name or IP of your Solr server.'),
      'port' => dt('The solr port.'),
      'path' => dt('The path that identifies the Solr instance to use on the server.'),
      'servername' => dt('The name that identifies the Solr instance to use on the server.'),
    ),
  );

  return $items;
}

/**
 * Callback for settings search api server host.
 */
function drush_toolkit_search_search_api_set_solr_server_url($scheme, $host, $port, $path, $servername) {
  $server = search_api_server_load($servername, TRUE);
  $options = $server->options;
  $options['scheme'] = $scheme;
  $options['host'] = $host;
  $options['port'] = $port;
  $options['path'] = $path;
  search_api_server_edit($servername, array('options' => $options));
}
