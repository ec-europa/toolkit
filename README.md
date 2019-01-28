TESTING COMMIT

[![Build Status](https://drone.fpfis.eu/api/badges/ec-europa/toolkit/status.svg)](https://drone.ne-dev.eu/ec-europa/toolkit) [![License](https://img.shields.io/badge/License-EUPL%201.1-blue.svg)](LICENSE)

# NextEuropa Toolkit
<img align="left" width="50%" src="https://ec.europa.eu/info/sites/info/themes/europa/images/svg/logo/logo--en.svg" />

<p>The NextEuropa Toolkit is a composer package designed to speed up the
development of Drupal websites in the NextEuropa project. Its main component is
the Phing build system that builds your development environments, deploy
and test packages.</p>

## Requirements
There are three separate ways of using the NextEuropa project. Either you use an
environment with Docker installed, an environment without, or a mix of both.

<details><summary><b>Docker Solo</b></summary>

This requirement for docker only needs to have docker in docker support. The
configuration to accomplish this is complex and if implemented incorrectly can
give you problems. We recommend this approach only for seasoned docker users.
<br>*Required components*:
[Docker](https://docs.docker.com/engine/installation/linux/docker-ce/centos/)
</details>
<details><summary><b>Docker Plus</b></summary>

Instead of having the absolute minimal requirement you can install the host
level components Composer and Phing on the non-docker environment. Then this can
spin up the docker containers for you without having to configure a complicated
docker installation.<br>*Required components*:
[Composer](https://getcomposer.org/),
[Phing](https://packagist.org/packages/phing/phing),
[Docker](https://docs.docker.com/engine/installation/linux/docker-ce/centos/)
</details>
<details><summary><b>Docker Zero</b></summary>

If you are not interested in the advantages that the toolkit can give you with
the provided docker images you can keep a normal host only setup. But it is very
much recommended to use docker as it will give you everything you need.
<br>*Required components*:
[Composer](https://getcomposer.org/),
[LAMP Stack](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-centos-7)
</details>

## User guide

The toolkit contains different components that help you during the development, the main
component is the Phing build system that let's you easily set up the project
locally and can be used in CI tools like Jenkins, Drone or Travis.

- [Setting up a project](/docs/setting-up-project.md#setting-up-a-project)
    - [Getting the source code](/docs/setting-up-project.md#getting-the-source-code)
        - [New project](/docs/setting-up-project.md#new-project)
        - [Existing project](/docs/setting-up-project.md#existing-project)
- [Configuring a project](/docs/configuring-project.md#configuring-a-project)
    - [Build properties](docs/configuring-project.md#build-properties)
    - [Build files](docs/configuring-project.md#build-files)
    - [Cache system](docs/configuring-project.md#cache-system)
- [Building the codebase](docs/building-codebase.md#building-the-codebase)
    - [Platform builds](docs/building-codebase.md#platform-builds)
    - [Subsite builds](docs/building-codebase.md#subsite-builds)
    - [Theme builds](docs/building-codebase.md#theme-builds)
- [Installing the project](/docs/installing-project.md#installing-project)
    - [Clean installation](/docs/installing-project.md#clean-installation)
    - [Clone installation](/docs/installing-project.md#clone-installation)
- [Testing the project](/docs/testing-project.md/#testing-project)
    - [PHPCS testing](docs/testing-project.md#phpcs-testing)
    - [Behat testing](docs/testing-project.md#behat-testing)
    - [PHPUnit testing](docs/testing-project.md#phpunit-testing)
- [Using Composer hooks](/docs/composer-hooks.md#using-composer-hooks)
- [Using Git hooks](/docs/git-hooks.md#using-git-hooks)
- [Using Docker environment](/docs/docker-environment.md#using-docker-environment)
- [Supported profiles](/docs/profiles.md)

## Maintainers

This project is maintained by members of the Quality Assurance team who review
incoming pull requests for the NextEuropa project. The board on which they
operate can be found at [https://webgate.ec.europa.eu/CITnet/jira].

<details><summary><b>Contact list</b></summary>

- [Alex Verbruggen](https://github.com/verbruggenalex): Maintainer - Quality Assurance
- [Joao Santos](https://github.com/jonhy81): Maintainer - Quality Assurance
</details>

[https://webgate.ec.europa.eu/CITnet/jira]: https://webgate.ec.europa.eu/CITnet/jira/secure/RapidBoard.jspa?rapidView=581
[verbruggenalex]: https://github.com/verbruggenalex
[jonhy81]: https://github.com/jonhy81
