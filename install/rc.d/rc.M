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

#Start cacheing resolver
if [ ! -e /etc/rndc.key ];then
  rndc-confgen -a -r /dev/urandom >/dev/null 2>&1
fi;
named

#Start dbus
if [ -e /var/run/dbus/pid ];then
  rm /var/run/dbus/pid
fi;
dbus-daemon --system

#Setup from the install config
/usr/sbin/sysreconf /install/default.conf.sysvars
cp /tmp/netsentry.conf /var/spool/apache/htdocs/ns/config

#Start Auto IP's
if [ -d /sys/class/net/br0 ];then
  IFACE=br0
 elif [ -d /sys/class/net/ethA ];then
  IFACE=ethA
fi;

dhclient -nw ${IFACE}
dhclient -6 -nw ${IFACE}

if [ -e /var/run//avahi-autoipd.${IFACE}.pid ];then
  avahi-autoipd --force-bind -D -w ${IFACE}
fi
avahi-autoipd --force-bind -D -w ${IFACE} &

#Avahi Daemon
if [ -e /var/run/avahi-daemon/pid ];then
  rm /var/run/avahi-daemon/pid
fi;
avahi-daemon -D
