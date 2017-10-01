<big><table><thead><tr><th nowrap> [Setting up a project](./setting-up-project.md) </th><th width="100%" align="center"> [Home](../README.md) </th><th nowrap> [Building the codebase](./building-codebase.md) </th></tr></thead></table>

# Configuring a project

This guide explains the basic structure of the toolkit and which files can be
used in what context.

## Build properties

### build.default.props

The current toolkit is modular and saves different properties files in different
locations. At installation of the toolkit you will get an aggregated
[build.default.props] file copied to the root of your project to have easy
access to all possible conguration of the toolkit. This file is never loaded
into active configuration. To re-generate this file manually you can execute a
Phing target called toolkit-default-props.

### build.develop.props

This file is intended for local use only and needs to be included in the
[.gitignore] of your project. Here you can store any configuration that is
needed to run your project locally. Useful for storing credentials and 
individual preferences for development.

### build.project.props

In the previous release this file was called build.properties. To make it more
obvious it is essential to your project we have renamed it in such a way. In
this file there are a few properties that are required. The definitions of these
required properties you find in a file named [required.props].


## Build files

### build.xml

The [build.xml] file has be located in the root of your project. This file
should not be altered in any way. It contains the link to your toolkit and it is
the default file that Phing looks for when you execute it.

### buld.project.xml

If there is a need to customize certain build targets you can override them by
placing a [build.project.xml] file in the root of your project. Then it is just
a matter of re-using the name of the target you wish to alter and place your
custom logic there. Beware, overriding build targets can have unexpected
results if your project is running on a CI provider that has pipelines
especially constructed for toolkit builds.


### composer.json

The [composer.json] installs the toolkit by the use of the composer hooks. The
reason we do a seperated install is to avoid developers running composer update
on the toolkit. Now regardles of wether you run composer install or update, you
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
