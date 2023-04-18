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
        "php_version": "8.0"
    },
    "php_version": "8.0",
    "toolkit": "^3.7.2|^8.7.1|^9.6|^10",
    "drupal": "^7.91|>=9.4.10 <9.5.0|^9.5.2|^10.0.2",
    "vendor_list": [
        "drupal",
        "vlucas"
    ]
}
';
