
Update Project Documentation
============================

Use docker to update the documentation locally

The Docker image comes with all dependencies pre-installed.

To update the documentation run the following command. This will update
the content inside the folder ‘docs’.

.. code-block::

   docker run --rm -v $(pwd):/data phpdoc/phpdoc run

Then push the changes generated.
