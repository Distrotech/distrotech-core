<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template name="ifacevlan">
  <xsl:for-each select="/config/IP/Interfaces/Interface[contains(.,'.')]">
    <xsl:value-of select="concat('VLAN[',position(),']=&quot;',substring-after(.,'.'),'&quot;;',$nl)"/>
    <xsl:value-of select="concat('IP_ADDRV[',position(),']=&quot;',@ipaddr,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('SN_ADDRV[',position(),']=&quot;',@subnet,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('IP_SDHCPV[',position(),']=&quot;',@dhcpstart,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('IP_EDHCPV[',position(),']=&quot;',@dhcpend,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('INT_BWINV[',position(),']=&quot;',@bwin,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('INT_BWOUTV[',position(),']=&quot;',@bwout,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('INT_GWV[',position(),']=&quot;',@gateway,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('INT_NAMEV[',position(),']=&quot;',@name,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('INT_PARV[',position(),']=&quot;',substring-before(.,'.'),'&quot;;',$nl)"/>
  </xsl:for-each>
</xsl:template>

<xsl:template name="physiface">
  <xsl:for-each select="/config/IP/Interfaces/Interface[not(contains(.,'.')) and not(contains(.,':'))]">
    <xsl:choose>
      <xsl:when test="/config/IP/WiFi[(. = current()) and (@type = 'Hotspot')] != ''">
        <xsl:value-of select="concat('IP_ADDR[',position(),']=&quot;',@nwaddr,'&quot;;',$nl)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat('IP_ADDR[',position(),']=&quot;',@ipaddr,'&quot;;',$nl)"/>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:value-of select="concat('SN_ADDR[',position(),']=&quot;',@subnet,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('IP_SDHCP[',position(),']=&quot;',@dhcpstart,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('IP_EDHCP[',position(),']=&quot;',@dhcpend,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('INT_BWIN[',position(),']=&quot;',@bwin,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('INT_BWOUT[',position(),']=&quot;',@bwout,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('INT_MAC[',position(),']=&quot;',@macaddr,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('INT_GW[',position(),']=&quot;',@gatewway,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('INT_NAME[',position(),']=&quot;',@name,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('INT_IFACE[',position(),']=&quot;',.,'&quot;;',$nl)"/>
  </xsl:for-each>
</xsl:template>

<xsl:template name="ifacealias">
  <xsl:for-each select="/config/IP/Interfaces/Interface[contains(.,':')]">
    <xsl:value-of select="concat('ALIAS[',position(),']=&quot;',.,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('IP_ADDRA[',position(),']=&quot;',@ipaddr,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('SN_ADDRA[',position(),']=&quot;',@subnet,'&quot;;',$nl)"/>
    <xsl:value-of select="concat('INT_NAMEA[',position(),']=&quot;',@name,'&quot;;',$nl)"/>
  </xsl:for-each>
</xsl:template>

<xsl:template name="x509">
  <xsl:value-of select="concat('X509_C=&quot;',/config/X509/Option[@option = 'Country'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('X509_ST=&quot;',/config/X509/Option[@option = 'State'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('X509_L=&quot;',/config/X509/Option[@option = 'City'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('X509_O=&quot;',/config/X509/Option[@option = 'Company'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('X509_OU=&quot;',/config/X509/Option[@option = 'Division'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('X509_CN=&quot;',/config/X509/Option[@option = 'Name'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('X509_EMAIL=&quot;',/config/X509/Option[@option = 'Email'],'&quot;;',$nl)"/>
</xsl:template>

