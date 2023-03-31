<?php

header('Content-Type: application/json; charset=utf-8');

echo '
{
    "defaults": {
        "php_version": {
            "image": "fpfis/httpd-php",
            "version": "8.1",
            "service": "web"
        },
        "mysql_version": {
            "image": "percona/percona-server",
            "version": "5.7",
            "service": "mysql"
        },
        "selenium_version": {
            "image": "selenium/standalone-chrome",
            "version": "4.1.3-20220405",
            "service": "selenium"
        },
        "solr_version": {
            "image": "solr",
            "version": "8",
            "service": "solr"
        }
    },
    "requirements": {
        "php_version": "7.4"
    },
    "php_version": "7.4",
    "toolkit": "^3.6.6|^8.6.17|^9.0|^10",
    "drupal": "^7.91|^9.3.22|^9.4.7",
    "vendor_list": [
        "drupal",
        "vlucas"
    ]
}
';
