#!/bin/sh

# Execute all scripts in the folder that matches the argument.
DIRECTORY=$(dirname $0)
HOOK="$1"
if [ -d "$DIRECTORY/$HOOK" ] ; then
  for SCRIPT in $DIRECTORY/$HOOK/*
  do
    if [ -f $SCRIPT ] && [ -x $SCRIPT ] ; then
      $SCRIPT
    fi
  done
fi
