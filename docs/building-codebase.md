# Building the codebase

<big><table><thead><tr><th nowrap> [Configuring a project](./configuring-project.md#configuring-a-project) </th><th width="100%" align="center"> [User guide](../README.md#user-guide) </th><th nowrap> [Installing a project](./installing-project.md#installing-the-project) </th></tr></thead></table>

There are three different types of codebases you can build.

**Functional codebase**: Build codebase by download of a release package.<br>
**Developer codebase**: Build codebase by symlinking to the `lib/` folder.<br>
**Distribution codebase**: Build codebase intended to be packaged for release.<br>

## Platform builds

<details><p><summary>execute <code>./toolkit/phing build-project-platform</code></summary></p>

This build downloads the latest released platform deploy package depending on
what version you specified as the platform.package.version property. After
download it unpacks it to the build folder. This build is mainly used by subsites
that need to test their code on a cetain platform version.
</p></details>

<!-- <details><p><summary>execute <code>composer create-project ec-europa/platform toolkit-demo dev-master</code></summary> 

Clones the repository with the master branch and runs composer install in the
root of the project. You will be prompted to keep or remove the version control
system before starting the installation.
</p></details>
<details><p><summary>execute <code>nano build.develop.props</code></summary>

Put the properties file in the root of your project and add the build properties
you wish to set. For more information on the list of available build properties
refer to the [build.default.props] file that is provided by the toolkit.
</p></details> -->
<details><p><summary>execute <code>./toolkit/phing build-platform-dev</code></summary></p>

Build the actual codebase. This action will transform your `lib/` and
`resources/` folder into a Drupal codebase which you can install. This action by
default will start by backing up any site specific files if there were any
present.

Currently platform-dev has not migrated to the toolkit yet for building their
codebase. This is a work in progress. If you are developing for a subsite this
part of the documentation does not apply to your codebase.
</p></details>
<!-- <details><p><summary>execute <code>./toolkit/phing build-project-subsite</code></summary>

This feature has not been implemented yet. It would allow platform developers
to install any subsite that is using the platform. To complete this feature, CI
needs to be fully implmented so subsites have a deploy package available for
download.
</p></details> -->
<details><p><summary>execute <code>./toolkit/phing build-platform-dist</code></summary></p>

Build the disstribution files for a single profile. You can change the profile
either through changing the property in your build properties file or through
defining it in the command itself with the `-D'profile'=` option.

This build will only build the files necessary for deployment. The result of the
build will be compressed and uploaded to github when your project gets tagged
for release. After the release this package will be available for download by
the `./toolkit/phing build-project-platform` command.
</p></details>

## Subsite builds

<details><p><summary>execute <code>./toolkit/phing build-project-subsite</code></summary></p>

This build downloads and unpacks the latest released subsite deploy package and
unpacks it to the `build/sites/default` folder. This build will be mainly used
by platform who need to test subsite configurations on their codebase.

This build will download the latest release package of the version you define in
your build properties file. Currently subsites are not yet using the release
assets for deployment so the command will not give you any code.
</p></details>

<!-- <details><p><summary>execute <code>composer create-project ec-europa/subsite toolkit-demo dev-master</code></summary>

Clones the repository with the master branch and runs composer install in the
root of the project. You will be prompted to keep or remove the version control
system before starting the installation.
</p></details>
<details><p><summary>execute <code>nano build.develop.props</code></summary>

Put the properties file in the root of your project and add the build properties
you wish to set. For more information on the list of available build properties
refer to the [build.default.props] file that is provided by the toolkit.
</p></details>
<details><p><summary>execute <code>./toolkit/phing build-project-platform</code></summary>

Downloads the platform package of which you defined the version in your build
properties. After succesful download it will unpack the package into the
`build/` folder of your project.
</p></details> -->
<details><p><summary>execute <code>./toolkit/phing build-subsite-dev</code></summary>

Builds all resources and symlinks the individual modules, themes and libraries
to their location in the lib/ folder. The `lib/` folder effectively becomes a
mirror of `build/sites/default`. 
</p></details>

<details><p><summary>execute <code>./toolkit/phing build-subsite-dist</code></summary></p>

Build the disstribution files for a single subsite. You can change the subsite
either through changing the property in your build properties file or through
defining it in the command itself with the `-D'subsite'=` option.

This build will only build the files necessary for deployment. The result of the
build will be compressed and uploaded to github when your project gets tagged
for release. After the release this package will be available for download by
the `./toolkit/phing build-project-subsite` command.
</p></details>