#!/bin/sh

# Execute all scripts in the folder that matches the scripts filename.
# This execution script is merely a template which will
# be copied when there are git hooks defined.
HOOK=$(basename $0)
HOOK_DIR="resources/git/hooks/$HOOK"
if [ -d $HOOK_DIR ] ; then
  for SCRIPT in $HOOK_DIR/*
  do
    if [ -f $SCRIPT ] && [ -x $SCRIPT ] ; then
      $SCRIPT
    fi
  done
fi


