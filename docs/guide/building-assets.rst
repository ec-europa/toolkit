Building assets
===============

Overview
--------

Toolkit provides a way to build theme assets with Gulp.js, Grunt.js, or/and Europa Component Library (ECL).

By default, a config file is included and as well some npm packages in order to:

* Look for Scss files and convert them into Css
* Minify Css and Js
* Merge files into one minimized file
* Validate and fix scss files
* Other options depending on the chosen runner

How to use
----------
Installation
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Before execution, the installation needs to run.
Add the chosen runners and packages to your runner.yml.dist
like shown below:

.. code-block::

  toolkit:
    build:
      npm:
        theme-task-runner: ecl-builder
        packages: @ecl/builder pikaday moment

Command to run:

.. code-block::

   docker-compose vendor/bin/run toolkit:build-assets

Edit the config file in order to fit your needs
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
('gulpfile.js', 'Gruntfile.js' or/and 'ecl-builder.config.js')
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Depending on the chosen runners a config file(s) will be created on theme root folder.
It's possible to edit this file after creation or replace it by another (with same name).
If the file do not exists, toolkit will create it using the default template.
After creation please check the entry points and output for your css, scss and js files.
Make sure that are pointed to the right path.

Execution - Build theme assets
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Run the following command:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:build-assets --execute=[RUNNER]

This will (re)generate the output file(s) defined on the config file(s).

Execution - Ecl command
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

In case the chosen runner  is 'ecl-builder' and additional parameter should be provided:
'--ecl-command'

.. code-block::

   Check all the available commands:
   docker-compose exec web ./vendor/bin/run toolkit:build-assets --execute=ecl-builder

   Execute an available command from the ecl-builder list:
   docker-compose exec web ./vendor/bin/run toolkit:build-assets --execute=ecl-builder --ecl-command=styles


Define 'default-theme'
^^^^^^^^^^^^^^^^^^^

The default theme can be specified in a parameter the parameter in the task call:

.. code-block::

   docker-compose vendor/bin/run toolkit:build-assets --default-theme=your-theme

  toolkit:
    build:
      npm:
        theme-task-runner: ecl-builder
        packages: @ecl/builder pikaday moment

Define 'custom-code-folder'
^^^^^^^^^^^^^^^^^^^^^^^^^^^

If for some reason your project is running custom code in other folder then ``lib``, it's possible to make it configurable with the following:

.. code-block::

   toolkit:
     build:
       custom-code-folder: 'your_folder'


Enable build assets during CI
-----------------------------

To enable auto build of assets you should extend the tasks ``build-dev`` and ``build-dist``. See example bellow.

.. code-block::

   toolkit:
     project_id: "my-project"
     build:
       dev:
         commands:
         - ...
         - ./vendor/bin/run toolkit:build-assets --execute=[RUNNER]
       dist:
         commands:
         - ...
         - ./vendor/bin/run toolkit:build-assets --execute=[RUNNER]


Install additional npm packages
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Additional npm packages can be installed to extend the functionality.
The package version can be added after the package name followed by '@':

@ecl/preset-ec@3.13.0

or

grunt@1.6.1"


In order to do that add them in the file ``runner.yml.dist`` like the example bellow:


.. code-block::

   toolkit:
     build:
       npm:
         packages: gulp gulp-sass gulp-concat gulp-clean-css gulp-minify @ecl/preset-ec@3.13.0 grunt@1.6.1
