<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="pwins" select="/config/IP/SysConf/Option[@option = 'PrimaryWins']"/>
<xsl:variable name="swins" select="/config/IP/SysConf/Option[@option = 'SecondaryWins']"/>
<xsl:variable name="pdns" select="/config/IP/SysConf/Option[@option = 'PrimaryDns']"/>
<xsl:variable name="sdns" select="/config/IP/SysConf/Option[@option = 'SecondaryDns']"/>
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>
<xsl:variable name="fqdn" select="concat($hname,'.',$domain)"/>
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
<xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'"/>

<xsl:template name="getdns">
  <xsl:param name="linkip"/>

  <xsl:text>option domain-name-servers </xsl:text>
  <xsl:if test="/config/DNS/Config/Option[@option = 'IntFirst'] = 'true'">
    <xsl:choose>
      <xsl:when test="$linkip != $intip">
        <xsl:value-of select="concat($linkip,',',$intip)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$intip"/>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:if test="$pdns != ''">
      <xsl:text>,</xsl:text>
    </xsl:if>
  </xsl:if>

  <xsl:if test="$pdns != ''">
    <xsl:value-of select="$pdns"/>
    <xsl:if test="$sdns != ''">
      <xsl:value-of select="concat(',',$sdns)"/>
    </xsl:if>
  </xsl:if>

  <xsl:if test="/config/DNS/Config/Option[@option = 'IntFirst'] != 'true'">
    <xsl:if test="$pdns != ''">
      <xsl:text>,</xsl:text>
    </xsl:if>
    <xsl:choose>
      <xsl:when test="$linkip != $intip">
        <xsl:value-of select="concat($linkip,',',$intip)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$intip"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:if>
  <xsl:text>;&#xa;</xsl:text>
</xsl:template>

<xsl:template name="getwins">
  <xsl:param name="linkip" select="$intip"/>

  <xsl:text>option netbios-name-servers </xsl:text>
  <xsl:choose>
    <xsl:when test="$pwins != ''">
      <xsl:value-of select="$pwins"/>
      <xsl:if test="$swins != ''">
        <xsl:value-of select="concat(', ',$swins)"/>
      </xsl:if>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$intip"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>;&#xa;</xsl:text>
</xsl:template>

<xsl:template match="Tunnel">
  <xsl:text>&#xa;</xsl:text>
  <xsl:value-of select="concat('#',.,$nl)"/>
  <xsl:value-of select="concat('subnet ',@nwaddr,' netmask 255.255.255.252 {',$nl)"/>
  <xsl:text>  not authoritative;&#xa;}&#xa;</xsl:text>
</xsl:template>

