<big><table>
    <thead>
        <tr align="left" valign="top">
            <th>Command</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr align="left" valign="top">
            <td> [build-clean](/includes/build/build.test.xml#L193) </td>
            <td>
                <details>
                    <summary>Build local version of subsite with a clean install.</summary>
                    <code>
                        <ul>
                            <li>dependencies:</li>
                            <ul>
                                <li>[drush-create-files-dirs](/includes/build/build.drush.xml#L32)</li>
                                <li>[install](/includes/build/build.test.xml#L5)</li>
                                <li>[subsite-modules-development-enable](/includes/build/build.test.xml#L71)</li>
                            </ul>
                        </ul>
                    </code>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-clone](/includes/build/build.clone.xml#L118) </td>
            <td>
                <details>
                    <summary>Build local version of subsite with production data.</summary>
> - dependencies: [subsite-database-download](/includes/build/build.clone.xml#L17), [drush-regenerate-settings](/includes/build/build.drush.xml#L111), [subsite-database-import](/includes/build/build.clone.xml#L5), [subsite-modules-development-enable](/includes/build/build.test.xml#L71)
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-code](/includes/build/build.package.xml#L74) </td>
            <td>
                <details>
                    <summary>Build local version of subsite without install.</summary>
> - dependencies: [subsite-site-backup](/includes/build/build.subsite.xml#L45), [platform-delete](/includes/build/build.platform.xml#L16), [platform-make](/includes/build/build.platform.xml#L65), [platform-link-resources](/includes/build/build.platform.xml#L54), [subsite-composer-install](/includes/build/build.subsite.xml#L5), [test-behat-setup-link](/includes/build/build.package.xml#L21), [test-behat-setup](/includes/build/build.test.xml#L127), [platform-update-htaccess](/includes/build/build.platform.xml#L108), [test-phpcs-setup](/includes/build/build.test.xml#L78), [subsite-modules-development-download](/includes/build/build.subsite.xml#L36), [subsite-site-restore](/includes/build/build.subsite.xml#L112)
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-keep](/includes/build/build.package.xml#L92) </td>
            <td>
                <details>
                    <summary>Build local version of subsite with backup and restore.</summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-release](/includes/build/build.package.xml#L63) </td>
            <td>
                <details>
                    <summary>Build subsite source code release package.</summary>
> - dependencies: [build-dist](/includes/build/build.package.xml#L100)
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-tests](/includes/build/build.package.xml#L69) </td>
            <td>
                <details>
                    <summary>Build subsite tests code release package.</summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [docker-compose-down](/includes/build/build.docker.xml#L22) </td>
            <td>
                <details>
                    <summary>Trash docker project.</summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [docker-compose-stop](/includes/build/build.docker.xml#L15) </td>
            <td>
                <details>
                    <summary>Stop docker project.</summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [docker-compose-up](/includes/build/build.docker.xml#L5) </td>
            <td>
                <details>
                    <summary>Start docker project.</summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [install](/includes/build/build.test.xml#L5) </td>
            <td>
                <details>
                    <summary>Install the subsite.</summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [link-docroot](/includes/build/build.package.xml#L28) </td>
            <td>
                <details>
                    <summary>Create symlink from build to docroot.</summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-behat](/includes/build/build.test.xml#L150) </td>
            <td>
                <details>
                    <summary>Refresh configuration and run behat tests.</summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-phpcs](/includes/build/build.test.xml#L186) </td>
            <td>
                <details>
                    <summary>Refresh configuration and run phpcs review.</summary>
> - dependencies: [test-phpcs-setup](/includes/build/build.test.xml#L78), [test-run-php-codesniffer](/includes/build/build.test.xml#L170)
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-qa](/includes/build/build.test.xml#L179) </td>
            <td>
                <details>
                    <summary>Refresh configuration and run qa review.</summary>
> - dependencies: [test-phpcs-setup](/includes/build/build.test.xml#L78), [test-quality-assurance](/includes/build/build.test.xml#L161)
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-dev](/includes/build/build.deprecated.xml#L5) </td>
            <td>
                <details>
                    <summary> Target build-dev has been replaced by build-code. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-dist](/includes/build/build.package.xml#L100) </td>
            <td>
                <details>
                    <summary> Create distribution code base. </summary>
> - dependencies: [dist-delete](/includes/build/build.dist.xml#L50), [dist-make](/includes/build/build.dist.xml#L58), [dist-copy-resources](/includes/build/build.dist.xml#L18), [dist-composer-install](/includes/build/build.dist.xml#L5)
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [check-for-default-settings-or-rebuild](/includes/build/build.clone.xml#L88) </td>
            <td>
                <details>
                    <summary> Target to check if we have default settings, otherwise propose user to rebuild. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [composer-echo-hook-phingcalls](/includes/build/build.composer.xml#L5) </td>
            <td>
                <details>
                    <summary> Echo the composer hook phingcalls. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [copy-folder](/includes/build/build.helpers.xml#L5) </td>
            <td>
                <details>
                    <summary> Copies a given folder to a new location. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [create-tmp-dirs](/includes/build/build.package.xml#L35) </td>
            <td>
                <details>
                    <summary> Create temp dirs. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [delete-folder](/includes/build/build.helpers.xml#L12) </td>
            <td>
                <details>
                    <summary> Delete a given folder. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-composer-install](/includes/build/build.dist.xml#L5) </td>
            <td>
                <details>
                    <summary> Install Composer dist dependencies for the subsite. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-copy-resources](/includes/build/build.dist.xml#L18) </td>
            <td>
                <details>
                    <summary> Copy subsite resources into the build folder. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-delete](/includes/build/build.dist.xml#L50) </td>
            <td>
                <details>
                    <summary> Delete the previous distribution build. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-delete](/includes/build/build.dist.xml#L50) </td>
            <td>
                <details>
                    <summary> Delete the previous distribution build. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-create-files-dirs](/includes/build/build.drush.xml#L32) </td>
            <td>
                <details>
                    <summary> Create the directories. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-dl-rr](/includes/build/build.drush.xml#L162) </td>
            <td>
                <details>
                    <summary> Download registry rebuild. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-enable-modules](/includes/build/build.drush.xml#L19) </td>
            <td>
                <details>
                    <summary> Enable modules. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-enable-solr](/includes/build/build.drush.xml#L83) </td>
            <td>
                <details>
                    <summary> Activate solr if needed. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-make-no-core](/includes/build/build.drush.xml#L99) </td>
            <td>
                <details>
                    <summary> Execute a makefile with the no-core option. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-rebuild-node-access](/includes/build/build.drush.xml#L169) </td>
            <td>
                <details>
                    <summary> Rebuild node access. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-regenerate-settings](/includes/build/build.drush.xml#L111) </td>
            <td>
                <details>
                    <summary> Regenerate the settings file with database credentials and development variables. </summary>
> - dependencies: [check-for-default-settings-or-rebuild](/includes/build/build.clone.xml#L88)
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-registry-rebuild](/includes/build/build.drush.xml#L142) </td>
            <td>
                <details>
                    <summary> Rebuild registry. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-site-install](/includes/build/build.drush.xml#L5) </td>
            <td>
                <details>
                    <summary> Install the site. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-create](/includes/build/build.drush.xml#L41) </td>
            <td>
                <details>
                    <summary> Create the database. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-drop](/includes/build/build.drush.xml#L65) </td>
            <td>
                <details>
                    <summary> Drop the database. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-dump](/includes/build/build.drush.xml#L73) </td>
            <td>
                <details>
                    <summary> Backup the database. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-import](/includes/build/build.drush.xml#L49) </td>
            <td>
                <details>
                    <summary> Import a database. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-composer-install](/includes/build/build.platform.xml#L5) </td>
            <td>
                <details>
                    <summary> Install Composer dependencies for the build system. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-delete](/includes/build/build.platform.xml#L16) </td>
            <td>
                <details>
                    <summary> Delete the previous development build. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-download](/includes/build/build.platform.xml#L29) </td>
            <td>
                <details>
                    <summary> Download the platform. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-link-resources](/includes/build/build.platform.xml#L54) </td>
            <td>
                <details>
                    <summary> Symlink the source folders for easy development. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-make](/includes/build/build.platform.xml#L65) </td>
            <td>
                <details>
                    <summary> Make the development version of the subsite. </summary>
> - dependencies: [platform-unpack](/includes/build/build.platform.xml#L82)
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-unpack](/includes/build/build.platform.xml#L82) </td>
            <td>
                <details>
                    <summary> Unpack the platform. </summary>
> - dependencies: [platform-download](/includes/build/build.platform.xml#L29)
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-update-htaccess](/includes/build/build.platform.xml#L108) </td>
            <td>
                <details>
                    <summary> Update .htaccess. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [prompt-for-credentials-and-retry](/includes/build/build.clone.xml#L81) </td>
            <td>
                <details>
                    <summary> Simple prompt for user credentials and recurse into subsite-database-wget. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-build-documentation-index](/includes/build/build.starterkit.xml#L60) </td>
            <td>
                <details>
                    <summary> Build documentation index. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-copy-templates](/includes/build/build.starterkit.xml#L11) </td>
            <td>
                <details>
                    <summary> Ensure needed files are present. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-link-binary](/includes/build/build.starterkit.xml#L5) </td>
            <td>
                <details>
                    <summary> Provide handy access with root symlink to starterkit binary. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-upgrade](/includes/build/build.starterkit.xml#L19) </td>
            <td>
                <details>
                    <summary> Upgrade subsite-starterkit 2.x to 3.x. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-composer-install](/includes/build/build.subsite.xml#L5) </td>
            <td>
                <details>
                    <summary> Install Composer dev dependencies for the subsite. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-database-download](/includes/build/build.clone.xml#L17) </td>
            <td>
                <details>
                    <summary> Download the production database. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-database-import](/includes/build/build.clone.xml#L5) </td>
            <td>
                <details>
                    <summary> Import production database. </summary>
> - dependencies: [subsite-database-download](/includes/build/build.clone.xml#L17)
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-database-wget](/includes/build/build.clone.xml#L40) </td>
            <td>
                <details>
                    <summary> Target to actually fetch the database dump. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-development-download](/includes/build/build.subsite.xml#L36) </td>
            <td>
                <details>
                    <summary> Download development modules. </summary>
> - dependencies: [subsite-modules-development-makefile](/includes/build/build.subsite.xml#L18)
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-development-enable](/includes/build/build.test.xml#L71) </td>
            <td>
                <details>
                    <summary> Enable development modules. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-development-makefile](/includes/build/build.subsite.xml#L18) </td>
            <td>
                <details>
                    <summary> Generate the makefile used to download development modules. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-install-enable](/includes/build/build.test.xml#L64) </td>
            <td>
                <details>
                    <summary> Enable required modules after installation of the profile. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-setup-files-directory](/includes/build/build.subsite.xml#L222) </td>
            <td>
                <details>
                    <summary> Setup file directory </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-backup](/includes/build/build.subsite.xml#L45) </td>
            <td>
                <details>
                    <summary> Backs up files and folders listed in platform.rebuild properties in order to rebuild. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-backup-item](/includes/build/build.subsite.xml#L162) </td>
            <td>
                <details>
                    <summary> Backs up a site item from the platform that will be removed in order to rebuild. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-restore](/includes/build/build.subsite.xml#L112) </td>
            <td>
                <details>
                    <summary> Restoring sites directory if backed up before rebuild-dev. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-restore-item](/includes/build/build.subsite.xml#L192) </td>
            <td>
                <details>
                    <summary> Restores a site item from the platform.rebuild.backup.destination to the new build. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-behat-setup](/includes/build/build.test.xml#L127) </td>
            <td>
                <details>
                    <summary> Set up Behat. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-behat-setup-link](/includes/build/build.package.xml#L21) </td>
            <td>
                <details>
                    <summary> Symlink the Behat bin and test directory in the subsite folder. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-phpcs-setup](/includes/build/build.test.xml#L78) </td>
            <td>
                <details>
                    <summary> Set up PHP CodeSniffer. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-phpcs-setup-prepush](/includes/build/build.test.xml#L111) </td>
            <td>
                <details>
                    <summary> Setup the PHP CodeSniffer pre-push hook. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-quality-assurance](/includes/build/build.test.xml#L161) </td>
            <td>
                <details>
                    <summary> Do quality assurance checks. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-php-codesniffer](/includes/build/build.test.xml#L170) </td>
            <td>
                <details>
                    <summary> Do quality assurance checks. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [unprotect-folder](/includes/build/build.helpers.xml#L32) </td>
            <td>
                <details>
                    <summary> Make the given folder writeable. </summary>
> - dependencies: 
                </details>
            </td>
        </tr>
    </tbody>
</table>
