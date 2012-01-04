#!/bin/bash

if [ -e /var/run/safe_slapd.pid ] && [ ! -s /var/run/safe_slapd.pid ];then
  MAXT=""
  while [ -d /proc/`cat /var/run/safe_slapd.pid` ] && [ "${MAXT}" != "XXXXX" ];do
    kill -9 `cat /var/run/safe_slapd.pid`
    sleep 2
    MAXT=${MAXT}X
  done
fi;

if [ -e /var/run/slapd.pid ] && [ ! -s /var/run/slapd.pid ];then
  MAXT=""
  while [ -d /proc/`cat /var/run/slapd.pid` ] && [ "${MAXT}" != "XXXXX" ];do
    kill -9 `cat /var/run/slapd.pid`
    sleep 2
    MAXT=${MAXT}X
  done
fi;

if [ -e /var/run/nscd/nscd.pid ] && [ ! -s /var/run/nscd/nscd.pid ];then
  MAXT=""
  while [ -d /proc/`cat /var/run/nscd/nscd.pid` ] && [ "${MAXT}" != "XXXXX" ];do
    kill -9 `cat /var/run/nscd/nscd.pid`
    sleep 2
    MAXT=${MAXT}X
  done
fi;

#Set the basic NSS conf
diff /etc/nsswitch.conf.boot /etc/nsswitch.conf > /dev/null 2>&1
if [ $? != 0 ];then
  if [ -e /etc/nsswitch.conf.boot ] && [ -e /etc/nsswitch.conf.ldap ];then
    cp /etc/nsswitch.conf.boot /etc/nsswitch.conf
  fi;
fi

if [ -e /etc/.install ];then
  (/usr/sbin/sysreconf /install/default.conf.sysvars
  cp /tmp/netsentry.conf /var/spool/apache/htdocs/ns/config) > /dev/null 2>&1
fi;

reset -Q
