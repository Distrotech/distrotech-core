<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="extint" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="intint" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>

<xsl:template match="Interface6">
  <xsl:value-of select="concat('&#x9;prefix ',@prefix,'::/64 {',$nl)"/>
  <xsl:text>		AdvOnLink on;&#xa;</xsl:text>
  <xsl:text>		AdvAutonomous on;&#xa;</xsl:text>
  <xsl:text>		AdvRouterAddr off;&#xa;</xsl:text>
  <xsl:text>	};&#xa;</xsl:text>
</xsl:template>

<xsl:template name="radvman">
  <xsl:param name="iface"/>

  <xsl:choose>
    <xsl:when test="count(/config/IP/Interfaces/Interface6[( . = $iface) and (@dhcpstart != '') and (@dhcpend != '')]) &gt; 0">
      <xsl:text>	AdvManagedFlag on;&#xa;</xsl:text>
      <xsl:text>	AdvOtherConfigFlag on;&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>	AdvManagedFlag off;&#xa;</xsl:text>
      <xsl:text>	AdvOtherConfigFlag off;&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="radvdconf">
  <xsl:param name="iface"/>
  <xsl:param name="sncnt"/>

  <xsl:if test="(count(/config/IP/Interfaces/Interface6[ . = $iface]) &gt; 0) or
                ($baseprefix != '')">
    <xsl:value-of select="concat('interface ',$iface,' {',$nl)"/>
    <xsl:text>	AdvSendAdvert on;&#xa;</xsl:text>
    <xsl:call-template name="radvman">
      <xsl:with-param name="iface" select="$iface"/>
    </xsl:call-template>
    <xsl:text>	MaxRtrAdvInterval 300;&#xa;</xsl:text>

    <xsl:if test="$baseprefix != ''">
      <xsl:value-of select="concat('&#x9;prefix ',$baseprefix,':',$sncnt,'::/64 {',$nl)"/>
      <xsl:text>		AdvOnLink on;&#xa;</xsl:text>
      <xsl:text>		AdvAutonomous on;&#xa;</xsl:text>
      <xsl:text>		AdvRouterAddr off;&#xa;</xsl:text>
      <xsl:if test="($extcon = 'ADSL') or ($extint = 'Dialup')">
        <xsl:text>		Base6to4Interface ppp0;&#xa;</xsl:text>
        <xsl:if test="($extcon = 'ADSL') or ($extint = 'Dialup')">
          <xsl:text>		AdvValidLifetime 600;&#xa;</xsl:text>
          <xsl:text>		AdvPreferredLifetime 300;&#xa;</xsl:text>
        </xsl:if>
      </xsl:if>
      <xsl:text>	};&#xa;</xsl:text>
    </xsl:if>

    <xsl:apply-templates select="/config/IP/Interfaces/Interface6[ . = $iface]"/>
    <xsl:text>};&#xa;&#xa;</xsl:text>
  </xsl:if>
</xsl:template>

<xsl:template name="setupiface">
  <xsl:param name="iface"/>
  <xsl:param name="sncnt"/>
  <xsl:if test="(/config/IP/WiFi[. = $iface]/@type != 'Hotspot') or (name(/config/IP/WiFi[. = $iface]) = '')">
    <xsl:call-template name="radvdconf">
      <xsl:with-param name="iface" select="$iface"/>
      <xsl:with-param name="sncnt" select="$sncnt"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template match="Interface">
  <xsl:call-template name="setupiface">
    <xsl:with-param name="iface" select="."/>
    <xsl:with-param name="sncnt" select="position()+1"/>
  </xsl:call-template>
</xsl:template>

<xsl:template match="Tunnel">
  <xsl:param name="ifcnt"/>
  <xsl:call-template name="setupiface">
    <xsl:with-param name="iface" select="concat('gtun',position()-1)"/>
    <xsl:with-param name="sncnt" select="position()+1+$ifcnt"/>
  </xsl:call-template>
</xsl:template>

<xsl:template match="/config">
  <xsl:call-template name="setupiface">
    <xsl:with-param name="iface" select="$intint"/>
    <xsl:with-param name="sncnt" select="'1'"/>
  </xsl:call-template>

  <xsl:apply-templates select="/config/IP/Interfaces/Interface[(. != $intint) and 
                 (not(contains(/config/IP/SysConf/Option[@option='Bridge'],.))) and
                 (not(contains(.,':')))]"/>

  <xsl:variable name="ifcnt" select="count(/config/IP/Interfaces/Interface[(. != $intint) and
                 (not(contains(/config/IP/SysConf/Option[@option='Bridge'],.))) and
                 (not(contains(.,':')))])"/>
  <xsl:apply-templates select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:with-param name="ifcnt" select="$ifcnt"/>
  </xsl:apply-templates>
</xsl:template>
</xsl:stylesheet>
