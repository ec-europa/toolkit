# Using Git hooks

<big><table><thead><tr><th nowrap> [Using Composer hooks](./composer-hooks.md#using-composer-hooks) </th><th width="100%" align="center"> [User guide](../README.md#user-guide) </th><th nowrap> [Using Docker](./using-docker.md#using-docker) </th></tr></thead></table>

The toolkit allows you to hook into git events. This is useful for
example if you want to apply standards to commit messages or need to
perform coding standards before pushing your code.

## How it works

The toolkit provides two targets with which you can control the status
of your git hook scripts.

### 1. toolkit-hooks-git-update
The execution of this target is automatically triggered after toolkit
installation. The target will look for scripts in a folder that is named
after the hook you wish to use. Two locations will be scanned:
- `vendor/ec-europa/toolkit/includes/git/hooks`
- `resources/git/hooks`

If any scripts are found in these locations the toolkit will copy a
bash script to the `.git/hooks` location with the chosen hook name. If
no scripts are found in the folder it will remove any previous execution
script. When you execute a git command that triggers a certain hook and
there is an execution script present it will execute all the scripts
contained in these folders in alpahnumerical order. Here is an example
of a script being placed in the `resources/git/hooks/pre-push` folder:

```
resources/
├── composer.json
├── composer.lock
├── devel.make
├── git
│   └── hooks
│       └── pre-push
│           └── phpcs -> ../../../../vendor/pfrenssen/phpcs-pre-push/pre-push
└── site.make
```

> Note: the script itself should be made executable to work.

### 2. toolkit-hooks-git-disable
This target will delete the execution scripts so no more git hooks will
be invoked.