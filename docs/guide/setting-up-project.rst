Setting up a project
====================

Getting the sourcecode
----------------------

Below we explain the difference between setting up a new project and setting up
an existing project:

New project from scratch
^^^^^^^^^^^^^^^^^^^^^^^^

To instantiate a new project to running as a subsite you only have to execute one command which will perform multiple steps
for you automatically.

.. code-block::

   composer create-project ec-europa/subsite --stability=dev <dg-name>-<project-id>-reference dev-release/10.x
   docker compose up -d
   docker compose exec web vendor/bin/run

From existent project from repository
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

To install locally a project running Toolkit you should run the following commands:

.. code-block::

   git clone git@github.com:ec-europa/<repository-name>.git
   docker compose up -d
   docker compose exec web composer install
   docker compose exec web vendor/bin/run
