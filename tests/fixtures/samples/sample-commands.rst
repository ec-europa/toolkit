Commands
====

See bellow current list of available commands:

.. toolkit-block-commands

.. code-block::

 Available commands:
   completion                        Dump the shell completion script
   config                            Dumps the current configuration.
   help                              Display help for a command
   list                              List commands

.. toolkit-block-commands-end

Creating custom commands
----

To provide custom commands, make sure that your classes are loaded, for example using
PSR-4 namespacing set the autoload in the composer.json file.
