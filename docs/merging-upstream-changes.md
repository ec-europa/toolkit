## Merging upstream changes

```
$ git remote add starterkit https://github.com/ec-europa/subsite-starterkit.git
$ git fetch starterkit
$ git merge starterkit/master
```

You might need to fix merge conflicts as usual. Pay extra attention to your
resources/site.make file. Performing an upstream merge often renames the file
to resources/site.make.example. This will be fixed in a future release.

When you have completed the update of your starterkit you must also run

```
$ composer install
```
to get the latest composer packages defined in the composer.lock file.
