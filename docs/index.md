<details><summary><h3>build-clean</h3>: Build local version of subsite with a clean install.</summary><p> 
>
>- <b>Code description</b>: Build local version of subsite with a clean install. 
>- <b>Code link</b>: [includes/build/build.test.xml#L193](includes/build/build.test.xml#L193)
>- <b>Dependencies</b>: drush-create-files-dirs, install, subsite-modules-development-enable
</p></details>
>
<details><summary><h3>build-clone</h3>: Build local version of subsite with production data.</summary><p> 
>
>- <b>Code description</b>: Build local version of subsite with production data. 
>- <b>Code link</b>: [includes/build/build.clone.xml#L118](includes/build/build.clone.xml#L118)
>- <b>Dependencies</b>: subsite-database-download, drush-regenerate-settings, subsite-database-import, subsite-modules-development-enable
</p></details>
>
<details><summary><h3>build-code</h3>: Build local version of subsite without install.</summary><p> 
>
>- <b>Code description</b>: Build local version of subsite without install. 
>- <b>Code link</b>: [includes/build/build.package.xml#L74](includes/build/build.package.xml#L74)
>- <b>Dependencies</b>: subsite-site-backup, platform-delete, platform-make, platform-link-resources, subsite-composer-install, test-behat-setup-link, test-behat-setup, platform-update-htaccess, test-phpcs-setup, subsite-modules-development-download, subsite-site-restore
</p></details>
>
<details><summary><h3>build-keep</h3>: Build local version of subsite with backup and restore.</summary><p> 
>
>- <b>Code description</b>: Build local version of subsite with backup and restore. 
>- <b>Code link</b>: [includes/build/build.package.xml#L92](includes/build/build.package.xml#L92)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>build-release</h3>: Build subsite source code release package.</summary><p> 
>
>- <b>Code description</b>: Build subsite source code release package. 
>- <b>Code link</b>: [includes/build/build.package.xml#L63](includes/build/build.package.xml#L63)
>- <b>Dependencies</b>: build-dist
</p></details>
>
<details><summary><h3>build-tests</h3>: Build subsite tests code release package.</summary><p> 
>
>- <b>Code description</b>: Build subsite tests code release package. 
>- <b>Code link</b>: [includes/build/build.package.xml#L69](includes/build/build.package.xml#L69)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>docker-compose-down</h3>: Trash docker project.</summary><p> 
>
>- <b>Code description</b>: Trash docker project. 
>- <b>Code link</b>: [includes/build/build.docker.xml#L22](includes/build/build.docker.xml#L22)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>docker-compose-stop</h3>: Stop docker project.</summary><p> 
>
>- <b>Code description</b>: Stop docker project. 
>- <b>Code link</b>: [includes/build/build.docker.xml#L15](includes/build/build.docker.xml#L15)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>docker-compose-up</h3>: Start docker project.</summary><p> 
>
>- <b>Code description</b>: Start docker project. 
>- <b>Code link</b>: [includes/build/build.docker.xml#L5](includes/build/build.docker.xml#L5)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>install</h3>: Install the subsite.</summary><p> 
>
>- <b>Code description</b>: Install the subsite. 
>- <b>Code link</b>: [includes/build/build.test.xml#L5](includes/build/build.test.xml#L5)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>link-docroot</h3>: Create symlink from build to docroot.</summary><p> 
>
>- <b>Code description</b>: Create symlink from build to docroot. 
>- <b>Code link</b>: [includes/build/build.package.xml#L28](includes/build/build.package.xml#L28)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>test-run-behat</h3>: Refresh configuration and run behat tests.</summary><p> 
>
>- <b>Code description</b>: Refresh configuration and run behat tests. 
>- <b>Code link</b>: [includes/build/build.test.xml#L150](includes/build/build.test.xml#L150)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>test-run-phpcs</h3>: Refresh configuration and run phpcs review.</summary><p> 
>
>- <b>Code description</b>: Refresh configuration and run phpcs review. 
>- <b>Code link</b>: [includes/build/build.test.xml#L186](includes/build/build.test.xml#L186)
>- <b>Dependencies</b>: test-phpcs-setup, test-run-php-codesniffer
</p></details>
>
<details><summary><h3>test-run-qa</h3>: Refresh configuration and run qa review.</summary><p> 
>
>- <b>Code description</b>: Refresh configuration and run qa review. 
>- <b>Code link</b>: [includes/build/build.test.xml#L179](includes/build/build.test.xml#L179)
>- <b>Dependencies</b>: test-phpcs-setup, test-quality-assurance
</p></details>
>
<details><summary><h3>build-dev</h3>:  Target build-dev has been replaced by build-code. </summary><p> 
>
>- <b>Code description</b>:  Target build-dev has been replaced by build-code.  
>- <b>Code link</b>: [includes/build/build.deprecated.xml#L5](includes/build/build.deprecated.xml#L5)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>build-dist</h3>:  Create distribution code base. </summary><p> 
>
>- <b>Code description</b>:  Create distribution code base.  
>- <b>Code link</b>: [includes/build/build.package.xml#L100](includes/build/build.package.xml#L100)
>- <b>Dependencies</b>: dist-delete, dist-make, dist-copy-resources, dist-composer-install
</p></details>
>
<details><summary><h3>check-for-default-settings-or-rebuild</h3>:  Target to check if we have default settings, otherwise propose user to rebuild. </summary><p> 
>
>- <b>Code description</b>:  Target to check if we have default settings, otherwise propose user to rebuild.  
>- <b>Code link</b>: [includes/build/build.clone.xml#L88](includes/build/build.clone.xml#L88)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>composer-echo-hook-phingcalls</h3>:  Echo the composer hook phingcalls. </summary><p> 
>
>- <b>Code description</b>:  Echo the composer hook phingcalls.  
>- <b>Code link</b>: [includes/build/build.composer.xml#L5](includes/build/build.composer.xml#L5)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>copy-folder</h3>:  Copies a given folder to a new location. </summary><p> 
>
>- <b>Code description</b>:  Copies a given folder to a new location.  
>- <b>Code link</b>: [includes/build/build.helpers.xml#L5](includes/build/build.helpers.xml#L5)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>create-tmp-dirs</h3>:  Create temp dirs. </summary><p> 
>
>- <b>Code description</b>:  Create temp dirs.  
>- <b>Code link</b>: [includes/build/build.package.xml#L35](includes/build/build.package.xml#L35)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>delete-folder</h3>:  Delete a given folder. </summary><p> 
>
>- <b>Code description</b>:  Delete a given folder.  
>- <b>Code link</b>: [includes/build/build.helpers.xml#L12](includes/build/build.helpers.xml#L12)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>dist-composer-install</h3>:  Install Composer dist dependencies for the subsite. </summary><p> 
>
>- <b>Code description</b>:  Install Composer dist dependencies for the subsite.  
>- <b>Code link</b>: [includes/build/build.dist.xml#L5](includes/build/build.dist.xml#L5)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>dist-copy-resources</h3>:  Copy subsite resources into the build folder. </summary><p> 
>
>- <b>Code description</b>:  Copy subsite resources into the build folder.  
>- <b>Code link</b>: [includes/build/build.dist.xml#L18](includes/build/build.dist.xml#L18)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>dist-delete</h3>:  Delete the previous distribution build. </summary><p> 
>
>- <b>Code description</b>:  Delete the previous distribution build.  
>- <b>Code link</b>: [includes/build/build.dist.xml#L50](includes/build/build.dist.xml#L50)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>dist-make</h3>:  Make the distribution version of the subsite. </summary><p> 
>
>- <b>Code description</b>:  Make the distribution version of the subsite.  
>- <b>Code link</b>: [includes/build/build.dist.xml#L58](includes/build/build.dist.xml#L58)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>drush-create-files-dirs</h3>:  Create the directories. </summary><p> 
>
>- <b>Code description</b>:  Create the directories.  
>- <b>Code link</b>: [includes/build/build.drush.xml#L32](includes/build/build.drush.xml#L32)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>drush-dl-rr</h3>:  Download registry rebuild. </summary><p> 
>
>- <b>Code description</b>:  Download registry rebuild.  
>- <b>Code link</b>: [includes/build/build.drush.xml#L162](includes/build/build.drush.xml#L162)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>drush-enable-modules</h3>:  Enable modules. </summary><p> 
>
>- <b>Code description</b>:  Enable modules.  
>- <b>Code link</b>: [includes/build/build.drush.xml#L19](includes/build/build.drush.xml#L19)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>drush-enable-solr</h3>:  Activate solr if needed. </summary><p> 
>
>- <b>Code description</b>:  Activate solr if needed.  
>- <b>Code link</b>: [includes/build/build.drush.xml#L83](includes/build/build.drush.xml#L83)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>drush-make-no-core</h3>:  Execute a makefile with the no-core option. </summary><p> 
>
>- <b>Code description</b>:  Execute a makefile with the no-core option.  
>- <b>Code link</b>: [includes/build/build.drush.xml#L99](includes/build/build.drush.xml#L99)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>drush-rebuild-node-access</h3>:  Rebuild node access. </summary><p> 
>
>- <b>Code description</b>:  Rebuild node access.  
>- <b>Code link</b>: [includes/build/build.drush.xml#L169](includes/build/build.drush.xml#L169)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>drush-regenerate-settings</h3>:  Regenerate the settings file with database credentials and development variables. </summary><p> 
>
>- <b>Code description</b>:  Regenerate the settings file with database credentials and development variables.  
>- <b>Code link</b>: [includes/build/build.drush.xml#L111](includes/build/build.drush.xml#L111)
>- <b>Dependencies</b>: check-for-default-settings-or-rebuild
</p></details>
>
<details><summary><h3>drush-registry-rebuild</h3>:  Rebuild registry. </summary><p> 
>
>- <b>Code description</b>:  Rebuild registry.  
>- <b>Code link</b>: [includes/build/build.drush.xml#L142](includes/build/build.drush.xml#L142)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>drush-site-install</h3>:  Install the site. </summary><p> 
>
>- <b>Code description</b>:  Install the site.  
>- <b>Code link</b>: [includes/build/build.drush.xml#L5](includes/build/build.drush.xml#L5)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>drush-sql-create</h3>:  Create the database. </summary><p> 
>
>- <b>Code description</b>:  Create the database.  
>- <b>Code link</b>: [includes/build/build.drush.xml#L41](includes/build/build.drush.xml#L41)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>drush-sql-drop</h3>:  Drop the database. </summary><p> 
>
>- <b>Code description</b>:  Drop the database.  
>- <b>Code link</b>: [includes/build/build.drush.xml#L65](includes/build/build.drush.xml#L65)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>drush-sql-dump</h3>:  Backup the database. </summary><p> 
>
>- <b>Code description</b>:  Backup the database.  
>- <b>Code link</b>: [includes/build/build.drush.xml#L73](includes/build/build.drush.xml#L73)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>drush-sql-import</h3>:  Import a database. </summary><p> 
>
>- <b>Code description</b>:  Import a database.  
>- <b>Code link</b>: [includes/build/build.drush.xml#L49](includes/build/build.drush.xml#L49)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>platform-composer-install</h3>:  Install Composer dependencies for the build system. </summary><p> 
>
>- <b>Code description</b>:  Install Composer dependencies for the build system.  
>- <b>Code link</b>: [includes/build/build.platform.xml#L5](includes/build/build.platform.xml#L5)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>platform-delete</h3>:  Delete the previous development build. </summary><p> 
>
>- <b>Code description</b>:  Delete the previous development build.  
>- <b>Code link</b>: [includes/build/build.platform.xml#L16](includes/build/build.platform.xml#L16)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>platform-download</h3>:  Download the platform. </summary><p> 
>
>- <b>Code description</b>:  Download the platform.  
>- <b>Code link</b>: [includes/build/build.platform.xml#L29](includes/build/build.platform.xml#L29)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>platform-link-resources</h3>:  Symlink the source folders for easy development. </summary><p> 
>
>- <b>Code description</b>:  Symlink the source folders for easy development.  
>- <b>Code link</b>: [includes/build/build.platform.xml#L54](includes/build/build.platform.xml#L54)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>platform-make</h3>:  Make the development version of the subsite. </summary><p> 
>
>- <b>Code description</b>:  Make the development version of the subsite.  
>- <b>Code link</b>: [includes/build/build.platform.xml#L65](includes/build/build.platform.xml#L65)
>- <b>Dependencies</b>: platform-unpack
</p></details>
>
<details><summary><h3>platform-unpack</h3>:  Unpack the platform. </summary><p> 
>
>- <b>Code description</b>:  Unpack the platform.  
>- <b>Code link</b>: [includes/build/build.platform.xml#L82](includes/build/build.platform.xml#L82)
>- <b>Dependencies</b>: platform-download
</p></details>
>
<details><summary><h3>platform-update-htaccess</h3>:  Update .htaccess. </summary><p> 
>
>- <b>Code description</b>:  Update .htaccess.  
>- <b>Code link</b>: [includes/build/build.platform.xml#L108](includes/build/build.platform.xml#L108)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>prompt-for-credentials-and-retry</h3>:  Simple prompt for user credentials and recurse into subsite-database-wget. </summary><p> 
>
>- <b>Code description</b>:  Simple prompt for user credentials and recurse into subsite-database-wget.  
>- <b>Code link</b>: [includes/build/build.clone.xml#L81](includes/build/build.clone.xml#L81)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>starterkit-build-documentation-index</h3>:  Build documentation index. </summary><p> 
>
>- <b>Code description</b>:  Build documentation index.  
>- <b>Code link</b>: [includes/build/build.starterkit.xml#L60](includes/build/build.starterkit.xml#L60)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>starterkit-copy-templates</h3>:  Ensure needed files are present. </summary><p> 
>
>- <b>Code description</b>:  Ensure needed files are present.  
>- <b>Code link</b>: [includes/build/build.starterkit.xml#L11](includes/build/build.starterkit.xml#L11)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>starterkit-link-binary</h3>:  Provide handy access with root symlink to starterkit binary. </summary><p> 
>
>- <b>Code description</b>:  Provide handy access with root symlink to starterkit binary.  
>- <b>Code link</b>: [includes/build/build.starterkit.xml#L5](includes/build/build.starterkit.xml#L5)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>starterkit-upgrade</h3>:  Upgrade subsite-starterkit 2.x to 3.x. </summary><p> 
>
>- <b>Code description</b>:  Upgrade subsite-starterkit 2.x to 3.x.  
>- <b>Code link</b>: [includes/build/build.starterkit.xml#L19](includes/build/build.starterkit.xml#L19)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>subsite-composer-install</h3>:  Install Composer dev dependencies for the subsite. </summary><p> 
>
>- <b>Code description</b>:  Install Composer dev dependencies for the subsite.  
>- <b>Code link</b>: [includes/build/build.subsite.xml#L5](includes/build/build.subsite.xml#L5)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>subsite-database-download</h3>:  Download the production database. </summary><p> 
>
>- <b>Code description</b>:  Download the production database.  
>- <b>Code link</b>: [includes/build/build.clone.xml#L17](includes/build/build.clone.xml#L17)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>subsite-database-import</h3>:  Import production database. </summary><p> 
>
>- <b>Code description</b>:  Import production database.  
>- <b>Code link</b>: [includes/build/build.clone.xml#L5](includes/build/build.clone.xml#L5)
>- <b>Dependencies</b>: subsite-database-download
</p></details>
>
<details><summary><h3>subsite-database-wget</h3>:  Target to actually fetch the database dump. </summary><p> 
>
>- <b>Code description</b>:  Target to actually fetch the database dump.  
>- <b>Code link</b>: [includes/build/build.clone.xml#L40](includes/build/build.clone.xml#L40)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>subsite-modules-development-download</h3>:  Download development modules. </summary><p> 
>
>- <b>Code description</b>:  Download development modules.  
>- <b>Code link</b>: [includes/build/build.subsite.xml#L36](includes/build/build.subsite.xml#L36)
>- <b>Dependencies</b>: subsite-modules-development-makefile
</p></details>
>
<details><summary><h3>subsite-modules-development-enable</h3>:  Enable development modules. </summary><p> 
>
>- <b>Code description</b>:  Enable development modules.  
>- <b>Code link</b>: [includes/build/build.test.xml#L71](includes/build/build.test.xml#L71)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>subsite-modules-development-makefile</h3>:  Generate the makefile used to download development modules. </summary><p> 
>
>- <b>Code description</b>:  Generate the makefile used to download development modules.  
>- <b>Code link</b>: [includes/build/build.subsite.xml#L18](includes/build/build.subsite.xml#L18)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>subsite-modules-install-enable</h3>:  Enable required modules after installation of the profile. </summary><p> 
>
>- <b>Code description</b>:  Enable required modules after installation of the profile.  
>- <b>Code link</b>: [includes/build/build.test.xml#L64](includes/build/build.test.xml#L64)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>subsite-setup-files-directory</h3>:  Setup file directory </summary><p> 
>
>- <b>Code description</b>:  Setup file directory  
>- <b>Code link</b>: [includes/build/build.subsite.xml#L222](includes/build/build.subsite.xml#L222)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>subsite-site-backup</h3>:  Backs up files and folders listed in platform.rebuild properties in order to rebuild. </summary><p> 
>
>- <b>Code description</b>:  Backs up files and folders listed in platform.rebuild properties in order to rebuild.  
>- <b>Code link</b>: [includes/build/build.subsite.xml#L45](includes/build/build.subsite.xml#L45)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>subsite-site-backup-item</h3>:  Backs up a site item from the platform that will be removed in order to rebuild. </summary><p> 
>
>- <b>Code description</b>:  Backs up a site item from the platform that will be removed in order to rebuild.  
>- <b>Code link</b>: [includes/build/build.subsite.xml#L162](includes/build/build.subsite.xml#L162)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>subsite-site-restore</h3>:  Restoring sites directory if backed up before rebuild-dev. </summary><p> 
>
>- <b>Code description</b>:  Restoring sites directory if backed up before rebuild-dev.  
>- <b>Code link</b>: [includes/build/build.subsite.xml#L112](includes/build/build.subsite.xml#L112)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>subsite-site-restore-item</h3>:  Restores a site item from the platform.rebuild.backup.destination to the new build. </summary><p> 
>
>- <b>Code description</b>:  Restores a site item from the platform.rebuild.backup.destination to the new build.  
>- <b>Code link</b>: [includes/build/build.subsite.xml#L192](includes/build/build.subsite.xml#L192)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>test-behat-setup</h3>:  Set up Behat. </summary><p> 
>
>- <b>Code description</b>:  Set up Behat.  
>- <b>Code link</b>: [includes/build/build.test.xml#L127](includes/build/build.test.xml#L127)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>test-behat-setup-link</h3>:  Symlink the Behat bin and test directory in the subsite folder. </summary><p> 
>
>- <b>Code description</b>:  Symlink the Behat bin and test directory in the subsite folder.  
>- <b>Code link</b>: [includes/build/build.package.xml#L21](includes/build/build.package.xml#L21)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>test-phpcs-setup</h3>:  Set up PHP CodeSniffer. </summary><p> 
>
>- <b>Code description</b>:  Set up PHP CodeSniffer.  
>- <b>Code link</b>: [includes/build/build.test.xml#L78](includes/build/build.test.xml#L78)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>test-phpcs-setup-prepush</h3>:  Setup the PHP CodeSniffer pre-push hook. </summary><p> 
>
>- <b>Code description</b>:  Setup the PHP CodeSniffer pre-push hook.  
>- <b>Code link</b>: [includes/build/build.test.xml#L111](includes/build/build.test.xml#L111)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>test-quality-assurance</h3>:  Do quality assurance checks. </summary><p> 
>
>- <b>Code description</b>:  Do quality assurance checks.  
>- <b>Code link</b>: [includes/build/build.test.xml#L161](includes/build/build.test.xml#L161)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>test-run-php-codesniffer</h3>:  Do quality assurance checks. </summary><p> 
>
>- <b>Code description</b>:  Do quality assurance checks.  
>- <b>Code link</b>: [includes/build/build.test.xml#L170](includes/build/build.test.xml#L170)
>- <b>Dependencies</b>: 
</p></details>
>
<details><summary><h3>unprotect-folder</h3>:  Make the given folder writeable. </summary><p> 
>
>- <b>Code description</b>:  Make the given folder writeable.  
>- <b>Code link</b>: [includes/build/build.helpers.xml#L32](includes/build/build.helpers.xml#L32)
>- <b>Dependencies</b>: 
</p></details>
>
