<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:variable name='cr'><xsl:text>&#xd;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
<xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'"/>
<xsl:variable name="hname" select="translate(/config/DNS/Config/Option[@option = 'Hostname'],$smallcase,$uppercase)"/>

<xsl:template match="Share">
  <xsl:value-of select="concat('NET USE ',translate(@drive,$smallcase,$uppercase),': &quot;\\',$hname,'\',translate(.,$smallcase,$uppercase),'&quot;',$cr,$nl)"/>
</xsl:template>

<xsl:template match="Mapping">
  <xsl:value-of select="concat('NET USE ',translate(@drive,$smallcase,$uppercase),': &quot;\\',translate(.,$smallcase,$uppercase),'\',translate(@folder,$smallcase,$uppercase),'&quot;',$cr,$nl)"/>
</xsl:template>

<xsl:template match="/config">
  <xsl:choose>
    <xsl:when test="/config/FileServer/@profile = 'true'">
      <xsl:value-of select="concat('NET USE ',/config/FileServer/@homedir,': /HOME',$cr,$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('NET USE ',/config/FileServer/@homedir,': \\',$hname,'\homes',$cr,$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:if test="/config/FileServer/@sharedir != ''">
    <xsl:value-of select="concat('NET USE ',/config/FileServer/@sharedir,': &quot;\\',$hname,'\SHAREDFILES&quot;',$cr,$nl)"/>
  </xsl:if>
  <xsl:apply-templates select="/config/FileServer/Shares/Share[@drive != '']"/>
  <xsl:apply-templates select="/config/FileServer/Mappings/Mapping[@drive != '']"/>
  <xsl:if test="/config/IP/SysConf/Option[@option = 'NTPServer'] != ''">
    <xsl:value-of select="concat('NET TIME &quot;\\',$hname,'&quot; /SET /YES',$cr,$nl)"/>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>
