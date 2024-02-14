Building assets
===============

Overview
--------

Toolkit provides a way to build theme assets with Europa Component Library (ECL) and/or Gulp.js.

By default, a config file is included as well as some npm packages in order to:

* Look for Scss files and convert them into Css
* Minify Css and Js
* Merge files into one minimized file
* Validate and fix scss files
* Other options depending on the chosen runner

How to use
----------
Building theme assets (general)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Toolkit will install all packages and create config files (if not exist) on the first run.
Add the chosen runners and packages to your runner.yml.dist file
like shown below:

.. code-block::

  toolkit:
    build:
      npm:
        theme-task-runner: ecl-builder gulp
        packages: '@ecl/builder pikaday moment gulp gulp-concat gulp-sass gulp-clean-css gulp-minify'
        ecl-command: 'styles scripts'

Command to run:

.. code-block::

   docker-compose vendor/bin/run toolkit:build-assets


Edit the config file in order to fit your needs
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
('ecl-builder.config.js' and/or 'gulpfile.js')
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Depending on the chosen runners a config file will be created on the theme root folder.
It's possible to edit this file after creation and run again the command 'toolkit:build-assets'.
If the file does not exist, Toolkit will create it using the default template.
After creation please check the entry and output points for your css, scss and js files.
Make sure that are pointed to the right path.

This will (re)generate the output file(s) defined on the config file(s).


Build theme assets (ecl-builder)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

By default Toolkit compiles the Css and Js files, defined in the configuration file
'ecl-builder.config.js' as entry and destination paths.
The ecl-builder command used for this action is 'styles'. This the default command.

To use other command listed on 'ecl-builder' options an additional parameter can be provided:
'--ecl-command'

.. code-block::

   // Execute an available command from the ecl-builder list - Get help:
   docker-compose exec web ./vendor/bin/run toolkit:build-assets --ecl-command=help


Define 'default-theme'
^^^^^^^^^^^^^^^^^^^

The default theme can be specified in a parameter in the task call:

.. code-block::

  // Command line
  docker-compose vendor/bin/run toolkit:build-assets --default-theme=your-theme

  // File 'runner.yml.dist'
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

To enable auto build of assets you should extend the tasks ``build-dev`` and ``build-dist``. See example below.

.. code-block::

   toolkit:
     project_id: "my-project"
     build:
       dev:
         commands:
         - ...
         - ./vendor/bin/run toolkit:build-assets
       dist:
         commands:
         - ...
         - ./vendor/bin/run toolkit:build-assets


Install additional npm packages
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Additional npm packages can be installed to extend the functionality.
The package version can be added after the package name like shown in the example below:

.. code-block::

   '@ecl/preset-ec@3.13.0'

   or

   'gulp@4.0.1'

To do it add them to the file 'runner.yml.dist':

.. code-block::

   toolkit:
     build:
       npm:
         packages: '``@ecl/preset-ec@3.13.0`` ``gulp@4.0.1`` gulp-sass gulp-concat gulp-clean-css gulp-minify'
