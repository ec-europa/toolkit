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
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;build-clean&quot;&nbsp;description=&quot;Build&nbsp;local&nbsp;version&nbsp;of&nbsp;subsite&nbsp;with&nbsp;a&nbsp;clean&nbsp;install.&quot;&nbsp;depends=&quot;drush-create-files-dirs,&nbsp;install,&nbsp;subsite-modules-development-enable&quot;/&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-clone](/includes/build/build.clone.xml#L118) </td>
            <td>
                <details>
                    <summary>Build local version of subsite with production data.</summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;build-clone&quot;&nbsp;description=&quot;Build&nbsp;local&nbsp;version&nbsp;of&nbsp;subsite&nbsp;with&nbsp;production&nbsp;data.&quot;&nbsp;depends=&quot;subsite-database-download,&nbsp;drush-regenerate-settings,&nbsp;subsite-database-import,&nbsp;subsite-modules-development-enable&quot;/&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-code](/includes/build/build.package.xml#L74) </td>
            <td>
                <details>
                    <summary>Build local version of subsite without install.</summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;build-code&quot;&nbsp;description=&quot;Build&nbsp;local&nbsp;version&nbsp;of&nbsp;subsite&nbsp;without&nbsp;install.&quot;&nbsp;depends=&quot;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;subsite-site-backup,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;platform-delete,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;platform-make,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;platform-link-resources,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;subsite-composer-install,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;test-behat-setup-link,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;test-behat-setup,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;platform-update-htaccess,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;test-phpcs-setup,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;subsite-modules-development-download,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;subsite-site-restore&quot;/&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-keep](/includes/build/build.package.xml#L92) </td>
            <td>
                <details>
                    <summary>Build local version of subsite with backup and restore.</summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;build-keep&quot;&nbsp;description=&quot;Build&nbsp;local&nbsp;version&nbsp;of&nbsp;subsite&nbsp;with&nbsp;backup&nbsp;and&nbsp;restore.&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Execute&nbsp;build-dev&nbsp;with&nbsp;automatic&nbsp;rebuild&nbsp;enabled.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;build-dev&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;platform.rebuild.auto&quot;&nbsp;value=&quot;1&quot;&nbsp;override=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-release](/includes/build/build.package.xml#L63) </td>
            <td>
                <details>
                    <summary>Build subsite source code release package.</summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;build-release&quot;&nbsp;description=&quot;Build&nbsp;subsite&nbsp;source&nbsp;code&nbsp;release&nbsp;package.&quot;&nbsp;depends=&quot;build-dist&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${project.release.path}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;tar&nbsp;-czf&nbsp;${project.release.path}/${project.release.name}.tar.gz&nbsp;${phing.subsite.build.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-tests](/includes/build/build.package.xml#L69) </td>
            <td>
                <details>
                    <summary>Build subsite tests code release package.</summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;build-tests&quot;&nbsp;description=&quot;Build&nbsp;subsite&nbsp;tests&nbsp;code&nbsp;release&nbsp;package.&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${project.release.path}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [docker-compose-down](/includes/build/build.docker.xml#L22) </td>
            <td>
                <details>
                    <summary>Trash docker project.</summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;docker-compose-down&quot;&nbsp;description=&quot;Trash&nbsp;docker&nbsp;project.&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Removing&nbsp;containers&nbsp;and&nbsp;volumes&nbsp;for&nbsp;${docker.project.id}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;docker-compose&nbsp;-p&nbsp;${docker.project.id}&nbsp;-f&nbsp;${subsite.starterkit.root}/resources/docker/docker-compose.yml&nbsp;down&nbsp;--volumes&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;delete&nbsp;file=&quot;${project.basedir}/ssk-${docker.project.id}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [docker-compose-stop](/includes/build/build.docker.xml#L15) </td>
            <td>
                <details>
                    <summary>Stop docker project.</summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;docker-compose-stop&quot;&nbsp;description=&quot;Stop&nbsp;docker&nbsp;project.&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Stopping&nbsp;containers&nbsp;for&nbsp;${docker.project.id}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;docker-compose&nbsp;-p&nbsp;${docker.project.id}&nbsp;-f&nbsp;${subsite.starterkit.root}/resources/docker/docker-compose.yml&nbsp;stop&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;${project.basedir}/ssk-${docker.project.id}&nbsp;ps&quot;&nbsp;passthru=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [docker-compose-up](/includes/build/build.docker.xml#L5) </td>
            <td>
                <details>
                    <summary>Start docker project.</summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;docker-compose-up&quot;&nbsp;description=&quot;Start&nbsp;docker&nbsp;project.&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Starting&nbsp;containers&nbsp;for&nbsp;${docker.project.id}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${platform.build.dir}&quot;/&gt;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${share.platform.path}/databases/platform-dev-${platform.package.reference}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;DB_LOCATION_DIR=${share.platform.path}/databases/platform-dev-${platform.package.reference}&nbsp;docker-compose&nbsp;-p&nbsp;${docker.project.id}&nbsp;-f&nbsp;${subsite.starterkit.root}/resources/docker/docker-compose.yml&nbsp;up&nbsp;-d&nbsp;--no-recreate&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;rel-sym&nbsp;link=&quot;${project.basedir}/ssk-${docker.project.id}&quot;&nbsp;target=&quot;${subsite.starterkit.root}/resources/docker/dbash&quot;&nbsp;overwrite=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;${project.basedir}/ssk-${docker.project.id}&nbsp;ps&quot;&nbsp;passthru=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [install](/includes/build/build.test.xml#L5) </td>
            <td>
                <details>
                    <summary>Install the subsite.</summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;install&quot;&nbsp;description=&quot;Install&nbsp;the&nbsp;subsite.&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ensure&nbsp;the&nbsp;settings&nbsp;folder&nbsp;is&nbsp;writable&nbsp;so&nbsp;the&nbsp;installer&nbsp;can&nbsp;create
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;the&nbsp;settings.php&nbsp;file.
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;chmod&nbsp;mode=&quot;0775&quot;&nbsp;failonerror=&quot;false&quot;&nbsp;verbose=&quot;false&quot;&nbsp;quiet=&quot;true&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;fileset&nbsp;dir=&quot;${platform.build.settings.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/chmod&gt;

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;and&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${platform.package.database}&quot;&nbsp;arg2=&quot;1&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${share.platform.path}/databases/platform-dev-${platform.package.reference}/platform-dev-${platform.package.reference}.sql&quot;&nbsp;type=&quot;file&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/and&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-regenerate-settings&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;${drush.bin}&nbsp;--root=${platform.build.dir}&nbsp;status&nbsp;bootstrap&nbsp;|&nbsp;grep&nbsp;-q&nbsp;Successful&quot;&nbsp;returnProperty=&quot;drush-status-bootstrap&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;not&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${drush-status-bootstrap}&quot;&nbsp;arg2=&quot;0&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/not&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-sql-create&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-sql-import&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;database-file&quot;&nbsp;value=&quot;${share.platform.path}/databases/platform-dev-${platform.package.reference}/platform-dev-${platform.package.reference}.sql&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Install&nbsp;site&nbsp;with&nbsp;drush.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-site-install&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Backup&nbsp;vanilla&nbsp;database.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${platform.package.database}&quot;&nbsp;arg2=&quot;1&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-sql-dump&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;database-file&quot;&nbsp;value=&quot;${share.platform.path}/databases/platform-dev-${platform.package.reference}/platform-dev-${platform.package.reference}.sql&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Enable&nbsp;solr&nbsp;if&nbsp;needed.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-enable-solr&quot;/&gt;

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Subsites&nbsp;are&nbsp;not&nbsp;allowed&nbsp;to&nbsp;use&nbsp;their&nbsp;own&nbsp;installation&nbsp;profile&nbsp;for
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;historical&nbsp;reasons.&nbsp;The&nbsp;functionality&nbsp;is&nbsp;contained&nbsp;in&nbsp;one&nbsp;of&nbsp;more
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;features&nbsp;and&nbsp;modules&nbsp;which&nbsp;need&nbsp;to&nbsp;be&nbsp;enabled&nbsp;after&nbsp;installation.
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;subsite-modules-install-enable&quot;/&gt;

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Rebuild&nbsp;node&nbsp;access&nbsp;after&nbsp;Subsites&nbsp;modules&nbsp;activation&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-rebuild-node-access&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [link-docroot](/includes/build/build.package.xml#L28) </td>
            <td>
                <details>
                    <summary>Create symlink from build to docroot.</summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;link-docroot&quot;&nbsp;description=&quot;Create&nbsp;symlink&nbsp;from&nbsp;build&nbsp;to&nbsp;docroot.&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;rel-sym&nbsp;link=&quot;${server.docroot}&quot;&nbsp;target=&quot;${platform.build.dir}&quot;&nbsp;overwrite=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-behat](/includes/build/build.test.xml#L150) </td>
            <td>
                <details>
                    <summary>Refresh configuration and run behat tests.</summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;test-run-behat&quot;&nbsp;description=&quot;Refresh&nbsp;configuration&nbsp;and&nbsp;run&nbsp;behat&nbsp;tests.&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;behat&nbsp;executable=&quot;${behat.bin}&quot;&nbsp;config=&quot;${behat.yml.path}&quot;&nbsp;strict=&quot;${behat.options.strict}&quot;&nbsp;verbose=&quot;${behat.options.verbosity}&quot;&nbsp;passthru=&quot;${behat.options.passthru}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-phpcs](/includes/build/build.test.xml#L186) </td>
            <td>
                <details>
                    <summary>Refresh configuration and run phpcs review.</summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;test-run-phpcs&quot;&nbsp;description=&quot;Refresh&nbsp;configuration&nbsp;and&nbsp;run&nbsp;phpcs&nbsp;review.&quot;&nbsp;depends=&quot;test-phpcs-setup,&nbsp;test-run-php-codesniffer&quot;/&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-qa](/includes/build/build.test.xml#L179) </td>
            <td>
                <details>
                    <summary>Refresh configuration and run qa review.</summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;test-run-qa&quot;&nbsp;description=&quot;Refresh&nbsp;configuration&nbsp;and&nbsp;run&nbsp;qa&nbsp;review.&quot;&nbsp;depends=&quot;test-phpcs-setup,&nbsp;test-quality-assurance&quot;/&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-dev](/includes/build/build.deprecated.xml#L5) </td>
            <td>
                <details>
                    <summary> Target build-dev has been replaced by build-code. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;hidden=&quot;true&quot;&nbsp;name=&quot;build-dev&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;replaced&nbsp;target=&quot;build-code&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-dist](/includes/build/build.package.xml#L100) </td>
            <td>
                <details>
                    <summary> Create distribution code base. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;build-dist&quot;&nbsp;hidden=&quot;true&quot;&nbsp;depends=&quot;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dist-delete,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dist-make,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dist-copy-resources,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dist-composer-install&quot;/&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [check-for-default-settings-or-rebuild](/includes/build/build.clone.xml#L88) </td>
            <td>
                <details>
                    <summary> Target to check if we have default settings, otherwise propose user to rebuild. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;check-for-default-settings-or-rebuild&quot;&nbsp;hidden=&quot;true&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;not&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${platform.build.settings.dir}/default.settings.php&quot;&nbsp;type=&quot;file&quot;&nbsp;property=&quot;platform.build.settings.dir.default.settings&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/not&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;If&nbsp;we&nbsp;can&nbsp;not&nbsp;find&nbsp;default&nbsp;settings&nbsp;in&nbsp;the&nbsp;build&nbsp;settings&nbsp;folder,&nbsp;prompt&nbsp;to&nbsp;ask&nbsp;user&nbsp;to&nbsp;rebuild.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;No&nbsp;default&nbsp;settings&nbsp;detected&nbsp;at&nbsp;${platform.build.settings.dir}/default.settings.php.&quot;&nbsp;level=&quot;warning&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;propertyprompt&nbsp;propertyName=&quot;platform-rebuild&quot;&nbsp;defaultValue=&quot;no&quot;&nbsp;promptText=&quot;Do&nbsp;you&nbsp;wish&nbsp;to&nbsp;rebuild?&nbsp;(y/n)&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${platform-rebuild}&quot;&nbsp;arg2=&quot;y&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;build-dev&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;If&nbsp;user&nbsp;chooses&nbsp;not&nbsp;to&nbsp;rebuild&nbsp;we&nbsp;have&nbsp;no&nbsp;other&nbsp;choice&nbsp;to&nbsp;fail&nbsp;the&nbsp;build.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Can&nbsp;not&nbsp;re-generate&nbsp;settings,&nbsp;canceling&nbsp;clone&nbsp;task.&quot;&nbsp;level=&quot;error&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;fail/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;If&nbsp;we&nbsp;have&nbsp;found&nbsp;the&nbsp;default&nbsp;settings&nbsp;inform&nbsp;the&nbsp;user&nbsp;we&nbsp;will&nbsp;proceed&nbsp;with&nbsp;generation.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Default&nbsp;settings&nbsp;found&nbsp;at&nbsp;${platform.build.settings.dir}/default.settings.php.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Proceeding&nbsp;with&nbsp;re-generation&nbsp;of&nbsp;the&nbsp;settings.php.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [composer-echo-hook-phingcalls](/includes/build/build.composer.xml#L5) </td>
            <td>
                <details>
                    <summary> Echo the composer hook phingcalls. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;composer-echo-hook-phingcalls&quot;&nbsp;hidden=&quot;true&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echoproperties&nbsp;prefix=&quot;composer.hook.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [copy-folder](/includes/build/build.helpers.xml#L5) </td>
            <td>
                <details>
                    <summary> Copies a given folder to a new location. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;copy-folder&quot;&nbsp;hidden=&quot;true&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;copy&nbsp;todir=&quot;${copy.destination.path}&quot;&nbsp;haltonerror=&quot;${copy.path.haltonerror}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;fileset&nbsp;dir=&quot;${copy.source.path}&quot;&nbsp;defaultexcludes=&quot;false&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/copy&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [create-tmp-dirs](/includes/build/build.package.xml#L35) </td>
            <td>
                <details>
                    <summary> Create temp dirs. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;create-tmp-dirs&quot;&nbsp;hidden=&quot;true&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Create&nbsp;the&nbsp;global&nbsp;cache&nbsp;directory&nbsp;if&nbsp;it&nbsp;doesn't&nbsp;exist.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;not&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${platform.package.cachedir}&quot;&nbsp;type=&quot;dir&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/not&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${platform.package.cachedir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Directory&nbsp;${platform.package.cachedir}&nbsp;exists.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Create&nbsp;the&nbsp;destination&nbsp;directory&nbsp;if&nbsp;it&nbsp;doesn't&nbsp;exist.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;not&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${platform.package.destination}&quot;&nbsp;type=&quot;dir&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/not&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${platform.package.destination}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Directory&nbsp;${platform.package.destination}&nbsp;exists.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [delete-folder](/includes/build/build.helpers.xml#L12) </td>
            <td>
                <details>
                    <summary> Delete a given folder. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;delete-folder&quot;&nbsp;hidden=&quot;true&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Use&nbsp;the&nbsp;faster&nbsp;native&nbsp;command&nbsp;on&nbsp;UNIX&nbsp;systems.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;os&nbsp;family=&quot;unix&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;rm&nbsp;-rf&nbsp;&amp;quot;${folder.to.delete}&amp;quot;&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;rm&nbsp;-rf&nbsp;&amp;quot;${folder.to.delete}&amp;quot;&quot;&nbsp;dir=&quot;${project.basedir}&quot;&nbsp;passthru=&quot;true&quot;&nbsp;checkreturn=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;delete&nbsp;dir=&quot;${folder.to.delete}&quot;&nbsp;includeemptydirs=&quot;true&quot;&nbsp;failonerror=&quot;false&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-composer-install](/includes/build/build.dist.xml#L5) </td>
            <td>
                <details>
                    <summary> Install Composer dist dependencies for the subsite. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;dist-composer-install&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Run&nbsp;'composer&nbsp;install&nbsp;--no-dev'&nbsp;in&nbsp;the&nbsp;build&nbsp;destination&nbsp;folder.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;composer&nbsp;command=&quot;install&quot;&nbsp;composer=&quot;${composer.bin}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;arg&nbsp;value=&quot;--working-dir=${dist.build.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;arg&nbsp;value=&quot;--no-interaction&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;arg&nbsp;value=&quot;--no-plugins&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;arg&nbsp;value=&quot;--no-suggest&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;arg&nbsp;value=&quot;--no-dev&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;arg&nbsp;value=&quot;--ansi&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/composer&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-copy-resources](/includes/build/build.dist.xml#L18) </td>
            <td>
                <details>
                    <summary> Copy subsite resources into the build folder. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;dist-copy-resources&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Copy&nbsp;custom&nbsp;resources.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Copy&nbsp;our&nbsp;custom&nbsp;modules.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;copy-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;copy.source.path&quot;&nbsp;value=&quot;${subsite.resources.modules.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;copy.destination.path&quot;&nbsp;value=&quot;${dist.build.modules.custom.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;copy.path.haltonerror&quot;&nbsp;value=&quot;false&quot;&nbsp;override=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Copy&nbsp;our&nbsp;custom&nbsp;features.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;copy-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;copy.source.path&quot;&nbsp;value=&quot;${subsite.resources.features.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;copy.destination.path&quot;&nbsp;value=&quot;${dist.build.modules.features.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;copy.path.haltonerror&quot;&nbsp;value=&quot;false&quot;&nbsp;override=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Copy&nbsp;our&nbsp;custom&nbsp;themes.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;copy-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;copy.source.path&quot;&nbsp;value=&quot;${subsite.resources.themes.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;copy.destination.path&quot;&nbsp;value=&quot;${dist.build.themes.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;copy.path.haltonerror&quot;&nbsp;value=&quot;false&quot;&nbsp;override=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Copy&nbsp;our&nbsp;custom&nbsp;PSR-4&nbsp;code.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;copy-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;copy.source.path&quot;&nbsp;value=&quot;${subsite.resources.source.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;copy.destination.path&quot;&nbsp;value=&quot;${dist.build.source.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;copy.path.haltonerror&quot;&nbsp;value=&quot;false&quot;&nbsp;override=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Copy&nbsp;composer&nbsp;configuration.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;copy&nbsp;todir=&quot;${dist.build.dir}&quot;&nbsp;file=&quot;${subsite.resources.composer.json}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;copy&nbsp;todir=&quot;${dist.build.dir}&quot;&nbsp;file=&quot;${subsite.resources.composer.lock}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-delete](/includes/build/build.dist.xml#L50) </td>
            <td>
                <details>
                    <summary> Delete the previous distribution build. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;dist-delete&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Delete&nbsp;previous&nbsp;build.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;delete-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;folder.to.delete&quot;&nbsp;value=&quot;${dist.build.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-make](/includes/build/build.dist.xml#L58) </td>
            <td>
                <details>
                    <summary> Make the distribution version of the subsite. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;dist-make&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Delete&nbsp;temporary&nbsp;build&nbsp;folder.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;delete-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;folder.to.delete&quot;&nbsp;value=&quot;${phing.subsite.tmp.dir}/build&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Make&nbsp;the&nbsp;subsite.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Drush&nbsp;make&nbsp;builds&nbsp;the&nbsp;site&nbsp;as&nbsp;if&nbsp;it&nbsp;is&nbsp;part&nbsp;of&nbsp;a&nbsp;complete&nbsp;Drupal
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;installation.&nbsp;The&nbsp;actual&nbsp;build&nbsp;is&nbsp;in&nbsp;the&nbsp;/sites/all&nbsp;subfolder.&nbsp;Build
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;in&nbsp;a&nbsp;temporary&nbsp;folder&nbsp;and&nbsp;move&nbsp;the&nbsp;subsite&nbsp;into&nbsp;place&nbsp;when&nbsp;done.
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${subsite.make}&quot;&nbsp;type=&quot;file&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;loadfile&nbsp;property=&quot;sitemake&quot;&nbsp;file=&quot;${subsite.make}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;propertyregex&nbsp;property=&quot;not.empty&quot;&nbsp;subject=&quot;${sitemake}&quot;&nbsp;pattern=&quot;([^#;&nbsp;])(libraries\[|projects\[)&quot;&nbsp;match=&quot;$1&quot;&nbsp;casesensitive=&quot;false&quot;&nbsp;defaultvalue=&quot;empty&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;not&gt;&lt;equals&nbsp;arg1=&quot;${not.empty}&quot;&nbsp;arg2=&quot;empty&quot;/&gt;&lt;/not&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-make-no-core&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;drush.make.target.file&quot;&nbsp;value=&quot;${subsite.make}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;drush.make.root&quot;&nbsp;value=&quot;${phing.subsite.tmp.dir}/build&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Empty&nbsp;make&nbsp;file&nbsp;found.&nbsp;Skipping...&nbsp;${not.empty}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${phing.subsite.tmp.dir}/build/sites/all&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;No&nbsp;make&nbsp;file&nbsp;found.&nbsp;Skipping...&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${phing.subsite.tmp.dir}/build/sites/all&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Move&nbsp;the&nbsp;subsite&nbsp;to&nbsp;its&nbsp;destination.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;mv&nbsp;&amp;quot;${phing.subsite.tmp.dir}/build/sites/all/&amp;quot;&nbsp;&amp;quot;${dist.build.dir}&amp;quot;&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;mv&nbsp;&amp;quot;${phing.subsite.tmp.dir}/build/sites/all/&amp;quot;&nbsp;&amp;quot;${dist.build.dir}&amp;quot;&quot;&nbsp;dir=&quot;${project.basedir}&quot;&nbsp;passthru=&quot;true&quot;&nbsp;checkreturn=&quot;true&quot;/&gt;

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Clean&nbsp;up&nbsp;temporary&nbsp;build&nbsp;folder.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;delete-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;folder.to.delete&quot;&nbsp;value=&quot;${phing.subsite.tmp.dir}/build&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-create-files-dirs](/includes/build/build.drush.xml#L32) </td>
            <td>
                <details>
                    <summary> Create the directories. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;drush-create-files-dirs&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Creating&nbsp;files&nbsp;directories&nbsp;for&nbsp;${drupal.db.name}.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Execute&nbsp;setttings&nbsp;generation&nbsp;script.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;drush&nbsp;command=&quot;php-script&quot;&nbsp;root=&quot;${platform.build.dir}&quot;&nbsp;bin=&quot;${drush.bin}&quot;&nbsp;verbose=&quot;${drush.verbose}&quot;&nbsp;color=&quot;${drush.color}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;param&gt;${subsite.starterkit.root}/includes/drush/generate-directories.php&lt;/param&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/drush&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-dl-rr](/includes/build/build.drush.xml#L162) </td>
            <td>
                <details>
                    <summary> Download registry rebuild. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;drush-dl-rr&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Installing&nbsp;registry&nbsp;rebuild&nbsp;on&nbsp;user&nbsp;account.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;${drush.bin}&nbsp;pm-download&nbsp;registry_rebuild-7&nbsp;-n&nbsp;&amp;gt;/dev/null&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;${drush.bin}&nbsp;cc&nbsp;drush&nbsp;&amp;gt;/dev/null&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-enable-modules](/includes/build/build.drush.xml#L19) </td>
            <td>
                <details>
                    <summary> Enable modules. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;drush-enable-modules&quot;&nbsp;hidden=&quot;true&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;drush&nbsp;command=&quot;pm-enable&quot;&nbsp;assume=&quot;yes&quot;&nbsp;root=&quot;${platform.build.dir}&quot;&nbsp;bin=&quot;${drush.bin}&quot;&nbsp;verbose=&quot;${drush.verbose}&quot;&nbsp;color=&quot;${drush.color}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;param&gt;${drupal.modules}&lt;/param&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/drush&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-enable-solr](/includes/build/build.drush.xml#L83) </td>
            <td>
                <details>
                    <summary> Activate solr if needed. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;drush-enable-solr&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${drupal.solr.activate}&quot;&nbsp;arg2=&quot;1&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Enable&nbsp;apachesolr&nbsp;for&nbsp;${drupal.db.name}.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-enable-modules&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;drupal.modules&quot;&nbsp;value=&quot;apachesolr&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;drush&nbsp;command=&quot;solr-set-env-url&quot;&nbsp;assume=&quot;yes&quot;&nbsp;root=&quot;${platform.build.dir}&quot;&nbsp;bin=&quot;${drush.bin}&quot;&nbsp;verbose=&quot;${drush.verbose}&quot;&nbsp;color=&quot;${drush.color}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;param&gt;${drupal.solr.env.url}&lt;/param&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/drush&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-make-no-core](/includes/build/build.drush.xml#L99) </td>
            <td>
                <details>
                    <summary> Execute a makefile with the no-core option. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;drush-make-no-core&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Running&nbsp;make&nbsp;file&nbsp;${drush.make.target.file}&nbsp;into&nbsp;folder&nbsp;${drush.make.root}.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;drush&nbsp;command=&quot;make&quot;&nbsp;assume=&quot;yes&quot;&nbsp;bin=&quot;${drush.bin}&quot;&nbsp;pipe=&quot;yes&quot;&nbsp;verbose=&quot;${drush.verbose}&quot;&nbsp;root=&quot;${drush.make.root}&quot;&nbsp;color=&quot;${drush.color}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;param&gt;${drush.make.target.file}&lt;/param&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;param&gt;${drush.make.root}&lt;/param&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option&nbsp;name=&quot;concurrency&quot;&gt;10&lt;/option&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option&nbsp;name=&quot;no-patch-txt&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option&nbsp;name=&quot;no-core&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/drush&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-rebuild-node-access](/includes/build/build.drush.xml#L169) </td>
            <td>
                <details>
                    <summary> Rebuild node access. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;drush-rebuild-node-access&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;drush&nbsp;command=&quot;php-eval&quot;&nbsp;assume=&quot;yes&quot;&nbsp;root=&quot;${platform.build.dir}&quot;&nbsp;bin=&quot;${drush.bin}&quot;&nbsp;verbose=&quot;${drush.verbose}&quot;&nbsp;color=&quot;${drush.color}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;param&gt;&quot;node_access_rebuild()&quot;&lt;/param&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/drush&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-regenerate-settings](/includes/build/build.drush.xml#L111) </td>
            <td>
                <details>
                    <summary> Regenerate the settings file with database credentials and development variables. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;drush-regenerate-settings&quot;&nbsp;depends=&quot;check-for-default-settings-or-rebuild&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;copy&nbsp;file=&quot;${subsite.starterkit.root}/includes/drush/generate-settings.php&quot;&nbsp;tofile=&quot;tmp/generate-settings.php&quot;&nbsp;overwrite=&quot;true&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;filterchain&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;replacetokens&nbsp;begintoken=&quot;%%&quot;&nbsp;endtoken=&quot;%%&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Replace&nbsp;tokens&nbsp;in&nbsp;settings&nbsp;generation&nbsp;script.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;drupal.db.type&quot;&nbsp;value=&quot;${drupal.db.type}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;drupal.db.name&quot;&nbsp;value=&quot;${drupal.db.name}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;drupal.db.user&quot;&nbsp;value=&quot;${drupal.db.user}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;drupal.db.password&quot;&nbsp;value=&quot;${drupal.db.password}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;drupal.db.host&quot;&nbsp;value=&quot;${drupal.db.host}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;drupal.db.port&quot;&nbsp;value=&quot;${drupal.db.port}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;error_level&quot;&nbsp;value=&quot;${development.variables.error_level}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;views_ui_show_sql_query&quot;&nbsp;value=&quot;${development.variables.views_ui_show_sql_query}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;views_ui_show_performance_statistics&quot;&nbsp;value=&quot;${development.variables.views_ui_show_performance_statistics}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;views_show_additional_queries&quot;&nbsp;value=&quot;${development.variables.views_show_additional_queries}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;stage_file_proxy_origin&quot;&nbsp;value=&quot;${development.variables.stage_file_proxy_origin}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;stage_file_proxy_origin_dir&quot;&nbsp;value=&quot;${development.variables.stage_file_proxy_origin_dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;stage_file_proxy_hotlink&quot;&nbsp;value=&quot;${development.variables.stage_file_proxy_hotlink}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;file_public_path&quot;&nbsp;value=&quot;${platform.build.files.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;file_private_path&quot;&nbsp;value=&quot;${platform.build.files.dir}/private_files&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;file_temporary_path&quot;&nbsp;value=&quot;${platform.build.tmp.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/replacetokens&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/filterchain&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/copy&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Execute&nbsp;setttings&nbsp;generation&nbsp;script.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;drush&nbsp;command=&quot;php-script&quot;&nbsp;root=&quot;${platform.build.dir}&quot;&nbsp;bin=&quot;${drush.bin}&quot;&nbsp;verbose=&quot;${drush.verbose}&quot;&nbsp;color=&quot;${drush.color}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;param&gt;tmp/generate-settings.php&lt;/param&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/drush&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-registry-rebuild](/includes/build/build.drush.xml#L142) </td>
            <td>
                <details>
                    <summary> Rebuild registry. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;drush-registry-rebuild&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;trycatch&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;try&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Check&nbsp;if&nbsp;registry&nbsp;rebuild&nbsp;is&nbsp;available.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;${drush.bin}&nbsp;rr&nbsp;--help&quot;&nbsp;checkreturn=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/try&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;catch&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Download&nbsp;if&nbsp;not&nbsp;available.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-dl-rr&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/catch&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;finally&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Rebuild&nbsp;Registry.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;drush&nbsp;command=&quot;registry-rebuild&quot;&nbsp;assume=&quot;yes&quot;&nbsp;root=&quot;${platform.build.dir}&quot;&nbsp;bin=&quot;${drush.bin}&quot;&nbsp;verbose=&quot;${drush.verbose}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;param&gt;--fire-bazooka&lt;/param&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/drush&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/finally&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/trycatch&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-site-install](/includes/build/build.drush.xml#L5) </td>
            <td>
                <details>
                    <summary> Install the site. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;drush-site-install&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Installing&nbsp;site&nbsp;${subsite.name}.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;drush&nbsp;command=&quot;site-install&quot;&nbsp;assume=&quot;yes&quot;&nbsp;root=&quot;${platform.build.dir}&quot;&nbsp;bin=&quot;${drush.bin}&quot;&nbsp;verbose=&quot;${drush.verbose}&quot;&nbsp;color=&quot;${drush.color}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option&nbsp;name=&quot;db-url&quot;&nbsp;value=&quot;${drupal.db.url}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option&nbsp;name=&quot;site-name&quot;&nbsp;value=&quot;'${subsite.name}'&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option&nbsp;name=&quot;account-name&quot;&nbsp;value=&quot;${drupal.admin.username}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option&nbsp;name=&quot;account-pass&quot;&nbsp;value=&quot;${drupal.admin.password}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option&nbsp;name=&quot;account-mail&quot;&nbsp;value=&quot;${drupal.admin.email}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;param&gt;${platform.profile.name}&lt;/param&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;param&gt;install_configure_form.update_status_module='array(FALSE,FALSE)'&lt;/param&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/drush&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-create](/includes/build/build.drush.xml#L41) </td>
            <td>
                <details>
                    <summary> Create the database. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;drush-sql-create&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Creating&nbsp;database&nbsp;${drupal.db.name}.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;drush&nbsp;command=&quot;sql-create&quot;&nbsp;assume=&quot;yes&quot;&nbsp;root=&quot;${platform.build.dir}&quot;&nbsp;bin=&quot;${drush.bin}&quot;&nbsp;verbose=&quot;${drush.verbose}&quot;&nbsp;color=&quot;${drush.color}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option&nbsp;name=&quot;db-url&quot;&nbsp;value=&quot;${drupal.db.url}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/drush&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-drop](/includes/build/build.drush.xml#L65) </td>
            <td>
                <details>
                    <summary> Drop the database. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;drush-sql-drop&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Dropping&nbsp;database&nbsp;${drupal.db.name}.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;drush&nbsp;command=&quot;sql-drop&quot;&nbsp;assume=&quot;yes&quot;&nbsp;root=&quot;${platform.build.dir}&quot;&nbsp;bin=&quot;${drush.bin}&quot;&nbsp;verbose=&quot;${drush.verbose}&quot;&nbsp;color=&quot;${drush.color}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option&nbsp;name=&quot;db-url&quot;&nbsp;value=&quot;${drupal.db.url}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/drush&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-dump](/includes/build/build.drush.xml#L73) </td>
            <td>
                <details>
                    <summary> Backup the database. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;drush-sql-dump&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Backing&nbsp;up&nbsp;database&nbsp;${drupal.db.name}&nbsp;to&nbsp;${database-file}.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;dirname&nbsp;property=&quot;database-cachedir&quot;&nbsp;file=&quot;${database-file}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${database-cachedir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;drush&nbsp;command=&quot;sql-dump&quot;&nbsp;assume=&quot;yes&quot;&nbsp;root=&quot;${platform.build.dir}&quot;&nbsp;bin=&quot;${drush.bin}&quot;&nbsp;verbose=&quot;${drush.verbose}&quot;&nbsp;color=&quot;${drush.color}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option&nbsp;name=&quot;result-file&quot;&nbsp;value=&quot;${database-file}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/drush&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-import](/includes/build/build.drush.xml#L49) </td>
            <td>
                <details>
                    <summary> Import a database. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;drush-sql-import&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Importing&nbsp;database.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;drush&nbsp;command=&quot;sql-cli&quot;&nbsp;root=&quot;${platform.build.dir}&quot;&nbsp;bin=&quot;${drush.bin}&quot;&nbsp;verbose=&quot;${drush.verbose}&quot;&nbsp;color=&quot;${drush.color}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;param&gt;&amp;lt;&nbsp;${database-file}&lt;/param&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/drush&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-registry-rebuild&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-create-files-dirs&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Update&nbsp;database.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;drush&nbsp;command=&quot;updatedb&quot;&nbsp;assume=&quot;yes&quot;&nbsp;root=&quot;${platform.build.dir}&quot;&nbsp;bin=&quot;${drush.bin}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Clear&nbsp;Caches.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;drush&nbsp;command=&quot;cc&quot;&nbsp;assume=&quot;yes&quot;&nbsp;root=&quot;${platform.build.dir}&quot;&nbsp;bin=&quot;${drush.bin}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;param&gt;all&lt;/param&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/drush&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-composer-install](/includes/build/build.platform.xml#L5) </td>
            <td>
                <details>
                    <summary> Install Composer dependencies for the build system. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;platform-composer-install&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Run&nbsp;'composer&nbsp;install'&nbsp;in&nbsp;platform&nbsp;root.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;composer&nbsp;command=&quot;install&quot;&nbsp;composer=&quot;${composer.bin}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;arg&nbsp;value=&quot;--working-dir=${project.basedir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;arg&nbsp;value=&quot;--no-interaction&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;arg&nbsp;value=&quot;--no-suggest&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;arg&nbsp;value=&quot;--ansi&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/composer&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-delete](/includes/build/build.platform.xml#L16) </td>
            <td>
                <details>
                    <summary> Delete the previous development build. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;platform-delete&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Delete&nbsp;previous&nbsp;build.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;unprotect-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;folder.to.unprotect&quot;&nbsp;value=&quot;${platform.build.settings.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Unprotecting&nbsp;folder.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;delete-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;folder.to.delete&quot;&nbsp;value=&quot;${platform.build.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Deleting&nbsp;folder.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-download](/includes/build/build.platform.xml#L29) </td>
            <td>
                <details>
                    <summary> Download the platform. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;platform-download&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz&quot;&nbsp;type=&quot;file&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Package&nbsp;platform-dev-${platform.package.reference}.tar.gz&nbsp;already&nbsp;downloaded.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Create&nbsp;the&nbsp;destination&nbsp;directory&nbsp;if&nbsp;it&nbsp;doesn't&nbsp;exist.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${platform.package.cachedir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Starting&nbsp;platform&nbsp;download.&nbsp;Depending&nbsp;on&nbsp;your&nbsp;connection&nbsp;this&nbsp;can&nbsp;take&nbsp;between&nbsp;5-15&nbsp;minutes.&nbsp;Go&nbsp;get&nbsp;some&nbsp;coffee.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;http&nbsp;url=&quot;https://github.com/ec-europa/platform-dev/releases/download/${platform.package.reference}/platform-dev-${platform.package.reference}.tar.gz&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;curl&nbsp;-L&nbsp;-o&nbsp;${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz&nbsp;https://github.com/ec-europa/platform-dev/releases/download/${platform.package.reference}/platform-dev-${platform.package.reference}.tar.gz&quot;&nbsp;passthru=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Downloaded&nbsp;platform&nbsp;package&nbsp;reference&nbsp;${platform.package.reference}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;fail&nbsp;msg=&quot;Failed&nbsp;downloading&nbsp;platform&nbsp;package&nbsp;reference&nbsp;${platform.package.reference}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-link-resources](/includes/build/build.platform.xml#L54) </td>
            <td>
                <details>
                    <summary> Symlink the source folders for easy development. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;platform-link-resources&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;rel-sym&nbsp;link=&quot;${platform.build.subsite.modules.custom.dir}&quot;&nbsp;target=&quot;${subsite.resources.modules.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;rel-sym&nbsp;link=&quot;${platform.build.subsite.modules.features.dir}&quot;&nbsp;target=&quot;${subsite.resources.features.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;delete&nbsp;dir=&quot;${platform.build.subsite.themes.dir}&quot;&nbsp;includeemptydirs=&quot;true&quot;&nbsp;failonerror=&quot;false&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;rel-sym&nbsp;link=&quot;${platform.build.subsite.themes.dir}&quot;&nbsp;target=&quot;${subsite.resources.themes.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;rel-sym&nbsp;link=&quot;${platform.build.subsite.source.dir}&quot;&nbsp;target=&quot;${subsite.resources.source.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;rel-sym&nbsp;link=&quot;${platform.build.subsite.composer.json}&quot;&nbsp;target=&quot;${subsite.resources.composer.json}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;rel-sym&nbsp;link=&quot;${platform.build.subsite.composer.lock}&quot;&nbsp;target=&quot;${subsite.resources.composer.lock}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-make](/includes/build/build.platform.xml#L65) </td>
            <td>
                <details>
                    <summary> Make the development version of the subsite. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;platform-make&quot;&nbsp;depends=&quot;platform-unpack&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${subsite.make}&quot;&nbsp;type=&quot;file&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Make&nbsp;the&nbsp;subsite.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-make-no-core&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;drush.make.target.file&quot;&nbsp;value=&quot;${subsite.make}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;drush.make.root&quot;&nbsp;value=&quot;${platform.build.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;No&nbsp;make&nbsp;file&nbsp;found.&nbsp;Skipping...&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-unpack](/includes/build/build.platform.xml#L82) </td>
            <td>
                <details>
                    <summary> Unpack the platform. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;platform-unpack&quot;&nbsp;depends=&quot;platform-download&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Use&nbsp;the&nbsp;faster&nbsp;native&nbsp;commands&nbsp;on&nbsp;UNIX&nbsp;systems.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;os&nbsp;family=&quot;unix&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;mkdir&nbsp;&amp;quot;${platform.build.dir}&amp;quot;&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;mkdir&nbsp;&amp;quot;${platform.build.dir}&amp;quot;&quot;&nbsp;dir=&quot;${project.basedir}&quot;&nbsp;passthru=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;tar&nbsp;xzf&nbsp;&amp;quot;${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz&amp;quot;&nbsp;-C&nbsp;&amp;quot;${platform.build.dir}&amp;quot;&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;tar&nbsp;xzf&nbsp;&amp;quot;${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz&amp;quot;&nbsp;-C&nbsp;&amp;quot;${platform.build.dir}&amp;quot;&quot;&nbsp;dir=&quot;${project.basedir}&quot;&nbsp;passthru=&quot;true&quot;&nbsp;checkreturn=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;untar&nbsp;file=&quot;${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz&quot;&nbsp;todir=&quot;${platform.build.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-update-htaccess](/includes/build/build.platform.xml#L108) </td>
            <td>
                <details>
                    <summary> Update .htaccess. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;platform-update-htaccess&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;istrue&nbsp;value=&quot;${drupal.htaccess.append.text}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Appended&nbsp;text&nbsp;to&nbsp;htaccess.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;append&nbsp;destfile=&quot;${drupal.htaccess.path}&quot;&nbsp;text=&quot;${drupal.htaccess.append.text}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Appended&nbsp;no&nbsp;text&nbsp;to&nbsp;htaccess.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [prompt-for-credentials-and-retry](/includes/build/build.clone.xml#L81) </td>
            <td>
                <details>
                    <summary> Simple prompt for user credentials and recurse into subsite-database-wget. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;prompt-for-credentials-and-retry&quot;&nbsp;hidden=&quot;true&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;input&nbsp;propertyName=&quot;project.database.url.htaccess.username&quot;&nbsp;message=&quot;Please&nbsp;enter&nbsp;your&nbsp;username.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;input&nbsp;hidden=&quot;true&quot;&nbsp;propertyName=&quot;project.database.url.htaccess.password&quot;&nbsp;message=&quot;Please&nbsp;enter&nbsp;your&nbsp;password.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;subsite-database-wget&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-build-documentation-index](/includes/build/build.starterkit.xml#L60) </td>
            <td>
                <details>
                    <summary> Build documentation index. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;starterkit-build-documentation-index&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;build-documentation-index/&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-copy-templates](/includes/build/build.starterkit.xml#L11) </td>
            <td>
                <details>
                    <summary> Ensure needed files are present. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;starterkit-copy-templates&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Ensuring&nbsp;the&nbsp;presence&nbsp;of&nbsp;build.xml&nbsp;and&nbsp;Jenkinsfile.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;copy&nbsp;todir=&quot;${project.basedir}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;fileset&nbsp;dir=&quot;${subsite.starterkit.templates}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/copy&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-link-binary](/includes/build/build.starterkit.xml#L5) </td>
            <td>
                <details>
                    <summary> Provide handy access with root symlink to starterkit binary. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;starterkit-link-binary&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Provide&nbsp;project&nbsp;with&nbsp;starterkit&nbsp;binary&nbsp;at&nbsp;root&nbsp;level.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;rel-sym&nbsp;link=&quot;${project.basedir}/ssk&quot;&nbsp;target=&quot;${subsite.starterkit.bin}&quot;&nbsp;overwrite=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-upgrade](/includes/build/build.starterkit.xml#L19) </td>
            <td>
                <details>
                    <summary> Upgrade subsite-starterkit 2.x to 3.x. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;starterkit-upgrade&quot;&gt;

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Delete&nbsp;starterkit&nbsp;folders.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;delete-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;folder.to.delete&quot;&nbsp;value=&quot;${project.basedir}/bin&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;delete-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;folder.to.delete&quot;&nbsp;value=&quot;${project.basedir}/docs&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;delete-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;folder.to.delete&quot;&nbsp;value=&quot;${project.basedir}/src&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;delete-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;folder.to.delete&quot;&nbsp;value=&quot;${subsite.resources.dir}/cloudformation&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;delete-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;folder.to.delete&quot;&nbsp;value=&quot;${subsite.resources.dir}/codedeploy&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;delete-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;folder.to.delete&quot;&nbsp;value=&quot;${subsite.resources.dir}/composer&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Delete&nbsp;starterkit&nbsp;files.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;delete&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;fileset&nbsp;dir=&quot;${project.basedir}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;include&nbsp;name=&quot;CHANGELOG.md&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;include&nbsp;name=&quot;LICENSE.md&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;include&nbsp;name=&quot;README.md&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;include&nbsp;name=&quot;appspec.yml&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;include&nbsp;name=&quot;build.clone.xml&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;include&nbsp;name=&quot;build.package.xml&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;include&nbsp;name=&quot;build.properties.dist&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;include&nbsp;name=&quot;build.test.xml&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;include&nbsp;name=&quot;composer.lock&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;include&nbsp;name=&quot;phpcs-ruleset.xml&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/fileset&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/delete&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Move&nbsp;subsite&nbsp;files&nbsp;to&nbsp;new&nbsp;location.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;move&nbsp;file=&quot;${subsite.resources.dir}/phpcs-custom.xml&quot;&nbsp;tofile=&quot;phpcs-ruleset.xml&quot;&nbsp;overwrite=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-composer-install](/includes/build/build.subsite.xml#L5) </td>
            <td>
                <details>
                    <summary> Install Composer dev dependencies for the subsite. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;subsite-composer-install&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Run&nbsp;'composer&nbsp;install'&nbsp;in&nbsp;the&nbsp;subsite&nbsp;folder&nbsp;for&nbsp;development&nbsp;purposes.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;composer&nbsp;command=&quot;install&quot;&nbsp;composer=&quot;${composer.bin}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;arg&nbsp;value=&quot;--working-dir=${platform.build.subsite.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;arg&nbsp;value=&quot;--no-interaction&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;&lt;arg&nbsp;value=&quot;no-plugins&quot;&nbsp;/&gt;&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;arg&nbsp;value=&quot;--no-suggest&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;arg&nbsp;value=&quot;--ansi&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/composer&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-database-download](/includes/build/build.clone.xml#L17) </td>
            <td>
                <details>
                    <summary> Download the production database. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;subsite-database-download&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Download&nbsp;the&nbsp;production&nbsp;database.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--Strips&nbsp;gz&nbsp;suffix.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;php&nbsp;expression=&quot;substr('${project.database.filename}',&nbsp;0,&nbsp;-3)&quot;&nbsp;returnProperty=&quot;gunzipped.filename&quot;&nbsp;level=&quot;debug&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;not&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Check&nbsp;if&nbsp;we&nbsp;have&nbsp;a&nbsp;previously&nbsp;downloaded&nbsp;dump&nbsp;available.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;tmp/${gunzipped.filename}&quot;&nbsp;type=&quot;file&quot;&nbsp;property=&quot;gunzipped.project.db&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/not&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;If&nbsp;not&nbsp;available,&nbsp;download&nbsp;and&nbsp;unzip&nbsp;the&nbsp;file.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;subsite-database-wget&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;gunzip&nbsp;tmp/${project.database.filename}&quot;&nbsp;checkreturn=&quot;true&quot;&nbsp;passthru=&quot;false&quot;&nbsp;logoutput=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Inform&nbsp;user&nbsp;if&nbsp;file&nbsp;was&nbsp;already&nbsp;downloaded.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;File&nbsp;${gunzipped.filename}&nbsp;already&nbsp;downloaded.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Proceeding&nbsp;to&nbsp;import.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-database-import](/includes/build/build.clone.xml#L5) </td>
            <td>
                <details>
                    <summary> Import production database. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;subsite-database-import&quot;&nbsp;depends=&quot;subsite-database-download&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Import&nbsp;production&nbsp;database.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Drop&nbsp;database,&nbsp;create&nbsp;if&nbsp;necessary&nbsp;and&nbsp;import&nbsp;the&nbsp;dump.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-sql-drop&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-sql-create&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-sql-import&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;database-file&quot;&nbsp;value=&quot;tmp/${gunzipped.filename}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-registry-rebuild&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-database-wget](/includes/build/build.clone.xml#L40) </td>
            <td>
                <details>
                    <summary> Target to actually fetch the database dump. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;subsite-database-wget&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--Generate&nbsp;.htaccess&nbsp;credential&nbsp;property&nbsp;if&nbsp;needed,&nbsp;empty&nbsp;if&nbsp;not.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;or&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${project.database.url.htaccess.username}&quot;&nbsp;arg2=&quot;&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${project.database.url.htaccess.password}&quot;&nbsp;arg2=&quot;&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/or&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;If&nbsp;username&nbsp;or&nbsp;password&nbsp;is&nbsp;not&nbsp;provided,&nbsp;empty&nbsp;the&nbsp;credential&nbsp;string.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;project.database.url.credentials&quot;&nbsp;value=&quot;&quot;&nbsp;override=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;If&nbsp;username&nbsp;or&nbsp;password&nbsp;is&nbsp;provided,&nbsp;build&nbsp;the&nbsp;credential&nbsp;string.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;project.database.url.credentials&quot;&nbsp;value=&quot;${project.database.url.htaccess.username}:${project.database.url.htaccess.password}@&quot;&nbsp;override=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Attempt&nbsp;to&nbsp;download&nbsp;the&nbsp;database&nbsp;dump.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;wget&nbsp;${project.database.url.scheme}://${project.database.url.credentials}${project.database.url}${project.database.filename}&quot;&nbsp;dir=&quot;tmp&quot;&nbsp;checkreturn=&quot;false&quot;&nbsp;passthru=&quot;false&quot;&nbsp;outputProperty=&quot;project.database.download&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Upon&nbsp;success&nbsp;inform&nbsp;the&nbsp;user.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;contains&nbsp;string=&quot;${project.database.download}&quot;&nbsp;substring=&quot;200&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Database&nbsp;successfully&nbsp;downloaded.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;When&nbsp;denied&nbsp;access,&nbsp;prompt&nbsp;the&nbsp;user&nbsp;for&nbsp;credentials&nbsp;and&nbsp;retry&nbsp;the&nbsp;download.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;elseif&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;contains&nbsp;string=&quot;${project.database.download}&quot;&nbsp;substring=&quot;401&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;prompt-for-credentials-and-retry&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/elseif&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Otherwise&nbsp;we&nbsp;fail&nbsp;the&nbsp;build&nbsp;and&nbsp;display&nbsp;the&nbsp;download&nbsp;message.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Failed&nbsp;to&nbsp;download&nbsp;the&nbsp;database&nbsp;dump.&nbsp;Result&nbsp;of&nbsp;wget:&quot;&nbsp;level=&quot;error&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;${project.database.download}&quot;&nbsp;level=&quot;error&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;fail/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-development-download](/includes/build/build.subsite.xml#L36) </td>
            <td>
                <details>
                    <summary> Download development modules. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;subsite-modules-development-download&quot;&nbsp;depends=&quot;subsite-modules-development-makefile&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Download&nbsp;development&nbsp;modules.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-make-no-core&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;drush.make.target.file&quot;&nbsp;value=&quot;${subsite.temporary.development.make}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;drush.make.root&quot;&nbsp;value=&quot;${platform.build.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-development-enable](/includes/build/build.test.xml#L71) </td>
            <td>
                <details>
                    <summary> Enable development modules. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;subsite-modules-development-enable&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-enable-modules&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;drupal.modules&quot;&nbsp;value=&quot;${development.modules.enable}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-development-makefile](/includes/build/build.subsite.xml#L18) </td>
            <td>
                <details>
                    <summary> Generate the makefile used to download development modules. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;subsite-modules-development-makefile&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Generate&nbsp;the&nbsp;makefile&nbsp;for&nbsp;development&nbsp;modules.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${subsite.temporary.development.make}&quot;&nbsp;type=&quot;file&quot;&nbsp;property=&quot;development.makefile.available&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Deleting&nbsp;existing&nbsp;makefile.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;delete&nbsp;file=&quot;${subsite.temporary.development.make}&quot;&nbsp;failonerror=&quot;false&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;drushmakefile&nbsp;makeFile=&quot;${subsite.temporary.development.make}&quot;&nbsp;coreVersion=&quot;${drupal.core.version}&quot;&nbsp;projects=&quot;${development.modules.download}&quot;&nbsp;defaultProjectDir=&quot;${development.modules.location}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-install-enable](/includes/build/build.test.xml#L64) </td>
            <td>
                <details>
                    <summary> Enable required modules after installation of the profile. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;subsite-modules-install-enable&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;drush-enable-modules&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;drupal.modules&quot;&nbsp;value=&quot;${subsite.install.modules}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-setup-files-directory](/includes/build/build.subsite.xml#L222) </td>
            <td>
                <details>
                    <summary> Setup file directory </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;subsite-setup-files-directory&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;istrue&nbsp;value=&quot;${platform.build.files.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${platform.build.files.dir}/private_files&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${platform.build.tmp.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Support&nbsp;CSS&nbsp;and&nbsp;JS&nbsp;injector.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${platform.build.files.dir}/css_injector&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${platform.build.files.dir}/js_injector&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-backup](/includes/build/build.subsite.xml#L45) </td>
            <td>
                <details>
                    <summary> Backs up files and folders listed in platform.rebuild properties in order to rebuild. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;subsite-site-backup&quot;&gt;

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Check&nbsp;if&nbsp;site&nbsp;exists.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${platform.build.settings.dir}/settings.php&quot;&nbsp;type=&quot;file&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;site-detected&quot;&nbsp;value=&quot;1&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;No&nbsp;site&nbsp;installation&nbsp;detected.&nbsp;Skipping&nbsp;backup.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;and&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${platform.rebuild.auto}&quot;&nbsp;arg2=&quot;0&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${site-detected}&quot;&nbsp;arg2=&quot;1&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/and&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Installed&nbsp;site&nbsp;detected.&quot;&nbsp;level=&quot;warning&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;propertyprompt&nbsp;propertyName=&quot;subsite-site-backup-activated&quot;&nbsp;promptText=&quot;Do&nbsp;you&nbsp;wish&nbsp;to&nbsp;backup&nbsp;site&nbsp;for&nbsp;this&nbsp;build?&nbsp;(y/n)&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${subsite-site-backup-activated}&quot;&nbsp;arg2=&quot;y&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;platform.rebuild.auto&quot;&nbsp;value=&quot;1&quot;&nbsp;override=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;and&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${platform.rebuild.auto}&quot;&nbsp;arg2=&quot;1&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${site-detected}&quot;&nbsp;arg2=&quot;1&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/and&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Delete&nbsp;any&nbsp;remains&nbsp;of&nbsp;previous&nbsp;backup&nbsp;attempts.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${platform.rebuild.backup.destination}&quot;&nbsp;type=&quot;dir&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;delete&nbsp;dir=&quot;${platform.rebuild.backup.destination}&quot;&nbsp;includeemptydirs=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Create&nbsp;backup&nbsp;directory.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${platform.rebuild.backup.destination}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Make&nbsp;the&nbsp;settings&nbsp;directory&nbsp;writable&nbsp;because&nbsp;we&nbsp;can&nbsp;not&nbsp;delete&nbsp;it&nbsp;otherwise&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;unprotect-folder&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;folder.to.unprotect&quot;&nbsp;value=&quot;${platform.build.settings.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/phingcall&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Back&nbsp;up&nbsp;folders&nbsp;list.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;foreach&nbsp;list=&quot;${platform.rebuild.backup.folders}&quot;&nbsp;param=&quot;site-item&quot;&nbsp;target=&quot;subsite-site-backup-item&quot;&nbsp;delimiter=&quot;;&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;site-item-type&quot;&nbsp;value=&quot;dir&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/foreach&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Back&nbsp;up&nbsp;files&nbsp;list.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;foreach&nbsp;list=&quot;${platform.rebuild.backup.files}&quot;&nbsp;param=&quot;site-item&quot;&nbsp;target=&quot;subsite-site-backup-item&quot;&nbsp;delimiter=&quot;;&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;site-item-type&quot;&nbsp;value=&quot;file&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/foreach&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${subsite-site-backup-activated}&quot;&nbsp;arg2=&quot;y&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;platform.rebuild.auto&quot;&nbsp;value=&quot;0&quot;&nbsp;override=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-backup-item](/includes/build/build.subsite.xml#L162) </td>
            <td>
                <details>
                    <summary> Backs up a site item from the platform that will be removed in order to rebuild. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;subsite-site-backup-item&quot;&nbsp;hidden=&quot;true&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;php&nbsp;expression=&quot;dirname(&amp;quot;${site-item}&amp;quot;)&quot;&nbsp;returnProperty=&quot;site-item-dir&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;site-item-backup-dir&quot;&nbsp;value=&quot;${site-item-dir}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;filterchain&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;replaceregexp&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;regexp&nbsp;pattern=&quot;${platform.build.dir}&quot;&nbsp;replace=&quot;${platform.rebuild.backup.destination}&quot;&nbsp;ignoreCase=&quot;false&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/replaceregexp&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/filterchain&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/property&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${site-item}&quot;&nbsp;type=&quot;${site-item-type}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;not&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${site-item-backup-dir}&quot;&nbsp;type=&quot;dir&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/not&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${site-item-backup-dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;move&nbsp;file=&quot;${site-item}&quot;&nbsp;todir=&quot;${site-item-backup-dir}&quot;&nbsp;includeemptydirs=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;php&nbsp;expression=&quot;ucwords(&amp;quot;${site-item-type}&amp;quot;)&quot;&nbsp;returnProperty=&quot;site-item-type-capitalized&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Skipping&nbsp;${site-item}.&nbsp;${site-item-type-capitalized}&nbsp;not&nbsp;found.&quot;&nbsp;level=&quot;warning&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-restore](/includes/build/build.subsite.xml#L112) </td>
            <td>
                <details>
                    <summary> Restoring sites directory if backed up before rebuild-dev. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;subsite-site-restore&quot;&gt;

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Check&nbsp;if&nbsp;backup&nbsp;exists.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${platform.rebuild.backup.destination}&quot;&nbsp;type=&quot;dir&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;backup-detected&quot;&nbsp;value=&quot;1&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;No&nbsp;site&nbsp;backup&nbsp;detected.&nbsp;Skipping&nbsp;restore.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;and&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${platform.rebuild.auto}&quot;&nbsp;arg2=&quot;0&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${backup-detected}&quot;&nbsp;arg2=&quot;1&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/and&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Site&nbsp;backup&nbsp;detected.&quot;&nbsp;level=&quot;warning&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;propertyprompt&nbsp;propertyName=&quot;subsite-site-restore-activated&quot;&nbsp;promptText=&quot;Do&nbsp;you&nbsp;wish&nbsp;to&nbsp;restore&nbsp;site&nbsp;for&nbsp;this&nbsp;build?&nbsp;(y/n)&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${subsite-site-restore-activated}&quot;&nbsp;arg2=&quot;y&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;platform.rebuild.auto&quot;&nbsp;value=&quot;1&quot;&nbsp;override=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;and&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${platform.rebuild.auto}&quot;&nbsp;arg2=&quot;1&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${backup-detected}&quot;&nbsp;arg2=&quot;1&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/and&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Restoring&nbsp;site&nbsp;files&nbsp;and&nbsp;folders&nbsp;from&nbsp;${platform.rebuild.backup.destination}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Restore&nbsp;folders&nbsp;list.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;foreach&nbsp;list=&quot;${platform.rebuild.backup.folders}&quot;&nbsp;param=&quot;site-item&quot;&nbsp;target=&quot;subsite-site-restore-item&quot;&nbsp;delimiter=&quot;;&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;site-item-type&quot;&nbsp;value=&quot;dir&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/foreach&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Restore&nbsp;files&nbsp;list.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;foreach&nbsp;list=&quot;${platform.rebuild.backup.files}&quot;&nbsp;param=&quot;site-item&quot;&nbsp;target=&quot;subsite-site-restore-item&quot;&nbsp;delimiter=&quot;;&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;site-item-type&quot;&nbsp;value=&quot;file&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/foreach&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Delete&nbsp;the&nbsp;site&nbsp;backup&nbsp;directory.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;delete&nbsp;dir=&quot;${platform.rebuild.backup.destination}&quot;&nbsp;includeemptydirs=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-restore-item](/includes/build/build.subsite.xml#L192) </td>
            <td>
                <details>
                    <summary> Restores a site item from the platform.rebuild.backup.destination to the new build. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;subsite-site-restore-item&quot;&nbsp;hidden=&quot;true&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;property&nbsp;name=&quot;site-item-backup&quot;&nbsp;value=&quot;${site-item}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;filterchain&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;replaceregexp&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;regexp&nbsp;pattern=&quot;${platform.build.dir}&quot;&nbsp;replace=&quot;${platform.rebuild.backup.destination}&quot;&nbsp;ignoreCase=&quot;false&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/replaceregexp&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/filterchain&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/property&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${site-item-backup}&quot;&nbsp;type=&quot;${site-item-type}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;php&nbsp;expression=&quot;dirname(&amp;quot;${site-item}&amp;quot;)&quot;&nbsp;returnProperty=&quot;site-item-dir&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;not&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${site-item-dir}&quot;&nbsp;type=&quot;dir&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/not&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${site-item-dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;move&nbsp;file=&quot;${site-item-backup}&quot;&nbsp;todir=&quot;${site-item-dir}&quot;&nbsp;includeemptydirs=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;php&nbsp;expression=&quot;ucwords(&amp;quot;${site-item-type}&amp;quot;)&quot;&nbsp;returnProperty=&quot;site-item-type-capitalized&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Skipping&nbsp;${site-item}.&nbsp;${site-item-type-capitalized}&nbsp;not&nbsp;found.&quot;&nbsp;level=&quot;warning&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-behat-setup](/includes/build/build.test.xml#L127) </td>
            <td>
                <details>
                    <summary> Set up Behat. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;test-behat-setup&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${behat.yml.path}&quot;&nbsp;type=&quot;file&quot;&nbsp;property=&quot;behat.yml.available&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Deleting&nbsp;existing&nbsp;behat.yml&nbsp;configuration&nbsp;file&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;delete&nbsp;file=&quot;${behat.yml.path}&quot;&nbsp;failonerror=&quot;false&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Creating&nbsp;behat.yml&nbsp;configuration&nbsp;file&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;loadfile&nbsp;property=&quot;behat.yml.content&quot;&nbsp;file=&quot;${behat.yml.template}&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;filterchain&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;replacetokens&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;project.code.dir&quot;&nbsp;value=&quot;${project.code.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;drupal.site.dir&quot;&nbsp;value=&quot;${drupal.site.dir}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;behat.base_url&quot;&nbsp;value=&quot;${behat.base_url}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;token&nbsp;key=&quot;behat.formatter.name&quot;&nbsp;value=&quot;${behat.formatter.name}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/replacetokens&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/filterchain&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/loadfile&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;${behat.yml.content}&quot;&nbsp;file=&quot;${behat.yml.path}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-behat-setup-link](/includes/build/build.package.xml#L21) </td>
            <td>
                <details>
                    <summary> Symlink the Behat bin and test directory in the subsite folder. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;test-behat-setup-link&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;msg=&quot;Symlink&nbsp;the&nbsp;Behat&nbsp;bin&nbsp;and&nbsp;test&nbsp;directory&nbsp;in&nbsp;'./sites/all'.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;rel-sym&nbsp;link=&quot;${project.basedir}/ssk/behat&quot;&nbsp;target=&quot;${subsite.starterkit.vendor}/bin/behat&quot;&nbsp;overwrite=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;rel-sym&nbsp;link=&quot;${platform.build.subsite.dir}/tests&quot;&nbsp;target=&quot;${project.basedir}/tests&quot;&nbsp;overwrite=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-phpcs-setup](/includes/build/build.test.xml#L78) </td>
            <td>
                <details>
                    <summary> Set up PHP CodeSniffer. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;test-phpcs-setup&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${phpcs.config}&quot;&nbsp;type=&quot;file&quot;&nbsp;property=&quot;phpcs.config.available&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Deleting&nbsp;existing&nbsp;PHP&nbsp;Codesniffer&nbsp;default&nbsp;configuration&nbsp;file.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;delete&nbsp;file=&quot;${phpcs.config}&quot;&nbsp;failonerror=&quot;false&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${phpcs.global.config}&quot;&nbsp;type=&quot;file&quot;&nbsp;property=&quot;phpcs.global.config.available&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Deleting&nbsp;existing&nbsp;PHP&nbsp;Codesniffer&nbsp;global&nbsp;configuration&nbsp;file.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;delete&nbsp;file=&quot;${phpcs.global.config}&quot;&nbsp;failonerror=&quot;false&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phpcodesnifferconfiguration&nbsp;configFile=&quot;${phpcs.config}&quot;&nbsp;extensions=&quot;${phpcs.extensions}&quot;&nbsp;files=&quot;${phpcs.files}&quot;&nbsp;globalConfig=&quot;${phpcs.global.config}&quot;&nbsp;ignorePatterns=&quot;${phpcs.ignore}&quot;&nbsp;passWarnings=&quot;${phpcs.passwarnings}&quot;&nbsp;report=&quot;${phpcs.report}&quot;&nbsp;showProgress=&quot;${phpcs.progress}&quot;&nbsp;showSniffCodes=&quot;${phpcs.sniffcodes}&quot;&nbsp;standards=&quot;${phpcs.standards}&quot;/&gt;

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;Set&nbsp;up&nbsp;the&nbsp;git&nbsp;pre-push&nbsp;hook.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;phingcall&nbsp;target=&quot;test-phpcs-setup-prepush&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-phpcs-setup-prepush](/includes/build/build.test.xml#L111) </td>
            <td>
                <details>
                    <summary> Setup the PHP CodeSniffer pre-push hook. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;test-phpcs-setup-prepush&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;equals&nbsp;arg1=&quot;${phpcs.prepush.enable}&quot;&nbsp;arg2=&quot;1&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Enabling&nbsp;git&nbsp;pre-push&nbsp;hook.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;mkdir&nbsp;dir=&quot;${project.basedir}/resources/git/hooks/pre-push&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;rel-sym&nbsp;link=&quot;${phpcs.prepush.destination}&quot;&nbsp;target=&quot;${phpcs.prepush.source}&quot;&nbsp;overwrite=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;echo&nbsp;message=&quot;Disabling&nbsp;git&nbsp;pre-push&nbsp;hook.&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;delete&nbsp;file=&quot;${phpcs.prepush.destination}&quot;&nbsp;failonerror=&quot;false&quot;&nbsp;quiet=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/else&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-quality-assurance](/includes/build/build.test.xml#L161) </td>
            <td>
                <details>
                    <summary> Do quality assurance checks. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;test-quality-assurance&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;${subsite.starterkit.bin}/qa&nbsp;review:full&nbsp;--no-interaction&nbsp;--ansi&quot;&nbsp;passthru=&quot;true&quot;&nbsp;checkreturn=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-php-codesniffer](/includes/build/build.test.xml#L170) </td>
            <td>
                <details>
                    <summary> Do quality assurance checks. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;test-run-php-codesniffer&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;exec&nbsp;command=&quot;${subsite.starterkit.bin}/phpcs&quot;&nbsp;passthru=&quot;true&quot;&nbsp;checkreturn=&quot;true&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [unprotect-folder](/includes/build/build.helpers.xml#L32) </td>
            <td>
                <details>
                    <summary> Make the given folder writeable. </summary>
                    <pre lang="xml">
                        <code>
&lt;?xml&nbsp;version=&quot;1.0&quot;?&gt;
&lt;target&nbsp;name=&quot;unprotect-folder&quot;&nbsp;hidden=&quot;true&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!--&nbsp;This&nbsp;should&nbsp;only&nbsp;be&nbsp;used&nbsp;on&nbsp;folders&nbsp;that&nbsp;need&nbsp;to&nbsp;be&nbsp;removed.&nbsp;--&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;available&nbsp;file=&quot;${folder.to.unprotect}&quot;&nbsp;type=&quot;dir&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;chmod&nbsp;mode=&quot;0777&quot;&nbsp;failonerror=&quot;true&quot;&nbsp;verbose=&quot;false&quot;&nbsp;quiet=&quot;true&quot;&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;fileset&nbsp;dir=&quot;${folder.to.unprotect}&quot;/&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/chmod&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/then&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/if&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
    </tbody>
</table>