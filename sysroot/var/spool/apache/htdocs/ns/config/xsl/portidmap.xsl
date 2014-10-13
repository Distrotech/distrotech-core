<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template name="setporttype">
  <xsl:param name="type"/>

  <xsl:if test="$type = 'Async'">
    <xsl:text>0</xsl:text>
  </xsl:if>

  <xsl:if test="$type = 'ISDN'">
    <xsl:text>2</xsl:text>
  </xsl:if>

  <xsl:if test="$type = 'ISDN-V120'">
    <xsl:text>3</xsl:text>
  </xsl:if>

  <xsl:if test="$type = 'ISDN-V110'">
    <xsl:text>4</xsl:text>
  </xsl:if>

  <xsl:if test="$type = 'xDSL'">
    <xsl:text>16</xsl:text>
  </xsl:if>
</xsl:template>

<xsl:template match="Modem">
  <xsl:value-of select="concat(.,'&#x9;')"/>
  <xsl:value-of select="concat(position(),'&#x9;')"/>
  <xsl:value-of select="concat(@remote,'&#x9;')"/>
  <xsl:value-of select="concat(@local,'&#x9;')"/>

  <xsl:choose>
   <xsl:when test="@connection = 'Dialup'">
     <xsl:text>dial&#x9;</xsl:text>
   </xsl:when>
   <xsl:otherwise>
     <xsl:text>lease&#x9;</xsl:text>
   </xsl:otherwise>
  </xsl:choose>

  <xsl:value-of select="concat(@speed,'&#x9;')"/>
  <xsl:call-template name="setporttype">
    <xsl:with-param name="type" select="@type"/>
  </xsl:call-template>
  <xsl:text>&#x9;</xsl:text>

  <xsl:value-of select="@mtu"/>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:apply-templates select="/config/Radius/RAS/Modem"/>
</xsl:template>
</xsl:stylesheet>
