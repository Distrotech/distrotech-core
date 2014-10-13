<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>
<xsl:variable name="extint" select="/config/IP/SysConf/Option[@option = 'External']"/>

<xsl:template name="backup3g">
  <xsl:text>#!/bin/bash

(flock -w 20 10 || exit
sleep 5

if [ ! -e /dev/gsmmodem ] &amp;&amp; [ -e /dev/tts/GSN0 ];then
  ln -s /dev/tts/GSM0 /dev/gsmmodem
 elif [ ! -e /dev/gsmmodem ];then
  exit -1
fi;

date > /var/log/pppd.log.3g
/usr/sbin/pppd /dev/gsmmodem connect "/usr/sbin/chat -v -f /etc/ppp/diald.3g" lock maxfail 5 unit 10 logfile /var/log/pppd.log.3g linkname 3g ipparam 3g nodefaultroute noauth persist nomultilink nodetach usepeerdns
flock -u 10
) 10>/var/lock/ppp10.lock

</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:choose>
    <xsl:when test="($extint = 'Dialup') and ($extcon = '3G')">
      <xsl:text>#!/bin/bash&#xa;&#xa;sleep 5&#xa;exec /etc/ifconf/pppup.ppp0 $@&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="backup3g"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>
</xsl:stylesheet>
