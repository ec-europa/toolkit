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

> ```xml 
> <?xml version="1.0"?>
> <target name="build-clean" description="Build local version of subsite with a clean install." depends="drush-create-files-dirs, install, subsite-modules-development-enable"/>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-clone](/includes/build/build.clone.xml#L118) </td>
            <td>
                <details>
                    <summary>Build local version of subsite with production data.</summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="build-clone" description="Build local version of subsite with production data." depends="subsite-database-download, drush-regenerate-settings, subsite-database-import, subsite-modules-development-enable"/>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-code](/includes/build/build.package.xml#L74) </td>
            <td>
                <details>
                    <summary>Build local version of subsite without install.</summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="build-code" description="Build local version of subsite without install." depends="             subsite-site-backup,             platform-delete,             platform-make,             platform-link-resources,             subsite-composer-install,             test-behat-setup-link,             test-behat-setup,             platform-update-htaccess,             test-phpcs-setup,             subsite-modules-development-download,             subsite-site-restore"/>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-keep](/includes/build/build.package.xml#L92) </td>
            <td>
                <details>
                    <summary>Build local version of subsite with backup and restore.</summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="build-keep" description="Build local version of subsite with backup and restore.">
>         <!-- Execute build-dev with automatic rebuild enabled. -->
>         <phingcall target="build-dev">
>             <property name="platform.rebuild.auto" value="1" override="true"/>
>         </phingcall>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-release](/includes/build/build.package.xml#L63) </td>
            <td>
                <details>
                    <summary>Build subsite source code release package.</summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="build-release" description="Build subsite source code release package." depends="build-dist">
>         <mkdir dir="${project.release.path}"/>
>         <exec command="tar -czf ${project.release.path}/${project.release.name}.tar.gz ${phing.subsite.build.dir}"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-tests](/includes/build/build.package.xml#L69) </td>
            <td>
                <details>
                    <summary>Build subsite tests code release package.</summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="build-tests" description="Build subsite tests code release package.">
>         <mkdir dir="${project.release.path}"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [docker-compose-down](/includes/build/build.docker.xml#L22) </td>
            <td>
                <details>
                    <summary>Trash docker project.</summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="docker-compose-down" description="Trash docker project.">
>         <echo msg="Removing containers and volumes for ${docker.project.id}"/>
>         <exec command="docker-compose -p ${docker.project.id} -f ${subsite.starterkit.root}/resources/docker/docker-compose.yml down --volumes"/>
>         <delete file="${project.basedir}/ssk-${docker.project.id}"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [docker-compose-stop](/includes/build/build.docker.xml#L15) </td>
            <td>
                <details>
                    <summary>Stop docker project.</summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="docker-compose-stop" description="Stop docker project.">
>         <echo msg="Stopping containers for ${docker.project.id}"/>
>         <exec command="docker-compose -p ${docker.project.id} -f ${subsite.starterkit.root}/resources/docker/docker-compose.yml stop"/>
>         <exec command="${project.basedir}/ssk-${docker.project.id} ps" passthru="true"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [docker-compose-up](/includes/build/build.docker.xml#L5) </td>
            <td>
                <details>
                    <summary>Start docker project.</summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="docker-compose-up" description="Start docker project.">
>         <echo msg="Starting containers for ${docker.project.id}"/>
>         <mkdir dir="${platform.build.dir}"/> 
>         <mkdir dir="${share.platform.path}/databases/platform-dev-${platform.package.reference}"/>
>         <exec command="DB_LOCATION_DIR=${share.platform.path}/databases/platform-dev-${platform.package.reference} docker-compose -p ${docker.project.id} -f ${subsite.starterkit.root}/resources/docker/docker-compose.yml up -d --no-recreate"/>
>         <rel-sym link="${project.basedir}/ssk-${docker.project.id}" target="${subsite.starterkit.root}/resources/docker/dbash" overwrite="true"/>
>         <exec command="${project.basedir}/ssk-${docker.project.id} ps" passthru="true"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [install](/includes/build/build.test.xml#L5) </td>
            <td>
                <details>
                    <summary>Install the subsite.</summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="install" description="Install the subsite.">
>         <!--
>             Ensure the settings folder is writable so the installer can create
>             the settings.php file.
>          -->
>         <chmod mode="0775" failonerror="false" verbose="false" quiet="true">
>             <fileset dir="${platform.build.settings.dir}"/>
>         </chmod>
> 
>         <if>
>             <and>
>                 <equals arg1="${platform.package.database}" arg2="1"/>
>                 <available file="${share.platform.path}/databases/platform-dev-${platform.package.reference}/platform-dev-${platform.package.reference}.sql" type="file"/>
>             </and>
>             <then>
>                 <phingcall target="drush-regenerate-settings"/>
>                 <exec command="${drush.bin} --root=${platform.build.dir} status bootstrap | grep -q Successful" returnProperty="drush-status-bootstrap"/>
>                 <if>
>                     <not>
>                         <equals arg1="${drush-status-bootstrap}" arg2="0"/>
>                     </not>
>                     <then>
>                         <phingcall target="drush-sql-create"/>
>                         <phingcall target="drush-sql-import">
>                             <property name="database-file" value="${share.platform.path}/databases/platform-dev-${platform.package.reference}/platform-dev-${platform.package.reference}.sql"/>
>                         </phingcall>
>                     </then>
>                 </if> 
>             </then>
>             <else>
>                 <!-- Install site with drush. -->
>                 <phingcall target="drush-site-install"/>
>                 <!-- Backup vanilla database. -->
>                 <if>
>                     <equals arg1="${platform.package.database}" arg2="1"/>
>                     <then>
>                         <phingcall target="drush-sql-dump">
>                             <property name="database-file" value="${share.platform.path}/databases/platform-dev-${platform.package.reference}/platform-dev-${platform.package.reference}.sql"/>
>                         </phingcall>
>                     </then>
>                 </if>
>             </else>
>         </if>
> 
>         <!-- Enable solr if needed. -->
>         <phingcall target="drush-enable-solr"/>
> 
>         <!--
>             Subsites are not allowed to use their own installation profile for
>             historical reasons. The functionality is contained in one of more
>             features and modules which need to be enabled after installation.
>         -->
>         <phingcall target="subsite-modules-install-enable"/>
> 
>         <!-- Rebuild node access after Subsites modules activation -->
>         <phingcall target="drush-rebuild-node-access"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [link-docroot](/includes/build/build.package.xml#L28) </td>
            <td>
                <details>
                    <summary>Create symlink from build to docroot.</summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="link-docroot" description="Create symlink from build to docroot.">
>         <rel-sym link="${server.docroot}" target="${platform.build.dir}" overwrite="true"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-behat](/includes/build/build.test.xml#L150) </td>
            <td>
                <details>
                    <summary>Refresh configuration and run behat tests.</summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="test-run-behat" description="Refresh configuration and run behat tests.">
>         <behat executable="${behat.bin}" config="${behat.yml.path}" strict="${behat.options.strict}" verbose="${behat.options.verbosity}" passthru="${behat.options.passthru}"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-phpcs](/includes/build/build.test.xml#L186) </td>
            <td>
                <details>
                    <summary>Refresh configuration and run phpcs review.</summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="test-run-phpcs" description="Refresh configuration and run phpcs review." depends="test-phpcs-setup, test-run-php-codesniffer"/>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-qa](/includes/build/build.test.xml#L179) </td>
            <td>
                <details>
                    <summary>Refresh configuration and run qa review.</summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="test-run-qa" description="Refresh configuration and run qa review." depends="test-phpcs-setup, test-quality-assurance"/>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-dev](/includes/build/build.deprecated.xml#L5) </td>
            <td>
                <details>
                    <summary> Target build-dev has been replaced by build-code. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target hidden="true" name="build-dev">
>         <replaced target="build-code"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [build-dist](/includes/build/build.package.xml#L100) </td>
            <td>
                <details>
                    <summary> Create distribution code base. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="build-dist" hidden="true" depends="             dist-delete,             dist-make,             dist-copy-resources,             dist-composer-install"/>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [check-for-default-settings-or-rebuild](/includes/build/build.clone.xml#L88) </td>
            <td>
                <details>
                    <summary> Target to check if we have default settings, otherwise propose user to rebuild. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="check-for-default-settings-or-rebuild" hidden="true">
>         <if>
>             <not>
>                 <available file="${platform.build.settings.dir}/default.settings.php" type="file" property="platform.build.settings.dir.default.settings"/>
>             </not>
>             <then>
>                 <!-- If we can not find default settings in the build settings folder, prompt to ask user to rebuild. -->
>                 <echo msg="No default settings detected at ${platform.build.settings.dir}/default.settings.php." level="warning"/>
>                 <propertyprompt propertyName="platform-rebuild" defaultValue="no" promptText="Do you wish to rebuild? (y/n)"/>
>                 <if>
>                     <equals arg1="${platform-rebuild}" arg2="y"/>
>                     <then>
>                         <phingcall target="build-dev"/>
>                     </then>
>                     <else>
>                         <!-- If user chooses not to rebuild we have no other choice to fail the build. -->
>                         <echo msg="Can not re-generate settings, canceling clone task." level="error"/>
>                         <fail/>
>                     </else>
>                 </if>
>             </then>
>             <else>
>                 <!-- If we have found the default settings inform the user we will proceed with generation. -->
>                 <echo msg="Default settings found at ${platform.build.settings.dir}/default.settings.php."/>
>                 <echo msg="Proceeding with re-generation of the settings.php."/>
>             </else>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [composer-echo-hook-phingcalls](/includes/build/build.composer.xml#L5) </td>
            <td>
                <details>
                    <summary> Echo the composer hook phingcalls. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="composer-echo-hook-phingcalls" hidden="true">
>         <echoproperties prefix="composer.hook."/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [copy-folder](/includes/build/build.helpers.xml#L5) </td>
            <td>
                <details>
                    <summary> Copies a given folder to a new location. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="copy-folder" hidden="true">
>         <copy todir="${copy.destination.path}" haltonerror="${copy.path.haltonerror}">
>             <fileset dir="${copy.source.path}" defaultexcludes="false"/>
>         </copy>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [create-tmp-dirs](/includes/build/build.package.xml#L35) </td>
            <td>
                <details>
                    <summary> Create temp dirs. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="create-tmp-dirs" hidden="true">
>         <if>
>             <!-- Create the global cache directory if it doesn't exist. -->
>             <not>
>                 <available file="${platform.package.cachedir}" type="dir"/>
>             </not>
>             <then>
>                 <mkdir dir="${platform.package.cachedir}"/>
>             </then>
>             <else>
>                 <echo msg="Directory ${platform.package.cachedir} exists."/>
>             </else>
>         </if>
>         <if>
>             <!-- Create the destination directory if it doesn't exist. -->
>             <not>
>                 <available file="${platform.package.destination}" type="dir"/>
>             </not>
>             <then>
>                 <mkdir dir="${platform.package.destination}"/>
>             </then>
>             <else>
>                 <echo msg="Directory ${platform.package.destination} exists."/>
>             </else>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [delete-folder](/includes/build/build.helpers.xml#L12) </td>
            <td>
                <details>
                    <summary> Delete a given folder. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="delete-folder" hidden="true">
>         <!-- Use the faster native command on UNIX systems. -->
>         <if>
>             <os family="unix"/>
>             <then>
>                 <echo msg="rm -rf &quot;${folder.to.delete}&quot;"/>
>                 <exec command="rm -rf &quot;${folder.to.delete}&quot;" dir="${project.basedir}" passthru="true" checkreturn="true"/>
>             </then>
>             <else>
>                 <delete dir="${folder.to.delete}" includeemptydirs="true" failonerror="false"/>
>             </else>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-composer-install](/includes/build/build.dist.xml#L5) </td>
            <td>
                <details>
                    <summary> Install Composer dist dependencies for the subsite. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="dist-composer-install">
>         <echo msg="Run 'composer install --no-dev' in the build destination folder."/>
>         <composer command="install" composer="${composer.bin}">
>             <arg value="--working-dir=${dist.build.dir}"/>
>             <arg value="--no-interaction"/>
>             <arg value="--no-plugins"/>
>             <arg value="--no-suggest"/>
>             <arg value="--no-dev"/>
>             <arg value="--ansi"/>
>         </composer>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-copy-resources](/includes/build/build.dist.xml#L18) </td>
            <td>
                <details>
                    <summary> Copy subsite resources into the build folder. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="dist-copy-resources">
>         <echo msg="Copy custom resources."/>
>         <!-- Copy our custom modules. -->
>         <phingcall target="copy-folder">
>             <property name="copy.source.path" value="${subsite.resources.modules.dir}"/>
>             <property name="copy.destination.path" value="${dist.build.modules.custom.dir}"/>
>             <property name="copy.path.haltonerror" value="false" override="true"/>
>         </phingcall>
>         <!-- Copy our custom features. -->
>         <phingcall target="copy-folder">
>             <property name="copy.source.path" value="${subsite.resources.features.dir}"/>
>             <property name="copy.destination.path" value="${dist.build.modules.features.dir}"/>
>             <property name="copy.path.haltonerror" value="false" override="true"/>
>         </phingcall>
>         <!-- Copy our custom themes. -->
>         <phingcall target="copy-folder">
>             <property name="copy.source.path" value="${subsite.resources.themes.dir}"/>
>             <property name="copy.destination.path" value="${dist.build.themes.dir}"/>
>             <property name="copy.path.haltonerror" value="false" override="true"/>
>         </phingcall>
>         <!-- Copy our custom PSR-4 code. -->
>         <phingcall target="copy-folder">
>             <property name="copy.source.path" value="${subsite.resources.source.dir}"/>
>             <property name="copy.destination.path" value="${dist.build.source.dir}"/>
>             <property name="copy.path.haltonerror" value="false" override="true"/>
>         </phingcall>
>         <!-- Copy composer configuration. -->
>         <copy todir="${dist.build.dir}" file="${subsite.resources.composer.json}"/>
>         <copy todir="${dist.build.dir}" file="${subsite.resources.composer.lock}"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-delete](/includes/build/build.dist.xml#L50) </td>
            <td>
                <details>
                    <summary> Delete the previous distribution build. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="dist-delete">
>         <echo msg="Delete previous build."/>
>         <phingcall target="delete-folder">
>             <property name="folder.to.delete" value="${dist.build.dir}"/>
>         </phingcall>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [dist-make](/includes/build/build.dist.xml#L58) </td>
            <td>
                <details>
                    <summary> Make the distribution version of the subsite. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="dist-make">
>         <echo msg="Delete temporary build folder."/>
>         <phingcall target="delete-folder">
>             <property name="folder.to.delete" value="${phing.subsite.tmp.dir}/build"/>
>         </phingcall>
> 
>         <echo msg="Make the subsite."/>
>         <!--
>             Drush make builds the site as if it is part of a complete Drupal
>             installation. The actual build is in the /sites/all subfolder. Build
>             in a temporary folder and move the subsite into place when done.
>          -->
>         <if>
>             <available file="${subsite.make}" type="file"/>
>             <then>
>                 <loadfile property="sitemake" file="${subsite.make}"/>
>                 <propertyregex property="not.empty" subject="${sitemake}" pattern="([^#; ])(libraries\[|projects\[)" match="$1" casesensitive="false" defaultvalue="empty"/>
>                 <if>
>                     <not><equals arg1="${not.empty}" arg2="empty"/></not>
>                     <then>
>                         <phingcall target="drush-make-no-core">
>                             <property name="drush.make.target.file" value="${subsite.make}"/>
>                             <property name="drush.make.root" value="${phing.subsite.tmp.dir}/build"/>
>                         </phingcall>
>                     </then>
>                     <else>
>                        <echo msg="Empty make file found. Skipping... ${not.empty}"/>
>                        <mkdir dir="${phing.subsite.tmp.dir}/build/sites/all"/>
>                     </else>
>                 </if>
>             </then>
>             <else>
>                 <echo msg="No make file found. Skipping..."/>
>                 <mkdir dir="${phing.subsite.tmp.dir}/build/sites/all"/>
>             </else>
>         </if>
> 
>         <!-- Move the subsite to its destination. -->
>         <echo msg="mv &quot;${phing.subsite.tmp.dir}/build/sites/all/&quot; &quot;${dist.build.dir}&quot;"/>
>         <exec command="mv &quot;${phing.subsite.tmp.dir}/build/sites/all/&quot; &quot;${dist.build.dir}&quot;" dir="${project.basedir}" passthru="true" checkreturn="true"/>
> 
>         <echo msg="Clean up temporary build folder."/>
>         <phingcall target="delete-folder">
>             <property name="folder.to.delete" value="${phing.subsite.tmp.dir}/build"/>
>         </phingcall>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-create-files-dirs](/includes/build/build.drush.xml#L32) </td>
            <td>
                <details>
                    <summary> Create the directories. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="drush-create-files-dirs">
>         <echo message="Creating files directories for ${drupal.db.name}."/>
>         <!-- Execute setttings generation script. -->
>         <drush command="php-script" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
>             <param>${subsite.starterkit.root}/includes/drush/generate-directories.php</param>
>         </drush>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-dl-rr](/includes/build/build.drush.xml#L162) </td>
            <td>
                <details>
                    <summary> Download registry rebuild. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="drush-dl-rr">
>         <echo message="Installing registry rebuild on user account."/>
>         <exec command="${drush.bin} pm-download registry_rebuild-7 -n &gt;/dev/null"/>
>         <exec command="${drush.bin} cc drush &gt;/dev/null"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-enable-modules](/includes/build/build.drush.xml#L19) </td>
            <td>
                <details>
                    <summary> Enable modules. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="drush-enable-modules" hidden="true">
>         <drush command="pm-enable" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
>             <param>${drupal.modules}</param>
>         </drush>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-enable-solr](/includes/build/build.drush.xml#L83) </td>
            <td>
                <details>
                    <summary> Activate solr if needed. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="drush-enable-solr">
>         <if>
>             <equals arg1="${drupal.solr.activate}" arg2="1"/>
>             <then>
>                 <echo message="Enable apachesolr for ${drupal.db.name}."/>
>                 <phingcall target="drush-enable-modules">
>                     <property name="drupal.modules" value="apachesolr"/>
>                 </phingcall>
>                 <drush command="solr-set-env-url" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
>                     <param>${drupal.solr.env.url}</param>
>                 </drush>
>             </then>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-make-no-core](/includes/build/build.drush.xml#L99) </td>
            <td>
                <details>
                    <summary> Execute a makefile with the no-core option. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="drush-make-no-core">
>         <echo message="Running make file ${drush.make.target.file} into folder ${drush.make.root}."/>
>         <drush command="make" assume="yes" bin="${drush.bin}" pipe="yes" verbose="${drush.verbose}" root="${drush.make.root}" color="${drush.color}">
>             <param>${drush.make.target.file}</param>
>             <param>${drush.make.root}</param>
>             <option name="concurrency">10</option>
>             <option name="no-patch-txt"/>
>             <option name="no-core"/>
>         </drush>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-rebuild-node-access](/includes/build/build.drush.xml#L169) </td>
            <td>
                <details>
                    <summary> Rebuild node access. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="drush-rebuild-node-access">
>         <drush command="php-eval" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
>             <param>"node_access_rebuild()"</param>
>         </drush>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-regenerate-settings](/includes/build/build.drush.xml#L111) </td>
            <td>
                <details>
                    <summary> Regenerate the settings file with database credentials and development variables. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="drush-regenerate-settings" depends="check-for-default-settings-or-rebuild">
>         <copy file="${subsite.starterkit.root}/includes/drush/generate-settings.php" tofile="tmp/generate-settings.php" overwrite="true">
>             <filterchain>
>                 <replacetokens begintoken="%%" endtoken="%%">
>                     <!-- Replace tokens in settings generation script. -->
>                     <token key="drupal.db.type" value="${drupal.db.type}"/>
>                     <token key="drupal.db.name" value="${drupal.db.name}"/>
>                     <token key="drupal.db.user" value="${drupal.db.user}"/>
>                     <token key="drupal.db.password" value="${drupal.db.password}"/>
>                     <token key="drupal.db.host" value="${drupal.db.host}"/>
>                     <token key="drupal.db.port" value="${drupal.db.port}"/>
>                     <token key="error_level" value="${development.variables.error_level}"/>
>                     <token key="views_ui_show_sql_query" value="${development.variables.views_ui_show_sql_query}"/>
>                     <token key="views_ui_show_performance_statistics" value="${development.variables.views_ui_show_performance_statistics}"/>
>                     <token key="views_show_additional_queries" value="${development.variables.views_show_additional_queries}"/>
>                     <token key="stage_file_proxy_origin" value="${development.variables.stage_file_proxy_origin}"/>
>                     <token key="stage_file_proxy_origin_dir" value="${development.variables.stage_file_proxy_origin_dir}"/>
>                     <token key="stage_file_proxy_hotlink" value="${development.variables.stage_file_proxy_hotlink}"/>
>                     <token key="file_public_path" value="${platform.build.files.dir}"/>
>                     <token key="file_private_path" value="${platform.build.files.dir}/private_files"/>
>                     <token key="file_temporary_path" value="${platform.build.tmp.dir}"/>
>                 </replacetokens>
>             </filterchain>
>         </copy>
>         <!-- Execute setttings generation script. -->
>         <drush command="php-script" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
>             <param>tmp/generate-settings.php</param>
>         </drush>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-registry-rebuild](/includes/build/build.drush.xml#L142) </td>
            <td>
                <details>
                    <summary> Rebuild registry. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="drush-registry-rebuild">
>         <trycatch>
>             <try>
>                 <!-- Check if registry rebuild is available. -->
>                 <exec command="${drush.bin} rr --help" checkreturn="true"/>
>             </try>
>             <catch>
>                 <!-- Download if not available. -->
>                 <phingcall target="drush-dl-rr"/>
>             </catch>
>             <finally>
>                  <!-- Rebuild Registry. -->
>                  <drush command="registry-rebuild" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}">
>                      <param>--fire-bazooka</param>
>                  </drush>
>             </finally>
>         </trycatch>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-site-install](/includes/build/build.drush.xml#L5) </td>
            <td>
                <details>
                    <summary> Install the site. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="drush-site-install">
>         <echo message="Installing site ${subsite.name}."/>
>         <drush command="site-install" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
>             <option name="db-url" value="${drupal.db.url}"/>
>             <option name="site-name" value="'${subsite.name}'"/>
>             <option name="account-name" value="${drupal.admin.username}"/>
>             <option name="account-pass" value="${drupal.admin.password}"/>
>             <option name="account-mail" value="${drupal.admin.email}"/>
>             <param>${platform.profile.name}</param>
>             <param>install_configure_form.update_status_module='array(FALSE,FALSE)'</param>
>         </drush>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-create](/includes/build/build.drush.xml#L41) </td>
            <td>
                <details>
                    <summary> Create the database. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="drush-sql-create">
>         <echo message="Creating database ${drupal.db.name}."/>
>         <drush command="sql-create" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
>             <option name="db-url" value="${drupal.db.url}"/>
>         </drush>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-drop](/includes/build/build.drush.xml#L65) </td>
            <td>
                <details>
                    <summary> Drop the database. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="drush-sql-drop">
>         <echo message="Dropping database ${drupal.db.name}."/>
>         <drush command="sql-drop" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
>             <option name="db-url" value="${drupal.db.url}"/>
>         </drush>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-dump](/includes/build/build.drush.xml#L73) </td>
            <td>
                <details>
                    <summary> Backup the database. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="drush-sql-dump">
>         <echo message="Backing up database ${drupal.db.name} to ${database-file}."/>
>         <dirname property="database-cachedir" file="${database-file}"/>
>         <mkdir dir="${database-cachedir}"/>
>         <drush command="sql-dump" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
>             <option name="result-file" value="${database-file}"/>
>         </drush>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [drush-sql-import](/includes/build/build.drush.xml#L49) </td>
            <td>
                <details>
                    <summary> Import a database. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="drush-sql-import">
>         <echo message="Importing database."/>
>         <drush command="sql-cli" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
>             <param>&lt; ${database-file}</param>
>         </drush>
>         <phingcall target="drush-registry-rebuild"/>
>         <phingcall target="drush-create-files-dirs"/>
>         <!-- Update database. -->
>         <drush command="updatedb" assume="yes" root="${platform.build.dir}" bin="${drush.bin}"/>
>         <!-- Clear Caches. -->
>         <drush command="cc" assume="yes" root="${platform.build.dir}" bin="${drush.bin}">
>             <param>all</param>
>         </drush>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-composer-install](/includes/build/build.platform.xml#L5) </td>
            <td>
                <details>
                    <summary> Install Composer dependencies for the build system. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="platform-composer-install">
>         <echo msg="Run 'composer install' in platform root."/>
>         <composer command="install" composer="${composer.bin}">
>             <arg value="--working-dir=${project.basedir}"/>
>             <arg value="--no-interaction"/>
>             <arg value="--no-suggest"/>
>             <arg value="--ansi"/>
>         </composer>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-delete](/includes/build/build.platform.xml#L16) </td>
            <td>
                <details>
                    <summary> Delete the previous development build. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="platform-delete">
>         <echo msg="Delete previous build."/>
>         <phingcall target="unprotect-folder">
>             <property name="folder.to.unprotect" value="${platform.build.settings.dir}"/>
>         </phingcall>
>         <echo msg="Unprotecting folder."/>
>         <phingcall target="delete-folder">
>             <property name="folder.to.delete" value="${platform.build.dir}"/>
>         </phingcall>
>         <echo msg="Deleting folder."/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-download](/includes/build/build.platform.xml#L29) </td>
            <td>
                <details>
                    <summary> Download the platform. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="platform-download">
>         <if>
>             <available file="${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz" type="file"/>
>             <then>
>                   <echo msg="Package platform-dev-${platform.package.reference}.tar.gz already downloaded."/>
>             </then>
>             <else>
>                 <!-- Create the destination directory if it doesn't exist. -->
>                 <mkdir dir="${platform.package.cachedir}"/>
>                 <echo msg="Starting platform download. Depending on your connection this can take between 5-15 minutes. Go get some coffee."/>
>                 <if>
>                     <http url="https://github.com/ec-europa/platform-dev/releases/download/${platform.package.reference}/platform-dev-${platform.package.reference}.tar.gz"/>
>                     <then>
>                         <exec command="curl -L -o ${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz https://github.com/ec-europa/platform-dev/releases/download/${platform.package.reference}/platform-dev-${platform.package.reference}.tar.gz" passthru="true"/>
>                         <echo msg="Downloaded platform package reference ${platform.package.reference}"/>
>                     </then>
>                     <else>
>                         <fail msg="Failed downloading platform package reference ${platform.package.reference}"/>
>                     </else>
>                 </if>
>             </else>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-link-resources](/includes/build/build.platform.xml#L54) </td>
            <td>
                <details>
                    <summary> Symlink the source folders for easy development. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="platform-link-resources">
>         <rel-sym link="${platform.build.subsite.modules.custom.dir}" target="${subsite.resources.modules.dir}"/>
>         <rel-sym link="${platform.build.subsite.modules.features.dir}" target="${subsite.resources.features.dir}"/>
>         <delete dir="${platform.build.subsite.themes.dir}" includeemptydirs="true" failonerror="false"/>
>         <rel-sym link="${platform.build.subsite.themes.dir}" target="${subsite.resources.themes.dir}"/>
>         <rel-sym link="${platform.build.subsite.source.dir}" target="${subsite.resources.source.dir}"/>
>         <rel-sym link="${platform.build.subsite.composer.json}" target="${subsite.resources.composer.json}"/>
>         <rel-sym link="${platform.build.subsite.composer.lock}" target="${subsite.resources.composer.lock}"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-make](/includes/build/build.platform.xml#L65) </td>
            <td>
                <details>
                    <summary> Make the development version of the subsite. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="platform-make" depends="platform-unpack">
>         <if>
>             <available file="${subsite.make}" type="file"/>
>             <then>
>                 <echo msg="Make the subsite."/>
>                 <phingcall target="drush-make-no-core">
>                     <property name="drush.make.target.file" value="${subsite.make}"/>
>                     <property name="drush.make.root" value="${platform.build.dir}"/>
>                 </phingcall>
>             </then>
>             <else>
>                 <echo msg="No make file found. Skipping..."/>
>             </else>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-unpack](/includes/build/build.platform.xml#L82) </td>
            <td>
                <details>
                    <summary> Unpack the platform. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="platform-unpack" depends="platform-download">
>         <!-- Use the faster native commands on UNIX systems. -->
>         <if>
>             <os family="unix"/>
>             <then>
>                 <echo msg="mkdir &quot;${platform.build.dir}&quot;"/>
>                 <exec command="mkdir &quot;${platform.build.dir}&quot;" dir="${project.basedir}" passthru="true"/>
>                 <echo msg="tar xzf &quot;${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz&quot; -C &quot;${platform.build.dir}&quot;"/>
>                 <exec command="tar xzf &quot;${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz&quot; -C &quot;${platform.build.dir}&quot;" dir="${project.basedir}" passthru="true" checkreturn="true"/>
>             </then>
>             <else>
>                 <untar file="${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz" todir="${platform.build.dir}"/>
>             </else>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [platform-update-htaccess](/includes/build/build.platform.xml#L108) </td>
            <td>
                <details>
                    <summary> Update .htaccess. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="platform-update-htaccess">
>         <if>
>             <istrue value="${drupal.htaccess.append.text}"/>
>             <then>
>                 <echo msg="Appended text to htaccess."/>
>                 <append destfile="${drupal.htaccess.path}" text="${drupal.htaccess.append.text}"/>
>             </then>
>             <else>
>                 <echo msg="Appended no text to htaccess."/>
>             </else>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [prompt-for-credentials-and-retry](/includes/build/build.clone.xml#L81) </td>
            <td>
                <details>
                    <summary> Simple prompt for user credentials and recurse into subsite-database-wget. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="prompt-for-credentials-and-retry" hidden="true">
>         <input propertyName="project.database.url.htaccess.username" message="Please enter your username."/>
>         <input hidden="true" propertyName="project.database.url.htaccess.password" message="Please enter your password."/>
>         <phingcall target="subsite-database-wget"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-build-documentation-index](/includes/build/build.starterkit.xml#L60) </td>
            <td>
                <details>
                    <summary> Build documentation index. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="starterkit-build-documentation-index">
>         <build-documentation-index/>        
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-copy-templates](/includes/build/build.starterkit.xml#L11) </td>
            <td>
                <details>
                    <summary> Ensure needed files are present. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="starterkit-copy-templates">
>         <echo msg="Ensuring the presence of build.xml and Jenkinsfile."/>
>         <copy todir="${project.basedir}">
>             <fileset dir="${subsite.starterkit.templates}"/>
>         </copy>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-link-binary](/includes/build/build.starterkit.xml#L5) </td>
            <td>
                <details>
                    <summary> Provide handy access with root symlink to starterkit binary. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="starterkit-link-binary">
>         <echo msg="Provide project with starterkit binary at root level."/>
>         <rel-sym link="${project.basedir}/ssk" target="${subsite.starterkit.bin}" overwrite="true"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [starterkit-upgrade](/includes/build/build.starterkit.xml#L19) </td>
            <td>
                <details>
                    <summary> Upgrade subsite-starterkit 2.x to 3.x. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="starterkit-upgrade">
> 
>         <!-- Delete starterkit folders. -->
>         <phingcall target="delete-folder">
>             <property name="folder.to.delete" value="${project.basedir}/bin"/>
>         </phingcall>
>         <phingcall target="delete-folder">
>             <property name="folder.to.delete" value="${project.basedir}/docs"/>
>         </phingcall>
>         <phingcall target="delete-folder">
>             <property name="folder.to.delete" value="${project.basedir}/src"/>
>         </phingcall>
>         <phingcall target="delete-folder">
>             <property name="folder.to.delete" value="${subsite.resources.dir}/cloudformation"/>
>         </phingcall>
>         <phingcall target="delete-folder">
>             <property name="folder.to.delete" value="${subsite.resources.dir}/codedeploy"/>
>         </phingcall>
>         <phingcall target="delete-folder">
>             <property name="folder.to.delete" value="${subsite.resources.dir}/composer"/>
>         </phingcall>
>         <!-- Delete starterkit files. -->
>         <delete>
>             <fileset dir="${project.basedir}">
>                 <include name="CHANGELOG.md"/>
>                 <include name="LICENSE.md"/>
>                 <include name="README.md"/>
>                 <include name="appspec.yml"/>
>                 <include name="build.clone.xml"/>
>                 <include name="build.package.xml"/>
>                 <include name="build.properties.dist"/>
>                 <include name="build.test.xml"/>
>                 <include name="composer.lock"/>
>                 <include name="phpcs-ruleset.xml"/>
>             </fileset>
>         </delete>
>         <!-- Move subsite files to new location. -->
>         <move file="${subsite.resources.dir}/phpcs-custom.xml" tofile="phpcs-ruleset.xml" overwrite="true"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-composer-install](/includes/build/build.subsite.xml#L5) </td>
            <td>
                <details>
                    <summary> Install Composer dev dependencies for the subsite. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="subsite-composer-install">
>         <echo msg="Run 'composer install' in the subsite folder for development purposes."/>
>         <composer command="install" composer="${composer.bin}">
>             <arg value="--working-dir=${platform.build.subsite.dir}"/>
>             <arg value="--no-interaction"/>
>             <!-- <arg value="no-plugins" /> -->
>             <arg value="--no-suggest"/>
>             <arg value="--ansi"/>
>         </composer>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-database-download](/includes/build/build.clone.xml#L17) </td>
            <td>
                <details>
                    <summary> Download the production database. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="subsite-database-download">
>         <echo msg="Download the production database."/>
>         <!--Strips gz suffix. -->
>         <php expression="substr('${project.database.filename}', 0, -3)" returnProperty="gunzipped.filename" level="debug"/>
>         <if>
>             <not>
>                 <!-- Check if we have a previously downloaded dump available. -->
>                 <available file="tmp/${gunzipped.filename}" type="file" property="gunzipped.project.db"/>
>             </not>
>             <then>
>                 <!-- If not available, download and unzip the file. -->
>                 <phingcall target="subsite-database-wget"/>
>                 <exec command="gunzip tmp/${project.database.filename}" checkreturn="true" passthru="false" logoutput="true"/>
>             </then>
>             <else>
>                 <!-- Inform user if file was already downloaded. -->
>                 <echo msg="File ${gunzipped.filename} already downloaded."/>
>                 <echo msg="Proceeding to import."/>
>             </else>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-database-import](/includes/build/build.clone.xml#L5) </td>
            <td>
                <details>
                    <summary> Import production database. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="subsite-database-import" depends="subsite-database-download">
>         <echo msg="Import production database."/>
>         <!-- Drop database, create if necessary and import the dump. -->
>         <phingcall target="drush-sql-drop"/>
>         <phingcall target="drush-sql-create"/>
>         <phingcall target="drush-sql-import">
>             <property name="database-file" value="tmp/${gunzipped.filename}"/>
>         </phingcall>
>         <phingcall target="drush-registry-rebuild"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-database-wget](/includes/build/build.clone.xml#L40) </td>
            <td>
                <details>
                    <summary> Target to actually fetch the database dump. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="subsite-database-wget">
>         <!--Generate .htaccess credential property if needed, empty if not. -->
>         <if>
>             <or>
>                 <equals arg1="${project.database.url.htaccess.username}" arg2=""/>
>                 <equals arg1="${project.database.url.htaccess.password}" arg2=""/>
>             </or>
>             <then>
>                 <!-- If username or password is not provided, empty the credential string. -->
>                 <property name="project.database.url.credentials" value="" override="true"/>
>             </then>
>             <else>
>                 <!-- If username or password is provided, build the credential string. -->
>                 <property name="project.database.url.credentials" value="${project.database.url.htaccess.username}:${project.database.url.htaccess.password}@" override="true"/>
>             </else>
>         </if>
>         <!-- Attempt to download the database dump. -->
>         <exec command="wget ${project.database.url.scheme}://${project.database.url.credentials}${project.database.url}${project.database.filename}" dir="tmp" checkreturn="false" passthru="false" outputProperty="project.database.download"/>
>         <if>
>             <!-- Upon success inform the user. -->
>             <contains string="${project.database.download}" substring="200"/>
>             <then>
>                 <echo msg="Database successfully downloaded."/>
>             </then>
>             <!-- When denied access, prompt the user for credentials and retry the download. -->
>             <elseif>
>                 <contains string="${project.database.download}" substring="401"/>
>                 <then>
>                     <phingcall target="prompt-for-credentials-and-retry"/>
>                 </then>
>             </elseif>
>             <!-- Otherwise we fail the build and display the download message. -->
>             <else>
>                 <echo msg="Failed to download the database dump. Result of wget:" level="error"/>
>                 <echo msg="${project.database.download}" level="error"/>
>                 <fail/>
>             </else>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-development-download](/includes/build/build.subsite.xml#L36) </td>
            <td>
                <details>
                    <summary> Download development modules. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="subsite-modules-development-download" depends="subsite-modules-development-makefile">
>         <echo msg="Download development modules."/>
>         <phingcall target="drush-make-no-core">
>             <property name="drush.make.target.file" value="${subsite.temporary.development.make}"/>
>             <property name="drush.make.root" value="${platform.build.dir}"/>
>         </phingcall>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-development-enable](/includes/build/build.test.xml#L71) </td>
            <td>
                <details>
                    <summary> Enable development modules. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="subsite-modules-development-enable">
>         <phingcall target="drush-enable-modules">
>             <property name="drupal.modules" value="${development.modules.enable}"/>
>         </phingcall>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-development-makefile](/includes/build/build.subsite.xml#L18) </td>
            <td>
                <details>
                    <summary> Generate the makefile used to download development modules. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="subsite-modules-development-makefile">
>         <echo msg="Generate the makefile for development modules."/>
>         <if>
>             <available file="${subsite.temporary.development.make}" type="file" property="development.makefile.available"/>
>             <then>
>                 <echo message="Deleting existing makefile."/>
>                 <delete file="${subsite.temporary.development.make}" failonerror="false"/>
>             </then>
>         </if>
>         <drushmakefile makeFile="${subsite.temporary.development.make}" coreVersion="${drupal.core.version}" projects="${development.modules.download}" defaultProjectDir="${development.modules.location}"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-modules-install-enable](/includes/build/build.test.xml#L64) </td>
            <td>
                <details>
                    <summary> Enable required modules after installation of the profile. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="subsite-modules-install-enable">
>         <phingcall target="drush-enable-modules">
>             <property name="drupal.modules" value="${subsite.install.modules}"/>
>         </phingcall>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-setup-files-directory](/includes/build/build.subsite.xml#L222) </td>
            <td>
                <details>
                    <summary> Setup file directory </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="subsite-setup-files-directory">
>         <if>
>             <istrue value="${platform.build.files.dir}"/>
>             <then>
>                 <mkdir dir="${platform.build.files.dir}/private_files"/>
>                 <mkdir dir="${platform.build.tmp.dir}"/>
>                 <!-- Support CSS and JS injector. -->
>                 <mkdir dir="${platform.build.files.dir}/css_injector"/>
>                 <mkdir dir="${platform.build.files.dir}/js_injector"/>
>             </then>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-backup](/includes/build/build.subsite.xml#L45) </td>
            <td>
                <details>
                    <summary> Backs up files and folders listed in platform.rebuild properties in order to rebuild. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="subsite-site-backup">
> 
>         <!-- Check if site exists. -->
>         <if>
>             <available file="${platform.build.settings.dir}/settings.php" type="file"/>
>             <then>
>                 <property name="site-detected" value="1"/>
>             </then>
>             <else>
>                 <echo msg="No site installation detected. Skipping backup."/>
>             </else>
>         </if>
> 
>         <if>
>             <and>
>                 <equals arg1="${platform.rebuild.auto}" arg2="0"/>
>                 <equals arg1="${site-detected}" arg2="1"/>
>             </and>
>             <then>
>                 <echo msg="Installed site detected." level="warning"/>
>                 <propertyprompt propertyName="subsite-site-backup-activated" promptText="Do you wish to backup site for this build? (y/n)"/>
>                 <if>
>                     <equals arg1="${subsite-site-backup-activated}" arg2="y"/>
>                     <then>
>                         <property name="platform.rebuild.auto" value="1" override="true"/>
>                     </then>
>                 </if>
>             </then>
>         </if>
>         <if>
>             <and>
>                 <equals arg1="${platform.rebuild.auto}" arg2="1"/>
>                 <equals arg1="${site-detected}" arg2="1"/>
>             </and>
>             <then>
>                 <if>
>                     <!-- Delete any remains of previous backup attempts. -->
>                     <available file="${platform.rebuild.backup.destination}" type="dir"/>
>                     <then>
>                         <delete dir="${platform.rebuild.backup.destination}" includeemptydirs="true"/>
>                     </then>
>                 </if>
>                 <!-- Create backup directory. -->
>                 <mkdir dir="${platform.rebuild.backup.destination}"/>
>                 <!-- Make the settings directory writable because we can not delete it otherwise -->
>                 <phingcall target="unprotect-folder">
>                     <property name="folder.to.unprotect" value="${platform.build.settings.dir}"/>
>                 </phingcall>
>                 <!-- Back up folders list. -->
>                 <foreach list="${platform.rebuild.backup.folders}" param="site-item" target="subsite-site-backup-item" delimiter=";">
>                     <property name="site-item-type" value="dir"/>
>                 </foreach>
>                 <!-- Back up files list. -->
>                 <foreach list="${platform.rebuild.backup.files}" param="site-item" target="subsite-site-backup-item" delimiter=";">
>                     <property name="site-item-type" value="file"/>
>                 </foreach>
>             </then>
>         </if>
>         <if>
>             <equals arg1="${subsite-site-backup-activated}" arg2="y"/>
>             <then>
>                 <property name="platform.rebuild.auto" value="0" override="true"/>
>             </then>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-backup-item](/includes/build/build.subsite.xml#L162) </td>
            <td>
                <details>
                    <summary> Backs up a site item from the platform that will be removed in order to rebuild. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="subsite-site-backup-item" hidden="true">
>         <php expression="dirname(&quot;${site-item}&quot;)" returnProperty="site-item-dir"/>
>         <property name="site-item-backup-dir" value="${site-item-dir}">
>             <filterchain>
>                 <replaceregexp>
>                     <regexp pattern="${platform.build.dir}" replace="${platform.rebuild.backup.destination}" ignoreCase="false"/>
>                 </replaceregexp>
>             </filterchain>
>         </property>
>         <if>
>             <available file="${site-item}" type="${site-item-type}"/>
>             <then>
>                 <if>
>                     <not>
>                         <available file="${site-item-backup-dir}" type="dir"/>
>                     </not>
>                     <then>
>                         <mkdir dir="${site-item-backup-dir}"/>
>                     </then>
>                 </if>
>                 <move file="${site-item}" todir="${site-item-backup-dir}" includeemptydirs="true"/>
>             </then>
>             <else>
>                 <php expression="ucwords(&quot;${site-item-type}&quot;)" returnProperty="site-item-type-capitalized"/>
>                 <echo msg="Skipping ${site-item}. ${site-item-type-capitalized} not found." level="warning"/>
>             </else>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-restore](/includes/build/build.subsite.xml#L112) </td>
            <td>
                <details>
                    <summary> Restoring sites directory if backed up before rebuild-dev. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="subsite-site-restore">
> 
>         <!-- Check if backup exists. -->
>         <if>
>             <available file="${platform.rebuild.backup.destination}" type="dir"/>
>             <then>
>                 <property name="backup-detected" value="1"/>
>             </then>
>             <else>
>                 <echo msg="No site backup detected. Skipping restore."/>
>             </else>
>         </if>
>         <if>
>             <and>
>                 <equals arg1="${platform.rebuild.auto}" arg2="0"/>
>                 <equals arg1="${backup-detected}" arg2="1"/>
>             </and>
>             <then>
>                 <echo msg="Site backup detected." level="warning"/>
>                 <propertyprompt propertyName="subsite-site-restore-activated" promptText="Do you wish to restore site for this build? (y/n)"/>
>                 <if>
>                     <equals arg1="${subsite-site-restore-activated}" arg2="y"/>
>                     <then>
>                         <property name="platform.rebuild.auto" value="1" override="true"/>
>                     </then>
>                 </if>
>             </then>
>         </if>
>         <if>
>             <and>
>                 <equals arg1="${platform.rebuild.auto}" arg2="1"/>
>                 <equals arg1="${backup-detected}" arg2="1"/>
>             </and>
>             <then>
>                 <echo msg="Restoring site files and folders from ${platform.rebuild.backup.destination}"/>
>                 <!-- Restore folders list. -->
>                 <foreach list="${platform.rebuild.backup.folders}" param="site-item" target="subsite-site-restore-item" delimiter=";">
>                     <property name="site-item-type" value="dir"/>
>                 </foreach>
>                 <!-- Restore files list. -->
>                 <foreach list="${platform.rebuild.backup.files}" param="site-item" target="subsite-site-restore-item" delimiter=";">
>                     <property name="site-item-type" value="file"/>
>                 </foreach>
>                 <!-- Delete the site backup directory. -->
>                 <delete dir="${platform.rebuild.backup.destination}" includeemptydirs="true"/>
>             </then>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [subsite-site-restore-item](/includes/build/build.subsite.xml#L192) </td>
            <td>
                <details>
                    <summary> Restores a site item from the platform.rebuild.backup.destination to the new build. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="subsite-site-restore-item" hidden="true">
>         <property name="site-item-backup" value="${site-item}">
>             <filterchain>
>                 <replaceregexp>
>                     <regexp pattern="${platform.build.dir}" replace="${platform.rebuild.backup.destination}" ignoreCase="false"/>
>                 </replaceregexp>
>             </filterchain>
>         </property>
>         <if>
>             <available file="${site-item-backup}" type="${site-item-type}"/>
>             <then>
>                 <php expression="dirname(&quot;${site-item}&quot;)" returnProperty="site-item-dir"/>
>                 <if>
>                     <not>
>                         <available file="${site-item-dir}" type="dir"/>
>                     </not>
>                     <then>
>                         <mkdir dir="${site-item-dir}"/>
>                     </then>
>                 </if>
>                 <move file="${site-item-backup}" todir="${site-item-dir}" includeemptydirs="true"/>
>             </then>
>             <else>
>                 <php expression="ucwords(&quot;${site-item-type}&quot;)" returnProperty="site-item-type-capitalized"/>
>                 <echo msg="Skipping ${site-item}. ${site-item-type-capitalized} not found." level="warning"/>
>             </else>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-behat-setup](/includes/build/build.test.xml#L127) </td>
            <td>
                <details>
                    <summary> Set up Behat. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="test-behat-setup">
>         <if>
>             <available file="${behat.yml.path}" type="file" property="behat.yml.available"/>
>             <then>
>                 <echo message="Deleting existing behat.yml configuration file"/>
>                 <delete file="${behat.yml.path}" failonerror="false"/>
>             </then>
>         </if>
>         <echo message="Creating behat.yml configuration file"/>
>         <loadfile property="behat.yml.content" file="${behat.yml.template}">
>             <filterchain>
>                 <replacetokens>
>                     <token key="project.code.dir" value="${project.code.dir}"/>
>                     <token key="drupal.site.dir" value="${drupal.site.dir}"/>
>                     <token key="behat.base_url" value="${behat.base_url}"/>
>                     <token key="behat.formatter.name" value="${behat.formatter.name}"/>
>                 </replacetokens>
>             </filterchain>
>         </loadfile>
>         <echo message="${behat.yml.content}" file="${behat.yml.path}"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-behat-setup-link](/includes/build/build.package.xml#L21) </td>
            <td>
                <details>
                    <summary> Symlink the Behat bin and test directory in the subsite folder. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="test-behat-setup-link">
>         <echo msg="Symlink the Behat bin and test directory in './sites/all'."/>
>         <rel-sym link="${project.basedir}/ssk/behat" target="${subsite.starterkit.vendor}/bin/behat" overwrite="true"/>
>         <rel-sym link="${platform.build.subsite.dir}/tests" target="${project.basedir}/tests" overwrite="true"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-phpcs-setup](/includes/build/build.test.xml#L78) </td>
            <td>
                <details>
                    <summary> Set up PHP CodeSniffer. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="test-phpcs-setup">
>         <if>
>             <available file="${phpcs.config}" type="file" property="phpcs.config.available"/>
>             <then>
>                 <echo message="Deleting existing PHP Codesniffer default configuration file."/>
>                 <delete file="${phpcs.config}" failonerror="false"/>
>             </then>
>         </if>
>         <if>
>             <available file="${phpcs.global.config}" type="file" property="phpcs.global.config.available"/>
>             <then>
>                 <echo message="Deleting existing PHP Codesniffer global configuration file."/>
>                 <delete file="${phpcs.global.config}" failonerror="false"/>
>             </then>
>         </if>
>         <phpcodesnifferconfiguration configFile="${phpcs.config}" extensions="${phpcs.extensions}" files="${phpcs.files}" globalConfig="${phpcs.global.config}" ignorePatterns="${phpcs.ignore}" passWarnings="${phpcs.passwarnings}" report="${phpcs.report}" showProgress="${phpcs.progress}" showSniffCodes="${phpcs.sniffcodes}" standards="${phpcs.standards}"/>
> 
>         <!-- Set up the git pre-push hook. -->
>         <phingcall target="test-phpcs-setup-prepush"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-phpcs-setup-prepush](/includes/build/build.test.xml#L111) </td>
            <td>
                <details>
                    <summary> Setup the PHP CodeSniffer pre-push hook. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="test-phpcs-setup-prepush">
>         <if>
>             <equals arg1="${phpcs.prepush.enable}" arg2="1"/>
>             <then>
>                 <echo message="Enabling git pre-push hook."/>
>                 <mkdir dir="${project.basedir}/resources/git/hooks/pre-push"/>
>                 <rel-sym link="${phpcs.prepush.destination}" target="${phpcs.prepush.source}" overwrite="true"/>
>             </then>
>            <else>
>                 <echo message="Disabling git pre-push hook."/>
>                 <delete file="${phpcs.prepush.destination}" failonerror="false" quiet="true"/>
>           </else>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-quality-assurance](/includes/build/build.test.xml#L161) </td>
            <td>
                <details>
                    <summary> Do quality assurance checks. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="test-quality-assurance">
>         <exec command="${subsite.starterkit.bin}/qa review:full --no-interaction --ansi" passthru="true" checkreturn="true"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [test-run-php-codesniffer](/includes/build/build.test.xml#L170) </td>
            <td>
                <details>
                    <summary> Do quality assurance checks. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="test-run-php-codesniffer">
>         <exec command="${subsite.starterkit.bin}/phpcs" passthru="true" checkreturn="true"/>
>     </target>
> ```

                </details>
            </td>
        </tr>
        <tr align="left" valign="top">
            <td> [unprotect-folder](/includes/build/build.helpers.xml#L32) </td>
            <td>
                <details>
                    <summary> Make the given folder writeable. </summary>

> ```xml 
> <?xml version="1.0"?>
> <target name="unprotect-folder" hidden="true">
>         <!-- This should only be used on folders that need to be removed. -->
>         <if>
>             <available file="${folder.to.unprotect}" type="dir"/>
>             <then>
>                 <chmod mode="0777" failonerror="true" verbose="false" quiet="true">
>                     <fileset dir="${folder.to.unprotect}"/>
>                 </chmod>
>             </then>
>         </if>
>     </target>
> ```

                </details>
            </td>
        </tr>
    </tbody>
</table>