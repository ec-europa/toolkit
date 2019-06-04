# Installing the project

<big><table><thead><tr><th nowrap> [Configuring a project](./configuring-project.md#configuring-a-project) </th><th width="100%" align="center"> [User guide](../README.md#user-guide) </th><th nowrap> [Testing a project](./testing-project.md#testing-the-project) </th></tr></thead></table>

This guide explains how to install a new project or how to clone your production
environment with the import of a database. These installations require a Drupal
codebase to be present. In the default subsite workflow you achieve this with
the following commands:

* execute `./toolkit/phing build-platform`
* execute `./toolkit/phing build-subsite-dev`

## Clean installation
A clean installation means that you will run through the whole Drupal
installation process. To get a good end result here, your project needs to be
have all installation steps written into the module and feature install files.
Before executing the command make sure you have defined the build properties
needed to install a project:

<details><summary>execute <code>nano build.develop.props</code></summary><p>

```
# Subsite configuration.
# ----------------------
project.id = myproject
project.install.modules = myproject_core
project.name = My Project
project.theme.default = ec_resp

# Database connection settings.
# -----------------------------
db.type = mysql
db.name = ${project.id}
db.user = root
db.password =
db.host = 127.0.0.1
db.port = 3306
```
</p></details>
<details><summary>execute <code>./toolkit/phing install-clean</code></summary><p>

This target will install your site from scratch and by default it will save the
database right after install to cache it. That way on a future build with the
same platform version you will skip a part of the installation process.
</p></details>

## Clone installation
A clone installation means that you will be importing a sanitized database dump
from production to mirror the current production as well as you can. This is
very useful for debugging.

<details><summary>execute <code>nano build.develop.props</code></summary><p>

```
# Subsite configuration.
# ----------------------
project.id = myproject
project.install.modules = myproject_core
project.name = My Project
project.theme.default = ec_resp

# Database connection settings.
# -----------------------------
db.type = mysql
db.name = ${project.id}
db.user = root
db.password =
db.host = 127.0.0.1
db.port = 3306

# Database download settings.
# ---------------------------
db.dl.username =
db.dl.password =
```
</p></details>
<details><summary>execute <code>./toolkit/phing install-clone</code></summary><p>

Toolkit provide a phing target to clone your subsite project, please refer to
targets documentation get more details.</p>
</details>
