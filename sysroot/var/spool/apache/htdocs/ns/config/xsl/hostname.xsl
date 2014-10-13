<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="/config">
  <xsl:choose>
    <xsl:when test="/config/DNS/Config/Option[@option = 'Hostname'] = ''">
      <xsl:value-of select="/config/DNS/Config/Option[@option = 'Domain']"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="/config/DNS/Config/Option[@option = 'Hostname']"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>
</xsl:stylesheet>
