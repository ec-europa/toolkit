# NextEuropa Subsite Starterkit packaged for composer

This is a starting point for creating new websites for the [NextEuropa
platform](https://blogs.ec.europa.eu/eu-digital/content/next-europa-it-platform)
of the European Commission.

## Installation

The installation of the starterkit packaged in composer depends on 3 files being present in your repository.

<details><summary><b>composer.json</b>: Important to take note of the script commands.</summary><p>

SSK version is starting at 3.0. Performing a `composer update` on this requirement will be the only thing necessary to get the ssk package installed and updated. You may choose the version which you want to run. But Quality Assurance will always run your code on the lastest release.

The `phingexec` script function is mereley an example for people who do not have php installed on their system, but only docker. That script will allow you to use phing to setup a development environment without too much hassle.

```json
{
  "require": {
    "ec-europa/ssk": "~3.0"
  },
  "scripts": {
    "phingexec": "./ssk/phing",
    "post-update-cmd": "PROJECT=$(pwd) composer install --working-dir=vendor/ec-europa/ssk/includes/composer --no-interaction --no-suggest --ansi"
  }
}
```

</p></details>

<details><summary><b>build.xml</b>: This file points phing to the ssk installation root and imports it.</summary><p>

This is simply a pointer file to tell phing where you've installed the ssk. Important to know is that the previous `/resources/build.custom.xml` is now renamed to `/build.project.xml`. Also the `/resources/phpcs-custom.xml` has been renamed and located in your project basedir at `/phpcs-ruleset.xml'. These 2 files do no longer belong to the starterkit. They are essential to your project and fully under your control.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<project name="My subsite" default="help">
    <!-- Set starterkit root property -->
    <property name="project.starterkit.root" value="${project.basedir}/vendor/ec-europa/ssk" />
    <!-- Import starterkit build xml files. -->
    <import file="${project.starterkit.root}/build.xml" />
</project>
```

</p></details>

<details><summary><b>Jenkinsfile</b>: Currently only important to the Quality Assurance team for CI builds.</summary><p>

Again this file contains a simple pointer to the starterkits own Jenkinsfile. We are still working at giving this file a customized name so that subsites will be able to provide their own Jenkinsfile in their repository if they wish.

```groovy
def extcode

node {
  wrap([$class: 'AnsiColorBuildWrapper', cxolorMapName: 'xterm']) {
    deleteDir()
    checkout scm
    sh "composer update --no-interaction --no-suggest"
    extcode = load "vendor/ec-europa/ssk/Jenkinsfile"
    extcode.createWorkflow()
  }
}
```

</p></details>

## Features

- Support for NextEuropa 2.2.89 and later.
- Easily test your code on any release, branch or tag of the NextEuropa
  platform to validate your site will be compatible on an upgrade.
- Integrated support for Behat and PHP CodeSniffer.
- QA automation tools to provide static code checks.
- Built-in support for Continuous Integration using Docker Jenkins.
- A generally accepted docker enviroment that is built expecially for this
  project.

## Recent notable changes

- **2017-03-13**: The resources folder is expanded with a composer and git
    folder where you can place scripts that will be executed on the name of the
    hook that matches the folder name.
- **2017-02-04**: The build-dev target now has backup and restore support for
    when you have a site installed. This allows you to make a selection of
    certain files and folders to backup before build-dev and restore them after.
    The added functionality also comes with a dedicated target "rebuild-dev".
- **2017-02-06**: The resources/build.custom.xml is now the master build file.
    This allows you to override starterkit build targets in this file. The phing
    calls build-custom in build-dist and build-dev have been removed.
- **2017-01-19**: Subsites now have a dedicated phpcs-custom.xml file in their
    resources folder to add excludes if they wish not to clutter their code with
    codingStandardsIgnore tags.
- **2017-01-18**: New build target "build-clone" added to setup a local
    clone by importing a sanitized production database.
- **2017-01-17**: New version of QA automation tools based on symfony/console.
- **2016-10-05**: Added QA automation tools that should be run before each time
    you make a pull request to your reference repository.
- **2016-08-09**: We made the `resources/site.make` optional for use. Please
    pay attention when upgrading (merging upstream) that you don't lose
    your `resources/site.make` in the process.
- **2016-08-04**: The Subsite Starterkit has now actual releases. Tags are
    prepended with `starterkit/` in order to avoid conflicts with existing tags
    on forked subsite repositories.
- **2016-07-19**: Subsites now support [Composer](https://getcomposer.org) 
    dependencies. Please refer to the [Developer guide](docs/developer-guide.md) 
    for more information.
- **2016-07-07**: The Subsite Starterkit now supports NextEuropa 2.2.35 and later.
- **2015-12-22**: The structure of the platform package that is being downloaded
    from ContinuousPHP has changed. The codebase now resides in the root folder
    instead of the `build/` folder. The platform downloads should be much faster
    now.


## Repository structure

### 1. Project configuration

The configuration of the project is managed in 3 `build.properties` files:

1.  `build.properties.dist`: This contains default configuration which is
    common for all NextEuropa projects. *This file should never be edited.*
2.  `build.properties`: This is the configuration for your project. In here you
    can override the default configuration with settings that are more suitable
    for your project. Some typical settings would be the site name, the install
    profile to use and the modules/features to enable after installation.
3.  `build.properties.local`: This contains configuration which is unique for
    the local development environment. In here you would place things like your
    database credentials and the development modules you would like to install.
    *This file should never be committed.*

### 2. Project code

* Your custom modules, themes and custom PHP code go in the `lib/` folder. The
  contents of this folder get symlinked into the Drupal website at `sites/all/`.
* Any contrib modules, themes, libraries and patches you use should be put in
  the make file `resources/site.make`. Whenever the site is built these will be
  downloaded and copied into the Drupal website. By default we provide an example
  file in `resources/site.make.example`. Feel free to copy or rename this file
  to `resources/site.make`. This make file will be included in `build-dev`
  and `build-dist` by default.
* If you have any custom Composer dependencies, declare them in
  `resources/composer.json` and `resources/composer.lock`.
* If you require custom build steps for your subsite, you are free to use the 
  `resources/build.custom.xml` phing target. This target is included by default
   in build-dev & build-dist targets.

### 3. Drupal root

The Drupal site will be placed in the `platform/` folder when it is built. Point
your webserver here. This is also where you would execute your Drush commands.
Your custom modules are symlinked from `platform/sites/all/modules/custom/` to
`lib/modules/` so you can work in either location, whichever you find the most
comfortable.

### 4. Behat tests

All Behat related files are located in the `tests/` folder.

* `tests/behat.yml`: The Behat configuration file. This file is regenerated
  automatically when the project is built and should never be edited or
   committed.
* `tests/behat.yml.dist`: The template that is used for generating `behat.yml`.
  If you need to tweak the configuration of Behat then this is the place to do
  that.
* `tests/features/`: Put your Behat test scenarios here.
* `tests/src/Context/`: The home of custom Context classes.

### 5. Other files and folders

* `bin/`: Contains command line executables for the various tools we use such as
  Behat, Drush, Phing, PHP CodeSniffer etc.
* `build/`: Will contain the build intended for deployment to production. Use
  the `build-dist` Phing target to build it.
* `src/`: Custom PHP code for the build system, such as project specific Phing
  tasks.
* `tmp/`: A temporary folder where the platform tarball is downloaded and
  unpacked during the build process.
* `tmp/deploy-package.tar.gz`: The platform tarball. This file is very large and
  will only be downloaded once. When a new build is started in the future the
  download will be skipped, unless this file is deleted manually.
* `vendor/`: Composer dependencies and autoloader.


## Getting started

This README is divided in different parts, please read the relevant section:

1. [Developer guide](docs/developer-guide.md): Explains day-to-day
   development practices when working on a NextEuropa subsite.
2. [Starting a new project](docs/starting-a-new-project.md): This
   section explains how to set up a brand new project on the NextEuropa
   platform. These instructions need only to be followed once by the lead
   developer at the start of the project.
3. [Converting an existing project](docs/converting-an-existing-project.md):
   If you already have a project that runs on NextEuropa and you want to start
   using Continuous Integration, check out this section.
4. [Merging upstream changes](docs/merging-upstream-changes.md): How to
   merge the latest changes that have been made to the Subsite Starterkit in
   your own project.
5. [Contributing](docs/contributing.md): How to contribute bugfixes and
   new features to the Subsite Starterkit.
6. [Upgrading from 1.0.x to 2.0.x](docs/upgrading.md): How to upgrade to the
   2.0.x and enjoy full Composer support.
7. [Running scripts on composer and/or git hooks](docs/scripts.md): Add your
   own scripts that will be called when a composer hook or git hook is
   executed.
