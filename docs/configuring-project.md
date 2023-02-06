# Configuring a project

<big><table><thead><tr><th nowrap> [Setting up a project](./setting-up-project.md#setting-up-a-project) </th><th width="100%" align="center"> [User guide](../README.md#user-guide) </th><th nowrap> [Building the codebase](./building-codebase.md#building-the-codebase) </th></tr></thead></table>

## Build properties

This guide walks you through the different kind of build properties files and
what configuration belongs where. It is important that you choose the correct
file to store your configuration in. These files need to be located in the root
of your project.

### Default properties

This file is never loaded into active configuration and purely acts as an
overview of all build properties that are available to you.

<details><summary>Example of the <code>build.default.props</code> file</summary><p>

```yaml
# Toolkit location: ./includes/phing/build/boot.props
# -----------------------------------------------------------------------------------
# These are the toolkit paths that should not be altered. Altering paths here have a
# good chance of breaking things.
# -----------------------------------------------------------------------------------

# Toolkit directories.
# -----------------------

toolkit.dir = ${phing.dir.starterkit}
toolkit.dir.incl = ${toolkit.dir}/includes
toolkit.dir.incl.composer = ${toolkit.dir.incl}/composer
toolkit.dir.incl.docker = ${toolkit.dir.incl}/docker
toolkit.dir.incl.drush = ${toolkit.dir.incl}/drush
toolkit.dir.incl.phing = ${toolkit.dir.incl}/phing
toolkit.dir.incl.phing.build = ${toolkit.dir.incl.phing}/build
toolkit.dir.incl.phing.props = ${toolkit.dir.incl.phing}/props
toolkit.dir.incl.phing.src = ${toolkit.dir.incl.phing}/src
toolkit.dir.incl.templates = ${toolkit.dir.incl}/templates
toolkit.dir.vendor = ${toolkit.dir}/vendor

# Toolkit binaries.
# --------------------
toolkit.dir.bin = ${toolkit.dir}/bin
toolkit.dir.bin.drush = ${toolkit.dir.bin}/drush
toolkit.dir.bin.phing = ${toolkit.dir.bin}/phing


# Toolkit location: ./includes/phing/build/test/phpcs.props
# -----------------------------------------------------------------------------------
# PHPCS sprecific configuration
# -----------------------------------------------------------------------------------

# The file extensions to test.
# Delimited by space, comma or semicolon.
phpcs.extensions = php inc module install info test profile theme css js

# The default configuration file to generate.
phpcs.config = ${project.basedir}/phpcs.xml

# The locations for installed standards, delimited by comma.
phpcs.installed.paths = ${toolkit.dir.vendor}/ec-europa/qa-automation/phpcs/SubStandards

# The coding standards to enforce.
# Delimited by space, comma or semicolon..
phpcs.standards = Subsite;${project.basedir}/phpcs-ruleset.xml

# Paths to check, delimited by semicolons.
phpcs.files = ${resources.dir};${lib.dir}

# Paths to ignore, delimited by semicolons.
phpcs.ignore =

# Verbosity of PHP Codesniffer. Set to 0 for standard output, 1 for progress
# report, 2 for debugging info.
phpcs.verbose = 0

# Returns a 0 error code when only warnings are found if enabled. Ment for CI.
phpcs.passwarnings = 0

# The report format. For example 'full', 'summary', 'diff', 'xml', 'json'.
# Delimited by space, comma or semicolon.
phpcs.reports = summary

# Whether or not to show sniff codes in the report.
phpcs.sniffcodes = 0

# Whether or not to show the progress of the run.
phpcs.progress = 1

# The location of the file containing the global configuration options.
phpcs.global.config = ${toolkit.dir.vendor}/squizlabs/php_codesniffer/CodeSniffer.conf

# Whether or not to run a coding standards check before doing a git push. Note
# that this will abort the push if the coding standards check fails.
phpcs.prepush.enable = 1

# The source and destination paths of the git pre-push hook.
phpcs.prepush.source = ${toolkit.dir.vendor}/pfrenssen/phpcs-pre-push/pre-push
phpcs.prepush.destination = ${project.basedir}/resources/git/hooks/pre-push/phpcs


# Toolkit location: ./includes/phing/build/test/behat.props
# -----------------------------------------------------------------------------------
# Behat specific configuration
# -----------------------------------------------------------------------------------

# Browser name for selenium.
behat.browser.name = firefox

# Behat API driver.
behat.api.driver = drupal

# The location of the Behat tests.
behat.dir = ${project.basedir}/tests

# The location of the Behat executable.
behat.bin = ${behat.dir}/vendor/behat/behat/bin/behat

# The location of the Behat configuration template.
behat.yml.template = ${behat.dir}/behat.yml.dist

# The location of the generated Behat configuration file.
behat.yml.path = ${behat.dir}/behat.yml

# The base URL to use in Behat tests.
behat.base_url = http://web:8080

# A drush alias to run behat on.
behat.drush.alias = docker

# The URL of the Behat webdriver host.
behat.wd_host.url = http://selenium:4444/wd/hub

# The location to search for Behat subcontexts.
behat.subcontexts.path = ${build.platform.dir.profile}/modules

# The output format to use for Behat tests, either 'progress' or 'pretty'.
behat.formatter.name = progress

# Enable strict mode in Behat tests. Will only pass if all tests are explicitly
# passing.
behat.options.strict = true

# Proceed the build even after error.
behat.options.haltonerror = true

# Set verbosity for Behat tests. 0 is completely silent, 1 is normal output, 2
# shows exception backtraces, 3 shows debugging information.
behat.options.verbosity = 2

# Load balancer Phing task configuration.
behat.load_balancer.containers = 5
behat.load_balancer.root = ${behat.dir}
behat.load_balancer.destination = ${behat.dir}/balancer
behat.load_balancer.import = ${behat.yml.path}


# Toolkit location: ./includes/phing/build/test/phpunit.props
# -----------------------------------------------------------------------------------
# PHPUnit sprecific configuration
# -----------------------------------------------------------------------------------

# The location of the PHPUnit executable.
phpunit.bin = ${tests.dir}/bin/phpunit

# The location of the PHPUnit configuration files.
phpunit.dir = ${tests.dir}

# The location of the PHPUnit configuration template.
phpunit.xml.template = ${phpunit.dir}/phpunit.xml.dist

# The location of the generated Behat configuration file.
phpunit.xml.path = ${phpunit.dir}/phpunit.xml

# The base URL to use in PHPUnit tests.
phpunit.base_url = ${behat.base_url}


# Toolkit location: ./includes/phing/props/main.props
# -----------------------------------------------------------------------------------
# The main properties of the toolkit. Most of them are build and development related.
# -----------------------------------------------------------------------------------

# Binaries.
# ---------
project.bin.composer = composer.phar
project.bin.git = git

# Temporary folders and resources.
# --------------------------------
project.docroot = /var/www/html
project.tmp.devel.make = ${project.tmp.dir}/devel.make
project.tmp.dir = ${project.basedir}/.tmp

# Subsite configuration.
# ----------------------
project.id = myproject
project.install.modules = myproject_core
project.name = My Project
project.theme.default = ec_resp
project.type = subsite
project.url.base = http://web:8080
project.url.production =

# Development modules.
# --------------------
devel.mdls.dir = devel
devel.mdls.dl = devel maillog stage_file_proxy
devel.mdls.en = devel context field_ui maillog stage_file_proxy views_ui

# Development variables.
# ----------------------
devel.vars.error_level = 2
devel.vars.stage_file_proxy_hotlink = 1
devel.vars.stage_file_proxy_origin = https://ec.europa.eu/${project.id}
devel.vars.stage_file_proxy_origin_dir = sites/${project.id}/files
devel.vars.views_show_additional_queries = 1
devel.vars.views_ui_show_performance_statistics = 1
devel.vars.views_ui_show_sql_query = 1

# Debugging configuration.
# ------------------------
drush.color = 1
drush.verbose = FALSE

# Docker. (TODO)
# -------
docker.project.id = environment

# Database download settings.
# ---------------------------
db.dl.filename =
db.dl.dir = fpfis/files-for/automate_dumps
db.dl.host = webgate.ec.europa.eu
db.dl.url = ${db.dl.host}/${db.dl.dir}/${project.id}/
db.dl.username =
db.dl.password =

# Database connection settings.
# -----------------------------
db.type = mysql
db.name = ${project.id}
db.user = root
db.password =
db.host = mysql
db.port = 3306
db.url = ${db.type}://${db.user}:${db.password}@${db.host}:${db.port}/${db.name}

# Solr configuration.
# -------------------
solr.enable = 1
solr.scheme = http
solr.host = localhost
solr.port = 8983
solr.path = /solr
solr.type = d7_apachesolr
solr.url = ${solr.scheme}://${solr.host}:${solr.port}${solr.path}/${solr.type}

# Admin configuration.
# --------------------
admin.email = ${admin.username}@example.com
admin.password = pass
admin.username = admin

# Platform configuration. (deploy props?)
# -----------------------
profile = multisite_drupal_standard
profile.core = 7.x
profile.core.make = ${resources.dir}/drupal-core.make
profile.make = ${resources.dir}/${profile}.make
platform.package = deploy-package-${platform.package.reference}.tar.gz
platform.package.db.cache = 1
platform.package.provider = git-hub
platform.package.provider.token = # TODO: Github API limit.
platform.package.repository = ec-europa/platform-dev
platform.package.version = 2.5

# Theme configuration (deploy props?)
# --------------------
theme.ecl.version = v0.10.0
theme.ec_europa.version = 0.0.3
theme.atomium.repo.url = https://github.com/ec-europa/atomium.git
theme.atomium.repo.branch = 7.x-1.x
theme.europa.repo.url = https://github.com/ec-europa/ec_europa.git
theme.europa.repo.branch = master

# Project resources.
# ------------------
lib.dir = ${project.basedir}/lib
lib.dir.libraries = ${lib.dir}/libraries
lib.dir.modules = ${lib.dir}/modules
lib.dir.modules.custom = ${lib.dir.modules}/custom
lib.dir.modules.features = ${lib.dir.modules}/features
lib.dir.profiles = ${lib.dir}/profiles
lib.dir.profiles.profile = ${lib.dir.profiles}/${profile}
lib.dir.source = ${lib.dir}/src
lib.dir.themes = ${lib.dir}/themes

resources.dir = ${project.basedir}/resources
resources.dir.composer.json = ${resources.dir}/composer.json
resources.dir.composer.lock = ${resources.dir}/composer.lock
resources.dir.favicon.ico = ${resources.dir}/favicon.ico
resources.dir.devel.make = ${resources.dir}/devel.make
resources.dir.site.make = ${resources.dir}/site.make

tests.dir = ${project.basedir}/tests

# Build folders.
# --------------
build.dev = build
build.dist = dist
build.site = default
#build.site = ${project.id}

Build halts.
# ----------
build.haltonerror.dir.copy = true
build.haltonerror.props.validate = false

# Platform build resources.
# -------------------------
build.platform.dir = ${project.basedir}/${build.dev}
build.platform.dir.settings = ${build.platform.dir.sites}/default
build.platform.dir.sites = ${build.platform.dir}/sites
build.platform.dir.profile = ${build.platform.dir.profiles}/${profile}
build.platform.dir.profile.themes = ${build.platform.dir.profile}/themes
build.platform.dir.profiles = ${build.platform.dir}/profiles
build.platform.composer.json = ${build.platform.dir}/composer.json
build.platform.composer.lock = ${build.platform.dir}/composer.lock
build.platform.favicon.ico = ${build.platform.dir}/favicon.ico
build.platform.htaccess.append.text =

# Subsite build resources.
# ------------------------
build.subsite.composer.json = ${build.subsite.dir}/composer.json
build.subsite.composer.lock = ${build.subsite.dir}/composer.lock
build.subsite.dir = ${build.platform.dir.sites}/${build.site}
build.subsite.dir.files = ${build.subsite.dir}/files
build.subsite.dir.libraries = ${build.subsite.dir}/libraries
build.subsite.dir.modules = ${build.subsite.dir}/modules
build.subsite.dir.modules.contrib = ${build.subsite.dir.modules}/contrib
build.subsite.dir.modules.custom = ${build.subsite.dir.modules}/custom
build.subsite.dir.modules.features = ${build.subsite.dir.modules}/features
build.subsite.dir.source = ${build.subsite.dir}/src
build.subsite.dir.themes = ${build.subsite.dir}/themes
build.subsite.dir.tmp = ${build.subsite.dir}/tmp

# platform build files and directories.
# -----------------------------------------
build.dist.composer.json = ${build.dist.dir}/composer.json
build.dist.composer.lock = ${build.dist.dir}/composer.lock
build.dist.dir = ${project.basedir}/${build.dist}
build.dist.dir.modules = ${build.dist.dir}/modules
build.dist.dir.modules.custom = ${build.dist.dir.modules}/custom
build.dist.dir.modules.features = ${build.dist.dir.modules}/features
build.dist.dir.profile = ${build.dist.dir.profiles}/${profile}
build.dist.dir.profiles = ${build.dist.dir}/profiles
build.dist.dir.source = ${build.dist.dir}/src
build.dist.dir.themes = ${build.dist.dir}/themes

# Rebuild configuration.
# ----------------------
rebuild.auto = 1
rebuild.backup.destination = ${project.tmp.dir}/backup-site
rebuild.backup.files = ${build.subsite.dir}/settings.php
rebuild.backup.folders = ${build.subsite.dir.files};${build.subsite.dir.tmp}

# Shared paths.
# -------------
share.path = /cache
share.name = share
share.path.global = ${share.path}/${share.name}
share.path.composer = ${share.path.global}/composer
share.path.platform = ${share.path.global}/platform
share.path.subsites = ${share.path.global}/subsites
share.path.composer.packages = ${share.path.composer}/packages
share.path.composer.packages.shared = ${share.path.composer.packages}/shared
share.path.platform.packages = ${share.path.platform}/packages
share.path.platform.packages.database = ${share.path.platform.packages}/database
share.path.platform.packages.deploy = ${share.path.platform.packages}/deploy
share.path.platform.packages.test = ${share.path.platform.packages}/test
share.path.subsites.packages = ${share.path.subsites}/packages
share.path.subsites.packages.database = ${share.path.subsites.packages}/database
share.path.subsites.packages.deploy = ${share.path.subsites.packages}/deploy
share.path.subsites.packages.test = ${share.path.subsites.packages}/test

# Composer hook phingcall target lists. Space separated only.
# -----------------------------------------------------------
composer.hook.post.install = build-toolkit
composer.hook.post.update =
composer.hook.pre.install =
composer.hook.pre.update =

# Git hook phingcall target lists. Space separated only.
# -----------------------------------------------------------
git.hook.applypatch.msg =
git.hook.post.update =
git.hook.pre.commit =
git.hook.pre.push =
git.hook.pre.receive =
git.hook.commit.msg
git.hook.pre.applypatch =
git.hook.prepare.commit.msg =
git.hook.pre.rebase =
git.hook.update =

# Flickr configuration.
# ---------------------
flickr.key = foobar
flickr.secret = bas

# Integration configuration.
# --------------------------
integration.server.port = 8888

# Varnish configuration.
# ----------------------
varnish.server.port = 8888

# Drush Context configuration.
# ----------------------------
drush.db.dump = ${build.platform.dir}/dump.sql
```
</p></details>

### Development properties

In this file you can define properties that are specific to your local 
development environment. This file may not be committed into the repository.

<details><summary>Example of a <code>build.develop.props</code> file</summary><p>

```yaml
# Development modules.
# --------------------
devel.mdls.dir = devel
devel.mdls.en = devel context field_ui maillog simpletest stage_file_proxy views_ui

# Development variables.
# ----------------------
devel.vars.error_level = 2
devel.vars.views_show_additional_queries = 1
devel.vars.views_ui_show_performance_statistics = 1
devel.vars.views_ui_show_sql_query = 1

# Database download settings.
# ---------------------------
db.dl.username = myusername
db.dl.password = mypassword


# Database connection settings.
# -----------------------------
db.name = ${project.id}
db.user = root
db.password = mypassword
db.host = localhost
db.port = 3306
```
</p></details>

### Project properties

In this file you should only define properties that are specific to the project.
It also has a number of required properties that you can find in the file named
[required.props].

<details><summary>Example of a <code>build.project.props</code> file</summary><p>

```yaml
# Subsite configuration.
# ----------------------
project.id = myproject
project.install.modules = myproject_core
project.name = My Project
project.theme.default = ec_resp
project.url.production = https://myproject.com

# Platform configuration.
# -----------------------
profile = multisite_drupal_standard
platform.package.version = 2.5
```
</p></details>


## Build files

These are important files for your project to connect to the toolkit. These
files contain part that should not be edited, find more information in the
description of the files.

### build.xml

The [build.xml] file has be located in the root of your project, this file
should not be altered in any way. It contains the link to your toolkit and it is
the default file that Phing looks for when you execute it.

### build.project.xml

If there is a need to customize certain build targets you can override them by
placing a [build.project.xml] file in the root of your project. Then it is just
a matter of re-using the name of the target you wish to alter and place your
custom logic there. Beware, overriding build targets can have unexpected
results if your project is running on a CI provider that has pipelines
especially constructed for toolkit builds.

### composer.json

The [composer.json] installs the toolkit by the use of the composer hooks. The
reason we do a separated install is to avoid developers running composer update
on the toolkit. Now regardless of wether you run composer install or update, you
will always install the toolkit as it is defined in its own [composer.lock]
file. For a clearer picture here is an example of the resulting folder structure
after installing a toolkit.

<big><pre><code>.
├── lib
├── resources
├── tests
└── toolkit -> **vendor/ec-europa/toolkit/bin**: easy access binary
└── **vendor**: project installs
&nbsp;&nbsp;&nbsp;&nbsp;└── **ec-europa**
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└── **toolkit**
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├── bin
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├── docs
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├── includes
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└── **vendor**: isolated toolkit install
</pre></code></big>

[build.default.props]: ../build.default.props
[build.project.xml]: ../includes/templates/subsite/build.project.xml
[build.xml]: ../build.xml
[composer.json]: ../includes/templates/subsite/composer.json
[composer.lock]: ../includes/composer/composer.lock
[.gitignore]: ../includes/templates/subsite/.gitignore
[required.props]: ../includes/phing/props/required.props


## Cache system

In order to speed up your builds the toolkit provides a caching system.

### Configure cache

#### Global cache
Toolkit stores files to be shared accross all your projects. This allows you to
skip platform downloads and installations. The location of the global cache can
be configured through:

<details><summary>execute <code>nano build.develop.props</code></summary><p>

```
# Shared paths.
# -------------
share.path = /tmp
share.name = toolkit
```
</p></details>

#### Local cache
Toolkit stores files that are specific to the project itself inside a folder
located within the project. The location of the local cache can be configured
through:

<details><summary>execute <code>nano build.develop.props</code></summary><p>

```
# Temporary folders and resources.
# --------------------------------
project.tmp.dir = ${project.basedir}/.tmp
```
</p></details>

### Clearing caches
Ìf you are having issues with caching you can clear the entire cache with:

<details><summary>execute <code>./toolkit/phing cache-clear-all</code></summary><p>

```
Buildfile: /home/user/github/ec-europa/project-id/build.xml
 [property] Loading /home/user/github/ec-europa/project-id/includes/phing/build/boot.props
 [property] Loading /home/user/github/ec-europa/project-id/build.develop.props
 [property] Loading /home/user/github/ec-europa/project-id/build.project.props
 [property] Loading /home/user/github/ec-europa/project-id/.tmp/build.version.props
     [echo] Global share directory /tmp/toolkit available.
     [echo] Temporary directory /home/user/github/ec-europa/project-id/.tmp available.

core > cache-clear-global:

   [delete] Deleting directory /tmp/toolkit

core > cache-clear-local:

   [delete] Deleting directory /home/user/github/ec-europa/project-id/.tmp

core > cache-clear-all:


BUILD FINISHED

Total time: 0.6896 seconds
```
</p></details>

If you only want to clear global or local cache you can use these commands:

<details><summary>execute <code>./toolkit/phing cache-clear-global</code></summary><p>

```
Buildfile: /home/user/github/ec-europa/project-id/build.xml
 [property] Loading /home/user/github/ec-europa/project-id/includes/phing/build/boot.props
 [property] Loading /home/user/github/ec-europa/project-id/build.develop.props
 [property] Loading /home/user/github/ec-europa/project-id/build.project.props
 [property] Loading /home/user/github/ec-europa/project-id/.tmp/build.version.props
     [echo] Global share directory /tmp/toolkit available.
     [echo] Temporary directory /home/user/github/ec-europa/project-id/.tmp available.

core > cache-clear-global:

   [delete] Deleting directory /tmp/toolkit


BUILD FINISHED

Total time: 0.6896 seconds
```
</p></details>
<details><summary>execute <code>./toolkit/phing cache-clear-local</code></summary><p>

```
Buildfile: /home/user/github/ec-europa/project-id/build.xml
 [property] Loading /home/user/github/ec-europa/project-id/includes/phing/build/boot.props
 [property] Loading /home/user/github/ec-europa/project-id/build.develop.props
 [property] Loading /home/user/github/ec-europa/project-id/build.project.props
 [property] Loading /home/user/github/ec-europa/project-id/.tmp/build.version.props
     [echo] Global share directory /tmp/toolkit available.
     [echo] Temporary directory /home/user/github/ec-europa/project-id/.tmp available.

core > cache-clear-local:

   [delete] Deleting directory /home/user/github/ec-europa/project-id/.tmp


BUILD FINISHED

Total time: 0.6896 seconds
```
</p></details>
