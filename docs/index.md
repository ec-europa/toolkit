### build-platform-dev
<table>
    <thead>
        <tr align="left">
            <th>Description</th>
            <th width="100%">Build a local development version with a single platform profile.<img src="https://cdn0.iconfinder.com/data/icons/octicons/1024/checklist-20.png" align="right" /></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                <details><summary>Properties</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th nowrap>Property</th>
                            <th nowrap>Value</th>
                            <th width='@%"'>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>dir-build</td>
                            <td nowrap>${build.platform.dir}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>dir-profile</td>
                            <td nowrap>${build.platform.dir.profile}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>dir-profiles</td>
                            <td nowrap>${build.platform.dir.profiles}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>profile-name</td>
                            <td nowrap>${profile}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>profile-make</td>
                            <td nowrap>${profile.make}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>drupal-make</td>
                            <td nowrap>${profile.core.make}</td>
                            <td>Description</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <details><summary>Playbook</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th>Callback target</th>
                            <th>Buildfile</th>
                            <th width="100%">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>project-subsite-backup</td>
                            <td nowrap>./project.xml</td>
                            <td>Backup site defined files from properties.</td>
                        </tr>
                        <tr>
                            <td nowrap>project-scratch-build</td>
                            <td nowrap>./project.xml</td>
                            <td>Delete previous build to start over clean.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-make-drupal</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Build the Drupal core codebase.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-link-profiles</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Link platform profiles to lib folder for development.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-make-profiles</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Makes all profile resources with drush.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-link-resources</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Link platform resources to lib folder for development.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-theme-dev</td>
                            <td nowrap>./project/theme.xml</td>
                            <td>Build EC Europa theme with version control.</td>
                        </tr>
                        <tr>
                            <td nowrap>project-platform-composer-no-dev</td>
                            <td nowrap>./project.xml</td>
                            <td>Run composer install without dev on platform.</td>
                        </tr>
                        <tr>
                            <td nowrap>project-modules-devel-dl</td>
                            <td nowrap>./project.xml</td>
                            <td>Download development modules with drush.</td>
                        </tr>
                        <tr>
                            <td nowrap>project-platform-set-htaccess</td>
                            <td nowrap>./project.xml</td>
                            <td>Append htaccess config to root .htaccess.</td>
                        </tr>
                        <tr>
                            <td nowrap>project-subsite-restore</td>
                            <td nowrap>./project.xml</td>
                            <td>Restore site defined files from properties.</td>
                        </tr>
                        <tr>
                            <td nowrap>project-subsite-setup-files</td>
                            <td nowrap>./project.xml</td>
                            <td>Create files directories for subsite.</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
    </tbody>
</table>

### build-platform-dev-all
<table>
    <thead>
        <tr align="left">
            <th>Description</th>
            <th width="100%">Build a local development version with all platform profiles.<img src="https://cdn0.iconfinder.com/data/icons/octicons/1024/checklist-20.png" align="right" /></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                <details><summary>Properties</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th nowrap>Property</th>
                            <th nowrap>Value</th>
                            <th width='@%"'>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>dir-build</td>
                            <td nowrap>${build.platform.dir}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>dir-profile</td>
                            <td nowrap>${build.platform.dir.profile}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>dir-profiles</td>
                            <td nowrap>${build.platform.dir.profiles}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>profile-name</td>
                            <td nowrap>${profile}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>profile-make</td>
                            <td nowrap>${profile.make}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>drupal-make</td>
                            <td nowrap>${profile.core.make}</td>
                            <td>Description</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <details><summary>Playbook</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th>Callback target</th>
                            <th>Buildfile</th>
                            <th width="100%">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>project-scratch-build</td>
                            <td nowrap>./project.xml</td>
                            <td>Delete previous build to start over clean.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-make-drupal</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Build the Drupal core codebase.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-copy-profiles</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Copies all profiles for distirbution.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-make-profiles</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Makes all profile resources with drush.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-copy-resources</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Copies platform resources for distribution.</td>
                        </tr>
                        <tr>
                            <td nowrap>project-platform-composer-no-dev</td>
                            <td nowrap>./project.xml</td>
                            <td>Run composer install without dev on platform.</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
    </tbody>
</table>

