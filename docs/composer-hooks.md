# Composer hooks

## User guide

The toolkit allows you to hook into composer with Phing. These hooks
allow you to perform custom tasks before, during or after installation
of your project. Simply define your targets in your build properties:

```
# Composer hook phingcall target lists. Space separated only.
# -----------------------------------------------------------
composer.hook.post.install = toolkit-initialize
composer.hook.post.update =
composer.hook.pre.install =
composer.hook.pre.update =
```

## How it works

In the composer.json of the toolkit a bash script is called on each
hook. The script that wil be executed can be found in
`includes/composer/scripts/phingcalls.sh`. The script will call Phing
to retrieve the targets you want to execute. After which it will call
Phing a second time to execute the target list.