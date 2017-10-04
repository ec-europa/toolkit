# Cache system

In order to speed up your builds the toolkit provides a caching system.

## Configure cache

### Global cache
Toolkit stores files to be shared accross all your projects. This allows you to
skip platform downloads and installations. The location of the global cache can
be configured through:

<details><summary>execute <code>nano build.develop.props</code></summary><p>

```
# Shared paths.
# -------------
share.path = /tmp
share.name = toolkit
```
</p></details>

### Local cache
Toolkit stores files that are specific to the project itself inside a folder
located within the project. The location of the local cache can be configured
through:

<details><summary>execute <code>nano build.develop.props</code></summary><p>

```
# Temporary folders and resources.
# --------------------------------
project.tmp.dir = ${project.basedir}/.tmp
```
</p></details>

## Clearing caches
Ìf you are having issues with caching you can clear the entire cache with:

<details><summary>execute <code>./toolkit/phing cache-clear-all</code></summary><p>

```
Buildfile: /home/user/github/ec-europa/project-id/build.xml
 [property] Loading /home/user/github/ec-europa/project-id/includes/phing/build/boot.props
 [property] Loading /home/user/github/ec-europa/project-id/build.develop.props
 [property] Loading /home/user/github/ec-europa/project-id/build.project.props
 [property] Loading /home/user/github/ec-europa/project-id/.tmp/build.version.props
     [echo] Global share directory /tmp/toolkit available.
     [echo] Temporary directory /home/user/github/ec-europa/project-id/.tmp available.

core > cache-clear-global:

   [delete] Deleting directory /tmp/toolkit

core > cache-clear-local:

   [delete] Deleting directory /home/user/github/ec-europa/project-id/.tmp

core > cache-clear-all:


BUILD FINISHED

Total time: 0.6896 seconds
```
</p></details>

If you only want to clear global or local cache you can use these commands:

<details><summary>execute <code>./toolkit/phing cache-clear-global</code></summary><p>

```
Buildfile: /home/user/github/ec-europa/project-id/build.xml
 [property] Loading /home/user/github/ec-europa/project-id/includes/phing/build/boot.props
 [property] Loading /home/user/github/ec-europa/project-id/build.develop.props
 [property] Loading /home/user/github/ec-europa/project-id/build.project.props
 [property] Loading /home/user/github/ec-europa/project-id/.tmp/build.version.props
     [echo] Global share directory /tmp/toolkit available.
     [echo] Temporary directory /home/user/github/ec-europa/project-id/.tmp available.

core > cache-clear-global:

   [delete] Deleting directory /tmp/toolkit


BUILD FINISHED

Total time: 0.6896 seconds
```
</p></details>
<details><summary>execute <code>./toolkit/phing cache-clear-local</code></summary><p>

```
Buildfile: /home/user/github/ec-europa/project-id/build.xml
 [property] Loading /home/user/github/ec-europa/project-id/includes/phing/build/boot.props
 [property] Loading /home/user/github/ec-europa/project-id/build.develop.props
 [property] Loading /home/user/github/ec-europa/project-id/build.project.props
 [property] Loading /home/user/github/ec-europa/project-id/.tmp/build.version.props
     [echo] Global share directory /tmp/toolkit available.
     [echo] Temporary directory /home/user/github/ec-europa/project-id/.tmp available.

core > cache-clear-local:

   [delete] Deleting directory /home/user/github/ec-europa/project-id/.tmp


BUILD FINISHED

Total time: 0.6896 seconds
```
</p></details>
