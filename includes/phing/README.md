# Phing toolkit

## About
The build system included in this toolkit is developed to build Drupal
7 projects based on the NextEuropa platform. It supports building the
profiles or subsites. It consists of 4 main parts:

### 1. Bootstrap
The bootstrap is loaded first in the main build.xml of the toolkit. The
bootstrap is responsible for making the task runner functional. It does
three things:

1. **extensions.xml**: custom classes for extra tasks, conditions, etc.
2. **properties.xml**: build properties for configuration and setting of
conditional properties.
3. **directories.xml**: Create needed directories to optimize builds.

> After loading these three files it also loads the project build file
> through the boostrap.xml itself that allows overriding any targets
> defined by this toolkit.

### 2. Help
The help build files contain helper targets. They are split up per
category. Current catgorized build files are:

1. **deprecated.xml**: contains a mapping of subsite-starterkit to toolkit
targets.
2. **docker.xml**: contains phing targets to manage docker containers.
(experimental!)
3. **drush.xml**: contains drush helper targets that help manage your
Drupal installation.
4. **toolkit.xml**: targets related to setup and configuration of the
toolkit.

> If a target does not belong to a category it will be defined in the
> help.xml file itself.

### 3. Project
These are the main build targets used to create the codebase and install
projects. There are three project types:

1. **theme.xml**: builds themes like ec_europa and places
2. **platform.xml**: builds the platform if a profiles folder is
detected.
3. **subsite.xml**: builds subsites within the platform.

> The platform.xml and subsite.xml are loaded conditionally depending on
> what type of project is being used. Any targets that are used by
> multiple project types are defined in the project.xml file itself.

### 4. Test
The test file contains different targets to ease the testing of your
project. They are split up per category:

1. behat.xml: allows you to configure and run behat on your project.
2. phpcs.xml: allows you to configure and run phpcs on your codebase.
3. phpunit.xml: allows you to configure and run phpunit on your pruject.

> The test system is designed to be standalone so next to a project
> deploy package we can also create a project test package. This allows
> for swift testing without actually needing to install the project
> through cloning the repository.


