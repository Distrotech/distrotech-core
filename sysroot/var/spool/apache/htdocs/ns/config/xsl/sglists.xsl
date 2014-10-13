<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="ProxyFilter[@type = 'Keyword']">
  <xsl:value-of select="."/>
  <xsl:if test="last() != position()">
    <xsl:text>|</xsl:text>
  </xsl:if>
</xsl:template>

<xsl:template match="ProxyFilter[(@type = 'URL') or (@type = 'Domain')]">
  <xsl:value-of select="concat(.,$nl)"/>
</xsl:template>

<xsl:template match="/config">
  <xsl:if test="$type = 'Keyword'">
    <xsl:text>(^|[.-\.\?&amp;+=/_]|\w)(</xsl:text>
  </xsl:if>
  <xsl:apply-templates select="/config/Proxy/List/ProxyFilter[(@type = $type) and (@filter = $filter)]"/>
  <xsl:if test="$type = 'Keyword'">
    <xsl:text>)(\w|[.-\.\?&amp;+=/_]|$)&#xa;</xsl:text>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>
