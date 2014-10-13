<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>
<xsl:variable name="tdmproto" select="name(/config/IP/VOIP/*/Peers[starts-with(@context,'tdm')]/..)"/>

<xsl:template name="parentdial">
  <xsl:variable name="proto" select="/config/IP/VOIP/@protocol"/>
  <xsl:text>PARENTDIAL=</xsl:text>

  <xsl:if test="$proto = 'IAX'">
    <xsl:text>IAX2/parent</xsl:text>
  </xsl:if>

  <xsl:if test="$proto = 'SIP'">
    <xsl:text>SIP/parent</xsl:text>
  </xsl:if>

  <xsl:if test="$proto = 'H.323'">
    <xsl:text>OOH323</xsl:text>
  </xsl:if>

  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template name="parentcnt">
  <xsl:text>PARENTCNT=</xsl:text>
  <xsl:variable name="pcnt" select="count(/config/IP/VOIP/*[name() = /config/IP/VOIP/@protocol]/Peers[starts-with(@context,'parent')])"/>

  <xsl:choose>
    <xsl:when test="/config/IP/VOIP/@server != ''">
      <xsl:value-of select="$pcnt+1"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$pcnt"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template name="tdmgwdial">
  <xsl:text>TDMGWDIAL=</xsl:text>

  <xsl:if test="$tdmproto = 'IAX'">
    <xsl:text>IAX2/tdm</xsl:text>
  </xsl:if>

  <xsl:if test="$tdmproto = 'SIP'">
    <xsl:text>SIP/tdm</xsl:text>
  </xsl:if>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template name="tdmgwcnt">
  <xsl:text>TDMGWCNT=</xsl:text>
  <xsl:variable name="pcnt" select="count(/config/IP/VOIP/*[name() = $tdmproto]/Peers[starts-with(@context,'tdm')])"/>
  <xsl:value-of select="$pcnt"/>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:value-of select="concat('PARENTNUM=',/config/IP/VOIP/@username,$nl)"/>
  <xsl:value-of select="concat('HOSTNAME=',/config/DNS/Config/Option[@option = 'Hostname'],$nl)"/>
  <xsl:choose>
    <xsl:when test="/config/IP/VOIP/@server != ''">
      <xsl:call-template name="parentdial"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>PARENTDIAL=&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:call-template name="parentcnt"/>
  <xsl:call-template name="tdmgwdial"/>
  <xsl:call-template name="tdmgwcnt"/>
  <xsl:value-of select="concat('AGISERVER=',/config/SQL/Option[@option = 'MAsteriskServ'],$nl)"/>
  <xsl:value-of select="concat('DOMAIN=',/config/DNS/Config/Option[@option = 'Domain'],$nl)"/>
  <xsl:value-of select="concat('IPADDR=',$intip,$nl)"/>
</xsl:template>
</xsl:stylesheet>
