# Toolkit change log
## Version 3.0.10
  * New phing target to set variable with drush by use of ${variable-name} and ${variable-value} properties.
  * New phing target to loop over all phing properties starting with devel.vars. and set them accordingly with another new target
  * Removes all variables from settings.php
  * Removes cache-reset-target since it is obsolete with this functionality
  * Include maillog_send to the dev variables to avoid sending emails
  * Bug fix to project info target
  * update upgrade documentation step to use a previous composer.json that does not include the URL tokens which impede on the upgrade process
  * run the toolkit-generate-project-info step inside of the toolkit-starterkit-upgrade target at the end
  * override the files if generating project info
  * added project.install.modules for usage on existing toolkit projects
  * build.dist new default value to match with project.id

## Version 3.0.9
  * Several improvements and stable version.

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

<<<<<<< HEAD
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
=======
### Improvements
  * [MULTISITE-17744] - Toolkit is re-architectured into a separated composer package
  * [MULTISITE-17744] - Toolkit is re-architectured to support both platform and subsites
  * [MULTISITE-17365] - Toolkit is re-architectured to categorize build files into a better structure
  * [MULTISITE-18484] - Development modules are moved to resources/devel.make
  * [MULTISITE-18485] - Phing will look for composer.bin if the setting is incorrect
  * [MULTISITE-18306] - Phing help command is customized to improve the display and descriptions
  * [MULTISITE-18315] - Package downloads now all use same helper target and are automatically cached
  * [MULTISITE-18315] - ASDA download is simplified. Credentials can only be set through build properties
  * [MULTISITE-18486] - Devops modules are now included in the build to avoid missing modules
  * [MULTISITE-18487] - Files directories are created now with the ensuring of htaccess files
  * [MULTISITE-17365] - All drush targets have been aggregated into a single helper file build/help/drush.xml
  * [MULTISITE-16459] - Drush registry rebuild target will download itself if not available yet
  * [MULTISITE-17365] - Platform rebuilds will automatically backup and restore subsite by default
  * [MULTISITE-18488] - Platform version file is generated on the fly and informs user on latest available version
  * [MULTISITE-18318] - New target to validate build properties has been introduced to ensure minimum required properties
  * [MULTISITE-18096] - Github helper target has been introduced to upload release packages to the repository
  * [MULTISITE-18349] - Subsite starterkit 2.x targets have been deprecated and mapped to new ones
  * [MULTISITE-18340] - Drush generates aliases on installation process to improve multisite support
  * [MULTISITE-18196] - EC Europa theme build has been added in a separate build file
  * [MULTISITE-17744] - Installation process support created for composer create-project command
  * [MULTISITE-18096] - Test folder has been made independent to allow test package releases
  * [MULTISITE-18490] - Composer hooks now run phing targets defined in the build properties
  * [MULTISITE-18494] - General fixes for CI integration
  * [MULTISITE-18489] - Temporary files folder has been renamed from ./tmp to ./.tmp to decrease visibility
  * [MULTISITE-18520] - Improved user guide
  * [MULTISITE-18525] - Integration with Git hooks system
  * [MULTISITE-18563] - Integration with PHPUnit tests
  * [MULTISITE-18579] - Integration with Drone CI pipeline
  * [MULTISITE-17096] - Build dist now uses new symlink system. Lib folder structure will match dist folder structure
  * [MULTISITE-17624] - New build property introduced to select a solr module and accompanied core
  * [MULTISITE-18248] - Docker environment has been introduced, still experimental!

### Security
  * [NESTF-31] - New phing task introduced to validate the make files according to Drupal's security advisory
>>>>>>> master

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
