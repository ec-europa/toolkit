# Testing the project

<big><table><thead><tr><th nowrap> [Installing the project](./installing-project.md#installing-the-project) </th><th width="100%" align="center"> [User guide](../README.md#user-guide) </th><th nowrap> [Using Composer hooks](./composer-hooks.md#using-composer-hooks) </th></tr></thead></table>

This guide explains how to use the resources provided by toolkit to test your 
project. You can list all resources available with the following command:

<details><p><summary>execute <code>./toolkit/phing help-test</code></summary></p>

```
$ toolkit/phing help-test
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
```
</p></details>

## Behat testing
To run behat tests you can make use of the `test-run-behat` command. This will
re-generate your behat configuration int `tests/behat.yml` and run it on your
current site installation.

<details><p><summary>execute <code>./toolkit/phing test-run-behat</code></summary></p>

```
+ ./toolkit/phing test-run-behat
Buildfile: /test/toolkit/build.xml
 [property] Loading /test/toolkit/vendor/ec-europa/toolkit/includes/phing/build/boot.props
 [property] Loading /test/toolkit/build.project.props
 [property] Loading /test/toolkit/.tmp/build.version.props
     [echo] Global share directory /cache/share available.
     [echo] Temporary directory /test/toolkit/.tmp available.

root > test-behat-setup:

     [copy] Copying 1 file to /test/toolkit/tests

root > test-composer-install:

     [echo] Run 'composer install' in best folder.
 [composer] Executing /usr/bin/php composer.phar install --working-dir=/test/toolkit/tests --no-interaction --no-suggest --ansi
You are running composer with xdebug enabled. This has a major impact on runtime performance. See https://getcomposer.org/xdebug
Loading composer repositories with package information
Installing dependencies (including require-dev) from lock file
  - Installing guzzlehttp/promises (v1.3.1)
    Loading from cache

  - Installing psr/http-message (1.0.1)
    Loading from cache

  - Installing guzzlehttp/psr7 (1.4.2)
    Loading from cache

  - Installing behat/gherkin (v4.4.5)
    Loading from cache

  - Installing symfony/polyfill-mbstring (v1.4.0)
    Loading from cache

  - Installing symfony/dom-crawler (v3.3.6)
    Loading from cache

  - Installing symfony/browser-kit (v3.3.6)
    Loading from cache

  - Installing symfony/css-selector (v3.3.6)
    Loading from cache

  - Installing behat/mink (v1.7.1)
    Loading from cache

  - Installing behat/mink-browserkit-driver (v1.3.2)
    Loading from cache

  - Installing behat/transliterator (v1.2.0)
    Loading from cache

  - Installing symfony/finder (v3.3.6)
    Loading from cache

  - Installing symfony/filesystem (v3.3.6)
    Loading from cache

  - Installing symfony/yaml (v3.3.6)
    Loading from cache

  - Installing symfony/translation (v3.3.6)
    Loading from cache

  - Installing symfony/event-dispatcher (v3.0.9)
    Loading from cache

  - Installing psr/container (1.0.0)
    Loading from cache

  - Installing symfony/dependency-injection (v3.3.6)
    Loading from cache

  - Installing psr/log (1.0.2)
    Loading from cache

  - Installing symfony/debug (v3.3.6)
    Loading from cache

  - Installing symfony/console (v3.3.6)
    Loading from cache

  - Installing symfony/config (v3.3.6)
    Loading from cache

  - Installing symfony/class-loader (v3.3.6)
    Loading from cache

  - Installing behat/behat (v3.1.0)
    Loading from cache

  - Installing bex/behat-extension-driver-locator (1.0.2)
    Loading from cache

  - Installing behat/mink-extension (v2.2)
    Loading from cache

  - Installing bex/behat-screenshot (1.2.6)
    Loading from cache

  - Installing kriswallsmith/buzz (v0.15)
    Loading from cache

  - Installing bex/behat-screenshot-image-driver-img42 (1.0.0)
    Loading from cache

  - Installing sebastian/recursion-context (1.0.5)
    Loading from cache

  - Installing sebastian/exporter (1.2.2)
    Loading from cache

  - Installing sebastian/diff (1.4.3)
    Loading from cache

  - Installing sebastian/comparator (1.2.4)
    Loading from cache

  - Installing bovigo/assert (v1.7.1)
    Loading from cache

  - Installing symfony/process (v3.3.6)
    Loading from cache

  - Installing drupal/drupal-driver (v1.2.1)
    Loading from cache

  - Installing instaclick/php-webdriver (1.4.5)
    Loading from cache

  - Installing behat/mink-selenium2-driver (v1.3.1)
    Loading from cache

  - Installing guzzlehttp/guzzle (6.3.0)
    Loading from cache

  - Installing fabpot/goutte (v3.2.1)
    Loading from cache

  - Installing behat/mink-goutte-driver (v1.2.1)
    Loading from cache

  - Installing drupal/drupal-extension (v3.1.5)
    Loading from cache

  - Installing phing/phing (2.16.0)
    Loading from cache

  - Installing drupol/phingbehattask (1.0.0)
    Loading from cache

  - Installing symfony/routing (v3.0.9)
    Loading from cache

  - Installing symfony/http-foundation (v3.0.9)
    Loading from cache

  - Installing symfony/http-kernel (v3.0.9)
    Loading from cache

  - Installing pimple/pimple (v1.1.1)
    Loading from cache

  - Installing silex/silex (v1.3.6)
    Loading from cache

  - Installing lstrojny/hmmmath (0.5.1)
    Loading from cache

  - Installing symfony/polyfill-util (v1.4.0)
    Loading from cache

  - Installing symfony/polyfill-php56 (v1.4.0)
    Loading from cache

  - Installing nikic/php-parser (v3.1.0)
    Loading from cache

  - Installing jeremeamia/superclosure (2.3.0)
    Loading from cache

  - Installing guzzle/guzzle (v3.8.1)
    Loading from cache

  - Installing internations/http-mock (0.7.8)
    Loading from cache

  - Installing webmozart/assert (1.2.0)
    Loading from cache

  - Installing phpdocumentor/reflection-common (1.0)
    Loading from cache

  - Installing phpdocumentor/type-resolver (0.3.0)
    Loading from cache

  - Installing phpdocumentor/reflection-docblock (3.2.2)
    Loading from cache

  - Installing phpunit/php-token-stream (1.4.11)
    Loading from cache

  - Installing sebastian/version (2.0.1)
    Loading from cache

  - Installing sebastian/resource-operations (1.0.0)
    Loading from cache

  - Installing sebastian/object-enumerator (1.0.0)
    Loading from cache

  - Installing sebastian/global-state (1.1.1)
    Loading from cache

  - Installing sebastian/environment (2.0.0)
    Loading from cache

  - Installing phpunit/php-text-template (1.2.1)
    Loading from cache

  - Installing doctrine/instantiator (1.0.5)
    Loading from cache

  - Installing phpunit/phpunit-mock-objects (3.4.4)
    Loading from cache

  - Installing phpunit/php-timer (1.0.9)
    Loading from cache

  - Installing phpunit/php-file-iterator (1.4.2)
    Loading from cache

  - Installing sebastian/code-unit-reverse-lookup (1.0.1)
    Loading from cache

  - Installing phpunit/php-code-coverage (4.0.8)
    Loading from cache

  - Installing phpspec/prophecy (v1.7.0)
    Loading from cache

  - Installing myclabs/deep-copy (1.6.1)
    Loading from cache

  - Installing phpunit/phpunit (5.6.4)
    Loading from cache

  - Installing rych/random (v0.1.0)
    Loading from cache

Package guzzle/guzzle is abandoned, you should avoid using it. Use guzzlehttp/guzzle instead.
Generating autoload files

root > test-behat-exec:

    [behat] Executing command: /test/toolkit/tests/vendor/behat/behat/bin/behat --config="/test/toolkit/tests/behat.yml" --strict
...............................................

17 scenarios (17 passed)
47 steps (47 passed)
4m1.02s (93.58Mb)

root > test-run-behat:


BUILD FINISHED

Total time: 4 minutes 6.64 seconds
```
</p></details>

### Manual or custom behat testing:

If you wish to manually execute behat after editing the behat.yml for example
you could:

<details><p><summary>execute <code>tests/vendor/behat/behat/bin/behat --config="tests/behat.yml" --strict</code></summary></p>

If you project needs a custom version of behat or other packages you should add
these to the require-dev section of your main composer.json file. Then you can
use any package you need to perform your tests by executing your own binary or
by changing the build property of the behat.bin to your own location:

```shell
behat.bin = ${project.basedir}/vendor/behat/behat/bin/behat
```

```javascript
{
    "name": "ec-europa/subsite",
    "require": {
        "ec-europa/toolkit": "3.*"
    },
    "require-dev": {
        "behat/behat": "~3.1.0@rc",
        "drupal/drupal-extension": "~3.1.0"
    }
    "scripts": {
        "post-install-cmd": "@toolkit-install",
        "post-update-cmd": "@toolkit-install",
        "toolkit-install": "PROJECT=$(pwd) composer run-script toolkit-install -d ./vendor/ec-europa/toolkit"
    }
}
```
</p></details>

Click for command above for more information on how to use your own packages for
testing.

## PHPCS testing
To run phpcs tests you can make use of the `test-run-phpcs` command. This will
re-generate your phpcs configuration int `./phpcs.xml` and run it on your
current codebase.

<details><p><summary>execute <code>./toolkit/phing test-run-phpcs</code></summary></p>

```
$ toolkit/phing test-run-phpcs
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

## PHPCS Compatibility testing
To run phpcs compatibility tests you can make use of the `test-run-phpcs-compatibility`
command. This will run build-dist, re-generate your phpcs configuration int `./phpcs.xml`
and check if your build is compatible with PHP 7.

You can control the way it's run with the following properties:
```
# Compatibility settings.
phpcs.compat.version = 7.0-
phpcs.compat.checkreturn = true
phpcs.compat.skip = false
```

<details><p><summary>execute <code>./toolkit/phing test-run-phpcs-compatibility</code></summary></p>

```

```
</details>

## PHPUnit testing
To run behat tests you can make use of the `test-run-phpunit` command. This will
re-generate your phpunit configuration int `./phpunit.xml` and run it on your
site installation

<details><p><summary>execute <code>./toolkit/phing test-run-phpunit</code></summary></p>

```
Buildfile: ~/toolkit/build.xml
 [property] Loading ~/toolkit/includes/phing/build/boot.props
 [property] Loading ~/toolkit/build.develop.props
 [property] Loading ~/toolkit/build.project.props
 [property] Loading ~/toolkit/.tmp/build.version.props
     [echo] Global share directory /cache/share available.
     [echo] Temporary directory ~/toolkit/.tmp available.

core > test-phpunit-setup:

     [copy] Copying 1 file to ~/toolkit/tests

core > test-composer-install:

     [echo] Run 'composer install' in best folder.
 [composer] Composer binary not found at "composer.phar"
 [composer] Composer binary found at "/usr/local/bin/composer", updating location
 [composer] Executing /usr/bin/php /usr/local/bin/composer install --working-dir=~/toolkit/tests --no-interaction --no-suggest --ansi
Loading composer repositories with package information
Installing dependencies (including require-dev) from lock file
Nothing to install or update
Package guzzle/guzzle is abandoned, you should avoid using it. Use guzzlehttp/guzzle instead.
Generating autoload files

core > test-phpunit-exec:

PHPUnit 5.6.4 by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: 1.31 seconds, Memory: 45.75MB

OK (1 test, 3 assertions)

core > test-run-phpunit:


BUILD FINISHED

Total time: 6.5193 seconds


```
</p></details>
