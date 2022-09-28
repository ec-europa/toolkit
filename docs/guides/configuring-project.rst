Configuring a project
=====================

Environment configuration
~~~~~~~~~~~~~~~~~~~~~~~~~

By default, Docker Compose reads two files, a ``docker-compose.yml`` and
an optional ``docker-compose.override.yml`` file. By convention, the
``docker-compose.yml`` contains your base configuration and it’s
provided by default. The override file, as its name implies, can contain
configuration overrides for existing services or entirely new services.
If a service is defined in both files, Docker Compose merges the
configurations.

Find more information on Docker Compose extension mechanism on `the
official Docker Compose
documentation <https://docs.docker.com/compose/extends/>`__.

The following configuration parameters are provided as environment
variables in the `/.env <.env>`__ file:

+---------------------------+------------------------------------------+
| Name                      | Description                              |
+===========================+==========================================+
| ``DRUPAL_DATABASE_NAME``  | Database name                            |
+---------------------------+------------------------------------------+
| ``D                       | Database username                        |
| RUPAL_DATABASE_USERNAME`` |                                          |
+---------------------------+------------------------------------------+
| ``D                       | Database password                        |
| RUPAL_DATABASE_PASSWORD`` |                                          |
+---------------------------+------------------------------------------+
| `                         | Database prefix                          |
| `DRUPAL_DATABASE_PREFIX`` |                                          |
+---------------------------+------------------------------------------+
| ``DRUPAL_DATABASE_HOST``  | Database host                            |
+---------------------------+------------------------------------------+
| ``DRUPAL_DATABASE_PORT``  | Database port                            |
+---------------------------+------------------------------------------+
| `                         | SPARQL hostname                          |
| `DRUPAL_SPARQL_HOSTNAME`` |                                          |
+---------------------------+------------------------------------------+
| ``DRUPAL_SPARQL_PORT``    | SPARQL port                              |
+---------------------------+------------------------------------------+
| ``DRUPAL_CAS_HOSTNAME``   | EULogin hostname, use                    |
|                           | ``ecas.ec.europa.eu`` for production     |
+---------------------------+------------------------------------------+
| ``DRUPAL_CAS_PORT``       | EULogin port, use ``443`` for production |
+---------------------------+------------------------------------------+
| ``                        | Drupal admin account, defaults to        |
| DRUPAL_ACCOUNT_USERNAME`` | ``admin`` if empty                       |
+---------------------------+------------------------------------------+
| ``                        | Drupal admin password, defaults to       |
| DRUPAL_ACCOUNT_PASSWORD`` | random string if empty                   |
+---------------------------+------------------------------------------+
| ``DRUPAL_BASE_URL``       | Drupal site base URL, used to setup      |
|                           | Behat tests                              |
+---------------------------+------------------------------------------+
| ``DRUPAL_WEB              | Drupal site unique identifier            |
| TOOLS_ANALYTICS_SITE_ID`` |                                          |
+---------------------------+------------------------------------------+
| ``DRUPAL_WEBTO            | The domain + root path without protocol. |
| OLS_ANALYTICS_SITE_PATH`` |                                          |
+---------------------------+------------------------------------------+

Environment variables will be loaded by Docker Compose when running
``docker-compose up -d``.

Subsite configuration
~~~~~~~~~~~~~~~~~~~~~

By default, subsite configuration go into file ``runner.yml.dist``, see
bellow an example.

::

   drupal:
     root: "web"
     base_url: ${env.DRUPAL_BASE_URL}
     site:
       profile: "standard"
       name: "Drupal website configuration goes here!"
       generate_db_url: false
     account:
       name: ${env.DRUPAL_ACCOUNT_USERNAME}
       password: ${env.DRUPAL_ACCOUNT_PASSWORD}

   toolkit:
     project_id: 'PROJECTID'

   selenium:
     host: "http://selenium"
     port: "4444"
     browser: "chrome"

Clone configuration
~~~~~~~~~~~~~~~~~~~

Toolkit will load the credentials necessary to clone your subsite from
your environment, so make sure you have in your env the following
variables:

-  ASDA_USER
-  ASDA_PASSWORD
