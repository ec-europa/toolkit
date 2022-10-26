
Git hooks
=========

Overview
--------

Toolkit provides a way to activate or deactivate git hooks.

By default, Toolkit provides three hooks:


* pre-commit: Run the PHPcs against the modified files.
* prepare-commit-msg: Check if the commit message mets the conditions.
* pre-push: Run a set of linters and checks.

Configuration
-------------

The default configurations can be found in the runner configurations under ``toolkit.hooks``.

.. code-block:: yaml

   toolkit:
     hooks:
       # A relative path from the project root where the hooks are located.
       dir: 'resources/git/hooks'
       active:
         # Check for modified files and run PHPcs.
         - pre-commit
         # Check if the commit message is properly formatted.
         - prepare-commit-msg
         # Run the PHPcs and linters (configurable).
         - pre-push
       prepare-commit-msg:
         example: 'ABC-123: The commit message.'
         conditions:
           - message: "The commit message must start with the JIRA issue number."
             regex: /^[A-Z]+\-\d+/
           - message: "The JIRA issue number must be followed by a colon and space."
             regex: /^[A-Z]+\-\d+:\ /
           - message: "The subject must start with capital letter."
             regex: /^[A-Z]+\-\d+:\ [A-Z]/
           - message: "The commit message must end with a period."
             regex: /\.$/
       pre-push:
         commands:
           - toolkit:test-phpcs
           - toolkit:lint-yaml
           - toolkit:lint-php
           - toolkit:opts-review

How to use
----------

List available hooks
^^^^^^^^^^^^^^^^^^^^

To list the available commands, execute the following command:

.. code-block::

   > ./vendor/bin/run toolkit:hooks-list
   +------------------------------+------------------+-------------+---------------+
   | Hook                         | Active by config | Hook exists | Modified file |
   +------------------------------+------------------+-------------+---------------+
   | pre-commit (toolkit)         | Yes              | No          | No            |
   | pre-push (toolkit)           | Yes              | No          | No            |
   | prepare-commit-msg (toolkit) | Yes              | No          | No            |
   +------------------------------+------------------+-------------+---------------+

Labels:
``Hook`` - Represents the name of the hook.

``Active by config`` - Whether the hook is active in configuration.

``Hooks exists`` - Whether the hook is enable under ``.git/hooks`` folder.

``Modified file`` - Whether the hook is not the same as in the ``resources/git/hooks`` folder.

Enable all the active hooks
^^^^^^^^^^^^^^^^^^^^^^^^^^^

The command ``toolkit:hooks-enable`` will read the configuration
``toolkit.hooks.active`` and enable these hooks.

Alternatively you can use the option ``hooks`` to enable a set of hooks.

Disable all the active hooks
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The command ``toolkit:hooks-disable`` will read the configuration
``toolkit.hooks.active`` and disable these hooks.

Alternatively you can use the option ``hooks`` to disable a set of hooks.

Delete all the enabled hooks
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The command ``toolkit:hooks-delete-all`` will delete all the enabled hooks
in the ``.git/hooks`` directory.

How it works
------------

Each hook can call directly the command ``toolkit:hooks-run`` with the first
argument representing the hook name, and you can pass up to 3 extra arguments.

Example of the ``pre-commit`` hook (no extra arguments).

.. code-block:: shell

   #!/bin/sh
   ./vendor/bin/run toolkit:hooks-run `basename "$0"`

Example of the ``prepare-commit-msg`` hook (receives two extra arguments).

.. code-block:: shell

   #!/bin/sh
   ./vendor/bin/run toolkit:hooks-run `basename "$0"` $1 $2

The command ``toolkit:hooks-run`` will transform the hook name and look for a
callback in the format ``runHookName``.

Example for hook ``prepare-commit-msg``\ , toolkit will look for a callback
named ``runPrepareCommitMsg()``.

The callback method is responsible to retrieve the arguments
with ``$this->input()->getArguments()``.

For more details check the Commands class
at ``EcEuropa\Toolkit\TaskRunner\Commands\GitHookCommands``

Extending the git hooks
-----------------------

Toolkit allows you to provide your own hooks and callbacks.

To do so, you need to


* Create the hook under your ``resources/git/hooks`` folder.
* Add the hook name to your active hooks under ``toolkit.hooks.active``.
* Create a new class extending the ``GitHookCommands`` class and define the
  ``run`` callback for the hook.

Create the hook
^^^^^^^^^^^^^^^

Add the hook that you want to the ``resources/git/hooks``\ , in this example we will
use the hook ``commit-msg``\ , so we add the file ``resources/git/hooks/commit-msg``.

.. code-block:: shell

   #!/bin/sh
   ./vendor/bin/run toolkit:hooks-run `basename "$0"` $1 $2

Add the hook to the active hooks
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

In your ``runner.yml``\ , extend the Toolkit configuration to add your custom hook.

Do not forget to copy the existing ones (in case you want to use them),
otherwise your configuration will override the default provided by Toolkit.

.. code-block:: yaml

   toolkit:
     hooks:
       active:
         - pre-commit
         - prepare-commit-msg
         - pre-push
         - commit-msg

Create a class extending the GitHookCommands
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Is in this class that you will define the callback for your hook.

Add your class under ``src/TaskRunner/Commands``.

.. code-block:: php

   <?php

   namespace Digit\Qa\TaskRunner\Commands;

   use EcEuropa\Toolkit\TaskRunner\Commands\GitHookCommands;
   use Robo\ResultData;

   class QaGitHookCommands extends GitHookCommands
   {
       public function runCommitMsg()
       {
         $args = $this->input()->getArguments();
         $commit_message = trim(file_get_contents($args['arg1']));
         $this->io()->say("Commit message: $commit_message");
         return ResultData::EXITCODE_OK;
       }
   }

Check if your hooks it active
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

List the available commands, your custom hook should be available.

.. code-block::

   > ./vendor/bin/run toolkit:hooks-list
   +------------------------------+------------------+-------------+---------------+
   | Hook                         | Active by config | Hook exists | Modified file |
   +------------------------------+------------------+-------------+---------------+
   | pre-commit (toolkit)         | Yes              | No          | No            |
   | pre-push (toolkit)           | Yes              | No          | No            |
   | prepare-commit-msg (toolkit) | Yes              | No          | No            |
   | commit-msg (digit-qa)        | Yes              | No          | No            |
   +------------------------------+------------------+-------------+---------------+

Other topics
^^^^^^^^^^^^


* `Setting up a project </docs/guide/setting-up-project.rst>`_
* `Configuring a project </docs/guide/configuring-project.rst>`_
* `Installing the project </docs/guide/installing-project.rst>`_
* `Testing the project </docs/guide/testing-project.rst>`_
* `Using Docker environment </docs/guide/docker-environment.rst>`_
* `Continuous integration </docs/guide/continuous-integration.rst>`_
* `Available tasks </docs/guide/available-tasks.rst>`_
* `Building assets </docs/guide/building-assets.rst>`_
* Git Hooks
* `Update Project Documentation </docs/guide/project-documentation.rst>`_
* `Changelog </CHANGELOG.md>`_
