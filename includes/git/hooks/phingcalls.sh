#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PHING=".$DIR/../../bin/phing"
echo $PHING

# If phing is available.
if [ -f $PHING ] && [ -x $PHING ] ; then

  # Get phing targets.
  HOOK="git.hook.${1//-/.}"
  PHINGCALLS=$(\
    $PHING toolkit-hooks-git \
    -Dproject.basedir=$PROJECT \
    -Dproject.build.haltonerror.props.validate=false \
    -logger phing.listener.AnsiColorLogger \
    -find | grep -o "$HOOK=.*" | cut -f2- -d=\
  )

  # Execute targets, if any.
  if [ -n "$PHINGCALLS" ] ; then
    $PHING \
      $PHINGCALLS \
      -Dproject.basedir=$PROJECT \
      -Dproject.build.haltonerror.props.validate=false \
      -logger phing.listener.AnsiColorLogger \
      -find
  fi

else
  # Notify user that no phing targets can be executed for the hook.
  echo "Phing unavailable:"
  echo "No composer hooks will be executed for $1."
fi
