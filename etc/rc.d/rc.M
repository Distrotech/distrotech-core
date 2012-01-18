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

#Set the ldap NSS conf
diff /etc/nsswitch.conf.ldap /etc/nsswitch.conf > /dev/null 2>&1
if [ $? != 0 ];then
  /usr/sbin/socktest 127.0.0.1 389 > /dev/null 2>&1
  if [ $? == 0 ];then
    if [ -e /etc/nsswitch.conf.boot ] && [ -e /etc/nsswitch.conf.ldap ];then
      if [ ! -e /etc/nsswitch.conf.local ];then  
        cp /etc/nsswitch.conf.ldap /etc/nsswitch.conf
       else
        cp /etc/nsswitch.conf.local /etc/nsswitch.conf
      fi;
    fi;
  fi;
fi

#ACPI Deamon
/usr/sbin/acpid > /dev/null 2>&1

if [ -x /etc/rc.d/rc.network ];then
  /etc/rc.d/rc.network
fi;

if [ -e /etc/.install ];then
  /usr/bin/setsid /sbin/agetty 38400 tty8 linux &
  /usr/bin/setsid /sbin/agetty 38400 tty9 linux &
  /usr/bin/setsid /sbin/agetty -nl /bin/bash 38400 tty2 linux &
  /install/sysconfig
  clear
  init 6
 else
  #Run RPC Portmap
  portmap

  #Start RPC
  echo 262144 > /proc/sys/net/core/rmem_default
  echo 262144 > /proc/sys/net/core/rmem_max
  chown 99 /var/lib/nfs
  chown -R 99 /var/lib/nfs/sm
  chown -R 99 /var/lib/nfs/sm.bak
  /usr/sbin/rpc.idmapd
  /usr/sbin/rpc.statd -p 32765 -o 32766
  /usr/sbin/rpc.mountd -p 32767
  /usr/sbin/rpc.nfsd -p 2049 32

  #Mount Remote File Blocks
  if [ -x /etc/rc.d/rc.mount ];then
    /etc/rc.d/rc.mount
  fi;
  /usr/sbin/exportfs -a
  mount -t nfs -a

  /usr/sbin/ippoold >/tmp/ippool.out 2>&1
  (/usr/bin/sleep 1
  for ippool in /etc/ippool/* ;do
    /usr/bin/ippoolconfig "config  restore  file=${ippool}"
  done) > /dev/null 2>&1

  if [ ! -e /etc/openl2tpd.conf ] && [ -e /etc/openl2tpd.conf.orig ];then
    cp /etc/openl2tpd.conf.orig /etc/openl2tpd.conf
  fi;
  if [ -e /var/run/openl2tpd.pid ];then
    rm /var/run/openl2tpd.pid
  fi;
  if [ -e /etc/openl2tpd.conf ];then
    openl2tpd -p ipsec.so > /dev/null 2>&1
  fi;

  #Start UP TDM Channels
  if [ ! -e /etc/.networksentry-lite ];then
    if [ -e /usr/sbin/dahdi_cfg ];then
      TDM_CFG="/usr/sbin/dahdi_cfg"
      TDM_REV="/etc/dahdi/systemr.conf";
     else
      TDM_CFG="/sbin/ztcfg"
      TDM_REV="/etc/zaptelr.conf";
    fi
    if [ -s ${TDM_REV} ];then
      (${TDM_CFG}
      /usr/bin/sleep 30
      ${TDM_CFG} -c ${TDM_REV}
      /usr/bin/sleep 30
      ${TDM_CFG}) > /dev/null 2>&1 &
     else
     ${TDM_CFG} > /dev/null 2>&1
    fi
  fi;

  #Start Webserver
  if [ -d /var/spool/apache/htdocs/ns/config ];then
    chown -R www.www /var/spool/apache/htdocs/ns/config
    chown -R nobody.nogroup /etc/squid
  fi;
  if [ -d /var/spool/apache/htdocs/mrtg ];then
    chown -R www.www /var/spool/apache/htdocs/mrtg
  fi;
  if [ ! -e /etc/.install ];then
     if [ -x /usr/sbin/webserver ];then
       /usr/sbin/webserver start >/dev/null 2>&1
     fi;
  fi;
  TST="";
  while [ "${TST}" != "XXXXXX" ] && [ ! -e /tmp/mysql.sock ];do
    sleep 1;
    TST=${TST}X;
  done
  if [ -e /tmp/mysql.sock ];then
    /usr/sbin/ulogd -d > /dev/null 2>&1
  fi

  #Start Up Proxy Server
  if [ ! -d /var/spool/squid/00 ];then
    if [ ! -d /var/spool/squid ];then
      mkdir /var/spool/squid
    fi;
    chown nobody.nogroup /var/spool/squid
    /usr/bin/squid -z > /dev/null 2>&1
   else
    /usr/bin/squid -z > /dev/null 2>&1
  fi;
  /usr/bin/RunCache > /dev/null 2>&1&
#  /usr/sbin/frox > /dev/null 2>&1 &

  #Run PGSQL And Start AGI Agent
  if [ ! -e /etc/.networksentry-lite ];then
    chmod 700 /etc/rc.d/rc.exchange
    /etc/rc.d/rc.exchange > /dev/null 2>&1
    if [ -x /usr/sbin/asterisk ];then
      /var/lib/asterisk/agi-bin/phpagi-fast.php > /dev/null 2>&1
    fi;
    if [ ! -e /tmp/op_buttons.cfg.new ] || [ ! -e /tmp/op_style.cfg.new ];then
      /etc/asterisk/pannel/genbut.pl > /dev/null 2>&1 
    fi
  fi;

  #Start Email
  if [ -x /etc/rc.d/rc.mail ];then
    if [ ! -d /var/spool/mqueue.in ];then
      mkdir /var/spool/mqueue.in;
      chmod 750 /var/spool/mqueue.in
      chown root.root /var/spool/mqueue.in
    fi;
    if [ -e /var/run/dovecot/master.pid ];then
      rm /var/run/dovecot/master.pid
    fi;
    /etc/rc.d/rc.mail > /dev/null 2>&1 &
   else
    /usr/sbin/sendmail -bd > /dev/null 2>&1 &
    /usr/sbin/popper > /dev/null 2>&1
  fi;

  #SAMBA
  if [ ! -e /etc/.firstboot ];then 
    if [ ! -e /etc/samba/private/secrets.tdb.bak ];then
      /usr/bin/tdbbackup /etc/samba/private/secrets.tdb > /dev/null 2>&1
     else
      /usr/bin/tdbbackup -v /etc/samba/private/secrets.tdb  > /dev/null 2>&1
    fi;
    /usr/sbin/winbindd &
    /usr/sbin/nmbd -D &
    /usr/sbin/smbd -D &
  fi;

  #Set Echo Coefficients
  if [ -d /proc/dahdi ];then
    if [  "`cat /proc/dahdi/* 2>/dev/null |grep FXSKS`" ];then
      if [ -e /etc/fxotune.conf ];then
        /usr/sbin/fxotune -s > /dev/null 2>&1
       else
        echo "Initiating ZAP FXO Coefficients";
        /usr/sbin/fxotune -i 0
      fi;
    fi;
  fi;

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
  rm /var/spool/cron/crontabs/* >/dev/null 2>&1
  if [ -e /usr/libexec/startcron ];then
    /usr/libexec/startcron &
   else
    /usr/sbin/crond >> /var/log/cron 2>&1
  fi;
  if [ -e /root/crontab ];then
    /usr/bin/crontab /root/crontab -u root
  fi;
  while [ "`pidof named`" ];do
    rndc stop
    if [ $? != 0 ];then
      killall -9 named
    fi;
    sleep 1
  done
  killall -9 named > /dev/null 2>&1
  sleep 1
  named
  killall -9 squid > /dev/null 2>&1
  /usr/sbin/clamupdate >/dev/null 2>&1 &
  if [ -e /etc/no-ip2.conf ];then
    /usr/sbin/noip2 > /dev/null 2>&1
  fi;
  #Start Super Daemon
  if [ ! "`pidof inetd`" ];then
    if [ -x /usr/sbin/inetd ];then
      /usr/sbin/inetd > /dev/null 2>&1
     else
      /usr/libexec/inetd > /dev/null 2>&1
    fi;
  fi;
  /usr/sbin/conntrack -F
  /usr/sbin/conntrack -F expectation

  #Start Linux Call Router
#  if [ -x /usr/sbin/lcr ];then
#    /usr/sbin/lcr fork > /dev/null 2>&1
#  fi;

  #Enable Advanced Routing
  if [ ! -e /etc/zebra/Quagga.conf ];then
    cp /etc/zebra/Zebra.conf.orig /etc/zebra/Quagga.conf
  fi;
  (for prog in zebra ripd ospfd bgpd isisd;do
    rm /var/run/$prog.*
    $prog -d
  done;
  /usr/bin/vtysh -b) > /dev/null 2>&1

  if [ -x /etc/rc.d/rc.local ];then
    /etc/rc.d/rc.local
  fi;
  if [ -x /etc/rc.d/rc.fix ];then
    /etc/rc.d/rc.fix
    rm /etc/rc.d/rc.fix
  fi;
  reset -Q
fi;
