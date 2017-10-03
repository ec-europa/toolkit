# Cache system

<p>In order to keep toolkip faster, we included some cache system in order to
cache:</p>

<details>
    <p><summary>Platform package</summary></p>
    <p>Toolkip stores a copy of platform package in your <code>share.path</code>
    directory.</p>
</details>
<details>
    <p><summary>Theme package</summary></p>
    <p>Some stuff here</p>
</details>
<details>
    <p><summary>Database dump</summary></p>
    <p>Some stuff here.</p>
</details>
<details>
    <p><summary>ASDA Dump</summary></p>
    <p>some stuff here.</p>
</details>

#### How to configure the cache in your project?
<p>Cache system is already in place, but you can adjust some settings in order
to adjust toolkit to your local environment. See bellow some settings you can
override in your <code>build.develop.props</code> file.</p>

```
platform.package.db.cache = 1
share.path = /tmp/cache
```

<p>Please, check <code>build.default.props</code> file in order to get all the
available settings.</p>

#### How to clean the cache?
<p>Toolkit provide a specific target to allow to remove all the cached files.
You should execute <code>./toolkit/phing cache-clear-all</code> to clean all the
caches.</p>

```
$> toolkit/phing cache-clear-all
Buildfile: /home/santosj/SourceCode/coolsite/build.xml
 [property] Loading ~/coolsite/vendor/ec-europa/toolkit/includes/phing/build/boot.props
 [property] Loading ~/coolsite/build.develop.props
 [property] Loading ~/coolsite/build.project.props
 [property] Loading ~/coolsite/.tmp/build.version.props
     [echo] Global share directory /tmp/cache/share available.
     [echo] Temporary directory ~/coolsite/.tmp available.

root > cache-delete:

     [echo] Cleaning cached files...
   [delete] Deleting directory /tmp/cache/share/platform/databases/platform-dev-2.3.71
   [delete] Deleting: /tmp/cache/share/platform/packages/deploy/platform-dev-2.3.71.tar.gz
   [delete] Deleting directory ~/coolsite/.tmp

```