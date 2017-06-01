## Upgrade from 1.0.x to 2.0.x
   
Subsite Starterkit 2.0.0 introduces full Composer support. In order to
provide that a new building procedure has been put in place.

This upgrade instructions assume that your subsite is a "git fork" of the
[main repository](https://github.com/ec-europa/subsite-starterkit),
as suggested on the "Starting a new project" page.

First of all add the main Subsite Starterkit repository as a new remote: 

```
$ git remote add starterkit https://github.com/ec-europa/subsite-starterkit.git
```

Then fetch the newly added remote:

```
$ git fetch starterkit
```

At this point run the following command to discover which commit hash has been
tagged with `starterkit/2.0.0` (tags are prepended with `starterkit/` in order
to avoid conflicts with existing tags on forked subsite repositories):

```
$ git ls-remote --tags starterkit 
aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa   refs/tags/starterkit/2.0.0
bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb   refs/tags/starterkit/1.0.1
cccccccccccccccccccccccccccccccccccccccc   refs/tags/starterkit/1.0.0
```

After that just run:

```
$ git diff aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
```

This will give you the list of changes you need to apply to your current repository.
You can also merge the 2.0.0 tag into your current branch and solve conflicts manually.

Now you have Subsite Starterkit 2.0.0 on your project and you need a full re-build
in order to benefit from the new features.

In your repository root run:

```
$ composer update
```

Then run:

```
$ ./bin/phing build-dev
```
