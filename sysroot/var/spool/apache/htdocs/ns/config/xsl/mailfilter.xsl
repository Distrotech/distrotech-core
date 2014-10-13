<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="Filter">
  <xsl:value-of select="concat(@policy,'&#x9;',@regex,'&#x9;',@log,'&#x9;',.,$nl)"/>
</xsl:template>

<xsl:template match="/config">
  <xsl:apply-templates select="/config/Email/Filters/Filter"/>
</xsl:template>
</xsl:stylesheet>
