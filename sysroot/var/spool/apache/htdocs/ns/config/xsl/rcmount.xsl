<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="Mount">
  <xsl:text>if [ -e "</xsl:text><xsl:value-of select="@bind"/><xsl:text>" ];then
  mv "</xsl:text><xsl:value-of select="@bind"/><xsl:text>" "</xsl:text><xsl:value-of select="@bind"/><xsl:text>-`date "+%d%m%Y"`"
  ln -s "/mnt/autofs/</xsl:text><xsl:value-of select="@folder"/><xsl:text>" "</xsl:text><xsl:value-of select="@bind"/><xsl:text>"
 else
  if [ -h "</xsl:text><xsl:value-of select="@bind"/><xsl:text>" ];then
    rm "</xsl:text><xsl:value-of select="@bind"/><xsl:text>"
    ln -s "/mnt/autofs/</xsl:text><xsl:value-of select="@folder"/><xsl:text>" "</xsl:text><xsl:value-of select="@bind"/><xsl:text>"
   else
    ln -s "/mnt/autofs/</xsl:text><xsl:value-of select="@folder"/><xsl:text>" "</xsl:text><xsl:value-of select="@bind"/><xsl:text>"
  fi;
fi;
</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>#!/bin/bash

#Stop Automount
AMPID=`/bin/pidof automount`
if [ "$AMPID" ];then
  while [ "$AMPID" ] &amp;&amp; [ "$KFLAG" != "aaaaaaaaaaaaaaa" ];do
    killall -USR2 automount
    sleep 1
    AMPID=`/bin/pidof automount`
    KFLAG=a$KFLAG
  done
fi;

#Allow CD To Be Ejected
echo 0 > /proc/sys/dev/cdrom/lock

if [ ! -d /mnt/autofs ];then
  mkdir /mnt/autofs
fi;

if [ ! -d /var/run/autofs ];then
  mkdir /var/run/autofs
fi;

#Start Automounter
/usr/sbin/automount

#Bind Mounts
</xsl:text>
  <xsl:apply-templates select="/config/NFS/Mounts/Mount[(@bind != '-') and (@bind != '')]"/>
</xsl:template>
</xsl:stylesheet>
