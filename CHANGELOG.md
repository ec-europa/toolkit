# Toolkit change log

## Version 4.0.6
  - MULTISITE-22612: Introduce component-whitelist check for NE-Pipelines
  - MULTISITE-22957: Allow usage of Drush 10

## Version 4.0.5
  - MULTISITE-23137: Support new version for .opts.yml file.

## Version 4.0.4
  - MULTISITE-22484: Create toolkit task to reset project.
  - MULTISITE-22672: ENV Variable for setting the TMP path in drupal 8.
  - MULTISITE-22694: Allow site to be installed in a different site folder.
  - MULTISITE-22840: Replace rsync with git archive in toolkit:build-dist.
  - MULTISITE-22878: Make ASDA url configurable through runner.yml.dist.
  - MULTISITE-22354: Provide documentation in Toolkit regarding .opts.yml.

## Version 4.0.3
  - MULTISITE-22483: Document use of behat tags in toolkit 4.
  - MULTISITE-22583: Remove hardcoded grumphp configuration.
  - MULTISITE-22715: Add file_private_path to the toolkit settings.php block.
  - MULTISITE-22585: ASDA url should be fully configurable.

## Version 4.0.2
  - MULTISITE-22407: Step failing if no scenario is provided.

## Version 4.0.1
  - Provide update command for .opts.yml file in toolkit:install-clone.
  - Allow hash_salt to read from environment variable.
  - Change grumphp convention on toolkit.

## Version 4.0.0
  - Include config:import within toolkit:install-clean

## Version 4.0.0-beta10
  - Fix issues toolkit:install-dump

## Version 4.0.0-beta9
  - Fix issues with vendor/bin folder.

## Version 4.0.0-beta8
  - Fix issues with ASDA download, improve process.
  - Regenerate file behat.yml within each execution.
  - Refactor build-dist to support local patches.
  - Introduce support to local packages.
  - Include manifest.json in the distribution package.
  - Move drupal/console to suggest section.
  - Update task-runner for beta6.

## Version 4.0.0-beta7
  - Create notitications target "toolkit:notifications".

## Version 4.0.0-beta5
  - Fix issue with toolkit:install-clone.
  - Update documentation for project-id and env vars.

## Version 4.0.0-beta3
  - Force QA Automation conventions to be used.

## Version 4.0.0-beta2 (MVP)
  - Build site for local development.
  - Build the distribution package.
  - Disable aggregation and clear cache.
  - Download ASDA snapshot.
  - Import config.
  - Install a clean website.
  - Install a clone website.
  - Install clone from production snapshot.
  - Run Behat tests.
  - Run PHP code review.
