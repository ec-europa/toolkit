# Setting up a project

<big><table><thead><tr><th nowrap> [NextEuropa Toolkit](../README.md#user-guide) </th><th width="100%" align="center"> [Home](../README.md) </th><th nowrap> [Configuring a project](./configuring-project.md#configuring-a-project) </th></tr></thead></table>

## Getting the sourcecode

Before attempting to get the source code of a project please make sure you arre
familiar with the composer create-project command:
https://getcomposer.org/doc/03-cli.md#create-project

### New project
To instatiate a new project that will be running as a subsite on the NextEuropa
platform you only have to execute one command which will perform multiple steps
for you automatically.

<details><summary><code>composer create-project ec-europa/subsite project-id ~3.0.0 --no-interaction</code></summary><p>
<blockquote>
<details><p><summary>1. Clone the subsite repository</summary>

```
Installing ec-europa/subsite (dev-master 445139d17feb18d93411ff369b09ff172956cde5)
  - Installing ec-europa/subsite (dev-master master)
    Cloning master

Created project in project-id
```
</p></details>
<details><p><summary>2. Install the dependencies</summary>

```
Loading composer repositories with package information
Updating dependencies (including require-dev)
  - Installing ec-europa/toolkit (dev-develop 9d7d1c6)
    Cloning 9d7d1c643c106e0c1d8e41166c9f6d3038162dcb

Writing lock file
Generating autoload files
```
</p></details>
<details><p><summary>3. Install the toolkit</summary>

```
> PROJECT=$(pwd) composer run-script toolkit-install -d ./vendor/ec-europa/toolkit
> PROJECT=${PROJECT} composer install --working-dir=includes/composer --ignore-platform-reqs --no-suggest --ansi
> scripts/phingcalls.sh pre-install
Phing unavailable:
No composer hooks will be executed for pre-install.
Loading composer repositories with package information
Installing dependencies (including require-dev) from lock file
  - Installing cweagans/composer-patches (dev-checkpatched 5b63ccc)
    Cloning 5b63ccc68eee83c2b382297074e1b4836d090a16

Gathering patches for root package.
Gathering patches for dependencies. This might take a minute.
  - Installing symfony/yaml (v3.3.9)
    Loading from cache

  - Installing phing/phing (2.16.0)
    Loading from cache

  - Applying patches for phing/phing
    patches/phing-customize-colors.patch (Customize terminal colors)
    patches/phing-xterm-support.patch (Support xterm autocolor)
    patches/phing-composer-task_automatic-path.patch (https://github.com/phingofficial/phing/pull/701)
    patches/phing-hidden-input.patch (https://github.com/phingofficial/phing/issues/553)

  - Installing symfony/polyfill-mbstring (v1.5.0)
    Loading from cache

  - Installing symfony/var-dumper (v3.3.9)
    Loading from cache

  - Installing psr/log (1.0.2)
    Loading from cache

  - Installing symfony/debug (v3.3.9)
    Loading from cache

  - Installing symfony/console (v3.3.9)
    Loading from cache

  - Installing nikic/php-parser (v3.1.1)
    Loading from cache

  - Installing jakub-onderka/php-console-color (0.1)
    Loading from cache

  - Installing jakub-onderka/php-console-highlighter (v0.3.2)
    Loading from cache

  - Installing dnoegel/php-xdg-base-dir (0.1)
    Loading from cache

  - Installing psy/psysh (v0.8.11)
    Loading from cache

  - Installing pear/console_table (v1.3.0)
    Loading from cache

  - Installing drush/drush (8.0.5)
    Loading from cache
  - Applying patches for drush/drush
    patches/drush-force-color.patch (https://github.com/drush-ops/drush/pull/2215)


  - Installing drupal/phingdrushtask (1.1.1)
    Cloning 6737f3d6c28c6c830f58d5fa09327b65acbda0a9
  - Applying patches for drupal/phingdrushtask
    patches/phingdrushtask-color-support.patch (Support forcing of color with drush)


  - Installing drupol/phingbehattask (1.0.0)
    Loading from cache

  - Installing guzzlehttp/promises (v1.3.1)
    Loading from cache

  - Installing psr/http-message (1.0.1)
    Loading from cache

  - Installing guzzlehttp/psr7 (1.4.2)
    Loading from cache

  - Installing symfony/event-dispatcher (v3.3.9)
    Loading from cache

  - Installing guzzle/guzzle (v3.8.1)
    Loading from cache

  - Installing knplabs/github-api (1.3.1)
    Loading from cache

  - Installing kasperg/phing-github (dev-feature/import 167c7ca)
    Cloning 167c7ca98fa5384f94be08a168a14d2c6a9fa9b6

  - Installing pear/pear_exception (v1.0.0)
    Loading from cache

  - Installing pear/console_getopt (v1.4.1)
    Loading from cache

  - Installing pear/pear-core-minimal (v1.10.3)
    Loading from cache

  - Installing pear/archive_tar (1.4.3)
    Loading from cache

  - Installing pear/versioncontrol_git (dev-master f074b9e)
    Cloning f074b9e7805197cb1019e05953960dde35ef3e31

  - Installing pfrenssen/phpcs-pre-push (1.0)
    Cloning master

  - Installing squizlabs/php_codesniffer (2.9.1)
    Loading from cache

  - Installing wimg/php-compatibility (7.0.8)
    Loading from cache

  - Installing behat/gherkin (v4.5.1)
    Loading from cache

  - Installing symfony/dom-crawler (v3.3.9)
    Loading from cache

  - Installing symfony/browser-kit (v3.3.9)
    Loading from cache

  - Installing symfony/css-selector (v3.3.9)
    Loading from cache

  - Installing behat/mink (v1.7.1)
    Loading from cache

  - Installing behat/mink-browserkit-driver (v1.3.2)
    Loading from cache

  - Installing behat/transliterator (v1.2.0)
    Loading from cache

  - Installing symfony/process (v3.3.9)
    Loading from cache

  - Installing psr/container (1.0.0)
    Loading from cache

  - Installing symfony/dependency-injection (v3.3.9)
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

  - Installing symfony/filesystem (v3.3.9)
    Loading from cache

  - Installing symfony/config (v3.3.9)
    Loading from cache

  - Installing symfony/translation (v3.3.9)
    Loading from cache

  - Installing symfony/class-loader (v3.3.9)
    Loading from cache

  - Installing behat/behat (v3.1.0)
    Loading from cache

  - Installing behat/mink-extension (v2.2)
    Loading from cache

  - Installing drupal/drupal-extension (v3.1.5)
    Loading from cache

  - Installing symfony/finder (v3.3.9)
    Loading from cache

  - Installing drupal/coder (8.2.12)
    Cloning 984c54a7b1e8f27ff1c32348df69712afd86b17f

  - Installing cpliakas/git-wrapper (dev-master 141d53f)
    Cloning 141d53fc80707666bc453140e3cd7cba8ecd2dee

  - Installing ec-europa/qa-automation (dev-release/3.0 fbec58c)
    Cloning fbec58cfaf143c4e9ebb2de37099df2ca4525558

Package guzzle/guzzle is abandoned, you should avoid using it. Use guzzlehttp/guzzle instead.
Generating autoload files
```
</p></details>
<details><summary>4. Generate the project structure</summary><p>

```
> scripts/phingcalls.sh post-install
Buildfile: /home/user/github/ec-europa/project-id/vendor/ec-europa/toolkit/build.xml
 [property] Loading /home/user/github/ec-europa/project-id/vendor/ec-europa/toolkit/includes/phing/build/boot.props
     [echo] Global share directory /cache/share available.
     [echo] Temporary directory /home/user/github/ec-europa/project-id/.tmp available.

core > toolkit-binary-link:

     [echo] Provide project with starterkit binary at root level.
   [relsym] Linking: /home/user/github/ec-europa/project-id/toolkit to vendor/ec-europa/toolkit/bin

core > toolkit-structure-generate:


core > toolkit-templates-copy:

     [echo] Ensuring the presence of build.xml and Jenkinsfile.
     [copy] Copying 1 file to /home/user/github/ec-europa/project-id
     [copy] Created 16 empty directories in /home/user/github/ec-europa/project-id
     [copy] Copying 26 files to /home/user/github/ec-europa/project-id
     [echo] Project structure for subsite created.

core > build-toolkit:

     [echo] Toolkit successfully initialized.

BUILD FINISHED

Total time: 1.1604 second
```
</p></details>
<details><summary>5. Remove the VCS files</summary><p>

```
Do you want to remove the existing VCS (.git, .svn..) history? [Y,n]? y
```
</p></details>
</blockquote>
</p></details>

### Existing project

To instantiate an existing project with the compser create-project command
you need to simulate the package registry locally. After that composer will
target the repository you defined in the package definition.

<details><summary><code>nano ~/.composer/config.json</code></summary><p>

```json
{
  "repositories": [
    {
      "type": "package",
      "package": {
        "name": "ec-europa/<project-id>-dev",
        "version": "dev-master",
        "source": {
          "type" : "git",
          "url" : "https://github.com/<owner-name>/<project-id>-dev.git",
          "reference" : "master"
        }
      }
    }
  ],
}

```
</p></details>

<details><summary><code>composer create-project &#x3C;owner-name&#x3E;/&#x3C;project-id&#x3E;-dev project-id dev-master --keep-vcs --no-interaction</code></summary><p>

<blockquote>
<details><p><summary>1. Clone the subsite repository</summary>

```
Installing ec-europa/subsite (dev-master 445139d17feb18d93411ff369b09ff172956cde5)
  - Installing ec-europa/subsite (dev-master master)
    Cloning master

Created project in project-id
```
</p></details>
<details><p><summary>2. Install the dependencies</summary>

```
Loading composer repositories with package information
Updating dependencies (including require-dev)
  - Installing ec-europa/toolkit (dev-develop 9d7d1c6)
    Cloning 9d7d1c643c106e0c1d8e41166c9f6d3038162dcb

Writing lock file
Generating autoload files
```
</p></details>
<details><p><summary>3. Install the toolkit</summary>

```
> PROJECT=$(pwd) composer run-script toolkit-install -d ./vendor/ec-europa/toolkit
> PROJECT=${PROJECT} composer install --working-dir=includes/composer --ignore-platform-reqs --no-suggest --ansi
> scripts/phingcalls.sh pre-install
Phing unavailable:
No composer hooks will be executed for pre-install.
Loading composer repositories with package information
Installing dependencies (including require-dev) from lock file
  - Installing cweagans/composer-patches (dev-checkpatched 5b63ccc)
    Cloning 5b63ccc68eee83c2b382297074e1b4836d090a16

Gathering patches for root package.
Gathering patches for dependencies. This might take a minute.
  - Installing symfony/yaml (v3.3.9)
    Loading from cache

  - Installing phing/phing (2.16.0)
    Loading from cache

  - Applying patches for phing/phing
    patches/phing-customize-colors.patch (Customize terminal colors)
    patches/phing-xterm-support.patch (Support xterm autocolor)
    patches/phing-composer-task_automatic-path.patch (https://github.com/phingofficial/phing/pull/701)
    patches/phing-hidden-input.patch (https://github.com/phingofficial/phing/issues/553)

  - Installing symfony/polyfill-mbstring (v1.5.0)
    Loading from cache

  - Installing symfony/var-dumper (v3.3.9)
    Loading from cache

  - Installing psr/log (1.0.2)
    Loading from cache

  - Installing symfony/debug (v3.3.9)
    Loading from cache

  - Installing symfony/console (v3.3.9)
    Loading from cache

  - Installing nikic/php-parser (v3.1.1)
    Loading from cache

  - Installing jakub-onderka/php-console-color (0.1)
    Loading from cache

  - Installing jakub-onderka/php-console-highlighter (v0.3.2)
    Loading from cache

  - Installing dnoegel/php-xdg-base-dir (0.1)
    Loading from cache

  - Installing psy/psysh (v0.8.11)
    Loading from cache

  - Installing pear/console_table (v1.3.0)
    Loading from cache

  - Installing drush/drush (8.0.5)
    Loading from cache
  - Applying patches for drush/drush
    patches/drush-force-color.patch (https://github.com/drush-ops/drush/pull/2215)


  - Installing drupal/phingdrushtask (1.1.1)
    Cloning 6737f3d6c28c6c830f58d5fa09327b65acbda0a9
  - Applying patches for drupal/phingdrushtask
    patches/phingdrushtask-color-support.patch (Support forcing of color with drush)


  - Installing drupol/phingbehattask (1.0.0)
    Loading from cache

  - Installing guzzlehttp/promises (v1.3.1)
    Loading from cache

  - Installing psr/http-message (1.0.1)
    Loading from cache

  - Installing guzzlehttp/psr7 (1.4.2)
    Loading from cache

  - Installing symfony/event-dispatcher (v3.3.9)
    Loading from cache

  - Installing guzzle/guzzle (v3.8.1)
    Loading from cache

  - Installing knplabs/github-api (1.3.1)
    Loading from cache

  - Installing kasperg/phing-github (dev-feature/import 167c7ca)
    Cloning 167c7ca98fa5384f94be08a168a14d2c6a9fa9b6

  - Installing pear/pear_exception (v1.0.0)
    Loading from cache

  - Installing pear/console_getopt (v1.4.1)
    Loading from cache

  - Installing pear/pear-core-minimal (v1.10.3)
    Loading from cache

  - Installing pear/archive_tar (1.4.3)
    Loading from cache

  - Installing pear/versioncontrol_git (dev-master f074b9e)
    Cloning f074b9e7805197cb1019e05953960dde35ef3e31

  - Installing pfrenssen/phpcs-pre-push (1.0)
    Cloning master

  - Installing squizlabs/php_codesniffer (2.9.1)
    Loading from cache

  - Installing wimg/php-compatibility (7.0.8)
    Loading from cache

  - Installing behat/gherkin (v4.5.1)
    Loading from cache

  - Installing symfony/dom-crawler (v3.3.9)
    Loading from cache

  - Installing symfony/browser-kit (v3.3.9)
    Loading from cache

  - Installing symfony/css-selector (v3.3.9)
    Loading from cache

  - Installing behat/mink (v1.7.1)
    Loading from cache

  - Installing behat/mink-browserkit-driver (v1.3.2)
    Loading from cache

  - Installing behat/transliterator (v1.2.0)
    Loading from cache

  - Installing symfony/process (v3.3.9)
    Loading from cache

  - Installing psr/container (1.0.0)
    Loading from cache

  - Installing symfony/dependency-injection (v3.3.9)
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

  - Installing symfony/filesystem (v3.3.9)
    Loading from cache

  - Installing symfony/config (v3.3.9)
    Loading from cache

  - Installing symfony/translation (v3.3.9)
    Loading from cache

  - Installing symfony/class-loader (v3.3.9)
    Loading from cache

  - Installing behat/behat (v3.1.0)
    Loading from cache

  - Installing behat/mink-extension (v2.2)
    Loading from cache

  - Installing drupal/drupal-extension (v3.1.5)
    Loading from cache

  - Installing symfony/finder (v3.3.9)
    Loading from cache

  - Installing drupal/coder (8.2.12)
    Cloning 984c54a7b1e8f27ff1c32348df69712afd86b17f

  - Installing cpliakas/git-wrapper (dev-master 141d53f)
    Cloning 141d53fc80707666bc453140e3cd7cba8ecd2dee

  - Installing ec-europa/qa-automation (dev-release/3.0 fbec58c)
    Cloning fbec58cfaf143c4e9ebb2de37099df2ca4525558

Package guzzle/guzzle is abandoned, you should avoid using it. Use guzzlehttp/guzzle instead.
Generating autoload files
```
</p></details>
<details><summary>4. Link to the toolkit binary</summary><p>

```
> scripts/phingcalls.sh post-install
Buildfile: /home/verbral/github/ec-europa/project-id/vendor/ec-europa/toolkit/build.xml
 [property] Loading /home/verbral/github/ec-europa/project-id/vendor/ec-europa/toolkit/includes/phing/build/boot.props
 [property] Loading /home/verbral/github/ec-europa/project-id/build.project.props
     [echo] Global share directory /cache/share available.
     [echo] Temporary directory /home/verbral/github/ec-europa/project-id/.tmp available.

core > toolkit-binary-link:

     [echo] Provide project with starterkit binary at root level.
   [relsym] Link exists: /home/verbral/github/ec-europa/project-id/toolkit

core > toolkit-structure-generate:

     [echo] Project structure exists already.

core > build-toolkit:

     [echo] Toolkit successfully initialized.

BUILD FINISHED

Total time: 0.1636 seconds
```
</p></details>
</blockquote>
</p></details>