### build-platform-dist
<table>
    <thead>
        <tr align="left">
            <th>Description</th>
            <th width="100%">Build a single platform profile intended as a release package.<img src="https://cdn0.iconfinder.com/data/icons/octicons/1024/checklist-20.png" align="right" /></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                <details><summary>Properties</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th nowrap>Property</th>
                            <th nowrap>Value</th>
                            <th width='@%"'>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>composer-dev</td>
                            <td nowrap>no-dev</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>dir-build</td>
                            <td nowrap>${build.dist.dir}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>dir-profile</td>
                            <td nowrap>${build.dist.dir.profile}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>dir-profiles</td>
                            <td nowrap>${build.dist.dir.profiles}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>profile-name</td>
                            <td nowrap>${profile}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>profile-make</td>
                            <td nowrap>${profile.make}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>drupal-make</td>
                            <td nowrap>${profile.core.make}</td>
                            <td>Description</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <details><summary>Playbook</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th>Callback target</th>
                            <th>Buildfile</th>
                            <th width="100%">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>project-scratch-build</td>
                            <td nowrap>./project.xml</td>
                            <td>Delete previous build to start over clean.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-make-drupal</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Build the Drupal core codebase.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-copy-profile</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Copies single profile for distribution.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-make-profile</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Makes single profile resources with drush.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-copy-resources</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Copies platform resources for distribution.</td>
                        </tr>
                        <tr>
                            <td nowrap>project-platform-composer-no-dev</td>
                            <td nowrap>./project.xml</td>
                            <td>Run composer install without dev on platform.</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
    </tbody>
</table>

### build-platform-dist-all
<table>
    <thead>
        <tr align="left">
            <th>Description</th>
            <th width="100%">Build all platform profiles intended as a release package.<img src="https://cdn0.iconfinder.com/data/icons/octicons/1024/checklist-20.png" align="right" /></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                <details><summary>Properties</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th nowrap>Property</th>
                            <th nowrap>Value</th>
                            <th width='@%"'>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>composer-dev</td>
                            <td nowrap>no-dev</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>dir-build</td>
                            <td nowrap>${build.dist.dir}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>dir-profile</td>
                            <td nowrap>${build.dist.dir.profile}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>dir-profiles</td>
                            <td nowrap>${build.dist.dir.profiles}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>profile-name</td>
                            <td nowrap>${profile}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>profile-make</td>
                            <td nowrap>${profile.make}</td>
                            <td>Description</td>
                        </tr>
                        <tr>
                            <td nowrap>drupal-make</td>
                            <td nowrap>${profile.core.make}</td>
                            <td>Description</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <details><summary>Playbook</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th>Callback target</th>
                            <th>Buildfile</th>
                            <th width="100%">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>project-scratch-build</td>
                            <td nowrap>./project.xml</td>
                            <td>Delete previous build to start over clean.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-make-drupal</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Build the Drupal core codebase.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-copy-profiles</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Copies all profiles for distirbution.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-make-profiles</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Makes all profile resources with drush.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-platform-copy-resources</td>
                            <td nowrap>./project/platform.xml</td>
                            <td>Copies platform resources for distribution.</td>
                        </tr>
                        <tr>
                            <td nowrap>project-platform-composer-no-dev</td>
                            <td nowrap>./project.xml</td>
                            <td>Run composer install without dev on platform.</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
    </tbody>
</table>

### build-subsite-dev
<table>
    <thead>
        <tr align="left">
            <th>Description</th>
            <th width="100%">Build a local development version of the site.<img src="https://cdn0.iconfinder.com/data/icons/octicons/1024/checklist-20.png" align="right" /></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                <details><summary>Properties</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th nowrap>Property</th>
                            <th nowrap>Value</th>
                            <th width='@%"'>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>dir-build</td>
                            <td nowrap>${build.subsite.dir}</td>
                            <td>Description</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <details><summary>Playbook</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th>Callback target</th>
                            <th>Buildfile</th>
                            <th width="100%">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>project-scratch-build</td>
                            <td nowrap>./project.xml</td>
                            <td>Delete previous build to start over clean.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-subsite-link-resources</td>
                            <td nowrap>./project/subsite.xml</td>
                            <td>Link subsite resources to lib folder for development.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-subsite-make-site</td>
                            <td nowrap>./project/subsite.xml</td>
                            <td>Makes the subsite resources with drush.</td>
                        </tr>
                        <tr>
                            <td nowrap>project-modules-devel-dl</td>
                            <td nowrap>./project.xml</td>
                            <td>Download development modules with drush.</td>
                        </tr>
                        <tr>
                            <td nowrap>project-subsite-composer-dev</td>
                            <td nowrap>./project.xml</td>
                            <td>Run composer install with dev on subsite.</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
    </tbody>
</table>