<xsl:template name="config">
  <xsl:value-of select="concat('DOM_ADDR=&quot;',/config/DNS/Config/Option[@option = 'Domain'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('HN_ADDR=&quot;',/config/DNS/Config/Option[@option = 'Hostname'],'&quot;;',$nl)"/>
  <xsl:if test="/config/Email/Config/Option[@option = 'Smarthost'] != ''">
    <xsl:value-of select="concat('SMTP_FWD=&quot;',/config/Email/Config/Option[@option = 'Smarthost'],'&quot;;',$nl)"/>
  </xsl:if>
  <xsl:choose>
    <xsl:when test="/config/FileServer/Config/Option[@option = 'netbios name'] != ''">
      <xsl:value-of select="concat('NB_NAME=&quot;',/config/FileServer/Config/Option[@option = 'netbios name'],'&quot;;',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:if test="substring-after(/config/FileServer/Config/Item[starts-with(.,'netbios name = ')],'netbios name = ') != ''">
        <xsl:value-of select="concat('NB_NAME=&quot;',substring-after(/config/FileServer/Config/Item[starts-with(.,'netbios name = ')],'netbios name = '),'&quot;;',$nl)"/>
      </xsl:if>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:choose>
    <xsl:when test="/config/FileServer/Config/Option[@option = 'os level'] != ''">
      <xsl:value-of select="concat('OSLEVEL=&quot;',/config/FileServer/Config/Option[@option = 'os level'],'&quot;;',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:if test="substring-after(/config/FileServer/Config/Item[starts-with(.,'os level = ')],'os level = ') != ''">
        <xsl:value-of select="concat('OSLEVEL=&quot;',substring-after(/config/FileServer/Config/Item[starts-with(.,'os level = ')],'os level = '),'&quot;;',$nl)"/>
      </xsl:if>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:value-of select="concat('DOM_WG=&quot;',/config/FileServer/Setup/Option[@option = 'Domain'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('DTYPE=&quot;',/config/FileServer/Setup/Option[@option = 'Security'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('DOM_ADS=&quot;',/config/FileServer/Setup/Option[@option = 'ADSRealm'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('DOM_DC=&quot;',/config/FileServer/Setup/Option[@option = 'ADSServer'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('GW_ADDR=&quot;',/config/IP/SysConf/Option[@option = 'Nexthop'],'&quot;;',$nl)"/>
  <xsl:choose>
    <xsl:when test="(/config/FileServer/@sharedir != '') and (/config/FileServer/@homedir != '')">
       <xsl:text>DOMC="1";&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
       <xsl:text>DOMC="0";&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:value-of select="concat('DNS_SERV1=&quot;',/config/IP/SysConf/Option[@option = 'PrimaryDns'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('DNS_SERV2=&quot;',/config/IP/SysConf/Option[@option = 'SecondaryDns'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('WINS_SERV1=&quot;',/config/IP/SysConf/Option[@option = 'PrimaryWins'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('WINS_SERV2=&quot;',/config/IP/SysConf/Option[@option = 'SecondaryWins'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('DYN_SERV=&quot;',/config/DNS/Config/Option[@option = 'DynServ'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('DYN_ZONE=&quot;',/config/DNS/Config/Option[@option = 'DynZone'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('DYN_KEY=&quot;',/config/DNS/Config/Option[@option = 'SmartKey'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('DNS_MX1=&quot;',/config/Email/Config/Option[@option = 'MailExchange1'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('DNS_MX2=&quot;',/config/Email/Config/Option[@option = 'MailExchange2'],'&quot;;',$nl)"/>
</xsl:template>

