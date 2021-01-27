<?php

/**
 * @file
 * Custom overrides.
 */

$aliases['self'] = array(
  'root' => getenv('DOCUMENT_ROOT'),
  'uri'  => end(explode(",", getenv('VIRTUAL_HOST'))),
   array(
     '%drush' => '/usr/local/bin',
     '%site' => 'sites/default/',
   ),
);
