# Installing a project

### Before install
To start, run:

docker-compose up
It's advised to not daemonize docker-compose so you can turn it off (CTRL+C) quickly when you're done working. However, if you'd like to daemonize it, you have to add the flag -d:

```
docker-compose up -d
```

Build the site by running:
```
docker-compose exec web composer install
```

Using default configuration, the development site files should be available in the web directory and the development site should be available at: http://localhost:8080/web.


### Clean installation

To setup the site in local development mode and install it run:

```
docker-compose exec web ./vendor/bin/run toolkit:install-clean
```

To install the site from existing configuration run:

docker-compose exec web ./vendor/bin/run drupal:site-install --existing-config


### Clone installation


To setup the site using clone mode run:

```
docker-compose exec web ./vendor/bin/run toolkit:build-dev
docker-compose exec web ./vendor/bin/run toolkit:download-dump
docker-compose exec web ./vendor/bin/run toolkit:install-clone
```

Toolkit will load the credentials necessary to download the sanitized database dump for your project, so make sure you have the following variables in your environment:

```
- ASDA_USER
- ASDA_PASSWORD
```

If you want to run extra commands after the `toolkit:install-clone` command you
can configure the following in your `runner.yml.dist` file:

```yaml
toolkit:
  install:
    clone:
      commands:
        - ./vendor/bin/drush status
```

When running the command toolkit:install-clone it will run the following sequence of commands after the database import:

```
./vendor/bin/drush state:set system.maintenance_mode 1 --input-format=integer -y
./vendor/bin/drush updatedb -y
./vendor/bin/run toolkit:import-config
./vendor/bin/drush state:set system.maintenance_mode 0 --input-format=integer -y
./vendor/bin/drush cache:rebuild
```

These commands simulate the automated deployment that Drone provides through its pipeline. You can alter these commands by providing a file named **.opts.yml** in the root of your project folder. For more detailed information on the contents of this **.opts.yml** file please refer to this page: https://webgate.ec.europa.eu/fpfis/wikis/display/MULTISITE/NE+Pipelines#NEPipelines-DeploymentOverrides .

### Other topics
- [Setting up a project](/docs/setting-up-project.md)
- [Configuring a project](/docs/configuring-project.md)
- [Testing the project](/docs/testing-project.md)
- [Using Docker environment](/docs/docker-environment.md)
- [Continuous integration](/docs/continuous-integration.md)
- [Building assets](/docs/building-assets.md)
- [Available tasks](/docs/available-tasks.md)
- [Project documentation](/docs/project-documentation.md)
- [Changelog](/CHANGELOG.md)