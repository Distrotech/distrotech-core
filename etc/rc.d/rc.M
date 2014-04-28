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

if [ -e /etc/.install ];then
  exec /install/rc.d/rc.M
fi;

#Core services
for cservice in ipv6 ldap nfs interface;do
  if [ -x /etc/rc.d/rc.${cservice} ];then
    /etc/rc.d/rc.${cservice} > /dev/null 2>&1
  fi;
done

dbus-daemon --system

for mservice in /etc/rc.d/M.d/*;do
  ${mservice}
done

(/etc/rc.d/rc.hfax;/usr/libexec/t38modem_start) > /dev/null 2>&1 &

#Run AGI Agent
if [ -x /usr/sbin/asterisk ];then
   /var/lib/asterisk/agi-bin/phpagi-fast.php > /dev/null 2>&1
fi;
if [ ! -e /tmp/op_buttons.cfg.new ] || [ ! -e /tmp/op_style.cfg.new ];then
  /etc/asterisk/pannel/genbut.pl > /dev/null 2>&1 
fi

(/usr/sbin/cfrestore;
/usr/sbin/genconf;
if [ -x /etc/rc.d/rc.ppp ];then
  /etc/rc.d/rc.ppp &
 else
  /usr/sbin/servconfig
fi

while [ "`pidof racoon`" ] && [ "${RKCNT}" != "XXXXXX" ];do
  /usr/bin/killall racoon
  /usr/bin/sleep 1
  RKCNT=${RKCNT}X
done;
if [ "`/bin/pidof racoon`" ];then 
  /usr/bin/killall -9 racoon
fi;
rm /var/racoon/racoon.sock;)>/dev/null 2>&1

(/usr/sbin/conntrack -F
/usr/sbin/conntrack -F expect) > /dev/null 2>/dev/null

if [ -x /etc/rc.d/rc.local ];then
  /etc/rc.d/rc.local
fi;

#Start Cron Post Cleanup
/usr/libexec/startcron &

reset -Q
