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
                    <sub>
                        <ul>
                            <li><p>Dependencies</p>
                            <ul>
                                <li>[drush-create-files-dirs](/includes/build/build.drush.xml#L32)</li>
                                <li>[install](/includes/build/build.test.xml#L5)</li>
                                <li>[subsite-modules-development-enable](/includes/build/build.test.xml#L71)</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-clone](/includes/build/build.clone.xml#L118) </td>
            <td>
                <details>
                    <summary>Build local version of subsite with production data.</summary>
                    <sub>
                        <ul>
                            <li><p>Dependencies</p>
                            <ul>
                                <li>[subsite-database-download](/includes/build/build.clone.xml#L17)</li>
                                <li>[drush-regenerate-settings](/includes/build/build.drush.xml#L111)</li>
                                <li>[subsite-database-import](/includes/build/build.clone.xml#L5)</li>
                                <li>[subsite-modules-development-enable](/includes/build/build.test.xml#L71)</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-code](/includes/build/build.package.xml#L74) </td>
            <td>
                <details>
                    <summary>Build local version of subsite without install.</summary>
                    <sub>
                        <ul>
                            <li><p>Dependencies</p>
                            <ul>
                                <li>[subsite-site-backup](/includes/build/build.subsite.xml#L45)</li>
                                <li>[platform-delete](/includes/build/build.platform.xml#L16)</li>
                                <li>[platform-make](/includes/build/build.platform.xml#L65)</li>
                                <li>[platform-link-resources](/includes/build/build.platform.xml#L54)</li>
                                <li>[subsite-composer-install](/includes/build/build.subsite.xml#L5)</li>
                                <li>[test-behat-setup-link](/includes/build/build.package.xml#L21)</li>
                                <li>[test-behat-setup](/includes/build/build.test.xml#L127)</li>
                                <li>[platform-update-htaccess](/includes/build/build.platform.xml#L108)</li>
                                <li>[test-phpcs-setup](/includes/build/build.test.xml#L78)</li>
                                <li>[subsite-modules-development-download](/includes/build/build.subsite.xml#L36)</li>
                                <li>[subsite-site-restore](/includes/build/build.subsite.xml#L112)</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-keep](/includes/build/build.package.xml#L92) </td>
            <td>
                <details>
                    <summary>Build local version of subsite with backup and restore.</summary>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-release](/includes/build/build.package.xml#L63) </td>
            <td>
                <details>
                    <summary>Build subsite source code release package.</summary>
                    <sub>
                        <ul>
                            <li><p>Dependencies</p>
                            <ul>
                                <li>[build-dist](/includes/build/build.package.xml#L100)</li>
                            </ul></li>
                        </ul>
                    </sub>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [phing.subsite.build.dir](/build.properties.dist#L163) </li>
                                <li>project.release.name</li>
                                <li>project.release.path</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-tests](/includes/build/build.package.xml#L69) </td>
            <td>
                <details>
                    <summary>Build subsite tests code release package.</summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li>project.release.path</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [docker-compose-down](/includes/build/build.docker.xml#L22) </td>
            <td>
                <details>
                    <summary>Trash docker project.</summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [docker.project.id](/build.properties.dist#L389) </li>
                                <li>project.basedir</li>
                                <li>subsite.starterkit.root</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [docker-compose-stop](/includes/build/build.docker.xml#L15) </td>
            <td>
                <details>
                    <summary>Stop docker project.</summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [docker.project.id](/build.properties.dist#L389) </li>
                                <li>project.basedir</li>
                                <li>subsite.starterkit.root</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [docker-compose-up](/includes/build/build.docker.xml#L5) </td>
            <td>
                <details>
                    <summary>Start docker project.</summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [docker.project.id](/build.properties.dist#L389) </li>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                                <li> [platform.package.reference](/build.properties.dist#L128) </li>
                                <li>project.basedir</li>
                                <li> [share.platform.path](/build.properties.dist#L380) </li>
                                <li>subsite.starterkit.root</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [install](/includes/build/build.test.xml#L5) </td>
            <td>
                <details>
                    <summary>Install the subsite.</summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drush.bin](/build.properties.dist#L158) </li>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                                <li> [platform.build.settings.dir](/build.properties.dist#L184) </li>
                                <li> [platform.package.database](/build.properties.dist#L140) </li>
                                <li> [platform.package.reference](/build.properties.dist#L128) </li>
                                <li> [share.platform.path](/build.properties.dist#L380) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [link-docroot](/includes/build/build.package.xml#L28) </td>
            <td>
                <details>
                    <summary>Create symlink from build to docroot.</summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                                <li> [server.docroot](/build.properties.dist#L322) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-behat](/includes/build/build.test.xml#L150) </td>
            <td>
                <details>
                    <summary>Refresh configuration and run behat tests.</summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [behat.bin](/build.properties.dist#L233) </li>
                                <li> [behat.options.passthru](/build.properties.dist#L262) </li>
                                <li> [behat.options.strict](/build.properties.dist#L255) </li>
                                <li> [behat.options.verbosity](/build.properties.dist#L259) </li>
                                <li> [behat.yml.path](/build.properties.dist#L242) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-phpcs](/includes/build/build.test.xml#L186) </td>
            <td>
                <details>
                    <summary>Refresh configuration and run phpcs review.</summary>
                    <sub>
                        <ul>
                            <li><p>Dependencies</p>
                            <ul>
                                <li>[test-phpcs-setup](/includes/build/build.test.xml#L78)</li>
                                <li>[test-run-php-codesniffer](/includes/build/build.test.xml#L170)</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-qa](/includes/build/build.test.xml#L179) </td>
            <td>
                <details>
                    <summary>Refresh configuration and run qa review.</summary>
                    <sub>
                        <ul>
                            <li><p>Dependencies</p>
                            <ul>
                                <li>[test-phpcs-setup](/includes/build/build.test.xml#L78)</li>
                                <li>[test-quality-assurance](/includes/build/build.test.xml#L161)</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-dev](/includes/build/build.deprecated.xml#L5) </td>
            <td>
                <details>
                    <summary> Target build-dev has been replaced by build-code. </summary>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-dist](/includes/build/build.package.xml#L100) </td>
            <td>
                <details>
                    <summary> Create distribution code base. </summary>
                    <sub>
                        <ul>
                            <li><p>Dependencies</p>
                            <ul>
                                <li>[dist-delete](/includes/build/build.dist.xml#L50)</li>
                                <li>[dist-make](/includes/build/build.dist.xml#L58)</li>
                                <li>[dist-copy-resources](/includes/build/build.dist.xml#L18)</li>
                                <li>[dist-composer-install](/includes/build/build.dist.xml#L5)</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [check-for-default-settings-or-rebuild](/includes/build/build.clone.xml#L88) </td>
            <td>
                <details>
                    <summary> Target to check if we have default settings, otherwise propose user to rebuild. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.settings.dir](/build.properties.dist#L184) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [composer-echo-hook-phingcalls](/includes/build/build.composer.xml#L5) </td>
            <td>
                <details>
                    <summary> Echo the composer hook phingcalls. </summary>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [copy-folder](/includes/build/build.helpers.xml#L5) </td>
            <td>
                <details>
                    <summary> Copies a given folder to a new location. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li>copy.destination.path</li>
                                <li> [copy.path.haltonerror](/build.properties.dist#L370) </li>
                                <li>copy.source.path</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [create-tmp-dirs](/includes/build/build.package.xml#L35) </td>
            <td>
                <details>
                    <summary> Create temp dirs. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.package.cachedir](/build.properties.dist#L137) </li>
                                <li> [platform.package.destination](/build.properties.dist#L143) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [delete-folder](/includes/build/build.helpers.xml#L12) </td>
            <td>
                <details>
                    <summary> Delete a given folder. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li>folder.to.delete</li>
                                <li>project.basedir</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-composer-install](/includes/build/build.dist.xml#L5) </td>
            <td>
                <details>
                    <summary> Install Composer dist dependencies for the subsite. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [composer.bin](/build.properties.dist#L157) </li>
                                <li> [dist.build.dir](/build.properties.dist#L165) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-copy-resources](/includes/build/build.dist.xml#L18) </td>
            <td>
                <details>
                    <summary> Copy subsite resources into the build folder. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [dist.build.dir](/build.properties.dist#L165) </li>
                                <li> [dist.build.modules.custom.dir](/build.properties.dist#L205) </li>
                                <li> [dist.build.modules.features.dir](/build.properties.dist#L206) </li>
                                <li> [dist.build.source.dir](/build.properties.dist#L207) </li>
                                <li> [dist.build.themes.dir](/build.properties.dist#L208) </li>
                                <li> [subsite.resources.composer.json](/build.properties.dist#L177) </li>
                                <li> [subsite.resources.composer.lock](/build.properties.dist#L178) </li>
                                <li> [subsite.resources.features.dir](/build.properties.dist#L173) </li>
                                <li> [subsite.resources.modules.dir](/build.properties.dist#L174) </li>
                                <li> [subsite.resources.source.dir](/build.properties.dist#L175) </li>
                                <li> [subsite.resources.themes.dir](/build.properties.dist#L176) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-delete](/includes/build/build.dist.xml#L50) </td>
            <td>
                <details>
                    <summary> Delete the previous distribution build. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [dist.build.dir](/build.properties.dist#L165) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-delete](/includes/build/build.dist.xml#L50) </td>
            <td>
                <details>
                    <summary> Delete the previous distribution build. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [dist.build.dir](/build.properties.dist#L165) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-create-files-dirs](/includes/build/build.drush.xml#L32) </td>
            <td>
                <details>
                    <summary> Create the directories. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drupal.db.name](/build.properties.dist#L82) </li>
                                <li> [drush.bin](/build.properties.dist#L158) </li>
                                <li> [drush.color](/build.properties.dist#L332) </li>
                                <li> [drush.verbose](/build.properties.dist#L329) </li>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                                <li>subsite.starterkit.root</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-dl-rr](/includes/build/build.drush.xml#L162) </td>
            <td>
                <details>
                    <summary> Download registry rebuild. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drush.bin](/build.properties.dist#L158) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-enable-modules](/includes/build/build.drush.xml#L19) </td>
            <td>
                <details>
                    <summary> Enable modules. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li>drupal.modules</li>
                                <li> [drush.bin](/build.properties.dist#L158) </li>
                                <li> [drush.color](/build.properties.dist#L332) </li>
                                <li> [drush.verbose](/build.properties.dist#L329) </li>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-enable-solr](/includes/build/build.drush.xml#L83) </td>
            <td>
                <details>
                    <summary> Activate solr if needed. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drupal.db.name](/build.properties.dist#L82) </li>
                                <li> [drupal.solr.activate](/build.properties.dist#L90) </li>
                                <li> [drupal.solr.env.url](/build.properties.dist#L91) </li>
                                <li> [drush.bin](/build.properties.dist#L158) </li>
                                <li> [drush.color](/build.properties.dist#L332) </li>
                                <li> [drush.verbose](/build.properties.dist#L329) </li>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-make-no-core](/includes/build/build.drush.xml#L99) </td>
            <td>
                <details>
                    <summary> Execute a makefile with the no-core option. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drush.bin](/build.properties.dist#L158) </li>
                                <li> [drush.color](/build.properties.dist#L332) </li>
                                <li>drush.make.root</li>
                                <li>drush.make.target.file</li>
                                <li> [drush.verbose](/build.properties.dist#L329) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-rebuild-node-access](/includes/build/build.drush.xml#L169) </td>
            <td>
                <details>
                    <summary> Rebuild node access. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drush.bin](/build.properties.dist#L158) </li>
                                <li> [drush.color](/build.properties.dist#L332) </li>
                                <li> [drush.verbose](/build.properties.dist#L329) </li>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-regenerate-settings](/includes/build/build.drush.xml#L111) </td>
            <td>
                <details>
                    <summary> Regenerate the settings file with database credentials and development variables. </summary>
                    <sub>
                        <ul>
                            <li><p>Dependencies</p>
                            <ul>
                                <li>[check-for-default-settings-or-rebuild](/includes/build/build.clone.xml#L88)</li>
                            </ul></li>
                        </ul>
                    </sub>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drupal.db.host](/build.properties.dist#L85) </li>
                                <li> [drupal.db.name](/build.properties.dist#L82) </li>
                                <li> [drupal.db.password](/build.properties.dist#L84) </li>
                                <li> [drupal.db.port](/build.properties.dist#L86) </li>
                                <li> [drupal.db.type](/build.properties.dist#L81) </li>
                                <li> [drupal.db.user](/build.properties.dist#L83) </li>
                                <li> [drush.bin](/build.properties.dist#L158) </li>
                                <li> [drush.color](/build.properties.dist#L332) </li>
                                <li> [drush.verbose](/build.properties.dist#L329) </li>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                                <li> [platform.build.files.dir](/build.properties.dist#L185) </li>
                                <li> [platform.build.tmp.dir](/build.properties.dist#L186) </li>
                                <li>subsite.starterkit.root</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-registry-rebuild](/includes/build/build.drush.xml#L142) </td>
            <td>
                <details>
                    <summary> Rebuild registry. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drush.bin](/build.properties.dist#L158) </li>
                                <li> [drush.verbose](/build.properties.dist#L329) </li>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-site-install](/includes/build/build.drush.xml#L5) </td>
            <td>
                <details>
                    <summary> Install the site. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drupal.admin.email](/build.properties.dist#L96) </li>
                                <li> [drupal.admin.password](/build.properties.dist#L95) </li>
                                <li> [drupal.admin.username](/build.properties.dist#L94) </li>
                                <li> [drupal.db.url](/build.properties.dist#L87) </li>
                                <li> [drush.bin](/build.properties.dist#L158) </li>
                                <li> [drush.color](/build.properties.dist#L332) </li>
                                <li> [drush.verbose](/build.properties.dist#L329) </li>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                                <li> [platform.profile.name](/build.properties.dist#L120) </li>
                                <li> [subsite.name](/build.properties.dist#L4) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-create](/includes/build/build.drush.xml#L41) </td>
            <td>
                <details>
                    <summary> Create the database. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drupal.db.name](/build.properties.dist#L82) </li>
                                <li> [drupal.db.url](/build.properties.dist#L87) </li>
                                <li> [drush.bin](/build.properties.dist#L158) </li>
                                <li> [drush.color](/build.properties.dist#L332) </li>
                                <li> [drush.verbose](/build.properties.dist#L329) </li>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-drop](/includes/build/build.drush.xml#L65) </td>
            <td>
                <details>
                    <summary> Drop the database. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drupal.db.name](/build.properties.dist#L82) </li>
                                <li> [drupal.db.url](/build.properties.dist#L87) </li>
                                <li> [drush.bin](/build.properties.dist#L158) </li>
                                <li> [drush.color](/build.properties.dist#L332) </li>
                                <li> [drush.verbose](/build.properties.dist#L329) </li>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-dump](/includes/build/build.drush.xml#L73) </td>
            <td>
                <details>
                    <summary> Backup the database. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drupal.db.name](/build.properties.dist#L82) </li>
                                <li> [drush.bin](/build.properties.dist#L158) </li>
                                <li> [drush.color](/build.properties.dist#L332) </li>
                                <li> [drush.verbose](/build.properties.dist#L329) </li>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-import](/includes/build/build.drush.xml#L49) </td>
            <td>
                <details>
                    <summary> Import a database. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drush.bin](/build.properties.dist#L158) </li>
                                <li> [drush.color](/build.properties.dist#L332) </li>
                                <li> [drush.verbose](/build.properties.dist#L329) </li>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-composer-install](/includes/build/build.platform.xml#L5) </td>
            <td>
                <details>
                    <summary> Install Composer dependencies for the build system. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [composer.bin](/build.properties.dist#L157) </li>
                                <li>project.basedir</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-delete](/includes/build/build.platform.xml#L16) </td>
            <td>
                <details>
                    <summary> Delete the previous development build. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                                <li> [platform.build.settings.dir](/build.properties.dist#L184) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-download](/includes/build/build.platform.xml#L29) </td>
            <td>
                <details>
                    <summary> Download the platform. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.package.cachedir](/build.properties.dist#L137) </li>
                                <li> [platform.package.reference](/build.properties.dist#L128) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-link-resources](/includes/build/build.platform.xml#L54) </td>
            <td>
                <details>
                    <summary> Symlink the source folders for easy development. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.subsite.composer.json](/build.properties.dist#L200) </li>
                                <li> [platform.build.subsite.composer.lock](/build.properties.dist#L201) </li>
                                <li> [platform.build.subsite.modules.custom.dir](/build.properties.dist#L196) </li>
                                <li> [platform.build.subsite.modules.features.dir](/build.properties.dist#L197) </li>
                                <li> [platform.build.subsite.source.dir](/build.properties.dist#L198) </li>
                                <li> [platform.build.subsite.themes.dir](/build.properties.dist#L199) </li>
                                <li> [subsite.resources.composer.json](/build.properties.dist#L177) </li>
                                <li> [subsite.resources.composer.lock](/build.properties.dist#L178) </li>
                                <li> [subsite.resources.features.dir](/build.properties.dist#L173) </li>
                                <li> [subsite.resources.modules.dir](/build.properties.dist#L174) </li>
                                <li> [subsite.resources.source.dir](/build.properties.dist#L175) </li>
                                <li> [subsite.resources.themes.dir](/build.properties.dist#L176) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-make](/includes/build/build.platform.xml#L65) </td>
            <td>
                <details>
                    <summary> Make the development version of the subsite. </summary>
                    <sub>
                        <ul>
                            <li><p>Dependencies</p>
                            <ul>
                                <li>[platform-unpack](/includes/build/build.platform.xml#L82)</li>
                            </ul></li>
                        </ul>
                    </sub>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                                <li> [subsite.make](/build.properties.dist#L10) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-unpack](/includes/build/build.platform.xml#L82) </td>
            <td>
                <details>
                    <summary> Unpack the platform. </summary>
                    <sub>
                        <ul>
                            <li><p>Dependencies</p>
                            <ul>
                                <li>[platform-download](/includes/build/build.platform.xml#L29)</li>
                            </ul></li>
                        </ul>
                    </sub>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                                <li> [platform.package.cachedir](/build.properties.dist#L137) </li>
                                <li> [platform.package.reference](/build.properties.dist#L128) </li>
                                <li>project.basedir</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-update-htaccess](/includes/build/build.platform.xml#L108) </td>
            <td>
                <details>
                    <summary> Update .htaccess. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drupal.htaccess.append.text](/build.properties.dist#L102) </li>
                                <li> [drupal.htaccess.path](/build.properties.dist#L99) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [prompt-for-credentials-and-retry](/includes/build/build.clone.xml#L81) </td>
            <td>
                <details>
                    <summary> Simple prompt for user credentials and recurse into subsite-database-wget. </summary>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-build-documentation-index](/includes/build/build.starterkit.xml#L60) </td>
            <td>
                <details>
                    <summary> Build documentation index. </summary>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-copy-templates](/includes/build/build.starterkit.xml#L11) </td>
            <td>
                <details>
                    <summary> Ensure needed files are present. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li>project.basedir</li>
                                <li> [subsite.starterkit.templates](/build.properties.dist#L49) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-link-binary](/includes/build/build.starterkit.xml#L5) </td>
            <td>
                <details>
                    <summary> Provide handy access with root symlink to starterkit binary. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li>project.basedir</li>
                                <li> [subsite.starterkit.bin](/build.properties.dist#L52) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-upgrade](/includes/build/build.starterkit.xml#L19) </td>
            <td>
                <details>
                    <summary> Upgrade subsite-starterkit 2.x to 3.x. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li>project.basedir</li>
                                <li> [subsite.resources.dir](/build.properties.dist#L171) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-composer-install](/includes/build/build.subsite.xml#L5) </td>
            <td>
                <details>
                    <summary> Install Composer dev dependencies for the subsite. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [composer.bin](/build.properties.dist#L157) </li>
                                <li> [platform.build.subsite.dir](/build.properties.dist#L192) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-database-download](/includes/build/build.clone.xml#L17) </td>
            <td>
                <details>
                    <summary> Download the production database. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li>gunzipped.filename</li>
                                <li>project.database.filename</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-database-import](/includes/build/build.clone.xml#L5) </td>
            <td>
                <details>
                    <summary> Import production database. </summary>
                    <sub>
                        <ul>
                            <li><p>Dependencies</p>
                            <ul>
                                <li>[subsite-database-download](/includes/build/build.clone.xml#L17)</li>
                            </ul></li>
                        </ul>
                    </sub>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li>gunzipped.filename</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-database-wget](/includes/build/build.clone.xml#L40) </td>
            <td>
                <details>
                    <summary> Target to actually fetch the database dump. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li>project.database.download</li>
                                <li>project.database.filename</li>
                                <li>project.database.url</li>
                                <li>project.database.url.credentials</li>
                                <li>project.database.url.htaccess.password</li>
                                <li>project.database.url.htaccess.username</li>
                                <li>project.database.url.scheme</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-development-download](/includes/build/build.subsite.xml#L36) </td>
            <td>
                <details>
                    <summary> Download development modules. </summary>
                    <sub>
                        <ul>
                            <li><p>Dependencies</p>
                            <ul>
                                <li>[subsite-modules-development-makefile](/includes/build/build.subsite.xml#L18)</li>
                            </ul></li>
                        </ul>
                    </sub>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                                <li> [subsite.temporary.development.make](/build.properties.dist#L181) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-development-enable](/includes/build/build.test.xml#L71) </td>
            <td>
                <details>
                    <summary> Enable development modules. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [development.modules.enable](/build.properties.dist#L62) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-development-makefile](/includes/build/build.subsite.xml#L18) </td>
            <td>
                <details>
                    <summary> Generate the makefile used to download development modules. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [development.modules.download](/build.properties.dist#L59) </li>
                                <li> [development.modules.location](/build.properties.dist#L65) </li>
                                <li> [drupal.core.version](/build.properties.dist#L108) </li>
                                <li> [subsite.temporary.development.make](/build.properties.dist#L181) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-install-enable](/includes/build/build.test.xml#L64) </td>
            <td>
                <details>
                    <summary> Enable required modules after installation of the profile. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [subsite.install.modules](/build.properties.dist#L13) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-setup-files-directory](/includes/build/build.subsite.xml#L222) </td>
            <td>
                <details>
                    <summary> Setup file directory </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.files.dir](/build.properties.dist#L185) </li>
                                <li> [platform.build.tmp.dir](/build.properties.dist#L186) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-backup](/includes/build/build.subsite.xml#L45) </td>
            <td>
                <details>
                    <summary> Backs up files and folders listed in platform.rebuild properties in order to rebuild. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.settings.dir](/build.properties.dist#L184) </li>
                                <li> [platform.rebuild.auto](/build.properties.dist#L217) </li>
                                <li> [platform.rebuild.backup.destination](/build.properties.dist#L220) </li>
                                <li> [platform.rebuild.backup.files](/build.properties.dist#L226) </li>
                                <li> [platform.rebuild.backup.folders](/build.properties.dist#L223) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-backup-item](/includes/build/build.subsite.xml#L162) </td>
            <td>
                <details>
                    <summary> Backs up a site item from the platform that will be removed in order to rebuild. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                                <li> [platform.rebuild.backup.destination](/build.properties.dist#L220) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-restore](/includes/build/build.subsite.xml#L112) </td>
            <td>
                <details>
                    <summary> Restoring sites directory if backed up before rebuild-dev. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.rebuild.auto](/build.properties.dist#L217) </li>
                                <li> [platform.rebuild.backup.destination](/build.properties.dist#L220) </li>
                                <li> [platform.rebuild.backup.files](/build.properties.dist#L226) </li>
                                <li> [platform.rebuild.backup.folders](/build.properties.dist#L223) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-restore-item](/includes/build/build.subsite.xml#L192) </td>
            <td>
                <details>
                    <summary> Restores a site item from the platform.rebuild.backup.destination to the new build. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.dir](/build.properties.dist#L116) </li>
                                <li> [platform.rebuild.backup.destination](/build.properties.dist#L220) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-behat-setup](/includes/build/build.test.xml#L127) </td>
            <td>
                <details>
                    <summary> Set up Behat. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [behat.formatter.name](/build.properties.dist#L251) </li>
                                <li>behat.yml.content</li>
                                <li> [behat.yml.path](/build.properties.dist#L242) </li>
                                <li> [behat.yml.template](/build.properties.dist#L239) </li>
                                <li>drupal.site.dir</li>
                                <li>project.code.dir</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-behat-setup-link](/includes/build/build.package.xml#L21) </td>
            <td>
                <details>
                    <summary> Symlink the Behat bin and test directory in the subsite folder. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.subsite.dir](/build.properties.dist#L192) </li>
                                <li>project.basedir</li>
                                <li> [subsite.starterkit.vendor](/build.properties.dist#L51) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-phpcs-setup](/includes/build/build.test.xml#L78) </td>
            <td>
                <details>
                    <summary> Set up PHP CodeSniffer. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [phpcs.config](/build.properties.dist#L275) </li>
                                <li> [phpcs.extensions](/build.properties.dist#L272) </li>
                                <li> [phpcs.files](/build.properties.dist#L281) </li>
                                <li> [phpcs.global.config](/build.properties.dist#L299) </li>
                                <li> [phpcs.ignore](/build.properties.dist#L284) </li>
                                <li> [phpcs.passwarnings](/build.properties.dist#L287) </li>
                                <li> [phpcs.progress](/build.properties.dist#L296) </li>
                                <li> [phpcs.report](/build.properties.dist#L290) </li>
                                <li> [phpcs.sniffcodes](/build.properties.dist#L293) </li>
                                <li> [phpcs.standards](/build.properties.dist#L278) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-phpcs-setup-prepush](/includes/build/build.test.xml#L111) </td>
            <td>
                <details>
                    <summary> Setup the PHP CodeSniffer pre-push hook. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [phpcs.prepush.destination](/build.properties.dist#L307) </li>
                                <li> [phpcs.prepush.enable](/build.properties.dist#L303) </li>
                                <li> [phpcs.prepush.source](/build.properties.dist#L306) </li>
                                <li>project.basedir</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-quality-assurance](/includes/build/build.test.xml#L161) </td>
            <td>
                <details>
                    <summary> Do quality assurance checks. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [subsite.starterkit.bin](/build.properties.dist#L52) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-php-codesniffer](/includes/build/build.test.xml#L170) </td>
            <td>
                <details>
                    <summary> Do quality assurance checks. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [subsite.starterkit.bin](/build.properties.dist#L52) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [unprotect-folder](/includes/build/build.helpers.xml#L32) </td>
            <td>
                <details>
                    <summary> Make the given folder writeable. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li>folder.to.unprotect</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
    </tbody>
</table>