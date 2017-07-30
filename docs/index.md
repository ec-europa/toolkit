## Table
<big><table width="100%" cellpadding="0" cellspacing="0">
    <thead>
        <tr align="left" valign="top" cellpadding="0" cellspacing="0">
            <th cellpadding="0" cellspacing="0">Command</th>
            <th cellpadding="0" cellspacing="0">Description</th>
        </tr>
    </thead>
    <tbody>
        <tr align="left" valign="top">
            <td nowrap><a name="build-clean">[:link:](#build-clean)</a> [build-clean](/includes/build/build.test.xml#L193)</td>
            <td width="100%">
                <details>
                    <summary>Build local version of subsite with a clean install.</summary>
                    <sub>
                        <ul>
                            <li><p>Dependencies</p>
                            <ul>
                                <li>[drush-create-files-dirs](/includes/build/build.drush.xml#L32)</li>
                                <li>[install](/includes/build/build.test.xml#L5)</li>
                                <li>[subsite-modules-devel-en](/includes/build/build.test.xml#L71)</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="build-clone"></a> [build-clone](/includes/build/build.clone.xml#L118) </h4></td>
            <td width="100%">
                <details>
                    <summary>Build local version of subsite with production data.</summary>
                    <sub>
                        <ul>
                            <li><p>Dependencies</p>
                            <ul>
                                <li>[subsite-database-download](/includes/build/build.clone.xml#L17)</li>
                                <li>[drush-regenerate-settings](/includes/build/build.drush.xml#L111)</li>
                                <li>[subsite-database-import](/includes/build/build.clone.xml#L5)</li>
                                <li>[subsite-modules-devel-en](/includes/build/build.test.xml#L71)</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="build-code"></a> [build-code](/includes/build/build.package.xml#L74) </h4></td>
            <td width="100%">
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
                                <li>[subsite-modules-devel-dl](/includes/build/build.subsite.xml#L36)</li>
                                <li>[subsite-site-restore](/includes/build/build.subsite.xml#L112)</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="build-keep"></a> [build-keep](/includes/build/build.package.xml#L92) </h4></td>
            <td width="100%">
                <details>
                    <summary>Build local version of subsite with backup and restore.</summary>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="build-release"></a> [build-release](/includes/build/build.package.xml#L63) </h4></td>
            <td width="100%">
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
                                <li> [phing.subsite.build.dir](/build.properties.dist#L164) </li>
                                <li>project.release.name</li>
                                <li>project.release.path</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="build-tests"></a> [build-tests](/includes/build/build.package.xml#L69) </h4></td>
            <td width="100%">
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
            <td nowrap><a name="docker-compose-down"></a> [docker-compose-down](/includes/build/build.docker.xml#L22) </h4></td>
            <td width="100%">
                <details>
                    <summary>Trash docker project.</summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [docker.project.id](/build.properties.dist#L390) </li>
                                <li>project.basedir</li>
                                <li>subsite.starterkit.root</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="docker-compose-stop"></a> [docker-compose-stop](/includes/build/build.docker.xml#L15) </h4></td>
            <td width="100%">
                <details>
                    <summary>Stop docker project.</summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [docker.project.id](/build.properties.dist#L390) </li>
                                <li>project.basedir</li>
                                <li>subsite.starterkit.root</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="docker-compose-up"></a> [docker-compose-up](/includes/build/build.docker.xml#L5) </h4></td>
            <td width="100%">
                <details>
                    <summary>Start docker project.</summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [docker.project.id](/build.properties.dist#L390) </li>
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                                <li> [platform.package.reference](/build.properties.dist#L129) </li>
                                <li>project.basedir</li>
                                <li> [share.platform.path](/build.properties.dist#L381) </li>
                                <li>subsite.starterkit.root</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="install"></a> [install](/includes/build/build.test.xml#L5) </h4></td>
            <td width="100%">
                <details>
                    <summary>Install the subsite.</summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drush.bin](/build.properties.dist#L159) </li>
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                                <li> [platform.build.settings.dir](/build.properties.dist#L185) </li>
                                <li> [platform.package.database](/build.properties.dist#L141) </li>
                                <li> [platform.package.reference](/build.properties.dist#L129) </li>
                                <li> [share.platform.path](/build.properties.dist#L381) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="link-docroot"></a> [link-docroot](/includes/build/build.package.xml#L28) </h4></td>
            <td width="100%">
                <details>
                    <summary>Create symlink from build to docroot.</summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                                <li> [server.docroot](/build.properties.dist#L323) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="test-run-behat"></a> [test-run-behat](/includes/build/build.test.xml#L150) </h4></td>
            <td width="100%">
                <details>
                    <summary>Refresh configuration and run behat tests.</summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [behat.bin](/build.properties.dist#L234) </li>
                                <li> [behat.options.passthru](/build.properties.dist#L263) </li>
                                <li> [behat.options.strict](/build.properties.dist#L256) </li>
                                <li> [behat.options.verbosity](/build.properties.dist#L260) </li>
                                <li> [behat.yml.path](/build.properties.dist#L243) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="test-run-phpcs"></a> [test-run-phpcs](/includes/build/build.test.xml#L186) </h4></td>
            <td width="100%">
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
            <td nowrap><a name="test-run-qa"></a> [test-run-qa](/includes/build/build.test.xml#L179) </h4></td>
            <td width="100%">
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
    </tbody>
</table>

## Table
<big><table width="100%">
    <thead>
        <tr align="left" valign="top">
            <th>Command</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr align="left" valign="top">
            <td nowrap><a name="dist-composer-install"></a> [dist-composer-install](/includes/build/build.dist.xml#L5) </h4></td>
            <td width="100%">
                <details>
                    <summary> Install Composer dist dependencies for the subsite. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [composer.bin](/build.properties.dist#L158) </li>
                                <li> [dist.build.dir](/build.properties.dist#L166) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="dist-copy-resources"></a> [dist-copy-resources](/includes/build/build.dist.xml#L18) </h4></td>
            <td width="100%">
                <details>
                    <summary> Copy subsite resources into the build folder. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [dist.build.dir](/build.properties.dist#L166) </li>
                                <li> [dist.build.modules.custom.dir](/build.properties.dist#L206) </li>
                                <li> [dist.build.modules.features.dir](/build.properties.dist#L207) </li>
                                <li> [dist.build.source.dir](/build.properties.dist#L208) </li>
                                <li> [dist.build.themes.dir](/build.properties.dist#L209) </li>
                                <li> [subsite.resources.composer.json](/build.properties.dist#L178) </li>
                                <li> [subsite.resources.composer.lock](/build.properties.dist#L179) </li>
                                <li> [subsite.resources.features.dir](/build.properties.dist#L174) </li>
                                <li> [subsite.resources.modules.dir](/build.properties.dist#L175) </li>
                                <li> [subsite.resources.source.dir](/build.properties.dist#L176) </li>
                                <li> [subsite.resources.themes.dir](/build.properties.dist#L177) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="dist-delete"></a> [dist-delete](/includes/build/build.dist.xml#L50) </h4></td>
            <td width="100%">
                <details>
                    <summary> Delete the previous distribution build. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [dist.build.dir](/build.properties.dist#L166) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="dist-delete"></a> [dist-delete](/includes/build/build.dist.xml#L50) </h4></td>
            <td width="100%">
                <details>
                    <summary> Delete the previous distribution build. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [dist.build.dir](/build.properties.dist#L166) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="drush-create-files-dirs"></a> [drush-create-files-dirs](/includes/build/build.drush.xml#L32) </h4></td>
            <td width="100%">
                <details>
                    <summary> Create the directories. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drupal.db.name](/build.properties.dist#L83) </li>
                                <li> [drush.bin](/build.properties.dist#L159) </li>
                                <li> [drush.color](/build.properties.dist#L333) </li>
                                <li> [drush.verbose](/build.properties.dist#L330) </li>
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                                <li>subsite.starterkit.root</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="drush-dl-rr"></a> [drush-dl-rr](/includes/build/build.drush.xml#L162) </h4></td>
            <td width="100%">
                <details>
                    <summary> Download registry rebuild. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drush.bin](/build.properties.dist#L159) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="drush-enable-solr"></a> [drush-enable-solr](/includes/build/build.drush.xml#L83) </h4></td>
            <td width="100%">
                <details>
                    <summary> Activate solr if needed. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drupal.db.name](/build.properties.dist#L83) </li>
                                <li> [drupal.solr.activate](/build.properties.dist#L91) </li>
                                <li> [drupal.solr.env.url](/build.properties.dist#L92) </li>
                                <li> [drush.bin](/build.properties.dist#L159) </li>
                                <li> [drush.color](/build.properties.dist#L333) </li>
                                <li> [drush.verbose](/build.properties.dist#L330) </li>
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="drush-make-no-core"></a> [drush-make-no-core](/includes/build/build.drush.xml#L99) </h4></td>
            <td width="100%">
                <details>
                    <summary> Execute a makefile with the no-core option. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drush.bin](/build.properties.dist#L159) </li>
                                <li> [drush.color](/build.properties.dist#L333) </li>
                                <li>drush.make.root</li>
                                <li>drush.make.target.file</li>
                                <li> [drush.verbose](/build.properties.dist#L330) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="drush-rebuild-node-access"></a> [drush-rebuild-node-access](/includes/build/build.drush.xml#L169) </h4></td>
            <td width="100%">
                <details>
                    <summary> Rebuild node access. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drush.bin](/build.properties.dist#L159) </li>
                                <li> [drush.color](/build.properties.dist#L333) </li>
                                <li> [drush.verbose](/build.properties.dist#L330) </li>
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="drush-regenerate-settings"></a> [drush-regenerate-settings](/includes/build/build.drush.xml#L111) </h4></td>
            <td width="100%">
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
                                <li> [drupal.db.host](/build.properties.dist#L86) </li>
                                <li> [drupal.db.name](/build.properties.dist#L83) </li>
                                <li> [drupal.db.password](/build.properties.dist#L85) </li>
                                <li> [drupal.db.port](/build.properties.dist#L87) </li>
                                <li> [drupal.db.type](/build.properties.dist#L82) </li>
                                <li> [drupal.db.user](/build.properties.dist#L84) </li>
                                <li> [drush.bin](/build.properties.dist#L159) </li>
                                <li> [drush.color](/build.properties.dist#L333) </li>
                                <li> [drush.verbose](/build.properties.dist#L330) </li>
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                                <li> [platform.build.files.dir](/build.properties.dist#L186) </li>
                                <li> [platform.build.tmp.dir](/build.properties.dist#L187) </li>
                                <li>subsite.starterkit.root</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="drush-registry-rebuild"></a> [drush-registry-rebuild](/includes/build/build.drush.xml#L142) </h4></td>
            <td width="100%">
                <details>
                    <summary> Rebuild registry. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drush.bin](/build.properties.dist#L159) </li>
                                <li> [drush.verbose](/build.properties.dist#L330) </li>
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="drush-site-install"></a> [drush-site-install](/includes/build/build.drush.xml#L5) </h4></td>
            <td width="100%">
                <details>
                    <summary> Install the site. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drupal.admin.email](/build.properties.dist#L97) </li>
                                <li> [drupal.admin.password](/build.properties.dist#L96) </li>
                                <li> [drupal.admin.username](/build.properties.dist#L95) </li>
                                <li> [drupal.db.url](/build.properties.dist#L88) </li>
                                <li> [drush.bin](/build.properties.dist#L159) </li>
                                <li> [drush.color](/build.properties.dist#L333) </li>
                                <li> [drush.verbose](/build.properties.dist#L330) </li>
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                                <li> [platform.profile.name](/build.properties.dist#L121) </li>
                                <li> [subsite.name](/build.properties.dist#L5) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="drush-sql-create"></a> [drush-sql-create](/includes/build/build.drush.xml#L41) </h4></td>
            <td width="100%">
                <details>
                    <summary> Create the database. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drupal.db.name](/build.properties.dist#L83) </li>
                                <li> [drupal.db.url](/build.properties.dist#L88) </li>
                                <li> [drush.bin](/build.properties.dist#L159) </li>
                                <li> [drush.color](/build.properties.dist#L333) </li>
                                <li> [drush.verbose](/build.properties.dist#L330) </li>
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="drush-sql-drop"></a> [drush-sql-drop](/includes/build/build.drush.xml#L65) </h4></td>
            <td width="100%">
                <details>
                    <summary> Drop the database. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drupal.db.name](/build.properties.dist#L83) </li>
                                <li> [drupal.db.url](/build.properties.dist#L88) </li>
                                <li> [drush.bin](/build.properties.dist#L159) </li>
                                <li> [drush.color](/build.properties.dist#L333) </li>
                                <li> [drush.verbose](/build.properties.dist#L330) </li>
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="drush-sql-dump"></a> [drush-sql-dump](/includes/build/build.drush.xml#L73) </h4></td>
            <td width="100%">
                <details>
                    <summary> Backup the database. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drupal.db.name](/build.properties.dist#L83) </li>
                                <li> [drush.bin](/build.properties.dist#L159) </li>
                                <li> [drush.color](/build.properties.dist#L333) </li>
                                <li> [drush.verbose](/build.properties.dist#L330) </li>
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="drush-sql-import"></a> [drush-sql-import](/includes/build/build.drush.xml#L49) </h4></td>
            <td width="100%">
                <details>
                    <summary> Import a database. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drush.bin](/build.properties.dist#L159) </li>
                                <li> [drush.color](/build.properties.dist#L333) </li>
                                <li> [drush.verbose](/build.properties.dist#L330) </li>
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="platform-composer-install"></a> [platform-composer-install](/includes/build/build.platform.xml#L5) </h4></td>
            <td width="100%">
                <details>
                    <summary> Install Composer dependencies for the build system. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [composer.bin](/build.properties.dist#L158) </li>
                                <li>project.basedir</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="platform-delete"></a> [platform-delete](/includes/build/build.platform.xml#L16) </h4></td>
            <td width="100%">
                <details>
                    <summary> Delete the previous development build. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                                <li> [platform.build.settings.dir](/build.properties.dist#L185) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="platform-download"></a> [platform-download](/includes/build/build.platform.xml#L29) </h4></td>
            <td width="100%">
                <details>
                    <summary> Download the platform. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.package.cachedir](/build.properties.dist#L138) </li>
                                <li> [platform.package.reference](/build.properties.dist#L129) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="platform-link-resources"></a> [platform-link-resources](/includes/build/build.platform.xml#L54) </h4></td>
            <td width="100%">
                <details>
                    <summary> Symlink the source folders for easy development. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.subsite.composer.json](/build.properties.dist#L201) </li>
                                <li> [platform.build.subsite.composer.lock](/build.properties.dist#L202) </li>
                                <li> [platform.build.subsite.modules.custom.dir](/build.properties.dist#L197) </li>
                                <li> [platform.build.subsite.modules.features.dir](/build.properties.dist#L198) </li>
                                <li> [platform.build.subsite.source.dir](/build.properties.dist#L199) </li>
                                <li> [platform.build.subsite.themes.dir](/build.properties.dist#L200) </li>
                                <li> [subsite.resources.composer.json](/build.properties.dist#L178) </li>
                                <li> [subsite.resources.composer.lock](/build.properties.dist#L179) </li>
                                <li> [subsite.resources.features.dir](/build.properties.dist#L174) </li>
                                <li> [subsite.resources.modules.dir](/build.properties.dist#L175) </li>
                                <li> [subsite.resources.source.dir](/build.properties.dist#L176) </li>
                                <li> [subsite.resources.themes.dir](/build.properties.dist#L177) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="platform-make"></a> [platform-make](/includes/build/build.platform.xml#L65) </h4></td>
            <td width="100%">
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
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                                <li> [subsite.make](/build.properties.dist#L11) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="platform-unpack"></a> [platform-unpack](/includes/build/build.platform.xml#L82) </h4></td>
            <td width="100%">
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
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                                <li> [platform.package.cachedir](/build.properties.dist#L138) </li>
                                <li> [platform.package.reference](/build.properties.dist#L129) </li>
                                <li>project.basedir</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="platform-update-htaccess"></a> [platform-update-htaccess](/includes/build/build.platform.xml#L108) </h4></td>
            <td width="100%">
                <details>
                    <summary> Update .htaccess. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [drupal.htaccess.append.text](/build.properties.dist#L103) </li>
                                <li> [drupal.htaccess.path](/build.properties.dist#L100) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="starterkit-build-docs"></a> [starterkit-build-docs](/includes/build/build.starterkit.xml#L60) </h4></td>
            <td width="100%">
                <details>
                    <summary> Build documentation index. </summary>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="starterkit-copy-templates"></a> [starterkit-copy-templates](/includes/build/build.starterkit.xml#L11) </h4></td>
            <td width="100%">
                <details>
                    <summary> Ensure needed files are present. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li>project.basedir</li>
                                <li> [subsite.starterkit.templates](/build.properties.dist#L50) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="starterkit-link-binary"></a> [starterkit-link-binary](/includes/build/build.starterkit.xml#L5) </h4></td>
            <td width="100%">
                <details>
                    <summary> Provide handy access with root symlink to starterkit binary. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li>project.basedir</li>
                                <li> [subsite.starterkit.bin](/build.properties.dist#L53) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="starterkit-upgrade"></a> [starterkit-upgrade](/includes/build/build.starterkit.xml#L19) </h4></td>
            <td width="100%">
                <details>
                    <summary> Upgrade subsite-starterkit 2.x to 3.x. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li>project.basedir</li>
                                <li> [subsite.resources.dir](/build.properties.dist#L172) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="subsite-composer-install"></a> [subsite-composer-install](/includes/build/build.subsite.xml#L5) </h4></td>
            <td width="100%">
                <details>
                    <summary> Install Composer dev dependencies for the subsite. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [composer.bin](/build.properties.dist#L158) </li>
                                <li> [platform.build.subsite.dir](/build.properties.dist#L193) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="subsite-database-download"></a> [subsite-database-download](/includes/build/build.clone.xml#L17) </h4></td>
            <td width="100%">
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
            <td nowrap><a name="subsite-database-import"></a> [subsite-database-import](/includes/build/build.clone.xml#L5) </h4></td>
            <td width="100%">
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
            <td nowrap><a name="subsite-database-wget"></a> [subsite-database-wget](/includes/build/build.clone.xml#L40) </h4></td>
            <td width="100%">
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
            <td nowrap><a name="subsite-modules-devel-dl"></a> [subsite-modules-devel-dl](/includes/build/build.subsite.xml#L36) </h4></td>
            <td width="100%">
                <details>
                    <summary> Download development modules. </summary>
                    <sub>
                        <ul>
                            <li><p>Dependencies</p>
                            <ul>
                                <li>[subsite-modules-devel-mf](/includes/build/build.subsite.xml#L18)</li>
                            </ul></li>
                        </ul>
                    </sub>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.dir](/build.properties.dist#L117) </li>
                                <li> [subsite.temporary.development.make](/build.properties.dist#L182) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="subsite-modules-devel-en"></a> [subsite-modules-devel-en](/includes/build/build.test.xml#L71) </h4></td>
            <td width="100%">
                <details>
                    <summary> Enable development modules. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [development.modules.enable](/build.properties.dist#L63) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="subsite-modules-devel-mf"></a> [subsite-modules-devel-mf](/includes/build/build.subsite.xml#L18) </h4></td>
            <td width="100%">
                <details>
                    <summary> Generate the makefile used to download development modules. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [development.modules.download](/build.properties.dist#L60) </li>
                                <li> [development.modules.location](/build.properties.dist#L66) </li>
                                <li> [drupal.core.version](/build.properties.dist#L109) </li>
                                <li> [subsite.temporary.development.make](/build.properties.dist#L182) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="subsite-modules-install-en"></a> [subsite-modules-install-en](/includes/build/build.test.xml#L64) </h4></td>
            <td width="100%">
                <details>
                    <summary> Enable required modules after installation of the profile. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [subsite.install.modules](/build.properties.dist#L14) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="subsite-setup-files-directory"></a> [subsite-setup-files-directory](/includes/build/build.subsite.xml#L222) </h4></td>
            <td width="100%">
                <details>
                    <summary> Setup file directory </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.files.dir](/build.properties.dist#L186) </li>
                                <li> [platform.build.tmp.dir](/build.properties.dist#L187) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="subsite-site-backup"></a> [subsite-site-backup](/includes/build/build.subsite.xml#L45) </h4></td>
            <td width="100%">
                <details>
                    <summary> Backs up files and folders listed in platform.rebuild properties in order to rebuild. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.settings.dir](/build.properties.dist#L185) </li>
                                <li> [platform.rebuild.auto](/build.properties.dist#L218) </li>
                                <li> [platform.rebuild.backup.destination](/build.properties.dist#L221) </li>
                                <li> [platform.rebuild.backup.files](/build.properties.dist#L227) </li>
                                <li> [platform.rebuild.backup.folders](/build.properties.dist#L224) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="subsite-site-restore"></a> [subsite-site-restore](/includes/build/build.subsite.xml#L112) </h4></td>
            <td width="100%">
                <details>
                    <summary> Restoring sites directory if backed up before rebuild-dev. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.rebuild.auto](/build.properties.dist#L218) </li>
                                <li> [platform.rebuild.backup.destination](/build.properties.dist#L221) </li>
                                <li> [platform.rebuild.backup.files](/build.properties.dist#L227) </li>
                                <li> [platform.rebuild.backup.folders](/build.properties.dist#L224) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="test-behat-setup"></a> [test-behat-setup](/includes/build/build.test.xml#L127) </h4></td>
            <td width="100%">
                <details>
                    <summary> Set up Behat. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [behat.formatter.name](/build.properties.dist#L252) </li>
                                <li>behat.yml.content</li>
                                <li> [behat.yml.path](/build.properties.dist#L243) </li>
                                <li> [behat.yml.template](/build.properties.dist#L240) </li>
                                <li>drupal.site.dir</li>
                                <li>project.code.dir</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="test-behat-setup-link"></a> [test-behat-setup-link](/includes/build/build.package.xml#L21) </h4></td>
            <td width="100%">
                <details>
                    <summary> Symlink the Behat bin and test directory in the subsite folder. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [platform.build.subsite.dir](/build.properties.dist#L193) </li>
                                <li>project.basedir</li>
                                <li> [subsite.starterkit.vendor](/build.properties.dist#L52) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="test-phpcs-setup"></a> [test-phpcs-setup](/includes/build/build.test.xml#L78) </h4></td>
            <td width="100%">
                <details>
                    <summary> Set up PHP CodeSniffer. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [phpcs.config](/build.properties.dist#L276) </li>
                                <li> [phpcs.extensions](/build.properties.dist#L273) </li>
                                <li> [phpcs.files](/build.properties.dist#L282) </li>
                                <li> [phpcs.global.config](/build.properties.dist#L300) </li>
                                <li> [phpcs.ignore](/build.properties.dist#L285) </li>
                                <li> [phpcs.passwarnings](/build.properties.dist#L288) </li>
                                <li> [phpcs.progress](/build.properties.dist#L297) </li>
                                <li> [phpcs.report](/build.properties.dist#L291) </li>
                                <li> [phpcs.sniffcodes](/build.properties.dist#L294) </li>
                                <li> [phpcs.standards](/build.properties.dist#L279) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="test-phpcs-setup-prepush"></a> [test-phpcs-setup-prepush](/includes/build/build.test.xml#L111) </h4></td>
            <td width="100%">
                <details>
                    <summary> Setup the PHP CodeSniffer pre-push hook. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [phpcs.prepush.destination](/build.properties.dist#L308) </li>
                                <li> [phpcs.prepush.enable](/build.properties.dist#L304) </li>
                                <li> [phpcs.prepush.source](/build.properties.dist#L307) </li>
                                <li>project.basedir</li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="test-quality-assurance"></a> [test-quality-assurance](/includes/build/build.test.xml#L161) </h4></td>
            <td width="100%">
                <details>
                    <summary> Do quality assurance checks. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [subsite.starterkit.bin](/build.properties.dist#L53) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td nowrap><a name="test-run-php-codesniffer"></a> [test-run-php-codesniffer](/includes/build/build.test.xml#L170) </h4></td>
            <td width="100%">
                <details>
                    <summary> Do quality assurance checks. </summary>
                    <sub>
                        <ul>
                            <li><p>Properties</p>
                            <ul>
                                <li> [subsite.starterkit.bin](/build.properties.dist#L53) </li>
                            </ul></li>
                        </ul>
                    </sub>
                </details>
            </td>
        </tr>
    </tbody>
</table>
