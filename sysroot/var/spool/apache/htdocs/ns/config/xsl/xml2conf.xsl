<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no"/>

<xsl:template match="IP/Interfaces/Interface">
  <xsl:value-of select="concat('IP Interface ',translate(@name,' ','_'),' ',@ipaddr,' ',@subnet,' ',.,' ',@dhcpstart,' ',@dhcpend,' ',@bwin,' ',@bwout,' ',@macaddr,' ',@gateway,$nl)"/>
</xsl:template>

<xsl:template match="IP/WiFi">
  <xsl:value-of select="concat('IP WiFi ',.,' ',@mode,' ',@auth,' ',@type,' ',@regdom,' ',@channel,' ',@txpower,' ',@key,$nl)"/>
</xsl:template>

<xsl:template match="IP/Routes/Route">
  <xsl:value-of select="concat('IP Route ',translate(.,' ','_'),' ',@network,' ',@subnet,' ',@gateway,' ',@remote)"/>
  <xsl:if test="(@dhcpstart != '') and (@dhcpend != '')">
    <xsl:value-of select="concat(' ',@dhcpstart,' ',@dhcpend)"/>
  </xsl:if>
  <xsl:value-of select="$nl"/>
</xsl:template>

<xsl:template match="IP/GenRoutes/Route">
  <xsl:value-of select="concat('IP GenRoute ',translate(.,' ','_'),' ',@network,' ',@subnet,' ',@gateway,$nl)"/>
</xsl:template>

<xsl:template match="IP/SysConf/Option">
  <xsl:if test="(@option != 'Type') and (@option != 'Ingress') and (@option != 'Egress')">
    <xsl:value-of select="concat('IP SysConf ',@option,' ',.,$nl)"/>
  </xsl:if>
</xsl:template>

<xsl:template match="IP/GRE/Tunnels/Tunnel">
  <xsl:value-of select="concat('IP GRE Tunnel ',@local,' ',.,' ',@interface,' ',@ipsec,' ',@mtu,' ',@crlurl,$nl)"/>
</xsl:template>

<xsl:template match="IP/ESP/Tunnels/ESPTunnel">
  <xsl:value-of select="concat('IP ESP Tunnel ',@dest,' ',@local,' ',.,' ',@gateway,' ',@cipher,' ',@hash,' ',@dhgroup,$nl)"/>
</xsl:template>

<xsl:template match="IP/ESP/Nodes/Node">
  <xsl:value-of select="concat('IP ESP Access ',@local,' ',.,' ',@interface,$nl)"/>
</xsl:template>

<xsl:template match="LDAP/Config/Option">
  <xsl:value-of select="concat('IP LDAP ',@option,' ',.,$nl)"/>
</xsl:template>

<xsl:template match="IAX/Peers">
  <xsl:value-of select="concat('IP VOIP IAX ',.,' ',@password,' ',@addr,' ',@auth,' ',@context,' ',@key,$nl)"/>
</xsl:template>

<xsl:template match="SIP/Peers">
  <xsl:value-of select="concat('IP VOIP SIP ',.,' ',@password,' ',@addr,' ',@context,' ',@ext,$nl)"/>
</xsl:template>

<xsl:template match="IP/VOIP">
  <xsl:value-of select="concat('IP VOIP VBOX ',@username,' ',@secret,' ',@server,' ',@protocol,' ',@prefix,' ',@gkid,' ',@register,' ',@dtmf,' ',@fromuser,' ',@novideo,' ',@srtp,$nl)"/>
  <xsl:apply-templates select="IAX/Peers"/>
  <xsl:apply-templates select="SIP/Peers"/>
</xsl:template>

<xsl:template match="X509/Option">
  <xsl:value-of select="concat('X509 Config ',@option,' ',.,$nl)"/>
</xsl:template>

