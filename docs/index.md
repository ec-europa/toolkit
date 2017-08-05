Buildfile: /var/www/html/build.xml
 [property] Loading /var/www/html/vendor/ec-europa/ssk/build.properties.dist
     [echo] Loading PHP Codesniffer Configuration task.
     [echo] Loading Phing Deprecated Task.
     [echo] Loading Drush Makefile task.
     [echo] Importing the Drush task.
     [echo] Importing the Behat task.
     [echo] Loading Relative Symlink task.

My platform > help:

Default target:
-------------------------------------------------------------------------------
 help

Main targets:
-------------------------------------------------------------------------------
 build-clean          Build local version of subsite with a clean install.
 build-clone          Build local version of subsite with production data.
 build-code           Build local version of subsite without install.
 build-keep           Build local version of subsite with backup and restore.
 build-release        Build subsite source code release package.
 build-tests          Build subsite tests code release package.
 docker-compose-down  Trash docker project.
 docker-compose-stop  Stop docker project.
 docker-compose-up    Start docker project.
 install              Install the subsite.
 link-docroot         Create symlink from build to docroot.
 test-run-behat       Refresh configuration and run behat tests.
 test-run-phpcs       Refresh configuration and run phpcs review.
 test-run-qa          Refresh configuration and run qa review.

Subtargets:
-------------------------------------------------------------------------------
 dist-composer-install
 dist-copy-resources
 dist-delete
 dist-make
 drush-create-files-dirs
 drush-dl-rr
 drush-enable-solr
 drush-make-no-core
 drush-rebuild-node-access
 drush-regenerate-settings
 drush-registry-rebuild
 drush-site-install
 drush-sql-create
 drush-sql-drop
 drush-sql-dump
 drush-sql-import
 platform-composer-install
 platform-delete
 platform-download
 platform-link-resources
 platform-make
 platform-unpack
 platform-update-htaccess
 starterkit-build-docs
 starterkit-copy-templates
 starterkit-link-binary
 starterkit-upgrade
 subsite-composer-install
 subsite-create-directories
 subsite-database-download
 subsite-database-import
 subsite-database-wget
 subsite-modules-devel-dl
 subsite-modules-devel-en
 subsite-modules-devel-mf
 subsite-modules-install-en
 subsite-setup-files-directory
 subsite-site-backup
 subsite-site-restore
 test-behat-setup
 test-behat-setup-link
 test-phpcs-setup
 test-phpcs-setup-prepush
 test-quality-assurance
 test-run-php-codesniffer


BUILD FINISHED

Total time: 0.5472 seconds
