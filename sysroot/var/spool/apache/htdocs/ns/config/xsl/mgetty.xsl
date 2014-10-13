<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="Modem">
  <xsl:value-of select="concat('port ',.,$nl)"/>
  <xsl:value-of select="concat('  speed ',@speed,$nl)"/>
  <xsl:value-of select="concat('  post-init-chat &quot;&quot; ATL0M0 OK',$nl)"/>
  <xsl:if test="@connection != 'Dialup'">
    <xsl:value-of select="concat('  direct y',$nl)"/>
  </xsl:if> 
  <xsl:value-of select="$nl"/>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>debug 4
data-only y

</xsl:text>
  <xsl:apply-templates select="/config/Radius/RAS/Modem"/>
</xsl:template>
</xsl:stylesheet>
