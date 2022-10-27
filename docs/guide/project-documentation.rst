
Update Project Documentation
============================

Use docker to update the documentation locally

The Docker image comes with all dependencies pre-installed.

To update the documentation run the following command. This will update
the content inside the folder ‘docs’.

.. code-block::

   docker run --rm -v $(pwd):/data phpdoc/phpdoc run

Then push the changes generated.

Other topics
^^^^^^^^^^^^


* `Setting up a project </docs/guide/setting-up-project.rst>`_
* `Configuring a project </docs/guide/configuring-project.rst>`_
* `Installing the project </docs/guide/installing-project.rst>`_
* `Testing the project </docs/guide/testing-project.rst>`_
* `Using Docker environment </docs/guide/docker-environment.rst>`_
* `Continuous integration </docs/guide/continuous-integration.rst>`_
* `Building assets </docs/guide/building-assets.rst>`_
* `Commands </docs/guide/commands.rst>`_
* `Git Hooks </docs/guide/git-hooks.rst>`_
* Update Project Documentation
* `Changelog </CHANGELOG.md>`_
