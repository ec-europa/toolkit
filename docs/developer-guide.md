## Developer guide

### 1. Download the project

First we obviously need to get hold of the project source code. For most
projects the "dev" version will be hosted on Github, but individual projects
might be hosted elsewhere. Your project manager will be able to inform you of
the exact location.

> Note that you will not be working directly on the Subsite Starterkit code base
> (aka `ec-europa/subsite-starterkit`), but on a project specific fork.

Let's assume our project is called `ec-europa/myproject` and is hosted on our
Github. We will be working on the "dev" version.

```
$ git clone https://github.com/ec-europa/myproject-dev.git
$ cd myproject-dev
```

### 2. Install dependencies

The software packages that are needed to build the project are installed with
[Composer](https://getcomposer.org/). See the documentation for Composer for
more information on how to install and use it.

```
$ composer install
```

### 3. Create a build.properties.local file

This file contains the configuration which is unique to your local environment,
i.e. your development machine. In here you specify your database credentials,
your base URL, the administrator password etc.

Some other things that can be useful is to provide a list of your favourite
development modules to enable, and whether you want to see verbose output of
drush commands and tests. Another good trick is that you can try out your
project against different versions of the Multisite / NextEuropa platform, for
example you might want to check out if your code still runs fine on the latest
development version by setting `platform.package.reference` to `develop`.

> Because these settings are personal they should not be shared with the rest of
> the team. *Make sure you never commit this file!*

Example `build.properties.local` file:

```
# Database settings.
drupal.db.name = my_project
drupal.db.user = root
drupal.db.password = hunter2

# Admin user.
drupal.admin.username = admin
drupal.admin.password = admin

# Development / testing modules to download.
development.modules.download = coffee devel maillog

# Development / testing modules to enable.
drupal.development.modules = coffee devel field_ui maillog simpletest views_ui

# The base URL to use in Behat tests.
behat.base_url = http://myproject.local/

# The location of the Composer executable.
composer.bin = /etc/bin/composer

# Verbosity of drush commands. Set to TRUE to be verbose.
drush.verbose = FALSE

# Verbosity of PHP Codesniffer. Set to 0 for standard output, 1 for progress
# report, 2 for debugging info.
phpcs.verbose = 2

# Set verbosity for Behat tests. 0 is completely silent, 1 is normal output, 2
# shows exception backtraces, 3 shows debugging information.
behat.options.verbosity = 3
```

### 4. Build your local development environment

Now that our configuration is ready, we can start downloading everything we need
to build our Drupal site.

```
$ ./bin/phing build-dev
```

This will:

* Download the latest validated version of the platform of the branch you
  specified.
* Build the project into the `platform/` subfolder.
* Symlink your custom modules and themes into the platform. This allows you to
  work inside the Drupal site, and still commit your files easily.
* Run `composer install` in the Subsite development directory
  (typically `platform/sites/all`).

> Go get some coffee. The first time you build the site this can take a very
> long time. Future builds will be quicker since the platform will be cached and
> does not have to be downloaded every time.

### 5. Clone a production site.

```
$ ./bin/phing build-clone
```

This will:

* Re-generate the settings.php with addition of development variables.
* Download a sanitized production database if provided in build.properties.
* Drop the current database and import the downloaded one.
* Rebuild the registry, run updates and clear caches.

This target can only be run after running a successful build-dev first.
If no default.settings.php is found you will be prompted for executing
a rebuild.

### 6. Set up your webserver

The Drupal site will now be present in the `platform/` folder. Set up a
virtualhost in your favourite web server (Apache, Nginx, Lighttpd, ...) and
point its webroot to this folder.

If you intend to run Behat tests then you should put the base URL you assign to
your website in the `build.properies.local` file for the `behat.base_url`
property. See the example above.

### 7. Install the site

```
$ ./bin/phing install-dev
```

This will:

* Install the website with `drush site-install`.
* Enable the modules you specified in the `drupal.development.modules` property.

After a few minutes this process should complete and the website should be up
and running!

### 8. Run tests

Behat has been preconfigured in the `tests/` folder and an example test and
some example Contexts are supplied. Feel free to change them to your needs.
A symlink to the Behat executable is placed in the `tests/` folder for your
convenience.

```
$ cd tests/
$ ./behat
```

### 9. Check coding standards

#### Automated coding standards check on git push

PHP CodeSniffer is preconfigured with the latest Drupal coding standards
courtesy of the Coder module. A "git pre-push hook" is installed that will
automatically check the coding standards whenever you push your branch.  When
you are pushing new code to an existing branch only the changed files will be
checked to increase performance. When you push an entirely new branch a
complete check will be performed.

This is intended to help you find coding standards violations before your work
is pushed. The Continuous Integration service will fail any build containing
coding standards violations, so it's better to catch them early.

If you want to disable this pre-push hook, for instance because you are under
time pressure and need to push your code urgently to switch to a different task,
you can run the following Phing target:

```
$ ./bin/phing disable-pre-push
```

This will disable the automated check and you will be able to push freely. The
CI server will still fail any build that doesn't follow coding standards, but at
least your work is now safely pushed to the repository.

To re-enable the pre-push hook:

```
$ ./bin/phing setup-php-codesniffer
```

You can also prevent the pre-push hook from being activated on new builds by
setting the following property to 0 in your `build.properties.local`:

```
phpcs.prepush.enable = 1
```

This is not recommended however, the CI server will still reject any code that
has coding standards violations.


#### Manual coding standards check

Make sure you have the latest phpcs ruleset by executing:

```
$ ./bin/phing setup-php-codesniffer
```

This will generate a new phpcs.xml file that includes other rulesets such as
[phpcs-ruleset.xml](../phpcs-ruleset.xml) and [resources/phpcs-custom.xml](../resources/phpcs-custom.xml)
This documentation page [phpcs-index.md](phpcs-index.md) contains an overview
with the current enabled sniffs codes and will be updated with each release.

To manually check the coding standards you can run PHP CodeSniffer from the
root folder:

```
$ ./bin/phpcs
```

In many cases PHP CodeSniffer can fix the found violations automatically. To do
this, execute the following command from the root folder:

```
$ ./bin/phpcbf
```

If you need to tweak the coding standards (for example because you have created
a Views plugin which has class names that do not strictly respect the coding
standards) then you can put your additional rules in the `phpcs-ruleset.xml`
file. Please refer to the documentation of PHP CodeSniffer on how to configure
the rules.

#### QA automation tool

Before each pull request to a reference repository of ec-europa you need to run the
QA automation tool. This will perform static code checks on the directories defined
in your build properties as phpcs.files. The tool is included in the starterkit as
a composer dependency. More information on the tool can be found at 
https://github.com/ec-europa/qa-automation.

```
$ ./bin/phing mjolnir
```

You can also run commands of the qa-automation individually by calling ./bin/qa
directly.

### 10. Create a pull request

When your code is ready for review, you should create a pull request. The code
can be considered ready if:

* The functionality has been fully implemented.
* Test coverage for all end user facing functionality has been provided as a
Behat test scenario.

Creating a pull request happens in five steps:

1. **Create a fork**: The first (or actually zeroth) step is to create a fork of
your project in your own profile. If your project is called `myproject`, the
official repository will be called `myproject-reference` and you can find it at
https://github.com/ec-europa/myproject-reference. In the top right you can find
a "Fork" button. Click it to create a fork. You will only need to do this once.
When the fork is set up you can use it for all your pull requests in the future.
Make sure you are using this fork to push your code to. You can find information
on how to do this in the Github documentation:
[Fork A Repo](https://help.github.com/articles/fork-a-repo/).
2. **Create a ticket in Jira**: As usual each feature request and bug fix needs a
ticket in Jira for the QA team. You can create one at the
[MULTISITE project](https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE/)
in Jira. Your ticket will get a number such as MULTISITE-12345. Write this down.
3. **Create a feature branch**: In your local development repository, create a
feature branch. The ticket number should be part of the name of the branch so
we can always find the relevant information in Jira. If you are submitting a
bugfix, call the branch `bugfix/MULTISITE-12345`. If it is a new feature it
shall be named `feature/MULTISITE-12345`. Push it to your fork. For more
detailed information on this step, see [Creating and deleting branches within
your repository](https://help.github.com/articles/creating-and-deleting-branches-within-your-repository/).
4. **Create the pull request**: Now you are ready to create the pull request.
Go to the homepage of your fork and click on "Branches", then click the "New
pull request" button next to your branch. Make sure to mention your Jira ticket
number in the title of the pull request. Full instructions for this step can be
found in [Using pull requests](https://help.github.com/articles/using-pull-requests/).
5. **Link the PR in your Jira ticket**: Finally, go back to the Jira ticket and
post a link to the PR in the ticket, so that the QA engineer can find it.

The code will now be automatically tested: the Behat test scenarios will be
executed, the coding standards will be checked and an automated QA checklist
will be run. You will be able to see the result of this automated check in your
pull request as soon as this process is finished.

### 11. Working with Composer

The Subsite Starterkit comes with full Composer support. Subsite dependencies
are specified in `resources/composer.json`.

It is important to notice that `resources/composer.json` is a carbon-copy of
the platform's one, as it contains platform-level dependencies. Simply add your
dependencies next to those already specified in `resources/composer.json`.

The NextEuropa platform team might update the core `composer.json`; when that
happen they will take care of updating the Subsite Starterkit accordingly.
It will be your responsibility, as Subsite developer, to manually merge the two.

Also, after modifying `resources/composer.json` and running `composer update`
in the current Subsite development directory (typically `platform/sites/all`)
you need to push `resources/composer.lock` changes in order to share them with
your team members.