<xsl:template match="IP/Dialup/Option">
  <xsl:choose>
    <xsl:when test=". != 'true'">
      <xsl:value-of select="concat('IP Modem ',@option,' ',.,$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:choose>
        <xsl:when test="(@option = 'Number') or (@option = 'Username') or (@option = 'Password')">
          <xsl:value-of select="concat('IP Modem ',@option,' ',$nl)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:if test=". = 'true'">
            <xsl:value-of select="concat('IP Modem ',@option,$nl)"/>
          </xsl:if>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Firewall">
  <xsl:value-of select="concat('IP FW Rule ',.,' ',@ip,' ',@source,' ',@dest,' ',@proto,' ',@type,' ',@action,' ',../@name,' ',@output,' ',../../@iface,' ',@state,' ',@direction,' ',@tos,' ',@priority,$nl)"/>
</xsl:template>

<xsl:template match="IP/FW/Iface/Source">
  <xsl:value-of select="concat('IP FW SourceNetwork ',@name,' ',@ipaddr,' ',@subnet,' ',../@iface,' ',@bwin,' ',@bwout,$nl)"/>
  <xsl:apply-templates select="Firewall"/>
</xsl:template>

<xsl:template match="IP/GRE/Routes/Route">
  <xsl:value-of select="concat('IP GRE Route ',@local,' ',translate(.,'/',' '),$nl)"/>
</xsl:template>

<xsl:template match="IP/ADSL/Users/User">
  <xsl:value-of select="concat('IP ADSL_USER ',.,' ',@password,' ',@used,' ',@flag,$nl)"/>
</xsl:template>

<xsl:template match="IP/Fax/Option">
  <xsl:choose>
    <xsl:when test=". != 'true'">
      <xsl:value-of select="concat('IP FAX ',@option,' ',.,$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:if test=". = 'true'">
        <xsl:value-of select="concat('IP FAX ',@option,' ',$nl)"/>
      </xsl:if>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="IP/ADSL/Links/Link">
  <xsl:value-of select="concat('IP ADSL ',.,' ',@username,' ',@password,' ',@bwin,' ',@bwout,' ',@tos,' ',@interface,' ',@service,' ',@virtip,' ',@remip,$nl)"/>
</xsl:template>

<xsl:template match="IP/QOS/TOS">
  <xsl:value-of select="concat('IP TOS ',translate(@name,' ','_'),' ',@ipaddr,' ',@dport,' ',@sport,' ',@protocol,' ',.,' ',@priority,$nl)"/>
</xsl:template>

<xsl:template match="DNS/Config/Option">
  <xsl:value-of select="concat('DNS ',@option,' ',.,$nl)"/>
</xsl:template>

<xsl:template match="NameServer">
  <xsl:value-of select="concat('DNS NameServer ',../@domain,' ',.,$nl)"/>
</xsl:template>

<xsl:template match="Hosted/Domain">
  <xsl:variable name='domain' select='@domain'/>
  <xsl:value-of select="concat('DNS Hosted ',$domain,' ')"/>
  <xsl:if test="@key != ''">
    <xsl:value-of select="concat(@key,' ')"/>
  </xsl:if>
  <xsl:value-of select="concat(@internal,$nl)"/>
  <xsl:apply-templates select="NameServer"/>
</xsl:template>

<xsl:template match="Hosts/Host">
  <xsl:value-of select="concat('DNS Host ',.,' ',@ipaddr,' ',@macaddr,$nl)"/>
</xsl:template>

<xsl:template match="DNS">
  <xsl:apply-templates select="Config/Option"/>
  <xsl:apply-templates select="Hosts/Host"/>
  <xsl:apply-templates select="Hosted/Domain"/>
</xsl:template>

<xsl:template match="Radius/Config/Option">
  <xsl:value-of select="concat('Radius ',@option,' ',.)"/>
  <xsl:if test="@option = 'PPPoE'">
    <xsl:value-of select="concat(' ',@flag)"/>
  </xsl:if>
  <xsl:value-of select="$nl"/>
</xsl:template>

<xsl:template match="Clients/Client">
  <xsl:value-of select="concat('Radius Client ',@hostname,' ',@secret,' ',.,$nl)"/>
