# Diffy Drush Commands

These commands are purposed to allow easy Diffy interaction before, during and
after deployments.

## Requirements
- Drush 8 >=
- `export DIFFY_API_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxx`

## Commands

### diffy-refresh-token

This command requires the **DIFFY_API_KEY** environment variable to be set. If
set correctly this command will save a Diffy token inside of the database under
the Drupal variable name: **diffy_token**. This token will be used for
subsequently executed Diffy commands.


### diffy-project-snapshot

This command allows you to take a snapshot of the current website IF the
**diffy_project_id** Drupal variable is defined. If not you need to provide it
as an argument. By default the environment option will be set to production.

This command will set Drupal variables **diffy_last_snapshot** and
**diffy_prev_snapshot** for easy usage afterwards of the `diffy-project-diff`
command.

`./vendor/bin/drush diffy-project-snapshot <project-id> --environment=production`

or

`./vendor/bin/drush diffy-project-snapshot`


### diffy-project-compare

This command will request a comparison between two different environments. To
use a baseline set please have a look at the `diffy-project-baseline` command.
The environments option can be of the following values: 'prod-stage', 'prod-dev'
, 'stage-dev', 'custom', 'baseline-stage', 'baseline-prod', 'baseline-dev'

`./vendor/bin/drush diffy-project-compare <project-id> --environments=baseline-prod`


### diffy-project-diff

This command will create a diff between two specified snapshots. If no snapshots
are provided in the command's options it will check and see if the
**diffy_last_snapshot** and **diffy_prev_snapshot** is available to take a diff
for.

`./vendor/bin/drush diffy-project-diff <project-id> --snapshot1=xxxx --snapshot2=xxxx`

or

`./vendor/bin/drush diffy-project-diff`


### diffy-project-baseline

This command will set a new baseline set for the project. You can specify which
snapshot you would want to take as baseline. If none is provided it will try to
fetch the last snapshot within the Drupal **diffy_last_snapshot** variable.

`./vendor/bin/drush diffy-project-baseline <project-id> <snaphost-id>`

or

`./vendor/bin/drush diffy-project-baseline`

## Example usage

### Before & after snapshots

Take a snapshot before the deployment. Wait until all screenshots are ready.
Deploy your site. Take a snapshot after the deployment. And request a diff
between the last two snapshot requests.

```
./vendor/bin/drush diffy-project-snapshot --environment=production
./vendor/bin/drush diffy-project-snapshot --environment=production
./vendor/bin/drush diffy-project-diff
```

### Before baseline & after comparison.

Take a snapshot before the deployment. Wait untill all screenshots are ready.
Set the snapshot as the baseline set. Deploy your site. Request a comparison
between your new baseline and production.
```
./vendor/bin/drush diffy-project-snapshot --environment=production
./vendor/bin/drush diffy-project-baseline
./vendor/bin/drush diffy-project-compare --environments=baseline-prod
```