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
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>
<xsl:variable name="pdns" select="/config/IP/SysConf/Option[@option = 'PrimaryDns']"/>
<xsl:variable name="sdns" select="/config/IP/SysConf/Option[@option = 'SecondaryDns']"/>
<xsl:variable name="dynzone" select="/config/DNS/Config/Option[@option = 'DynZone']"/>
<!--
XXXX
  int smb AD dom bits / ext auth domain setup
-->

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
      <xsl:call-template name="intdomain">
        <xsl:with-param name="zone" select="concat($revdom,'ip6.arpa')"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="inthosted">
  <xsl:param name="zone"/>
  <xsl:param name="file" select="$zone"/>

  <xsl:text>       zone "</xsl:text><xsl:value-of select="$zone"/><xsl:text>" {
              type master;
              notify yes;
              file "</xsl:text><xsl:value-of select="$file"/><xsl:text>";
              also-notify {
                     127.0.0.1;
                     ::1;
              };
              allow-update {
                     key </xsl:text><xsl:value-of select="$zone"/><xsl:text>;
                     key rndc-key;
                     key </xsl:text><xsl:value-of select="$domain"/><xsl:text>;
              };
              check-names ignore;
       };&#xa;</xsl:text>
</xsl:template>

<xsl:template name="extslave">
  <xsl:param name="zone"/>
  <xsl:text>       zone "</xsl:text><xsl:value-of select="$zone"/><xsl:text>" {
              type slave;
              file "</xsl:text>
  <xsl:choose>
    <xsl:when test="$zone = $domain">
      <xsl:value-of select="'domain.ext'"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$zone"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>";
              allow-notify {
                        any;
              };
              masters {&#xa;</xsl:text>
  <xsl:for-each select="/config/DNS/Hosted/Domain[@domain = $zone]/NameServer">
    <xsl:value-of select="concat('                        ',.,';',$nl)"/>
  </xsl:for-each>
  <xsl:text>              };
              check-names ignore;
       };&#xa;</xsl:text>
</xsl:template>

<xsl:template name="hosted">
  <xsl:param name="zone"/>
  <xsl:text>       zone "</xsl:text><xsl:value-of select="$zone"/><xsl:text>" {
              type master;
              notify yes;
              file "</xsl:text><xsl:value-of select="$zone"/><xsl:text>";
              also-notify {
                     127.0.0.1;
                     ::1;
              };
              allow-transfer {
                        127.0.0.1/32;
                        ::1/128;&#xa;</xsl:text>
  <xsl:value-of select="concat('                        127.0.0.2;',$nl)"/>
  <xsl:for-each select="/config/DNS/Hosted/Domain[@domain = $zone]/NameServer">
    <xsl:value-of select="concat('                        ',.,';',$nl)"/>
  </xsl:for-each>
  <xsl:text>              };
              allow-update {
                        key </xsl:text><xsl:value-of select="$zone"/><xsl:text>;
                        key rndc-key;
                        key </xsl:text><xsl:value-of select="$domain"/><xsl:text>;
              };
              check-names ignore;
       };&#xa;</xsl:text>
</xsl:template>

<xsl:template name="intslave">
  <xsl:param name="zone"/>
  <xsl:text>       zone "</xsl:text><xsl:value-of select="$zone"/><xsl:text>" {
              type slave;
              file "int.</xsl:text><xsl:value-of select="$zone"/><xsl:text>";
              allow-notify {
                        any;
              };
              masters {&#xa;</xsl:text>
  <xsl:if test="($intiface != $extiface) or ($extcon = 'ADSL')">
    <xsl:value-of select="concat('                        127.0.0.2;',$nl)"/>
  </xsl:if>
  <xsl:for-each select="/config/DNS/Hosted/Domain[@domain = $zone]/NameServer">
    <xsl:value-of select="concat('                        ',.,';',$nl)"/>
  </xsl:for-each>
  <xsl:text>              };
              check-names ignore;
       };&#xa;</xsl:text>
</xsl:template>

<xsl:template name="intdomain">
  <xsl:param name="zone"/>

  <xsl:value-of select="concat('       zone &quot;',$zone,'&quot; {',$nl)"/>
  <xsl:text>              type master;
              notify yes;
              file "</xsl:text><xsl:value-of select="concat($zone,'&quot;;')"/><xsl:text>
              allow-update {
                     key rndc-key;
                     key </xsl:text><xsl:value-of select="concat($domain,';')"/><xsl:text>
              };
              check-names ignore;
       };&#xa;</xsl:text>
