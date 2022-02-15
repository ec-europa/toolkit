# Toolkit change log

## Version 8.5.1
  - Support version 1 and 2 of openeuropa/task-runner

## Version 8.5.0
  - Extend support to PHP version 8.0 and 8.1;
  - Support to drush version 11;
  - Update qa-automation minimum version to 8.1.2;
  - Disable grumphp parallel feature;
  - Include drush.yml file in the distribution with options:uri set with the value from env.VIRTUAL_HOST;
  - Improve test coverage to toolkit itself;
  - Add new target toolkit:check-version;
  - Add new experimental target toolkit:fix-permissions;
  - Extend list of .opts.yml of forbidden commands with: php-eval, composer, git, curl and wget;
  - Add option to toolkit:test-phpunit to control report format (--log-junit report.xml);
  - Include support to new ASDA version;
  - Other minor improvements and fixes;

## Version 8.4.1
  - Hotfix to PHP version detection;

## Version 8.4.0
  - Introduce target toolkit:requirements;
  - Remove support to compatible grumphp.yml file;

## Version 8.3.0
  - Add support to Behat profiles, see toolkit:test-behat;
  - Add support for php-unit, see toolkit:test-phpunit;
  - Include integration for Blackfire service;
  - Improve toolkit:build-assets task with support to grunt;
  - Improve output of toolkit:download-dump with data display after download;
  - Fix toolkit:test-behat mandatory test check to enforce 1 running test;
  - Add new target toolkit:lint-php to check php files syntax;
  - Add new target toolkit:lint-yaml to check yaml files syntax;
  - Add new target toolkit:opts-review to check .opts.yaml file;
  - Refactor toolkit:component-check with detection of insecure, outdated, mandatory and recommended packages;

NOTE: To have more details on the history of this major branch please check version 4.x
