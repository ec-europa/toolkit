<?php
$databases = array (
  'default' => array (
    'default' => array (
      'driver' => 'mysql',
      'username' => 'root',
      'password' => '',
      'host' => 'mysql',
      'port' => '3306',
      'database' => 'drupal',
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_general_ci',
    ),
    'extra' => array (
      'driver' => 'mysql',
      'username' => 'root',
      'password' => 'password',
      'host' => 'mysql',
      'port' => '3306',
      'database' => 'extra',
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_general_ci',
    ),
  ),
);
$conf['reverse_proxy'] = TRUE;
$conf['reverse_proxy_proto_header'] = 'HTTP_X_FORWARDED_PROTO';