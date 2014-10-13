<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>
<xsl:variable name="extint" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="intint" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name='link' select="concat('ppp',$id)"/>

<xsl:template match="Tunnel">
  <xsl:if test="@interface = $link">
    <xsl:value-of select="concat('/sbin/ip tun del gtun',position()-1,$nl)"/>
  </xsl:if>
</xsl:template>

<xsl:template name="startpppd">
  <xsl:param name="iface"/>
  <xsl:param name="link"/>
  <xsl:param name="user"/>
  <xsl:param name="ipparam"/>
  <xsl:param name="service"/>
  <xsl:param name="virtip"/>
  <xsl:param name="xtraopts"/>

  <xsl:value-of select="concat('date > /var/log/pppd.log.',$id,$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/pppd ',$iface,' unit ',$id,' logfile /var/log/pppd.log.',$id,' linkname ',$link)"/>

  <xsl:if test="$user != ''">
    <xsl:value-of select="concat(' user ',$user)"/>
  </xsl:if>

  <xsl:if test="$ipparam != ''">
    <xsl:value-of select="concat(' ipparam ',$ipparam)"/>
  </xsl:if>

  <xsl:text> nodefaultroute noauth persist nomultilink</xsl:text>

  <xsl:if test="$service != ''">
    <xsl:choose>
      <xsl:when test="contains($service,'/')">
        <xsl:value-of select="concat(' rp_pppoe_service &quot;',string-before($service,'/'),'&quot;')"/>
        <xsl:value-of select="concat(' rp_pppoe_ac &quot;',string-after($service,'/'),'&quot;')"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat(' rp_pppoe_service &quot;',$service,'&quot;')"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:if>
  <xsl:text> nodetach</xsl:text>

  <xsl:if test="$virtip != ''">
    <xsl:value-of select="concat(' endpoint IP:',$virtip)"/>
  </xsl:if>

  <xsl:if test="$xtraopts != ''">
    <xsl:value-of select="$xtraopts"/>
  </xsl:if>

  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template match="Link">
  <xsl:choose>
    <xsl:when test="$intint != @interface">
      <xsl:value-of select="concat('if [ -d /sys/class/net/ethDSL.',$id,' ];then',$nl)"/>
      <xsl:value-of select="concat('  /sbin/ip link delete ethDSL.',$id,$nl)"/>
      <xsl:text>fi&#xa;&#xa;</xsl:text>
      <xsl:value-of select="concat('/sbin/ip link add link ',@interface,' name ethDSL.',$id,' type macvlan',$nl)"/>
      <xsl:value-of select="concat('/sbin/ip link set dev ethDSL.',$id,' up',$nl)"/>
      <xsl:text>&#xa;</xsl:text>
      <xsl:call-template name="startpppd">
        <xsl:with-param name="iface" select="concat('sync plugin rp-pppoe.so ethDSL.',$id)"/>
        <xsl:with-param name="link" select="."/>
        <xsl:with-param name="ipparam" select="."/>
        <xsl:with-param name="user" select="@username"/>
        <xsl:with-param name="service" select="@service"/>
        <xsl:with-param name="virtip" select="@virtip"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="startpppd">
        <xsl:with-param name="iface" select="concat('sync plugin rp-pppoe.so ',@interface)"/>
        <xsl:with-param name="link" select="."/>
        <xsl:with-param name="ipparam" select="."/>
        <xsl:with-param name="user" select="@username"/>
        <xsl:with-param name="service" select="@service"/>
        <xsl:with-param name="virtip" select="@virtip"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>flock -u 10&#xa;</xsl:text>
  <xsl:value-of select="concat(') 10>/var/lock/ppp',$id,'.lock',$nl)"/>
</xsl:template>