<xsl:template match="Interface">
  <xsl:text>&#xa;</xsl:text>
  <xsl:value-of select="concat('#',@name,$nl)"/>
  <xsl:value-of select="concat('subnet ',@nwaddr,' netmask ',@netmask,' {',$nl)"/>
  <xsl:choose>
    <xsl:when test="(@dhcpstart != '') and (@dhcpend != '') and (@dhcpstart != '-') and (@dhcpend != '-')">
      <xsl:text>  authoritative;&#xa;</xsl:text>
      <xsl:value-of select="concat('  range ',@dhcpstart,' ',@dhcpend,';',$nl)"/>
      <xsl:text>  option routers </xsl:text>
      <xsl:choose>
        <xsl:when test="@gateway != ''">
          <xsl:value-of select="concat(@gateway,';',$nl)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="concat(@ipaddr,';',$nl)"/>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:value-of select="concat('  option domain-name &quot;',$domain,'&quot;;',$nl)"/>
      <xsl:value-of select="concat('  option nis-domain &quot;',$domain,'&quot;;',$nl)"/>
      <xsl:text>  option time-offset 7200;&#xa;</xsl:text>
      <xsl:value-of select="concat('  option ntp-servers ',@ipaddr,';',$nl)"/>
      <xsl:text>  </xsl:text>
      <xsl:call-template name="getdns">
        <xsl:with-param name="linkip" select="@ipaddr"/>
      </xsl:call-template>
      <xsl:value-of select="concat('  option subnet-mask ',@netmask,';',$nl)"/>
      <xsl:value-of select="concat('  option broadcast-address ',@bcaddr,';',$nl)"/>
      <xsl:choose>
        <xsl:when test=". = $intiface">
          <xsl:value-of select="concat('  option tftp-server-name &quot;',@ipaddr,'&quot;;',$nl)"/>
          <xsl:value-of select="concat('  option yealink-url &quot;http://',$fqdn,'&quot;;',$nl)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="concat('  option tftp-server-name &quot;http://',@ipaddr,'&quot;;',$nl)"/>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:if test="(count(/config/IP/SysConf/Option[@option = 'PrimaryWins']) = 0) or ($pwins = $intip)">
        <xsl:text>  </xsl:text>
        <xsl:call-template name="getwins">
          <xsl:with-param name="linkip" select="@ipaddr"/>
        </xsl:call-template>
      </xsl:if>
      <xsl:value-of select="concat('  option tftp-cisco-server-name ',@ipaddr,';',$nl)"/>
      <xsl:value-of select="concat('  option wpad-url &quot;http://',$fqdn,'/proxy.pac&quot;;',$nl)"/>
      <xsl:value-of select="concat('}',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>  not authoritative;&#xa;}&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Route">
  <xsl:variable name="name" select="concat(.,' Wan Link')"/>
  <xsl:variable name="nwaddr" select="@network"/>
  <xsl:variable name="gateway" select="@remote"/>
  <xsl:variable name="ipaddr" select="$intip"/>  

  <xsl:text>&#xa;</xsl:text>
  <xsl:value-of select="concat('#',$name,$nl)"/>
  <xsl:value-of select="concat('subnet ',$nwaddr,' netmask ',@netmask,' {',$nl)"/>
  <xsl:choose>
    <xsl:when test="(@dhcpstart != '') and (@dhcpend != '') and (@dhcpstart != '-') and (@dhcpend != '-')">
      <xsl:text>  authoritative;&#xa;</xsl:text>
      <xsl:value-of select="concat('  range ',@dhcpstart,' ',@dhcpend,';',$nl)"/>
      <xsl:text>  option routers </xsl:text>
      <xsl:choose>
        <xsl:when test="$gateway != ''">
          <xsl:value-of select="concat($gateway,';',$nl)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="concat($ipaddr,';',$nl)"/>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:value-of select="concat('  option domain-name &quot;',$domain,'&quot;;',$nl)"/>
      <xsl:value-of select="concat('  option nis-domain &quot;',$domain,'&quot;;',$nl)"/>
      <xsl:text>  option time-offset 7200;&#xa;</xsl:text>
      <xsl:value-of select="concat('  option ntp-servers ',$ipaddr,';',$nl)"/>
      <xsl:text>  </xsl:text>
      <xsl:call-template name="getdns">
        <xsl:with-param name="linkip" select="$ipaddr"/>
      </xsl:call-template>
      <xsl:value-of select="concat('  option subnet-mask ',@netmask,';',$nl)"/>
      <xsl:value-of select="concat('  option broadcast-address ',@bcaddr,';',$nl)"/>
      <xsl:choose>
        <xsl:when test=". = $intiface">
          <xsl:value-of select="concat('  option tftp-server-name &quot;',$ipaddr,'&quot;;',$nl)"/>
          <xsl:value-of select="concat('  option yealink-url &quot;http://',$fqdn,'&quot;;',$nl)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="concat('  option tftp-server-name &quot;http://',$ipaddr,'&quot;;',$nl)"/>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:value-of select="concat('  option tftp-cisco-server-name ',$ipaddr,';',$nl)"/>
      <xsl:value-of select="concat('  option wpad-url &quot;http://',$fqdn,'/proxy.pac&quot;;',$nl)"/>
      <xsl:value-of select="concat('}',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>  not authoritative;&#xa;}&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Host">
  <xsl:variable name="host" select="translate(.,$uppercase,$smallcase)"/>
  <xsl:text>&#xa;</xsl:text>
  <xsl:value-of select="concat('host ',$host,' {',$nl)"/>
  <xsl:value-of select="concat('  hardware ethernet ',@macaddr,';',$nl)"/>
  <xsl:value-of select="concat('  fixed-address ',@ipaddr,';',$nl)"/>
  <xsl:value-of select="concat('  option host-name &quot;',$host,'&quot;;',$nl)"/>
  <xsl:value-of select="concat('  option tftp-server-name &quot;',$fqdn,'&quot;;',$nl)"/>
  <xsl:value-of select="concat('  filename &quot;','pxelinux.0','&quot;;',$nl)"/>
  <xsl:value-of select="concat('}',$nl)"/>
