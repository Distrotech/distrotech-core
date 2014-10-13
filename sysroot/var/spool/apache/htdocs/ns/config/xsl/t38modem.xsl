<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>

<xsl:template match="/config">
  <xsl:text>#!/bin/bash

while [ "`pidof t38modem`" ];do
  killall t38modem
done;

sleep 10

rm /dev/ttyF*

</xsl:text>
  <xsl:value-of select="concat('/usr/bin/t38modem --listenport 1722 -u t38modem --old-asn -i ',$intip,' -g ',$intip,' -D ulaw -P alaw\',$nl)"/>
<xsl:text>                        -p +/dev/ttyF0,+/dev/ttyF1,+/dev/ttyF2,+/dev/ttyF3,+/dev/ttyF4,+/dev/ttyF5,+/dev/ttyF6,+/dev/ttyF7,+/dev/ttyF8,+/dev/ttyF9 &amp;

ln -s /var/spool/hylafax/etc/faxtty /var/spool/hylafax/etc/config.ttyF0
/usr/sbin/faxmodem ttyF0
ln -s /var/spool/hylafax/etc/faxtty /var/spool/hylafax/etc/config.ttyF1
/usr/sbin/faxmodem ttyF1
ln -s /var/spool/hylafax/etc/faxtty /var/spool/hylafax/etc/config.ttyF2
/usr/sbin/faxmodem ttyF2
ln -s /var/spool/hylafax/etc/faxtty /var/spool/hylafax/etc/config.ttyF3
/usr/sbin/faxmodem ttyF3
ln -s /var/spool/hylafax/etc/faxtty /var/spool/hylafax/etc/config.ttyF4
/usr/sbin/faxmodem ttyF4
ln -s /var/spool/hylafax/etc/faxtty /var/spool/hylafax/etc/config.ttyF5
/usr/sbin/faxmodem ttyF5
ln -s /var/spool/hylafax/etc/faxtty /var/spool/hylafax/etc/config.ttyF6
/usr/sbin/faxmodem ttyF6
ln -s /var/spool/hylafax/etc/faxtty /var/spool/hylafax/etc/config.ttyF7
/usr/sbin/faxmodem ttyF7
ln -s /var/spool/hylafax/etc/faxtty /var/spool/hylafax/etc/config.ttyF8
/usr/sbin/faxmodem ttyF8
ln -s /var/spool/hylafax/etc/faxtty /var/spool/hylafax/etc/config.ttyF9
/usr/sbin/faxmodem ttyF9
</xsl:text>
</xsl:template>
</xsl:stylesheet>
