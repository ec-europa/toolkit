# Cache system

<p>In order to keep toolkit faster, we included a cache system, this is splitted
in 2 levels:</p>

<details>
    <p><summary>global cache</summary></p>
    <p>Toolkit stores files to be shared accross all your projects, you can
    control the place where the files are stored by updating the property
    <code>share.path</code> in your <code>build.develop.props</code> file.</p>
</details>
<details>
    <p><summary>local cache</summary></p>
    <p>Inside your project folder your have a folder .tmp that stores some cached
    files like database dumps and others. This is also used when the global cache
    is not available and toolkit cannot generate it.</p>
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
caches, this will affect the global and local cache.</p>

```
$> toolkit/phing cache-clear-all
Buildfile: /home/santosj/SourceCode/coolsite/build.xml
 [property] Loading ~/coolsite/vendor/ec-europa/toolkit/includes/phing/build/boot.props
 [property] Loading ~e/coolsite/build.develop.props
 [property] Loading ~e/coolsite/build.project.props
 [property] Loading ~e/coolsite/.tmp/build.version.props
     [echo] Global share directory /tmp/cache/share available.
     [echo] Temporary directory ~/coolsite/.tmp available.

root > cache-clear-all:

     [echo] Cleaning cached and temporary file...
   [delete] Deleting directory /tmp/cache
   [delete] Deleting directory ~/coolsite/.tmp

BUILD FINISHED

Total time: 0.7483 seconds


```
