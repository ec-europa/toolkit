Components
===================

In the `QA Website <https://digit-dqa.fpfis.tech.ec.europa.eu>`_, you can find the components being controlled by our CI/CD.

`Components <https://digit-dqa.fpfis.tech.ec.europa.eu/package-reviews>`_

Mandatory module check
----
Ensure that packages marked as Mandatory in the QA website inventory are being used by the project.

You can check the `list of mandatory modules <https://digit-dqa.fpfis.tech.ec.europa.eu/package-reviews?f[0]=package_mandatory:1>`_.

* It is not possible to bypass this check.

Recommended module check
----
Ensure that packages marked as Recommended in the QA website inventory are being used by the project.

You can check the `list of recommended modules <https://digit-dqa.fpfis.tech.ec.europa.eu/package-reviews?f[0]=package_usage:3>`_.

* This step is in reporting mode.

Insecure module check
----
Uses the command ``composer audit`` command.

* It is possible to bypass this check using the environment variable ``QA_SKIP_INSECURE=true`` or by using the
token ``[SKIP_INSECURE]`` in the commit message.

Outdated module check
----
Uses the ``composer outdated`` command.

* It is possible to bypass this check using the token ``[SKIP_OUTDATED]`` in the commit message or by configuration
``toolkit.components.outdated.check: false``.

* It is also possible to bypass specific package in a specific version, example:

.. code-block::

    toolkit:
      components:
        outdated:
          ignores:
            - name: drupal/webform
              version: 1.1.1

Abandoned module check
----
Uses the ``composer outdated`` command.

* It is possible to bypass this check using the configuration ``toolkit.components.abandoned.check: false``.

Unsupported module check
----
Uses the `update_available_releases() <https://api.drupal.org/api/drupal/core%21modules%21update%21update.module/function/update_get_available/10>`_  in combination with `update_calculate_project_data() <https://api.drupal.org/api/drupal/core%21modules%21update%21update.compare.inc/function/update_calculate_project_data/8.0.x>`_ functions.

* It is possible to bypass this check using the configuration ``toolkit.components.unsupported.check: false``.

Evaluation module check
----
Uses the Quality Assurance packages inventory to validate the used components if they are whitelisted or blacklisted.

Using a set of vendors configured in the endpoint to validate the packages, it is possible to restrict the use of packages by project-id, type of
project or profile.

All ``metapackages`` and local packages are ignored.

Development module check
----
Uses the Quality Assurance packages inventory to validate that no development packages are used in the production environment.

Composer validation check
----
Performs a series of checks in the ``composer.json`` file.

Ensures that packages are not used in dev branches (like: ``^dev-*`` or ``*-dev$``).

Enforce the setting ``extra.enable-patching`` to be ``false``.

Enforce the setting ``extra.composer-exit-on-patch-failure`` to be ``true``.

Validate existing patches, by default only local and Drupal.org patches are allowed.

* It is possible to block remote patches from Drupal.org by using configuration ``toolkit.components.composer.drupal_patches: false``.
