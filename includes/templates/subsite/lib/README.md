Examples for Developers
--------------------------
This example code is intended to provide some information about how to
use features to control the configuration of your subsite:
- How to enable a new module in your project
- Enable and set the default theme
- How to use `hook_update_N()` properly
- How to configure the subsite through core feature

This is an work in progress, feel free to contribute by let's know
which examples you like to see here.

How to enable a new module in your project?
--------------------------
Sometimes is need to include a new module from d.org in our project, to
do it we need to follow some simple steps:
- Include the module in the file `resources/site.make`
- Provide the necessary to enable it by `hook_update_N()`, see how in the
  example code;
- Execute `drush updb`

Enable and set the default theme?
--------------------------
The theme must be enabled by code, please refer the example code to
see how you can do it.

How to use `hook_update_N()` properly?
--------------------------
This is one of most used hooks in our projects, in every delivery you
should provide a `hook_update_N` in order to execute the modifications
your need do in your project.

Keep in mind that the updates will no run in the install phase and that
should be properly documented and numbered, refer the code in this folder
to see some examples.

How to configure the subsite through core feature?
--------------------------
The configuration of your subsite should reside in code, setup your website
through the UI is not recommended. Refer to code so see some examples how
to configure some settings in your code.