# Building the codebase

There are two different ways for building a codebase.

1. Build codebase without VCS by downloading a deploy package.
2. Build codebase with VCS by symlinking correct locations to the lib/ folder.

The build targets for the first option are provided by the **project.xml**. Each
target is prefixed with **build-project-<type>**. Currently the starterkit
supports three different types: platform, subsite and theme builds.

## Platform builds

Currently platform-dev has not migrated to the toolkit yet for building their
codebase. This is a work in progress.

<details>
    <summary>execute <code>composer create-project ec-europa/platform toolkit-demo dev-master</code></summary> 
    <p>Clones the platform template repository with the master branch and runs
    composer install.</p>
</details>

<details><summary>execute <code>nano build.develop.props</code></summary>
    <p>Provide the build properties for the project you wish to build.</p>
</details>

<details><summary>execute <code>./toolkit/phing build-platform-dev</code></summary>
    <p>Build the actual codebase.</>
</details>

<details><summary>execute <code>./toolkit/phing build-project-subsite</code> <sup><sub>**not implemented yet</sub></sup></summary>
    <p>Download the platform package of which you defined the version in the
    build properties.</>
</details>

## Subsite builds

<details>
    <summary>execute <code>composer create-project ec-europa/subsite toolkit-demo dev-master</code></summary> 
    <p>Clones the subsite template repository with the master branch and runs
    composer install.</p>
</details>

<details><summary>execute <code>nano build.develop.props</code></summary>
    <p>Provide the build properties for the project you wish to build.</p>
</details>

<details><summary>execute <code>./toolkit/phing build-project-platform</code></summary>
    <p>Download the platform package of which you defined the version in the
    build properties.</>
</details>

<details><summary>execute <code>./toolkit/phing build-subsite-dev</code></summary>
    <p>Build all resources and symlink the individual modules, themes and
    libraries to their location in the lib/ folder.</>
</details>

## Theme builds

The theme build provided in the toolkit belongs to the platform. For subsites
there is no real usage yet.

<details>
    <summary>execute <code>composer create-project ec-europa/platform toolkit-demo dev-master</code></summary> 
    <p>Clones the platform template repository with the master branch and runs
    composer install.</p>
</details>

<details><summary>execute <code>nano build.develop.props</code></summary>
    <p>Provide the build properties for the project you wish to build.</p>
</details>

<details><summary>execute <code>./toolkit/phing build-platform-dev</code></summary>
    <p>Build the actual codebase.</>
</details>

<details><summary>execute <code>./toolkit/phing build-theme-dev</code></summary>
    <p>Download the platform package of which you defined the version in the
    build properties.</>
</details>