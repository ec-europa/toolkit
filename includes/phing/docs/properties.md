# Properties

There are 3 different sets of build properties files that you can use.
If you are unfamiliar with the purpose behind each different type of
properties file please open the descriptions and read what they are
designed for.

## build.default.props

This properties file contains the default settings, acts as a loading
mechanism and is an example file of what properties are available to
you. Upon the installation or update of the toolkit this file will be
placed in your repository.

## build.develop.props

This file will contain configuration which is unique to your development
environment. It is useful for specifying your database credentials and
the username and password of the Drupal admin user so they can be used
during the installation. Next to credentials you have many development
settings that you can change to your liking. Because these settings are
personal they should not be shared with the rest of the team. Make sure
you never commit this file.

## build.project.props

Always commit this file to your repository. This file is required for
all NextEuropa projects. Without it your build system will fail. It must
contain a minimum set of properties, like project.id, etc. The toolkit
will notify you if any properties are missing.