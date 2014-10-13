<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="fqdn" select="concat(/config/DNS/Config/Option[@option = 'Hostname'],'.',/config/DNS/Config/Option[@option = 'Domain'])"/>

<xsl:template match="/config">
  <xsl:text>DatabaseMirror database.clamav.net
MaxAttempts 3
Checks 12
HTTPProxyServer </xsl:text><xsl:value-of select="$fqdn"/><xsl:text>
HTTPProxyPort 3128
</xsl:text>
</xsl:template>
</xsl:stylesheet>
