# Profiles

<big><table><thead><tr><th nowrap> [Using Docker environment](./docker-environment.md#using-docker-environment) </th><th width="100%" align="center"> [User guide](../README.md#user-guide) </th><th nowrap> [NextEuropa Toolkit](../README.md#nexteuropa-toolkit) </th></tr></thead></table>

## EC Profiles
By default toolkit will install the multisite_drupal_standard, part of EC NextEuropa
platform but can be be configured to run any profile.

Configuration:
```
profile = multisite_drupal_standard
```

## Other profiles
Toolkit as redesigned to support any drupal profile, in order to use this ressource you should
modify your `build.project.prop` and define the following information:

Example:
```
profile = drupal
profile.name = standard
```