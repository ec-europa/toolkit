## Upgrade subsite-starterkit to toolkit
   
<a href="http://www.youtube.com/watch?feature=player_embedded&v=cwGZilB3BjQ
" target="_blank"><img src="http://img.youtube.com/vi/cwGZilB3BjQ/0.jpg" 
alt="Upgrade screencast" width="50%" align="left" /></a>

This screencast explains how to upgrade a subsite-starterkit based project to
the composer package based toolkit. To complete the upgrade you need to execute
the 5 steps listed below in the upgrade steps. After the upgrade you might have
to fix some files that may have been altered by the upgrade script. It is best
to use a dedicated branch for the upgrade so your master branch stays unaffected
until the pull request has been merged into the master of reference.

If your project has no custom Phing targets and has not altered any composer
files that were provided by the subsite-starterkit you should not encounter any
problems during this upgrade process.

### Upgrade guide

#### Up-to-date branch

Check out new dedicated branch and make sure it is up to date with master of the
reference repository.

>```bash
> git checkout -b starterkit/upgrade
> git remote add reference https://github.com/ec-europa/<project-id>-reference.git
> git fetch reference
> git merge reference/master
>```

#### Upgrade steps

These are the 5 steps needed to complete the upgrade.

>```bash
> curl https://raw.githubusercontent.com/ec-europa/toolkit/master/includes/templates/subsite/composer.json > composer.json
> curl https://raw.githubusercontent.com/ec-europa/toolkit/master/includes/templates/subsite/build.xml > build.xml
> rm -rf ./vendor ./bin ./composer.lock
> composer install
> ./toolkit/phing toolkit-upgrade-starterkit
>```

#### Gitignore

After the upgrade is complete you should check what files have changed and that
your gitignore file does not allow to commit new files placed by the toolkit. If
you want to make sure you have a correct gitignore file you can download it from
the toolkit subsite templates folder.

>```bash
> git status
> curl https://raw.githubusercontent.com/ec-europa/toolkit/master/includes/templates/subsite/.gitignore > .gitignore
> git status
>```

#### Files renamed

Good to know is that there have been some files renamed. Here is a list of the
most important ones.

|subsite-starterkit|toolkit|Description|
|:---|:---|:---|
|./resources/phpcs-custom.xml|./phpcs-ruleset.xml|The phpcs exclusion rules defined by the project.| 
|./resources/build.custom.xml|./build.project.xml|Custom Phing build targets defined by the project.|
|./build.properties|./build.project.props|Properties defined by the project.|
|./build.properties.dist|./build.default.props|List of all available properties.|
|./build.properties.local|./build.develop.props|Local properties for credentials and developer settings.|

#### Build properties

During the upgrade your build.properties file will be renamed to
build.project.props. In toolkit this file is required for CI purposes and you
need a minimum of properties defined there:
[required.props](../includes/phing/props/required.props)

The toolkit is not backwards compatible with
subsite-starterkit so you might have to rename some properties. View a list
of old property names mapped to new property names here:
[deprecated.props](../includes/phing/build/help/deprecated.props).

#### Phpcs

After you completed the upgrade you also need to make sure your codebase is in
compliance with the new version of coder. To run PHPCS you can use a Phing
target:

>```bash
> ./toolkit/phing test-run-phpcs
> ./toolkit/phpcbf
> ./toolkit/phing test-run-phpcs
>```

#### Behat

Your ./tests folder is overridden by the `./toolkit/phing toolkit-starterkit-upgrade`
target. The new ./tests folder contains a couple of generic tests that we will
be using on CI tools. If you do not wish to use the generic tests and you have
provided your own you can checkout your own tests again.

>```bash
> rm -rf ./tests
> git checkout master ./tests
>```

After you have made sure all is working correctly you can commit and push your
upgrade. When it's ready for review create a pull request to the reference
repository and a MULTISITE ticket so it can be QA'ed.