</xsl:template>

<xsl:template match="Realms/Realm">
  <xsl:value-of select="concat('Radius Realm ',.,' ',@authhost,' ',@accthost,' ',@rrobin,' ',@nostrip,' ',@secret,$nl)"/>
</xsl:template>

<xsl:template match="RAS/Modem">
  <xsl:value-of select="concat('Radius RAS ',.,' ',@remote,' ',@local,' ',@connection,' ',@speed,' ',@type,' ',@mtu,$nl)"/>
</xsl:template>

<xsl:template match="Radius">
  <xsl:apply-templates select="Clients/Client"/>
  <xsl:apply-templates select="Realms/Realm"/>
  <xsl:apply-templates select="RAS/Modem"/>
  <xsl:apply-templates select="Config/Option"/>
</xsl:template>

<xsl:template match="NFS/Mounts/Mount">
  <xsl:value-of select="concat('NFS Mount ',.,' ',@folder,' ',@mount,' ',@bind,' ')"/>
  <xsl:choose>
    <xsl:when test="@type = 'nfs'">
      <xsl:value-of select="concat(@backup,' ',@av)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat(@username,' ',@password,' ',@uid,' ',@gid,' ',@ro,' ',@backup,' ',@av)"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:value-of select="$nl"/>
</xsl:template>

<xsl:template match="NFS/Shares/Share">
  <xsl:value-of select="concat('NFS Share ',@ipaddr,' ',.,' ',@uid,' ',@gid,' ',@ro,' ',@squash,$nl)"/>
</xsl:template>

<xsl:template match="NFS">
  <xsl:apply-templates select="Mounts/Mount"/>
  <xsl:apply-templates select="Shares/Share"/>
</xsl:template>

<xsl:template match="Email/Config/Option">
  <xsl:value-of select="concat('Email ',@option,' ',.,$nl)"/>
</xsl:template>

<xsl:template match="Email/Collections/Collection">
  <xsl:value-of select="concat('Email POP3 ',.,' ',@username,' ',@password,' ',@domain,' ',@multidrop,' ',@envelope,' ',@usessl,' ',@protocol,' ',@smtp,$nl)"/>
</xsl:template>

<xsl:template match="Email/Filters/Filter">
  <xsl:value-of select="concat('Email Filter ',@policy,' ',@regex,' ',translate(.,' ','_'),' ',translate(@log,' ','_'),$nl)"/>
</xsl:template>

<xsl:template match="Proxy/Config/Option">
  <xsl:value-of select="concat('Proxy ',@option,' ',.,$nl)"/>
</xsl:template>

<xsl:template match="SquidAccess/Access">
  <xsl:value-of select="concat('Proxy Access ',@ipaddr,' ',@subnet,' ',.,$nl)"/>
</xsl:template>

<xsl:template match="Bypass">
  <xsl:value-of select="concat('Proxy Bypass ',.,' ',@subnet,$nl)"/>
</xsl:template>

<xsl:template match="List/ProxyFilter">
  <xsl:choose>
    <xsl:when test="@type = 'Domain'">
      <xsl:value-of select="concat('Proxy ',@filter,' URL ',.,$nl)"/>
    </xsl:when>
    <xsl:otherwise>
     <xsl:value-of select="concat('Proxy ',@filter,' ',@type,' ',.,$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="ACLS/ACL/BlackList">
  <xsl:value-of select="concat('Proxy ACL ',../@group,' ',../@time,' ',../@intime,' ',.,' ',@deny,' ',../@policy,$nl)"/>
</xsl:template>

<xsl:template match="/config/Proxy/TimeGroups/TimeGroup">
  <xsl:value-of select="concat('Proxy TimeGroup ',@group,' ',@start,' ',@end,' ',.,$nl)"/>
</xsl:template>

<xsl:template match="/config/Proxy/SourceGroups/SourceGroup/IPAddress">
  <xsl:value-of select="concat('Proxy SourceGroup ',../@group,' ',.,' ',@subnet,$nl)"/>
