<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="Interface">#!/bin/bash

/sbin/ip route add <xsl:value-of select="@nwaddr"/>/<xsl:value-of select="@subnet"/> dev ${1} src ${2} table Link
/sbin/ip addr add ::${2}/96 dev sit0
</xsl:template>

<xsl:template match="/config">
  <xsl:apply-templates select="/config/IP/Interfaces/Interface[ . = $hspot]"/>
</xsl:template>
</xsl:stylesheet>
