# Configuring a project

This guide explains the basic structure of the toolkit and goes more
in depth into the installation process.

## Installation explained

The toolkit requires 3 files to be present in your repository that make
a reference to the toolkit or visa versa.

* composer.json
* build.xml
* build.project.props

### composer.json

The [composer.json] installs the toolkit by the use of the composer
hooks. The reason we do a seperated install is to avoid developers
running composer update on the toolkit. Now regardles of wether you run
composer install or update, you will always install the toolkit as it is
defined in its own composer.lock file. For a clearer picture here is an
example of the resulting folder structure:

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

### build.xml

The [build.xml] file has be located in the root of your project. This
file should not be altered in any way. If you need to override targets
within the toolkit you can also create a [build.project.xml] file in the
root of your project. For more on this check out the section
[Using build xml files](#using-build-xml-files).


## Using build properties

### build.default.props

The current toolkit is modular and saves different properties files in
different locations. At installation of the toolkit you will get an
aggregated [build.default.props] file copied to the root of your project
to have easy access to all possible conguration of the toolkit. This file
is never loaded into active configuration. To re-generate this file
manually you can execute a Phing target called toolkit-default-props.

### build.develop.props

This file is intended for local use only and needs to be included in the
[.gitignore] of your project. Here you can store any configuration that is
needed to run your project locally. Useful for storing credentials and
individual preferences for development.

### build.project.props

In the previous release this file was called build.properties. To make
it more obvious it is essential to your project we have renamed it in
such a way. In this file there are a few properties that are required.
The definitions of these required properties you find in a file named
[required.props].


## Using build xml files

To list all available targets defined in the toolkit you simply have to
execute phing. This will display the targets per file.

[build.default.props]: ../build.default.props
[build.project.xml]: ../includes/templates/subsite/build.project.xml
[build.xml]: ../build.xml
[composer.json]: ../includes/templates/subsite/composer.json
[composer.lock]: ../includes/composer/composer.lock
[.gitignore]: ../includes/templates/subsite/.gitignore
[required.props]: ../includes/phing/props/required.props