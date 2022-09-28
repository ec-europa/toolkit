
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

   composer create-project ec-europa/subsite --stability=dev <dg-name>-<project-id>-reference dev-release/8.x
   docker-compose up -d
   docker-compose exec web vendor/bin/run

From existent project from repository
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

To install locally a project running Toolkit 4 you should run the following commands:

.. code-block::

   git clone git@github.com:ec-europa/<repository-name>.git
   docker-compose up -d
   docker-compose exec web composer install
   docker-compose exec web vendor/bin/run

Other topics
^^^^^^^^^^^^


* Setting up a project
* `Configuring a project </docs/configuring-project.md>`_
* `Installing the project </docs/installing-project.md>`_
* `Testing the project </docs/testing-project.md>`_
* `Using Docker environment </docs/docker-environment.md>`_
* `Continuous integration </docs/continuous-integration.md>`_
* `Available tasks </docs/available-tasks.md>`_
* `Building assets </docs/building-assets.md>`_
* `Git Hooks </docs/git-hooks.md>`_
* `Update Project Documentation </docs/project-documentation.md>`_
* `Changelog </CHANGELOG.md>`_
