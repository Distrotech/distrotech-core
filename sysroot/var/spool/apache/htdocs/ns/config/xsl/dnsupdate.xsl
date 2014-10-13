<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="extiface" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>
<xsl:variable name="extint" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="intint" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>
<xsl:variable name="fqdn" select="concat($hname,'.',$domain)"/>
<xsl:variable name="defttl" select="/config/DNS/Config/Option[@option = 'DefaultTTL']"/>
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
<xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'"/>
<xsl:variable name="adsrvs" select="translate(/config/FileServer/Setup/Option[@option = 'ADSServer'],$uppercase,$smallcase)"/>
<xsl:variable name="dynzone" select="/config/DNS/Config/Option[@option = 'DynZone']"/>
<xsl:variable name="addom" select="translate(/config/FileServer/Setup/Option[@option = 'ADSRealm'],$uppercase,$smallcase)"/>

<xsl:template name="setptr">
  <xsl:param name="ptr"/>
  <xsl:param name="host"/>
  <xsl:param name="classc"/>

  <xsl:choose>
    <xsl:when test="$ptr != ''">
      <xsl:value-of select="concat('update delete ',$ptr,'.',$classc,'. PTR',$nl)"/>
      <xsl:value-of select="concat('update add ',$ptr,'.',$classc,'. ',$defttl,' PTR ',
        translate(translate($host,$uppercase,$smallcase),' ','-'),'.',$domain,'.',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('update delete ',$classc,'. PTR',$nl)"/>
      <xsl:value-of select="concat('update add ',$classc,'. ',$defttl,' PTR ',
        translate(translate($host,$uppercase,$smallcase),' ','-'),'.',$domain,'.',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="getrev">
  <xsl:param name="fwdmatch"/>
  <xsl:param name="classc"/>

  <xsl:for-each select="/config/DNS/Hosts/Host[starts-with(@ipaddr,$fwdmatch) and not(contains(.,'.'))]">
    <xsl:call-template name="setptr">
      <xsl:with-param name="ptr" select="substring(@ipaddr,string-length($fwdmatch)+2)"/>
      <xsl:with-param name="host" select="."/>
      <xsl:with-param name="classc" select="$classc"/>
    </xsl:call-template>
  </xsl:for-each>

  <xsl:for-each select="/config/IP/Interfaces/Interface[starts-with(@ipaddr,$fwdmatch)]">
    <xsl:variable name="ptr" select="substring(@ipaddr,string-length($fwdmatch)+2)"/>
    <xsl:choose>
      <xsl:when test="(. = $intiface) or ((. = $extiface) and ($extcon != 'ADSL'))">
        <xsl:call-template name="setptr">
          <xsl:with-param name="ptr" select="$ptr"/>
          <xsl:with-param name="host" select="$hname"/>
          <xsl:with-param name="classc" select="$classc"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="setptr">
          <xsl:with-param name="ptr" select="$ptr"/>
          <xsl:with-param name="host" select="@name"/>
          <xsl:with-param name="classc" select="$classc"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:for-each>

  <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel[starts-with(@local,$fwdmatch)]">
    <xsl:call-template name="setptr">
      <xsl:with-param name="ptr" select="substring(@local,string-length($fwdmatch)+2)"/>
      <xsl:with-param name="host" select="."/>
      <xsl:with-param name="classc" select="$classc"/>
    </xsl:call-template>
  </xsl:for-each>
</xsl:template>

<xsl:template match="Reverse">
  <xsl:value-of select="concat('zone ',.,$nl)"/>
  <xsl:value-of select="concat('update add ',.,'. ',$defttl,' NS ',$fqdn,'.',$nl)"/>
  <xsl:call-template name="getrev">
    <xsl:with-param name="fwdmatch" select="@fwdmatch"/>
    <xsl:with-param name="classc" select="."/>
  </xsl:call-template>
  <xsl:text>send&#xa;</xsl:text>
</xsl:template>

