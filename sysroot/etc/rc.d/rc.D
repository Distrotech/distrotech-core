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

export PATH=/sbin:/usr/sbin:/bin:/usr/bin

#Core services
for cservice in ipv6 ldap nfs interface;do
  if [ -x /etc/rc.d/rc.${cservice} ];then
    /etc/rc.d/rc.${cservice} > /dev/null 2>&1
  fi;
done

if [ ! -d /var/run/dbus ];then
  mkdir -p /var/run/dbus
fi;
dbus-daemon --system

if [ -x /etc/rc.d/rc.local ];then
  /etc/rc.d/rc.local
fi;

reset -Q
