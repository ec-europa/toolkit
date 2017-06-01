## Converting an existing project

If you already have a project that was built on NextEuropa or Multisite CMS and
want to convert it to the Subsite Starterkit methodology then please follow
these guidelines.

> Note that only NextEuropa 2.1.0 or higher is supported. If your project is on
> version 1.7.x or 2.0.x you will need to update to 2.1.x first.

### 0. Move the project to Github

If your project was made before the adoption of the starterkit it probably is
still hosted on Stash. For the automated tests and coding standards checks to
work please move the code to Github first.

* Create a Github account if you do not have one yet. It is fine to use your
  personal e-mail address for this.
* Ask to get added to the 'ec-europa' organisation, this hosts all code of
  the European Commission. You can ask anyone in the QA team to add you.
* [Create a new repository](https://github.com/organizations/ec-europa/repositories/new)
  in the 'ec-europa' organisation for your project. The name of the repository
  will be your project name with the '-dev' suffix. Example 'myproject-dev'.
* Your project can be either public or private. We encourage projects to be
  public to increase interoperability with other government organisations.
  Websites that contain only content intended to be shared with the public
  are encouraged to make their code public.
  Make your repository private if you have a good reason for it: for example
  if you have known security vulnerabilities, or have committed sensitive
  data such as database dumps, e-mail addresses or passwords.
* Push the code from Stash to Github. For this you need the URLs of both. You
  can get these from the project page on Stash and the new one of Github.

  ```
  # This is a one time operation, so it's a good idea to do it in /tmp.
  $ cd /tmp
  # Make a mirror clone of the project, using the URL from Stash.
  $ git clone --mirror https://myname@webgate.ec.europa.eu/CITnet/stash/path/to/myproject-dev.git
  # Leap into the mirrored repository.
  $ cd myproject-dev.git
  # Push the mirror to Github, using the URL from the new repository.
  $ git push --mirror https://github.com/ec-europa/myproject-dev.git
  ```

* Check that all code and branches are now available on Github. If all is
  well, inform your colleagues to no longer use the repository on Stash, and
  either remove the repository from Stash or rename it to
  'myproject-dev-obsolete' so that it is clear it should no longer be used.
* Finally, remove your mirror. This is a bare repository, so it is not
  useful for doing any work in.

  ```
  $ rm -rf /tmp/myproject-dev
  ```

### 1. Create a working branch

To avoid any unintentional damage to the existing code base it is advised to
work in a temporary working branch.

```
# Clone the repository from Github.
$ git clone https://github.com/ec-europa/myproject-dev.git
# Create a temporary working branch.
$ git checkout -b convert-to-starterkit
```

### 2. Get the code

We'll add the Subsite Starterkit repository as a remote called 'starterkit', and
merge its code. This will import the starterkit code into your project.

```
$ git remote add starterkit https://github.com/ec-europa/subsite-starterkit.git
$ git fetch starterkit
$ git merge starterkit/master
```

Note that you might have to solve merge conflicts, especially if you are already
using Composer or Phing.

### 3. Create a build.properties file

Create a new file called `build.properties` in the root folder and put
properties in it that are unique to your project. You can copy any of the
properties of the `build.properties.dist` file to override them and then commit
the file. The settings will then take effect for all developers that work on the
project.

Some typical project specific settings are the site name, the install profile,
the modules to enable after installation, paths to ignore during coding
standards checks, the version of the platform to use etc.

Example file:

```
# The site name.
subsite.name = My Project

# The install profile to use.
platform.profile.name = multisite_drupal_standard

# Modules to enable after installation. Separate multiple modules with spaces.
subsite.install.modules = myproject_super_feature
```

### Update directory structure

Next you'll need to adapt your directory structure to the one used by the
starterkit. Note that most of these paths can be adapted to your liking by
finding the relevant properties in `build.properties.dist`, copying them to
`build.properties` and changing their values.

* Custom modules, features, themes and PHP code go in the `lib/` folder.
* The make file should be moved to `resources/site.make`.
* Your Behat tests go in the `tests/` folder. The starterkit uses a template
  file `tests/behat.yml.dist` to generate the Behat configuration so you might
  want to look into this to port your project specific Behat settings.
* If you have custom PHP CodeSniffer rules, put them in `phpcs-ruleset.xml`.

Finally commit your changes and test the build:

```
$ composer install
$ ./bin/phing build-dev
$ ./bin/phing install-dev
```