<xsl:template name="defadsl">
  <xsl:choose>
    <xsl:when test="$intint != $extint">
      <xsl:text>if [ -d /sys/class/net/ethDSL.0 ];then&#xa;</xsl:text>
      <xsl:text>  /sbin/ip link delete ethDSL.0&#xa;</xsl:text>
      <xsl:text>fi&#xa;&#xa;</xsl:text>
      <xsl:value-of select="concat('/sbin/ip link add link ',$extint,' name ethDSL.0 type macvlan',$nl)"/>
      <xsl:text>/sbin/ip link set dev ethDSL.0 up&#xa;&#xa;</xsl:text>
      <xsl:call-template name="startpppd">
        <xsl:with-param name="iface" select="concat('sync plugin rp-pppoe.so ethDSL.',$id)"/>
        <xsl:with-param name="link" select="'main'"/>
        <xsl:with-param name="user" select="/config/IP/Dialup/Option[@option = 'Username']"/>
        <xsl:with-param name="service" select="/config/IP/Dialup/Option[@option = 'Number']"/>
        <xsl:with-param name="xtraopts" select="' usepeerdns'"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="startpppd">
        <xsl:with-param name="iface" select="concat('sync plugin rp-pppoe.so ',$extint)"/>
        <xsl:with-param name="link" select="'main'"/>
        <xsl:with-param name="user" select="/config/IP/Dialup/Option[@option = 'Username']"/>
        <xsl:with-param name="service" select="/config/IP/Dialup/Option[@option = 'Number']"/>
        <xsl:with-param name="xtraopts" select="' usepeerdns'"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="def3gipw">
  <xsl:call-template name="startpppd">
    <xsl:with-param name="iface" select="'/dev/tts/IPW0 connect &quot;/usr/sbin/chat -v -f /etc/ppp/diald.scr&quot; lock'"/>
    <xsl:with-param name="link" select="'main'"/>
    <xsl:with-param name="user" select="/config/IP/Dialup/Option[@option = 'Username']"/>
    <xsl:with-param name="xtraopts" select="' usepeerdns'"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="def3g">
  <xsl:call-template name="startpppd">
    <xsl:with-param name="iface" select="'/dev/gsmmodem connect &quot;/usr/sbin/chat -v -f /etc/ppp/diald.scr&quot; lock'"/>
    <xsl:with-param name="link" select="'main'"/>
    <xsl:with-param name="user" select="''"/>
    <xsl:with-param name="xtraopts" select="' usepeerdns'"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="defdialup">
  <xsl:variable name="comport" select="concat('/dev/tts/',substring(/config/IP/Dialup/Option[@option = 'ComPort'],4,1)-1)"/>
  <xsl:call-template name="startpppd">
    <xsl:with-param name="iface" select="concat($comport,' ',/config/IP/Dialup/Option[@option = 'Speed'],' ',/config/IP/Dialup/Option[@option = 'Address'],':',/config/IP/Dialup/Option[@option = 'Gateway'],' connect &quot;/usr/sbin/chat -v -f /etc/ppp/diald.scr&quot; lock')"/>
    <xsl:with-param name="link" select="'main'"/>
    <xsl:with-param name="user" select="/config/IP/Dialup/Option[@option = 'Username']"/>
    <xsl:with-param name="xtraopts" select="concat(' usepeerdns ',/config/IP/Dialup/Option[@option = 'FlowControl'],' ipcp-accept-local passive demand idle ',/config/IP/Dialup/Option[@option = 'IdleTimeout'])"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="default">
  <xsl:if test="($extint = 'Dialup') or ($extcon = 'ADSL')">
    <xsl:if test="$extcon = 'ADSL'">
      <xsl:call-template name="defadsl"/>
    </xsl:if>

    <xsl:if test="$extcon = '3GIPW'">
      <xsl:call-template name="def3gipw"/>
    </xsl:if>

    <xsl:if test="$extcon = '3G'">
      <xsl:call-template name="def3g"/>
    </xsl:if>

    <xsl:if test="$extcon = 'Dialup'">
      <xsl:call-template name="defdialup"/>
    </xsl:if>
  </xsl:if>

  <xsl:text>flock -u 10&#xa;) 10>/var/lock/ppp0.lock&#xa;</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>#!/bin/bash&#xa;&#xa;(flock -w 20 10 || exit&#xa;&#xa;</xsl:text>

  <xsl:if test="count(/config/IP/GRE/Tunnels/Tunnel[@interface = $link]) &gt; 0">
    <xsl:text>#Disable Tunnels&#xa;</xsl:text>
    <xsl:apply-templates select="/config/IP/GRE/Tunnels/Tunnel"/>
    <xsl:text>&#xa;</xsl:text>
  </xsl:if>

  <xsl:choose>
    <xsl:when test="$id &gt; 0">
      <xsl:apply-templates select="/config/IP/ADSL/Links/Link[@id = $id]"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="default"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>
</xsl:stylesheet>