</xsl:template>

<xsl:template name="kansas">
  <xsl:text>       zone "0.0.127.in-addr.arpa" {
              type master;
              notify yes;
              also-notify {
                     127.0.0.1;
                     ::1;
              };
              file "0.0.127.in-addr.arpa";
              check-names ignore;
       };&#xa;</xsl:text>
</xsl:template>

<xsl:template name="intreverse">
  <xsl:for-each select="/config/IP/Interfaces/Interface6">
    <xsl:call-template name="toarpa">
      <xsl:with-param name="prefix" select="concat(@prefix,':')"/>
    </xsl:call-template>
  </xsl:for-each>

  <xsl:for-each select="/config/DNS/InAddr/Reverse">
    <xsl:call-template name="intdomain">
      <xsl:with-param name="zone" select="."/>
    </xsl:call-template>
  </xsl:for-each>

  <xsl:call-template name="intdomain">
    <xsl:with-param name="zone" select="$domain"/>
  </xsl:call-template>

  <xsl:if test="($dynzone != '') and ($dynzone != $domain)">
    <xsl:call-template name="inthosted">
      <xsl:with-param name="zone" select="$dynzone"/>
      <xsl:with-param name="file" select="'domain.dyn'"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="intvmatch">
  <xsl:for-each select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32')]">
    <xsl:if test="(. != $extiface) or ($extcon = 'ADSL')">
      <xsl:value-of select="concat('               ',@nwaddr,'/',@subnet,';',$nl)"/>
      <xsl:if test="count(/config/IP/Interfaces/Interface6[. = current()]) &gt; 0">
        <xsl:value-of select="concat('               ',
               /config/IP/Interfaces/Interface6[. = current()]/@prefix,'::/',/config/IP/Interfaces/Interface6[. = current()]/@subnet,';',$nl)"/>
      </xsl:if>
    </xsl:if>
  </xsl:for-each>

  <xsl:if test="/config/Radius/Config/Option[@option = 'PPPoE'] != ''">
    <xsl:value-of select="concat('               ',/config/Radius/Config/Option[@option = 'PPPoE'],';',$nl)"/>
  </xsl:if>
  <xsl:if test="/config/IP/SysConf/Option[@option = 'VPNNet'] != ''">
    <xsl:value-of select="concat('               ',/config/IP/SysConf/Option[@option = 'VPNNet'],';',$nl)"/>
  </xsl:if>
  <xsl:if test="/config/IP/SysConf/Option[@option = 'OVPNNet'] != ''">
    <xsl:value-of select="concat('               ',/config/IP/SysConf/Option[@option = 'OVPNNet'],';',$nl)"/>
  </xsl:if>
  <xsl:if test="/config/IP/SysConf/Option[@option = 'L2TPNet'] != ''">
    <xsl:value-of select="concat('               ',/config/IP/SysConf/Option[@option = 'L2TPNet'],';',$nl)"/>
  </xsl:if>
  <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:value-of select="concat('               ',@nwaddr,'/30',';',$nl)"/>
  </xsl:for-each>
  <xsl:for-each select="/config/IP/Routes/Route">
    <xsl:value-of select="concat('               ',@network,'/',@subnet,';',$nl)"/>
  </xsl:for-each>
  <xsl:for-each select="/config/Radius/RAS/Modem">
    <xsl:value-of select="concat('               ',@remote,'/32',';',$nl)"/>
  </xsl:for-each>
  <xsl:for-each select="/config/IP/ESP/Tunnels/ESPTunnel">
    <xsl:value-of select="concat('               ',.,';',$nl)"/>
  </xsl:for-each>
  <xsl:text>               127.0.0.1/32;
               ::1/128;
               169.254.0.0/16;&#xa;</xsl:text>
</xsl:template>

