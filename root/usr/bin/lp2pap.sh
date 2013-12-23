#!/bin/sh
# pap script for lp systems

chdir "/etc/lp/printers/`basename $0`"

if [ -r "$6" ]; then
    /usr/bin/pap -E "$6"
    exit $?
fi

exit 2