<xsl:template name="smbadsrv">
  <xsl:param name="servers"/>
  <xsl:param name="output"/>
  <xsl:param name="record"/>

  <xsl:variable name="cur" select="substring-before($servers,' ')"/>
  <xsl:variable name="next" select="substring-after($servers,' ')"/>

  <xsl:choose>
    <xsl:when test="$servers != ''">
      <xsl:choose>
        <xsl:when test="$cur != ''">
          <xsl:value-of select="concat('update add ',$record,'.',$domain,'. ',$defttl,' NS ',$cur,'.',$domain,'.',$nl)"/>
          <xsl:call-template name="smbadsrv">
            <xsl:with-param name="servers" select="$next"/>
            <xsl:with-param name="record" select="$record"/>
            <xsl:with-param name="output" select="concat($output,' ',$cur)"/>
          </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="concat('update add ',$record,'.',$domain,'. ',$defttl,' NS ',$servers,'.',$domain,'.',$nl)"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
  </xsl:choose>
</xsl:template>


<xsl:template name="adnsrec">
  <xsl:value-of select="concat('update delete _msdcs.',$domain,'. NS',$nl)"/>
  <xsl:call-template name="smbadsrv">
    <xsl:with-param name="servers" select="$adsrvs"/>
    <xsl:with-param name="record" select="'_msdcs'"/>
  </xsl:call-template>
  <xsl:value-of select="concat('update delete _sites.',$domain,'. NS',$nl)"/>
  <xsl:call-template name="smbadsrv">
    <xsl:with-param name="servers" select="$adsrvs"/>
    <xsl:with-param name="record" select="'_sites'"/>
  </xsl:call-template>
  <xsl:value-of select="concat('update delete forestdnszones.',$domain,'. NS',$nl)"/>
  <xsl:call-template name="smbadsrv">
    <xsl:with-param name="servers" select="$adsrvs"/>
    <xsl:with-param name="record" select="'forestdnszones'"/>
  </xsl:call-template>
  <xsl:value-of select="concat('update delete domaindnszones.',$domain,'. NS',$nl)"/>
  <xsl:call-template name="smbadsrv">
    <xsl:with-param name="servers" select="$adsrvs"/>
    <xsl:with-param name="record" select="'domaindnszones'"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="addsmbsrv">
  <xsl:param name="servers" select="$adsrvs"/>
  <xsl:param name="priority" select="'50'"/> 

  <xsl:variable name="cur" select="substring-before($servers,' ')"/>
  <xsl:variable name="next" select="substring-after($servers,' ')"/>

  <xsl:variable name="active">
    <xsl:choose>
      <xsl:when test="$cur != ''">
        <xsl:value-of select="$cur"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$servers"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:if test="$active != ''">
    <xsl:value-of select="concat('update add _gc._tcp.',$addom,'. ',$defttl,' SRV ',$priority,' 10 3268 ',$active,'.',$addom,'.',$nl)"/>
    <xsl:value-of select="concat('update add _kerberos._tcp.',$addom,'. ',$defttl,' SRV ',$priority,' 10 88 ',$active,'.',$addom,'.',$nl)"/>
    <xsl:value-of select="concat('update add _kpasswd._tcp.',$addom,'. ',$defttl,' SRV ',$priority,' 10 464 ',$active,'.',$addom,'.',$nl)"/>
    <xsl:value-of select="concat('update add _ldap._tcp.',$addom,'. ',$defttl,' SRV ',$priority,' 10 389 ',$active,'.',$addom,'.',$nl)"/>
    <xsl:value-of select="concat('update add _kerberos._udp.',$addom,'. ',$defttl,' SRV ',$priority,' 10 88 ',$active,'.',$addom,'.',$nl)"/>
    <xsl:value-of select="concat('update add _kpasswd._udp.',$addom,'. ',$defttl,' SRV ',$priority,' 10 464 ',$active,'.',$addom,'.',$nl)"/>
  </xsl:if>
  <xsl:if test="$next">
    <xsl:call-template name="addsmbsrv">
      <xsl:with-param name="servers" select="$next"/>
      <xsl:with-param name="priority" select="$priority-1"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="smbadrecs">
  <xsl:text>key </xsl:text>
  <xsl:value-of select="concat($domain,' ',$dynb64key,$nl)"/>
  <xsl:value-of select="concat('zone ',$addom,$nl)"/>
  <xsl:value-of select="concat('update delete _kerberos._tcp.',$addom,'. SRV',$nl)"/>
  <xsl:value-of select="concat('update delete _kpasswd._tcp.',$addom,'. SRV',$nl)"/>
  <xsl:value-of select="concat('update delete _ldap._tcp.',$addom,'. SRV',$nl)"/>
  <xsl:value-of select="concat('update delete _gc._tcp.',$addom,'. SRV',$nl)"/>
  <xsl:value-of select="concat('update delete _kerberos._udp.',$addom,'. SRV',$nl)"/>
  <xsl:value-of select="concat('update delete _kpasswd._udp.',$addom,'. SRV',$nl)"/>

  <xsl:text>send&#xa;</xsl:text>
  <xsl:text>key </xsl:text>
  <xsl:value-of select="concat($domain,' ',$dynb64key,$nl)"/>
  <xsl:value-of select="concat('zone ',$addom,$nl)"/>

  <xsl:call-template name="addsmbsrv"/>
  <xsl:text>send&#xa;</xsl:text>
