Installing a project
====================

Before install
^^^^^^^^^^^^^^

To start, run:

.. code-block::

   docker-compose up

It's advised to not daemonize docker-compose, so you can turn it off (CTRL+C) quickly when you're
done working. However, if you'd like to daemonize it, you have to add the flag ``-d``:

.. code-block::

   docker-compose up -d

Build the site by running:

.. code-block::

   docker-compose exec web composer install

Using default configuration, the development site files should be available in the web directory
and the development site should be available at: http://localhost:8080/web.

Clean installation
^^^^^^^^^^^^^^^^^^

To setup the site in local development mode and install it run:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:build-dev
   docker-compose exec web ./vendor/bin/run toolkit:install-clean

To install the site from existing configuration run:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:build-dev
   docker-compose exec web ./vendor/bin/run toolkit:install-clean --existing-config

Commands to run before/after clean installation
"""""""""""""""""""""""""""""""""""""""""""""""

Additional commands can be run before and/or after installing the clean site.
Such commands should be defined in the configuration file ``runner.yml``:

.. code-block:: yaml

   toolkit:
     install:
       clean:
         commands:
           before:
             - task: exec
               command: ls -la
             - ...
           after:
             - task: exec
               command: whoami
             - ...

Clone installation
^^^^^^^^^^^^^^^^^^

To setup the site using clone mode run:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:build-dev
   docker-compose exec web ./vendor/bin/run toolkit:download-dump
   docker-compose exec web ./vendor/bin/run toolkit:install-clone

Toolkit will load the credentials necessary to download the sanitized database dump for your
project, so make sure you have the following variables in your environment:

.. code-block::

   # For nextcloud ASDA
   - NEXTCLOUD_USER
   - NEXTCLOUD_PASS

   # For default ASDA (deprecated)
   - ASDA_USER
   - ASDA_PASSWORD

Commands part of clone installation
"""""""""""""""""""""""""""""""""""

When running the command toolkit:install-clone it will run the following sequence of
commands after the database import:

.. code-block::

   ./vendor/bin/drush state:set system.maintenance_mode 1 --input-format=integer -y
   ./vendor/bin/drush updatedb -y
   ./vendor/bin/run toolkit:import-config
   ./vendor/bin/drush state:set system.maintenance_mode 0 --input-format=integer -y
   ./vendor/bin/drush cache:rebuild

These commands simulate the automated deployment that Drone provides through its pipeline. You can
alter these commands by providing a file named **.opts.yml** in the root of your project folder. For
more detailed information on the contents of this **.opts.yml** file please refer to this
page: https://webgate.ec.europa.eu/fpfis/wikis/display/MULTISITE/NE+Pipelines#NEPipelines-DeploymentOverrides .

Commands to run before/after clone installation
"""""""""""""""""""""""""""""""""""""""""""""""

Additional commands can be run before and/or after installing the cloned site.
Such commands should be defined in the configuration file ``runner.yml``:

.. code-block:: yaml

   toolkit:
     install:
       clone:
         commands:
           before:
             - task: exec
               command: ls -la
             - ...
           after:
             - task: exec
               command: whoami
             - ...
