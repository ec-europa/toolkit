# Toolkit Phing Targets
This is the list of targets provided by toolkit, please note that this is a auto-generated/partial list, you can check the full list [here](targets-list.md).



<details><p><summary>build-platform</summary></p>

Build NextEuropa Platform code without version control.

##### Example:
`toolkit\phing build-platform`

</details>

<details><p><summary>build-platform-dev</summary></p>

Build a local development version with a single platform profile.

##### Example:
`toolkit\phing build-platform-dev`

##### Dependencies: 
* build-theme-dev
* platform-delete
* platform-drupal-make
* platform-profiles-link
* platform-profiles-make
* platform-resources-link
* platform-type-dev
* project-modules-devel-dl
* project-platform-composer-no-dev
* project-platform-set-htaccess
* project-subsite-backup
* project-subsite-files-setup
* project-subsite-restore

</details>

<details><p><summary>build-platform-dev-all</summary></p>

Build a local development version with all platform profiles.

##### Example:
`toolkit\phing build-platform-dev-all`

##### Dependencies: 
* platform-delete
* platform-drupal-make
* platform-profiles-copy
* platform-profiles-make
* platform-resources-copy
* platform-type-dev
* project-platform-composer-no-dev

</details>

<details><p><summary>build-platform-dist</summary></p>

Build a single platform profile intended as a release package.

##### Example:
`toolkit\phing build-platform-dist`

##### Dependencies: 
* platform-delete
* platform-drupal-make
* platform-profile-copy
* platform-profile-make
* platform-resources-copy
* platform-type-dist
* project-platform-composer-no-dev

</details>

<details><p><summary>build-platform-dist-all</summary></p>

Build all platform profiles intended as a release package.

##### Example:
`toolkit\phing build-platform-dist-all`

##### Dependencies: 
* platform-delete
* platform-drupal-make
* platform-profiles-copy
* platform-profiles-make
* platform-resources-copy
* platform-type-dist
* project-platform-composer-no-dev

</details>

<details><p><summary>build-platform-test</summary></p>

Build a platform test package to test this release.

##### Example:
`toolkit\phing build-platform-test`

</details>

<details><p><summary>build-subsite</summary></p>

Build NextEuropa Subsite code without version control.

##### Example:
`toolkit\phing build-subsite`

##### Dependencies: 
* project-subsite-backup
* project-subsite-restore

</details>

<details><p><summary>build-subsite-dev</summary></p>

Build a local development version of the site.

##### Example:
`toolkit\phing build-subsite-dev`

##### Dependencies: 
* project-modules-devel-make
* project-subsite-backup
* project-subsite-composer-dev
* project-subsite-restore
* subsite-delete-dev
* subsite-make
* subsite-resources-link
* subsite-type-dev

</details>

<details><p><summary>build-subsite-dist</summary></p>

Build a site intended as a release package.

##### Example:
`toolkit\phing build-subsite-dist`

##### Dependencies: 
* project-subsite-composer-no-dev
* subsite-delete-dist
* subsite-make
* subsite-resources-copy
* subsite-resources-link
* subsite-type-dist
* subsite-type-tmp

</details>

<details><p><summary>build-subsite-test</summary></p>

Build a subsite test package to test this release.

##### Example:
`toolkit\phing build-subsite-test`

</details>

<details><p><summary>build-theme</summary></p>

Build EC Europa theme without version control.

##### Example:
`toolkit\phing build-theme`

##### Dependencies: 
* theme-europa-create-symlinks
* theme-europa-download-extract

</details>

<details><p><summary>build-theme-dev</summary></p>

Build EC Europa theme with version control.

##### Example:
`toolkit\phing build-theme-dev`

##### Dependencies: 
* theme-europa-build
* theme-europa-repo-clone

</details>

<details><p><summary>build-toolkit</summary></p>

Initializes toolkit and project directories.

##### Example:
`toolkit\phing build-toolkit`

##### Dependencies: 
* toolkit-binary-link
* toolkit-structure-generate

</details>

This is a partial list, please check the full list [here](targets-list.md).