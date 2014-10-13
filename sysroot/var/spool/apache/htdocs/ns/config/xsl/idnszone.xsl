<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template name="toarpa">
  <xsl:param name="prefix"/>
  <xsl:param name="revdom"/>
  <xsl:variable name="next" select="substring-after($prefix,':')"/>
  <xsl:variable name="cur" select="substring-before($prefix,':')"/>

  <xsl:choose>
    <xsl:when test="$cur != ''">
      <xsl:if test="string-length($cur) = '4'">
        <xsl:call-template name="toarpa">
         <xsl:with-param name="prefix" select="$next"/>
         <xsl:with-param name="revdom" select="concat(substring($cur,4,1),'.',substring($cur,3,1),'.',substring($cur,2,1),'.',substring($cur,1,1),'.',$revdom)"/>
        </xsl:call-template>
      </xsl:if>
      <xsl:if test="string-length($cur) = '3'">
        <xsl:call-template name="toarpa">
         <xsl:with-param name="prefix" select="$next"/>
         <xsl:with-param name="revdom" select="concat(substring($cur,3,1),'.',substring($cur,2,1),'.',substring($cur,1,1),'.0.',$revdom)"/>
        </xsl:call-template>
      </xsl:if>
      <xsl:if test="string-length($cur) = '2'">
        <xsl:call-template name="toarpa">
         <xsl:with-param name="prefix" select="$next"/>
         <xsl:with-param name="revdom" select="concat(substring($cur,2,1),'.',substring($cur,1,1),'.0.0.',$revdom)"/>
        </xsl:call-template>
      </xsl:if>
      <xsl:if test="string-length($cur) = '1'">
        <xsl:call-template name="toarpa">
         <xsl:with-param name="prefix" select="$next"/>
         <xsl:with-param name="revdom" select="concat(substring($cur,1,1),'.0.0.0.',$revdom)"/>
        </xsl:call-template>
      </xsl:if>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat($revdom,'ip6.arpa',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Interface6">
  <xsl:call-template name="toarpa">
    <xsl:with-param name="prefix" select="concat(@prefix,':')"/>
  </xsl:call-template>
</xsl:template>

<xsl:template match="Reverse">
  <xsl:value-of select="concat(.,$nl)"/>
</xsl:template>

<xsl:template match="Domain">
  <xsl:value-of select="concat(@domain,$nl)"/>
</xsl:template>

<xsl:template match="/config">
  <xsl:apply-templates select="/config/IP/Interfaces/Interface6"/>
  <xsl:if test="/config/DNS/Config/Option[@option = 'Auth'] = 'true'">
    <xsl:apply-templates select="/config/DNS/InAddr/Reverse"/>
    <xsl:value-of select="concat(/config/DNS/Config/Option[@option = 'Domain'],$nl)"/>
  </xsl:if>
  <xsl:apply-templates select="/config/DNS/Hosted/Domain[@key != '']"/>
</xsl:template>
</xsl:stylesheet>
