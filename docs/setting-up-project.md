# Setting up a project

## Getting the sourcecode

Below we explain the difference between setting up a new project and setting up
an existing project:

### New project from scratch
To instantiate a new project to running as a subsite you only have to execute one command which will perform multiple steps
for you automatically.

```
composer create-project openeuropa/drupal-site-template --stability=dev <dg-name>-<project-id>-reference
docker-compose up -d
docker-compose exec web vendor/bin/run
```

### From existent project from repository
To install locally a project running Toolkit 4 you should run the following commands:

```
git clone git@github.com:ec-europa/<repository-name>.git
docker-compose up -d
docker-compose exec web composer install
docker-compose exec web vendor/bin/run
```

### Others topics
- [Configuring a project](/docs/configuring-project.md)
- [Installing the project](/docs/installing-project.md)
- [Testing the project](/docs/testing-project.md)
- [Using Docker environment](/docs/docker-environment.md)
- [Continuous integration](/docs/continuous-integration.md)
- [Building assets](/docs/building-assets.md)
- [Available tasks](/docs/available-tasks.md)
- [Project documentation](/docs/project-documentation.md)
- [Changelog](/CHANGELOG.md)