
Commands
===============

To list all available tasks, please run:

.. code-block::

   docker-composer exec web ./vendor/bin/run

See bellow current list of available commands:

.. toolkit-block-commands

.. code-block::

   Available commands:
     completion                        Dump the shell completion script
     config                            Dumps the current configuration.
     help                              Display help for a command
     list                              List commands
    drupal
     drupal:drush-setup                Write Drush configuration file at "${drupal.root}/drush/drush.yml".
     drupal:permissions-setup          Setup Drupal permissions.
     drupal:settings-setup             Setup Drupal settings.php file in compliance with Toolkit conventions.
     drupal:site-install               [drupal:si|dsi] Install target site.
     drupal:site-post-install          Run Drupal post-install commands.
     drupal:site-pre-install           Run Drupal pre-install commands.
    toolkit
     toolkit:build-assets              [tba|tk-assets] Build theme assets (Css and Js).
     toolkit:build-dev                 [tk-bdev] Build site for local development.
     toolkit:build-dev-reset           Build site for local development from scratch with a clean git.
     toolkit:build-dist                [tk-bdist] Build the distribution package.
     toolkit:check-phpcs-requirements  Make sure that the config file exists and configuration is correct.
     toolkit:check-version             Check the Toolkit version.
     toolkit:code-review               This command will execute all the testing tools.
     toolkit:complock-check            Check if 'composer.lock' exists on the project root folder.
     toolkit:component-check           Check composer.json for components that are not whitelisted/blacklisted.
     toolkit:disable-drupal-cache      Disable aggregation and clear cache.
     toolkit:download-dump             Download ASDA snapshot.
     toolkit:drupal-upgrade-status     [tdus] Check project compatibility for Drupal 9/10 upgrade.
     toolkit:fix-permissions           Run script to fix permissions (experimental).
     toolkit:hooks-delete-all          Remove all existing hooks, this will ignore active hooks list.
     toolkit:hooks-disable             Disable the git hooks.
     toolkit:hooks-enable              Enable the git hooks defined in the configuration or in given option.
     toolkit:hooks-list                List available hooks and its status.
     toolkit:hooks-run                 Run a specific hook.
     toolkit:import-config             [tk-ic] Import config.
     toolkit:install-clean             [tk-iclean] Install a clean website.
     toolkit:install-clone             [tk-iclone] Install a clone website.
     toolkit:install-dependencies      Install packages present in the opts.yml file under extra_pkgs section.
     toolkit:install-dump              Import the production snapshot.
     toolkit:lint-js                   [tljs|tk-js] Run lint JS.
     toolkit:lint-php                  [tlp|tk-php] Run lint PHP.
     toolkit:lint-yaml                 [tly|tk-yaml] Run lint YAML.
     toolkit:opts-review               Check project's .opts.yml file for forbidden commands.
     toolkit:requirements              Check the Toolkit Requirements.
     toolkit:run-blackfire             [tbf|tk-bfire] Run Blackfire.
     toolkit:run-deploy                Run deployment sequence.
     toolkit:run-phpcbf                [tk-phpcbf] Run PHP code autofixing.
     toolkit:setup-blackfire-behat     Copy the needed resources to run Behat with Blackfire.
     toolkit:setup-eslint              Setup the ESLint configurations and dependencies.
     toolkit:setup-phpcs               Setup PHP code sniffer.
     toolkit:test-behat                [tb|tk-behat] Run Behat tests.
     toolkit:test-phpcs                [tk-phpcs] Run PHP code sniffer.
     toolkit:test-phpmd                [tk-phpmd] Run PHPMD.
     toolkit:test-phpstan              [tk-phpstan] Run PHPStan.
     toolkit:test-phpunit              [tp|tk-phpunit] Run PHPUnit tests.
     toolkit:vendor-list               Check 'Vendor' packages being monitorised.

.. toolkit-block-commands-end
