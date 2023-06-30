# Toolkit change log

## Version 9.11.0 | 10.1.0
- DQA-6750: Command to check drupal permissions.
- DQA-6750: Control blocker of drupal:check-permissions.
- DQA-7006: Duplicated options when running toolkit:lint-php.
- DQA-6154: Component check - remove limitation of checking drupal module only.
- DQA-6681: Command drupal:permissions-setup should not apply permissions recursively.
- DQA-7280: Support Drush12.
- DQA-7333: Add aliases to commands.
- DQA-7296: Add user-agent to the Toolkit api calls.

## Version 9.10.0 | 10.0.0
  - DQA-7047: Deploy commands run drush cr as first.
  - DQA-5985: Update default value of toolkit to match the new environment.

## Version 9.9.3 & 9.9.4 | 10.0.0-beta9
  - DQA-6996: Fix regression with php lint.
  - DQA-7005: Fix regression with QA_WEBSITE_URL override.
  - DQA-7025: Fix curl redirection behaviour.
  - DQA-7043: Fix missing --endpoint option to all target that connect to our API.

## Version 9.9.2 | 10.0.0-beta8
  - DQA-6954: Remove sudo references.
  - DQA-6889: runner.yml.dist - forcing reverse proxy settings for all repo.

## Version 9.9.1 | 10.0.0-beta7
  - DQA-6871: GitHooks detect docker-compose VS docker compose.
  - DQA-6930: Toolkit allow to skip abandoned components.

## Version 9.9.0 | 10.0.0-beta6
  - DQA-6736: PHPStan do not use includes if phpstan/extension-installer exist.
  - DQA-6762: Toolkit requirements should not use constant to validate version.
  - DQA-6670: GitHooks pass $io to extending class methods.
  - DQA-6677: Use git hook commit-msg instead of prepare-commit-msg.
  - DQA-6795: Consider mydumper in checkForNewerDump.
  - DQA-6808: GitHooks identify if running docker.
  - DQA-6792: Document how configuration works.
  - DQA-6759: Fix outdated packages command.

## Version 9.8.1 | 10.0.0-beta5
  - DQA-6717: Fix version check to allow version without dot.
  - DQA-6651: Command build-dist make tag and sha optional.
  - DQA-6694: PHPStan drupal root dynamic & missing ruleset.

## Version 9.8.0 | 10.0.0-beta4
  - DQA-6404: Git pre-commit re-use phpcs.xml file.
  - DQA-6403: PHPStan allow to run in standalone.
  - DQA-6544: Refactor command run-deploy to not use Robo config.
  - DQA-6602: Control install-dependencies in config.

## Version 9.7.1 | 10.0.0-beta3
  - DQA-0: Prevent gitattributes from ignoring nested files.
  - DQA-0: Apply branch condition in drone.

## Version 9.6.1 | 10.0.0-beta2
  - DQA-5962: Refactor configurations load.

## Version 9.6.0 | 10.0.0-beta1
  - DQA-6074: Create .gitattributes file for toolkit.
  - DQA-5407: Monitor abandoned components.
  - DQA-6133: Component check to use options no-dev & locked.
  - DQA-6261: Include support for key_auth in toolkit.
  - DQA-6344: ConfigurationCommands when overriding commands.
  - DQA-6355: Requirements remove api calls.
  - DQA-5962: Delayed task list to allow command hooks to run before.
  - DQA-6359: Load both runner.yml and runner.yml.dist files.
  - DQA-5962: Allow runner.yml to override all other configs.

## Version 9.5.0 | 10.0.0-beta
  - DQA-6103: Add configuration command to run drush commands.
  - DQA-6085: Rename toolkit:import-config into drupal:config-import.
  - DQA-6128: Update constraint for consolidation/annotated-command.
  - DQA-6138: Configuration commands are not overriding defaults.

## Version 9.4.0
  - DQA-6028: Allow options in command test-phpunit.

## Version 9.3.0
  - DQA-5410: Improve load of database during clone install.
  - DQA-5473: Tests for DrupalCommands.
  - DQA-5760: Fix overridden configurations not working.
  - DQA-5643: Apply to 'ensureArray' all tasks.
  - DQA-5484: Better error handling.
  - DQA-5743: Create task to self-update docker-compose.yml.
  - DQA-5317: Allow multiple config files to be loaded.
  - DQA-5885: Add command drupal:symlink-project.
  - DQA-4768: Allow myloader to be used.
  - DQA-5913: PHP 8.2 support.
  - DQA-5945: Allow option dumpfile in install-clone command.
  - DQA-5946: Add config command copy-dir.

