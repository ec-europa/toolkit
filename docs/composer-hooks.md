# Using Composer hooks

<big><table><thead><tr><th nowrap> [Testing the project](./testing-project.md#testing-the-project) </th><th width="100%" align="center"> [User guide](../README.md#user-guide) </th><th nowrap> [Using Git hooks](./git-hhooks.md#using-git-hooks) </th></tr></thead></table>

The toolkit allows you to hook into composer with Phing to run tasks
before, during or after installation of the project.

## How it works

In the composer.json of the toolkit a bash script is called on each
hook defined there. The script that wil be executed can be found in
`includes/composer/scripts/phingcalls.sh`. It will call Phing to
retrieve the targets you want to execute. After which it will call Phing
a second time to execute the target list. These hooks allow you to
perform custom tasks

## Configure targets

Simply define your targets in these build properties. They will be
called in the order in which you define them. They should be space
seperated.

```props
# Composer hook phingcall target lists. Space separated only.
# -----------------------------------------------------------
composer.hook.post.install = build-toolkit
composer.hook.post.update =
composer.hook.pre.install =
composer.hook.pre.update =
```