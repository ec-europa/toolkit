Building assets
===============

Overview
--------

Toolkit provides a way to build theme assets with Gulp.js.

By default, a gulpfile is included and as well some npm packages in order to:

* Look for Scss files and convert them into Css
* Minify Css and Js
* Merge files into one minimized file
* Validate and fix scss files

Command to run:

.. code-block::

   docker-compose vendor/bin/run toolkit:build-assets

How to use
----------

Source files
^^^^^^^^^^^^

The folder structure for the source files should be aligned like this:

* {your-theme-folder}/src/scss
* {your-theme-folder}/src/js

After this task is complete the generated folder '{your-theme}/assets' will look like this:

.. code-block::

   /your-theme
     /assets
       /css
         style.min.css
       /js
         script.min.js

Note: The folder name ``assets`` is the default value provided, it can be override in the 'gulpfile.js'.

Get 'default-theme'
^^^^^^^^^^^^^^^^^^^

If no config files are present in the project, the default theme can be specified in a parameter the parameter in the task call:

.. code-block::

   docker-compose vendor/bin/run toolkit:build-assets --default-theme=your-theme

Define 'custom-code-folder'
^^^^^^^^^^^^^^^^^^^^^^^^^^^

If for some reason your project is running custom code in other folder then ``lib``, it's possible to make it configurable with the following:

.. code-block::

   toolkit:
     build:
       custom-code-folder: 'your_folder'

Build theme assets
^^^^^^^^^^^^^^^^^^

Run the following command:

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:build-assets
   or
   docker-compose exec web ./vendor/bin/run tba

This will (re)generate the /assets folder.

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
         - ./vendor/bin/run toolkit:build-assets
       dist:
         commands:
         - ...
         - ./vendor/bin/run toolkit:build-assets

Extending functionality
-----------------------

Add a custom 'gulpfile.js' file
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

It's possible to use a custom gulpfile on the theme root folder.
If the file do not exists, toolkit will create one using the default template.

Install additional npm packages
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Additional npm packages can be installed to extend the functionality.
In order to do that add them in the file ``runner.yml`` like the example bellow:

.. code-block::

   toolkit:
     build:
       npm:
         packages: gulp gulp-sass gulp-concat gulp-clean-css gulp-minify

npm install --save-dev
~~~~~~~~~~~~~~~~~~~~~~

By default, the npm packages are installed with the option ``--save-dev`` and will appear in the devDependencies.
To override this behavior add in the file ``runner.yml`` the following property:

.. code-block::

   toolkit:
     build:
       npm:
        mode: (leave empty or add '--save-prod')

Validate and fix scss files
---------------------------

Check theme's scss files for issues
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Provides a report of violations.

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:build-assets --validate=check
   or
   docker-compose exec web ./vendor/bin/run toolkit:build-assets --validate

Automatically fix errors
^^^^^^^^^^^^^^^^^^^^^^^^

Automatically fix, where possible, violations reported.

.. code-block::

   docker-compose exec web ./vendor/bin/run toolkit:build-assets --validate=fix
