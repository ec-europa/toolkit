<?php

header('Content-Type: application/json; charset=utf-8');

echo '
{
    "php_version": "7.4",
    "toolkit": "^3.6.6|^8.6.17|^9.0",
    "drupal": "^7.91|^9.3.22|^9.4.7",
    "vendor_list": [
        "drupal",
        "vlucas"
    ]
}
';
