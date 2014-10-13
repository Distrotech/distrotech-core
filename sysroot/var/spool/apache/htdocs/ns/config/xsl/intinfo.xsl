<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="extint" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>

<xsl:template name="outputint">
  <xsl:param name="iface"/>
  <xsl:param name="ingress"/>
  <xsl:param name="egress"/>
  <xsl:param name="count"/>
  <xsl:choose>
    <xsl:when test="($egress != '') and ($egress &gt; 0)">
      <xsl:value-of select="concat('$ints[&quot;',$iface,'&quot;]=',$egress div 8,';',$nl)"/>
      <xsl:value-of select="concat('$imq[&quot;',$iface,'&quot;]=',$count,';',$nl)"/>
      <xsl:value-of select="concat('$imqmax[',$count,']=',$ingress div 8,';',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('$ints[&quot;',$iface,'&quot;]=0;',$nl)"/>
      <xsl:value-of select="concat('$imq[&quot;',$iface,'&quot;]=',$count,';',$nl)"/>
      <xsl:value-of select="concat('$imqmax[',$count,']=0;',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Link">
  <xsl:call-template name="outputint">
    <xsl:with-param name="iface" select="concat('ppp',position())"/>
    <xsl:with-param name="count" select="position()"/>
    <xsl:with-param name="ingress" select="@bwin"/>
    <xsl:with-param name="egress" select="@bwout"/>
  </xsl:call-template>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>&lt;%&#xa;</xsl:text>
  <xsl:choose>
    <xsl:when test="($extint = 'Dialup') or ($extcon = 'ADSL')">
      <xsl:call-template name="outputint">
        <xsl:with-param name="iface" select="'ppp0'"/>
        <xsl:with-param name="count" select="'0'"/>
        <xsl:with-param name="ingress" select="/config/IP/SysConf/Option[@option = 'Ingress']"/>
        <xsl:with-param name="egress" select="/config/IP/SysConf/Option[@option = 'Egress']"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="outputint">
        <xsl:with-param name="iface" select="$extint"/>
        <xsl:with-param name="count" select="'0'"/>
        <xsl:with-param name="ingress" select="/config/IP/SysConf/Option[@option = 'Ingress']"/>
        <xsl:with-param name="egress" select="/config/IP/SysConf/Option[@option = 'Egress']"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:apply-templates select="/config/IP/ADSL/Links/Link"/>
  <xsl:text>%&gt;&#xa;</xsl:text>
</xsl:template>
</xsl:stylesheet>