</xsl:template>

<xsl:template name="domupdate">
  <xsl:value-of select="concat('zone ',$domain,$nl)"/>
  <xsl:value-of select="concat('update delete ',$domain,'. A',$nl)"/>
  <xsl:value-of select="concat('update delete ',$domain,'. MX',$nl)"/>
  <xsl:value-of select="concat('update delete wpad.',$domain,'. CNAME',$nl)"/>
  <xsl:value-of select="concat('update delete pabx.',$domain,'. CNAME',$nl)"/>
  <xsl:value-of select="concat('update delete voip.',$domain,'. CNAME',$nl)"/>
  <xsl:value-of select="concat('update delete ticket.',$domain,'. CNAME',$nl)"/>

  <xsl:if test="$adsrvs != ''">
    <xsl:call-template name="adnsrec"/>
  </xsl:if>

  <xsl:choose>
    <xsl:when test="/config/Mail/Config/Option[@option = 'MailExchange1'] != ''">
      <xsl:value-of select="concat('update add ',$domain,'. ',$defttl,' MX 0 ',/config/Mail/Config/Option[@option = 'MailExchange1'],'.',$nl)"/>
      <xsl:if test="/config/Mail/Config/Option[@option = 'MailExchange2'] != ''">
        <xsl:value-of select="concat('update add ',$domain,'. ',$defttl,' MX 5 ',/config/Mail/Config/Option[@option = 'MailExchange2'],'.',$nl)"/>
      </xsl:if>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('update add ',$domain,'. ',$defttl,' MX 0 ',$fqdn,'.',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:value-of select="concat('update delete ',$fqdn,'. A',$nl)"/>
  <xsl:value-of select="concat('update add ',$fqdn,'. ',$defttl,' A ',$intip,$nl)"/>
  <xsl:value-of select="concat('update add ',$domain,'. ',$defttl,' A ',$intip,$nl)"/>
  <xsl:value-of select="concat('update delete ',$fqdn,'. AAAA',$nl)"/>
  <xsl:value-of select="concat('update delete ',$domain,'. AAAA',$nl)"/>

  <xsl:for-each select="/config/IP/Interfaces/Interface6[. = $intiface]">
    <xsl:value-of select="concat('update add ',$fqdn,'. ',$defttl,' AAAA ',@prefix,@ipaddr,$nl)"/>
    <xsl:value-of select="concat('update add ',$domain,'. ',$defttl,' AAAA ',@prefix,@ipaddr,$nl)"/>
  </xsl:for-each>

  <xsl:value-of select="concat('update add wpad.',$domain,'. ',$defttl,' CNAME ',$fqdn,'.',$nl)"/>
  <xsl:value-of select="concat('update add pabx.',$domain,'. ',$defttl,' CNAME ',$fqdn,'.',$nl)"/>
  <xsl:value-of select="concat('update add ticket.',$domain,'. ',$defttl,' CNAME ',$fqdn,'.',$nl)"/>
  <xsl:value-of select="concat('update add voip.',$domain,'. ',$defttl,' CNAME ',$fqdn,'.',$nl)"/>
  <xsl:value-of select="concat('update delete loopback.',$domain,'. A',$nl)"/>
  <xsl:value-of select="concat('update delete loopback2.',$domain,'. A',$nl)"/>
  <xsl:value-of select="concat('update delete loopback.',$domain,'. AAAA',$nl)"/>
  <xsl:value-of select="concat('update delete loopback2.',$domain,'. AAAA',$nl)"/>
  <xsl:value-of select="concat('update add loopback.',$domain,'. ',$defttl,' A 127.0.0.1',$nl)"/>
  <xsl:value-of select="concat('update add loopback2.',$domain,'. ',$defttl,' A 127.0.0.2',$nl)"/>
  <xsl:value-of select="concat('update add loopback.',$domain,'. ',$defttl,' AAAA ::1',$nl)"/>
  <xsl:value-of select="concat('update add loopback2.',$domain,'. ',$defttl,' AAAA ::127.0.0.2',$nl)"/>

  <xsl:for-each select="/config/DNS/Hosts/Host[not(contains(.,'.'))]">
    <xsl:variable name="host" select="translate(.,$uppercase,$smallcase)"/>
    <xsl:value-of select="concat('update delete ',$host,'.',$domain,'. A',$nl)"/>
    <xsl:value-of select="concat('update add ',$host,'.',$domain,'. ',$defttl,' A ',@ipaddr,$nl)"/>
  </xsl:for-each>
  <xsl:for-each select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32') and (. != $intiface) and ((. != $extiface) or ($extcon = 'ADSL'))]">
    <xsl:variable name="host" select="translate(@name,$uppercase,$smallcase)"/>
    <xsl:value-of select="concat('update delete ',$host,'.',$domain,'. A',$nl)"/>
    <xsl:value-of select="concat('update add ',$host,'.',$domain,'. ',$defttl,' A ',@ipaddr,$nl)"/>
  </xsl:for-each>

  <xsl:for-each select="/config/IP/Interfaces/Interface6[(. != $intiface) and ((. != $extiface) or ($extcon = 'ADSL'))]">
    <xsl:variable name="host" select="translate(/config/IP/Interfaces/Interface[. = current()]/@name,$uppercase,$smallcase)"/>
    <xsl:value-of select="concat('update delete ',$host,'.',$domain,'. AAAA',$nl)"/>
    <xsl:value-of select="concat('update add ',$host,'.',$domain,'. ',$defttl,' AAAA ',@prefix,@ipaddr,$nl)"/>
  </xsl:for-each>

  <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:variable name="host" select="translate(.,$uppercase,$smallcase)"/>
    <xsl:value-of select="concat('update delete ',$host,'.',$domain,'. A',$nl)"/>
    <xsl:value-of select="concat('update add ',$host,'.',$domain,'. ',$defttl,' A ',@local,$nl)"/>
  </xsl:for-each>
</xsl:template>

<xsl:template name="smartupdate">
  <xsl:text>send&#xa;</xsl:text>
  <xsl:value-of select="concat('zone ',$dynzone,$nl)"/>
  <xsl:value-of select="concat('update delete ',$dynzone,'. A',$nl)"/>
  <xsl:value-of select="concat('update add ',$dynzone,'. ',$defttl,' A ',$intip,$nl)"/>
  <xsl:for-each select="/config/IP/ADSL/Links/Link">
    <xsl:value-of select="concat('update add ',.,'.',$dynzone,'. ',$defttl,' A ',$intip,$nl)"/>
  </xsl:for-each>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>server 127.0.0.1&#xa;</xsl:text>
  <xsl:text>local 127.0.0.1&#xa;</xsl:text>
  <xsl:text>key </xsl:text>
  <xsl:value-of select="concat($domain,' ',$dynb64key,$nl)"/>

  <xsl:if test="/config/DNS/Config/Option[@option = 'Auth'] = 'true'">
    <xsl:apply-templates select="/config/DNS/InAddr/Reverse"/>

    <xsl:if test="(($addom = $domain) and (/config/DNS/Config/Option[@option = 'Auth'] = 'true')) or
                  (count(/config/DNS/Hosted/Domain[(@domain = $addom) and (@key != '')]) &gt; 0)">
      <xsl:call-template name="smbadrecs"/>
    </xsl:if>

    <xsl:call-template name="domupdate"/>

    <xsl:if test="(/config/DNS/Config/Option[@option = 'Auth'] = 'true') and ($dynzone != $domain)">
      <xsl:call-template name="smartupdate"/>
    </xsl:if>
    <xsl:text>send&#xa;</xsl:text>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>
