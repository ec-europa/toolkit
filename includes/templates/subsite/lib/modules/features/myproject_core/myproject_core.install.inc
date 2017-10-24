<?php

/**
 * @file
 * Helper functions to be used during install/update phase.
 */

/**
 * Create a new basic page for demo purpose.
 */
function _myproject_example_function() {
  $values = array(
    'type' => 'page',
    'uid' => 1,
    'status' => 1,
    'comment' => 0,
    'promote' => 0,
    'path' => array(
      'alias' => 'hello-world',
      'pathauto' => FALSE,
    ),
    'menu' => array(
      'link_title' => 'Hello world',
      'menu_name' => 'main-menu',
      'plid' => 0,
      'enabled' => 1,
      'weight' => -49,
    ),
  );
  $entity = entity_create('node', $values);
  $wrapper = entity_metadata_wrapper('node', $entity);
  $wrapper->title->set('Hello World!');
  $wrapper->save();
}
