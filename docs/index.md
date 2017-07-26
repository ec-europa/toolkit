> <details><summary><b>build-clean</b>: Build local version of subsite with a clean install.</summary><p>
>
> Code description: Build local version of subsite with a clean install.
> Code link: [includes/build/build.test.xml#L193](includes/build/build.test.xml#L193)
> Dependencies: drush-create-files-dirs, install, subsite-modules-development-enable
> </p></details>
>
> <details><summary><b>build-clone</b>: Build local version of subsite with production data.</summary><p>
>
> Code description: Build local version of subsite with production data.
> Code link: [includes/build/build.clone.xml#L118](includes/build/build.clone.xml#L118)
> Dependencies: subsite-database-download, drush-regenerate-settings, subsite-database-import, subsite-modules-development-enable
> </p></details>
>
> <details><summary><b>build-code</b>: Build local version of subsite without install.</summary><p>
>
> Code description: Build local version of subsite without install.
> Code link: [includes/build/build.package.xml#L74](includes/build/build.package.xml#L74)
> Dependencies: subsite-site-backup, platform-delete, platform-make, platform-link-resources, subsite-composer-install, test-behat-setup-link, test-behat-setup, platform-update-htaccess, test-phpcs-setup, subsite-modules-development-download, subsite-site-restore
> </p></details>
>
> <details><summary><b>build-keep</b>: Build local version of subsite with backup and restore.</summary><p>
>
> Code description: Build local version of subsite with backup and restore.
> Code link: [includes/build/build.package.xml#L92](includes/build/build.package.xml#L92)
> Dependencies: 
> </p></details>
>
> <details><summary><b>build-release</b>: Build subsite source code release package.</summary><p>
>
> Code description: Build subsite source code release package.
> Code link: [includes/build/build.package.xml#L63](includes/build/build.package.xml#L63)
> Dependencies: build-dist
> </p></details>
>
> <details><summary><b>build-tests</b>: Build subsite tests code release package.</summary><p>
>
> Code description: Build subsite tests code release package.
> Code link: [includes/build/build.package.xml#L69](includes/build/build.package.xml#L69)
> Dependencies: 
> </p></details>
>
> <details><summary><b>docker-compose-down</b>: Trash docker project.</summary><p>
>
> Code description: Trash docker project.
> Code link: [includes/build/build.docker.xml#L22](includes/build/build.docker.xml#L22)
> Dependencies: 
> </p></details>
>
> <details><summary><b>docker-compose-stop</b>: Stop docker project.</summary><p>
>
> Code description: Stop docker project.
> Code link: [includes/build/build.docker.xml#L15](includes/build/build.docker.xml#L15)
> Dependencies: 
> </p></details>
>
> <details><summary><b>docker-compose-up</b>: Start docker project.</summary><p>
>
> Code description: Start docker project.
> Code link: [includes/build/build.docker.xml#L5](includes/build/build.docker.xml#L5)
> Dependencies: 
> </p></details>
>
> <details><summary><b>install</b>: Install the subsite.</summary><p>
>
> Code description: Install the subsite.
> Code link: [includes/build/build.test.xml#L5](includes/build/build.test.xml#L5)
> Dependencies: 
> </p></details>
>
> <details><summary><b>link-docroot</b>: Create symlink from build to docroot.</summary><p>
>
> Code description: Create symlink from build to docroot.
> Code link: [includes/build/build.package.xml#L28](includes/build/build.package.xml#L28)
> Dependencies: 
> </p></details>
>
> <details><summary><b>test-run-behat</b>: Refresh configuration and run behat tests.</summary><p>
>
> Code description: Refresh configuration and run behat tests.
> Code link: [includes/build/build.test.xml#L150](includes/build/build.test.xml#L150)
> Dependencies: 
> </p></details>
>
> <details><summary><b>test-run-phpcs</b>: Refresh configuration and run phpcs review.</summary><p>
>
> Code description: Refresh configuration and run phpcs review.
> Code link: [includes/build/build.test.xml#L186](includes/build/build.test.xml#L186)
> Dependencies: test-phpcs-setup, test-run-php-codesniffer
> </p></details>
>
> <details><summary><b>test-run-qa</b>: Refresh configuration and run qa review.</summary><p>
>
> Code description: Refresh configuration and run qa review.
> Code link: [includes/build/build.test.xml#L179](includes/build/build.test.xml#L179)
> Dependencies: test-phpcs-setup, test-quality-assurance
> </p></details>
>
> <details><summary><b>build-dev</b>:  Target build-dev has been replaced by build-code. </summary><p>
>
> Code description:  Target build-dev has been replaced by build-code. 
> Code link: [includes/build/build.deprecated.xml#L5](includes/build/build.deprecated.xml#L5)
> Dependencies: 
> </p></details>
>
> <details><summary><b>build-dist</b>:  Create distribution code base. </summary><p>
>
> Code description:  Create distribution code base. 
> Code link: [includes/build/build.package.xml#L100](includes/build/build.package.xml#L100)
> Dependencies: dist-delete, dist-make, dist-copy-resources, dist-composer-install
> </p></details>
>
> <details><summary><b>check-for-default-settings-or-rebuild</b>:  Target to check if we have default settings, otherwise propose user to rebuild. </summary><p>
>
> Code description:  Target to check if we have default settings, otherwise propose user to rebuild. 
> Code link: [includes/build/build.clone.xml#L88](includes/build/build.clone.xml#L88)
> Dependencies: 
> </p></details>
>
> <details><summary><b>composer-echo-hook-phingcalls</b>:  Echo the composer hook phingcalls. </summary><p>
>
> Code description:  Echo the composer hook phingcalls. 
> Code link: [includes/build/build.composer.xml#L5](includes/build/build.composer.xml#L5)
> Dependencies: 
> </p></details>
>
> <details><summary><b>copy-folder</b>:  Copies a given folder to a new location. </summary><p>
>
> Code description:  Copies a given folder to a new location. 
> Code link: [includes/build/build.helpers.xml#L5](includes/build/build.helpers.xml#L5)
> Dependencies: 
> </p></details>
>
> <details><summary><b>create-tmp-dirs</b>:  Create temp dirs. </summary><p>
>
> Code description:  Create temp dirs. 
> Code link: [includes/build/build.package.xml#L35](includes/build/build.package.xml#L35)
> Dependencies: 
> </p></details>
>
> <details><summary><b>delete-folder</b>:  Delete a given folder. </summary><p>
>
> Code description:  Delete a given folder. 
> Code link: [includes/build/build.helpers.xml#L12](includes/build/build.helpers.xml#L12)
> Dependencies: 
> </p></details>
>
> <details><summary><b>dist-composer-install</b>:  Install Composer dist dependencies for the subsite. </summary><p>
>
> Code description:  Install Composer dist dependencies for the subsite. 
> Code link: [includes/build/build.dist.xml#L5](includes/build/build.dist.xml#L5)
> Dependencies: 
> </p></details>
>
> <details><summary><b>dist-copy-resources</b>:  Copy subsite resources into the build folder. </summary><p>
>
> Code description:  Copy subsite resources into the build folder. 
> Code link: [includes/build/build.dist.xml#L18](includes/build/build.dist.xml#L18)
> Dependencies: 
> </p></details>
>
> <details><summary><b>dist-delete</b>:  Delete the previous distribution build. </summary><p>
>
> Code description:  Delete the previous distribution build. 
> Code link: [includes/build/build.dist.xml#L50](includes/build/build.dist.xml#L50)
> Dependencies: 
> </p></details>
>
> <details><summary><b>dist-make</b>:  Make the distribution version of the subsite. </summary><p>
>
> Code description:  Make the distribution version of the subsite. 
> Code link: [includes/build/build.dist.xml#L58](includes/build/build.dist.xml#L58)
> Dependencies: 
> </p></details>
>
> <details><summary><b>drush-create-files-dirs</b>:  Create the directories. </summary><p>
>
> Code description:  Create the directories. 
> Code link: [includes/build/build.drush.xml#L32](includes/build/build.drush.xml#L32)
> Dependencies: 
> </p></details>
>
> <details><summary><b>drush-dl-rr</b>:  Download registry rebuild. </summary><p>
>
> Code description:  Download registry rebuild. 
> Code link: [includes/build/build.drush.xml#L162](includes/build/build.drush.xml#L162)
> Dependencies: 
> </p></details>
>
> <details><summary><b>drush-enable-modules</b>:  Enable modules. </summary><p>
>
> Code description:  Enable modules. 
> Code link: [includes/build/build.drush.xml#L19](includes/build/build.drush.xml#L19)
> Dependencies: 
> </p></details>
>
> <details><summary><b>drush-enable-solr</b>:  Activate solr if needed. </summary><p>
>
> Code description:  Activate solr if needed. 
> Code link: [includes/build/build.drush.xml#L83](includes/build/build.drush.xml#L83)
> Dependencies: 
> </p></details>
>
> <details><summary><b>drush-make-no-core</b>:  Execute a makefile with the no-core option. </summary><p>
>
> Code description:  Execute a makefile with the no-core option. 
> Code link: [includes/build/build.drush.xml#L99](includes/build/build.drush.xml#L99)
> Dependencies: 
> </p></details>
>
> <details><summary><b>drush-rebuild-node-access</b>:  Rebuild node access. </summary><p>
>
> Code description:  Rebuild node access. 
> Code link: [includes/build/build.drush.xml#L169](includes/build/build.drush.xml#L169)
> Dependencies: 
> </p></details>
>
> <details><summary><b>drush-regenerate-settings</b>:  Regenerate the settings file with database credentials and development variables. </summary><p>
>
> Code description:  Regenerate the settings file with database credentials and development variables. 
> Code link: [includes/build/build.drush.xml#L111](includes/build/build.drush.xml#L111)
> Dependencies: check-for-default-settings-or-rebuild
> </p></details>
>
> <details><summary><b>drush-registry-rebuild</b>:  Rebuild registry. </summary><p>
>
> Code description:  Rebuild registry. 
> Code link: [includes/build/build.drush.xml#L142](includes/build/build.drush.xml#L142)
> Dependencies: 
> </p></details>
>
> <details><summary><b>drush-site-install</b>:  Install the site. </summary><p>
>
> Code description:  Install the site. 
> Code link: [includes/build/build.drush.xml#L5](includes/build/build.drush.xml#L5)
> Dependencies: 
> </p></details>
>
> <details><summary><b>drush-sql-create</b>:  Create the database. </summary><p>
>
> Code description:  Create the database. 
> Code link: [includes/build/build.drush.xml#L41](includes/build/build.drush.xml#L41)
> Dependencies: 
> </p></details>
>
> <details><summary><b>drush-sql-drop</b>:  Drop the database. </summary><p>
>
> Code description:  Drop the database. 
> Code link: [includes/build/build.drush.xml#L65](includes/build/build.drush.xml#L65)
> Dependencies: 
> </p></details>
>
> <details><summary><b>drush-sql-dump</b>:  Backup the database. </summary><p>
>
> Code description:  Backup the database. 
> Code link: [includes/build/build.drush.xml#L73](includes/build/build.drush.xml#L73)
> Dependencies: 
> </p></details>
>
> <details><summary><b>drush-sql-import</b>:  Import a database. </summary><p>
>
> Code description:  Import a database. 
> Code link: [includes/build/build.drush.xml#L49](includes/build/build.drush.xml#L49)
> Dependencies: 
> </p></details>
>
> <details><summary><b>platform-composer-install</b>:  Install Composer dependencies for the build system. </summary><p>
>
> Code description:  Install Composer dependencies for the build system. 
> Code link: [includes/build/build.platform.xml#L5](includes/build/build.platform.xml#L5)
> Dependencies: 
> </p></details>
>
> <details><summary><b>platform-delete</b>:  Delete the previous development build. </summary><p>
>
> Code description:  Delete the previous development build. 
> Code link: [includes/build/build.platform.xml#L16](includes/build/build.platform.xml#L16)
> Dependencies: 
> </p></details>
>
> <details><summary><b>platform-download</b>:  Download the platform. </summary><p>
>
> Code description:  Download the platform. 
> Code link: [includes/build/build.platform.xml#L29](includes/build/build.platform.xml#L29)
> Dependencies: 
> </p></details>
>
> <details><summary><b>platform-link-resources</b>:  Symlink the source folders for easy development. </summary><p>
>
> Code description:  Symlink the source folders for easy development. 
> Code link: [includes/build/build.platform.xml#L54](includes/build/build.platform.xml#L54)
> Dependencies: 
> </p></details>
>
> <details><summary><b>platform-make</b>:  Make the development version of the subsite. </summary><p>
>
> Code description:  Make the development version of the subsite. 
> Code link: [includes/build/build.platform.xml#L65](includes/build/build.platform.xml#L65)
> Dependencies: platform-unpack
> </p></details>
>
> <details><summary><b>platform-unpack</b>:  Unpack the platform. </summary><p>
>
> Code description:  Unpack the platform. 
> Code link: [includes/build/build.platform.xml#L82](includes/build/build.platform.xml#L82)
> Dependencies: platform-download
> </p></details>
>
> <details><summary><b>platform-update-htaccess</b>:  Update .htaccess. </summary><p>
>
> Code description:  Update .htaccess. 
> Code link: [includes/build/build.platform.xml#L108](includes/build/build.platform.xml#L108)
> Dependencies: 
> </p></details>
>
> <details><summary><b>prompt-for-credentials-and-retry</b>:  Simple prompt for user credentials and recurse into subsite-database-wget. </summary><p>
>
> Code description:  Simple prompt for user credentials and recurse into subsite-database-wget. 
> Code link: [includes/build/build.clone.xml#L81](includes/build/build.clone.xml#L81)
> Dependencies: 
> </p></details>
>
> <details><summary><b>starterkit-build-documentation-index</b>:  Build documentation index. </summary><p>
>
> Code description:  Build documentation index. 
> Code link: [includes/build/build.starterkit.xml#L60](includes/build/build.starterkit.xml#L60)
> Dependencies: 
> </p></details>
>
> <details><summary><b>starterkit-copy-templates</b>:  Ensure needed files are present. </summary><p>
>
> Code description:  Ensure needed files are present. 
> Code link: [includes/build/build.starterkit.xml#L11](includes/build/build.starterkit.xml#L11)
> Dependencies: 
> </p></details>
>
> <details><summary><b>starterkit-link-binary</b>:  Provide handy access with root symlink to starterkit binary. </summary><p>
>
> Code description:  Provide handy access with root symlink to starterkit binary. 
> Code link: [includes/build/build.starterkit.xml#L5](includes/build/build.starterkit.xml#L5)
> Dependencies: 
> </p></details>
>
> <details><summary><b>starterkit-upgrade</b>:  Upgrade subsite-starterkit 2.x to 3.x. </summary><p>
>
> Code description:  Upgrade subsite-starterkit 2.x to 3.x. 
> Code link: [includes/build/build.starterkit.xml#L19](includes/build/build.starterkit.xml#L19)
> Dependencies: 
> </p></details>
>
> <details><summary><b>subsite-composer-install</b>:  Install Composer dev dependencies for the subsite. </summary><p>
>
> Code description:  Install Composer dev dependencies for the subsite. 
> Code link: [includes/build/build.subsite.xml#L5](includes/build/build.subsite.xml#L5)
> Dependencies: 
> </p></details>
>
> <details><summary><b>subsite-database-download</b>:  Download the production database. </summary><p>
>
> Code description:  Download the production database. 
> Code link: [includes/build/build.clone.xml#L17](includes/build/build.clone.xml#L17)
> Dependencies: 
> </p></details>
>
> <details><summary><b>subsite-database-import</b>:  Import production database. </summary><p>
>
> Code description:  Import production database. 
> Code link: [includes/build/build.clone.xml#L5](includes/build/build.clone.xml#L5)
> Dependencies: subsite-database-download
> </p></details>
>
> <details><summary><b>subsite-database-wget</b>:  Target to actually fetch the database dump. </summary><p>
>
> Code description:  Target to actually fetch the database dump. 
> Code link: [includes/build/build.clone.xml#L40](includes/build/build.clone.xml#L40)
> Dependencies: 
> </p></details>
>
> <details><summary><b>subsite-modules-development-download</b>:  Download development modules. </summary><p>
>
> Code description:  Download development modules. 
> Code link: [includes/build/build.subsite.xml#L36](includes/build/build.subsite.xml#L36)
> Dependencies: subsite-modules-development-makefile
> </p></details>
>
> <details><summary><b>subsite-modules-development-enable</b>:  Enable development modules. </summary><p>
>
> Code description:  Enable development modules. 
> Code link: [includes/build/build.test.xml#L71](includes/build/build.test.xml#L71)
> Dependencies: 
> </p></details>
>
> <details><summary><b>subsite-modules-development-makefile</b>:  Generate the makefile used to download development modules. </summary><p>
>
> Code description:  Generate the makefile used to download development modules. 
> Code link: [includes/build/build.subsite.xml#L18](includes/build/build.subsite.xml#L18)
> Dependencies: 
> </p></details>
>
> <details><summary><b>subsite-modules-install-enable</b>:  Enable required modules after installation of the profile. </summary><p>
>
> Code description:  Enable required modules after installation of the profile. 
> Code link: [includes/build/build.test.xml#L64](includes/build/build.test.xml#L64)
> Dependencies: 
> </p></details>
>
> <details><summary><b>subsite-setup-files-directory</b>:  Setup file directory </summary><p>
>
> Code description:  Setup file directory 
> Code link: [includes/build/build.subsite.xml#L222](includes/build/build.subsite.xml#L222)
> Dependencies: 
> </p></details>
>
> <details><summary><b>subsite-site-backup</b>:  Backs up files and folders listed in platform.rebuild properties in order to rebuild. </summary><p>
>
> Code description:  Backs up files and folders listed in platform.rebuild properties in order to rebuild. 
> Code link: [includes/build/build.subsite.xml#L45](includes/build/build.subsite.xml#L45)
> Dependencies: 
> </p></details>
>
> <details><summary><b>subsite-site-backup-item</b>:  Backs up a site item from the platform that will be removed in order to rebuild. </summary><p>
>
> Code description:  Backs up a site item from the platform that will be removed in order to rebuild. 
> Code link: [includes/build/build.subsite.xml#L162](includes/build/build.subsite.xml#L162)
> Dependencies: 
> </p></details>
>
> <details><summary><b>subsite-site-restore</b>:  Restoring sites directory if backed up before rebuild-dev. </summary><p>
>
> Code description:  Restoring sites directory if backed up before rebuild-dev. 
> Code link: [includes/build/build.subsite.xml#L112](includes/build/build.subsite.xml#L112)
> Dependencies: 
> </p></details>
>
> <details><summary><b>subsite-site-restore-item</b>:  Restores a site item from the platform.rebuild.backup.destination to the new build. </summary><p>
>
> Code description:  Restores a site item from the platform.rebuild.backup.destination to the new build. 
> Code link: [includes/build/build.subsite.xml#L192](includes/build/build.subsite.xml#L192)
> Dependencies: 
> </p></details>
>
> <details><summary><b>test-behat-setup</b>:  Set up Behat. </summary><p>
>
> Code description:  Set up Behat. 
> Code link: [includes/build/build.test.xml#L127](includes/build/build.test.xml#L127)
> Dependencies: 
> </p></details>
>
> <details><summary><b>test-behat-setup-link</b>:  Symlink the Behat bin and test directory in the subsite folder. </summary><p>
>
> Code description:  Symlink the Behat bin and test directory in the subsite folder. 
> Code link: [includes/build/build.package.xml#L21](includes/build/build.package.xml#L21)
> Dependencies: 
> </p></details>
>
> <details><summary><b>test-phpcs-setup</b>:  Set up PHP CodeSniffer. </summary><p>
>
> Code description:  Set up PHP CodeSniffer. 
> Code link: [includes/build/build.test.xml#L78](includes/build/build.test.xml#L78)
> Dependencies: 
> </p></details>
>
> <details><summary><b>test-phpcs-setup-prepush</b>:  Setup the PHP CodeSniffer pre-push hook. </summary><p>
>
> Code description:  Setup the PHP CodeSniffer pre-push hook. 
> Code link: [includes/build/build.test.xml#L111](includes/build/build.test.xml#L111)
> Dependencies: 
> </p></details>
>
> <details><summary><b>test-quality-assurance</b>:  Do quality assurance checks. </summary><p>
>
> Code description:  Do quality assurance checks. 
> Code link: [includes/build/build.test.xml#L161](includes/build/build.test.xml#L161)
> Dependencies: 
> </p></details>
>
> <details><summary><b>test-run-php-codesniffer</b>:  Do quality assurance checks. </summary><p>
>
> Code description:  Do quality assurance checks. 
> Code link: [includes/build/build.test.xml#L170](includes/build/build.test.xml#L170)
> Dependencies: 
> </p></details>
>
> <details><summary><b>unprotect-folder</b>:  Make the given folder writeable. </summary><p>
>
> Code description:  Make the given folder writeable. 
> Code link: [includes/build/build.helpers.xml#L32](includes/build/build.helpers.xml#L32)
> Dependencies: 
> </p></details>
>