</xsl:template>

<xsl:template match="/config/Proxy/SourceGroups/SourceGroup">
  <xsl:apply-templates select="IPAddress"/>
</xsl:template>

<xsl:template match="ACLS/ACL">
  <xsl:value-of select="concat('Proxy TimeGroup ',@time,$nl)"/>
  <xsl:variable name='time' select='@time'/>
  <xsl:variable name='group' select='@group'/>
  <xsl:apply-templates select="/config/Proxy/TimeGroups/TimeGroup[@group = $time]"/>
  <xsl:value-of select="concat('Proxy SourceGroup ',@group,$nl)"/>
  <xsl:apply-templates select="/config/Proxy/SourceGroups/SourceGroup[@group = $group]"/>
  <xsl:apply-templates select="BlackList"/>
</xsl:template>

<xsl:template match="Redirect/WWW">
  <xsl:value-of select="concat('WWW Redirect ',.,' ',@ipaddr,' ',@interface,$nl)"/>
</xsl:template>

<xsl:template match="Proxy">
  <xsl:apply-templates select="Config/Option"/>
  <xsl:apply-templates select="SquidAccess/Access"/>
  <xsl:apply-templates select="Bypass"/>
  <xsl:apply-templates select="List/ProxyFilter"/>
  <xsl:apply-templates select="ACLS/ACL"/>
  <xsl:apply-templates select="Redirect/WWW"/>
</xsl:template>

<xsl:template match="ClamAV/Option">
  <xsl:choose>
    <xsl:when test=". != ''">
      <xsl:value-of select="concat('FileServer ',@option,' ',.,$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('FileServer ',@option,$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="FileServer/Shares/Share">
  <xsl:value-of select="concat('FileServer Share ',translate(.,' ','_'),' ',@folder,' ',@grw,' ',@group,' ',@uread,' ',@av,' ',@backup,' ',@drive,$nl)"/>
</xsl:template>

<xsl:template match="Mappings/Mapping">
  <xsl:value-of select="concat('FileServer Mapping ',.,' ',@folder,' ',@drive,$nl)"/>
</xsl:template>

<xsl:template match="FileServer">
  <xsl:value-of select="concat('FileServer Option Domain ',Setup/Option[@option = 'Domain'],$nl)"/>
  <xsl:value-of select="concat('FileServer Config ',Config/Item[starts-with(.,'netbios name')],$nl)"/>
  <xsl:value-of select="concat('FileServer Config ',Config/Item[starts-with(.,'server string')],$nl)"/>
  <xsl:value-of select="concat('FileServer Option Security ',Setup/Option[@option = 'Security'],$nl)"/>
  <xsl:value-of select="concat('FileServer Option Winbind ',Setup/Option[@option = 'Winbind'],$nl)"/>
  <xsl:value-of select="concat('FileServer Config ',Config/Item[starts-with(.,'os level')],$nl)"/>
  <xsl:value-of select="concat('FileServer Config ',Config/Item[starts-with(.,'preferred master')],$nl)"/>
  <xsl:value-of select="concat('FileServer Config ',Config/Item[starts-with(.,'local master')],$nl)"/>
  <xsl:value-of select="concat('FileServer Config ',Config/Item[starts-with(.,'domain master')],$nl)"/>
  <xsl:if test="(@homedir != '') and (@sharedir != '')">
    <xsl:value-of select="concat('FileServer Controler ',@homedir,' ',@sharedir,$nl)"/>
  </xsl:if>
  <xsl:apply-templates select="ClamAV/Option"/>
  <xsl:if test="@profile = 'true'">
    <xsl:value-of select="concat('FileServer UProfile',$nl)"/>
  </xsl:if>
  <xsl:apply-templates select="Shares/Share"/>
  <xsl:apply-templates select="Mappings/Mapping"/>
</xsl:template>

