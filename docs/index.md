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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;build-clean&quot; description=&quot;Build local version of subsite with a clean install.&quot; depends=&quot;drush-create-files-dirs, install, subsite-modules-development-enable&quot;/&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;build-clone&quot; description=&quot;Build local version of subsite with production data.&quot; depends=&quot;subsite-database-download, drush-regenerate-settings, subsite-database-import, subsite-modules-development-enable&quot;/&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;build-code&quot; description=&quot;Build local version of subsite without install.&quot; depends=&quot;             subsite-site-backup,             platform-delete,             platform-make,             platform-link-resources,             subsite-composer-install,             test-behat-setup-link,             test-behat-setup,             platform-update-htaccess,             test-phpcs-setup,             subsite-modules-development-download,             subsite-site-restore&quot;/&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;build-keep&quot; description=&quot;Build local version of subsite with backup and restore.&quot;&gt;
        &lt;!-- Execute build-dev with automatic rebuild enabled. --&gt;
        &lt;phingcall target=&quot;build-dev&quot;&gt;
            &lt;property name=&quot;platform.rebuild.auto&quot; value=&quot;1&quot; override=&quot;true&quot;/&gt;
        &lt;/phingcall&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;build-release&quot; description=&quot;Build subsite source code release package.&quot; depends=&quot;build-dist&quot;&gt;
        &lt;mkdir dir=&quot;${project.release.path}&quot;/&gt;
        &lt;exec command=&quot;tar -czf ${project.release.path}/${project.release.name}.tar.gz ${phing.subsite.build.dir}&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;build-tests&quot; description=&quot;Build subsite tests code release package.&quot;&gt;
        &lt;mkdir dir=&quot;${project.release.path}&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;docker-compose-down&quot; description=&quot;Trash docker project.&quot;&gt;
        &lt;echo msg=&quot;Removing containers and volumes for ${docker.project.id}&quot;/&gt;
        &lt;exec command=&quot;docker-compose -p ${docker.project.id} -f ${subsite.starterkit.root}/resources/docker/docker-compose.yml down --volumes&quot;/&gt;
        &lt;delete file=&quot;${project.basedir}/ssk-${docker.project.id}&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;docker-compose-stop&quot; description=&quot;Stop docker project.&quot;&gt;
        &lt;echo msg=&quot;Stopping containers for ${docker.project.id}&quot;/&gt;
        &lt;exec command=&quot;docker-compose -p ${docker.project.id} -f ${subsite.starterkit.root}/resources/docker/docker-compose.yml stop&quot;/&gt;
        &lt;exec command=&quot;${project.basedir}/ssk-${docker.project.id} ps&quot; passthru=&quot;true&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;docker-compose-up&quot; description=&quot;Start docker project.&quot;&gt;
        &lt;echo msg=&quot;Starting containers for ${docker.project.id}&quot;/&gt;
        &lt;mkdir dir=&quot;${platform.build.dir}&quot;/&gt; 
        &lt;mkdir dir=&quot;${share.platform.path}/databases/platform-dev-${platform.package.reference}&quot;/&gt;
        &lt;exec command=&quot;DB_LOCATION_DIR=${share.platform.path}/databases/platform-dev-${platform.package.reference} docker-compose -p ${docker.project.id} -f ${subsite.starterkit.root}/resources/docker/docker-compose.yml up -d --no-recreate&quot;/&gt;
        &lt;rel-sym link=&quot;${project.basedir}/ssk-${docker.project.id}&quot; target=&quot;${subsite.starterkit.root}/resources/docker/dbash&quot; overwrite=&quot;true&quot;/&gt;
        &lt;exec command=&quot;${project.basedir}/ssk-${docker.project.id} ps&quot; passthru=&quot;true&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;install&quot; description=&quot;Install the subsite.&quot;&gt;
        &lt;!--
            Ensure the settings folder is writable so the installer can create
            the settings.php file.
         --&gt;
        &lt;chmod mode=&quot;0775&quot; failonerror=&quot;false&quot; verbose=&quot;false&quot; quiet=&quot;true&quot;&gt;
            &lt;fileset dir=&quot;${platform.build.settings.dir}&quot;/&gt;
        &lt;/chmod&gt;

        &lt;if&gt;
            &lt;and&gt;
                &lt;equals arg1=&quot;${platform.package.database}&quot; arg2=&quot;1&quot;/&gt;
                &lt;available file=&quot;${share.platform.path}/databases/platform-dev-${platform.package.reference}/platform-dev-${platform.package.reference}.sql&quot; type=&quot;file&quot;/&gt;
            &lt;/and&gt;
            &lt;then&gt;
                &lt;phingcall target=&quot;drush-regenerate-settings&quot;/&gt;
                &lt;exec command=&quot;${drush.bin} --root=${platform.build.dir} status bootstrap | grep -q Successful&quot; returnProperty=&quot;drush-status-bootstrap&quot;/&gt;
                &lt;if&gt;
                    &lt;not&gt;
                        &lt;equals arg1=&quot;${drush-status-bootstrap}&quot; arg2=&quot;0&quot;/&gt;
                    &lt;/not&gt;
                    &lt;then&gt;
                        &lt;phingcall target=&quot;drush-sql-create&quot;/&gt;
                        &lt;phingcall target=&quot;drush-sql-import&quot;&gt;
                            &lt;property name=&quot;database-file&quot; value=&quot;${share.platform.path}/databases/platform-dev-${platform.package.reference}/platform-dev-${platform.package.reference}.sql&quot;/&gt;
                        &lt;/phingcall&gt;
                    &lt;/then&gt;
                &lt;/if&gt; 
            &lt;/then&gt;
            &lt;else&gt;
                &lt;!-- Install site with drush. --&gt;
                &lt;phingcall target=&quot;drush-site-install&quot;/&gt;
                &lt;!-- Backup vanilla database. --&gt;
                &lt;if&gt;
                    &lt;equals arg1=&quot;${platform.package.database}&quot; arg2=&quot;1&quot;/&gt;
                    &lt;then&gt;
                        &lt;phingcall target=&quot;drush-sql-dump&quot;&gt;
                            &lt;property name=&quot;database-file&quot; value=&quot;${share.platform.path}/databases/platform-dev-${platform.package.reference}/platform-dev-${platform.package.reference}.sql&quot;/&gt;
                        &lt;/phingcall&gt;
                    &lt;/then&gt;
                &lt;/if&gt;
            &lt;/else&gt;
        &lt;/if&gt;

        &lt;!-- Enable solr if needed. --&gt;
        &lt;phingcall target=&quot;drush-enable-solr&quot;/&gt;

        &lt;!--
            Subsites are not allowed to use their own installation profile for
            historical reasons. The functionality is contained in one of more
            features and modules which need to be enabled after installation.
        --&gt;
        &lt;phingcall target=&quot;subsite-modules-install-enable&quot;/&gt;

        &lt;!-- Rebuild node access after Subsites modules activation --&gt;
        &lt;phingcall target=&quot;drush-rebuild-node-access&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;link-docroot&quot; description=&quot;Create symlink from build to docroot.&quot;&gt;
        &lt;rel-sym link=&quot;${server.docroot}&quot; target=&quot;${platform.build.dir}&quot; overwrite=&quot;true&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;test-run-behat&quot; description=&quot;Refresh configuration and run behat tests.&quot;&gt;
        &lt;behat executable=&quot;${behat.bin}&quot; config=&quot;${behat.yml.path}&quot; strict=&quot;${behat.options.strict}&quot; verbose=&quot;${behat.options.verbosity}&quot; passthru=&quot;${behat.options.passthru}&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;test-run-phpcs&quot; description=&quot;Refresh configuration and run phpcs review.&quot; depends=&quot;test-phpcs-setup, test-run-php-codesniffer&quot;/&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;test-run-qa&quot; description=&quot;Refresh configuration and run qa review.&quot; depends=&quot;test-phpcs-setup, test-quality-assurance&quot;/&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target hidden=&quot;true&quot; name=&quot;build-dev&quot;&gt;
        &lt;replaced target=&quot;build-code&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;build-dist&quot; hidden=&quot;true&quot; depends=&quot;             dist-delete,             dist-make,             dist-copy-resources,             dist-composer-install&quot;/&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;check-for-default-settings-or-rebuild&quot; hidden=&quot;true&quot;&gt;
        &lt;if&gt;
            &lt;not&gt;
                &lt;available file=&quot;${platform.build.settings.dir}/default.settings.php&quot; type=&quot;file&quot; property=&quot;platform.build.settings.dir.default.settings&quot;/&gt;
            &lt;/not&gt;
            &lt;then&gt;
                &lt;!-- If we can not find default settings in the build settings folder, prompt to ask user to rebuild. --&gt;
                &lt;echo msg=&quot;No default settings detected at ${platform.build.settings.dir}/default.settings.php.&quot; level=&quot;warning&quot;/&gt;
                &lt;propertyprompt propertyName=&quot;platform-rebuild&quot; defaultValue=&quot;no&quot; promptText=&quot;Do you wish to rebuild? (y/n)&quot;/&gt;
                &lt;if&gt;
                    &lt;equals arg1=&quot;${platform-rebuild}&quot; arg2=&quot;y&quot;/&gt;
                    &lt;then&gt;
                        &lt;phingcall target=&quot;build-dev&quot;/&gt;
                    &lt;/then&gt;
                    &lt;else&gt;
                        &lt;!-- If user chooses not to rebuild we have no other choice to fail the build. --&gt;
                        &lt;echo msg=&quot;Can not re-generate settings, canceling clone task.&quot; level=&quot;error&quot;/&gt;
                        &lt;fail/&gt;
                    &lt;/else&gt;
                &lt;/if&gt;
            &lt;/then&gt;
            &lt;else&gt;
                &lt;!-- If we have found the default settings inform the user we will proceed with generation. --&gt;
                &lt;echo msg=&quot;Default settings found at ${platform.build.settings.dir}/default.settings.php.&quot;/&gt;
                &lt;echo msg=&quot;Proceeding with re-generation of the settings.php.&quot;/&gt;
            &lt;/else&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;composer-echo-hook-phingcalls&quot; hidden=&quot;true&quot;&gt;
        &lt;echoproperties prefix=&quot;composer.hook.&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;copy-folder&quot; hidden=&quot;true&quot;&gt;
        &lt;copy todir=&quot;${copy.destination.path}&quot; haltonerror=&quot;${copy.path.haltonerror}&quot;&gt;
            &lt;fileset dir=&quot;${copy.source.path}&quot; defaultexcludes=&quot;false&quot;/&gt;
        &lt;/copy&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;create-tmp-dirs&quot; hidden=&quot;true&quot;&gt;
        &lt;if&gt;
            &lt;!-- Create the global cache directory if it doesn't exist. --&gt;
            &lt;not&gt;
                &lt;available file=&quot;${platform.package.cachedir}&quot; type=&quot;dir&quot;/&gt;
            &lt;/not&gt;
            &lt;then&gt;
                &lt;mkdir dir=&quot;${platform.package.cachedir}&quot;/&gt;
            &lt;/then&gt;
            &lt;else&gt;
                &lt;echo msg=&quot;Directory ${platform.package.cachedir} exists.&quot;/&gt;
            &lt;/else&gt;
        &lt;/if&gt;
        &lt;if&gt;
            &lt;!-- Create the destination directory if it doesn't exist. --&gt;
            &lt;not&gt;
                &lt;available file=&quot;${platform.package.destination}&quot; type=&quot;dir&quot;/&gt;
            &lt;/not&gt;
            &lt;then&gt;
                &lt;mkdir dir=&quot;${platform.package.destination}&quot;/&gt;
            &lt;/then&gt;
            &lt;else&gt;
                &lt;echo msg=&quot;Directory ${platform.package.destination} exists.&quot;/&gt;
            &lt;/else&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;delete-folder&quot; hidden=&quot;true&quot;&gt;
        &lt;!-- Use the faster native command on UNIX systems. --&gt;
        &lt;if&gt;
            &lt;os family=&quot;unix&quot;/&gt;
            &lt;then&gt;
                &lt;echo msg=&quot;rm -rf &amp;quot;${folder.to.delete}&amp;quot;&quot;/&gt;
                &lt;exec command=&quot;rm -rf &amp;quot;${folder.to.delete}&amp;quot;&quot; dir=&quot;${project.basedir}&quot; passthru=&quot;true&quot; checkreturn=&quot;true&quot;/&gt;
            &lt;/then&gt;
            &lt;else&gt;
                &lt;delete dir=&quot;${folder.to.delete}&quot; includeemptydirs=&quot;true&quot; failonerror=&quot;false&quot;/&gt;
            &lt;/else&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;dist-composer-install&quot;&gt;
        &lt;echo msg=&quot;Run 'composer install --no-dev' in the build destination folder.&quot;/&gt;
        &lt;composer command=&quot;install&quot; composer=&quot;${composer.bin}&quot;&gt;
            &lt;arg value=&quot;--working-dir=${dist.build.dir}&quot;/&gt;
            &lt;arg value=&quot;--no-interaction&quot;/&gt;
            &lt;arg value=&quot;--no-plugins&quot;/&gt;
            &lt;arg value=&quot;--no-suggest&quot;/&gt;
            &lt;arg value=&quot;--no-dev&quot;/&gt;
            &lt;arg value=&quot;--ansi&quot;/&gt;
        &lt;/composer&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;dist-copy-resources&quot;&gt;
        &lt;echo msg=&quot;Copy custom resources.&quot;/&gt;
        &lt;!-- Copy our custom modules. --&gt;
        &lt;phingcall target=&quot;copy-folder&quot;&gt;
            &lt;property name=&quot;copy.source.path&quot; value=&quot;${subsite.resources.modules.dir}&quot;/&gt;
            &lt;property name=&quot;copy.destination.path&quot; value=&quot;${dist.build.modules.custom.dir}&quot;/&gt;
            &lt;property name=&quot;copy.path.haltonerror&quot; value=&quot;false&quot; override=&quot;true&quot;/&gt;
        &lt;/phingcall&gt;
        &lt;!-- Copy our custom features. --&gt;
        &lt;phingcall target=&quot;copy-folder&quot;&gt;
            &lt;property name=&quot;copy.source.path&quot; value=&quot;${subsite.resources.features.dir}&quot;/&gt;
            &lt;property name=&quot;copy.destination.path&quot; value=&quot;${dist.build.modules.features.dir}&quot;/&gt;
            &lt;property name=&quot;copy.path.haltonerror&quot; value=&quot;false&quot; override=&quot;true&quot;/&gt;
        &lt;/phingcall&gt;
        &lt;!-- Copy our custom themes. --&gt;
        &lt;phingcall target=&quot;copy-folder&quot;&gt;
            &lt;property name=&quot;copy.source.path&quot; value=&quot;${subsite.resources.themes.dir}&quot;/&gt;
            &lt;property name=&quot;copy.destination.path&quot; value=&quot;${dist.build.themes.dir}&quot;/&gt;
            &lt;property name=&quot;copy.path.haltonerror&quot; value=&quot;false&quot; override=&quot;true&quot;/&gt;
        &lt;/phingcall&gt;
        &lt;!-- Copy our custom PSR-4 code. --&gt;
        &lt;phingcall target=&quot;copy-folder&quot;&gt;
            &lt;property name=&quot;copy.source.path&quot; value=&quot;${subsite.resources.source.dir}&quot;/&gt;
            &lt;property name=&quot;copy.destination.path&quot; value=&quot;${dist.build.source.dir}&quot;/&gt;
            &lt;property name=&quot;copy.path.haltonerror&quot; value=&quot;false&quot; override=&quot;true&quot;/&gt;
        &lt;/phingcall&gt;
        &lt;!-- Copy composer configuration. --&gt;
        &lt;copy todir=&quot;${dist.build.dir}&quot; file=&quot;${subsite.resources.composer.json}&quot;/&gt;
        &lt;copy todir=&quot;${dist.build.dir}&quot; file=&quot;${subsite.resources.composer.lock}&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;dist-delete&quot;&gt;
        &lt;echo msg=&quot;Delete previous build.&quot;/&gt;
        &lt;phingcall target=&quot;delete-folder&quot;&gt;
            &lt;property name=&quot;folder.to.delete&quot; value=&quot;${dist.build.dir}&quot;/&gt;
        &lt;/phingcall&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;dist-make&quot;&gt;
        &lt;echo msg=&quot;Delete temporary build folder.&quot;/&gt;
        &lt;phingcall target=&quot;delete-folder&quot;&gt;
            &lt;property name=&quot;folder.to.delete&quot; value=&quot;${phing.subsite.tmp.dir}/build&quot;/&gt;
        &lt;/phingcall&gt;

        &lt;echo msg=&quot;Make the subsite.&quot;/&gt;
        &lt;!--
            Drush make builds the site as if it is part of a complete Drupal
            installation. The actual build is in the /sites/all subfolder. Build
            in a temporary folder and move the subsite into place when done.
         --&gt;
        &lt;if&gt;
            &lt;available file=&quot;${subsite.make}&quot; type=&quot;file&quot;/&gt;
            &lt;then&gt;
                &lt;loadfile property=&quot;sitemake&quot; file=&quot;${subsite.make}&quot;/&gt;
                &lt;propertyregex property=&quot;not.empty&quot; subject=&quot;${sitemake}&quot; pattern=&quot;([^#; ])(libraries\[|projects\[)&quot; match=&quot;$1&quot; casesensitive=&quot;false&quot; defaultvalue=&quot;empty&quot;/&gt;
                &lt;if&gt;
                    &lt;not&gt;&lt;equals arg1=&quot;${not.empty}&quot; arg2=&quot;empty&quot;/&gt;&lt;/not&gt;
                    &lt;then&gt;
                        &lt;phingcall target=&quot;drush-make-no-core&quot;&gt;
                            &lt;property name=&quot;drush.make.target.file&quot; value=&quot;${subsite.make}&quot;/&gt;
                            &lt;property name=&quot;drush.make.root&quot; value=&quot;${phing.subsite.tmp.dir}/build&quot;/&gt;
                        &lt;/phingcall&gt;
                    &lt;/then&gt;
                    &lt;else&gt;
                       &lt;echo msg=&quot;Empty make file found. Skipping... ${not.empty}&quot;/&gt;
                       &lt;mkdir dir=&quot;${phing.subsite.tmp.dir}/build/sites/all&quot;/&gt;
                    &lt;/else&gt;
                &lt;/if&gt;
            &lt;/then&gt;
            &lt;else&gt;
                &lt;echo msg=&quot;No make file found. Skipping...&quot;/&gt;
                &lt;mkdir dir=&quot;${phing.subsite.tmp.dir}/build/sites/all&quot;/&gt;
            &lt;/else&gt;
        &lt;/if&gt;

        &lt;!-- Move the subsite to its destination. --&gt;
        &lt;echo msg=&quot;mv &amp;quot;${phing.subsite.tmp.dir}/build/sites/all/&amp;quot; &amp;quot;${dist.build.dir}&amp;quot;&quot;/&gt;
        &lt;exec command=&quot;mv &amp;quot;${phing.subsite.tmp.dir}/build/sites/all/&amp;quot; &amp;quot;${dist.build.dir}&amp;quot;&quot; dir=&quot;${project.basedir}&quot; passthru=&quot;true&quot; checkreturn=&quot;true&quot;/&gt;

        &lt;echo msg=&quot;Clean up temporary build folder.&quot;/&gt;
        &lt;phingcall target=&quot;delete-folder&quot;&gt;
            &lt;property name=&quot;folder.to.delete&quot; value=&quot;${phing.subsite.tmp.dir}/build&quot;/&gt;
        &lt;/phingcall&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;drush-create-files-dirs&quot;&gt;
        &lt;echo message=&quot;Creating files directories for ${drupal.db.name}.&quot;/&gt;
        &lt;!-- Execute setttings generation script. --&gt;
        &lt;drush command=&quot;php-script&quot; root=&quot;${platform.build.dir}&quot; bin=&quot;${drush.bin}&quot; verbose=&quot;${drush.verbose}&quot; color=&quot;${drush.color}&quot;&gt;
            &lt;param&gt;${subsite.starterkit.root}/includes/drush/generate-directories.php&lt;/param&gt;
        &lt;/drush&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;drush-dl-rr&quot;&gt;
        &lt;echo message=&quot;Installing registry rebuild on user account.&quot;/&gt;
        &lt;exec command=&quot;${drush.bin} pm-download registry_rebuild-7 -n &amp;gt;/dev/null&quot;/&gt;
        &lt;exec command=&quot;${drush.bin} cc drush &amp;gt;/dev/null&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;drush-enable-modules&quot; hidden=&quot;true&quot;&gt;
        &lt;drush command=&quot;pm-enable&quot; assume=&quot;yes&quot; root=&quot;${platform.build.dir}&quot; bin=&quot;${drush.bin}&quot; verbose=&quot;${drush.verbose}&quot; color=&quot;${drush.color}&quot;&gt;
            &lt;param&gt;${drupal.modules}&lt;/param&gt;
        &lt;/drush&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;drush-enable-solr&quot;&gt;
        &lt;if&gt;
            &lt;equals arg1=&quot;${drupal.solr.activate}&quot; arg2=&quot;1&quot;/&gt;
            &lt;then&gt;
                &lt;echo message=&quot;Enable apachesolr for ${drupal.db.name}.&quot;/&gt;
                &lt;phingcall target=&quot;drush-enable-modules&quot;&gt;
                    &lt;property name=&quot;drupal.modules&quot; value=&quot;apachesolr&quot;/&gt;
                &lt;/phingcall&gt;
                &lt;drush command=&quot;solr-set-env-url&quot; assume=&quot;yes&quot; root=&quot;${platform.build.dir}&quot; bin=&quot;${drush.bin}&quot; verbose=&quot;${drush.verbose}&quot; color=&quot;${drush.color}&quot;&gt;
                    &lt;param&gt;${drupal.solr.env.url}&lt;/param&gt;
                &lt;/drush&gt;
            &lt;/then&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;drush-make-no-core&quot;&gt;
        &lt;echo message=&quot;Running make file ${drush.make.target.file} into folder ${drush.make.root}.&quot;/&gt;
        &lt;drush command=&quot;make&quot; assume=&quot;yes&quot; bin=&quot;${drush.bin}&quot; pipe=&quot;yes&quot; verbose=&quot;${drush.verbose}&quot; root=&quot;${drush.make.root}&quot; color=&quot;${drush.color}&quot;&gt;
            &lt;param&gt;${drush.make.target.file}&lt;/param&gt;
            &lt;param&gt;${drush.make.root}&lt;/param&gt;
            &lt;option name=&quot;concurrency&quot;&gt;10&lt;/option&gt;
            &lt;option name=&quot;no-patch-txt&quot;/&gt;
            &lt;option name=&quot;no-core&quot;/&gt;
        &lt;/drush&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;drush-rebuild-node-access&quot;&gt;
        &lt;drush command=&quot;php-eval&quot; assume=&quot;yes&quot; root=&quot;${platform.build.dir}&quot; bin=&quot;${drush.bin}&quot; verbose=&quot;${drush.verbose}&quot; color=&quot;${drush.color}&quot;&gt;
            &lt;param&gt;&quot;node_access_rebuild()&quot;&lt;/param&gt;
        &lt;/drush&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;drush-regenerate-settings&quot; depends=&quot;check-for-default-settings-or-rebuild&quot;&gt;
        &lt;copy file=&quot;${subsite.starterkit.root}/includes/drush/generate-settings.php&quot; tofile=&quot;tmp/generate-settings.php&quot; overwrite=&quot;true&quot;&gt;
            &lt;filterchain&gt;
                &lt;replacetokens begintoken=&quot;%%&quot; endtoken=&quot;%%&quot;&gt;
                    &lt;!-- Replace tokens in settings generation script. --&gt;
                    &lt;token key=&quot;drupal.db.type&quot; value=&quot;${drupal.db.type}&quot;/&gt;
                    &lt;token key=&quot;drupal.db.name&quot; value=&quot;${drupal.db.name}&quot;/&gt;
                    &lt;token key=&quot;drupal.db.user&quot; value=&quot;${drupal.db.user}&quot;/&gt;
                    &lt;token key=&quot;drupal.db.password&quot; value=&quot;${drupal.db.password}&quot;/&gt;
                    &lt;token key=&quot;drupal.db.host&quot; value=&quot;${drupal.db.host}&quot;/&gt;
                    &lt;token key=&quot;drupal.db.port&quot; value=&quot;${drupal.db.port}&quot;/&gt;
                    &lt;token key=&quot;error_level&quot; value=&quot;${development.variables.error_level}&quot;/&gt;
                    &lt;token key=&quot;views_ui_show_sql_query&quot; value=&quot;${development.variables.views_ui_show_sql_query}&quot;/&gt;
                    &lt;token key=&quot;views_ui_show_performance_statistics&quot; value=&quot;${development.variables.views_ui_show_performance_statistics}&quot;/&gt;
                    &lt;token key=&quot;views_show_additional_queries&quot; value=&quot;${development.variables.views_show_additional_queries}&quot;/&gt;
                    &lt;token key=&quot;stage_file_proxy_origin&quot; value=&quot;${development.variables.stage_file_proxy_origin}&quot;/&gt;
                    &lt;token key=&quot;stage_file_proxy_origin_dir&quot; value=&quot;${development.variables.stage_file_proxy_origin_dir}&quot;/&gt;
                    &lt;token key=&quot;stage_file_proxy_hotlink&quot; value=&quot;${development.variables.stage_file_proxy_hotlink}&quot;/&gt;
                    &lt;token key=&quot;file_public_path&quot; value=&quot;${platform.build.files.dir}&quot;/&gt;
                    &lt;token key=&quot;file_private_path&quot; value=&quot;${platform.build.files.dir}/private_files&quot;/&gt;
                    &lt;token key=&quot;file_temporary_path&quot; value=&quot;${platform.build.tmp.dir}&quot;/&gt;
                &lt;/replacetokens&gt;
            &lt;/filterchain&gt;
        &lt;/copy&gt;
        &lt;!-- Execute setttings generation script. --&gt;
        &lt;drush command=&quot;php-script&quot; root=&quot;${platform.build.dir}&quot; bin=&quot;${drush.bin}&quot; verbose=&quot;${drush.verbose}&quot; color=&quot;${drush.color}&quot;&gt;
            &lt;param&gt;tmp/generate-settings.php&lt;/param&gt;
        &lt;/drush&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;drush-registry-rebuild&quot;&gt;
        &lt;trycatch&gt;
            &lt;try&gt;
                &lt;!-- Check if registry rebuild is available. --&gt;
                &lt;exec command=&quot;${drush.bin} rr --help&quot; checkreturn=&quot;true&quot;/&gt;
            &lt;/try&gt;
            &lt;catch&gt;
                &lt;!-- Download if not available. --&gt;
                &lt;phingcall target=&quot;drush-dl-rr&quot;/&gt;
            &lt;/catch&gt;
            &lt;finally&gt;
                 &lt;!-- Rebuild Registry. --&gt;
                 &lt;drush command=&quot;registry-rebuild&quot; assume=&quot;yes&quot; root=&quot;${platform.build.dir}&quot; bin=&quot;${drush.bin}&quot; verbose=&quot;${drush.verbose}&quot;&gt;
                     &lt;param&gt;--fire-bazooka&lt;/param&gt;
                 &lt;/drush&gt;
            &lt;/finally&gt;
        &lt;/trycatch&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;drush-site-install&quot;&gt;
        &lt;echo message=&quot;Installing site ${subsite.name}.&quot;/&gt;
        &lt;drush command=&quot;site-install&quot; assume=&quot;yes&quot; root=&quot;${platform.build.dir}&quot; bin=&quot;${drush.bin}&quot; verbose=&quot;${drush.verbose}&quot; color=&quot;${drush.color}&quot;&gt;
            &lt;option name=&quot;db-url&quot; value=&quot;${drupal.db.url}&quot;/&gt;
            &lt;option name=&quot;site-name&quot; value=&quot;'${subsite.name}'&quot;/&gt;
            &lt;option name=&quot;account-name&quot; value=&quot;${drupal.admin.username}&quot;/&gt;
            &lt;option name=&quot;account-pass&quot; value=&quot;${drupal.admin.password}&quot;/&gt;
            &lt;option name=&quot;account-mail&quot; value=&quot;${drupal.admin.email}&quot;/&gt;
            &lt;param&gt;${platform.profile.name}&lt;/param&gt;
            &lt;param&gt;install_configure_form.update_status_module='array(FALSE,FALSE)'&lt;/param&gt;
        &lt;/drush&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;drush-sql-create&quot;&gt;
        &lt;echo message=&quot;Creating database ${drupal.db.name}.&quot;/&gt;
        &lt;drush command=&quot;sql-create&quot; assume=&quot;yes&quot; root=&quot;${platform.build.dir}&quot; bin=&quot;${drush.bin}&quot; verbose=&quot;${drush.verbose}&quot; color=&quot;${drush.color}&quot;&gt;
            &lt;option name=&quot;db-url&quot; value=&quot;${drupal.db.url}&quot;/&gt;
        &lt;/drush&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;drush-sql-drop&quot;&gt;
        &lt;echo message=&quot;Dropping database ${drupal.db.name}.&quot;/&gt;
        &lt;drush command=&quot;sql-drop&quot; assume=&quot;yes&quot; root=&quot;${platform.build.dir}&quot; bin=&quot;${drush.bin}&quot; verbose=&quot;${drush.verbose}&quot; color=&quot;${drush.color}&quot;&gt;
            &lt;option name=&quot;db-url&quot; value=&quot;${drupal.db.url}&quot;/&gt;
        &lt;/drush&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;drush-sql-dump&quot;&gt;
        &lt;echo message=&quot;Backing up database ${drupal.db.name} to ${database-file}.&quot;/&gt;
        &lt;dirname property=&quot;database-cachedir&quot; file=&quot;${database-file}&quot;/&gt;
        &lt;mkdir dir=&quot;${database-cachedir}&quot;/&gt;
        &lt;drush command=&quot;sql-dump&quot; assume=&quot;yes&quot; root=&quot;${platform.build.dir}&quot; bin=&quot;${drush.bin}&quot; verbose=&quot;${drush.verbose}&quot; color=&quot;${drush.color}&quot;&gt;
            &lt;option name=&quot;result-file&quot; value=&quot;${database-file}&quot;/&gt;
        &lt;/drush&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;drush-sql-import&quot;&gt;
        &lt;echo message=&quot;Importing database.&quot;/&gt;
        &lt;drush command=&quot;sql-cli&quot; root=&quot;${platform.build.dir}&quot; bin=&quot;${drush.bin}&quot; verbose=&quot;${drush.verbose}&quot; color=&quot;${drush.color}&quot;&gt;
            &lt;param&gt;&amp;lt; ${database-file}&lt;/param&gt;
        &lt;/drush&gt;
        &lt;phingcall target=&quot;drush-registry-rebuild&quot;/&gt;
        &lt;phingcall target=&quot;drush-create-files-dirs&quot;/&gt;
        &lt;!-- Update database. --&gt;
        &lt;drush command=&quot;updatedb&quot; assume=&quot;yes&quot; root=&quot;${platform.build.dir}&quot; bin=&quot;${drush.bin}&quot;/&gt;
        &lt;!-- Clear Caches. --&gt;
        &lt;drush command=&quot;cc&quot; assume=&quot;yes&quot; root=&quot;${platform.build.dir}&quot; bin=&quot;${drush.bin}&quot;&gt;
            &lt;param&gt;all&lt;/param&gt;
        &lt;/drush&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;platform-composer-install&quot;&gt;
        &lt;echo msg=&quot;Run 'composer install' in platform root.&quot;/&gt;
        &lt;composer command=&quot;install&quot; composer=&quot;${composer.bin}&quot;&gt;
            &lt;arg value=&quot;--working-dir=${project.basedir}&quot;/&gt;
            &lt;arg value=&quot;--no-interaction&quot;/&gt;
            &lt;arg value=&quot;--no-suggest&quot;/&gt;
            &lt;arg value=&quot;--ansi&quot;/&gt;
        &lt;/composer&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;platform-delete&quot;&gt;
        &lt;echo msg=&quot;Delete previous build.&quot;/&gt;
        &lt;phingcall target=&quot;unprotect-folder&quot;&gt;
            &lt;property name=&quot;folder.to.unprotect&quot; value=&quot;${platform.build.settings.dir}&quot;/&gt;
        &lt;/phingcall&gt;
        &lt;echo msg=&quot;Unprotecting folder.&quot;/&gt;
        &lt;phingcall target=&quot;delete-folder&quot;&gt;
            &lt;property name=&quot;folder.to.delete&quot; value=&quot;${platform.build.dir}&quot;/&gt;
        &lt;/phingcall&gt;
        &lt;echo msg=&quot;Deleting folder.&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;platform-download&quot;&gt;
        &lt;if&gt;
            &lt;available file=&quot;${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz&quot; type=&quot;file&quot;/&gt;
            &lt;then&gt;
                  &lt;echo msg=&quot;Package platform-dev-${platform.package.reference}.tar.gz already downloaded.&quot;/&gt;
            &lt;/then&gt;
            &lt;else&gt;
                &lt;!-- Create the destination directory if it doesn't exist. --&gt;
                &lt;mkdir dir=&quot;${platform.package.cachedir}&quot;/&gt;
                &lt;echo msg=&quot;Starting platform download. Depending on your connection this can take between 5-15 minutes. Go get some coffee.&quot;/&gt;
                &lt;if&gt;
                    &lt;http url=&quot;https://github.com/ec-europa/platform-dev/releases/download/${platform.package.reference}/platform-dev-${platform.package.reference}.tar.gz&quot;/&gt;
                    &lt;then&gt;
                        &lt;exec command=&quot;curl -L -o ${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz https://github.com/ec-europa/platform-dev/releases/download/${platform.package.reference}/platform-dev-${platform.package.reference}.tar.gz&quot; passthru=&quot;true&quot;/&gt;
                        &lt;echo msg=&quot;Downloaded platform package reference ${platform.package.reference}&quot;/&gt;
                    &lt;/then&gt;
                    &lt;else&gt;
                        &lt;fail msg=&quot;Failed downloading platform package reference ${platform.package.reference}&quot;/&gt;
                    &lt;/else&gt;
                &lt;/if&gt;
            &lt;/else&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;platform-link-resources&quot;&gt;
        &lt;rel-sym link=&quot;${platform.build.subsite.modules.custom.dir}&quot; target=&quot;${subsite.resources.modules.dir}&quot;/&gt;
        &lt;rel-sym link=&quot;${platform.build.subsite.modules.features.dir}&quot; target=&quot;${subsite.resources.features.dir}&quot;/&gt;
        &lt;delete dir=&quot;${platform.build.subsite.themes.dir}&quot; includeemptydirs=&quot;true&quot; failonerror=&quot;false&quot;/&gt;
        &lt;rel-sym link=&quot;${platform.build.subsite.themes.dir}&quot; target=&quot;${subsite.resources.themes.dir}&quot;/&gt;
        &lt;rel-sym link=&quot;${platform.build.subsite.source.dir}&quot; target=&quot;${subsite.resources.source.dir}&quot;/&gt;
        &lt;rel-sym link=&quot;${platform.build.subsite.composer.json}&quot; target=&quot;${subsite.resources.composer.json}&quot;/&gt;
        &lt;rel-sym link=&quot;${platform.build.subsite.composer.lock}&quot; target=&quot;${subsite.resources.composer.lock}&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;platform-make&quot; depends=&quot;platform-unpack&quot;&gt;
        &lt;if&gt;
            &lt;available file=&quot;${subsite.make}&quot; type=&quot;file&quot;/&gt;
            &lt;then&gt;
                &lt;echo msg=&quot;Make the subsite.&quot;/&gt;
                &lt;phingcall target=&quot;drush-make-no-core&quot;&gt;
                    &lt;property name=&quot;drush.make.target.file&quot; value=&quot;${subsite.make}&quot;/&gt;
                    &lt;property name=&quot;drush.make.root&quot; value=&quot;${platform.build.dir}&quot;/&gt;
                &lt;/phingcall&gt;
            &lt;/then&gt;
            &lt;else&gt;
                &lt;echo msg=&quot;No make file found. Skipping...&quot;/&gt;
            &lt;/else&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;platform-unpack&quot; depends=&quot;platform-download&quot;&gt;
        &lt;!-- Use the faster native commands on UNIX systems. --&gt;
        &lt;if&gt;
            &lt;os family=&quot;unix&quot;/&gt;
            &lt;then&gt;
                &lt;echo msg=&quot;mkdir &amp;quot;${platform.build.dir}&amp;quot;&quot;/&gt;
                &lt;exec command=&quot;mkdir &amp;quot;${platform.build.dir}&amp;quot;&quot; dir=&quot;${project.basedir}&quot; passthru=&quot;true&quot;/&gt;
                &lt;echo msg=&quot;tar xzf &amp;quot;${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz&amp;quot; -C &amp;quot;${platform.build.dir}&amp;quot;&quot;/&gt;
                &lt;exec command=&quot;tar xzf &amp;quot;${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz&amp;quot; -C &amp;quot;${platform.build.dir}&amp;quot;&quot; dir=&quot;${project.basedir}&quot; passthru=&quot;true&quot; checkreturn=&quot;true&quot;/&gt;
            &lt;/then&gt;
            &lt;else&gt;
                &lt;untar file=&quot;${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz&quot; todir=&quot;${platform.build.dir}&quot;/&gt;
            &lt;/else&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;platform-update-htaccess&quot;&gt;
        &lt;if&gt;
            &lt;istrue value=&quot;${drupal.htaccess.append.text}&quot;/&gt;
            &lt;then&gt;
                &lt;echo msg=&quot;Appended text to htaccess.&quot;/&gt;
                &lt;append destfile=&quot;${drupal.htaccess.path}&quot; text=&quot;${drupal.htaccess.append.text}&quot;/&gt;
            &lt;/then&gt;
            &lt;else&gt;
                &lt;echo msg=&quot;Appended no text to htaccess.&quot;/&gt;
            &lt;/else&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;prompt-for-credentials-and-retry&quot; hidden=&quot;true&quot;&gt;
        &lt;input propertyName=&quot;project.database.url.htaccess.username&quot; message=&quot;Please enter your username.&quot;/&gt;
        &lt;input hidden=&quot;true&quot; propertyName=&quot;project.database.url.htaccess.password&quot; message=&quot;Please enter your password.&quot;/&gt;
        &lt;phingcall target=&quot;subsite-database-wget&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;starterkit-build-documentation-index&quot;&gt;
        &lt;build-documentation-index/&gt;        
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;starterkit-copy-templates&quot;&gt;
        &lt;echo msg=&quot;Ensuring the presence of build.xml and Jenkinsfile.&quot;/&gt;
        &lt;copy todir=&quot;${project.basedir}&quot;&gt;
            &lt;fileset dir=&quot;${subsite.starterkit.templates}&quot;/&gt;
        &lt;/copy&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;starterkit-link-binary&quot;&gt;
        &lt;echo msg=&quot;Provide project with starterkit binary at root level.&quot;/&gt;
        &lt;rel-sym link=&quot;${project.basedir}/ssk&quot; target=&quot;${subsite.starterkit.bin}&quot; overwrite=&quot;true&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;starterkit-upgrade&quot;&gt;

        &lt;!-- Delete starterkit folders. --&gt;
        &lt;phingcall target=&quot;delete-folder&quot;&gt;
            &lt;property name=&quot;folder.to.delete&quot; value=&quot;${project.basedir}/bin&quot;/&gt;
        &lt;/phingcall&gt;
        &lt;phingcall target=&quot;delete-folder&quot;&gt;
            &lt;property name=&quot;folder.to.delete&quot; value=&quot;${project.basedir}/docs&quot;/&gt;
        &lt;/phingcall&gt;
        &lt;phingcall target=&quot;delete-folder&quot;&gt;
            &lt;property name=&quot;folder.to.delete&quot; value=&quot;${project.basedir}/src&quot;/&gt;
        &lt;/phingcall&gt;
        &lt;phingcall target=&quot;delete-folder&quot;&gt;
            &lt;property name=&quot;folder.to.delete&quot; value=&quot;${subsite.resources.dir}/cloudformation&quot;/&gt;
        &lt;/phingcall&gt;
        &lt;phingcall target=&quot;delete-folder&quot;&gt;
            &lt;property name=&quot;folder.to.delete&quot; value=&quot;${subsite.resources.dir}/codedeploy&quot;/&gt;
        &lt;/phingcall&gt;
        &lt;phingcall target=&quot;delete-folder&quot;&gt;
            &lt;property name=&quot;folder.to.delete&quot; value=&quot;${subsite.resources.dir}/composer&quot;/&gt;
        &lt;/phingcall&gt;
        &lt;!-- Delete starterkit files. --&gt;
        &lt;delete&gt;
            &lt;fileset dir=&quot;${project.basedir}&quot;&gt;
                &lt;include name=&quot;CHANGELOG.md&quot;/&gt;
                &lt;include name=&quot;LICENSE.md&quot;/&gt;
                &lt;include name=&quot;README.md&quot;/&gt;
                &lt;include name=&quot;appspec.yml&quot;/&gt;
                &lt;include name=&quot;build.clone.xml&quot;/&gt;
                &lt;include name=&quot;build.package.xml&quot;/&gt;
                &lt;include name=&quot;build.properties.dist&quot;/&gt;
                &lt;include name=&quot;build.test.xml&quot;/&gt;
                &lt;include name=&quot;composer.lock&quot;/&gt;
                &lt;include name=&quot;phpcs-ruleset.xml&quot;/&gt;
            &lt;/fileset&gt;
        &lt;/delete&gt;
        &lt;!-- Move subsite files to new location. --&gt;
        &lt;move file=&quot;${subsite.resources.dir}/phpcs-custom.xml&quot; tofile=&quot;phpcs-ruleset.xml&quot; overwrite=&quot;true&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;subsite-composer-install&quot;&gt;
        &lt;echo msg=&quot;Run 'composer install' in the subsite folder for development purposes.&quot;/&gt;
        &lt;composer command=&quot;install&quot; composer=&quot;${composer.bin}&quot;&gt;
            &lt;arg value=&quot;--working-dir=${platform.build.subsite.dir}&quot;/&gt;
            &lt;arg value=&quot;--no-interaction&quot;/&gt;
            &lt;!-- &lt;arg value=&quot;no-plugins&quot; /&gt; --&gt;
            &lt;arg value=&quot;--no-suggest&quot;/&gt;
            &lt;arg value=&quot;--ansi&quot;/&gt;
        &lt;/composer&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;subsite-database-download&quot;&gt;
        &lt;echo msg=&quot;Download the production database.&quot;/&gt;
        &lt;!--Strips gz suffix. --&gt;
        &lt;php expression=&quot;substr('${project.database.filename}', 0, -3)&quot; returnProperty=&quot;gunzipped.filename&quot; level=&quot;debug&quot;/&gt;
        &lt;if&gt;
            &lt;not&gt;
                &lt;!-- Check if we have a previously downloaded dump available. --&gt;
                &lt;available file=&quot;tmp/${gunzipped.filename}&quot; type=&quot;file&quot; property=&quot;gunzipped.project.db&quot;/&gt;
            &lt;/not&gt;
            &lt;then&gt;
                &lt;!-- If not available, download and unzip the file. --&gt;
                &lt;phingcall target=&quot;subsite-database-wget&quot;/&gt;
                &lt;exec command=&quot;gunzip tmp/${project.database.filename}&quot; checkreturn=&quot;true&quot; passthru=&quot;false&quot; logoutput=&quot;true&quot;/&gt;
            &lt;/then&gt;
            &lt;else&gt;
                &lt;!-- Inform user if file was already downloaded. --&gt;
                &lt;echo msg=&quot;File ${gunzipped.filename} already downloaded.&quot;/&gt;
                &lt;echo msg=&quot;Proceeding to import.&quot;/&gt;
            &lt;/else&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;subsite-database-import&quot; depends=&quot;subsite-database-download&quot;&gt;
        &lt;echo msg=&quot;Import production database.&quot;/&gt;
        &lt;!-- Drop database, create if necessary and import the dump. --&gt;
        &lt;phingcall target=&quot;drush-sql-drop&quot;/&gt;
        &lt;phingcall target=&quot;drush-sql-create&quot;/&gt;
        &lt;phingcall target=&quot;drush-sql-import&quot;&gt;
            &lt;property name=&quot;database-file&quot; value=&quot;tmp/${gunzipped.filename}&quot;/&gt;
        &lt;/phingcall&gt;
        &lt;phingcall target=&quot;drush-registry-rebuild&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;subsite-database-wget&quot;&gt;
        &lt;!--Generate .htaccess credential property if needed, empty if not. --&gt;
        &lt;if&gt;
            &lt;or&gt;
                &lt;equals arg1=&quot;${project.database.url.htaccess.username}&quot; arg2=&quot;&quot;/&gt;
                &lt;equals arg1=&quot;${project.database.url.htaccess.password}&quot; arg2=&quot;&quot;/&gt;
            &lt;/or&gt;
            &lt;then&gt;
                &lt;!-- If username or password is not provided, empty the credential string. --&gt;
                &lt;property name=&quot;project.database.url.credentials&quot; value=&quot;&quot; override=&quot;true&quot;/&gt;
            &lt;/then&gt;
            &lt;else&gt;
                &lt;!-- If username or password is provided, build the credential string. --&gt;
                &lt;property name=&quot;project.database.url.credentials&quot; value=&quot;${project.database.url.htaccess.username}:${project.database.url.htaccess.password}@&quot; override=&quot;true&quot;/&gt;
            &lt;/else&gt;
        &lt;/if&gt;
        &lt;!-- Attempt to download the database dump. --&gt;
        &lt;exec command=&quot;wget ${project.database.url.scheme}://${project.database.url.credentials}${project.database.url}${project.database.filename}&quot; dir=&quot;tmp&quot; checkreturn=&quot;false&quot; passthru=&quot;false&quot; outputProperty=&quot;project.database.download&quot;/&gt;
        &lt;if&gt;
            &lt;!-- Upon success inform the user. --&gt;
            &lt;contains string=&quot;${project.database.download}&quot; substring=&quot;200&quot;/&gt;
            &lt;then&gt;
                &lt;echo msg=&quot;Database successfully downloaded.&quot;/&gt;
            &lt;/then&gt;
            &lt;!-- When denied access, prompt the user for credentials and retry the download. --&gt;
            &lt;elseif&gt;
                &lt;contains string=&quot;${project.database.download}&quot; substring=&quot;401&quot;/&gt;
                &lt;then&gt;
                    &lt;phingcall target=&quot;prompt-for-credentials-and-retry&quot;/&gt;
                &lt;/then&gt;
            &lt;/elseif&gt;
            &lt;!-- Otherwise we fail the build and display the download message. --&gt;
            &lt;else&gt;
                &lt;echo msg=&quot;Failed to download the database dump. Result of wget:&quot; level=&quot;error&quot;/&gt;
                &lt;echo msg=&quot;${project.database.download}&quot; level=&quot;error&quot;/&gt;
                &lt;fail/&gt;
            &lt;/else&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;subsite-modules-development-download&quot; depends=&quot;subsite-modules-development-makefile&quot;&gt;
        &lt;echo msg=&quot;Download development modules.&quot;/&gt;
        &lt;phingcall target=&quot;drush-make-no-core&quot;&gt;
            &lt;property name=&quot;drush.make.target.file&quot; value=&quot;${subsite.temporary.development.make}&quot;/&gt;
            &lt;property name=&quot;drush.make.root&quot; value=&quot;${platform.build.dir}&quot;/&gt;
        &lt;/phingcall&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;subsite-modules-development-enable&quot;&gt;
        &lt;phingcall target=&quot;drush-enable-modules&quot;&gt;
            &lt;property name=&quot;drupal.modules&quot; value=&quot;${development.modules.enable}&quot;/&gt;
        &lt;/phingcall&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;subsite-modules-development-makefile&quot;&gt;
        &lt;echo msg=&quot;Generate the makefile for development modules.&quot;/&gt;
        &lt;if&gt;
            &lt;available file=&quot;${subsite.temporary.development.make}&quot; type=&quot;file&quot; property=&quot;development.makefile.available&quot;/&gt;
            &lt;then&gt;
                &lt;echo message=&quot;Deleting existing makefile.&quot;/&gt;
                &lt;delete file=&quot;${subsite.temporary.development.make}&quot; failonerror=&quot;false&quot;/&gt;
            &lt;/then&gt;
        &lt;/if&gt;
        &lt;drushmakefile makeFile=&quot;${subsite.temporary.development.make}&quot; coreVersion=&quot;${drupal.core.version}&quot; projects=&quot;${development.modules.download}&quot; defaultProjectDir=&quot;${development.modules.location}&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;subsite-modules-install-enable&quot;&gt;
        &lt;phingcall target=&quot;drush-enable-modules&quot;&gt;
            &lt;property name=&quot;drupal.modules&quot; value=&quot;${subsite.install.modules}&quot;/&gt;
        &lt;/phingcall&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;subsite-setup-files-directory&quot;&gt;
        &lt;if&gt;
            &lt;istrue value=&quot;${platform.build.files.dir}&quot;/&gt;
            &lt;then&gt;
                &lt;mkdir dir=&quot;${platform.build.files.dir}/private_files&quot;/&gt;
                &lt;mkdir dir=&quot;${platform.build.tmp.dir}&quot;/&gt;
                &lt;!-- Support CSS and JS injector. --&gt;
                &lt;mkdir dir=&quot;${platform.build.files.dir}/css_injector&quot;/&gt;
                &lt;mkdir dir=&quot;${platform.build.files.dir}/js_injector&quot;/&gt;
            &lt;/then&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;subsite-site-backup&quot;&gt;

        &lt;!-- Check if site exists. --&gt;
        &lt;if&gt;
            &lt;available file=&quot;${platform.build.settings.dir}/settings.php&quot; type=&quot;file&quot;/&gt;
            &lt;then&gt;
                &lt;property name=&quot;site-detected&quot; value=&quot;1&quot;/&gt;
            &lt;/then&gt;
            &lt;else&gt;
                &lt;echo msg=&quot;No site installation detected. Skipping backup.&quot;/&gt;
            &lt;/else&gt;
        &lt;/if&gt;

        &lt;if&gt;
            &lt;and&gt;
                &lt;equals arg1=&quot;${platform.rebuild.auto}&quot; arg2=&quot;0&quot;/&gt;
                &lt;equals arg1=&quot;${site-detected}&quot; arg2=&quot;1&quot;/&gt;
            &lt;/and&gt;
            &lt;then&gt;
                &lt;echo msg=&quot;Installed site detected.&quot; level=&quot;warning&quot;/&gt;
                &lt;propertyprompt propertyName=&quot;subsite-site-backup-activated&quot; promptText=&quot;Do you wish to backup site for this build? (y/n)&quot;/&gt;
                &lt;if&gt;
                    &lt;equals arg1=&quot;${subsite-site-backup-activated}&quot; arg2=&quot;y&quot;/&gt;
                    &lt;then&gt;
                        &lt;property name=&quot;platform.rebuild.auto&quot; value=&quot;1&quot; override=&quot;true&quot;/&gt;
                    &lt;/then&gt;
                &lt;/if&gt;
            &lt;/then&gt;
        &lt;/if&gt;
        &lt;if&gt;
            &lt;and&gt;
                &lt;equals arg1=&quot;${platform.rebuild.auto}&quot; arg2=&quot;1&quot;/&gt;
                &lt;equals arg1=&quot;${site-detected}&quot; arg2=&quot;1&quot;/&gt;
            &lt;/and&gt;
            &lt;then&gt;
                &lt;if&gt;
                    &lt;!-- Delete any remains of previous backup attempts. --&gt;
                    &lt;available file=&quot;${platform.rebuild.backup.destination}&quot; type=&quot;dir&quot;/&gt;
                    &lt;then&gt;
                        &lt;delete dir=&quot;${platform.rebuild.backup.destination}&quot; includeemptydirs=&quot;true&quot;/&gt;
                    &lt;/then&gt;
                &lt;/if&gt;
                &lt;!-- Create backup directory. --&gt;
                &lt;mkdir dir=&quot;${platform.rebuild.backup.destination}&quot;/&gt;
                &lt;!-- Make the settings directory writable because we can not delete it otherwise --&gt;
                &lt;phingcall target=&quot;unprotect-folder&quot;&gt;
                    &lt;property name=&quot;folder.to.unprotect&quot; value=&quot;${platform.build.settings.dir}&quot;/&gt;
                &lt;/phingcall&gt;
                &lt;!-- Back up folders list. --&gt;
                &lt;foreach list=&quot;${platform.rebuild.backup.folders}&quot; param=&quot;site-item&quot; target=&quot;subsite-site-backup-item&quot; delimiter=&quot;;&quot;&gt;
                    &lt;property name=&quot;site-item-type&quot; value=&quot;dir&quot;/&gt;
                &lt;/foreach&gt;
                &lt;!-- Back up files list. --&gt;
                &lt;foreach list=&quot;${platform.rebuild.backup.files}&quot; param=&quot;site-item&quot; target=&quot;subsite-site-backup-item&quot; delimiter=&quot;;&quot;&gt;
                    &lt;property name=&quot;site-item-type&quot; value=&quot;file&quot;/&gt;
                &lt;/foreach&gt;
            &lt;/then&gt;
        &lt;/if&gt;
        &lt;if&gt;
            &lt;equals arg1=&quot;${subsite-site-backup-activated}&quot; arg2=&quot;y&quot;/&gt;
            &lt;then&gt;
                &lt;property name=&quot;platform.rebuild.auto&quot; value=&quot;0&quot; override=&quot;true&quot;/&gt;
            &lt;/then&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;subsite-site-backup-item&quot; hidden=&quot;true&quot;&gt;
        &lt;php expression=&quot;dirname(&amp;quot;${site-item}&amp;quot;)&quot; returnProperty=&quot;site-item-dir&quot;/&gt;
        &lt;property name=&quot;site-item-backup-dir&quot; value=&quot;${site-item-dir}&quot;&gt;
            &lt;filterchain&gt;
                &lt;replaceregexp&gt;
                    &lt;regexp pattern=&quot;${platform.build.dir}&quot; replace=&quot;${platform.rebuild.backup.destination}&quot; ignoreCase=&quot;false&quot;/&gt;
                &lt;/replaceregexp&gt;
            &lt;/filterchain&gt;
        &lt;/property&gt;
        &lt;if&gt;
            &lt;available file=&quot;${site-item}&quot; type=&quot;${site-item-type}&quot;/&gt;
            &lt;then&gt;
                &lt;if&gt;
                    &lt;not&gt;
                        &lt;available file=&quot;${site-item-backup-dir}&quot; type=&quot;dir&quot;/&gt;
                    &lt;/not&gt;
                    &lt;then&gt;
                        &lt;mkdir dir=&quot;${site-item-backup-dir}&quot;/&gt;
                    &lt;/then&gt;
                &lt;/if&gt;
                &lt;move file=&quot;${site-item}&quot; todir=&quot;${site-item-backup-dir}&quot; includeemptydirs=&quot;true&quot;/&gt;
            &lt;/then&gt;
            &lt;else&gt;
                &lt;php expression=&quot;ucwords(&amp;quot;${site-item-type}&amp;quot;)&quot; returnProperty=&quot;site-item-type-capitalized&quot;/&gt;
                &lt;echo msg=&quot;Skipping ${site-item}. ${site-item-type-capitalized} not found.&quot; level=&quot;warning&quot;/&gt;
            &lt;/else&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;subsite-site-restore&quot;&gt;

        &lt;!-- Check if backup exists. --&gt;
        &lt;if&gt;
            &lt;available file=&quot;${platform.rebuild.backup.destination}&quot; type=&quot;dir&quot;/&gt;
            &lt;then&gt;
                &lt;property name=&quot;backup-detected&quot; value=&quot;1&quot;/&gt;
            &lt;/then&gt;
            &lt;else&gt;
                &lt;echo msg=&quot;No site backup detected. Skipping restore.&quot;/&gt;
            &lt;/else&gt;
        &lt;/if&gt;
        &lt;if&gt;
            &lt;and&gt;
                &lt;equals arg1=&quot;${platform.rebuild.auto}&quot; arg2=&quot;0&quot;/&gt;
                &lt;equals arg1=&quot;${backup-detected}&quot; arg2=&quot;1&quot;/&gt;
            &lt;/and&gt;
            &lt;then&gt;
                &lt;echo msg=&quot;Site backup detected.&quot; level=&quot;warning&quot;/&gt;
                &lt;propertyprompt propertyName=&quot;subsite-site-restore-activated&quot; promptText=&quot;Do you wish to restore site for this build? (y/n)&quot;/&gt;
                &lt;if&gt;
                    &lt;equals arg1=&quot;${subsite-site-restore-activated}&quot; arg2=&quot;y&quot;/&gt;
                    &lt;then&gt;
                        &lt;property name=&quot;platform.rebuild.auto&quot; value=&quot;1&quot; override=&quot;true&quot;/&gt;
                    &lt;/then&gt;
                &lt;/if&gt;
            &lt;/then&gt;
        &lt;/if&gt;
        &lt;if&gt;
            &lt;and&gt;
                &lt;equals arg1=&quot;${platform.rebuild.auto}&quot; arg2=&quot;1&quot;/&gt;
                &lt;equals arg1=&quot;${backup-detected}&quot; arg2=&quot;1&quot;/&gt;
            &lt;/and&gt;
            &lt;then&gt;
                &lt;echo msg=&quot;Restoring site files and folders from ${platform.rebuild.backup.destination}&quot;/&gt;
                &lt;!-- Restore folders list. --&gt;
                &lt;foreach list=&quot;${platform.rebuild.backup.folders}&quot; param=&quot;site-item&quot; target=&quot;subsite-site-restore-item&quot; delimiter=&quot;;&quot;&gt;
                    &lt;property name=&quot;site-item-type&quot; value=&quot;dir&quot;/&gt;
                &lt;/foreach&gt;
                &lt;!-- Restore files list. --&gt;
                &lt;foreach list=&quot;${platform.rebuild.backup.files}&quot; param=&quot;site-item&quot; target=&quot;subsite-site-restore-item&quot; delimiter=&quot;;&quot;&gt;
                    &lt;property name=&quot;site-item-type&quot; value=&quot;file&quot;/&gt;
                &lt;/foreach&gt;
                &lt;!-- Delete the site backup directory. --&gt;
                &lt;delete dir=&quot;${platform.rebuild.backup.destination}&quot; includeemptydirs=&quot;true&quot;/&gt;
            &lt;/then&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;subsite-site-restore-item&quot; hidden=&quot;true&quot;&gt;
        &lt;property name=&quot;site-item-backup&quot; value=&quot;${site-item}&quot;&gt;
            &lt;filterchain&gt;
                &lt;replaceregexp&gt;
                    &lt;regexp pattern=&quot;${platform.build.dir}&quot; replace=&quot;${platform.rebuild.backup.destination}&quot; ignoreCase=&quot;false&quot;/&gt;
                &lt;/replaceregexp&gt;
            &lt;/filterchain&gt;
        &lt;/property&gt;
        &lt;if&gt;
            &lt;available file=&quot;${site-item-backup}&quot; type=&quot;${site-item-type}&quot;/&gt;
            &lt;then&gt;
                &lt;php expression=&quot;dirname(&amp;quot;${site-item}&amp;quot;)&quot; returnProperty=&quot;site-item-dir&quot;/&gt;
                &lt;if&gt;
                    &lt;not&gt;
                        &lt;available file=&quot;${site-item-dir}&quot; type=&quot;dir&quot;/&gt;
                    &lt;/not&gt;
                    &lt;then&gt;
                        &lt;mkdir dir=&quot;${site-item-dir}&quot;/&gt;
                    &lt;/then&gt;
                &lt;/if&gt;
                &lt;move file=&quot;${site-item-backup}&quot; todir=&quot;${site-item-dir}&quot; includeemptydirs=&quot;true&quot;/&gt;
            &lt;/then&gt;
            &lt;else&gt;
                &lt;php expression=&quot;ucwords(&amp;quot;${site-item-type}&amp;quot;)&quot; returnProperty=&quot;site-item-type-capitalized&quot;/&gt;
                &lt;echo msg=&quot;Skipping ${site-item}. ${site-item-type-capitalized} not found.&quot; level=&quot;warning&quot;/&gt;
            &lt;/else&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;test-behat-setup&quot;&gt;
        &lt;if&gt;
            &lt;available file=&quot;${behat.yml.path}&quot; type=&quot;file&quot; property=&quot;behat.yml.available&quot;/&gt;
            &lt;then&gt;
                &lt;echo message=&quot;Deleting existing behat.yml configuration file&quot;/&gt;
                &lt;delete file=&quot;${behat.yml.path}&quot; failonerror=&quot;false&quot;/&gt;
            &lt;/then&gt;
        &lt;/if&gt;
        &lt;echo message=&quot;Creating behat.yml configuration file&quot;/&gt;
        &lt;loadfile property=&quot;behat.yml.content&quot; file=&quot;${behat.yml.template}&quot;&gt;
            &lt;filterchain&gt;
                &lt;replacetokens&gt;
                    &lt;token key=&quot;project.code.dir&quot; value=&quot;${project.code.dir}&quot;/&gt;
                    &lt;token key=&quot;drupal.site.dir&quot; value=&quot;${drupal.site.dir}&quot;/&gt;
                    &lt;token key=&quot;behat.base_url&quot; value=&quot;${behat.base_url}&quot;/&gt;
                    &lt;token key=&quot;behat.formatter.name&quot; value=&quot;${behat.formatter.name}&quot;/&gt;
                &lt;/replacetokens&gt;
            &lt;/filterchain&gt;
        &lt;/loadfile&gt;
        &lt;echo message=&quot;${behat.yml.content}&quot; file=&quot;${behat.yml.path}&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;test-behat-setup-link&quot;&gt;
        &lt;echo msg=&quot;Symlink the Behat bin and test directory in './sites/all'.&quot;/&gt;
        &lt;rel-sym link=&quot;${project.basedir}/ssk/behat&quot; target=&quot;${subsite.starterkit.vendor}/bin/behat&quot; overwrite=&quot;true&quot;/&gt;
        &lt;rel-sym link=&quot;${platform.build.subsite.dir}/tests&quot; target=&quot;${project.basedir}/tests&quot; overwrite=&quot;true&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;test-phpcs-setup&quot;&gt;
        &lt;if&gt;
            &lt;available file=&quot;${phpcs.config}&quot; type=&quot;file&quot; property=&quot;phpcs.config.available&quot;/&gt;
            &lt;then&gt;
                &lt;echo message=&quot;Deleting existing PHP Codesniffer default configuration file.&quot;/&gt;
                &lt;delete file=&quot;${phpcs.config}&quot; failonerror=&quot;false&quot;/&gt;
            &lt;/then&gt;
        &lt;/if&gt;
        &lt;if&gt;
            &lt;available file=&quot;${phpcs.global.config}&quot; type=&quot;file&quot; property=&quot;phpcs.global.config.available&quot;/&gt;
            &lt;then&gt;
                &lt;echo message=&quot;Deleting existing PHP Codesniffer global configuration file.&quot;/&gt;
                &lt;delete file=&quot;${phpcs.global.config}&quot; failonerror=&quot;false&quot;/&gt;
            &lt;/then&gt;
        &lt;/if&gt;
        &lt;phpcodesnifferconfiguration configFile=&quot;${phpcs.config}&quot; extensions=&quot;${phpcs.extensions}&quot; files=&quot;${phpcs.files}&quot; globalConfig=&quot;${phpcs.global.config}&quot; ignorePatterns=&quot;${phpcs.ignore}&quot; passWarnings=&quot;${phpcs.passwarnings}&quot; report=&quot;${phpcs.report}&quot; showProgress=&quot;${phpcs.progress}&quot; showSniffCodes=&quot;${phpcs.sniffcodes}&quot; standards=&quot;${phpcs.standards}&quot;/&gt;

        &lt;!-- Set up the git pre-push hook. --&gt;
        &lt;phingcall target=&quot;test-phpcs-setup-prepush&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;test-phpcs-setup-prepush&quot;&gt;
        &lt;if&gt;
            &lt;equals arg1=&quot;${phpcs.prepush.enable}&quot; arg2=&quot;1&quot;/&gt;
            &lt;then&gt;
                &lt;echo message=&quot;Enabling git pre-push hook.&quot;/&gt;
                &lt;mkdir dir=&quot;${project.basedir}/resources/git/hooks/pre-push&quot;/&gt;
                &lt;rel-sym link=&quot;${phpcs.prepush.destination}&quot; target=&quot;${phpcs.prepush.source}&quot; overwrite=&quot;true&quot;/&gt;
            &lt;/then&gt;
           &lt;else&gt;
                &lt;echo message=&quot;Disabling git pre-push hook.&quot;/&gt;
                &lt;delete file=&quot;${phpcs.prepush.destination}&quot; failonerror=&quot;false&quot; quiet=&quot;true&quot;/&gt;
          &lt;/else&gt;
        &lt;/if&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;test-quality-assurance&quot;&gt;
        &lt;exec command=&quot;${subsite.starterkit.bin}/qa review:full --no-interaction --ansi&quot; passthru=&quot;true&quot; checkreturn=&quot;true&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;test-run-php-codesniffer&quot;&gt;
        &lt;exec command=&quot;${subsite.starterkit.bin}/phpcs&quot; passthru=&quot;true&quot; checkreturn=&quot;true&quot;/&gt;
    &lt;/target&gt;
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
&lt;?xml version=&quot;1.0&quot;?&gt;
&lt;target name=&quot;unprotect-folder&quot; hidden=&quot;true&quot;&gt;
        &lt;!-- This should only be used on folders that need to be removed. --&gt;
        &lt;if&gt;
            &lt;available file=&quot;${folder.to.unprotect}&quot; type=&quot;dir&quot;/&gt;
            &lt;then&gt;
                &lt;chmod mode=&quot;0777&quot; failonerror=&quot;true&quot; verbose=&quot;false&quot; quiet=&quot;true&quot;&gt;
                    &lt;fileset dir=&quot;${folder.to.unprotect}&quot;/&gt;
                &lt;/chmod&gt;
            &lt;/then&gt;
        &lt;/if&gt;
    &lt;/target&gt;
                        </code>
                    </pre>
                </details>
            </td>
        </tr>
    </tbody>
</table>