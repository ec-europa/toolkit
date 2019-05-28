# Testing the project

This guide explains how to use the resources provided by toolkit to test your 
project. You can list all resources available with the following command:

## Behat testing
To run behat tests you can make use of the `toolkit:test-behat` command. This will
re-generate your behat configuration from `./behat.yml.dist` and run it on your
current site installation.

To run the behat tests:
```
docker-compose exec web ./vendor/bin/run toolkit:test-behat
```

New tests should be stored in `./tests/tests/features/` folder, then they will be executed
automatically by toolkit task.


## PHPCS testing
To run behat tests you can make use of the `toolkit:test-phpcs` command. This will
re-generate your configuration from `./grumphp.yml.dist` and run it on your
current codebase.

To run the PHPCS checks:

```
docker-compose exec web ./vendor/bin/run toolkit:test-phpcs
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
- [Changelog](/CHANGELOG.md)