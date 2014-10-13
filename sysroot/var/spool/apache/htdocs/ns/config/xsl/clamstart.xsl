<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="/config">#!/bin/bash

CLAMTEST=`clamctrl PING 2> /dev/null`
if [ "$CLAMTEST" != "PONG" ];then
  /usr/sbin/clamctrl QUIT
  sleep 2
 elif [ "$1" != "START" ];then
  exit
fi;

CTEST=X
while [ "`pidof clamd`" ] &amp;&amp; [ "${CTEST}" != "XXXXXXXXXX" ];do
  if [ -e /var/run/clam.pid ];then
    kill `cat /var/run/clam.pid`
   else
    killall clamd
  fi;
  CTEST="${CTEST}X"
  sleep 1;
done

if [ "`pidof clamd`" ];then
  killall -9 clamd;
  sleep 2
fi;

if [ -S /tmp/clamd ];then
  rm /tmp/clamd> /dev/null 2>&amp;1
fi;

while [ "`pidof clamctrl`" ];do
  killall -9 clamctrl
done;


if [ ! -h /etc/clamd.conf ];then
  if [ -e /etc/clamd.conf ];then
    rm -rf /etc/clamd.conf
  fi;
  ln -s /etc/clamav.conf /etc/clamd.conf
fi;

/usr/sbin/clamd
</xsl:template>
</xsl:stylesheet>
