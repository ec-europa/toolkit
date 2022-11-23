Commands
====

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
   drupal:disable-cache              Disable aggregation and clear cache.
   drupal:drush-setup                Write Drush configuration file at "${drupal.root}/drush/drush.yml".
   drupal:permissions-setup          Setup Drupal permissions.
   drupal:settings-setup             Setup Drupal settings.php file in compliance with Toolkit conventions.
   drupal:site-install               [drupal:si|dsi] Install target site.
   drupal:site-post-install          Run Drupal post-install commands.
   drupal:site-pre-install           Run Drupal pre-install commands.
   drupal:upgrade-status             [tdus] Check project compatibility for Drupal 9/10 upgrade.
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
   toolkit:download-dump             Download ASDA snapshot.
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

Creating custom commands
----

To provide custom commands, make sure that your classes are loaded, for example using
PSR-4 namespacing set the autoload in the composer.json file.

.. code-block::

 {
   "autoload": {
     "psr-4": {
       "My\\Project\\": "./src/"
     }
   }
 }

Create your command class under ``src/TaskRunner/Commands`` that will extend the abstract Toolkit command, like:

.. code-block::

 <?php

 namespace My\Project\TaskRunner\Commands;

 use EcEuropa\Toolkit\TaskRunner\AbstractCommands;

 class ExampleCommands extends AbstractCommands {
   /**
    * @command example:first-command
    */
   public function commandOne() { }
 }

Creating configuration commands
----

Configuration commands are created in the configuration file ``runner.yml``, like shown below:

.. code-block:: yaml

   commands:
     drupal:setup-test:
       - { task: process, source: behat.yml.dist, destination: behat.yml }

     drupal:setup-test2:
       aliases: test
       description: 'Setup the behat file'
       help: 'Some help text'
       hidden: false
       usage: '--simulate'
       tasks:
         - { task: process, source: behat.yml.dist, destination: behat.yml }

The configuration commands are a mapping to the `Robo Tasks <https://robo.li/#tasks>`_, the
list of available tasks is:

+---------+------------------------------------------------------------------------+
| Task    | Robo Task                                                              |
+=========+========================================================================+
| mkdir   | `FilesystemStack <https://robo.li/tasks/Filesystem/#filesystemstack>`_ |
+---------+------------------------------------------------------------------------+
| touch   | `FilesystemStack <https://robo.li/tasks/Filesystem/#filesystemstack>`_ |
+---------+------------------------------------------------------------------------+
| copy    | `FilesystemStack <https://robo.li/tasks/Filesystem/#filesystemstack>`_ |
+---------+------------------------------------------------------------------------+
| chmod   | `FilesystemStack <https://robo.li/tasks/Filesystem/#filesystemstack>`_ |
+---------+------------------------------------------------------------------------+
| chgrp   | `FilesystemStack <https://robo.li/tasks/Filesystem/#filesystemstack>`_ |
+---------+------------------------------------------------------------------------+
| chown   | `FilesystemStack <https://robo.li/tasks/Filesystem/#filesystemstack>`_ |
+---------+------------------------------------------------------------------------+
| remove  | `FilesystemStack <https://robo.li/tasks/Filesystem/#filesystemstack>`_ |
+---------+------------------------------------------------------------------------+
| rename  | `FilesystemStack <https://robo.li/tasks/Filesystem/#filesystemstack>`_ |
+---------+------------------------------------------------------------------------+
| symlink | `FilesystemStack <https://robo.li/tasks/Filesystem/#filesystemstack>`_ |
+---------+------------------------------------------------------------------------+
| mirror  | `FilesystemStack <https://robo.li/tasks/Filesystem/#filesystemstack>`_ |
+---------+------------------------------------------------------------------------+
| process | `Process </src/Task/File/Process.php>`_                                |
+---------+------------------------------------------------------------------------+
| append  | `Write with append() <https://robo.li/tasks/File/#write>`_             |
+---------+------------------------------------------------------------------------+
| run     | Runner tasks                                                           |
+---------+------------------------------------------------------------------------+
| exec    | `Exec <https://robo.li/tasks/Base/#exec>`_                             |
+---------+------------------------------------------------------------------------+
