<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="/config">
  <xsl:text>Private-key-format: v1.2
Algorithm: 157 (HMAC_MD5)
Key: </xsl:text><xsl:value-of select="concat($key,$nl)"/>
</xsl:template>
</xsl:stylesheet>
