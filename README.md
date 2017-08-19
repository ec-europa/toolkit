[![Latest Stable Version](https://poser.pugx.org/drush/drush/v/stable.png)](https://packagist.org/packages/drush/drush) [![Total Downloads](https://poser.pugx.org/drush/drush/downloads.png)](https://packagist.org/packages/drush/drush) [![Latest Unstable Version](https://poser.pugx.org/drush/drush/v/unstable.png)](https://packagist.org/packages/drush/drush) [![License](https://poser.pugx.org/drush/drush/license.png)](https://packagist.org/packages/drush/drush) [![Documentation Status](https://readthedocs.org/projects/drush/badge/?version=master)](https://readthedocs.org/projects/drush/?badge=master)
Shields to consider: https://shields.io/

Note: This documentation is in progress and should not be relied on. The project is in full development.

# NextEuropa Toolkit
<img align="left" width="50%" src="https://ec.europa.eu/info/sites/info/themes/europa/images/svg/logo/logo--en.svg" />

<p>The NextEuropa Toolkit is a composer package designed to speed up the
development of Drupal websites in the NextEuropa project. It's main
component is the Phing build system that builds your development
environemnts, deploy packages and test packages.</p>

<details><summary>Table of Contents</summary>

- [Background](#background)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
	- [Phing](#phing)
	- [Build properties](#build-properties)
- [Badge](#badge)
- [Example Readmes](#example-readmes)
- [Related Efforts](#related-efforts)
- [Maintainers](#maintainers)
- [Contribute](#contribute)
- [License](#license)
</details>

## Background
This composer package helps developers working on Drupal websites in the
NextEuropa project speed up and align their development. The toolkit is
an opensource and in no way obligated to provide support or guarantee
compatibility with your system. It is officially maintained by members
of the Quality Assurance team for the NextEuropa project. They oversee
general workflow and overall quality of projects. The standards emposed
by the Quality Assurance team are a mix of internally provided standards
and a collection of standards established by the leading contributors to
the project.

## Requirements
There are three separate ways of using the NextEuropa project. Either
you use an environment with Docker installed, an environment without.
Or a mix of both.
  
<details><summary><b>Docker Solo</b></summary>

This requirement for docker only requires docker in docker support.
The configuration to accomplish this is complicated and if implemented
incorrectly can give you problems. We recommend this approach only
for seasond docker users.<br>*Required components*:
[Docker](https://docs.docker.com/engine/installation/linux/docker-ce/centos/)
</details>
<details><summary><b>Docker Plus</b></summary>

Instead of having the absolute minimal requirement you can install the
host level components Composer and Phing on the non-docker environment.
Then this can spin up the docker containers for you without having to
configure a complicated docker installation.<br>*Required components*:
[Composer](https://getcomposer.org/),
[Phing](https://packagist.org/packages/phing/phing),
[Docker](https://docs.docker.com/engine/installation/linux/docker-ce/centos/)
</details>
<details><summary><b>Docker Zero</b></summary>

If you are not interested in the advantages that the starterkit can give
you with the provided docker images you can keep a normal host only setup.
But it is very much recommended to use docker as it will give you
everything you need.<br>*Required components*:
[Composer](https://getcomposer.org/),
[LAMP Stack](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-centos-7)
</details>

## Installation
There are two different types of projects to install thiis composer
package. You can either create a platform repository or a subsite
repository. Both types use this toolkit to build the project and it's
release packages.

<details><summary><b>composer create-project ec-europa/platform dirname ~3.0.0</b></summary>

This command will clone the repository of the ec-europa/platform project
and run composer install on it. The installation of the toolkit itself
is run seperately to create a clear separation between the toolkit and
your project source code. Extending the toolkit is not possible without
contributing your functionalities through pull requests. You will be
requested to remove or keep the VCS files after cloning the project. For
development purposes you should NOT agree to remove these files. Only for
deploy and testing purposes it is recommended to remove the version
control system. There is only one official platform project which is
maintained by the NextEuropa core development team.
</details>

<details><summary><b>composer create-project ec-europa/subsite dirname ~3.0.0</b></summary>

This command will clone the repository of the ec-europa/subsite project
and run composer install on it. The installation of the toolkit itself
is run seperately to create a clear separation between the toolkit and
your project source code. Extending the toolkit is not possible without
contributing your functionalities through pull requests. You will be
requested to remove or keep the VCS files after cloning the project.
Upon initial creation of your project you need to remove the VCS files
as you will commit the source code to your own repository. After your
project is registered by NextEuropa as an official subsite you will be
able to direct pull requests to a reference repository.

After your project is accepted you can register your fork locally or
through packagist to use the same composer create-project command on 
your fork that serves development only.

<details><summary>To locally register your package the following code to your global config.json:</summary><p>

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
          "url" : "https://github.com/<github-account>/<project-id>-dev.git",
          "reference" : "master"
        }
      }
    }
  ],
}

```
</p></details>

<details><summary>To globally register your development repository you can visit packagist.org.</summary><p>

[https://packagist.org/packages/submit]
</p></details>
</details>

## Usage

### Phing
We keep the documentation light for this page because we are planning to
move all documentation to the github wiki. For now please help yourself
with the command listing target. You can get a list of all the available
Phing build commands ("targets") with a short description of each main
target.

Beware: the list below is under heavy development.

<details><summary><b>./ssk/phing</b> or <b>./ssk/phing help</b></summary>

```
Main targets:
-------------------------------------------------------------------------------
 build-clean          Build local version of subsite with a clean install.
 build-clone          Build local version of subsite with production data.
 build-code           Build local version of subsite without install.
 build-keep           Build local version of subsite with backup and restore.
 build-release        Build subsite source code release package.
 build-tests          Build subsite tests code release package.
 docker-compose-down  Trash docker project.
 docker-compose-stop  Stop docker project.
 docker-compose-up    Start docker project.
 help                 The default target used when no arguments have been given.
 help_xml.help        The default target used when no arguments have been given.
 link-docroot         Create symlink from build to docroot.
 subsite-install      Install the subsite.
 test-run-behat       Refresh configuration and run behat scenarios.
 test-run-phpcs       Refresh configuration and run phpcs review.
 test-run-qa          Refresh configuration and run qa review.

Subtargets:
-------------------------------------------------------------------------------
 dist-composer-install
 dist-copy-resources
 dist-delete
 dist-make
 drush-create-files-dirs
 drush-dl-rr
 drush-enable-solr
 drush-make-no-core
 drush-rebuild-node-access
 drush-regenerate-settings
 drush-registry-rebuild
 drush-site-install
 drush-sql-create
 drush-sql-drop
 drush-sql-dump
 drush-sql-import
 platform-composer-install
 platform-delete
 platform-download
 platform-link-resources
 platform-make
 platform-unpack
 platform-update-htaccess
 starterkit-build-docs
 starterkit-copy-templates
 starterkit-link-binary
 starterkit-upgrade
 subsite-composer-install
 subsite-create-directories
 subsite-database-download
 subsite-database-import
 subsite-database-wget
 subsite-modules-devel-dl
 subsite-modules-devel-en
 subsite-modules-devel-mf
 subsite-modules-install-en
 subsite-setup-files-directory
 subsite-site-backup
 subsite-site-restore
 test-behat-exec
 test-behat-setup
 test-behat-setup-link
 test-phpcs-exec
 test-phpcs-setup
 test-phpcs-setup-prepush
 test-qa-exec
```
</details>

### Build properties

There are 3 different sets of build properties file that you can use. If you
are unfamiliar with the purpose behind each different type of properties file
please open the descriptions and read what they are designed for.

<details><summary><b>build.properties.local</b></summary>

This file will contain configuration which is unique to your development
environment. It is useful for specifying your database credentials and the
username and password of the Drupal admin user so they can be used during the
installation. Next to credentials you have many development settings that you
can change to your liking. Because these settings are personal they should
not be shared with the rest of the team. Make sure you never commit this file.
</details>
<details><summary><b>build.properties.dist</b><br></summary>

This properties file contains the default settings and acts as a loading and
documentation file for the system to work correctly. Any time you install the
toolkit it will be copied to your repository root. Even though it is a template
you should not remove this file, but commmit it to your repository. The reason
for this is that it allows you to easily check the version of the toolkit and
what new properties were introduced or deprecated.
</details>
<details><summary><b>build.properties</b><br></summary>

Always commit this file to your repository. This file is required for all
NextEuropa projects. Without it your build system will fail with a build
exception. It must contain a minimum set of properties, like project.id, etc.
A list of required properties is still to be delivered. Aside from the
required properties you can add any other properties that are project
specific and do not contain any credentials.
</details>
