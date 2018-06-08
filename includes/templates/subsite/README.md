# ${PROJECT_DESCRIPTION}

[![Build Status](https://drone.fpfis.eu/api/badges/ec-europa/${PROJECT_ID}-reference/status.svg)](https://drone.fpfis.eu/ec-europa/${PROJECT_ID}-reference) [![Website ${PRODUCTION_URL}](https://img.shields.io/website-up-down-green-red/http/shields.io.svg)](${PRODUCTION_URL})

## 1. Documentation resources

> Please read all documentation resources before starting work on this project.
> Make sure that with each code delivery you have updated the documentation
> accordingly.

### 1.1 Project resources

* https://github.com/ec-europa/toolkit
* https://github.com/ec-europa/platform-dev
* https://github.com/ec-europa/ec-reps-platform

### 1.2 Delivery procedures

* [FPFIS Wiki - Delivery and QA procedure](https://webgate.ec.europa.eu/fpfis/wikis/display/MULTISITE/Delivery+and+QA+procedure)
* [FPFIS Wiki - Guide into code reviewing for Drupal 7](https://webgate.ec.europa.eu/fpfis/wikis/display/MULTISITE/Guide+into+code+reviewing+for+Drupal+7)

## 2. Installation procedures

### 2.1 Setup project

<details><summary>Execute: <code>git clone git@github.com:ec-europa/${PROJECT_ID}-reference.git</code></summary></details>
<details><summary>Execute: <code>cd ${PROJECT_ID}-reference</code></summary></details>
<details><summary>Execute: <code>composer install</code></summary></details>

### 2.2 Clean installation

<details><summary>Edit file: <code>build.develop.props</code></summary><p>
Depending on your environment you need to set the following properties.
Connection settings or secrets should never be committed or pushed to GitHub!

```ini
project.url.base = http://localhost
solr.host = 127.0.0.1
mysql.host = 127.0.0.1
```
</p></details>

<details><summary>Execute: <code>./toolkit/phing build-platform build-subsite-dev install-clean</code></summary></details>

### 2.3 Clone installation

<details><summary>Edit file: <code>build.develop.props</code></summary><p>
Depending on your environment you need to set the following properties.
Connection settings or secrets should never be committed or pushed to GitHub!

```ini
db.dl.username = <project-id>
db.dl.password = <password>
```
</p></details>
<details><summary>Execute: <code>./toolkit/phing build-platform build-subsite-dev install-clone</code></summary></details>

### 2.4 Upgrade platform

<details><summary>Edit file: <code>build.develop.props</code></summary><p>
Change the build property `platform.package.version` to the next major version.

```ini
platform.package.reference = 2.5
```
</p></details>
<details><summary>Execute: <code>./toolkit/phing build-platform</code></summary></details>
<details><summary>Execute: <code>./toolkit/drush -r build updb -y</code></summary></details>
