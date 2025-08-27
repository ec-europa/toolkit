Continuous integration
======================

To check the status of the continuous integration of your project, go to `CI/CD Platform`_.

A pipeline - created and maintained by DIGIT - is applied by default. It manages the code review of the code, runs all
the business tests on the repository and builds the site artifact for the deployment. Minimal customisation of this pipeline is possible by providing a file named **.opts.yml** in the root of your project folder. For more detailed
information on the pipelines you can visit `DevOps Pipelines`_.

Development Fork Testing
------------------------

Before creating a merge request and deploying, you can test your project in the pipeline. To run the CI test pipeline in development fork, push a branch or commits, or merge one branch into another.

Demo mode
---------

The pipeline allows an ephemeral environment to be spawned for a limited time. This is done by running a pipeline from an ephemeral-[x] branch in your project's reference repository. After the project is built, a tunnel opens that
gives you access to the build. The url can be found in the Environments tab in your project's reference repository of `CI/CD Platform`_

Container registry
------------------

Access to container registry from where you can pull latest image version of your website 1:1 –  to do that you can simply update your docker-compose.yml file with image reference. The history of deployed image can be found in the Environments tab in your project's reference repository of `CI/CD Platform`_

OpenEuropa registry
-------------------

Your custom modules can be stored and reused across projects. The corporate pipeline ensures development is done in a consistent manner. The pipeline is quite simple and very similar to what is used on Drupal.org. Once a module passes the pipeline and becomes installable, you can create a tag. For more information please visit OpenEuropa registry in `CI/CD Platform`_

Logs
----

Logs from various tools like Solr, CloudFront, Drupal, Apache are aggregated into one single `single logging platform <logging platform>`_.
Logs related to deployment are also connected to the platform. You just need to find a link with the appropriate filters inside the "logs" job in one of the deployment pipelines.
