# Available tasks

To list all available tasks, please run:
```
docker-composer exec web ./vendor/bin/run
```

See bellow current list for version `8.6.19`.
```
Available commands:
  help                           Displays help for a command
  list                           Lists commands
 changelog
  changelog:generate             [changelog:g|cg] Generate a changelog based on GitHub issues and pull requests.
 drupal
  drupal:drush-setup             Write Drush configuration files to given directories.
  drupal:permissions-setup       Setup Drupal permissions.
  drupal:settings-setup          Setup Drupal settings.php file in compliance with Toolkit conventions.
  drupal:setup-test
  drupal:site-install            [drupal:si|dsi] Install target site.
  drupal:site-post-install       Run Drupal post-install commands.
  drupal:site-pre-install        Run Drupal pre-install commands.
 release
  release:create-archive         [release:ca|rca] Create a release for the current project.
 toolkit
  toolkit:build-assets           [tba] Build theme assets (Css and Js).
  toolkit:build-dev              Build site for local development.
  toolkit:build-dev-reset        Build site for local development from scratch with a clean git.
  toolkit:build-dist             Build the distribution package.
  toolkit:check-version          Check the Toolkit version.
  toolkit:code-review            This command will execute all the testing tools.
  toolkit:complock-check         Check if 'composer.lock' exists on the project root folder.
  toolkit:component-check        Check composer.json for components that are not whitelisted/blacklisted.
  toolkit:disable-drupal-cache   Disable aggregation and clear cache.
  toolkit:download-dump          Download ASDA snapshot.
  toolkit:drupal-upgrade-status  [tdus] Check project compatibility for Drupal 9/10 upgrade.
  toolkit:fix-permissions        Run script to fix permissions (experimental).
  toolkit:import-config          Import config.
  toolkit:install-clean          Install a clean website.
  toolkit:install-clone          Install a clone website.
  toolkit:install-dependencies   Install packages present in the opts.yml file under extra_pkgs section.
  toolkit:install-dump           Import the production snapshot.
  toolkit:lint-php               [tlp] Run lint PHP.
  toolkit:lint-yaml              [tly] Run lint YAML.
  toolkit:notifications          Display toolkit notifications.
  toolkit:opts-review            Check project's .opts.yml file for forbidden commands.
  toolkit:requirements           Check the Toolkit Requirements.
  toolkit:run-blackfire          [tbf] Run Blackfire.
  toolkit:run-deploy             Run deployment sequence.
  toolkit:run-phpcbf             [tpb] Run PHP code autofixing.
  toolkit:run-phpcbf-standalone  Run PHP code autofixing in standalone mode.
  toolkit:setup-blackfire-behat  Copy the needed resources to run Behat with Blackfire.
  toolkit:setup-phpcs            Setup PHP code sniffer for standalone execution.
  toolkit:test-behat             [tb] Run Behat tests.
  toolkit:test-phpcs             [tp] Run PHP code sniffer.
  toolkit:test-phpmd             Run PHPMD standalone.
  toolkit:test-phpunit           [tp] Run PHPUnit tests.
  toolkit:vendor-list            Check 'Vendor' packages being monitorised.
```

### Other topics
- [Setting up a project](/docs/setting-up-project.md)
- [Configuring a project](/docs/configuring-project.md)
- [Installing the project](/docs/installing-project.md)
- [Testing the project](/docs/testing-project.md)
- [Using Docker environment](/docs/docker-environment.md)
- [Continuous integration](/docs/continuous-integration.md)
- Available tasks
- [Building assets](/docs/building-assets.md)
- [Update Project Documentation](/docs/project-documentation.md)
- [Changelog](/CHANGELOG.md)
