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
> <target name="build-clean" description="Build local version of subsite with a clean install." depends="drush-create-files-dirs, install, subsite-modules-development-enable"></target>
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
> <target name="build-clone" description="Build local version of subsite with production data." depends="subsite-database-download, drush-regenerate-settings, subsite-database-import, subsite-modules-development-enable"></target>
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
> <target name="build-code" description="Build local version of subsite without install." depends="             subsite-site-backup,             platform-delete,             platform-make,             platform-link-resources,             subsite-composer-install,             test-behat-setup-link,             test-behat-setup,             platform-update-htaccess,             test-phpcs-setup,             subsite-modules-development-download,             subsite-site-restore"></target>
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
> <target name="build-keep" description="Build local version of subsite with backup and restore.">
        <!-- Execute build-dev with automatic rebuild enabled. -->
        <phingcall target="build-dev">
            <property name="platform.rebuild.auto" value="1" override="true"></property>
        </phingcall>
    </target>
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
> <target name="build-release" description="Build subsite source code release package." depends="build-dist">
        <mkdir dir="${project.release.path}"></mkdir>
        <exec command="tar -czf ${project.release.path}/${project.release.name}.tar.gz ${phing.subsite.build.dir}"></exec>
    </target>
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
> <target name="build-tests" description="Build subsite tests code release package.">
        <mkdir dir="${project.release.path}"></mkdir>
    </target>
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
> <target name="docker-compose-down" description="Trash docker project.">
        <echo msg="Removing containers and volumes for ${docker.project.id}"></echo>
        <exec command="docker-compose -p ${docker.project.id} -f ${subsite.starterkit.root}/resources/docker/docker-compose.yml down --volumes"></exec>
        <delete file="${project.basedir}/ssk-${docker.project.id}"></delete>
    </target>
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
> <target name="docker-compose-stop" description="Stop docker project.">
        <echo msg="Stopping containers for ${docker.project.id}"></echo>
        <exec command="docker-compose -p ${docker.project.id} -f ${subsite.starterkit.root}/resources/docker/docker-compose.yml stop"></exec>
        <exec command="${project.basedir}/ssk-${docker.project.id} ps" passthru="true"></exec>
    </target>
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
> <target name="docker-compose-up" description="Start docker project.">
        <echo msg="Starting containers for ${docker.project.id}"></echo>
        <mkdir dir="${platform.build.dir}"></mkdir> 
        <mkdir dir="${share.platform.path}/databases/platform-dev-${platform.package.reference}"></mkdir>
        <exec command="DB_LOCATION_DIR=${share.platform.path}/databases/platform-dev-${platform.package.reference} docker-compose -p ${docker.project.id} -f ${subsite.starterkit.root}/resources/docker/docker-compose.yml up -d --no-recreate"></exec>
        <rel-sym link="${project.basedir}/ssk-${docker.project.id}" target="${subsite.starterkit.root}/resources/docker/dbash" overwrite="true"></rel-sym>
        <exec command="${project.basedir}/ssk-${docker.project.id} ps" passthru="true"></exec>
    </target>
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
> <target name="install" description="Install the subsite.">
        <!--
            Ensure the settings folder is writable so the installer can create
            the settings.php file.
         -->
        <chmod mode="0775" failonerror="false" verbose="false" quiet="true">
            <fileset dir="${platform.build.settings.dir}"></fileset>
        </chmod>

        <if>
            <and>
                <equals arg1="${platform.package.database}" arg2="1"></equals>
                <available file="${share.platform.path}/databases/platform-dev-${platform.package.reference}/platform-dev-${platform.package.reference}.sql" type="file"></available>
            </and>
            <then>
                <phingcall target="drush-regenerate-settings"></phingcall>
                <exec command="${drush.bin} --root=${platform.build.dir} status bootstrap | grep -q Successful" returnProperty="drush-status-bootstrap"></exec>
                <if>
                    <not>
                        <equals arg1="${drush-status-bootstrap}" arg2="0"></equals>
                    </not>
                    <then>
                        <phingcall target="drush-sql-create"></phingcall>
                        <phingcall target="drush-sql-import">
                            <property name="database-file" value="${share.platform.path}/databases/platform-dev-${platform.package.reference}/platform-dev-${platform.package.reference}.sql"></property>
                        </phingcall>
                    </then>
                </if> 
            </then>
            <else>
                <!-- Install site with drush. -->
                <phingcall target="drush-site-install"></phingcall>
                <!-- Backup vanilla database. -->
                <if>
                    <equals arg1="${platform.package.database}" arg2="1"></equals>
                    <then>
                        <phingcall target="drush-sql-dump">
                            <property name="database-file" value="${share.platform.path}/databases/platform-dev-${platform.package.reference}/platform-dev-${platform.package.reference}.sql"></property>
                        </phingcall>
                    </then>
                </if>
            </else>
        </if>

        <!-- Enable solr if needed. -->
        <phingcall target="drush-enable-solr"></phingcall>

        <!--
            Subsites are not allowed to use their own installation profile for
            historical reasons. The functionality is contained in one of more
            features and modules which need to be enabled after installation.
        -->
        <phingcall target="subsite-modules-install-enable"></phingcall>

        <!-- Rebuild node access after Subsites modules activation -->
        <phingcall target="drush-rebuild-node-access"></phingcall>
    </target>
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
> <target name="link-docroot" description="Create symlink from build to docroot.">
        <rel-sym link="${server.docroot}" target="${platform.build.dir}" overwrite="true"></rel-sym>
    </target>
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
> <target name="test-run-behat" description="Refresh configuration and run behat tests.">
        <behat executable="${behat.bin}" config="${behat.yml.path}" strict="${behat.options.strict}" verbose="${behat.options.verbosity}" passthru="${behat.options.passthru}"></behat>
    </target>
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
> <target name="test-run-phpcs" description="Refresh configuration and run phpcs review." depends="test-phpcs-setup, test-run-php-codesniffer"></target>
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
> <target name="test-run-qa" description="Refresh configuration and run qa review." depends="test-phpcs-setup, test-quality-assurance"></target>
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
> <target hidden="true" name="build-dev">
        <replaced target="build-code"></replaced>
    </target>
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
> <target name="build-dist" hidden="true" depends="             dist-delete,             dist-make,             dist-copy-resources,             dist-composer-install"></target>
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
> <target name="check-for-default-settings-or-rebuild" hidden="true">
        <if>
            <not>
                <available file="${platform.build.settings.dir}/default.settings.php" type="file" property="platform.build.settings.dir.default.settings"></available>
            </not>
            <then>
                <!-- If we can not find default settings in the build settings folder, prompt to ask user to rebuild. -->
                <echo msg="No default settings detected at ${platform.build.settings.dir}/default.settings.php." level="warning"></echo>
                <propertyprompt propertyName="platform-rebuild" defaultValue="no" promptText="Do you wish to rebuild? (y/n)"></propertyprompt>
                <if>
                    <equals arg1="${platform-rebuild}" arg2="y"></equals>
                    <then>
                        <phingcall target="build-dev"></phingcall>
                    </then>
                    <else>
                        <!-- If user chooses not to rebuild we have no other choice to fail the build. -->
                        <echo msg="Can not re-generate settings, canceling clone task." level="error"></echo>
                        <fail></fail>
                    </else>
                </if>
            </then>
            <else>
                <!-- If we have found the default settings inform the user we will proceed with generation. -->
                <echo msg="Default settings found at ${platform.build.settings.dir}/default.settings.php."></echo>
                <echo msg="Proceeding with re-generation of the settings.php."></echo>
            </else>
        </if>
    </target>
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
> <target name="composer-echo-hook-phingcalls" hidden="true">
        <echoproperties prefix="composer.hook."></echoproperties>
    </target>
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
> <target name="copy-folder" hidden="true">
        <copy todir="${copy.destination.path}" haltonerror="${copy.path.haltonerror}">
            <fileset dir="${copy.source.path}" defaultexcludes="false"></fileset>
        </copy>
    </target>
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
> <target name="create-tmp-dirs" hidden="true">
        <if>
            <!-- Create the global cache directory if it doesn't exist. -->
            <not>
                <available file="${platform.package.cachedir}" type="dir"></available>
            </not>
            <then>
                <mkdir dir="${platform.package.cachedir}"></mkdir>
            </then>
            <else>
                <echo msg="Directory ${platform.package.cachedir} exists."></echo>
            </else>
        </if>
        <if>
            <!-- Create the destination directory if it doesn't exist. -->
            <not>
                <available file="${platform.package.destination}" type="dir"></available>
            </not>
            <then>
                <mkdir dir="${platform.package.destination}"></mkdir>
            </then>
            <else>
                <echo msg="Directory ${platform.package.destination} exists."></echo>
            </else>
        </if>
    </target>
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
> <target name="delete-folder" hidden="true">
        <!-- Use the faster native command on UNIX systems. -->
        <if>
            <os family="unix"></os>
            <then>
                <echo msg='rm -rf "${folder.to.delete}"'></echo>
                <exec command='rm -rf "${folder.to.delete}"' dir="${project.basedir}" passthru="true" checkreturn="true"></exec>
            </then>
            <else>
                <delete dir="${folder.to.delete}" includeemptydirs="true" failonerror="false"></delete>
            </else>
        </if>
    </target>
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
> <target name="dist-composer-install">
        <echo msg="Run 'composer install --no-dev' in the build destination folder."></echo>
        <composer command="install" composer="${composer.bin}">
            <arg value="--working-dir=${dist.build.dir}"></arg>
            <arg value="--no-interaction"></arg>
            <arg value="--no-plugins"></arg>
            <arg value="--no-suggest"></arg>
            <arg value="--no-dev"></arg>
            <arg value="--ansi"></arg>
        </composer>
    </target>
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
> <target name="dist-copy-resources">
        <echo msg="Copy custom resources."></echo>
        <!-- Copy our custom modules. -->
        <phingcall target="copy-folder">
            <property name="copy.source.path" value="${subsite.resources.modules.dir}"></property>
            <property name="copy.destination.path" value="${dist.build.modules.custom.dir}"></property>
            <property name="copy.path.haltonerror" value="false" override="true"></property>
        </phingcall>
        <!-- Copy our custom features. -->
        <phingcall target="copy-folder">
            <property name="copy.source.path" value="${subsite.resources.features.dir}"></property>
            <property name="copy.destination.path" value="${dist.build.modules.features.dir}"></property>
            <property name="copy.path.haltonerror" value="false" override="true"></property>
        </phingcall>
        <!-- Copy our custom themes. -->
        <phingcall target="copy-folder">
            <property name="copy.source.path" value="${subsite.resources.themes.dir}"></property>
            <property name="copy.destination.path" value="${dist.build.themes.dir}"></property>
            <property name="copy.path.haltonerror" value="false" override="true"></property>
        </phingcall>
        <!-- Copy our custom PSR-4 code. -->
        <phingcall target="copy-folder">
            <property name="copy.source.path" value="${subsite.resources.source.dir}"></property>
            <property name="copy.destination.path" value="${dist.build.source.dir}"></property>
            <property name="copy.path.haltonerror" value="false" override="true"></property>
        </phingcall>
        <!-- Copy composer configuration. -->
        <copy todir="${dist.build.dir}" file="${subsite.resources.composer.json}"></copy>
        <copy todir="${dist.build.dir}" file="${subsite.resources.composer.lock}"></copy>
    </target>
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
> <target name="dist-delete">
        <echo msg="Delete previous build."></echo>
        <phingcall target="delete-folder">
            <property name="folder.to.delete" value="${dist.build.dir}"></property>
        </phingcall>
    </target>
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
> <target name="dist-make">
        <echo msg="Delete temporary build folder."></echo>
        <phingcall target="delete-folder">
            <property name="folder.to.delete" value="${phing.subsite.tmp.dir}/build"></property>
        </phingcall>

        <echo msg="Make the subsite."></echo>
        <!--
            Drush make builds the site as if it is part of a complete Drupal
            installation. The actual build is in the /sites/all subfolder. Build
            in a temporary folder and move the subsite into place when done.
         -->
        <if>
            <available file="${subsite.make}" type="file"></available>
            <then>
                <loadfile property="sitemake" file="${subsite.make}"></loadfile>
                <propertyregex property="not.empty" subject="${sitemake}" pattern="([^#; ])(libraries\[|projects\[)" match="$1" casesensitive="false" defaultvalue="empty"></propertyregex>
                <if>
                    <not><equals arg1="${not.empty}" arg2="empty"></equals></not>
                    <then>
                        <phingcall target="drush-make-no-core">
                            <property name="drush.make.target.file" value="${subsite.make}"></property>
                            <property name="drush.make.root" value="${phing.subsite.tmp.dir}/build"></property>
                        </phingcall>
                    </then>
                    <else>
                       <echo msg="Empty make file found. Skipping... ${not.empty}"></echo>
                       <mkdir dir="${phing.subsite.tmp.dir}/build/sites/all"></mkdir>
                    </else>
                </if>
            </then>
            <else>
                <echo msg="No make file found. Skipping..."></echo>
                <mkdir dir="${phing.subsite.tmp.dir}/build/sites/all"></mkdir>
            </else>
        </if>

        <!-- Move the subsite to its destination. -->
        <echo msg='mv "${phing.subsite.tmp.dir}/build/sites/all/" "${dist.build.dir}"'></echo>
        <exec command='mv "${phing.subsite.tmp.dir}/build/sites/all/" "${dist.build.dir}"' dir="${project.basedir}" passthru="true" checkreturn="true"></exec>

        <echo msg="Clean up temporary build folder."></echo>
        <phingcall target="delete-folder">
            <property name="folder.to.delete" value="${phing.subsite.tmp.dir}/build"></property>
        </phingcall>
    </target>
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
> <target name="drush-create-files-dirs">
        <echo message="Creating files directories for ${drupal.db.name}."></echo>
        <!-- Execute setttings generation script. -->
        <drush command="php-script" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
            <param>
        </drush>
    </target>
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
> <target name="drush-dl-rr">
        <echo message="Installing registry rebuild on user account."></echo>
        <exec command="${drush.bin} pm-download registry_rebuild-7 -n &gt;/dev/null"></exec>
        <exec command="${drush.bin} cc drush &gt;/dev/null"></exec>
    </target>
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
> <target name="drush-enable-modules" hidden="true">
        <drush command="pm-enable" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
            <param>
        </drush>
    </target>
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
> <target name="drush-enable-solr">
        <if>
            <equals arg1="${drupal.solr.activate}" arg2="1"></equals>
            <then>
                <echo message="Enable apachesolr for ${drupal.db.name}."></echo>
                <phingcall target="drush-enable-modules">
                    <property name="drupal.modules" value="apachesolr"></property>
                </phingcall>
                <drush command="solr-set-env-url" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
                    <param>
                </drush>
            </then>
        </if>
    </target>
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
> <target name="drush-make-no-core">
        <echo message="Running make file ${drush.make.target.file} into folder ${drush.make.root}."></echo>
        <drush command="make" assume="yes" bin="${drush.bin}" pipe="yes" verbose="${drush.verbose}" root="${drush.make.root}" color="${drush.color}">
            <param>
            <param>
            <option name="concurrency">10</option>
            <option name="no-patch-txt"></option>
            <option name="no-core"></option>
        </drush>
    </target>
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
> <target name="drush-rebuild-node-access">
        <drush command="php-eval" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
            <param>
        </drush>
    </target>
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
> <target name="drush-regenerate-settings" depends="check-for-default-settings-or-rebuild">
        <copy file="${subsite.starterkit.root}/includes/drush/generate-settings.php" tofile="tmp/generate-settings.php" overwrite="true">
            <filterchain>
                <replacetokens begintoken="%%" endtoken="%%">
                    <!-- Replace tokens in settings generation script. -->
                    <token key="drupal.db.type" value="${drupal.db.type}"></token>
                    <token key="drupal.db.name" value="${drupal.db.name}"></token>
                    <token key="drupal.db.user" value="${drupal.db.user}"></token>
                    <token key="drupal.db.password" value="${drupal.db.password}"></token>
                    <token key="drupal.db.host" value="${drupal.db.host}"></token>
                    <token key="drupal.db.port" value="${drupal.db.port}"></token>
                    <token key="error_level" value="${development.variables.error_level}"></token>
                    <token key="views_ui_show_sql_query" value="${development.variables.views_ui_show_sql_query}"></token>
                    <token key="views_ui_show_performance_statistics" value="${development.variables.views_ui_show_performance_statistics}"></token>
                    <token key="views_show_additional_queries" value="${development.variables.views_show_additional_queries}"></token>
                    <token key="stage_file_proxy_origin" value="${development.variables.stage_file_proxy_origin}"></token>
                    <token key="stage_file_proxy_origin_dir" value="${development.variables.stage_file_proxy_origin_dir}"></token>
                    <token key="stage_file_proxy_hotlink" value="${development.variables.stage_file_proxy_hotlink}"></token>
                    <token key="file_public_path" value="${platform.build.files.dir}"></token>
                    <token key="file_private_path" value="${platform.build.files.dir}/private_files"></token>
                    <token key="file_temporary_path" value="${platform.build.tmp.dir}"></token>
                </replacetokens>
            </filterchain>
        </copy>
        <!-- Execute setttings generation script. -->
        <drush command="php-script" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
            <param>
        </drush>
    </target>
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
> <target name="drush-registry-rebuild">
        <trycatch>
            <try>
                <!-- Check if registry rebuild is available. -->
                <exec command="${drush.bin} rr --help" checkreturn="true"></exec>
            </try>
            <catch>
                <!-- Download if not available. -->
                <phingcall target="drush-dl-rr"></phingcall>
            </catch>
            <finally>
                 <!-- Rebuild Registry. -->
                 <drush command="registry-rebuild" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}">
                     <param>
                 </drush>
            </finally>
        </trycatch>
    </target>
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
> <target name="drush-site-install">
        <echo message="Installing site ${subsite.name}."></echo>
        <drush command="site-install" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
            <option name="db-url" value="${drupal.db.url}"></option>
            <option name="site-name" value="'${subsite.name}'"></option>
            <option name="account-name" value="${drupal.admin.username}"></option>
            <option name="account-pass" value="${drupal.admin.password}"></option>
            <option name="account-mail" value="${drupal.admin.email}"></option>
            <param>
            <param>
        </drush>
    </target>
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
> <target name="drush-sql-create">
        <echo message="Creating database ${drupal.db.name}."></echo>
        <drush command="sql-create" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
            <option name="db-url" value="${drupal.db.url}"></option>
        </drush>
    </target>
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
> <target name="drush-sql-drop">
        <echo message="Dropping database ${drupal.db.name}."></echo>
        <drush command="sql-drop" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
            <option name="db-url" value="${drupal.db.url}"></option>
        </drush>
    </target>
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
> <target name="drush-sql-dump">
        <echo message="Backing up database ${drupal.db.name} to ${database-file}."></echo>
        <dirname property="database-cachedir" file="${database-file}"></dirname>
        <mkdir dir="${database-cachedir}"></mkdir>
        <drush command="sql-dump" assume="yes" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
            <option name="result-file" value="${database-file}"></option>
        </drush>
    </target>
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
> <target name="drush-sql-import">
        <echo message="Importing database."></echo>
        <drush command="sql-cli" root="${platform.build.dir}" bin="${drush.bin}" verbose="${drush.verbose}" color="${drush.color}">
            <param>
        </drush>
        <phingcall target="drush-registry-rebuild"></phingcall>
        <phingcall target="drush-create-files-dirs"></phingcall>
        <!-- Update database. -->
        <drush command="updatedb" assume="yes" root="${platform.build.dir}" bin="${drush.bin}"></drush>
        <!-- Clear Caches. -->
        <drush command="cc" assume="yes" root="${platform.build.dir}" bin="${drush.bin}">
            <param>
        </drush>
    </target>
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
> <target name="platform-composer-install">
        <echo msg="Run 'composer install' in platform root."></echo>
        <composer command="install" composer="${composer.bin}">
            <arg value="--working-dir=${project.basedir}"></arg>
            <arg value="--no-interaction"></arg>
            <arg value="--no-suggest"></arg>
            <arg value="--ansi"></arg>
        </composer>
    </target>
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
> <target name="platform-delete">
        <echo msg="Delete previous build."></echo>
        <phingcall target="unprotect-folder">
            <property name="folder.to.unprotect" value="${platform.build.settings.dir}"></property>
        </phingcall>
        <echo msg="Unprotecting folder."></echo>
        <phingcall target="delete-folder">
            <property name="folder.to.delete" value="${platform.build.dir}"></property>
        </phingcall>
        <echo msg="Deleting folder."></echo>
    </target>
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
> <target name="platform-download">
        <if>
            <available file="${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz" type="file"></available>
            <then>
                  <echo msg="Package platform-dev-${platform.package.reference}.tar.gz already downloaded."></echo>
            </then>
            <else>
                <!-- Create the destination directory if it doesn't exist. -->
                <mkdir dir="${platform.package.cachedir}"></mkdir>
                <echo msg="Starting platform download. Depending on your connection this can take between 5-15 minutes. Go get some coffee."></echo>
                <if>
                    <http url="https://github.com/ec-europa/platform-dev/releases/download/${platform.package.reference}/platform-dev-${platform.package.reference}.tar.gz"></http>
                    <then>
                        <exec command="curl -L -o ${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz https://github.com/ec-europa/platform-dev/releases/download/${platform.package.reference}/platform-dev-${platform.package.reference}.tar.gz" passthru="true"></exec>
                        <echo msg="Downloaded platform package reference ${platform.package.reference}"></echo>
                    </then>
                    <else>
                        <fail msg="Failed downloading platform package reference ${platform.package.reference}"></fail>
                    </else>
                </if>
            </else>
        </if>
    </target>
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
> <target name="platform-link-resources">
        <rel-sym link="${platform.build.subsite.modules.custom.dir}" target="${subsite.resources.modules.dir}"></rel-sym>
        <rel-sym link="${platform.build.subsite.modules.features.dir}" target="${subsite.resources.features.dir}"></rel-sym>
        <delete dir="${platform.build.subsite.themes.dir}" includeemptydirs="true" failonerror="false"></delete>
        <rel-sym link="${platform.build.subsite.themes.dir}" target="${subsite.resources.themes.dir}"></rel-sym>
        <rel-sym link="${platform.build.subsite.source.dir}" target="${subsite.resources.source.dir}"></rel-sym>
        <rel-sym link="${platform.build.subsite.composer.json}" target="${subsite.resources.composer.json}"></rel-sym>
        <rel-sym link="${platform.build.subsite.composer.lock}" target="${subsite.resources.composer.lock}"></rel-sym>
    </target>
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
> <target name="platform-make" depends="platform-unpack">
        <if>
            <available file="${subsite.make}" type="file"></available>
            <then>
                <echo msg="Make the subsite."></echo>
                <phingcall target="drush-make-no-core">
                    <property name="drush.make.target.file" value="${subsite.make}"></property>
                    <property name="drush.make.root" value="${platform.build.dir}"></property>
                </phingcall>
            </then>
            <else>
                <echo msg="No make file found. Skipping..."></echo>
            </else>
        </if>
    </target>
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
> <target name="platform-unpack" depends="platform-download">
        <!-- Use the faster native commands on UNIX systems. -->
        <if>
            <os family="unix"></os>
            <then>
                <echo msg='mkdir "${platform.build.dir}"'></echo>
                <exec command='mkdir "${platform.build.dir}"' dir="${project.basedir}" passthru="true"></exec>
                <echo msg='tar xzf "${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz" -C "${platform.build.dir}"'></echo>
                <exec command='tar xzf "${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz" -C "${platform.build.dir}"' dir="${project.basedir}" passthru="true" checkreturn="true"></exec>
            </then>
            <else>
                <untar file="${platform.package.cachedir}/platform-dev-${platform.package.reference}.tar.gz" todir="${platform.build.dir}"></untar>
            </else>
        </if>
    </target>
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
> <target name="platform-update-htaccess">
        <if>
            <istrue value="${drupal.htaccess.append.text}"></istrue>
            <then>
                <echo msg="Appended text to htaccess."></echo>
                <append destfile="${drupal.htaccess.path}" text="${drupal.htaccess.append.text}"></append>
            </then>
            <else>
                <echo msg="Appended no text to htaccess."></echo>
            </else>
        </if>
    </target>
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
> <target name="prompt-for-credentials-and-retry" hidden="true">
        <input propertyName="project.database.url.htaccess.username" message="Please enter your username.">
        <input hidden="true" propertyName="project.database.url.htaccess.password" message="Please enter your password.">
        <phingcall target="subsite-database-wget"></phingcall>
    </target>
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
> <target name="starterkit-build-documentation-index">
        <build-documentation-index></build-documentation-index>        
    </target>
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
> <target name="starterkit-copy-templates">
        <echo msg="Ensuring the presence of build.xml and Jenkinsfile."></echo>
        <copy todir="${project.basedir}">
            <fileset dir="${subsite.starterkit.templates}"></fileset>
        </copy>
    </target>
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
> <target name="starterkit-link-binary">
        <echo msg="Provide project with starterkit binary at root level."></echo>
        <rel-sym link="${project.basedir}/ssk" target="${subsite.starterkit.bin}" overwrite="true"></rel-sym>
    </target>
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
> <target name="starterkit-upgrade">

        <!-- Delete starterkit folders. -->
        <phingcall target="delete-folder">
            <property name="folder.to.delete" value="${project.basedir}/bin"></property>
        </phingcall>
        <phingcall target="delete-folder">
            <property name="folder.to.delete" value="${project.basedir}/docs"></property>
        </phingcall>
        <phingcall target="delete-folder">
            <property name="folder.to.delete" value="${project.basedir}/src"></property>
        </phingcall>
        <phingcall target="delete-folder">
            <property name="folder.to.delete" value="${subsite.resources.dir}/cloudformation"></property>
        </phingcall>
        <phingcall target="delete-folder">
            <property name="folder.to.delete" value="${subsite.resources.dir}/codedeploy"></property>
        </phingcall>
        <phingcall target="delete-folder">
            <property name="folder.to.delete" value="${subsite.resources.dir}/composer"></property>
        </phingcall>
        <!-- Delete starterkit files. -->
        <delete>
            <fileset dir="${project.basedir}">
                <include name="CHANGELOG.md"></include>
                <include name="LICENSE.md"></include>
                <include name="README.md"></include>
                <include name="appspec.yml"></include>
                <include name="build.clone.xml"></include>
                <include name="build.package.xml"></include>
                <include name="build.properties.dist"></include>
                <include name="build.test.xml"></include>
                <include name="composer.lock"></include>
                <include name="phpcs-ruleset.xml"></include>
            </fileset>
        </delete>
        <!-- Move subsite files to new location. -->
        <move file="${subsite.resources.dir}/phpcs-custom.xml" tofile="phpcs-ruleset.xml" overwrite="true"></move>
    </target>
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
> <target name="subsite-composer-install">
        <echo msg="Run 'composer install' in the subsite folder for development purposes."></echo>
        <composer command="install" composer="${composer.bin}">
            <arg value="--working-dir=${platform.build.subsite.dir}"></arg>
            <arg value="--no-interaction"></arg>
            <!-- <arg value="no-plugins" /> -->
            <arg value="--no-suggest"></arg>
            <arg value="--ansi"></arg>
        </composer>
    </target>
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
> <target name="subsite-database-download">
        <echo msg="Download the production database."></echo>
        <!--Strips gz suffix. -->
        <php expression="substr('${project.database.filename}', 0, -3)" returnProperty="gunzipped.filename" level="debug"></php>
        <if>
            <not>
                <!-- Check if we have a previously downloaded dump available. -->
                <available file="tmp/${gunzipped.filename}" type="file" property="gunzipped.project.db"></available>
            </not>
            <then>
                <!-- If not available, download and unzip the file. -->
                <phingcall target="subsite-database-wget"></phingcall>
                <exec command="gunzip tmp/${project.database.filename}" checkreturn="true" passthru="false" logoutput="true"></exec>
            </then>
            <else>
                <!-- Inform user if file was already downloaded. -->
                <echo msg="File ${gunzipped.filename} already downloaded."></echo>
                <echo msg="Proceeding to import."></echo>
            </else>
        </if>
    </target>
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
> <target name="subsite-database-import" depends="subsite-database-download">
        <echo msg="Import production database."></echo>
        <!-- Drop database, create if necessary and import the dump. -->
        <phingcall target="drush-sql-drop"></phingcall>
        <phingcall target="drush-sql-create"></phingcall>
        <phingcall target="drush-sql-import">
            <property name="database-file" value="tmp/${gunzipped.filename}"></property>
        </phingcall>
        <phingcall target="drush-registry-rebuild"></phingcall>
    </target>
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
> <target name="subsite-database-wget">
        <!--Generate .htaccess credential property if needed, empty if not. -->
        <if>
            <or>
                <equals arg1="${project.database.url.htaccess.username}" arg2=""></equals>
                <equals arg1="${project.database.url.htaccess.password}" arg2=""></equals>
            </or>
            <then>
                <!-- If username or password is not provided, empty the credential string. -->
                <property name="project.database.url.credentials" value="" override="true"></property>
            </then>
            <else>
                <!-- If username or password is provided, build the credential string. -->
                <property name="project.database.url.credentials" value="${project.database.url.htaccess.username}:${project.database.url.htaccess.password}@" override="true"></property>
            </else>
        </if>
        <!-- Attempt to download the database dump. -->
        <exec command="wget ${project.database.url.scheme}://${project.database.url.credentials}${project.database.url}${project.database.filename}" dir="tmp" checkreturn="false" passthru="false" outputProperty="project.database.download"></exec>
        <if>
            <!-- Upon success inform the user. -->
            <contains string="${project.database.download}" substring="200"></contains>
            <then>
                <echo msg="Database successfully downloaded."></echo>
            </then>
            <!-- When denied access, prompt the user for credentials and retry the download. -->
            <elseif>
                <contains string="${project.database.download}" substring="401"></contains>
                <then>
                    <phingcall target="prompt-for-credentials-and-retry"></phingcall>
                </then>
            </elseif>
            <!-- Otherwise we fail the build and display the download message. -->
            <else>
                <echo msg="Failed to download the database dump. Result of wget:" level="error"></echo>
                <echo msg="${project.database.download}" level="error"></echo>
                <fail></fail>
            </else>
        </if>
    </target>
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
> <target name="subsite-modules-development-download" depends="subsite-modules-development-makefile">
        <echo msg="Download development modules."></echo>
        <phingcall target="drush-make-no-core">
            <property name="drush.make.target.file" value="${subsite.temporary.development.make}"></property>
            <property name="drush.make.root" value="${platform.build.dir}"></property>
        </phingcall>
    </target>
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
> <target name="subsite-modules-development-enable">
        <phingcall target="drush-enable-modules">
            <property name="drupal.modules" value="${development.modules.enable}"></property>
        </phingcall>
    </target>
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
> <target name="subsite-modules-development-makefile">
        <echo msg="Generate the makefile for development modules."></echo>
        <if>
            <available file="${subsite.temporary.development.make}" type="file" property="development.makefile.available"></available>
            <then>
                <echo message="Deleting existing makefile."></echo>
                <delete file="${subsite.temporary.development.make}" failonerror="false"></delete>
            </then>
        </if>
        <drushmakefile makeFile="${subsite.temporary.development.make}" coreVersion="${drupal.core.version}" projects="${development.modules.download}" defaultProjectDir="${development.modules.location}"></drushmakefile>
    </target>
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
> <target name="subsite-modules-install-enable">
        <phingcall target="drush-enable-modules">
            <property name="drupal.modules" value="${subsite.install.modules}"></property>
        </phingcall>
    </target>
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
> <target name="subsite-setup-files-directory">
        <if>
            <istrue value="${platform.build.files.dir}"></istrue>
            <then>
                <mkdir dir="${platform.build.files.dir}/private_files"></mkdir>
                <mkdir dir="${platform.build.tmp.dir}"></mkdir>
                <!-- Support CSS and JS injector. -->
                <mkdir dir="${platform.build.files.dir}/css_injector"></mkdir>
                <mkdir dir="${platform.build.files.dir}/js_injector"></mkdir>
            </then>
        </if>
    </target>
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
> <target name="subsite-site-backup">

        <!-- Check if site exists. -->
        <if>
            <available file="${platform.build.settings.dir}/settings.php" type="file"></available>
            <then>
                <property name="site-detected" value="1"></property>
            </then>
            <else>
                <echo msg="No site installation detected. Skipping backup."></echo>
            </else>
        </if>

        <if>
            <and>
                <equals arg1="${platform.rebuild.auto}" arg2="0"></equals>
                <equals arg1="${site-detected}" arg2="1"></equals>
            </and>
            <then>
                <echo msg="Installed site detected." level="warning"></echo>
                <propertyprompt propertyName="subsite-site-backup-activated" promptText="Do you wish to backup site for this build? (y/n)"></propertyprompt>
                <if>
                    <equals arg1="${subsite-site-backup-activated}" arg2="y"></equals>
                    <then>
                        <property name="platform.rebuild.auto" value="1" override="true"></property>
                    </then>
                </if>
            </then>
        </if>
        <if>
            <and>
                <equals arg1="${platform.rebuild.auto}" arg2="1"></equals>
                <equals arg1="${site-detected}" arg2="1"></equals>
            </and>
            <then>
                <if>
                    <!-- Delete any remains of previous backup attempts. -->
                    <available file="${platform.rebuild.backup.destination}" type="dir"></available>
                    <then>
                        <delete dir="${platform.rebuild.backup.destination}" includeemptydirs="true"></delete>
                    </then>
                </if>
                <!-- Create backup directory. -->
                <mkdir dir="${platform.rebuild.backup.destination}"></mkdir>
                <!-- Make the settings directory writable because we can not delete it otherwise -->
                <phingcall target="unprotect-folder">
                    <property name="folder.to.unprotect" value="${platform.build.settings.dir}"></property>
                </phingcall>
                <!-- Back up folders list. -->
                <foreach list="${platform.rebuild.backup.folders}" param="site-item" target="subsite-site-backup-item" delimiter=";">
                    <property name="site-item-type" value="dir"></property>
                </foreach>
                <!-- Back up files list. -->
                <foreach list="${platform.rebuild.backup.files}" param="site-item" target="subsite-site-backup-item" delimiter=";">
                    <property name="site-item-type" value="file"></property>
                </foreach>
            </then>
        </if>
        <if>
            <equals arg1="${subsite-site-backup-activated}" arg2="y"></equals>
            <then>
                <property name="platform.rebuild.auto" value="0" override="true"></property>
            </then>
        </if>
    </target>
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
> <target name="subsite-site-backup-item" hidden="true">
        <php expression='dirname("${site-item}")' returnProperty="site-item-dir"></php>
        <property name="site-item-backup-dir" value="${site-item-dir}">
            <filterchain>
                <replaceregexp>
                    <regexp pattern="${platform.build.dir}" replace="${platform.rebuild.backup.destination}" ignoreCase="false"></regexp>
                </replaceregexp>
            </filterchain>
        </property>
        <if>
            <available file="${site-item}" type="${site-item-type}"></available>
            <then>
                <if>
                    <not>
                        <available file="${site-item-backup-dir}" type="dir"></available>
                    </not>
                    <then>
                        <mkdir dir="${site-item-backup-dir}"></mkdir>
                    </then>
                </if>
                <move file="${site-item}" todir="${site-item-backup-dir}" includeemptydirs="true"></move>
            </then>
            <else>
                <php expression='ucwords("${site-item-type}")' returnProperty="site-item-type-capitalized"></php>
                <echo msg="Skipping ${site-item}. ${site-item-type-capitalized} not found." level="warning"></echo>
            </else>
        </if>
    </target>
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
> <target name="subsite-site-restore">

        <!-- Check if backup exists. -->
        <if>
            <available file="${platform.rebuild.backup.destination}" type="dir"></available>
            <then>
                <property name="backup-detected" value="1"></property>
            </then>
            <else>
                <echo msg="No site backup detected. Skipping restore."></echo>
            </else>
        </if>
        <if>
            <and>
                <equals arg1="${platform.rebuild.auto}" arg2="0"></equals>
                <equals arg1="${backup-detected}" arg2="1"></equals>
            </and>
            <then>
                <echo msg="Site backup detected." level="warning"></echo>
                <propertyprompt propertyName="subsite-site-restore-activated" promptText="Do you wish to restore site for this build? (y/n)"></propertyprompt>
                <if>
                    <equals arg1="${subsite-site-restore-activated}" arg2="y"></equals>
                    <then>
                        <property name="platform.rebuild.auto" value="1" override="true"></property>
                    </then>
                </if>
            </then>
        </if>
        <if>
            <and>
                <equals arg1="${platform.rebuild.auto}" arg2="1"></equals>
                <equals arg1="${backup-detected}" arg2="1"></equals>
            </and>
            <then>
                <echo msg="Restoring site files and folders from ${platform.rebuild.backup.destination}"></echo>
                <!-- Restore folders list. -->
                <foreach list="${platform.rebuild.backup.folders}" param="site-item" target="subsite-site-restore-item" delimiter=";">
                    <property name="site-item-type" value="dir"></property>
                </foreach>
                <!-- Restore files list. -->
                <foreach list="${platform.rebuild.backup.files}" param="site-item" target="subsite-site-restore-item" delimiter=";">
                    <property name="site-item-type" value="file"></property>
                </foreach>
                <!-- Delete the site backup directory. -->
                <delete dir="${platform.rebuild.backup.destination}" includeemptydirs="true"></delete>
            </then>
        </if>
    </target>
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
> <target name="subsite-site-restore-item" hidden="true">
        <property name="site-item-backup" value="${site-item}">
            <filterchain>
                <replaceregexp>
                    <regexp pattern="${platform.build.dir}" replace="${platform.rebuild.backup.destination}" ignoreCase="false"></regexp>
                </replaceregexp>
            </filterchain>
        </property>
        <if>
            <available file="${site-item-backup}" type="${site-item-type}"></available>
            <then>
                <php expression='dirname("${site-item}")' returnProperty="site-item-dir"></php>
                <if>
                    <not>
                        <available file="${site-item-dir}" type="dir"></available>
                    </not>
                    <then>
                        <mkdir dir="${site-item-dir}"></mkdir>
                    </then>
                </if>
                <move file="${site-item-backup}" todir="${site-item-dir}" includeemptydirs="true"></move>
            </then>
            <else>
                <php expression='ucwords("${site-item-type}")' returnProperty="site-item-type-capitalized"></php>
                <echo msg="Skipping ${site-item}. ${site-item-type-capitalized} not found." level="warning"></echo>
            </else>
        </if>
    </target>
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
> <target name="test-behat-setup">
        <if>
            <available file="${behat.yml.path}" type="file" property="behat.yml.available"></available>
            <then>
                <echo message="Deleting existing behat.yml configuration file"></echo>
                <delete file="${behat.yml.path}" failonerror="false"></delete>
            </then>
        </if>
        <echo message="Creating behat.yml configuration file"></echo>
        <loadfile property="behat.yml.content" file="${behat.yml.template}">
            <filterchain>
                <replacetokens>
                    <token key="project.code.dir" value="${project.code.dir}"></token>
                    <token key="drupal.site.dir" value="${drupal.site.dir}"></token>
                    <token key="behat.base_url" value="${behat.base_url}"></token>
                    <token key="behat.formatter.name" value="${behat.formatter.name}"></token>
                </replacetokens>
            </filterchain>
        </loadfile>
        <echo message="${behat.yml.content}" file="${behat.yml.path}"></echo>
    </target>
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
> <target name="test-behat-setup-link">
        <echo msg="Symlink the Behat bin and test directory in './sites/all'."></echo>
        <rel-sym link="${project.basedir}/ssk/behat" target="${subsite.starterkit.vendor}/bin/behat" overwrite="true"></rel-sym>
        <rel-sym link="${platform.build.subsite.dir}/tests" target="${project.basedir}/tests" overwrite="true"></rel-sym>
    </target>
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
> <target name="test-phpcs-setup">
        <if>
            <available file="${phpcs.config}" type="file" property="phpcs.config.available"></available>
            <then>
                <echo message="Deleting existing PHP Codesniffer default configuration file."></echo>
                <delete file="${phpcs.config}" failonerror="false"></delete>
            </then>
        </if>
        <if>
            <available file="${phpcs.global.config}" type="file" property="phpcs.global.config.available"></available>
            <then>
                <echo message="Deleting existing PHP Codesniffer global configuration file."></echo>
                <delete file="${phpcs.global.config}" failonerror="false"></delete>
            </then>
        </if>
        <phpcodesnifferconfiguration configFile="${phpcs.config}" extensions="${phpcs.extensions}" files="${phpcs.files}" globalConfig="${phpcs.global.config}" ignorePatterns="${phpcs.ignore}" passWarnings="${phpcs.passwarnings}" report="${phpcs.report}" showProgress="${phpcs.progress}" showSniffCodes="${phpcs.sniffcodes}" standards="${phpcs.standards}"></phpcodesnifferconfiguration>

        <!-- Set up the git pre-push hook. -->
        <phingcall target="test-phpcs-setup-prepush"></phingcall>
    </target>
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
> <target name="test-phpcs-setup-prepush">
        <if>
            <equals arg1="${phpcs.prepush.enable}" arg2="1"></equals>
            <then>
                <echo message="Enabling git pre-push hook."></echo>
                <mkdir dir="${project.basedir}/resources/git/hooks/pre-push"></mkdir>
                <rel-sym link="${phpcs.prepush.destination}" target="${phpcs.prepush.source}" overwrite="true"></rel-sym>
            </then>
           <else>
                <echo message="Disabling git pre-push hook."></echo>
                <delete file="${phpcs.prepush.destination}" failonerror="false" quiet="true"></delete>
          </else>
        </if>
    </target>
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
> <target name="test-quality-assurance">
        <exec command="${subsite.starterkit.bin}/qa review:full --no-interaction --ansi" passthru="true" checkreturn="true"></exec>
    </target>
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
> <target name="test-run-php-codesniffer">
        <exec command="${subsite.starterkit.bin}/phpcs" passthru="true" checkreturn="true"></exec>
    </target>
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
> <target name="unprotect-folder" hidden="true">
        <!-- This should only be used on folders that need to be removed. -->
        <if>
            <available file="${folder.to.unprotect}" type="dir"></available>
            <then>
                <chmod mode="0777" failonerror="true" verbose="false" quiet="true">
                    <fileset dir="${folder.to.unprotect}"></fileset>
                </chmod>
            </then>
        </if>
    </target>
> ```
                </details>
            </td>
        </tr>
    </tbody>
</table>