<xsl:template match="Cron/Event">
  <xsl:value-of select="concat('Cron ',translate(.,' ','_'),' ',@min,' ',@from,' ',@to,' ',@days,$nl)"/>
</xsl:template>

<xsl:template match="Directories/Directory">
  <xsl:value-of select="concat('LDAP Addressbook ',.,' ',@open,$nl)"/>
</xsl:template>

<xsl:template match="Replica">
  <xsl:value-of select="concat('LDAP Replica ',.,' ',@sid,' ',@usessl,$nl)"/>
</xsl:template>

<xsl:template match="LDAP">
  <xsl:value-of select="concat('LDAP AnonRead ',@AnonRead,$nl)"/>
  <xsl:value-of select="concat('LDAP Backup ',@Backup,$nl)"/>
  <xsl:value-of select="concat('LDAP ReplicateDN ',@ReplicateDN,$nl)"/>
  <xsl:apply-templates select="Directories/Directory"/>
  <xsl:apply-templates select="Replica"/>
</xsl:template>

<xsl:template match="SQL/Option">
  <xsl:value-of select="concat('SQL ',@option,' ',.,$nl)"/>
</xsl:template>

<xsl:template match="/config">
  <xsl:apply-templates select="IP/Interfaces/Interface"/>
  <xsl:apply-templates select="IP/Routes/Route"/>
  <xsl:apply-templates select="IP/GenRoutes/Route"/>
  <xsl:apply-templates select="IP/WiFi"/>
  <xsl:apply-templates select="IP/SysConf/Option"/>
  <xsl:apply-templates select="IP/GRE/Tunnels/Tunnel"/>
  <xsl:apply-templates select="IP/ESP/Tunnels/ESPTunnel"/>
  <xsl:apply-templates select="IP/ESP/Nodes/Node"/>
  <xsl:apply-templates select="IP/VOIP"/>
  <xsl:apply-templates select="LDAP/Config/Option"/>
  <xsl:if test="IP/SysConf/Option[@option = 'Ingress']">
    <xsl:value-of select="concat('IP TC Ingress ',IP/SysConf/Option[@option = 'Ingress'],$nl)"/>
  </xsl:if>
  <xsl:if test="IP/SysConf/Option[@option = 'Egress']">
    <xsl:value-of select="concat('IP TC Egress ',IP/SysConf/Option[@option = 'Egress'],$nl)"/>
  </xsl:if>
  <xsl:apply-templates select="X509/Option"/>
  <xsl:value-of select="concat('X509 Config Locked ',X509/@locked,$nl)"/>
  <xsl:apply-templates select="IP/Dialup/Option"/>
  <xsl:apply-templates select="IP/FW/Iface/Source"/>
  <xsl:apply-templates select="IP/GRE/Routes/Route"/>
  <xsl:apply-templates select="IP/ADSL/Users/User"/>
  <xsl:apply-templates select="IP/Fax/Option"/>
  <xsl:apply-templates select="IP/ADSL/Links/Link"/>
  <xsl:apply-templates select="IP/QOS/TOS"/>
  <xsl:apply-templates select="DNS"/>
  <xsl:apply-templates select="Radius"/>
  <xsl:apply-templates select="NFS"/>
  <xsl:apply-templates select="Email/Config/Option"/>
  <xsl:apply-templates select="Email/Collections/Collection"/>
  <xsl:apply-templates select="Email/Filters/Filter"/>
  <xsl:apply-templates select="Proxy"/>
  <xsl:apply-templates select="FileServer"/>
  <xsl:apply-templates select="Cron/Event"/>
  <xsl:apply-templates select="LDAP"/>
  <xsl:apply-templates select="SQL/Option"/>
  <xsl:if test="@serial != ''">
    <xsl:value-of select="concat('Serial ',@serial,$nl)"/>
  </xsl:if>
  <xsl:if test="IP/SysConf/Option[@option = 'Type'] != ''">
    <xsl:value-of select="concat('System Type ',IP/SysConf/Option[@option = 'Type'],$nl)"/>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>
