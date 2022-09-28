
Update Project Documentation
============================

Use docker to update the documentation locally

The Docker image comes with all dependencies pre-installed.

To update the documentation run the following command. This will update
the content inside the folder ‘docs’.

.. code-block::

   docker run --rm -v $(pwd):/data phpdoc/phpdoc run -d ./src -t ./docs

Then push the changes generated.

Other topics
^^^^^^^^^^^^


* `Setting up a project </docs/setting-up-project.md>`_
* `Configuring a project </docs/configuring-project.md>`_
* `Installing the project </docs/installing-project.md>`_
* `Testing the project </docs/testing-project.md>`_
* `Using Docker environment </docs/docker-environment.md>`_
* `Continuous integration </docs/continuous-integration.md>`_
* `Available tasks </docs/available-tasks.md>`_
* `Building assets </docs/building-assets.md>`_
* `Git Hooks </docs/git-hooks.md>`_
* Update Project Documentation
* `Changelog </CHANGELOG.md>`_
