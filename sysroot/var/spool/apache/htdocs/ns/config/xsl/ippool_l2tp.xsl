<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="l2net" select="/config/IP/SysConf/Option[@option = 'L2TPNet']"/>

<xsl:template match="/config">
  <xsl:if test="$l2net != ''">
    <xsl:text>pool create pool_name=l2tp \

pool address add pool_name=l2tp first_addr=</xsl:text><xsl:value-of select="$nwaddr"/><xsl:text> \
	netmask=</xsl:text><xsl:value-of select="$netmask"/><xsl:text>
</xsl:text>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>
