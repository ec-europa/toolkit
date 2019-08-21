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

Toolkit will load the credentials necessary to clone your subsite from your environment, so make sure you have in your env the following variables:

```
- ASDA_USER
- ASDA_PASSWORD
```

### Other topics
- [Setting up a project](/docs/setting-up-project.md)
- [Configuring a project](/docs/configuring-project.md)
- [Testing the project](/docs/testing-project.md)
- [Using Docker environment](/docs/docker-environment.md)
- [Continuous integration](/docs/continuous-integration.md)
- [Available tasks](/docs/available-tasks.md)
- [Changelog](/CHANGELOG.md)