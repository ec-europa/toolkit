# Building assets

## Overview

Toolkit provides a way to build theme assets with Gulp.js.

By default a gulpfile is included and as well some npm packages in order to:

- Look for Scss files and convert them into Css
- Minify Css and Js
- Merge files into one minimized file

Command to run:
```
docker-compose vendor/bin/run toolkit:build-assets
```

## How to use

### Source files

The folder structure for the source files should be aligned like this:

- {your-theme-folder}/src/scss
- {your-theme-folder}/src/js


After this task is complete the generated folder '{your-theme}/assets' will look like this:

```
/your-theme
  /assests
    /css
      style.min.css
    /js
      script.min.js
```
Note: The folder name 'assets' is the default value provided. It can be override on the 'gulpfile.js'.

### Get 'default_theme'

If no config files are present in the project, the default theme can be added in the file 'runner.yml'.

```
drupal:
  site:
    default_theme: "your-theme"
```

Otherwise toolkit will get the parameter from the file 'config/sync/system.theme.yml'.

As alternative is also possible to use the parameter in the command line:
```
docker-compose vendor/bin/run toolkit:build-assets --default-theme=your-theme
```

### Build theme assets

Run the following command:

```
docker-compose exec web ./vendor/bin/run toolkit:build-assets
or
docker-compose exec web ./vendor/bin/run tba
```

This will (re)generate the /assets folder.


## Extending functionality

### Add a custom 'gulpfile.js' file

It's possible to use a custom gulpfile on the theme root folder.
If the file do not exists, toolkit will create one using the default template.

### Install additional npm packages

Additional npm packages can be installed to extend the functionality.
In order to do that add them in the file 'runner.yml' like the example bellow:

```
toolkit:
  build:
    npm:
      packages: gulp gulp-sass gulp-concat gulp-clean-css gulp-minify
```

#### npm install --save-dev

By default the npm packages are installed with the option '--save-dev' and will appear in the devDependencies.
To override this behavior add in the file 'runner.yml' the following property:

```
toolkit:
  build:
    npm:
     mode: (leave empty or add '--save-prod')
```

### Others topics
- [Configuring a project](/docs/configuring-project.md)
- [Installing the project](/docs/installing-project.md)
- [Testing the project](/docs/testing-project.md)
- [Using Docker environment](/docs/docker-environment.md)
- [Continuous integration](/docs/continuous-integration.md)
- [Building assets](/docs/building-assets.md)
- [Available tasks](/docs/available-tasks.md)
- [Changelog](/CHANGELOG.md)