<big><table><thead><tr><th nowrap> [Using Docker environment](./docker-environment.md#using-docker-environment) </th><th width="100%" align="center"> [User guide](../README.md#user-guide) </th><th nowrap> [NextEuropa Toolkit](../README.md#nexteuropa-toolkit) </th></tr></thead></table>

# Supported Profiles
Toolkit is supporting 3rd part profiles, this way you can use toolkit with any Drupal profile like Drupal standard, Commerce Kickstart, Panoply or any other.

## Default Profile
By default toolkit will install the multisite_drupal_standard, part of EC NextEuropa
platform.

Configuration:
```
profile = multisite_drupal_standard
```

## Other profiles
In order to use toolkit with a 3rd part profile you should
modify your `build.project.props` and define the following props:

Example:
```
# Profile configuration.
# -----------------------
profile = drupal
profile.name = standard
platform.package.version = 7.56

```
