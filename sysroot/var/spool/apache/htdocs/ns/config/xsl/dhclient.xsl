<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="Interface">
  <xsl:choose>
    <xsl:when test="/config/IP/SysConf/Option[@option = 'Internal'] = .">
      <xsl:text>interface "</xsl:text><xsl:value-of select="."/><xsl:text>" {
  send host-name "</xsl:text><xsl:value-of select="/config/DNS/Config/Option[@option = 'Hostname']"/><xsl:text>";
  send fqdn.fqdn "</xsl:text><xsl:value-of select="/config/DNS/Config/Option[@option = 'Hostname']"/><xsl:text>";
  send fqdn.encoded on;
  send fqdn.server-update on;
  also request fqdn, dhcp6.fqdn;
  script "/usr/bin/dhclient-script";
}</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>interface "</xsl:text><xsl:value-of select="."/><xsl:text>" {
  script "/usr/bin/dhclient-script";
}</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>&#xa;&#xa;</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:apply-templates select="/config/IP/Interfaces/Interface"/>
</xsl:template>
</xsl:stylesheet>
