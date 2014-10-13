<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="extiface" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>
<xsl:variable name="fqdn" select="concat($hname,'.',$domain)"/>

<xsl:template name="sethwmode">
  <xsl:text>hw_mode=</xsl:text>
  <xsl:if test="@mode = '0'">
    <xsl:text>a</xsl:text>
  </xsl:if>
  <xsl:if test="@mode = '1'">
    <xsl:text>b</xsl:text>
  </xsl:if>
  <xsl:if test="@mode = '2'">
    <xsl:text>g</xsl:text>
  </xsl:if>
  <xsl:if test="@mode = '3'">
    <xsl:text>g</xsl:text>
  </xsl:if>
  <xsl:if test="@mode = '4'">
    <xsl:text>g</xsl:text>
  </xsl:if>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template name="setauth">
  <xsl:param name="authtype"/>
  <xsl:param name="authkey"/>
  <xsl:param name="wifimac"/>

  <xsl:value-of select="concat('ssid=',/config/IP/Interfaces/Interface[text() = $wifimac]/@name,$nl)"/>
  <xsl:if test="$authtype = 'EAP'">
    <xsl:choose>
      <xsl:when test="$authkey != ''">
        <xsl:text>wpa_key_mgmt=WPA-EAP-SHA256 WPA-EAP WPA-PSK-SHA256 WPA-PSK&#xa;</xsl:text>
        <xsl:value-of select="concat('wpa_passphrase=',$authkey,$nl)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>wpa_key_mgmt=WPA-EAP-SHA256 WPA-EAP&#xa;</xsl:text>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:text>wpa_pairwise=TKIP CCMP&#xa;</xsl:text>
    <xsl:text>wep_key_len_broadcast=13&#xa;</xsl:text>
    <xsl:text>wep_key_len_unicast=13&#xa;</xsl:text>
    <xsl:text>wep_rekey_period=300&#xa;</xsl:text>
    <xsl:text>wpa=3&#xa;</xsl:text>
  </xsl:if>

  <xsl:if test="$authtype = 'WPA'">
    <xsl:text>wpa_key_mgmt=WPA-PSK-SHA256 WPA-PSK&#xa;</xsl:text>
    <xsl:text>wpa_pairwise=TKIP CCMP&#xa;</xsl:text>
    <xsl:value-of select="concat('wpa_passphrase=',$authkey,$nl)"/>
    <xsl:text>wpa=3&#xa;</xsl:text>
  </xsl:if>

  <xsl:if test="$authtype = 'None'">
    <xsl:text>wpa=0&#xa;</xsl:text>
  </xsl:if>
</xsl:template>

<xsl:template match="WiFi">
  <xsl:text>driver=nl80211&#xa;</xsl:text>
  <xsl:value-of select="concat('country_code=',@regdom,$nl)"/>
  <xsl:text>ieee80211d=1&#xa;</xsl:text>
  <xsl:text>ctrl_interface=/var/run/hostapd&#xa;</xsl:text>
  <xsl:value-of select="concat('own_ip_addr=',$intip,$nl)"/>
  <xsl:text>auth_server_addr=127.0.0.1&#xa;</xsl:text>
  <xsl:value-of select="concat('auth_server_port=',/config/Radius/Config/Option[@option = 'AuthPort'],$nl)"/>
  <xsl:value-of select="concat('auth_server_shared_secret=',/config/Radius/Config/Option[@option = 'Secret'],$nl)"/>
  <xsl:text>acct_server_addr=127.0.0.1&#xa;</xsl:text>
  <xsl:value-of select="concat('acct_server_port=',/config/Radius/Config/Option[@option = 'AccPort'],$nl)"/>
  <xsl:value-of select="concat('acct_server_shared_secret=',/config/Radius/Config/Option[@option = 'Secret'],$nl)"/>
  <xsl:text>radius_retry_primary_interval=600&#xa;</xsl:text>
  <xsl:choose>
    <xsl:when test="@mode != '1'">
      <xsl:text>wmm_enabled=1&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>wmm_enabled=0&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:value-of select="concat('interface=',.,$nl)"/>
  <xsl:if test="contains(/config/IP/SysConf/Option[@option = 'Bridge'],.)">
    <xsl:value-of select="concat('bridge=',$intiface,$nl)"/>
  </xsl:if>
  <xsl:text>auth_algs=3&#xa;</xsl:text>
  <xsl:value-of select="concat('channel=',@channel,$nl)"/>
  <xsl:call-template name="sethwmode"/>

  <xsl:text>&#xa;</xsl:text>
  <xsl:call-template name="setauth">
    <xsl:with-param name="authtype" select="@auth"/>
    <xsl:with-param name="authkey" select="@key"/>
    <xsl:with-param name="wifimac" select="$wifi"/>
  </xsl:call-template>
  <xsl:text>&#xa;</xsl:text>

  <xsl:for-each select="/config/IP/WiFi[(substring(text(),1,string-length($wifi)+1)=concat($wifi,'_')) and (text() != $wifi)]">
    <xsl:value-of select="concat('bss=',.,$nl)"/>
    <xsl:call-template name="setauth">
      <xsl:with-param name="authtype" select="@auth"/>
      <xsl:with-param name="authkey" select="@key"/>
      <xsl:with-param name="wifimac" select="."/>
    </xsl:call-template>
  </xsl:for-each>
</xsl:template>

<xsl:template match="/config">
  <xsl:apply-templates select="/config/IP/WiFi[text() = $wifi]"/>
</xsl:template>
</xsl:stylesheet>
