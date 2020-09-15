# Testing the project

This guide explains how to use the resources provided by toolkit to test your
project. You can list all resources available with the following command:

## Behat testing
To run behat tests you can make use of the `toolkit:test-behat` command. This will
re-generate your behat configuration from `./behat.yml.dist` and run it on your
current site installation.

New tests should be stored in `./tests/features/` folder, then they will be executed
automatically by toolkit task.

To run the behat tests:
```
docker-compose exec web ./vendor/bin/run toolkit:test-behat
```

The default testing pipeline in Drone allows you to differentiate between behat
tests that are supposed to run on a clean installation and behat tests that are
supposed to run on a clone installation of the website. This is done through the
usage of the @clone tag.

Tests with the @clone tag are excluded for testing behat on a clean installation
and are exclusively used for testing behat on a clone installation.

The default configuration for this is to have the following defined in your
`./behat.yml.dist` file:

```yaml
default:
  suites:
    default:
      // Add the replacement for behat.tags to your config.
      filters:
        tags: "${behat.tags}"
```

And then you can control which tests you would like to run by changing the
setting in your `./runner.yml.dist` file:

```yaml
// Excludes the @clone tags for your default behat testing.
behat:
  tags: "~@clone"
```


## PHPCS testing
To run coding standard tests you can make use of the `toolkit:test-phpcs`
command. This will validate your configuration from `./grumphp.yml.dist` and run
it on your current codebase.

To run the coding standard checks:

```
docker-compose exec web ./vendor/bin/run toolkit:test-phpcs
```

This will first validate the configuration of your `./grumphp.yml.dist`. The
correct configuration of this file contains the import of the qa conventions
like shown below:

```yaml
imports:
  - { resource: vendor/ec-europa/qa-automation/dist/qa-conventions.yml }
parameters:
  tasks.phpcs.ignore_patterns:
    - vendor/
    - web/
  tasks.phpcs.triggered_by:
    - php
    - module
    - inc
    - theme
    - install
    - yml

  extensions:
    - OpenEuropa\CodeReview\ExtraTasksExtension
```

Previously this was hardcoded in your composer.json. But if you want to be able
to override configuration from the qa convention you should remove that setting.
So if you have the following lines in your composer.json you should remove those
lines:

```json
"grumphp": {
    "config-default-path": "vendor/ec-europa/qa-automation/dist/qa-conventions.yml"
}
```

PHPCBF task is available, to automatic fix your issues please run the following
command:
```
docker-compose exec web ./vendor/bin/run toolkit:run-phpcbf --file-path=<file-to-fix>
```


## Testing in CI
Toolkit is full integrated with oficial pipeline that currently requires minimum of 1 behat
test and a clean report for phpcs check.

Any customization done in your project will be respected in Drone build.

### Other topics
- [Setting up a project](/docs/setting-up-project.md)
- [Configuring a project](/docs/configuring-project.md)
- [Installing the project](/docs/installing-project.md)
- [Using Docker environment](/docs/docker-environment.md)
- [Continuous integration](/docs/continuous-integration.md)
- [Building assets](/docs/building-assets.md)
- [Changelog](/CHANGELOG.md)