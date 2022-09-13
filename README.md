# NextEuropa Toolkit

The NextEuropa Toolkit is a composer package designed to speed up the development of Drupal websites in Drupal 9. Its main core is the robo based build system that builds your development environments, deploy and test your code.

[![Build Status](https://drone.fpfis.eu/api/badges/ec-europa/toolkit/status.svg)](https://drone.fpfis.eu/ec-europa/toolkit) [![License](https://img.shields.io/badge/License-EUPL%201.1-blue.svg)](LICENSE)

## Prerequisites
You need to have the following software installed on your local development environment:

* [Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx).
* [Docker Compose](https://docs.docker.com/compose/install/)

## User guide

```
Toolkit is a developer package, make sure you install this in the dev section of your composer.json.
composer require --dev ec-europa/toolkit:^8
```

The toolkit contains different components that help you during the development, the main
component is the Phing build system that let's you easily set up the project
locally and can be used in CI tools like Jenkins, Drone or Travis.

- [Setting up a project](/docs/setting-up-project.md)
- [Configuring a project](/docs/configuring-project.md)
- [Installing the project](/docs/installing-project.md)
- [Testing the project](/docs/testing-project.md)
- [Using Docker environment](/docs/docker-environment.md)
- [Continuous integration](/docs/continuous-integration.md)
- [Available tasks](/docs/available-tasks.md)
- [Building assets](/docs/building-assets.md)
- [Update Project Documentation](/docs/project-documentation.md)
- [Changelog](/CHANGELOG.md)

## Contributing
Please read [CONTRIBUTING.md](/CONTRIBUTING.md) for details on our code of conduct and the process to submit pull-request to us.

## License
This project is licensed under the EUROPEAN UNION PUBLIC LICENCE v. 1.2 - see the [LICENSE.txt](/LICENSE.txt) for details.

## Maintainers
This project is maintained by members of the Quality Assurance team who review
incoming pull requests for the NextEuropa project. The board on which they
operate can be found at [https://webgate.ec.europa.eu/CITnet/jira].

<details><summary><b>Contact list</b></summary>

- [Alex Verbruggen](https://github.com/verbruggenalex): Maintainer - Quality Assurance

- [Joao Santos](https://github.com/jonhy81): Maintainer - Quality Assurance
</details>
