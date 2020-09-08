# Building assets

## Overview

Toolkit provides a way to build theme assets with Gulp.js.

By default a gulpfile is inclued and as well some npm packages in order to:

- Look for Scss files and convert them into Css
- Minify Css and Js
- Merge files into one minimized file

## How to use

### Source files

The folder structure for the source files should be aligned like this:

- /theme_folder/css
- /theme_folder/css
- /theme_folder/js

Note: It will search on all childern folders.

After the task is complete the generated 'theme_folder/assets' will look like this:

```
/theme_folder
  /assests
    /css
      style.min.css
    /js
      script.min.js
```

### Declare 'default_theme'

The default theme needs to be added in the file runner.yml.

```
drupal:
  site:
    default_theme: "my-theme"
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

It's possible to use a custom gulpfile on the theme root folder.
If a gulpfile alredy exists on the root folder when running this command, the one provided by toolkit will be ignored.

Also the npm packages can be extended.
In order to do that add them in the file runner.yml like the example bellow:

```
toolkit:
  build:
    npm:
      packages: gulp gulp-sass gulp-concat gulp-clean-css gulp-minify
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