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

#Null Config
if [ ! -e /var/spool/apache/htdocs/ns/config/netsentry.conf ];then
  (/usr/sbin/sysreconf /install/default.conf.sysvars
  if [ ! -d /var/spool/apache/htdocs/ns/config ];then
    mkdir -p /var/spool/apache/htdocs/ns/config
  fi;
  cp /tmp/netsentry.conf /var/spool/apache/htdocs/ns/config) >/dev/null 2>&1
fi;

#Run DHCP
if [ ! -e /var/run/dhclient.pid ];then
  (if [ -d /sys/class/net/br0 ];then
    dhclient -nw br0
    dhclient -6 -nw -pf /var/run/dhclient6.pid br0
   elif [ -d /sys/class/net/ethA ];then
    dhclient -nw ethA
    dhclient -6 -nw -pf /var/run/dhclient6.pid ethA
  fi;) >/dev/null 2>&1 &
fi;

(while [ 1 ];do /usr/bin/setsid /sbin/agetty 38400 tty8 linux;done) &
(while [ 1 ];do /usr/bin/setsid /sbin/agetty 38400 tty9 linux;done) &
(while [ 1 ];do /usr/bin/setsid /sbin/agetty -nl /usr/libexec/shell 38400 tty2 linux;done) &
(while [ 1 ];do /usr/bin/setsid /sbin/agetty -nl /usr/libexec/shell 38400 tty3 linux;done) &

/usr/libexec/distrotech/sysconfig
clear
init 6
