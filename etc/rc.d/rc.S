#!/bin/bash

#    Copyright (C) 2002  <Gregory Hinton Nietsky>
#    Copyright (C) 2005  <ZA Telecomunications>
#
#    This program is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program; if not, write to the Free Software
#    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

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
