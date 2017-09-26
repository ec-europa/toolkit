# Using Composer hooks

## How it works

In the composer.json of the toolkit a bash script is called on each
hook defined there. The script that wil be executed can be found in
`includes/composer/scripts/phingcalls.sh`. It will call Phing to
retrieve the targets you want to execute. After which it will call Phing
a second time to execute the target list.

## Setting up the targets

The toolkit allows you to hook into composer with Phing. These hooks
allow you to perform custom tasks before, during or after installation
of your project. Simply define your targets in your build properties:

```props
# Composer hook phingcall target lists. Space separated only.
# -----------------------------------------------------------
composer.hook.post.install = build-toolkit
composer.hook.post.update =
composer.hook.pre.install =
composer.hook.pre.update =
```

This post install hook will call the the following target to prepare the
toolkit for usage:

```xml
<target
    name="build-toolkit"
    description="Initializes toolkit and project directories."
    depends="
        toolkit-binary-link,
        toolkit-structure-generate">
    <echo msg="Toolkit successfully initialized." />
</target>
```