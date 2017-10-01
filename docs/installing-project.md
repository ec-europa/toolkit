# Installing the project

<big><table><thead><tr><th nowrap> [Building the codebase](./building-codebase.md#building-the-codebase) </th><th width="100%" align="center"> [User guide](../README.md#user-guide) </th><th nowrap> [Testing a project](./testing-project.md#testing-the-project) </th></tr></thead></table>

This guide explains how to install a new project or how to clone your production
environment with the import of a database. These installations have as a
prerequisite:

<details><summary>A functional codebase</summary><p>

<blockquote>
<details><p><summary>execute <code>./toolkit/phing build-project-platform</code></summary>

```

```
</p></details>
<details><p><summary>execute <code>./toolkit/phing build-subsite-dev</code></summary>

```

```
</p></details>
</blockquote>
</p></details>
<details><summary>Correct build properties</summary><p>

<blockquote>
<details><p><summary>execute <code>nano build.project.props</code></summary>

```

```
</p></details>
<details><p><summary>execute <code>nano build.develop.props</code></summary>

```

```
</p></details>
</blockquote>
</p></details>

## Clean installation
A clean installation means that you will run through the whole Drupal
installation process. To get a good end result here, your project needs to be
have all installation steps written into the module and feature install files.

<details><summary>execute <code>./toolkit/phing install-project-clean</code></summary><p>

This target will install your site from scratch. And by default it will save the
database right after install to cache it. That way on a future build with the
same platform version you will skip a part of the installation process.
</p></details>

## Clone installation
A clone installation means that you will be importing a sanitized database dump
from production to mirror the current production as well as you can. This is
very useful for debugging.

<details><summary>execute <code>./toolkit/phing install-project-clone</code></summary><p>

Toolkit provide a phing target to clone your subsite project, please refer to
targets documentation get more details.</p>
</details>