</xsl:template>

<xsl:template name="ddns">
  <xsl:choose>
    <xsl:when test="(/config/DNS/Config/Option[@option = 'Auth'] = 'true') or ($intip = $pdns)">
      <xsl:text>  primary 127.0.0.1;&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('  primary ',$pdns,$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>  key rndc-key;&#xa;</xsl:text>
  <xsl:text>}&#xa;</xsl:text>
</xsl:template>

<xsl:template match="Reverse">
  <xsl:value-of select="concat($nl,'zone ',.,'. {',$nl)"/>
  <xsl:call-template name="ddns"/>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>not authoritative;

option wpad-url code 252 = text;
option tftp-cisco-server-name code 150 = ip-address;
option polycom-vlanid code 251 = unsigned integer 16;
option yealink-url code 250 = string;
</xsl:text>
  <xsl:for-each select="/config/IP/Interfaces/Interface[(@name = 'Phones') and contains(.,'.')]">
    <xsl:if test="position() = 1">
      <xsl:value-of select="concat('option polycom-vlanid ',substring-after(.,'.'),';',$nl)"/>
    </xsl:if>
  </xsl:for-each>
<xsl:text>ddns-update-style interim;
ddns-updates on;
ddns-domainname "</xsl:text><xsl:value-of select="$domain"/><xsl:text>";
ddns-rev-domainname "in-addr.arpa";
allow client-updates;
update-conflict-detection false;
update-optimization false;
allow leasequery;
include "/etc/rndc.key";

default-lease-time </xsl:text><xsl:value-of select="/config/IP/SysConf/Option[@option = 'DHCPLease']"/><xsl:text>;
max-lease-time </xsl:text><xsl:value-of select="/config/IP/SysConf/Option[@option = 'DHCPMaxLease']"/><xsl:text>;

</xsl:text>
  <xsl:call-template name="getwins"/>
  <xsl:text>
#Dummy0
subnet 127.255.255.252 netmask 255.255.255.252 {
  not authoritative;
}

#No IP
subnet 0.0.0.0 netmask 255.255.255.255 {
  not authoritative;
}

</xsl:text>
  <xsl:apply-templates select="/config/IP/Interfaces/Interface[not(contains(.,':')) and (@ipaddr != '0.0.0.0')]"/>
  <xsl:apply-templates select="/config/IP/GRE/Tunnels/Tunnel"/>
  <xsl:apply-templates select="/config/IP/Routes/Route"/>
  <xsl:apply-templates select="/config/DNS/Hosts/Host[@macaddr != '']"/>

  <xsl:value-of select="concat($nl,'host ',$hname,' {',$nl)"/>
  <xsl:value-of select="concat('  hardware ethernet ',/config/IP/Interfaces/Interface[. = $intiface]/@macaddr,';',$nl)"/>
  <xsl:value-of select="concat('  fixed-address ',$intip,';',$nl,'}',$nl)"/>

  <xsl:if test="(/config/DNS/Config/Option[@option = 'Auth'] = 'true') or
                (/config/DNS/Config/Option[@option = 'PrimaryDns'] != '')">
    <xsl:value-of select="concat($nl,'zone ',$domain,'. {',$nl)"/>
    <xsl:call-template name="ddns"/>

    <xsl:apply-templates select="/config/DNS/InAddr/Reverse"/>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>
