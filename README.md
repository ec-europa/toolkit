# Toolkit

The Toolkit is a composer package designed to speed up the development of Drupal websites in Drupal 9+. Its main core is the robo based build system that builds your development environments, deploy and test your code.

[![Build Status](https://drone.fpfis.eu/api/badges/ec-europa/toolkit/status.svg?branch=release/9.x)](https://drone.fpfis.eu/ec-europa/toolkit) [![License](https://img.shields.io/badge/License-EUPL%201.1-blue.svg)](LICENSE)

## Prerequisites
You need to have the following software installed on your local development environment:

* [Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)
* [Docker Compose](https://docs.docker.com/compose/install/)

## User guide and documentation

```
Toolkit is a developer package, make sure you install this in the dev section of your composer.json.
composer require --dev ec-europa/toolkit:^9
```

- [Setting up a project](/docs/guide/setting-up-project.rst)
- [Configuring a project](/docs/guide/configuring-project.rst)
- [Installing the project](/docs/guide/installing-project.rst)
- [Testing the project](/docs/guide/testing-project.rst)
- [Using Docker environment](/docs/guide/docker-environment.rst)
- [Continuous integration](/docs/guide/continuous-integration.rst)
- [Building assets](/docs/guide/building-assets.rst)
- [Commands](/docs/guide/commands.rst)
- [Git Hooks](/docs/guide/git-hooks.rst)
- [Update Project Documentation](/docs/guide/project-documentation.rst)
- [Changelog](/CHANGELOG.md)

To have more details about this package, please check the [full documentation](https://ec-europa.github.io/toolkit/).

## Contributing
Please read [CONTRIBUTING.md](/CONTRIBUTING.md) for details on our code of conduct and the process to submit pull-request to us.

## License
This project is licensed under the EUROPEAN UNION PUBLIC LICENCE v. 1.2 - see the [LICENSE.txt](/LICENSE.txt) for details.

## Maintainers
This project is maintained by members of the Quality Assurance team who review
incoming pull requests for this project. The board on which they
operate can be found at [https://webgate.ec.europa.eu/CITnet/jira].

<details><summary><b>Contact list</b></summary>

- [Joao Santos](https://github.com/jonhy81): Maintainer - Quality Assurance

- [Miguel Martins](https://github.com/zarabatana): Maintainer - Quality Assurance

- [Joao Silva](https://github.com/joaocsilva): Maintainer - Quality Assurance
</details>