## Version 9.2.0
  - DQA-5384: Improve test coverage for toolkit.
  - DQA-5102: Set outdated as soft block.
  - DQA-5452: Improve recommended check output.
  - DQA-5323: Avoid backslash bypass on .opts.yml file review.
  - DQA-5323: Website::basicAuth loads SymfonyStyle when not need.
  - DQA-5482: Tests for ComponentCheckCommands.
  - DQA-5483: PHPunit mock for webservices.
  - DQA-5453: Commands maintenance.
  - DQA-5456: Hook pre-push replace lint-yaml with test-phpmd.
  - DQA-5411: Allow options in the lint commands.
  - DQA-5319: Fix ReplaceBlock and add tests.
  - DQA-5507: Manifest drupal profile fallback to runner.yml.
  - DQA-5319: Move documentation into a branch.
  - DQA-5453: Remove usage of deprecated io() & PHPmd.
  - DQA-5477: Tests for ToolkitReleaseCommands & changelog-write improvements.
  - DQA-5536: PHPStan allow option memory-limit.
  - DQA-5546: Set default ASDA as NEXTCLOUD.
  - DQA-5453: Documentation & ToolkitRelease.
  - DQA-5476: Tests for ToolkitCommands & ConfigurationCommands.
  - DQA-5475: Tests for ToolCommands.
  - DQA-5474: Tests for TestsCommands.
  - DQA-5627: Command opts-review to check bad php_version.

## Version 9.1.1
  - DQA-5129: Define timeouts in curl.
  - DQA-5409: Fix Commands loaded twice.
  - DQA-5289: Display warning for abandoned packages.
  - DQA-5371: Do not execute phpmd together with phpcs.

## Version 9.1.0
  - DQA-5203: Changelog.
  - DQA-5203: Add missing tests.
  - DQA-5203: Revert drupal:settings-setup.
  - DQA-5203: Use taskReplaceBlock in settings-setup.
  - DQA-5203: Docs.
  - DQA-5203: Add task to replace block.
  - DQA-5203: Docs.
  - DQA-5203: Fix phpcs-setup.
  - DQA-5203: Docs
  - DQA-5203: Update docs.
  - DQA-5203: Update docs.
  - DQA-5203: Remove include from phpstan.
  - DQA-5203 PHPStan.
  - DQA-5203: Small improvements.
  - DQA-5203: Add missing config for drupal command.
  - DQA-5203: Add missing config for drupal command.
  - DQA-5203: Fix test-behat & add drush-setup.
  - DQA-5203: Fix minimum phpcs.
  - DQA-5203: Drone yaml.
  - DQA-5203: Improve PHPcs and PHPmd.
  - DQA-5203: CHANGELOG & DrupalCommands improvements.
  - DQA-5245: Fix version for annotated-command.
  - DQA-4473: Tool config file.
  - DQA-4473: Remove notifications command.
  - DQA-5229: Allow passing options to behat.
  - DQA-0: Small fixes.
  - DQA-4743: Move component-check to custom class.
  - DQA-4437: Add PHPStan.
  - DQA-4743: Small improvements.
  - DQA-4743: Remove setIgnoreCommandsInTraits.
  - DQA-4743: Add custom options configCommand.
  - DQA-4743: Configs.
  - DQA-4743: Copy runner to vendor.
  - DQA-4743: Copy runner to vendor.
  - DQA-4743: Lint js & drone.
  - DQA-4743: Yaml linter.
  - DQA-4743: Cleanup & lint-yaml.
  - DQA-4743: Runner check if config exists.
  - DQA-4743: Tests.
  - DQA-4743: Fix install commands.
  - DQA-4743: Add DrupalCommands.
  - DQA-4743: Remove name from ConfigurationCommands.
  - DQA-4743: Toolkit commands.
  - DQA-4743: Fix phpcs.
  - DQA-4743: Add ConfigurationCommands.
  - DQA-4743: Phpcs.
  - DQA-4743: Exclude bin from phpcs.
  - DQA-4743: Move run to root & adapt tests.
  - DQA-4743: Add runner & defaults.
  - DQA-4743: Remove openeuropa/task-runner.
  - DQA-4899: Add git hooks documentation.
  - DQA-4899: Refresh documentation.
  - DQA-4899: Refresh documentation.
  - DQA-4899: Fix links in the README file.
  - DQA-4899: Fix links in the README file.
  - DQA-4899: Rename Guides into Getting Started.
  - DQA-4899: Convert existent MD files into reStructuredText files.
  - DQA-4899: Add fresh documentation.
  - DQA-4899: Add fresh documentation.
  - DQA-4899: Add section getting started.
  - DQA-4899: Reallocate documentation in a specific folder.

## Version 9.0.0
  - DQA-0000: Update support email and README;
  - DQA-4585: Make pipeline fail if package is not found;
  - DQA-4745: Add Git hook commands;
  - DQA-4440: Drop GrumPHP dependency and set minimum PHP version to 8.1;

## Version 8.5.1
  - Support version 1 and 2 of openeuropa/task-runner;

## Version 8.5.0
  - Extend support to PHP version 8.0 and 8.1;
  - Support to drush version 11;
  - Update qa-automation minimum version to 8.1.2;
  - Disable GrumPHP parallel feature;
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
