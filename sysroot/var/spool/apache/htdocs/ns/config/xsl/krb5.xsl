<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />
<xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'" />
<xsl:variable name="adserv" select="translate(/config/FileServer/Setup/Option[@option = 'ADSServer'],$uppercase,$smallcase)"/>
<xsl:variable name="adsdom" select="translate(/config/FileServer/Setup/Option[@option = 'ADSRealm'],$smallcase,$uppercase)"/>
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>

<xsl:template name="parseserv">
  <xsl:param name="servers"/>
  <xsl:variable name="curserv" select="substring-before($servers,' ')"/>
  <xsl:variable name="nextserv" select="substring-after($servers,' ')"/>
  
  <xsl:if test="$curserv != ''">
    <xsl:value-of select="concat('                kdc = ',$curserv,'.',translate($adsdom,$uppercase,$smallcase),$nl)"/>
  </xsl:if>

  <xsl:if test="$nextserv != ''">
    <xsl:call-template name="parseserv">
      <xsl:with-param name="servers" select="$nextserv"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>[libdefaults]
        default_realm = </xsl:text><xsl:value-of select="$adsdom"/>
  <xsl:if test="$adserv != ''">
    <xsl:text>&#xa;[realms]&#xa;        </xsl:text>
    <xsl:value-of select="$adsdom"/><xsl:text> = {&#xa;</xsl:text>
    <xsl:call-template name="parseserv">
      <xsl:with-param name="servers" select="concat($adserv,' ')"/>
    </xsl:call-template>
    <xsl:text>        }&#xa;</xsl:text>
  </xsl:if>
  <xsl:if test="$adsdom != translate($domain,$smallcase,$uppercase)">
    <xsl:text>
[domain_realm]
	.</xsl:text><xsl:value-of select="concat($domain,' = ',$adsdom,$nl)"/>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>
