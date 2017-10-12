# Toolkit change log

## Version 3.0.0 (from subsite-starterkit 2.2.5)

### Improvements
  * [MULTISITE-17744] - Toolkit is re-architectured into a separated composer package
  * [MULTISITE-17744] - Toolkit is re-architectured to support both platform and subsites
  * [MULTISITE-17365] - Toolkit is re-architectured to categorize build files into a better structure
  * [MULTISITE-18484] - Development modules are moved to resources/devel.make
  * [MULTISITE-18485] - Phing will look for composer.bin if the setting is incorrect
  * [MULTISITE-18306] - Phing help command is customized to improve the display and descriptions
  * [MULTISITE-18315] - Package downloads now all use same helper target and are automatically cached
  * [MULTISITE-18315] - ASDA download is simplified. Credentials can only be set through build properties
  * [MULTISITE-18486] - Devops modules are now included in the build to avoid missing modules
  * [MULTISITE-18487] - Files directories are created now with the ensuring of htaccess files
  * [MULTISITE-17365] - All drush targets have been aggregated into a single helper file build/help/drush.xml
  * [MULTISITE-16459] - Drush registry rebuild target will download itself if not available yet
  * [MULTISITE-17365] - Platform rebuilds will automatically backup and restore subsite by default
  * [MULTISITE-18488] - Platform version file is generated on the fly and informs user on latest available version
  * [MULTISITE-18318] - New target to validate build properties has been introduced to ensure minimum required properties
  * [MULTISITE-18096] - Github helper target has been introduced to upload release packages to the repository
  * [MULTISITE-18349] - Subsite starterkit 2.x targets have been deprecated and mapped to new ones
  * [MULTISITE-18340] - Drush generates aliases on installation process to improve multisite support
  * [MULTISITE-18196] - EC Europa theme build has been added in a separate build file
  * [MULTISITE-17744] - Installation process support created for composer create-project command
  * [MULTISITE-18096] - Test folder has been made independent to allow test package releases
  * [MULTISITE-18490] - Composer hooks now run phing targets defined in the build properties
  * [MULTISITE-18494] - General fixes for CI integration
  * [MULTISITE-18489] - Temporary files folder has been renamed from ./tmp to ./.tmp to decrease visibility
  * [MULTISITE-18520] - Improved user guide
  * [MULTISITE-18525] - Integration with Git hooks system
  * [MULTISITE-18563] - Integration with PHPUnit tests
  * [MULTISITE-18579] - Integration with Drone CI pipeline
  * [MULTISITE-17096] - Build dist now uses new symlink system. Lib folder structure will match dist folder structure
  * [MULTISITE-17624] - New build property introduced to select a solr module and accompanied core
  * [MULTISITE-18248] - Docker environment has been introduced, still experimental!
  
### Security
  * [NESTF-31] - New phing task introduced to validate the make files according to Drupal's security advisory


[//]: # (Reference urls)
[MULTISITE-17744]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18492
[MULTISITE-17365]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-17365
[MULTISITE-18484]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18484
[MULTISITE-18485]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18485
[MULTISITE-18306]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18306
[MULTISITE-18315]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18315
[MULTISITE-18486]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18486
[MULTISITE-18487]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18487
[MULTISITE-17365]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-17365
[MULTISITE-16459]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-16459
[MULTISITE-17365]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-17365
[MULTISITE-18488]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18488
[MULTISITE-18318]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18318
[MULTISITE-18096]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18096
[MULTISITE-18349]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18349
[MULTISITE-18340]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18340
[MULTISITE-18196]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18196
[MULTISITE-17744]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-17744
[MULTISITE-18096]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18096
[MULTISITE-18490]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18490
[MULTISITE-18494]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18494
[MULTISITE-18489]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18489
[MULTISITE-18520]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18520
[MULTISITE-18525]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18525
[MULTISITE-18563]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18563
[MULTISITE-18579]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18579
[MULTISITE-17096]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-17096
[MULTISITE-17624]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-17624
[MULTISITE-18248]: https://webgate.ec.europa.eu/CITnet/jira/browse/MULTISITE-18248
[NESTF-31]: https://webgate.ec.europa.eu/CITnet/jira/browse/NESTF-31

