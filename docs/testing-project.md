# Testing the project

<big><table><thead><tr><th nowrap> [Installing a project](./installing-project.md#installing-the-project) </th><th width="100%" align="center"> [User guide](../README.md#user-guide) </th><th nowrap> [Todo](./todo) </th></tr></thead></table>

This guide explains how to use the resources provided by toolkit to test your 
project. You can list all resources available by typing the command <code>toolkit/phing help-test</code>.

<p>Out of the box, toolkit allow you to test your code by running behat, phpcs or
qa-automation rules. Found bellow the list of main targets you can use:</p>


```
$ toolkit/phing help-test
...
+--------------------+------------+-------------------------------------------------------------------------------+
| Target name        | Visibility | Description                                                                   |
+--------------------+------------+-------------------------------------------------------------------------------+
+--------------------+------------+-------------------------------------------------------------------------------+
| test                                                                                                            |
+--------------------+------------+-------------------------------------------------------------------------------+
| test-run-phpcs     | visible    | Refresh configuration and run phpcs review.                                   |
| test-run-qa        | visible    | Refresh configuration and run qa review.                                      |
| build-project-test | hidden     |                                                                               |
| test-qa-exec       | visible    |                                                                               |
+--------------------+------------+-------------------------------------------------------------------------------+
...
```

## Coding Standards
<p>To run the testing you should execute the command <code>./toolkit/phing test-run-phpcs</code>
in the root path of your project.</p>

<details>
    <summary>See here  a simple example running phpcs tests</summary>
    &nbsp;

```
$ toolkit/phing test-run-phpcs lib/
Buildfile: ~/toolkit/build.xml
 [property] Loading  ~/toolkit/vendor/ec-europa/toolkit/includes/phing/build/boot.props
 [property] Loading  ~/toolkit/build.develop.props
 [property] Loading  ~/toolkit/build.project.props
 [property] Loading  ~/toolkit/.tmp/build.version.props
     [echo] Global share directory /tmp/cache/share available.
     [echo] Temporary directory  ~/toolkit/coolsite/.tmp available.

root > test-phpcs-setup-prepush:

     [echo] Enabling git pre-push hook.
   [relsym] Link exists:  ~/toolkit/resources/git/hooks/pre-push/phpcs

root > test-phpcs-setup:

   [delete] Deleting:  ~/toolkit/phpcs.xml
   [delete] Deleting:  ~/toolkit/vendor/ec-europa/toolkit/vendor/squizlabs/php_codesniffer/CodeSniffer.conf
   [config] Updating:  ~/toolkit/phpcs.xml
   [config] Updating:  ~/toolkit/vendor/ec-europa/toolkit/vendor/squizlabs/php_codesniffer/CodeSniffer.conf

root > test-phpcs-exec:



PHP CODE SNIFFER REPORT SUMMARY
----------------------------------------------------------------------
FILE                                                  ERRORS  WARNINGS
----------------------------------------------------------------------
...lsite/lib/themes/example_theme/example_theme.info  2       0
...dules/features/myproject_core/myproject_core.info  3       0
...es/features/myproject_core/myproject_core.install  1       6
...modules/custom/example_module/example_module.info  2       0
----------------------------------------------------------------------
A TOTAL OF 8 ERRORS AND 6 WARNINGS WERE FOUND IN 4 FILES
----------------------------------------------------------------------
PHPCBF CAN FIX 1 OF THESE SNIFF VIOLATIONS AUTOMATICALLY
----------------------------------------------------------------------

Time: 256ms; Memory: 9.5Mb


BUILD FAILED
```
</details>

## Behat testing
<p>To run behat tests you should execute the command <code>./toolkit/phing test-run-behat</code>
in the root path of your project. The test and configuration are by default in the folder /tests.</p>

## QA Automation
<p>To run QA automated tests you should execute the command <code>./toolkit/phing test-run-qa</code>
in the root path of your project.</p>
