
Testing the project
===================

This guide explains how to use the resources provided by Toolkit to test your
project. You can list all resources available with the following command:

.. code-block::

   docker-compose exec web ./vendor/bin/run

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

CICD will pick the default profile to run against clone install and clean profile to run
against clean install.


* default profile: executed against clone-install
* clean profile: executed against clean-install

The default configuration for this is to have the following defined in your ``./behat.yml.dist`` file:

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
should be defined in the configuration file ``runner.yml``:

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

Behat tests in GitLab CI
^^^^^^^^^^^^^^^^^^^^^^^^

In GitLab CI, it is possible to run behat in parallel, to do so, the suites must be split like shown below:

.. code-block:: yaml

   default:
     suites:
       group_1:
         ...
       group_2:
         ...

PHPcs testing
-------------

To run coding standard tests you can make use of the ``toolkit:test-phpcs`` command:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:test-phpcs

If the configuration ``phpcs.xml`` file is not found, it will be created with the configurations
provided in the ``runner.yml`` file.
You can manually generate the configuration file using the command ``toolkit:setup-phpcs``:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:setup-phpcs

The configuration file ``phpcs.xml`` will be validated by the command ``toolkit:check-phpcs-requirements``.
You can manually validate your configuration file:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:check-phpcs-requirements

This will enforce the usage of the following standards:

.. code-block::

   vendor/drupal/coder/coder_sniffer/Drupal
   vendor/drupal/coder/coder_sniffer/DrupalPractice
   vendor/ec-europa/qa-automation/phpcs/QualityAssurance

These are the default configurations in the ``runner.yml`` file.


.. code-block:: yaml

   toolkit:
     test:
       phpcs:
         mode: phpcs
         config: phpcs.xml
         ignore_annotations: 'false'
         show_sniffs: 'false'
         standards:
           - ./vendor/drupal/coder/coder_sniffer/Drupal
           - ./vendor/drupal/coder/coder_sniffer/DrupalPractice
           - ./vendor/ec-europa/qa-automation/phpcs/QualityAssurance
         ignore_patterns:
           - vendor/
           - web/
           - node_modules/
           - config/
           - '*.min.css'
           - '*.min.js'
         triggered_by:
           - php
           - module
           - inc
           - theme
           - profile
           - install
           - yml
         files:
           - ./lib
           - ./resources
           - ./src

If you want to use GrumPHP, you need to require the dependency in your ``composer.json``
and set the phpcs mode in the configuration file ``runner.yml`` as shown below:

.. code-block:: yaml

   toolkit:
     test:
       phpcs:
         mode: grumphp

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

PHPcbf task is available (not with GrumPHP), to automatic fix your issues please run the following
command:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:run-phpcbf

PHPUnit testing
---------------

Use the ``toolkit:test-phpunit`` command to run PHPUnit tests. The command will
look for a ``phpunit.xml.dist`` or a  files, in the configured
locations. If ``phpunit.xml.dist`` is found, a ``phpunit.xml`` will be generated. If
``phpunit.xml.dist`` is missing but there's a ``phpunit.xml`` file, the latter is
used.

Tests should be organised according to `PHPUnit documentation <https://phpunit.readthedocs.io/en/9.5/organizing-tests.html#composing-a-test-suite-using-xml-configuration>`_.

To run the PHPUnit tests:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:test-phpunit

Commands to run before/after PHPUnit tests
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Additional commands can be run before and/or after the test. Such commands
should be defined in the configuration file ``runner.yml``:

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
command. If the project does not have a ``phpmd.xml`` file in the root folder,
Toolkit will create the default config file.

To run the PHP Mess Detector checks:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:test-phpmd

These are the default configurations in the ``runner.yml`` file.

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

PHPStan testing
---------------

To run the PHPStan tests you can make use of the ``toolkit:test-phpstan`` command:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:test-phpstan

If the configuration ``phpstan.neon`` file is not found, it will be created with the configurations
provided in the ``runner.yml`` file.

These are the default configurations in the ``runner.yml`` file.

.. code-block:: yaml

   toolkit:
     test:
       phpstan:
         config: phpstan.neon
         level: '1'
         files: [ 'lib', 'src' ]
         ignores: [ 'vendor' ]
         memory_limit: ''
         options: ''

ESLint testing
--------------

Toolkit uses ESLint to validate the JS and YAML files.

To set up the ESLint you can make use of the ``toolkit:setup-eslint`` command.

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:setup-eslint

The command will make sure that the project has a ``package.json`` file, if not
it will be created and the needed packages will be installed.
If the configuration file ``.eslintrc.json`` is not found, it will be created with
the default configurations including the Drupal .eslintrc.json. The file
``.prettierignore`` will also be created.

These are the default configurations in the ``runner.yml`` file.

.. code-block:: yaml

   toolkit:
     lint:
       eslint:
         config: .eslintrc.json
         packages: 'eslint-config-drupal eslint-plugin-yml'
         ignores: [ 'vendor/*', 'web/*', 'dist/*' ]
         extensions_yaml: [ '.yml', '.yaml' ]
         options_yaml: ''
         extensions_js: [ '.js' ]
         options_js: ''

ESLint JS testing
^^^^^^^^^^^^^^^^^

To run the ESLint JS tests you can make use of the ``toolkit:lint-js`` command:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:lint-js

ESLint YAML testing
^^^^^^^^^^^^^^^^^^^

To run the ESLint YAML tests you can make use of the ``toolkit:lint-yaml`` command:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:lint-yaml

Testing in CI
-------------

Toolkit is full integrated with official pipeline that currently requires minimum
of 1 behat test and a clean report for phpcs check.

Any customization done in your project will be respected in Drone build.
