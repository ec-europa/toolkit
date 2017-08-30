# Phing toolkit

## About
The build system included in this toolkit is developed to build Drupal
7 projects based on the NextEuropa platform. It supports building the
profiles or subsites. It consists of 4 main parts:

### Bootstrap
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

### Help
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

### Project
These are the main build targets used to create the codebase and install
projects. There are three project types:

1. **theme.xml**: builds themes like ec_europa and places
2. **platform.xml**: builds the platform if a profiles folder is
detected.
3. **subsite.xml**: builds subsites within the platform.

> The platform.xml and subsite.xml are loaded conditionally depending on
> what type of project is being used. Any targets that are used by
> multiple project types are defined in the project.xml file itself.


