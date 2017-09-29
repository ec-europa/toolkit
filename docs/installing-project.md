# Installing a project
This guide explains how to install a new project or how to 
clone your production website using the toolkit. This guide is intended to be
used in an unix environment.

## Clean installation
With toolkit you can install in few minutes a new project in order to start working
or test some new functionality. The process is quite simple and can be done in
few steps.


<details>
    <summary>execute <code>composer create-project</code></summary>
    <p>Toolkit provide a package to make all process easier</p>

```
composer create-project ec-europa/subsite toolkit-demo dev-master
```

</details>
<details>
    <summary>update the file <code>build.project.props</code></summary>
    <p>Toolkit provide a package to make all process easier</p>

<p>Example of build.project.props </p>

```
# Subsite configuration.
# ----------------------
project.id = toolkit-demo
project.name = Toolkit Demo
project.url.production = http://toolkit-demo.com

# Platform configuration.
# -----------------------
profile = multisite_drupal_standard
platform.package.version = 2.3
```

</details>
<details>
    <summary>create the file <code>build.develop.props</code></summary>
    <p>This file should include your local environment
    information like: data connection, website url and others. This file should <strong>never
    be commited to repository</strong>, it is intended to hold private information that should
    not be shared.</p>

```
project.url.base = http://vs-nxte-santosj.net1.cec.eu.int/coolsite

db.password = <your-database-password-here>
db.her create-project ec-europa/subsite toolkit-demo dev-master
```

</details>
<details>
    <summary>execute <code>./toolkit/phing build-project-platform</code></summary>
    <p>Toolkit provide a phing target to build platform</p>

```
$ ./toolkit/phing build-project-platform
```

</details>
<details>
    <summary>execute <code>./toolkit/phing build-subsite-dev</code></summary>
    <p>Toolkit provide a phing target to build your project</p>

```
$ ./toolkit/phing build-subsite-dev 
```

</details>
<details>
    <summary>execute <code>./toolkit/phing install-project-clean</code></summary>
    <p>Toolkit provide a phing target to install your drupal project</p>

```
$ ./toolkit/phing install-project-clean
```

</details>


<p>
&nbsp;

<p>Now put it all together and using terminal we got</p>

```
# Create the project from scratch in a folder name toolkit-demo
$> composer create-project ec-europa/subsite toolkit-demo dev-master
$> cd toolkit-devo

# Edit the file build.project.props to add the required information 
$> vim build.project.props

# Create the build.developer.props and add your local settings there
# touch build.develop.props
$> vim build.develop.props

# Build the platform, subsite and install it though phing
$> ./toolkit/phing build-project-platform build-subsite-dev install-project-clean
```
&nbsp;

## Clone installation
