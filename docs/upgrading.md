## Upgrade from 2.0.x to 3.0.x
   
Subsite Starterkit 3.0.0 introduces itself as a Composer package. In order
to provide that a new building procedure has been put in place. These
upgrade instructions assume that your subsite is a "git fork" of the old
repository at https://github.com/ec-europa/subsite-starterkit.

### 1.1 Phing upgrade

>```bash
> curl https://raw.githubusercontent.com/ec-europa/ssk/master/includes/templates/subsite/composer.json > composer.json
> curl https://raw.githubusercontent.com/ec-europa/ssk/master/includes/templates/subsite/build.xml > build.xml
> rm -rf ./vendor ./bin ./composer.lock
> composer install
> ./ssk/phing tookit-upgrade-starterkit
>```

The biggest structural change is in the lib folder. Because of the new symlink system the structure in the build folder will match the structure in the lib folder. To align all projects we request to make the following lib structure:
* libraries
* modules
  * custom
  * features
* profiles (platform only)
* src
* themes

Other tasks that need to happen:
- merge the projects gitignore with the new toolkit gitignore file. This
will probably happen through a build target.
- remove the build-custom target from the build.project.xml. Toolkit
should provide a new template if the original one wasn't altered.
- 

### 1.2 Manual process
Manually delete all files that are only specific to the starterkit.
Below is a list of files *to keep*. So anything not mentioned below should
be deleted.

<b>Starterkit 3.0.0 templates</b>: (fetch)
> 
>```bash
>- composer.json
>- build.xml
>- Jenkinsfile
>```

<b>Subsite specific files</b>: (to keep)
> 
>```bash
>- .git/
>- .gitattributes
>- .gitignore
>- build.properties
>- lib/features/*
>- lib/modules/*
>- lib/themes/*
>- resources/site.make
>- resources/composer.json
>- resources/composer.lock
>- tests/*
> ```

<b>Subsite specific files</b>: (to keep and rename)
> 
>```bash
>- resources/build.custom.xml => ../build.project.xml
>- resources/phpcs-custom.xml => ../phpcs-ruleset.xml
>```

### 1.3 Upgrade through upstream merge

If you are absolutely certain that you have no starterkit modifications in any other
files then we can let you try an upgrade path. But we do not guarantee a working
starterkit after you merge the branch. So if you decide to merge the upgrade branch,
please use an intermediary to forward a pull request so you can review it fully.

> ```
> $ git checkout -b intermediary
> $ git remote add starterkit https://github.com/ec-europa/subsite-starterkit.git
> $ git fetch starterkit
> $ git merge starterkit/upgrade
> ```

And last but not least we should remove the remote that has been replaced by the new
Subsite Starterkit package in your composer.json. Then you are ready to update the
new Subsite Starterkit for the first time.

> ```
> $ git remote rm starterkit
> $ composer update
> ```
