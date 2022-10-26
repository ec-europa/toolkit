
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

   composer create-project ec-europa/subsite --stability=dev <dg-name>-<project-id>-reference dev-release/9.x
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
* `Configuring a project </docs/guide/configuring-project.rst>`_
* `Installing the project </docs/guide/installing-project.rst>`_
* `Testing the project </docs/guide/testing-project.rst>`_
* `Using Docker environment </docs/guide/docker-environment.rst>`_
* `Continuous integration </docs/guide/continuous-integration.rst>`_
* `Available tasks </docs/guide/available-tasks.rst>`_
* `Building assets </docs/guide/building-assets.rst>`_
* `Git Hooks </docs/guide/git-hooks.rst>`_
* `Update Project Documentation </docs/guide/project-documentation.rst>`_
* `Changelog </CHANGELOG.md>`_
