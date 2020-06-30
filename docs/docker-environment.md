# Using Docker environment

<big><table><thead><tr><th nowrap> [Using Git hooks](./git-hooks.md#using-git-hooks) </th><th width="100%" align="center"> [User guide](../README.md#user-guide) </th><th nowrap> [NextEuropa Toolkit](../README.md#nexteuropa-toolkit) </th></tr></thead></table>

# Requirements

- [docker](https://docs.docker.com/install/linux/docker-ce/ubuntu/#install-using-the-convenience-script)
- [docker-compose](https://docs.docker.com/compose/install/#install-compose-on-linux-systems)

# Usage

## The docker-compose.yml file

To use the docker environment provided by Toolkit you need to copy the
vendor/ec-europa/toolkit/includes/docker/docker-compose.yml file to the root
of your project.

## Starting the containers

To start up the containers provided by the docker-compose.yml file you can
execute the following command:

```bash
docker-compose up -d
```

## Executing commands in the web container:

To execute commands in the web container you can execute
`docker-compose exec web <your command>`. For example to start your toolkit
based project from scratch:

```bash
docker-compose exec web composer install
docker-compose exec web ./toolkit/phing build-platform build-subsite-dev install-clone
```

## Shutting down the containers

To shut down your containers after you have finished working on them can be done
by executing:

```bash
docker-compose down
```