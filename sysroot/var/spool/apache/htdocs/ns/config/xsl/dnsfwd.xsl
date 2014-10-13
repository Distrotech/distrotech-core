<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="/config">
  <xsl:if test="(/config/IP/SysConf/Option[@option = 'PrimaryDns'] != '') or
                (/config/IP/SysConf/Option[@option = 'SecondaryDns'] != '')">
    <xsl:text>forward first;
forwarders {&#xa;</xsl:text>
    <xsl:if test="/config/IP/SysConf/Option[@option = 'PrimaryDns'] != ''">
      <xsl:value-of select="concat('&#9;',/config/IP/SysConf/Option[@option = 'PrimaryDns'],';&#xa;')"/>
    </xsl:if>
    <xsl:if test="/config/IP/SysConf/Option[@option = 'SecondaryDns'] != ''">
      <xsl:value-of select="concat('&#9;',/config/IP/SysConf/Option[@option = 'SecondaryDns'],';&#xa;')"/>
    </xsl:if>
    <xsl:text>};&#xa;</xsl:text>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>
