<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template name="gotfw">
  <xsl:text>#!/bin/bash

/sbin/iptables -F DYNAMICIPI
/sbin/iptables -F DYNAMICIPO
/sbin/iptables -t nat -F DYNAMICPRE

/sbin/iptables -A DYNAMICIPI -j SYSIN -s ${1}/${2} -d ${3}
/sbin/iptables -A DYNAMICIPI -j SYSIN -s ${1}/${2} -d </xsl:text><xsl:value-of select="$zcipaddr"/><xsl:text>
/sbin/iptables -A DYNAMICIPI -j MCASTIN -s ${1}/${2}
/sbin/iptables -A DYNAMICIPI -j ACCEPT -p udp --sport 137:138 -d ${4}/32 --dport 137:138
/sbin/iptables -A DYNAMICIPI -j ACCEPT -p udp --sport 1024:65535 -d ${4}/32 --dport 137:138
/sbin/iptables -A DYNAMICIPI -j DEFIN -d ${3}

/sbin/iptables -A DYNAMICIPO -j SYSOUT -d ${1}/${2} -s ${3}
/sbin/iptables -A DYNAMICIPO -j SYSOUT -d ${1}/${2} -s </xsl:text><xsl:value-of select="$zcipaddr"/><xsl:text>
/sbin/iptables -A DYNAMICIPO -j MCASTOUT -s ${1}/${2}
/sbin/iptables -A DYNAMICIPO -j ACCEPT -p udp --sport 137:138 -d ${4}/32 --dport 137:138
/sbin/iptables -A DYNAMICIPO -j ACCEPT -p udp --sport 1024:65535 -d ${4}/32 --dport 137:138
/sbin/iptables -A DYNAMICIPO -j DEFOUT -s ${3}

/sbin/iptables -t nat -A DYNAMICPRE -j DNAT -p tcp -d ${3} --sport 1024:65535 --dport 80 --to-destination </xsl:text><xsl:value-of select="$zcipaddr"/><xsl:text>:80
/sbin/iptables -t nat -A DYNAMICPRE -j REDIRECT -s ${1}/${2} -p tcp --sport 1024:65535 --dport 80 --to-port 3129
</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:choose>
    <xsl:when test="/config/IP/SysConf/Option[@option = 'Internal'] = /config/IP/SysConf/Option[@option = 'External']">
      <xsl:text>#!/bin/bash&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="gotfw"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>
</xsl:stylesheet>
