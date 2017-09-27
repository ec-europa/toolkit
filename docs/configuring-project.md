# Configuring a project

This guide explains the basic structure of the toolkit and goes more
in depth into the installation process.

## Installation explained

The toolkit requires 3 files to be present in your repository that make
a reference to the toolkit or visa versa. The three files are:

### composer.json

This file<sup>(1)</sup> installs the toolkit by the use of the composer
post-install-cmd hook. The reason we do a seperated install is to avoid
developers running composer update on the toolkit. Now regardles of
wether you run composer install or update, you will always install the
toolkit as it is defined in its own composer.lock file. For a clearer
picture look at the folder structure<sup>(2)</sup>.

<details><summary><b>View source <sup>(1)</sup></b></summary>

```json
{
    "name": "ec-europa/project-id",
    "require": {
        "ec-europa/toolkit": "~3.0.0"
    },
    "scripts": {
        "post-install-cmd": "@toolkit-install",
        "post-update-cmd": "@toolkit-install",
        "toolkit-install": "PROJECT=$(pwd) composer run-script toolkit-install -d ./vendor/ec-europa/toolkit"
    }
}
```
</details>
<details><summary><b>View folder structure <sup>(2)</sup></b></summary>

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
</details>

### build.xml

The build.xml file should be located in the root of your project. And
this file should not be altered in any way. If you need to override
targets within the toolkit you can create a **build.project.xml** file.

<details><summary><b>View source <sup>(1)</sup></b></summary>

```xml
<?xml version="1.0" encoding="UTF-8" ?>

<project name="root" description="The link between your project and toolkit." default="">

    <property name="toolkit.dir" value="${project.basedir}/vendor/ec-europa/toolkit" />
    <import file="${toolkit.dir}/includes/phing/build.xml" />

</project>
```
</details>


### build.project.props

This is the previous build.properties file. To make it more obvious it
is required to build your project we have renamed it in such a way. In
this file there are a few properties that should always be declared.