
Testing the project
===================

This guide explains how to use the resources provided by toolkit to test your
project. You can list all resources available with the following command:

Behat testing
-------------

To run behat tests you can make use of the ``toolkit:test-behat`` command. This will
re-generate your behat configuration from ``./behat.yml.dist`` and run it on your
current site installation.

New tests should be stored in ``./tests/features/`` folder, then they will be executed
automatically by toolkit task.

To run the behat tests:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:test-behat

CICD will pick the default profile to run against clone install and clean profile to run against clean install.


* default profile: executed against clone-install
* clean profile: executed against clean-install

The default configuration for this is to have the following defined in your
``./behat.yml.dist`` file:

.. code-block:: yaml

   default:
     suites:
       default:
       ...
   clean:
     suites:
       default:
       ...

Commands to run before/after Behat tests
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Additional commands can be run before and/or after the test. Such commands
should be defined in the ``./runner.yml.dist`` or ``./runner.yml`` files:

.. code-block:: yaml

   toolkit:
     test:
       behat:
         commands:
           before:
             - task: exec
               command: ls -la
             - ...
           after:
             - task: exec
               command: whoami
             - ...

PHPCS testing
-------------

To run coding standard tests you can make use of the ``toolkit:test-phpcs``
command. This will validate your configuration from ``./grumphp.yml.dist`` and run
it on your current codebase.

To run the coding standard checks:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:test-phpcs

This will first validate the configuration of your ``./grumphp.yml.dist``. The
correct configuration of this file contains the import of the qa conventions
like shown below:

.. code-block:: yaml

   imports:
     - { resource: vendor/ec-europa/qa-automation/dist/qa-conventions.yml }
   parameters:
     tasks.phpcs.ignore_patterns:
       - vendor/
       - web/
     tasks.phpcs.triggered_by:
       - php
       - module
       - inc
       - theme
       - install
       - yml

     extensions:
       - OpenEuropa\CodeReview\ExtraTasksExtension

Previously this was hardcoded in your composer.json. But if you want to be able
to override configuration from the qa convention you should remove that setting.
So if you have the following lines in your composer.json you should remove those
lines:

.. code-block:: json

   "grumphp": {
       "config-default-path": "vendor/ec-europa/qa-automation/dist/qa-conventions.yml"
   }

PHPCBF task is available, to automatic fix your issues please run the following
command:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:run-phpcbf --file-path=<file-to-fix>

PHPUnit testing
---------------

Use the ``toolkit:test-phopunit`` command to run PHPUnit tests. The command will
look for a ``phpunit.xml.dist`` or a  files, in the configured
locations. If ``phpunit.xml.dist`` is found, a ``phpunit.xml`` will be generated. If
``phpunit.xml.dist`` is missing but there's a ``phpunit.xml`` file, the latter is
used.

Tests should be organised according to `PHPUnit documentation <https://phpunit.readthedocs.io/en/9.5/organizing-tests.html>`_.

To run the PHPUnit tests:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:test-phpunit

Commands to run before/after PHPUnit tests
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Additional commands can be run before and/or after the test. Such commands
should be defined in the ``./runner.yml.dist`` or ``./runner.yml`` files:

.. code-block:: yaml

   toolkit:
     test:
       phpunit:
         commands:
           before:
             - task: exec
               command: ls -la
             - ...
           after:
             - task: exec
               command: whoami
             - ...

PHPMD testing
-------------

To run PHP Mess Detector tests you can make use of the ``toolkit:test-phpmd``
command. If the project does not have a phpmd.xml file in the root folder,
Toolkit will create the default config file.

To run the PHP Mess Detector checks:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:test-phpmd

These are the default configurations in the runner.yml file.

.. code-block:: yaml

   toolkit:
     test:
       phpmd:
         config: phpmd.xml
         format: ansi
         ignore_patterns:
           - vendor/
           - web/
           - node_modules/
           - config/
         triggered_by:
           - php
           - module
           - inc
           - theme
           - install
         files:
           - lib
           - src

Testing in CI
-------------

Toolkit is full integrated with official pipeline that currently requires minimum
of 1 behat test and a clean report for phpcs check.

Any customization done in your project will be respected in Drone build.

Other topics
^^^^^^^^^^^^


* `Setting up a project </docs/setting-up-project.md>`_
* `Configuring a project </docs/configuring-project.md>`_
* `Installing the project </docs/installing-project.md>`_
* Testing the project
* `Using Docker environment </docs/docker-environment.md>`_
* `Continuous integration </docs/continuous-integration.md>`_
* `Available tasks </docs/available-tasks.md>`_
* `Building assets </docs/building-assets.md>`_
* `Git Hooks </docs/git-hooks.md>`_
* `Update Project Documentation </docs/project-documentation.md>`_
* `Changelog </CHANGELOG.md>`_
