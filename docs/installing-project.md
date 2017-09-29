# Installing a project
This guide explains how to install a new project or how to 
clone your production website using the toolkit. This guide is intended to be
used in an unix environment.

## Clean installation
With toolkit you can install in few minutes a new project in order to start working
or test some new functionality. The process is quite simple and can be done in
few steps.

<big>
    <details>
    <summary>Create the project with composer</summary>
    <p>Toolkit provide a package to make all process easier</p>
    ```
    composer create-project ec-europa/subsite toolkit-demo dev-master
    ```
    </details>
</big>
<big>
    <details>
    <summary>Update the file `build.project.props` with the proper information</summary>
    <p>Toolkit provide a package to make all process easier</p>
    ```
    $ composer create-project ec-europa/subsite toolkit-demo dev-master
    ```

    <p>Example of build.project.props</p> 

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
</big>

<big>
    <details>
    <summary>Create a file named build.develop.props</summary>
    <p>this file should include your local environment
    information like: data connection, website url and others. This file should **never
    be commited to repository**, it is intended to hold private information that should
    not be shared.</p>

    ```
    project.url.base = http://vs-nxte-santosj.net1.cec.eu.int/coolsite

    db.password = <your-database-password-here>
    db.her create-project ec-europa/subsite toolkit-demo dev-master
    ```

    </details>
</big>

<big>
    <details>
    <summary>Build NextEuropa Platform</summary>
    <p>Toolkit provide a phing target to build platform</p>
    ```
    $ ./toolkit/phing build-project-platform
    ```
    </details>
</big>

<big>
    <details>
    <summary>Build the local development version of the site</summary>
    <p>Toolkit provide a phing target to build your project</p>
    ```
    $ ./toolkit/phing build-subsite-dev 
    ```
    </details>
</big>

<big>
    <details>
    <summary>Install NextEuropa site from scratch</summary>
    <p>Toolkit provide a phing target to install your drupal project</p>
    ```
    $ ./toolkit/phing install-project-clean
    ```
    </details>
</big>

Now put it all together and usiung terminal we got:
```bash
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

## Clone installation
