#!/bin/sh

# Post install or update.

PHING=$(pwd)/bin/phing
if [ -f $PHING ] && [ -x $PHING ] ; then
  echo "Executing $1 ping targets."
  $PHING composer-$1-cmd -Dproject.basedir=$PROJECT -logger phing.listener.AnsiColorLogger -find >/dev/null
else
  echo "Phing unavailable."
fi
