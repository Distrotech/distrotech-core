<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="extint" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>
<xsl:variable name="is_modem" select="count(/config/IP/ADSL/Links/Link) or
                                      /config/IP/Sysonf/Option[@option = 'External'] = 'Dialup' or
                                      /config/IP/Dialup/Option[@option = 'Connection'] = 'ADSL'"/>

<xsl:template match="Link">
  <xsl:value-of select="concat($nl,'#Starting Up ',.,' Link',$nl)"/>
  <xsl:value-of select="concat('if [ ! -d /sys/class/net/ppp',position(),' ];then',$nl)"/>
  <xsl:value-of select="concat('  /etc/ifconf/pppup.ppp',position(),' &amp;',$nl)"/>
  <xsl:value-of select="concat('fi;',$nl)"/>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>#!/bin/bash

(flock -w 20 10 || exit
#Kill Any Zombie Dialup Links
CHATPID=`/bin/pidof chat`
if [ "$CHATPID" ];then
  ps ax |grep chat |awk '$3 == "Z" {print "killall pppd;sleep 5;killall -9 pppd"}' |sh
  flock -u 10
  exit
fi;

</xsl:text>

  <xsl:if test="($extcon = 'ADSL') or ($extint = 'Dialup')">
    <xsl:text>#Starting Primary Link
/usr/sbin/servconfig
if [ ! -d /sys/class/net/ppp0 ];then
  /etc/ifconf/pppup.ppp0 &amp;
fi;
</xsl:text>
  </xsl:if>

  <xsl:apply-templates select="/config/IP/ADSL/Links/Link"/>
  
  <xsl:if test="$is_modem">
    <xsl:text>&#xa;#Apply System Config&#xa;/usr/sbin/servconfig&#xa;</xsl:text>
  </xsl:if>

  <xsl:text>
#Check And Restart GRE Tunnels
/etc/rc.d/rc.tunnels

flock -u 10
) 10>/var/run/netsentry-pppup
rm /var/run/netsentry-pppup
</xsl:text>
</xsl:template>
</xsl:stylesheet>