<xsl:template name="extview">
  <xsl:text>view "external" {
       allow-query {
               any;
       };
       match-clients {
               any;
       };&#xa;</xsl:text>
  <xsl:choose>
    <xsl:when test="(/config/DNS/Config/Option[@option = 'ExtServ'] = 'true') or ($extiface = $intiface)">
      <xsl:if test="($pdns != '') or ($sdns != '') or ((/config/DNS/Config/Option[@option = 'Usepeer'] = 'true') and (($extcon = 'ADSL') or ($extiface = 'Dialup')))">
        <xsl:text>       include "/etc/bind/forwarders.conf";&#xa;</xsl:text>
      </xsl:if>
      <xsl:text>       recursion yes;
       zone "." {
               type hint;
               file "root.cache";
       };&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>       recursion no;&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:for-each select="/config/DNS/Hosted/Domain[(@key != '') and (@internal = 'false')]">
    <xsl:call-template name="hosted">
      <xsl:with-param name="zone" select="@domain"/>
    </xsl:call-template>
  </xsl:for-each>

  <xsl:for-each select="/config/DNS/Hosted/Domain[(@key = '') and (@internal = 'false')]">
    <xsl:call-template name="extslave">
      <xsl:with-param name="zone" select="@domain"/>
    </xsl:call-template>
  </xsl:for-each>
  <xsl:text>};&#xa;</xsl:text>
</xsl:template>

<xsl:template name="intview">
  <xsl:text>view "internal" {
       allow-query {
               any;
       };
       match-clients {&#xa;</xsl:text>
  <xsl:choose>
    <xsl:when test="($extiface = $intiface) and ($extcon != 'ADSL')">
      <xsl:text>              any;&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="intvmatch"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>       };
       include "/etc/bind/forwarders.conf";
       recursion yes;
       zone "." {
               type hint;
               file "root.cache";
       };&#xa;</xsl:text>


  <xsl:if test="/config/DNS/Config/Option[@option = 'Auth'] = 'true'">
    <xsl:call-template name="intreverse"/>
  </xsl:if>
  <xsl:call-template name="kansas"/>

  <xsl:for-each select="/config/DNS/Hosted/Domain[((@key != '') and (@internal = 'false')) or ((@key = '') and (@internal = 'true'))]">
    <xsl:call-template name="intslave">
      <xsl:with-param name="zone" select="@domain"/>
    </xsl:call-template>
  </xsl:for-each>

  <xsl:for-each select="/config/DNS/Hosted/Domain[(@key != '') and (@internal = 'true')]">
    <xsl:call-template name="inthosted">
      <xsl:with-param name="zone" select="@domain"/>
    </xsl:call-template>
  </xsl:for-each>

  <xsl:text>};&#xa;</xsl:text>
</xsl:template>

<xsl:template name="printkeys">
  <xsl:for-each select="/config/DNS/Hosted/Domain[@b64key != '']">
    <xsl:value-of select="concat('key ',@domain,' {',$nl,'        algorithm hmac-md5;',$nl)"/>
    <xsl:value-of select="concat('        secret ',@b64key,';',$nl,'};',$nl)"/>
  </xsl:for-each>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>include "/etc/rndc.key";
options {
        listen-on-v6 {
               any;
        };
        directory "/var/named";
	notify yes;
};
logging {
        channel update_debug {
                file "/var/log/update-debug.log";
                severity  info;
                print-category yes;
                print-severity yes;
                print-time     yes;
        };
        channel security_info    {
                file "/var/log/named-auth.info";
                severity  info;
                print-category yes;
                print-severity yes;
                print-time     yes;
        };
        channel simple_log   {
                severity  warning;
		syslog daemon;
        };
        category update {
                update_debug;
        };
        category security {
                security_info;
        };
        category default {
                simple_log;
       };
};
controls {
        inet 127.0.0.1 port 953
        allow {
                127.0.0.1;
        };
        inet ::1 port 953
        allow {
                ::1;
        };
        inet 127.0.0.2 port 953
        allow {
                127.0.0.2;
        };
        inet ::127.0.0.2 port 953
        allow {
                ::127.0.0.2;
        };
};

</xsl:text>
  <xsl:call-template name="printkeys"/>

  <xsl:if test="($smart64key != '') and ($dynzone != '') and ($dynzone != $domain)">
    <xsl:value-of select="concat('key ',$dynzone,' {',$nl,'        algorithm hmac-md5;',$nl)"/>
    <xsl:value-of select="concat('        secret ',$smart64key,';',$nl,'};',$nl)"/>
  </xsl:if>
  <xsl:value-of select="concat('key ',$domain,' {',$nl,'        algorithm hmac-md5;',$nl)"/>
  <xsl:value-of select="concat('        secret ',$dynb64key,';',$nl,'};',$nl)"/>

  <xsl:call-template name="intview"/>
  <xsl:if test="($extiface != $intiface) or ($extcon = 'ADSL')">
    <xsl:call-template name="extview"/>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>
