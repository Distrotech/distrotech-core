<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="sdomain" select="/config/DNS/Config/Option[@option = 'Search']"/>
<xsl:variable name="dyndns" select="/config/DNS/Config/Option[@option = 'DynZone']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>
<xsl:variable name="fqdn" select="concat($hname,'.',$domain)"/>
<xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'" />
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />

<xsl:template match="/config">
  <xsl:value-of select="concat('domain ',$domain,$nl)"/>
  <xsl:value-of select="concat('search ',$domain)"/>

  <xsl:if test="/config/FileServer/Setup/Option[@option = 'Security']  = 'ADS'">
    <xsl:value-of select="concat(' ',translate(/config/FileServer/Setup/Option[@option = 'ADSRealm'],$uppercase, $smallcase))"/>
  </xsl:if>

  <xsl:if test="($dyndns != '') and ($dyndns != $domain)">
    <xsl:value-of select="concat(' ',$dyndns)"/>
  </xsl:if>

  <xsl:if test="($sdomain != '')">
    <xsl:value-of select="concat(' ',$sdomain)"/>
  </xsl:if>

  <xsl:text>&#xa;</xsl:text>

  <xsl:text>nameserver ::1&#xa;</xsl:text>

  <xsl:if test="/config/IP/SysConf/Option[@option = 'PrimaryDns'] != ''">
    <xsl:value-of select="concat('nameserver ',/config/IP/SysConf/Option[@option = 'PrimaryDns'],$nl)"/>
  </xsl:if>

  <xsl:if test="/config/IP/SysConf/Option[@option = 'SecondaryDns'] != ''">
    <xsl:value-of select="concat('nameserver ',/config/IP/SysConf/Option[@option = 'SecondaryDns'],$nl)"/>
  </xsl:if>

  <xsl:variable name="proto" select="translate(@protocol, $uppercase, $smallcase)"/>
</xsl:template>
</xsl:stylesheet>
