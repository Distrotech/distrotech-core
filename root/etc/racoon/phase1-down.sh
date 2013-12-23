#!/bin/sh

PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin

# Correctly flush automatically generated SAD and SPD
# This should go away the day racoon will properly do the job.

echo "
deleteall ${REMOTE_ADDR} ${LOCAL_ADDR} esp;
deleteall ${LOCAL_ADDR} ${REMOTE_ADDR} esp;
spddelete ${LOCAL_ADDR}/32[any] ${REMOTE_ADDR}/32[any] any
        -P out ipsec esp/transport//require;
spddelete ${REMOTE_ADDR}/32[any] ${LOCAL_ADDR}/32[any] any
        -P in ipsec esp/transport//require;
"|setkey -c
