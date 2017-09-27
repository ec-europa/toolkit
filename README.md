[![Build Status](https://drone.ne-dev.eu/api/badges/ec-europa/toolkit/status.svg)](https://drone.ne-dev.eu/ec-europa/toolkit) [![License](https://img.shields.io/badge/License-EUPL%201.1-blue.svg)](LICENSE)

# NextEuropa Toolkit
<img align="left" width="50%" src="https://ec.europa.eu/info/sites/info/themes/europa/images/svg/logo/logo--en.svg" />

<p>The NextEuropa Toolkit is a composer package designed to speed up the
development of Drupal websites in the NextEuropa project. It's main
component is the Phing build system that builds your development
environments, deploy packages and test packages.</p>

## Requirements
There are three separate ways of using the NextEuropa project. Either
you use an environment with Docker installed, an environment without, or
a mix of both.
  
<details><summary><b>Docker Solo</b></summary>

This requirement for docker only needs to have docker in docker support.
The configuration to accomplish this is complex and if implemented
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

If you are not interested in the advantages that the toolkit can give
you with the provided docker images you can keep a normal host only setup.
But it is very much recommended to use docker as it will give you
everything you need.<br>*Required components*:
[Composer](https://getcomposer.org/),
[LAMP Stack](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-centos-7)
</details>

## Installation
There are two different types of projects for which to install this
composer package. You can either create a new platform repository or a
new subsite repository. Both types use this toolkit to build the project
and it's release packages.

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

## User guide

The toolkit contains different components that help you in development.
The main component is the Phing build system that let's you easily set
up the project locally and can be used in CI tools like Jenkins, Drone
and Travis.

- [Configuring a project]()
    - [Installation explained]()
    - [Using build properties]()
- [Building the codebase]()
    - [Platform builds]()
    - [Subsite builds]()
    - [Theme builds]()
- [Installing the project]()
    - [Clean installation]()
    - [Clone installation]()
- [Testing the codebase]()
    - [Coding Standards]()
    - [Behat testing]()
    - [PHP Unit testing]()
- [Using Composer hooks](./docs/composer-hooks.md)
- [Using Git hooks](./docs/git-hooks.md)
- [Using Docker environment]()

## Maintainers

This project is maintained by members of the Quality Assurance team who
review incoming pull requests for the NextEuropa project. The board on
which they operate can be found at [https://webgate.ec.europa.eu/CITnet/jira].

<details><summary><b>Contact list</b></summary>

[Alex Verbruggen](verbruggenalex): Maintainer -Quality Assurance
[Joao Santos](jonhy81): Maintainer - Quality Assurance
</details>

[https://webgate.ec.europa.eu/CITnet/jira]: https://webgate.ec.europa.eu/CITnet/jira/secure/RapidBoard.jspa?rapidView=581
[verbruggenalex]: https://github.com/verbruggenalex
[jonhy81]: https://github.com/jonhy81
