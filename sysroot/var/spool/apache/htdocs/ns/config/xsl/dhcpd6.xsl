<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>
<xsl:variable name="fqdn" select="concat($hname,'.',$domain)"/>
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>

<xsl:template match="Interface6">
  <xsl:param name="int4ip"/>
  <xsl:param name="intname"/>

  <xsl:text>&#xa;</xsl:text>
  <xsl:value-of select="concat('#',$intname,$nl)"/>
  <xsl:value-of select="concat('subnet6 ',@prefix,'::/',@subnet,' {',$nl)"/>
  <xsl:text>  authoritative;&#xa;</xsl:text>
  <xsl:value-of select="concat('  range6 ',@prefix,'::',@dhcpstart,' ',@prefix,'::',@dhcpend,';',$nl)"/>
  <xsl:value-of select="concat('  option dhcp6.name-servers ',@prefix,@ipaddr,';',$nl)"/>
  <xsl:value-of select="concat('  option ntp-servers ',$fqdn,';',$nl)"/>
  <xsl:value-of select="concat('  option tftp-server-name &quot;http://',$int4ip,'&quot;;',$nl)"/>
  <xsl:value-of select="concat('  option yealink-url &quot;http://',$fqdn,'&quot;;',$nl)"/>
  <xsl:value-of select="concat('  option tftp-cisco-server-name ',$int4ip,';',$nl)"/>
  <xsl:value-of select="concat('}',$nl)"/>
</xsl:template>

<xsl:template match="Interface">
  <xsl:choose>
    <xsl:when test="count(/config/IP/Interfaces/Interface6[. = current()]) &gt; 0">
      <xsl:apply-templates select="/config/IP/Interfaces/Interface6[. = current()]">
        <xsl:with-param name="int4ip" select="@ipaddr"/>
        <xsl:with-param name="intname" select="@name"/>
      </xsl:apply-templates>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat($nl,'#',@name,' (',.,') Not authoritative',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="ddns">
  <xsl:choose>
    <xsl:when test="(/config/DNS/Config/Option[@option = 'Auth'] = 'true') or
                    (/config/DNS/Config/Option[@option = 'PrimaryDns'] = '$intip')">
      <xsl:text>  primary 127.0.0.1;&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('  primary ',/config/DNS/Config/Option[@option = 'PrimaryDns'],$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>  key rndc-key;&#xa;</xsl:text>
  <xsl:text>}&#xa;</xsl:text>
</xsl:template>

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
      <xsl:value-of select="concat($revdom,'ip6.arpa.')"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="revzone">
  <xsl:for-each select="/config/IP/Interfaces/Interface6">
    <xsl:text>&#xa;zone </xsl:text>
    <xsl:call-template name="toarpa">
      <xsl:with-param name="prefix" select="concat(@prefix,':')"/>
    </xsl:call-template>
    <xsl:text> {&#xa;</xsl:text>
    <xsl:call-template name="ddns"/>
  </xsl:for-each>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>option wpad-url code 252 = text;
option tftp-cisco-server-name code 150 = ip-address;
option polycom-vlanid code 251 = unsigned integer 16;
option yealink-url code 250 = string;
</xsl:text>
  <xsl:for-each select="/config/IP/Interfaces/Interface[(@name = 'Phones') and contains(.,'.')]">
    <xsl:if test="position() = 1">
      <xsl:value-of select="concat('option polycom-vlanid ',substring-after(.,'.'),';',$nl)"/>
    </xsl:if>
  </xsl:for-each>
<xsl:text>
option domain-name "</xsl:text><xsl:value-of select="$domain"/><xsl:text>";
option nis-domain "</xsl:text><xsl:value-of select="$domain"/><xsl:text>";
option wpad-url "http://</xsl:text><xsl:value-of select="$fqdn"/><xsl:text>/proxy.pac";
option tftp-server-name "http://</xsl:text><xsl:value-of select="$fqdn"/><xsl:text>";
option time-offset 7200;

ddns-update-style interim;
ddns-updates on;
ddns-domainname "</xsl:text><xsl:value-of select="$domain"/><xsl:text>";
ddns-rev-domainname "ip6.arpa";
include "/etc/rndc.key";
allow client-updates;

update-conflict-detection false;
update-optimization false;
allow leasequery;
include "/etc/rndc.key";
default-lease-time </xsl:text><xsl:value-of select="/config/IP/SysConf/Option[@option = 'DHCPLease']"/><xsl:text>;
max-lease-time </xsl:text><xsl:value-of select="/config/IP/SysConf/Option[@option = 'DHCPMaxLease']"/><xsl:text>;
</xsl:text>

  <xsl:apply-templates select="/config/IP/Interfaces/Interface[not(contains(.,':'))]"/>

  <xsl:if test="(/config/DNS/Config/Option[@option = 'Auth'] = 'true') or
                (/config/DNS/Config/Option[@option = 'PrimaryDns'] != '')">
    <xsl:value-of select="concat($nl,'zone ',$domain,'. {',$nl)"/>
    <xsl:call-template name="ddns"/>

    <xsl:call-template name="revzone"/>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>
