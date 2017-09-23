[![Build Status](https://drone.ne-dev.eu/api/badges/ec-europa/toolkit/status.svg)](https://drone.ne-dev.eu/ec-europa/toolkit) [![License](https://img.shields.io/badge/License-EUPL%201.1-blue.svg)](LICENSE)

# NextEuropa Toolkit
<img align="left" width="50%" src="https://ec.europa.eu/info/sites/info/themes/europa/images/svg/logo/logo--en.svg" />

<p>The NextEuropa Toolkit is a composer package designed to speed up the
development of Drupal websites in the NextEuropa project. It's main
component is the Phing build system that builds your development
environments, deploy packages and test packages.</p>

<b><details><summary>Table of Contents</summary>

- [Background](#background)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
	- [Phing](#phing)
	- [Properties](#properties)
- [Maintainers](#maintainers)
- [Contribute](#contribute)
- [License](#license)
</details></b>

## Background
This composer package helps developers working on Drupal websites in the
NextEuropa project speed up and align their development. It is
officially maintained by members of the Quality Assurance team for the
NextEuropa project. They oversee general workflow and overall quality of
projects. The standards enforced by the Quality Assurance team are a mix
of internally provided standards and a collection of standards
established by the leading contributors to the project.

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

## Usage

### Phing
This is the main component of the toolkit. It allows you to locally set
up your project and is integrated with the CI and CD tools to optimize
the development process. To learn more about the phing targets, read
more here:

<b><details><summary>Table of Contents</summary>

- [Phing toolkit](./includes/phing/README.md)
    - [Properties](./includes/phing/docs/properties.md)
    - [Main builds](./includes/phing/docs/main-builds.md)
    - [Target list](./includes/phing/docs/properties.md/target-list)
</details></b>

## Maintainers

This project is maintained by members of the Quality Assurance team who
review incoming pull requests for the NextEuropa project. The board on
which they operate can be found at [https://webgate.ec.europa.eu/CITnet/jira].

<details><summary><b>Contact list</b></summary>

|Full name|Username|Department|Role|
|:---|:---|:---|:---|
|Alex Verbruggen|[verbruggenalex]|Quality Assurance|Maintainer + Contact for Devops & Platform|
|Joao Santos|[jonhy81]|Quality Assurance|Maintainer + Contact for Subsites|
</details>

## License

The toolkit is an opensource project. We welcome contributions and bug
reports.

* [European Union Public License 1.1](LICENSE.md)

[https://webgate.ec.europa.eu/CITnet/jira]: https://webgate.ec.europa.eu/CITnet/jira/secure/RapidBoard.jspa?rapidView=581
[verbruggenalex]: https://github.com/verbruggenalex
[jonhy81]: https://github.com/jonhy81

