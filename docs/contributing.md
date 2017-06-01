## Contributing

### Contributing as non-maintainer:
1. Create a **multisite ticket** with the following values:
  * A short but descriptive title starting with Feature: , Bug:  or Task: .
  * An extensive description of what is the exact problem or requested functionality.
  * The correct ticket type, New feature, Bug or Task.
  * For fix versions enter subsite-starterkit and yourproject.
  * Add a link to the repository url of your subsite-starterkit fork on github.
2. In your fork of the subsite-starterkit create a branch called
`{type}/{TICKETNUMBER}` from within the `develop` branch:
  * Where **{type}** stands for feature, bugfix or task, always in lowercase.
  * Where **{TICKETNUMBER}** stands for MULTISITE-XXXXX, always in uppercase.
3. Create a pull request from that branch of your forked subsite-starterkit:
  * Always make that pull request to the `develop` branch of the subsite-starterkit.
  * Always allow maintainers of the project to commit to the branch of your fork.
4. Add a link to the pull request on the multisite ticket.

**Notes:**

> external pull requests always have to be made to the `develop` branch. The
maintainers may choose to reallocate the pull request to the next minor release
branch `release/2.X`. Others will be merged straight into `develop` and frequently
released with a new revision tag number `starterkit/2.1.X`.

> the same workflow applies for contributions to the
[qa-automation](https://github.com/ec-europa/qa-automation) tools
that are used in the subsite-starterkit.


### Contributing as a maintainer:
1. Create a **multisite ticket** with the following values:
  * A short but descriptive title starting with Feature: , Bug:  or Task: .
  * An extensive description of what is the exact problem or requested functionality.
  * The correct ticket type, New feature, Bug or Task.
  * For fix versions enter subsite-starterkit and projects.
  * Add a link to the repository url of the subsite-starterkit on github.
2. Add a branch called `{type}/{TICKETNUMBER}` from within the `develop` branch:
  * Where **{type}** stands for feature, bugfix or task, always in lowercase.
  * Where **{TICKETNUMBER}** stands for MULTISITE-XXXXX, always in uppercase.
3. Create a pull request to the `develop` or `release/2.X` branch accordingly.
4. Add a link to the pull request on the multisite ticket.

**Notes:**

> to work on an external pull request you have to clone the forked repository
mentioned on the ticket and checkout the correct branch.

> the same workflow applies for contributions to the
[qa-automation](https://github.com/ec-europa/qa-automation) tools that are used in 
the subsite-starterkit. Additions to the QualityAssurancTask.php class have to be
tested on the subsite-starterkit by creating a `test/{TICKETNUMBER}` branch where
you make the following updates:
  * Change the qa-automation version in composer.json to "dev-{type}/{TICKETNUMBER}".
  * Update the composer.lock file by running "composer update".
  * Add changes to the example module, feature or theme to test the improved class.

