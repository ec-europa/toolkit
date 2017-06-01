## Composer and Git hook scripts

Only scripts that are executable will be executed. Please inform the QA team when
you want to make use of this functionality. It is not in all cases allowed.

### Composer hooks:
You can add scripts to the `resources/composer/scripts/<hook-name>/` folder. These
scripts will be executed in alphabetical order for the hook in which folder they 
reside. The enabled composer hooks are:
> - resources
>   - composer
>     - scripts
>       - pre-install-cmd
>       - post-install-cmd
>       - pre-update-cmd
>       - post-update-cmd

An example implementation can be found in platform extensions like:
 - [ec-reps-platform](https://github.com/ec-europa/ec-reps-platform)
 - [ec-nems-platform](https://github.com/ec-europa/ec-nems-platform)

Where by copying it's script to `resources/composer/scripts/post-install-cmd/` it
will run an extra composer install and copy the needed source code to the `lib/`
folder of the subsite project on the composer post-install-cmd hook.

### Git hooks:
You can add scripts to the `resources/git/hooks/<hook-name>/`
directory for each hook defined in `.git/hooks/<hook-name>.sample`. Upon the
command `composer install` any folder that contains scripts will have its hook
activated. Available hooks are:
> - resources
>   - git
>     - hooks
>       - applypatch-msg
>       - commit-msg
>       - post-update
>       - pre-applypatch
>       - pre-commit
>       - prepare-commit-msg
>       - pre-push
>       - pre-rebase
>       - update

An example implementation can be found in `resources/git/hooks/pre-push/` after
you perform a composer install. Then there will be a symlink to a script from the
pfrenssen/phpcs-pre-push package which was linked by the post-install-cmd script
at `resources/composer/scripts/post-install-cmd/git-activate-pre-push-phpcs`.