### build-subsite-dist
<table>
    <thead>
        <tr align="left">
            <th>Description</th>
            <th width="100%">Build a site intended as a release package.<img src="https://cdn0.iconfinder.com/data/icons/octicons/1024/checklist-20.png" align="right" /></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                <details><summary>Properties</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th nowrap>Property</th>
                            <th nowrap>Value</th>
                            <th width='@%"'>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>dir-build</td>
                            <td nowrap>${build.dist}</td>
                            <td>Description</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <details><summary>Playbook</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th>Callback target</th>
                            <th>Buildfile</th>
                            <th width="100%">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>project-scratch-build</td>
                            <td nowrap>./project.xml</td>
                            <td>Delete previous build to start over clean.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-subsite-link-resources</td>
                            <td nowrap>./project/subsite.xml</td>
                            <td>Link subsite resources to lib folder for development.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-subsite-make-site</td>
                            <td nowrap>./project/subsite.xml</td>
                            <td>Makes the subsite resources with drush.</td>
                        </tr>
                        <tr>
                            <td nowrap>build-subsite-copy-resources</td>
                            <td nowrap>./project/subsite.xml</td>
                            <td>Copy subsite resources for distribution.</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
    </tbody>
</table>

### build-project-platform
<table>
    <thead>
        <tr align="left">
            <th>Description</th>
            <th width="100%">Build NextEuropa Platform code without version control.<img src="https://cdn0.iconfinder.com/data/icons/octicons/1024/checklist-20.png" align="right" /></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                <details><summary>Properties</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th nowrap>Property</th>
                            <th nowrap>Value</th>
                            <th width='@%"'>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>dir-build</td>
                            <td nowrap>${build.platform.dir}</td>
                            <td>Description</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <details><summary>Playbook</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th>Callback target</th>
                            <th>Buildfile</th>
                            <th width="100%">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>project-subsite-backup</td>
                            <td nowrap>./project.xml</td>
                            <td>Backup site defined files from properties.</td>
                        </tr>
                        <tr>
                            <td nowrap>project-scratch-build</td>
                            <td nowrap>./project.xml</td>
                            <td>Delete previous build to start over clean.</td>
                        </tr>
                        <tr>
                            <td nowrap>project-platform-set-version</td>
                            <td nowrap>./help.xml</td>
                            <td>Save the platform version used for builds.</td>
                        </tr>
                        <tr>
                            <td nowrap>project-platform-package-unpack</td>
                            <td nowrap>./project.xml</td>
                            <td>Download and unpack platform deploy package.</td>
                        </tr>
                        <tr>
                            <td nowrap>project-subsite-restore</td>
                            <td nowrap>./project.xml</td>
                            <td>Restore site defined files from properties.</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
    </tbody>
</table>

### build-project-theme
<table>
    <thead>
        <tr align="left">
            <th>Description</th>
            <th width="100%">Build EC Europa theme without version control.<img src="https://cdn0.iconfinder.com/data/icons/octicons/1024/checklist-20.png" align="right" /></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                <details><summary>Properties</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th nowrap>Property</th>
                            <th nowrap>Value</th>
                            <th width='@%"'>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>dir-build</td>
                            <td nowrap>${build.platform.dir}</td>
                            <td>Description</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <details><summary>Playbook</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th>Callback target</th>
                            <th>Buildfile</th>
                            <th width="100%">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>theme-europa-download-extract</td>
                            <td nowrap>./project/theme.xml</td>
                            <td>Download and unpack the EC Europa theme.</td>
                        </tr>
                        <tr>
                            <td nowrap>theme-europa-create-symlinks</td>
                            <td nowrap>./project/theme.xml</td>
                            <td>Create symlinks to themes in lib for development.</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
    </tbody>
</table>

### build-project-subsite
<table>
    <thead>
        <tr align="left">
            <th>Description</th>
            <th width="100%">Build NextEuropa Subsite code without version control.<img src="https://cdn0.iconfinder.com/data/icons/octicons/1024/checklist-20.png" align="right" /></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                <details><summary>Properties</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th nowrap>Property</th>
                            <th nowrap>Value</th>
                            <th width='@%"'>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td nowrap>dir-build</td>
                            <td nowrap>${build.platform.dir}</td>
                            <td>Description</td>
                        </tr>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <details><summary>Playbook</summary>
                <table width="100%">
                    <thead>
                        <tr align="left">
                            <th>Callback target</th>
                            <th>Buildfile</th>
                            <th width="100%">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                </details>
            </td>
        </tr>
    </tbody>
</table>

