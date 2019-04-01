# Using Git hooks

<big><table><thead><tr><th nowrap> [Using Composer hooks](./composer-hooks.md#using-composer-hooks) </th><th width="100%" align="center"> [User guide](../README.md#user-guide) </th><th nowrap> [Using Docker](./docker-environment.md#using-docker-environment) </th></tr></thead></table>

The toolkit allows you to hook into git events. This is useful for
example if you want to apply standards to commit messages or need to
perform coding standards before pushing your code.

## How it works

The toolkit provides two targets with which you can control the status
of your git hook scripts.

### 1. git-hook-enable
The execution of this target can be automatically triggered after toolkit
installation. The target will look for scripts in a folder inside of
`resources/git/hooks` that is named with the name of the git hook you wish to
use.

If any scripts are found in these locations the toolkit will simlink the scripts
into  `.git/hooks` location with the chosen hook name. If
no scripts are found in the folder it will remove any previous execution
script. Here is an example
of a script being placed in the `resources/git/hooks/pre-push` folder:

```
resources/
├── composer.json
├── composer.lock
├── devel.make
├── git
│   └── hooks
│       └── pre-push
│       └── prepare-commit-msg
└── site.make
```

> Note: the script itself should be made executable to work.

### 2. git-hook-disable
This target will disable the execution scripts so no more git hooks will
be invoked.