<xsl:template name="mdmconfig">
  <xsl:value-of select="concat('MDM_PORT=&quot;',/config/IP/Dialup/Option[@option = 'ComPort'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('MDM_SPEED=&quot;',/config/IP/Dialup/Option[@option = 'Speed'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('MDM_FLW=&quot;',/config/IP/Dialup/Option[@option = 'FlowControl'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('MDM_CONN=&quot;',/config/IP/Dialup/Option[@option = 'Connection'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('MDM_Init1=&quot;',/config/IP/Dialup/Option[@option = 'Init1'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('MDM_Init2=&quot;',/config/IP/Dialup/Option[@option = 'Init2'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('MDM_DSTR=&quot;',/config/IP/Dialup/Option[@option = 'DialString'],'&quot;;',$nl)"/>

  <xsl:choose>
    <xsl:when test="/config/IP/Dialup/Option[@option = 'Number'] != 'true'">
       <xsl:value-of select="concat('MDM_NUM=&quot;',/config/IP/Dialup/Option[@option = 'Number'],'&quot;;',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>MDM_NUM="";&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:choose>
    <xsl:when test="/config/IP/Dialup/Option[@option = 'Username'] != 'true'">
       <xsl:value-of select="concat('MDM_UN=&quot;',/config/IP/Dialup/Option[@option = 'Username'],'&quot;;',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>MDM_UN="";&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:choose>
    <xsl:when test="/config/IP/Dialup/Option[@option = 'Password'] != 'true'">
       <xsl:value-of select="concat('MDM_PW=&quot;',/config/IP/Dialup/Option[@option = 'Password'],'&quot;;',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>MDM_PW="";&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:value-of select="concat('MDM_MTU=&quot;',/config/IP/Dialup/Option[@option = 'MTU'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('MDM_ADDR=&quot;',/config/IP/Dialup/Option[@option = 'Address'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('MDM_GW=&quot;',/config/IP/Dialup/Option[@option = 'Gateway'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('MDM_TOUT=&quot;',/config/IP/Dialup/Option[@option = 'IdleTimeout'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('MDM_HO=&quot;',/config/IP/Dialup/Option[@option = 'Holdoff'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('MDM_MF=&quot;',/config/IP/Dialup/Option[@option = 'Maxfail'],'&quot;;',$nl)"/>
</xsl:template>

<xsl:template match="VOIP">
  <xsl:value-of select="concat('LCRAC=&quot;',@username,'&quot;;',$nl)"/>
  <xsl:value-of select="concat('LCRDTMF=&quot;',@dtmf,'&quot;;',$nl)"/>
  <xsl:value-of select="concat('LCRFROMU=&quot;',@fromuser,'&quot;;',$nl)"/>
  <xsl:value-of select="concat('LCRGWID=&quot;',@gkid,'&quot;;',$nl)"/>
  <xsl:value-of select="concat('LCRH323P=&quot;',@prefix,'&quot;;',$nl)"/>
  <xsl:value-of select="concat('LCRPROTO=&quot;',@protocol,'&quot;;',$nl)"/>
  <xsl:value-of select="concat('LCRPW=&quot;',@secret,'&quot;;',$nl)"/>
  <xsl:value-of select="concat('LCRREG=&quot;',@register,'&quot;;',$nl)"/>
  <xsl:value-of select="concat('LCRSRTP=&quot;',@srtp,'&quot;;',$nl)"/>
  <xsl:value-of select="concat('LCRSRV=&quot;',@server,'&quot;;',$nl)"/>
  <xsl:value-of select="concat('LCRVIDEO=&quot;',@novideo,'&quot;;',$nl)"/>
</xsl:template>

<xsl:template match="/config">
  <xsl:call-template name="physiface"/>
  <xsl:call-template name="ifacevlan"/>
  <xsl:call-template name="ifacealias"/>
  <xsl:value-of select="concat('NTP_SERV=&quot;',/config/IP/SysConf/Option[@option = 'NTPServer'],'&quot;;',$nl)"/>
  <xsl:call-template name="x509"/>
  <xsl:call-template name="config"/>
  <xsl:value-of select="concat('VLAN_PHY=&quot;',count(/config/IP/Interfaces/Interface[not(contains(.,'.')) and not(contains(.,':'))]),'&quot;;',$nl)"/>
  <xsl:call-template name="mdmconfig"/>
  <xsl:value-of select="concat('FWALL_INT=&quot;',/config/IP/SysConf/Option[@option = 'Internal'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('FWALL_EXT=&quot;',/config/IP/SysConf/Option[@option = 'External'],'&quot;;',$nl)"/>
  <xsl:text>DEL_DNS="0";&#xa;</xsl:text>
  <xsl:value-of select="concat('SERIAL=&quot;',/config/@serial,'&quot;;',$nl)"/>
  <xsl:apply-templates select="/config/IP/VOIP"/>
</xsl:template>
</xsl:stylesheet>
