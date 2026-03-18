Configuring a project
=====================

Environment configuration
^^^^^^^^^^^^^^^^^^^^^^^^^

By default, Docker Compose reads two files, a ``docker-compose.yml`` and an optional ``docker-compose.override.yml`` file.
By convention, the ``docker-compose.yml`` contains your base configuration and it's provided by default.
The override file, as its name implies, can contain configuration overrides for existing services or entirely new
services.
If a service is defined in both files, Docker Compose merges the configurations.

Find more information on Docker Compose extension mechanism on
`the official Docker Compose documentation <https://docs.docker.com/compose/extends/>`_.

The following configuration parameters are provided as environment variables in the ``./.env`` file:

+-----------------------------------+----------------------------------------------------------+
|Name                               |Description                                               |
+===================================+==========================================================+
|DRUPAL_DATABASE_NAME               |Database name                                             |
+-----------------------------------+----------------------------------------------------------+
|DRUPAL_DATABASE_USERNAME           |Database username                                         |
+-----------------------------------+----------------------------------------------------------+
|DRUPAL_DATABASE_PASSWORD           |Database password                                         |
+-----------------------------------+----------------------------------------------------------+
|DRUPAL_DATABASE_PREFIX             |Database prefix                                           |
+-----------------------------------+----------------------------------------------------------+
|DRUPAL_DATABASE_HOST               |Database host                                             |
+-----------------------------------+----------------------------------------------------------+
|DRUPAL_DATABASE_PORT               |Database port                                             |
+-----------------------------------+----------------------------------------------------------+
|DRUPAL_SPARQL_HOSTNAME             |SPARQL hostname                                           |
+-----------------------------------+----------------------------------------------------------+
|DRUPAL_SPARQL_PORT                 |SPARQL port                                               |
+-----------------------------------+----------------------------------------------------------+
|DRUPAL_CAS_HOSTNAME                |EULogin hostname, use ``ecas.ec.europa.eu`` for production|
+-----------------------------------+----------------------------------------------------------+
|DRUPAL_CAS_PORT                    |EULogin port, use ``443`` for production                  |
+-----------------------------------+----------------------------------------------------------+
|DRUPAL_ACCOUNT_USERNAME            |Drupal admin account, defaults to ``admin`` if empty      |
+-----------------------------------+----------------------------------------------------------+
|DRUPAL_ACCOUNT_PASSWORD            |Drupal admin password, defaults to random string if empty |
+-----------------------------------+----------------------------------------------------------+
|DRUPAL_BASE_URL                    |Drupal site base URL, used to setup Behat tests           |
+-----------------------------------+----------------------------------------------------------+
|DRUPAL_WEBTOOLS_ANALYTICS_SITE_ID  | Drupal site unique identifier                            |
+-----------------------------------+----------------------------------------------------------+
|DRUPAL_WEBTOOLS_ANALYTICS_SITE_PATH|The domain + root path without protocol.                  |
+-----------------------------------+----------------------------------------------------------+

Environment variables will be loaded by Docker Compose when running ``docker-compose up -d``.

Toolkit configuration
^^^^^^^^^^^^^^^^^^^^^

Toolkit uses the `consolidation/annotated-command <https://github.com/consolidation/annotated-command#hooks>`_ and
`Robo <https://robo.li/>`_, make sure to read the documentation.

The configurations are split into multiple files under the ``config`` directory and they are loaded
in the following order:

- ``config/default.yml``
- All files in ``config/runner``
- All commands options defined in the Command files with the method ``getConfigurationFile()``

Because the configurations are merged, if two different config files provides the same key, they will be merged.
For example, a configuration providing an array with 3 elements, a project would not be able to override and
provide only one element. To avoid this, Toolkit has a set of defined configurations that will behave in a
different way allowing projects to completely override the config.
These overriding configurations are located in the ``config/default.yml`` file under ``overrides`` key.


Project configuration
^^^^^^^^^^^^^^^^^^^^^

A project inherit the same configurations as Toolkit (described above).

To override the default configurations, projects can provide the configurations with the ``runner.yml.dist`` file,
or/and under the ``config/runner`` directory (by default). This directory can be changed (in the ``runner.yml.dist``
only) by specifying a custom path in the config key ``runner.config_dir``.
Local or development configurations should use the ``runner.yml`` file, this file should not be committed and
will be loaded as last configuration.

The configurations are loaded in the following order:

- ``runner.yml.dist``
- ``config/runner`` directory (or other defined in ``runner.yml.dist``)
- ``runner.yml``

The following examples describes how to use a single or multiple files to have the same configuration.

**Example using a single file:**

.. code-block::

    # runner.yml.dist
    drupal:
       root: 'web'
    toolkit:
       project_id: 'site-id'

**Example using multiple files under** ``config/runner`` **directory:**

.. code-block::

    # config/runner/drupal.yml
    drupal:
       root: 'web'

    # config/runner/toolkit.yml
    toolkit:
       project_id: 'site-id'

**Example using multiple files under** ``config/custom`` **directory:**

.. code-block::

    # runner.yml.dist
    runner:
        config_dir: config/custom

    # config/custom/config.yml
    drupal:
       root: 'web'
    toolkit:
       project_id: 'site-id'

Runtime configuration
^^^^^^^^^^^^^^^^^^^^^

There are multiple ways to provide runtime configurations.

**Override a configuration value in runtime**

.. code-block::

    /** @hook pre-command-event * */
    public function hook() {
      // Load configuration.
      $config = $this->getConfig();
      // Override a config value.
      $config->set('drupal.site.name', 'Test website');
      // Import newly built configuration.
      $this->config->replace($config->export());
    }

**Override a specific command option**

.. code-block::

    /** @hook init toolkit:test-behat */
    public function hook(InputInterface $input, AnnotationData $annotationData) {
      $input->setOption('from', 'behat.yml.example');
    }

**Override a command option for all commands that has a specific option**

.. code-block::

    /** @hook init * */
    public function hook(InputInterface $input, AnnotationData $annotationData) {
      if ($input->hasOption('from')) {
        $input->setOption('from', 'behat.yml.example');
      }
    }
