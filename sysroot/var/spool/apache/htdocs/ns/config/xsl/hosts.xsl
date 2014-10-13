<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="fqdn" select="concat(/config/DNS/Config/Option[@option = 'Hostname'],'.',/config/DNS/Config/Option[@option = 'Domain'])"/>
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="extiface" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>
<xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'" />
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />

<!--
http://www.dpawson.co.uk/xsl/sect2/padding.html
-->

<xsl:template name="prepend-pad">
  <xsl:param name="padChar"/>
  <xsl:param name="padVar"/>
  <xsl:param name="length"/>
  <xsl:choose>
    <xsl:when test="string-length($padVar) &lt; $length">
      <xsl:call-template name="prepend-pad">
        <xsl:with-param name="padChar" select="$padChar"/>
        <xsl:with-param name="padVar" select="concat($padChar,$padVar)"/>
        <xsl:with-param name="length" select="$length"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="substring($padVar,string-length($padVar) - $length + 1)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="append-pad">
  <xsl:param name="padChar"/>
  <xsl:param name="padVar"/>
  <xsl:param name="length"/>
  <xsl:choose>
    <xsl:when test="string-length($padVar) &lt; $length">
      <xsl:call-template name="append-pad">
        <xsl:with-param name="padChar" select="$padChar"/>
        <xsl:with-param name="padVar" select="concat($padVar,$padChar)"/>
        <xsl:with-param name="length" select="$length"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="substring($padVar,string-length($padVar) - $length +1)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="showhost">
  <xsl:param name="host"/>
  <xsl:param name="ipaddr"/>
  <xsl:call-template name="append-pad">
    <xsl:with-param name="padChar" select="' '"/>
    <xsl:with-param name="padVar" select="$ipaddr"/>
    <xsl:with-param name="length" select="'20'"/>
  </xsl:call-template>
  <xsl:value-of select="$host"/>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template match="WWW">
  <xsl:call-template name="showhost">
    <xsl:with-param name="ipaddr" select="@ipaddr"/>
    <xsl:with-param name="host" select="."/>
  </xsl:call-template>
</xsl:template>

<xsl:template match="Link">
  <xsl:call-template name="showhost">
    <xsl:with-param name="ipaddr" select="$intip"/>
    <xsl:with-param name="host" select="concat(.,'.',/config/DNS/Config/Option[@option = 'DynZone'])"/>
  </xsl:call-template>
</xsl:template>

<xsl:template match="Host">
  <xsl:choose>
    <xsl:when test="not(contains(.,'.'))">
      <xsl:call-template name="showhost">
        <xsl:with-param name="ipaddr" select="@ipaddr"/>
        <xsl:with-param name="host" select="
          concat(translate(.,$uppercase, $smallcase),'.',$domain,' ',translate(.,$uppercase, $smallcase))"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="showhost">
        <xsl:with-param name="ipaddr" select="@ipaddr"/>
        <xsl:with-param name="host" select="."/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Interface">
  <xsl:call-template name="showhost">
    <xsl:with-param name="ipaddr" select="@ipaddr"/>
    <xsl:with-param name="host" select="concat(@name,'.',$domain,' ',@name)"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="fullconf">
  <xsl:apply-templates select="/config/Proxy/Redirect/WWW"/>
  <xsl:if test="/config/DNS/Config/Option[@option = 'DynZone'] != ''">
    <xsl:call-template name="showhost">
      <xsl:with-param name="ipaddr" select="$intip"/>
      <xsl:with-param name="host" select="/config/DNS/Config/Option[@option = 'DynZone']"/>
    </xsl:call-template>
    <xsl:apply-templates select="/config/IP/ADSL/Links/Link"/>
  </xsl:if>
  <xsl:apply-templates select="/config/DNS/Hosts/Host"/>
  <xsl:call-template name="showhost">
    <xsl:with-param name="ipaddr" select="'127.255.255.253'"/>
    <xsl:with-param name="host" select="concat('dummy0.',$domain,' dummy0')"/>
  </xsl:call-template>
  <xsl:call-template name="showhost">
    <xsl:with-param name="ipaddr" select="$intip"/>
    <xsl:with-param name="host" select="concat($fqdn,' ',$hname)"/>
  </xsl:call-template>
  <xsl:apply-templates select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and 
                                                               (text() != $intiface) and
                                                               (text() != $extiface)]"/>
</xsl:template>

<xsl:template match="/config">
  <xsl:call-template name="fullconf"/>
  <xsl:call-template name="showhost">
    <xsl:with-param name="ipaddr" select="'127.0.0.1'"/>
    <xsl:with-param name="host" select="'localhost'"/>
  </xsl:call-template>
</xsl:template>
</xsl:stylesheet>
