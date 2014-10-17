<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="extiface" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>
<xsl:variable name="loclan" select="concat(/config/IP/Interfaces/Interface[text() = $intiface]/@nwaddr,'/',/config/IP/Interfaces/Interface[text() = $intiface]/@subnet)"/>
<xsl:variable name="dynzone" select="/config/DNS/Config/Option[@option = 'DynZone']"/>
<xsl:variable name="pppint" select="/config/Radius/Config/Option[@option = 'PPPoEIF']"/>
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>
<xsl:variable name="pppip" select="/config/Radius/Config/Option[@option = 'PPPoE']"/>
<xsl:variable name="sfnew" select="'-m state --state ESTABLISHED,NEW '"/>
<xsl:variable name="sfnre" select="'-m state --state ESTABLISHED,NEW,RELATED '"/>
<xsl:variable name="sfrel" select="'-m state --state ESTABLISHED,RELATED '"/>
<xsl:variable name="sfnrel" select="'-m state --state NEW,RELATED '"/>
<xsl:variable name="sfold" select="'-m state --state ESTABLISHED '"/>
<xsl:variable name="radacport" select="/config/Radius/Config/Option[@option = 'AccPort']"/>
<xsl:variable name="pdns" select="/config/IP/SysConf/Option[@option = 'PrimaryDns']"/>
<xsl:variable name="sdns" select="/config/IP/SysConf/Option[@option = 'SecondaryDns']"/>
<xsl:variable name="security" select="/config/FileServer/Setup/Option[@option = 'Security']"/>
<xsl:variable name="realm" select="translate(/config/FileServer/Setup/Option[@option = 'ADSRealm'],$uppercase,$smallcase)"/>
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />
<xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'" />
<xsl:variable name="adserv" select="translate(/config/FileServer/Setup/Option[@option = 'ADSServer'],$uppercase,$smallcase)"/>
<xsl:param name="zcipaddr"/>

<xsl:template name="getbaseif">
  <xsl:param name="iface" select="."/>
  <xsl:choose>
    <xsl:when test="contains($iface,':')">
      <xsl:value-of select="substring-before($iface,':')"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$iface"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="acctfwsrc">
    <xsl:param name="iface"/>
    <xsl:param name="nwaddr"/>
    <xsl:param name="ipaddr"/>
    <xsl:param name="subnet"/>
    <xsl:param name="srcip"/>
    <xsl:param name="srcsn"/>

  <xsl:text>/usr/sbin/iptables -A ACCT -j RETURN ! -i </xsl:text>
  <xsl:call-template name="getbaseif">
    <xsl:with-param name="iface" select="$iface"/>
  </xsl:call-template>
  <xsl:text> -o </xsl:text>
  <xsl:call-template name="getbaseif">
    <xsl:with-param name="iface" select="$iface"/>
  </xsl:call-template>
  <xsl:value-of select="concat(' -d ',$srcip,'/',$srcsn,' ! -s ',$ipaddr,'/32',$nl)"/>

  <xsl:text>/usr/sbin/iptables -A ACCT -j RETURN -i </xsl:text>
  <xsl:call-template name="getbaseif">
    <xsl:with-param name="iface" select="$iface"/>
  </xsl:call-template>
  <xsl:text> ! -o </xsl:text>
  <xsl:call-template name="getbaseif">
    <xsl:with-param name="iface" select="$iface"/>
  </xsl:call-template>
  <xsl:value-of select="concat(' -s ',$srcip,'/',$srcsn,' ! -d ',$ipaddr,'/32',$nl)"/>

  <xsl:text>/usr/sbin/iptables -A ACCT -j RETURN -i </xsl:text>
  <xsl:call-template name="getbaseif">
    <xsl:with-param name="iface" select="$iface"/>
  </xsl:call-template>
  <xsl:value-of select="concat(' -s ',$srcip,'/',$srcsn,' -d ',$ipaddr,'/32',$nl)"/>

  <xsl:text>/usr/sbin/iptables -A ACCT -j RETURN -o </xsl:text>
  <xsl:call-template name="getbaseif">
    <xsl:with-param name="iface" select="$iface"/>
  </xsl:call-template>
  <xsl:value-of select="concat(' -d ',$srcip,'/',$srcsn,' -s ',$ipaddr,'/32',$nl)"/>
</xsl:template>

<xsl:template name="acctdhcp">
  <xsl:for-each select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32')]"> 
    <xsl:variable name="ipaddr" select="@ipaddr"/>
    <xsl:variable name="iface" select="."/>
    <xsl:variable name="nwaddr" select="@nwaddr"/>
    <xsl:variable name="subnet" select="@subnet"/>
    <xsl:for-each select="/config/IP/FW/Iface[@iface = current()]/Source">
      <xsl:value-of select="concat($nl,'#ACCT Rules For ',@name,' (',@ipaddr,'/',@subnet,') Subnet',$nl)"/>
      <xsl:call-template name="acctfwsrc">
        <xsl:with-param name="iface" select="$iface"/>
        <xsl:with-param name="nwaddr" select="$nwaddr"/>
        <xsl:with-param name="descrip" select="@name"/>
        <xsl:with-param name="ipaddr" select="$ipaddr"/>
        <xsl:with-param name="subnet" select="$subnet"/>
        <xsl:with-param name="srcip" select="@ipaddr"/>
        <xsl:with-param name="srcsn" select="@subnet"/>
      </xsl:call-template>
    </xsl:for-each>
    <xsl:if test="count(/config/IP/FW/Iface[@iface = current()]/Source[(@ipaddr = $nwaddr) and (@subnet = $subnet)]) = 0">
      <xsl:value-of select="concat($nl,'#ACCT Rules For ',.,' (',@nwaddr,'/',@subnet,') Network',$nl)"/>
      <xsl:call-template name="acctfwsrc">
        <xsl:with-param name="iface" select="$iface"/>
        <xsl:with-param name="nwaddr" select="$nwaddr"/>
        <xsl:with-param name="descrip" select="@name"/>
        <xsl:with-param name="ipaddr" select="$ipaddr"/>
        <xsl:with-param name="srcip" select="$nwaddr"/>
        <xsl:with-param name="srcsn" select="@subnet"/>
      </xsl:call-template>
    </xsl:if>
    <xsl:text>&#xa;#Activate BOOTP/DHCPD&#xa;</xsl:text>
    <xsl:text>/usr/sbin/iptables -A INPUT -j DHCPIN -p udp --dport 67:69 -i </xsl:text>
    <xsl:call-template name="getbaseif"/>
    <xsl:value-of select="concat(' -s ',@nwaddr,'/',@subnet,' -d ',@ipaddr,'/32',$nl)"/>
  </xsl:for-each>
</xsl:template>

<xsl:template name="acctgre">
  <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:variable name="ipaddr" select="@local"/>
    <xsl:variable name="iface" select="concat('gtun',position()-1)"/>
    <xsl:variable name="nwaddr" select="@nwaddr"/>
    <xsl:variable name="subnet" select="30"/>
    <xsl:for-each select="/config/IP/FW/Iface[@iface = $ipaddr]/Source">
      <xsl:value-of select="concat($nl,'#ACCT Rules For ',@name,' (',@ipaddr,'/',@subnet,') Subnet',$nl)"/>
      <xsl:call-template name="acctfwsrc">
        <xsl:with-param name="iface" select="$iface"/>
        <xsl:with-param name="nwaddr" select="$nwaddr"/>
        <xsl:with-param name="descrip" select="@name"/>
        <xsl:with-param name="ipaddr" select="$ipaddr"/>
        <xsl:with-param name="subnet" select="$subnet"/>
        <xsl:with-param name="srcip" select="@ipaddr"/>
        <xsl:with-param name="srcsn" select="@subnet"/>
      </xsl:call-template>
    </xsl:for-each>
    <xsl:if test="count(/config/IP/FW/Iface[@iface = @local]/Source[(@ipaddr = $nwaddr) and (@subnet = $subnet)]) = 0">

      <xsl:value-of select="concat($nl,'#ACCT Rules For ',$iface,' (',$nwaddr,'/',$subnet,') Network',$nl)"/>
      <xsl:call-template name="acctfwsrc">
        <xsl:with-param name="iface" select="$iface"/>
        <xsl:with-param name="nwaddr" select="$nwaddr"/>
        <xsl:with-param name="ipaddr" select="$ipaddr"/>
        <xsl:with-param name="subnet" select="$subnet"/>
        <xsl:with-param name="srcip" select="$nwaddr"/>
        <xsl:with-param name="srcsn" select="$subnet"/>
      </xsl:call-template>
    </xsl:if>
    <xsl:text>&#xa;#Activate BOOTP/DHCPD&#xa;</xsl:text>
    <xsl:text>/usr/sbin/iptables -A INPUT -j DHCPIN -p udp --dport 67:69 -i </xsl:text>
    <xsl:call-template name="getbaseif">
      <xsl:with-param name="iface" select="$iface"/>
    </xsl:call-template>
    <xsl:value-of select="concat(' -s ',$nwaddr,'/',$subnet,' -d ',$ipaddr,'/32',$nl)"/>
  </xsl:for-each>
</xsl:template>

<xsl:template name="squidrules">
  <xsl:if test="/config/Proxy/Config/Option[@option = 'Parent'] != ''">
    <xsl:variable name="proxyip" select="substring-before(/config/Proxy/Config/Option[@option = 'Parent'],':')"/>
    <xsl:variable name="webcatch" select="substring-after(/config/Proxy/Config/Option[@option = 'Parent'],':')"/>
    <xsl:choose>
      <xsl:when test="$proxyip = $intip">
#Allow Local Access From Squid To Trend Anti Virus Proxy
/usr/sbin/iptables -A LOOPIN -j ACCEPT -i lo -p tcp $sfnew -s $localip/32 --sport $webcatch -d $extip --dport 1024:65535
/usr/sbin/iptables -A LOOPOUT -j ACCEPT -o lo -p tcp ! --syn $sfold -d $localip/32 --dport $webcatch -s $extip --sport 1024:65535
/usr/sbin/iptables -A LOOPOUT -j ACCEPT -o lo -p tcp ! --syn $sfold -s $localip/32 --sport $webcatch -d $extip --dport 1024:65535
/usr/sbin/iptables -A LOOPIN -j ACCEPT -i lo -p tcp $sfnew -d $localip/32 --dport $webcatch -s $extip --sport 1024:65535
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>#Allow Access To External Proxy Parent&#xa;</xsl:text>
        <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -d ',$proxyip,'/32 --dport ',$webcatch,' --sport 1024:65535',$nl,$nl)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:if>

  <xsl:for-each select="/config/Proxy/Bypass">
    <xsl:variable name="netip" select="concat(.,'/',@subnet)"/>
    <xsl:value-of select="concat('#Allow TX Proxy Bypass For ',$netip,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A PROXYBYPASS -j ACCEPT -t nat -i ',$intiface,' -p tcp ',$sfnew,' -s ',$loclan,' --sport 1024:65535 -d ',$netip,$nl)"/>
    <xsl:for-each select="/config/IP/Routes/Route">
      <xsl:value-of select="concat('/usr/sbin/iptables -A PROXYBYPASS -j ACCEPT -t nat -i ',$intiface,' -p tcp ',$sfnew,' -s ',@network,'/',@subnet,' --sport 1024:65535 -d ',$netip,$nl)"/>
    </xsl:for-each>
    <xsl:text>&#xa;</xsl:text>
  </xsl:for-each>

  <xsl:choose>
    <xsl:when test="count(/config/Proxy/SquidAccess/Access) &gt; 0">
      <xsl:for-each select="/config/Proxy/SquidAccess/Access[. = 'true']">
        <xsl:text>#Allow Transparent Proxy&#xa;</xsl:text>
        <xsl:value-of select="concat('/usr/sbin/iptables -A TXPROXY -p tcp -t nat -j REDIRECT --to-port 3129 -i ',$intiface,' -s ',@ipaddr,'/',@subnet,$nl,$nl)"/>
      </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>
      <xsl:for-each select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32')]">
        <xsl:text>#Allow Transparent Proxy&#xa;</xsl:text>
        <xsl:value-of select="concat('/usr/sbin/iptables -A TXPROXY -p tcp -t nat -j REDIRECT --to-port 3129 -i ',$intiface,' -s ',@nwaddr,'/',@subnet,$nl,$nl)"/>
      </xsl:for-each>
      <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
        <xsl:text>#Allow Transparent Proxy&#xa;</xsl:text>
        <xsl:value-of select="concat('/usr/sbin/iptables -A TXPROXY -p tcp -t nat -j REDIRECT --to-port 3129 -i ',$intiface,' -s ',@nwaddr,'/30',$nl,$nl)"/>
      </xsl:for-each>
      <xsl:for-each select="/config/IP/Routes/Route">
        <xsl:text>#Allow Transparent Proxy&#xa;</xsl:text>
        <xsl:value-of select="concat('/usr/sbin/iptables -A TXPROXY -p tcp -t nat -j REDIRECT --to-port 3129 -i ',$intiface,' -s ',@network,'/',@subnet,$nl,$nl)"/>
      </xsl:for-each>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:for-each select="/config/IP/Interfaces/Interface">
    <xsl:variable name="iface" value="."/>
    <xsl:if test="/config/IP/WiFi[. = current()]/@type = 'Hotspot'">
      <xsl:text>#Allow Transparent Proxy&#xa;</xsl:text>
      <xsl:value-of select="concat('/usr/sbin/iptables -A TXPROXY -p tcp -t nat -j REDIRECT --to-port 3129 -i ',$intiface,' -s ',@nwaddr,'/',@subnet,$nl,$nl)"/>
    </xsl:if>
  </xsl:for-each>
</xsl:template>

<xsl:template name="tunrules">
  <xsl:param name="iface"/>
  <xsl:param name="ipaddr"/>
  <xsl:param name="srcnet"/>

  <xsl:value-of select="concat('#Tunnel Rules For ',$iface,' ',$srcnet,$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j SYSIN -i ',$iface,' -s ',$srcnet,' -d ',$ipaddr,'/32',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j SYSOUT -i ',$iface,' -s ',$srcnet,' -d ',$ipaddr,'/32',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j SYSIN -o ',$iface,' -d ',$srcnet,' -s ',$ipaddr,'/32',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j SYSOUT -o ',$iface,' -d ',$srcnet,' -s ',$ipaddr,'/32',$nl)"/>

  <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j SYSIN -i ',$iface,' -s ',$srcnet,' -d ',$loclan,$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j SYSOUT -i ',$iface,' -s ',$srcnet,' -d ',$loclan,$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j SYSOUT -o ',$iface,' -d ',$srcnet,' -s ',$loclan,$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j SYSIN -o ',$iface,' -d ',$srcnet,' -s ',$loclan,$nl)"/>

  <xsl:value-of select="concat('/sbin/ip route add ',$srcnet,' dev ',$iface,' scope link src ',$intip,' table VPN',$nl)"/>

  <xsl:value-of select="concat('/usr/sbin/iptables -A FORWARD -j ACCEPT -i ',$iface,' -o gtun+',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A FORWARD -j ACCEPT -o ',$iface,' -i gtun+',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A FORWARD -j ACCEPT -i ',$iface,' -o ',$intiface,' -s ',$srcnet,' -d ',$loclan,$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A FORWARD -j ACCEPT -o ',$iface,' -i ',$intiface,' -d ',$srcnet,' -s ',$loclan,$nl,$nl)"/>
</xsl:template>
	
<xsl:template name="ifaceconf">
  <xsl:param name="ipaddr" select="@ipaddr"/>
  <xsl:param name="nwaddr" select="@nwaddr"/>
  <xsl:param name="subnet" select="@subnet"/>
  <xsl:param name="remote" select="@remote"/>
  <xsl:param name="iface" select="."/>

  <xsl:if test="($iface != $extiface) or ($extcon = 'ADSL')">
    <xsl:value-of select="concat('#SIP For ',$ipaddr,$nl)"/>
    <xsl:text>/usr/sbin/iptables -I SIPOUT -j ACCEPT -p udp -o </xsl:text>
    <xsl:call-template name="getbaseif">
      <xsl:with-param name="iface" select="$iface"/>
    </xsl:call-template>
    <xsl:value-of select="concat(' -m state --state NEW,RELATED,ESTABLISHED --sport 10000:20000 -s ',$intip,'/32 --dport 1024:65535',$nl)"/>
    <xsl:text>/usr/sbin/iptables -I SIPOUT -j ACCEPT -p udp -o </xsl:text>
    <xsl:call-template name="getbaseif">
      <xsl:with-param name="iface" select="$iface"/>
    </xsl:call-template>
    <xsl:value-of select="concat(' -m state --state NEW,ESTABLISHED --sport 5000 -s ',$intip,'/32 --dport 1024:65535',$nl)"/>
    <xsl:text>/usr/sbin/iptables -I SIPOUT -j ACCEPT -p udp -o </xsl:text>
    <xsl:call-template name="getbaseif">
      <xsl:with-param name="iface" select="$iface"/>
    </xsl:call-template>
    <xsl:value-of select="concat(' -m state --state NEW,ESTABLISHED --sport 5060 -s ',$intip,'/32 --dport 1024:65535',$nl)"/>
    <xsl:text>/usr/sbin/iptables -I SIPOUT -j ACCEPT -p tcp -o </xsl:text>
    <xsl:call-template name="getbaseif">
      <xsl:with-param name="iface" select="$iface"/>
    </xsl:call-template>
    <xsl:value-of select="concat(' -m state --state NEW,ESTABLISHED --sport 5060:5061 -s ',$intip,'/32 --dport 1024:65535',$nl)"/>
    <xsl:if test="$iface != $intiface">
      <xsl:text>/usr/sbin/iptables -I SIPOUT -j ACCEPT -p udp -o </xsl:text>
      <xsl:call-template name="getbaseif">
        <xsl:with-param name="iface" select="$iface"/>
      </xsl:call-template>
      <xsl:value-of select="concat(' -m state --state NEW,RELATED,ESTABLISHED --sport 10000:20000 -s ',$ipaddr,'/32 --dport 1024:65535',$nl)"/>
      <xsl:text>/usr/sbin/iptables -I SIPOUT -j ACCEPT -p udp -o </xsl:text>
      <xsl:call-template name="getbaseif">
        <xsl:with-param name="iface" select="$iface"/>
      </xsl:call-template>
      <xsl:value-of select="concat(' -m state --state NEW,ESTABLISHED --sport 5000 -s ',$ipaddr,'/32 --dport 1024:65535',$nl)"/>
      <xsl:text>/usr/sbin/iptables -I SIPOUT -j ACCEPT -p udp -o </xsl:text>
      <xsl:call-template name="getbaseif">
        <xsl:with-param name="iface" select="$iface"/>
      </xsl:call-template>
      <xsl:value-of select="concat(' -m state --state NEW,ESTABLISHED --sport 5060 -s ',$ipaddr,'/32 --dport 1024:65535',$nl)"/>
      <xsl:text>/usr/sbin/iptables -I SIPOUT -j ACCEPT -p tcp -o </xsl:text>
      <xsl:call-template name="getbaseif">
        <xsl:with-param name="iface" select="$iface"/>
      </xsl:call-template>
      <xsl:value-of select="concat(' -m state --state NEW,ESTABLISHED --sport 5060:5061 -s ',$ipaddr,'/32 --dport 1024:65535',$nl)"/>
    </xsl:if>
    <xsl:text>/usr/sbin/iptables -I SIPIN -j VOIPIN -p udp -i </xsl:text>
    <xsl:call-template name="getbaseif">
      <xsl:with-param name="iface" select="$iface"/>
    </xsl:call-template>
    <xsl:value-of select="concat(' --sport 1024:65535 -d ',$intip,'/32',$nl)"/>
  </xsl:if>

  <xsl:choose>
    <xsl:when test="starts-with($iface,'gtun')">
      <xsl:for-each select="/config/IP/FW/Iface[@iface = $ipaddr]/Source">
        <xsl:call-template name="tunrules">
          <xsl:with-param name="iface" select="$iface"/>
          <xsl:with-param name="ipaddr" select="$ipaddr"/>
          <xsl:with-param name="srcnet" select="concat(@ipaddr,'/',@subnet)"/>
        </xsl:call-template>
      </xsl:for-each>
      <xsl:call-template name="tunrules">
        <xsl:with-param name="iface" select="$iface"/>
        <xsl:with-param name="ipaddr" select="$ipaddr"/>
        <xsl:with-param name="srcnet" select="concat($remote,'/32')"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>/usr/sbin/iptables -A INPUT -j ACCEPT -i </xsl:text>
      <xsl:call-template name="getbaseif">
        <xsl:with-param name="iface" select="$iface"/>
      </xsl:call-template>
      <xsl:value-of select="concat(' -s ',$nwaddr,'/',$subnet,' -p tcp --dport 3129',$nl)"/>
      <xsl:text>/usr/sbin/iptables -A INPUT -j SYSIN -i </xsl:text>
      <xsl:call-template name="getbaseif">
        <xsl:with-param name="iface" select="$iface"/>
      </xsl:call-template>
      <xsl:value-of select="concat(' -s ',$nwaddr,'/',$subnet,' -d ',$ipaddr,'/32',$nl)"/>
      <xsl:text>/usr/sbin/iptables -A OUTPUT -j SYSOUT -o </xsl:text>
      <xsl:call-template name="getbaseif">
        <xsl:with-param name="iface" select="$iface"/>
      </xsl:call-template>
      <xsl:value-of select="concat(' -d ',$nwaddr,'/',$subnet,' -s ',$ipaddr,'/32',$nl)"/>

      <xsl:if test="$intip != $ipaddr">
        <xsl:text>/usr/sbin/iptables -A INPUT -j SYSIN -i </xsl:text>
        <xsl:call-template name="getbaseif">
          <xsl:with-param name="iface" select="$iface"/>
        </xsl:call-template>
        <xsl:value-of select="concat(' -s ',$nwaddr,'/',$subnet,' -d ',$intip,'/32',$nl)"/>
      </xsl:if>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:if test="$pdns != ''">
    <xsl:text>/usr/sbin/iptables -A INPUT -j DNSIN -i </xsl:text>
    <xsl:call-template name="getbaseif">
      <xsl:with-param name="iface" select="$iface"/>
    </xsl:call-template>
    <xsl:value-of select="concat(' -s ',$nwaddr,'/',$subnet,' -d ',$pdns,$nl)"/>
    <xsl:text>/usr/sbin/iptables -A OUTPUT -j DNSOUT -o </xsl:text>
    <xsl:call-template name="getbaseif">
      <xsl:with-param name="iface" select="$iface"/>
    </xsl:call-template>
    <xsl:value-of select="concat(' -d ',$nwaddr,'/',$subnet,' -s ',$pdns,$nl)"/>
    <xsl:text>/usr/sbin/iptables -A FORWARD -j DNSFWD -i </xsl:text>
    <xsl:call-template name="getbaseif">
      <xsl:with-param name="iface" select="$iface"/>
    </xsl:call-template>
    <xsl:value-of select="concat(' -s ',$nwaddr,'/',$subnet,' -d ',$pdns,$nl)"/>
  </xsl:if>

  <xsl:if test="$sdns != ''">
    <xsl:text>/usr/sbin/iptables -A INPUT -j DNSIN -i </xsl:text>
    <xsl:call-template name="getbaseif">
      <xsl:with-param name="iface" select="$iface"/>
    </xsl:call-template>
    <xsl:value-of select="concat(' -s ',$nwaddr,'/',$subnet,' -d ',$sdns,$nl)"/>
    <xsl:text>/usr/sbin/iptables -A OUTPUT -j DNSOUT -o </xsl:text>
    <xsl:call-template name="getbaseif">
      <xsl:with-param name="iface" select="$iface"/>
    </xsl:call-template>
    <xsl:value-of select="concat(' -d ',$nwaddr,'/',$subnet,' -s ',$sdns,$nl)"/>
    <xsl:text>/usr/sbin/iptables -A FORWARD -j DNSFWD -i </xsl:text>
    <xsl:call-template name="getbaseif">
      <xsl:with-param name="iface" select="$iface"/>
    </xsl:call-template>
    <xsl:value-of select="concat(' -s ',$nwaddr,'/',$subnet,' -d ',$sdns,$nl)"/>
  </xsl:if>
  <xsl:text>/usr/sbin/iptables -A INPUT -j MCASTIN -i </xsl:text>
  <xsl:call-template name="getbaseif">
    <xsl:with-param name="iface" select="$iface"/>
  </xsl:call-template>
  <xsl:value-of select="concat(' -s ',$nwaddr,'/',$subnet,$nl)"/>
  <xsl:text>/usr/sbin/iptables -A OUTPUT -j MCASTOUT -o </xsl:text>
  <xsl:call-template name="getbaseif">
    <xsl:with-param name="iface" select="$iface"/>
  </xsl:call-template>
  <xsl:value-of select="concat(' -s ',$nwaddr,'/',$subnet,$nl)"/>
</xsl:template>

<xsl:template name="smbadfw">
  <xsl:param name="dcnme"/>
  <xsl:param name="iface"/>

<!--
XXX NEED TESTiNG
      if ($hosts{$adsdc} ne "") {
        ($dcnme,$dcipaddr)=split(/\|/,$hosts{$adsdc});
      } elsif ($hosts{$dcfqdn} ne "") {
        ($dcnme,$dcipaddr)=split(/\|/,$hosts{$dcfqdn});
      }

      if ($dcipaddr ne "") {
        $adsdc=$dcipaddr . "/32";
      } else {
        $dcnme=$adsdc;
        $adsdc=$adsdc . "." . $smboption{'ADSRealm'};
      }
-->
  <xsl:variable name="adsdc" select="concat($dcnme,'.',$realm)"/>

  <xsl:value-of select="concat('#Allow Connections To ADS Server ',$dcnme,' (',$iface,')',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SMBIN -j ACCEPT -i ',$iface,' -p tcp -m state --state ESTABLISHED -d ',$intip,'/32 --sport 389 -s ',$adsdc,' --dport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SMBIN -j ACCEPT -i ',$iface,' -p udp -m state --state ESTABLISHED -d ',$intip,'/32 --sport 389 -s ',$adsdc,' --dport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SMBIN -j ACCEPT -i ',$iface,' -p udp -m state --state ESTABLISHED -d ',$intip,'/32 --sport 88 -s ',$adsdc,' --dport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SMBIN -j ACCEPT -i ',$iface,' -p tcp -m state --state ESTABLISHED -d ',$intip,'/32 --sport 88 -s ',$adsdc,' --dport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SMBIN -j ACCEPT -i ',$iface,' -p udp -m state --state ESTABLISHED -d ',$intip,'/32 --sport 88 -s ',$adsdc,' --dport 88',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SMBIN -j ACCEPT -i ',$iface,' -p udp -m state --state ESTABLISHED -d ',$intip,'/32 --sport 464 -s ',$adsdc,' --dport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SMBIN -j ACCEPT -i ',$iface,' -p udp -m state --state ESTABLISHED -d ',$intip,'/32 --sport 500 -s ',$adsdc,' --dport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SMBOUT -j ACCEPT -o ',$iface,' -p tcp -m state --state NEW,ESTABLISHED -s ',$intip,'/32 --dport 389 -d ',$adsdc,' --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SMBOUT -j ACCEPT -o ',$iface,' -p udp -m state --state NEW,ESTABLISHED -s ',$intip,'/32 --dport 389 -d ',$adsdc,' --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SMBOUT -j ACCEPT -o ',$iface,' -p udp -m state --state NEW,ESTABLISHED -s ',$intip,'/32 --dport 88 -d ',$adsdc,' --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SMBOUT -j ACCEPT -o ',$iface,' -p tcp -m state --state NEW,ESTABLISHED -s ',$intip,'/32 --dport 88 -d ',$adsdc,' --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SMBOUT -j ACCEPT -o ',$iface,' -p udp -m state --state NEW,ESTABLISHED -s ',$intip,'/32 --dport 88 -d ',$adsdc,' --sport 88',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SMBOUT -j ACCEPT -o ',$iface,' -p udp -m state --state NEW,ESTABLISHED -s ',$intip,'/32 --dport 464 -d ',$adsdc,' --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SMBOUT -j ACCEPT -o ',$iface,' -p udp -m state --state NEW,ESTABLISHED -s ',$intip,'/32 --dport 500 -d ',$adsdc,' --sport 1024:65535',$nl)"/>
</xsl:template>

<xsl:template name="adservrules">
  <xsl:param name="servers" select="$adserv"/>
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

  <xsl:call-template name="smbadfw">
    <xsl:with-param name="iface" select="$intiface"/>
    <xsl:with-param name="dcnme" select="$active"/>
  </xsl:call-template>
  <xsl:call-template name="smbadfw">
    <xsl:with-param name="iface" select="'gtun+'"/>
    <xsl:with-param name="dcnme" select="$active"/>
  </xsl:call-template>
  <xsl:call-template name="smbadfw">
    <xsl:with-param name="iface" select="'vpn+'"/>
    <xsl:with-param name="dcnme" select="$active"/>
  </xsl:call-template>

  <xsl:if test="$next != ''">
    <xsl:call-template name="adservrules">
      <xsl:with-param name="servers" select="$next"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="smbads">
  <xsl:if test="$security = 'ADS'">
    <xsl:text>/usr/sbin/iptables -A SMBNFSIN -j SMBIN -p tcp --sport 88
/usr/sbin/iptables -A SMBNFSIN -j SMBIN -p udp --sport 389
/usr/sbin/iptables -A SMBNFSIN -j SMBIN -p udp --sport 464
/usr/sbin/iptables -A SMBNFSIN -j SMBIN -p tcp --sport 139
/usr/sbin/iptables -A SMBNFSIN -j SMBIN -p tcp --sport 389
/usr/sbin/iptables -A SMBNFSIN -j SMBIN -p tcp --sport 445
/usr/sbin/iptables -A SMBNFSIN -j SMBIN -p udp --sport 500
/usr/sbin/iptables -A SMBNFSOUT -j SMBOUT -p tcp --dport 88
/usr/sbin/iptables -A SMBNFSOUT -j SMBOUT -p udp --dport 389
/usr/sbin/iptables -A SMBNFSOUT -j SMBOUT -p udp --dport 464
/usr/sbin/iptables -A SMBNFSOUT -j SMBOUT -p tcp --dport 139
/usr/sbin/iptables -A SMBNFSOUT -j SMBOUT -p tcp --dport 389
/usr/sbin/iptables -A SMBNFSOUT -j SMBOUT -p tcp --dport 445
/usr/sbin/iptables -A SMBNFSOUT -j SMBOUT -p udp --dport 500
</xsl:text>
    <xsl:call-template name="adservrules"/>
  </xsl:if>
</xsl:template>

<xsl:template name="sysinout">
  <xsl:text>/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp ! --syn -m state --state ESTABLISHED
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp --tcp-flags SYN,ACK,PSH ACK,PSH -m state --state ESTABLISHED
/usr/sbin/iptables -A SYSIN -j ACCEPT ! -p tcp -m state --state ESTABLISHED
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p tcp ! --syn -m state --state ESTABLISHED
/usr/sbin/iptables -A SYSOUT -j ACCEPT ! -p tcp -m state --state ESTABLISHED

#Allow Related Traffic
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state NEW,RELATED --sport 1024:65535 --dport 1024:65535
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state RELATED --sport 1024:65535 --dport 1024:65535
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p udp -m state --state RELATED --dport 1024:65535 --sport 1024:65535

#RIP
/usr/sbin/iptables -A SYSIN -j ACCEPT -m state --state NEW  -p udp --sport 520 --dport 520
/usr/sbin/iptables -A SYSOUT -j ACCEPT -m state --state NEW  -p udp --dport 520 --sport 520
/usr/sbin/iptables -A SYSOUT -j ACCEPT -m state --state NEW,INVALID  -p udp --dport 520 --sport 520

#BGP
/usr/sbin/iptables -A SYSIN -j ACCEPT -m state --state NEW  -p tcp --sport 1024:65535 --dport 179
/usr/sbin/iptables -A SYSOUT -j ACCEPT -m state --state NEW,INVALID  -p tcp --dport 1024:65535 --sport 179
/usr/sbin/iptables -A SYSOUT -j ACCEPT -m state --state NEW  -p tcp --dport 179 --sport 1024:65535

#OSPF
/usr/sbin/iptables -A SYSIN -j ACCEPT -m state --state NEW -p ospf
/usr/sbin/iptables -A SYSOUT -j ACCEPT -m state --state NEW,INVALID -p ospf

#HylaFax
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 4559
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p tcp -m state --state NEW,INVALID --dport 1024:65535 --sport 4559
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p tcp -m state --state NEW  --dport 1024:65535 --sport 4558

#FTP
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 21
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p tcp -m state --state NEW,INVALID  --dport 1024:65535 --sport 21
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p tcp -m state --state RELATED,NEW --sport 20 --dport 1024:65535
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p tcp -m state --state RELATED,NEW --sport 1024:65535 --dport 1024:65535

#FTPS
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 990
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p tcp -m state --state NEW,INVALID  --dport 1024:65535 --sport 990
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p tcp -m state --state NEW --sport 989 --dport 1024:65535

#DNS
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state NEW  --sport 1024:65535 --dport 53
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state NEW  --sport 53 --dport 53
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 53

#LDAP
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 389
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 636

#NTP
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state NEW  --sport 1024:65535 --dport 123
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state NEW  --sport 123 --dport 123

#MySQL
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 3306

#PGSQL
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 5432

#Orb
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 2809
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p tcp -m state --state NEW,INVALID --dport 1024:65535 --sport 2809

#E4L
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 1024:65535

#SMTP
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 25
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 587

#POP3
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 110

#POP3S
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 995

#SSH
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 0:65535 --dport 22

#IDENT
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p tcp -m state --state NEW  --dport 113 --sport 1024:65535
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p tcp -m state --state NEW,INVALID  --dport 113 --sport 1024:65535

#IMAP
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 143
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 993

#Trend
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 1812

#Asterisk FOP
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 4445

#HTTP
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 80
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p tcp -m state --state NEW --dport 80 --sport 1024:65535

#HTTPS
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 443

#HTTPS Management
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 666

#Proxy Server
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 3128
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 3129
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 8080

#NFS TCP/UDP
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 2049 
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 2049

#LPD
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 515

#IPSEC
/usr/sbin/iptables -A SYSIN -j ACCEPT -m state --state NEW  -p udp --sport 500 --dport 500
/usr/sbin/iptables -A SYSOUT -j ACCEPT -m state --state NEW  -p udp --dport 500 --sport 500
/usr/sbin/iptables -A SYSIN -j ACCEPT -m state --state NEW  -p udp --sport 1024:65535 --dport 500
/usr/sbin/iptables -A SYSOUT -j ACCEPT -m state --state NEW  -p udp --dport 1024:65535 --sport 500

#SMB
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 137:138 --dport 137:138
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p udp -m state --state NEW --sport 137:138 --dport 137:138
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 137:138
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state NEW --dport 1024:65535 --sport 137:138
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 137:138
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p udp -m state --state NEW --dport 1024:65535 --sport 137:138

/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 139
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 139
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 873

/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 445
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 445
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 548

#SIP
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 5000
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 5060
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 5060:5061

#H323
/usr/sbin/iptables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 1720:1722
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 1718:1729
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p udp -m state --state NEW --dport 1024:65535 --sport 1718:1722
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p udp -m state --state NEW --dport 1024:65535 --sport 1718:1722
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp --sport 1719 --dport 1719 -m state --state NEW
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p udp --dport 1719 --sport 1719 -m state --state NEW

#MGCP
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 2727

#IAX
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 4569

#IAX2
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 5036

#STUN
/usr/sbin/iptables -A SYSIN -j ACCEPT -p udp -m state --state ESTABLISHED,NEW --sport 3478:3479 --dport 1024:65535
/usr/sbin/iptables -A SYSOUT -j ACCEPT -p udp -m state --state NEW,INVALID --dport 3478:3479 --sport 1024:65535

</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>#!/bin/bash&#xa;&#xa;</xsl:text>
  <xsl:choose>
    <xsl:when test="($extiface != $intiface) or ($extcon = 'ADSL')">
      <xsl:call-template name="firewall"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="nofirewall"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="nofirewall">
  <xsl:text>
#Flush Tables And Chains And Exit 
/usr/sbin/iptables -F
/usr/sbin/iptables -X
/usr/sbin/iptables -t nat -F
/usr/sbin/iptables -t nat -X
/usr/sbin/iptables -t mangle -F
/usr/sbin/iptables -t mangle -X

/usr/sbin/iptables -P INPUT ACCEPT
/usr/sbin/iptables -P OUTPUT ACCEPT
/usr/sbin/iptables -P FORWARD ACCEPT

/usr/sbin/iptables -N 3GOUT
/usr/sbin/iptables -N 3GIN
/usr/sbin/iptables -t nat -N SIPMAP
/usr/sbin/iptables -t nat -N SIPNAT
/usr/sbin/iptables -t nat -N 3GNAT
/usr/sbin/iptables -t nat -N NATMAPI
/usr/sbin/iptables -t nat -N NATMAPO
/usr/sbin/iptables -t nat -N MANGLE
/usr/sbin/iptables -t nat -N SYSNAT

/usr/sbin/iptables -t nat -A PREROUTING -j DNAT -t nat -p tcp ! -d </xsl:text><xsl:value-of select="concat($intip, ' --to-destination ',$intip)"/><xsl:text> --sport 1024:65535 --dport 80
/usr/sbin/iptables -t nat -A PREROUTING -j SIPMAP
/usr/sbin/iptables -t nat -A PREROUTING -j NATMAPI
/usr/sbin/iptables -t nat -A POSTROUTING -j SIPNAT -p udp -s </xsl:text><xsl:value-of select="$intip"/><xsl:text> --sport 5000 --dport 1024:65535
/usr/sbin/iptables -t nat -A POSTROUTING -j SIPNAT -p udp -s </xsl:text><xsl:value-of select="$intip"/><xsl:text> --sport 5060 --dport 1024:65535
/usr/sbin/iptables -t nat -A POSTROUTING -j SIPNAT -p udp -s </xsl:text><xsl:value-of select="$intip"/><xsl:text> --sport 4569 --dport 1024:65535
/usr/sbin/iptables -t nat -A POSTROUTING -j SIPNAT -p tcp -s </xsl:text><xsl:value-of select="$intip"/><xsl:text> --sport 5060:5061 --dport 1024:65535
/usr/sbin/iptables -t nat -A POSTROUTING -j SIPNAT -p udp -s </xsl:text><xsl:value-of select="$intip"/><xsl:text> --sport 1718:1720 --dport 1024:65535
/usr/sbin/iptables -t nat -A POSTROUTING -j MANGLE -o ppp+
/usr/sbin/iptables -t nat -A POSTROUTING -j NATMAPO
/usr/sbin/ip6tables -A INPUT -j DROP ! -s fc00::/7 -d fc00::/7

</xsl:text>
  <xsl:for-each select="/config/IP/Interfaces/Interface[(. != $intiface) and (. != $extiface) and 
          (@ipaddr != '0.0.0.0') and (@subnet != '32')]">
    <xsl:value-of select="concat('#SIP/RTP Proxy For ',.,' ',@ipaddr,$nl)"/>
    <xsl:text>/usr/sbin/iptables -t nat -I SIPNAT -j SNAT -o </xsl:text>
    <xsl:call-template name="getbaseif"/>
    <xsl:value-of select="concat(' -d ',@nwaddr,'/',@subnet,' --to-source ',@ipaddr,$nl,$nl)"/>
  </xsl:for-each>

  <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:value-of select="concat('#SIP/RTP Proxy For gtun',position()-1,' ',@local,$nl)"/>
    <xsl:text>/usr/sbin/iptables -t nat -I SIPNAT -j SNAT -o </xsl:text>
    <xsl:value-of select="concat('gtun',position()-1,' -d ',@nwaddr,'/30',' --to-source ',@local,$nl,$nl)"/>
  </xsl:for-each>
<xsl:text>
if [ -x /etc/firewall.local ];then
  /etc/firewall.local
fi;
</xsl:text>
</xsl:template>

<xsl:template name="firewall">
  <xsl:text>#
#Firewall Startup
#

#Add Access For DHCP On Wan/DMZ
#Add Access For Web Redirects

if [ "$1" == "startup" ];then
  /usr/sbin/iptables -F
  /usr/sbin/iptables -X
  /usr/sbin/iptables -t nat -F
  /usr/sbin/iptables -t nat -X
  /usr/sbin/iptables -t mangle -F
  /usr/sbin/iptables -t mangle -X

  /usr/sbin/iptables -N DENY
  /usr/sbin/iptables -N ICMP
  /usr/sbin/iptables -N ACCT
  /usr/sbin/iptables -N 3GIN
  /usr/sbin/iptables -N 3GOUT
  /usr/sbin/iptables -N MANGLEFWD
  /usr/sbin/iptables -N MANGLEIN
  /usr/sbin/iptables -N MANGLEOUT
  /usr/sbin/iptables -N LOOPIN
  /usr/sbin/iptables -N LOOPOUT
#  /usr/sbin/iptables -N PROXYIN
#  /usr/sbin/iptables -N PROXYOUT
  /usr/sbin/iptables -N SMBIN
  /usr/sbin/iptables -N SMBOUT
  /usr/sbin/iptables -N NFSIN
  /usr/sbin/iptables -N NFSOUT
  /usr/sbin/iptables -N SMBNFSIN
  /usr/sbin/iptables -N SMBNFSOUT
  /usr/sbin/iptables -N SYSIN
  /usr/sbin/iptables -N DNSIN
  /usr/sbin/iptables -N DHCPIN
  /usr/sbin/iptables -N DHCPOUT
  /usr/sbin/iptables -N VOIPIN
  /usr/sbin/iptables -N VOIPOUT
  /usr/sbin/iptables -N MCASTIN
  /usr/sbin/iptables -N SYSOUT
  /usr/sbin/iptables -N SIPOUT
  /usr/sbin/iptables -N SIPIN
  /usr/sbin/iptables -N DNSOUT
  /usr/sbin/iptables -N MCASTOUT
  /usr/sbin/iptables -N VPNCIN
  /usr/sbin/iptables -N VPNCOUT
  /usr/sbin/iptables -N OVPNIN
  /usr/sbin/iptables -N OVPNOUT
  /usr/sbin/iptables -N VPNFWD
  /usr/sbin/iptables -N OVPNFWD
  /usr/sbin/iptables -N DNSFWD
  /usr/sbin/iptables -N TARPIT
  /usr/sbin/iptables -N DEFIN
  /usr/sbin/iptables -N DEFOUT
#  /usr/sbin/iptables -N VPNLIN
#  /usr/sbin/iptables -N VPNLOUT
  /usr/sbin/iptables -N SBSRULESI
  /usr/sbin/iptables -N LOCALIN
  /usr/sbin/iptables -N LOCALOUT
  /usr/sbin/iptables -N SBSRULESO
  /usr/sbin/iptables -N LOCALFWD
  /usr/sbin/iptables -N SYSFWD
  /usr/sbin/iptables -N WANFWD
  /usr/sbin/iptables -N PPPIN
  /usr/sbin/iptables -N PPPOUT
  /usr/sbin/iptables -N PPPFWD
#  /usr/sbin/iptables -N PROXYFWD
  /usr/sbin/iptables -N WEBACCESSI
  /usr/sbin/iptables -N WEBACCESSO
  /usr/sbin/iptables -N HOTSPOTI
  /usr/sbin/iptables -N DYNAMICIPI
  /usr/sbin/iptables -N DYNAMICIPO
  /usr/sbin/iptables -t nat -N DYNAMICPRE
  /usr/sbin/iptables -N HOTSPOTO
  /usr/sbin/iptables -N GWOUT
  /usr/sbin/iptables -N GWIN
  /usr/sbin/iptables -N IP6RD
  /usr/sbin/iptables -N IP6RDDSL

  /usr/sbin/iptables -t nat -N SIPLB
  /usr/sbin/iptables -t nat -N SIPMAP
  /usr/sbin/iptables -t nat -N SIPNAT
  /usr/sbin/iptables -t nat -N 3GNAT
  /usr/sbin/iptables -t nat -N NATMAPI
  /usr/sbin/iptables -t nat -N NATMAPO
  /usr/sbin/iptables -t nat -N WEBACCESS
  /usr/sbin/iptables -t nat -N PROXYBYPASS
  /usr/sbin/iptables -t nat -N TXPROXY
  /usr/sbin/iptables -t nat -N NONAT
  /usr/sbin/iptables -t nat -N NOFWDNAT
  /usr/sbin/iptables -t nat -N NOPPPNAT
  /usr/sbin/iptables -t nat -N VPNNAT
  /usr/sbin/iptables -t nat -N DMZNAT
  /usr/sbin/iptables -t nat -N VPNWEB
  /usr/sbin/iptables -t nat -N EXTNAT
  /usr/sbin/iptables -t nat -N SYSNAT
  /usr/sbin/iptables -t nat -N NATOUT
  /usr/sbin/iptables -t nat -N MANGLE
  /usr/sbin/iptables -t nat -N DEFPROXY
  /usr/sbin/iptables -t nat -N MANGLEPROXY
  /usr/sbin/iptables -t nat -N LOCALPROXY
  /usr/sbin/iptables -t nat -N EXTPROXY

  /usr/sbin/iptables -t mangle -N SIPLB -m mark --mark 0
  /usr/sbin/iptables -t mangle -N DMZ0
  /usr/sbin/iptables -t mangle -N DMZ1
  /usr/sbin/iptables -t mangle -N DMZ2
  /usr/sbin/iptables -t mangle -N DMZ3
  /usr/sbin/iptables -t mangle -N DMZ4
  /usr/sbin/iptables -t mangle -N DMZ5
  /usr/sbin/iptables -t mangle -N DMZ6
  /usr/sbin/iptables -t mangle -N DMZ7
  /usr/sbin/iptables -t mangle -N DMZ8
  /usr/sbin/iptables -t mangle -N DMZ9

  /usr/sbin/iptables -t mangle -N SYSTOS
  /usr/sbin/iptables -t mangle -N NOSYSTOS
  /usr/sbin/iptables -t mangle -N LOCALTOS
  /usr/sbin/iptables -t mangle -N LOADBIN
  /usr/sbin/iptables -t mangle -N LOADBOUT
  /usr/sbin/iptables -t mangle -N SYSINGRESS
  /usr/sbin/iptables -t mangle -N LOCALIN
  /usr/sbin/iptables -t mangle -N LOCALOUT
  /usr/sbin/iptables -t mangle -N SYSEGRESS
  /usr/sbin/iptables -t mangle -N PPPOECLAS
  /usr/sbin/iptables -t mangle -N IMQTBL
  /usr/sbin/iptables -t mangle -N CMARK
  /usr/sbin/iptables -t mangle -N VPN
  /usr/sbin/iptables -t mangle -N MANGLEP1
  /usr/sbin/iptables -t mangle -N MANGLEP2
  /usr/sbin/iptables -t mangle -N MANGLEP3
  /usr/sbin/iptables -t mangle -N MANGLEP4
  /usr/sbin/iptables -t mangle -N MANGLEP5
  /usr/sbin/iptables -t mangle -N MANGLEO1
  /usr/sbin/iptables -t mangle -N MANGLEO2
  /usr/sbin/iptables -t mangle -N MANGLEO3
  /usr/sbin/iptables -t mangle -N MANGLEO4
  /usr/sbin/iptables -t mangle -N MANGLEO5
#  /usr/sbin/iptables -t mangle -N MANGLEF1
#  /usr/sbin/iptables -t mangle -N MANGLEF2
#  /usr/sbin/iptables -t mangle -N MANGLEF3
#  /usr/sbin/iptables -t mangle -N MANGLEF4
#  /usr/sbin/iptables -t mangle -N MANGLEF5

fi;

#Flush Tables And Chains
/usr/sbin/iptables -P INPUT DROP
/usr/sbin/iptables -P OUTPUT DROP
/usr/sbin/iptables -P FORWARD DROP

/usr/sbin/iptables -F INPUT
/usr/sbin/iptables -F OUTPUT
/usr/sbin/iptables -F FORWARD

/usr/sbin/iptables -F DENY
/usr/sbin/iptables -F ICMP
/usr/sbin/iptables -F ACCT
/usr/sbin/iptables -F LOOPIN
/usr/sbin/iptables -F LOOPOUT
#/usr/sbin/iptables -F PROXYIN
#/usr/sbin/iptables -F PROXYOUT
/usr/sbin/iptables -F SMBIN
/usr/sbin/iptables -F SMBOUT
/usr/sbin/iptables -F NFSIN
/usr/sbin/iptables -F NFSOUT
/usr/sbin/iptables -F SMBNFSIN
/usr/sbin/iptables -F SMBNFSOUT
/usr/sbin/iptables -F SYSIN
/usr/sbin/iptables -F DNSIN
/usr/sbin/iptables -F DHCPIN
/usr/sbin/iptables -F DHCPOUT
/usr/sbin/iptables -F MANGLEIN
/usr/sbin/iptables -F MANGLEOUT
/usr/sbin/iptables -F VOIPIN
/usr/sbin/iptables -F VOIPOUT
/usr/sbin/iptables -F MCASTIN
/usr/sbin/iptables -F SYSOUT
/usr/sbin/iptables -F SIPOUT
/usr/sbin/iptables -F SIPIN
/usr/sbin/iptables -F DNSOUT
/usr/sbin/iptables -F MCASTOUT
/usr/sbin/iptables -F VPNCIN
/usr/sbin/iptables -F VPNCOUT
/usr/sbin/iptables -F OVPNIN
/usr/sbin/iptables -F OVPNOUT
/usr/sbin/iptables -F VPNFWD
/usr/sbin/iptables -F OVPNFWD
/usr/sbin/iptables -F DNSFWD
/usr/sbin/iptables -F PPPIN
/usr/sbin/iptables -F PPPOUT
/usr/sbin/iptables -F PPPFWD
/usr/sbin/iptables -F TARPIT
/usr/sbin/iptables -F DEFIN
/usr/sbin/iptables -F DEFOUT
/usr/sbin/iptables -F IP6RD
/usr/sbin/iptables -F IP6RDDSL

/usr/sbin/iptables -t nat -F PREROUTING
/usr/sbin/iptables -t nat -F POSTROUTING
/usr/sbin/iptables -t nat -F OUTPUT
/usr/sbin/iptables -t nat -F PROXYBYPASS
/usr/sbin/iptables -t nat -F WEBACCESS
/usr/sbin/iptables -F WEBACCESSI
/usr/sbin/iptables -F WEBACCESSO
/usr/sbin/iptables -F HOTSPOTI
/usr/sbin/iptables -F HOTSPOTO
/usr/sbin/iptables -F DYNAMICIPI
/usr/sbin/iptables -F DYNAMICIPO
/usr/sbin/iptables -t nat -F TXPROXY
/usr/sbin/iptables -t nat -F NONAT
/usr/sbin/iptables -t nat -F VPNNAT
/usr/sbin/iptables -t nat -F DMZNAT
/usr/sbin/iptables -t nat -F VPNWEB
/usr/sbin/iptables -t nat -F SIPMAP
/usr/sbin/iptables -t nat -F SIPNAT
/usr/sbin/iptables -t nat -F SIPLB
/usr/sbin/iptables -t mangle -F SIPLB
/usr/sbin/iptables -t mangle -F CMARK
/usr/sbin/iptables -t mangle -F VPN
/usr/sbin/iptables -t mangle -F DMZ0
/usr/sbin/iptables -t mangle -F DMZ1
/usr/sbin/iptables -t mangle -F DMZ2
/usr/sbin/iptables -t mangle -F DMZ3
/usr/sbin/iptables -t mangle -F DMZ4
/usr/sbin/iptables -t mangle -F DMZ5
/usr/sbin/iptables -t mangle -F DMZ6
/usr/sbin/iptables -t mangle -F DMZ7
/usr/sbin/iptables -t mangle -F DMZ8
/usr/sbin/iptables -t mangle -F DMZ9

#
#Firewall Configuration
#


#Configure A Deny Policy To Log And Send Appropriate Reply

/usr/sbin/iptables -A DENY -j ACCEPT -p tcp -m state --state RELATED  --tcp-flags SYN,ACK,RST ACK,RST
/usr/sbin/iptables -A DENY -j ACCEPT -p tcp --tcp-flags SYN,PSH PSH -m length --length 40
/usr/sbin/iptables -A DENY -j ACCEPT -p tcp --tcp-flags SYN,RST RST -m length --length 40
/usr/sbin/iptables -A DENY -j ACCEPT -p tcp --tcp-flags SYN,FIN FIN -m length --length 40
/usr/sbin/iptables -A DENY -j DROP -d 255.255.255.255/32
</xsl:text>

  <xsl:for-each select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32')]"> 
    <xsl:text>/usr/sbin/iptables -A DENY -j DROP -i </xsl:text>
    <xsl:call-template name="getbaseif"/>
    <xsl:value-of select="concat(' -s ',@nwaddr,'/',@subnet,' -d ',@bcaddr,$nl)"/>

    <xsl:text>/usr/sbin/iptables -A DENY -j DROP -i </xsl:text>
    <xsl:call-template name="getbaseif"/>
    <xsl:value-of select="concat(' -d ',@bcaddr,' -p udp --sport 137 --dport 137',$nl)"/>

    <xsl:text>/usr/sbin/iptables -A DENY -j DROP -i </xsl:text>
    <xsl:call-template name="getbaseif"/>
    <xsl:value-of select="concat(' -s ',@nwaddr,'/',@subnet,' -d 255.255.255.255',$nl)"/>

    <xsl:text>/usr/sbin/iptables -A DENY -j DROP -i </xsl:text>
    <xsl:call-template name="getbaseif"/>
    <xsl:value-of select="concat(' -s ',@nwaddr,'/',@subnet,' -d 224.0.0.0/3',$nl)"/>
  </xsl:for-each>

  <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:text>/usr/sbin/iptables -A DENY -j DROP -i </xsl:text>
    <xsl:value-of select="concat('gtun',position()-1)"/>
    <xsl:value-of select="concat(' -s ',@nwaddr,'/30',' -d ',@bcaddr,$nl)"/>

    <xsl:text>/usr/sbin/iptables -A DENY -j DROP -i </xsl:text>
    <xsl:value-of select="concat('gtun',position()-1)"/>
    <xsl:value-of select="concat(' -d ',@bcaddr,' -p udp --sport 137 --dport 137',$nl)"/>

    <xsl:text>/usr/sbin/iptables -A DENY -j DROP -i </xsl:text>
    <xsl:value-of select="concat('gtun',position()-1)"/>
    <xsl:value-of select="concat(' -s ',@nwaddr,'/30',' -d 255.255.255.255',$nl)"/>

    <xsl:text>/usr/sbin/iptables -A DENY -j DROP -i </xsl:text>
    <xsl:value-of select="concat('gtun',position()-1)"/>
    <xsl:value-of select="concat(' -s ',@nwaddr,'/30',' -d 224.0.0.0/3',$nl)"/>
  </xsl:for-each>

  <xsl:text>/usr/sbin/iptables -A DENY -j ULOG 
#/usr/sbin/iptables -A DENY -j LOG -p ! tcp --log-level info --log-ip-options --log-prefix "rejected packet "
#/usr/sbin/iptables -A DENY -j LOG -p tcp --log-level info --log-ip-options --log-tcp-options --log-tcp-sequence --log-prefix "rejected packet "
/usr/sbin/iptables -A DENY -j REJECT -p tcp --reject-with tcp-reset
/usr/sbin/iptables -A DENY -j REJECT

#Activate ACCT Chain
/usr/sbin/iptables -A INPUT -j ACCT 
/usr/sbin/iptables -A OUTPUT -j ACCT
/usr/sbin/iptables -A FORWARD -j ACCT

#Activate ICMP Chain
/usr/sbin/iptables -A INPUT -j ICMP -p icmp
/usr/sbin/iptables -A OUTPUT -j ICMP -p icmp

#Allow IGMP
/usr/sbin/iptables -A INPUT -j ACCEPT -p igmp
/usr/sbin/iptables -A OUTPUT -j ACCEPT -p igmp

#Activate IP6RD Chain
/usr/sbin/iptables -A INPUT -j IP6RD -p 41
/usr/sbin/iptables -A OUTPUT -j IP6RD -p 41
/usr/sbin/iptables -A INPUT -j IP6RDDSL -p 41
/usr/sbin/iptables -A OUTPUT -j IP6RDDSL -p 41

/usr/sbin/iptables -A VOIPIN -j ACCEPT -p udp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --dport 5000
/usr/sbin/iptables -A VOIPIN -j ACCEPT -p udp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --dport 4555
/usr/sbin/iptables -A VOIPIN -j ACCEPT -p udp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --dport 5060
/usr/sbin/iptables -A VOIPIN -j ACCEPT -p tcp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --dport 5060:5061
/usr/sbin/iptables -A VOIPIN -j ACCEPT -p udp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --dport 4569
/usr/sbin/iptables -A VOIPIN -j ACCEPT -p udp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --dport 1719:1722
/usr/sbin/iptables -A VOIPIN -j ACCEPT -p udp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --dport 10000:20000

/usr/sbin/iptables -A VOIPOUT -j ACCEPT -p udp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --sport 5000
/usr/sbin/iptables -A VOIPOUT -j ACCEPT -p udp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --sport 4555
/usr/sbin/iptables -A VOIPOUT -j ACCEPT -p udp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --sport 5060
/usr/sbin/iptables -A VOIPOUT -j ACCEPT -p tcp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --sport 5060:5061
/usr/sbin/iptables -A VOIPOUT -j ACCEPT -p udp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --sport 4569
/usr/sbin/iptables -A VOIPOUT -j ACCEPT -p udp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --sport 1719:1722
/usr/sbin/iptables -A VOIPOUT -j ACCEPT -p udp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --sport 10000:20000

#Allow DHCP/mDNS/DNS/NTP Packets
/usr/sbin/iptables -A INPUT -j ACCEPT -p udp -s 0.0.0.0/0 --sport 68 -d 255.255.255.255 --dport 67
/usr/sbin/iptables -A INPUT -j ACCEPT -p udp -s 0.0.0.0/0 --sport 123 -d 255.255.255.255 --dport 123
/usr/sbin/iptables -A INPUT -j ACCEPT -p udp -s 0.0.0.0/0 --sport 1024:65535 -d 255.255.255.255 --dport 123
/usr/sbin/iptables -A INPUT -j ACCEPT -p udp --sport 1024:65535 --dport 5353
/usr/sbin/iptables -A INPUT -j ACCEPT -p udp --dport 1024:65535 --sport 5353
/usr/sbin/iptables -A OUTPUT -j ACCEPT -p udp --dport 1024:65535 --sport 5353
/usr/sbin/iptables -A OUTPUT -j ACCEPT -p udp --sport 1024:65535 --dport 5353
/usr/sbin/iptables -A OUTPUT -j ACCEPT -p udp --dport 53 --sport 53
/usr/sbin/iptables -A OUTPUT -j ACCEPT -p udp --sport 1024:65535 --dport 53
/usr/sbin/iptables -A OUTPUT -j ACCEPT -p tcp --dport 53 --sport 53

#Far South comma-ls
#/usr/sbin/iptables -A INPUT -j ACCEPT -p udp -s 0.0.0.0/0 --sport 17409 -d 255.255.255.255 --dport 1024:65535

/usr/sbin/iptables -A DHCPIN -j ACCEPT -p udp --sport 68 --dport 67
/usr/sbin/iptables -A DHCPIN -j ACCEPT -p udp --sport 69 --dport 69
/usr/sbin/iptables -A DHCPIN -j ACCEPT -p udp --sport 1024:65535 --dport 69

/usr/sbin/iptables -A DHCPOUT -j ACCEPT -p udp --dport 67:68 --sport 67:68
/usr/sbin/iptables -A DHCPOUT -j ACCEPT -p udp --dport 69 --sport 69
/usr/sbin/iptables -A DHCPOUT -j ACCEPT -p udp --dport 1024:65535 --sport 69
#/usr/sbin/iptables -A DHCPOUT -j ACCEPT -p udp --sport 1024:65535 --dport 1024:65535 -m state --state ESTABLISHED,NEW
/usr/sbin/iptables -A DHCPOUT -j ACCEPT -p udp --sport 67 --dport 67 -m state --state NEW
#/usr/sbin/iptables -A DHCPOUT -j ACCEPT -p udp --sport 1024:65535 --dport 17409

#Allow ESP/GRE Traffic
/usr/sbin/iptables -A INPUT -j ACCEPT -p esp
/usr/sbin/iptables -A OUTPUT -j ACCEPT -p esp
/usr/sbin/iptables -A INPUT -j ACCEPT -p ah
/usr/sbin/iptables -A OUTPUT -j ACCEPT -p ah
/usr/sbin/iptables -A OUTPUT -j DHCPOUT -p udp --sport 67:69
</xsl:text>
  <xsl:call-template name="acctdhcp"/>
  <xsl:call-template name="acctgre"/>

  <xsl:for-each select="/config/IP/Routes/Route">
    <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j DHCPIN -i ',$intiface,' -s ',@network,'/',@subnet,' -d ',$intip,'/32',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j DHCPOUT -o ',$intiface,' -d ',@network,'/',@subnet,' -s ',$intip,'/32',$nl)"/>
  </xsl:for-each>

  <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:value-of select="concat('#Allow Point To Point GRE Data Flow For Interface ',@interface,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j ACCEPT -p gre -i ',@interface,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j ACCEPT -p gre -o ',@interface,$nl,$nl)"/>
  </xsl:for-each>

  <xsl:text>#Activate Loopback Chain's
/usr/sbin/iptables -A INPUT -j LOOPIN -i lo
/usr/sbin/iptables -A LOOPIN -j ACCEPT -i lo -s 127.0.0.1 -d 127.0.0.1
/usr/sbin/iptables -A LOOPIN -j ACCEPT -i lo -s 127.0.0.2 -d 127.0.0.2
/usr/sbin/iptables -A LOOPIN -j ACCEPT -i lo -s 127.0.0.1 -d 127.255.255.255
/usr/sbin/iptables -A LOOPIN -j ACCEPT -i lo -s 127.0.0.2 -d 127.255.255.255
/usr/sbin/iptables -A LOOPIN -j ACCEPT -i lo -d 127.0.0.1 -s 127.255.255.255
/usr/sbin/iptables -A LOOPIN -j ACCEPT -i lo -d 127.0.0.2 -s 127.255.255.255
</xsl:text>
  <xsl:if test="($zcipaddr != '')">
    <xsl:value-of select="concat('/usr/sbin/iptables -A LOOPIN -j ACCEPT -i lo -d ',$zcipaddr,' -s ',$zcipaddr,$nl)"/>
  </xsl:if>
<xsl:text>/usr/sbin/iptables -A OUTPUT -j LOOPOUT -o lo
/usr/sbin/iptables -A LOOPOUT -j ACCEPT -o lo -s 127.0.0.1 -d 127.0.0.1
/usr/sbin/iptables -A LOOPOUT -j ACCEPT -o lo -s 127.0.0.2 -d 127.0.0.2
/usr/sbin/iptables -A LOOPOUT -j ACCEPT -o lo -s 127.255.255.255 -d 127.0.0.1
/usr/sbin/iptables -A LOOPOUT -j ACCEPT -o lo -s 127.255.255.255 -d 127.0.0.2
/usr/sbin/iptables -A LOOPOUT -j ACCEPT -o lo -d 127.255.255.255 -s 127.0.0.1
/usr/sbin/iptables -A LOOPOUT -j ACCEPT -o lo -d 127.255.255.255 -s 127.0.0.2
</xsl:text>
  <xsl:if test="($zcipaddr != '')">
    <xsl:value-of select="concat('/usr/sbin/iptables -A LOOPOUT -j ACCEPT -o lo -d ',$zcipaddr,' -s ',$zcipaddr,$nl)"/>
  </xsl:if>
<xsl:text>
#Activate NAT Chains
/usr/sbin/iptables -t nat -A PREROUTING -j NATMAPI
/usr/sbin/iptables -t nat -A PREROUTING -j WEBACCESS -p tcp --dport 80
/usr/sbin/iptables -t nat -A WEBACCESS -j VPNWEB -p tcp --dport 80
/usr/sbin/iptables -t nat -A PREROUTING -j PROXYBYPASS -p tcp --dport 80
/usr/sbin/iptables -t nat -A PREROUTING -j PROXYBYPASS -p tcp --dport 21
/usr/sbin/iptables -t nat -A PREROUTING -j TXPROXY -p tcp --dport 80 --sport 1024:65535 -m state --state NEW,ESTABLISHED
/usr/sbin/iptables -t nat -A PREROUTING -j SIPMAP
/usr/sbin/iptables -t nat -A POSTROUTING -j SIPNAT -p udp -s </xsl:text><xsl:value-of select="$intip"/><xsl:text> --sport 5000 --dport 1024:65535
/usr/sbin/iptables -t nat -A POSTROUTING -j SIPNAT -p udp -s </xsl:text><xsl:value-of select="$intip"/><xsl:text> --sport 4569 --dport 1024:65535
/usr/sbin/iptables -t nat -A POSTROUTING -j SIPNAT -p udp -s </xsl:text><xsl:value-of select="$intip"/><xsl:text> --sport 5060 --dport 1024:65535
/usr/sbin/iptables -t nat -A POSTROUTING -j SIPNAT -p tcp -s </xsl:text><xsl:value-of select="$intip"/><xsl:text> --sport 5060:5061 --dport 1024:65535
/usr/sbin/iptables -t nat -A POSTROUTING -j SIPNAT -p udp -s </xsl:text><xsl:value-of select="$intip"/><xsl:text> --sport 1718:1720 --dport 1024:65535
/usr/sbin/iptables -t nat -A POSTROUTING -j MANGLE -o ppp+
/usr/sbin/iptables -t nat -A POSTROUTING -j 3GNAT
/usr/sbin/iptables -t nat -A POSTROUTING -j NATMAPO
/usr/sbin/iptables -t nat -A POSTROUTING -j NONAT
/usr/sbin/iptables -t nat -A POSTROUTING -j VPNNAT
/usr/sbin/iptables -t nat -A POSTROUTING -j DMZNAT
/usr/sbin/iptables -t nat -A POSTROUTING -j SYSNAT
/usr/sbin/iptables -t nat -A POSTROUTING -j EXTNAT
/usr/sbin/iptables -t nat -A OUTPUT -j SIPLB

#Activate Mangle Prerouting Chains
/usr/sbin/iptables -t mangle -F PREROUTING
/usr/sbin/iptables -t mangle -A PREROUTING -j CONNMARK --restore-mark -m state ! --state NEW
/usr/sbin/iptables -t mangle -A PREROUTING -j CMARK -m state --state NEW,RELATED
/usr/sbin/iptables -t mangle -A PREROUTING -j RETURN -m mark ! --mark 0
/usr/sbin/iptables -t mangle -A PREROUTING -j LOADBIN
/usr/sbin/iptables -t mangle -A PREROUTING -j IMQTBL
/usr/sbin/iptables -t mangle -A PREROUTING -j VPN
/usr/sbin/iptables -t mangle -A PREROUTING -j SYSTOS
/usr/sbin/iptables -t mangle -A PREROUTING -j LOCALTOS
/usr/sbin/iptables -t mangle -A PREROUTING -j MANGLEP1 -m mark --mark 0
/usr/sbin/iptables -t mangle -A PREROUTING -j MANGLEP2 -m mark --mark 0
/usr/sbin/iptables -t mangle -A PREROUTING -j MANGLEP3 -m mark --mark 0
/usr/sbin/iptables -t mangle -A PREROUTING -j MANGLEP4 -m mark --mark 0
/usr/sbin/iptables -t mangle -A PREROUTING -j MANGLEP5 -m mark --mark 0

#/usr/sbin/iptables -t mangle -A FORWARD -j VPN
#/usr/sbin/iptables -t mangle -A FORWARD -j MANGLEF1 
#/usr/sbin/iptables -t mangle -A FORWARD -j RETURN -m mark ! --mark 0
#/usr/sbin/iptables -t mangle -A FORWARD -j MANGLEF2
#/usr/sbin/iptables -t mangle -A FORWARD -j RETURN -m mark ! --mark 0
#/usr/sbin/iptables -t mangle -A FORWARD -j MANGLEF3
#/usr/sbin/iptables -t mangle -A FORWARD -j RETURN -m mark ! --mark 0
#/usr/sbin/iptables -t mangle -A FORWARD -j MANGLEF4
#/usr/sbin/iptables -t mangle -A FORWARD -j RETURN -m mark ! --mark 0
#/usr/sbin/iptables -t mangle -A FORWARD -j MANGLEF5
#/usr/sbin/iptables -t mangle -A FORWARD -j RETURN -m mark ! --mark 0

#/usr/sbin/iptables -t mangle -A PREROUTING -j VPN
#/usr/sbin/iptables -t mangle -A PREROUTING -j LOCALTOS
#/usr/sbin/iptables -t mangle -A PREROUTING -j NOSYSTOS
#/usr/sbin/iptables -t mangle -A PREROUTING -j SYSTOS
/usr/sbin/iptables -t mangle -F OUTPUT
/usr/sbin/iptables -t mangle -I OUTPUT -j CONNMARK --restore-mark -m mark --mark 0 -m state ! --state NEW
/usr/sbin/iptables -t mangle -A OUTPUT -j SIPLB -m mark --mark 0
/usr/sbin/iptables -t mangle -A OUTPUT -j LOADBOUT
/usr/sbin/iptables -t mangle -A OUTPUT -j VPN
</xsl:text>

  <xsl:for-each select="/config/IP/ESP/Tunnels/ESPTunnel">
    <xsl:value-of select="concat('/usr/sbin/iptables -t mangle -A OUTPUT -j DMZ',position()-1,' ! -o ',$extiface,' ! -s ',@nwaddr,' -d ',.,' -m state --state NEW',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -t mangle -A OUTPUT -j RETURN -m mark --mark ',20+position()-1,$nl)"/>
  </xsl:for-each>

  <xsl:text>/usr/sbin/iptables -t mangle -A OUTPUT -j NOSYSTOS
/usr/sbin/iptables -t mangle -A OUTPUT -j SYSTOS
/usr/sbin/iptables -t mangle -A OUTPUT -j MANGLEO1
/usr/sbin/iptables -t mangle -A OUTPUT -j RETURN -m mark ! --mark 0
/usr/sbin/iptables -t mangle -A OUTPUT -j MANGLEO2
/usr/sbin/iptables -t mangle -A OUTPUT -j RETURN -m mark ! --mark 0
/usr/sbin/iptables -t mangle -A OUTPUT -j MANGLEO3
/usr/sbin/iptables -t mangle -A OUTPUT -j RETURN -m mark ! --mark 0
/usr/sbin/iptables -t mangle -A OUTPUT -j MANGLEO4
/usr/sbin/iptables -t mangle -A OUTPUT -j RETURN -m mark ! --mark 0
/usr/sbin/iptables -t mangle -A OUTPUT -j MANGLEO5
/usr/sbin/iptables -t mangle -A OUTPUT -j RETURN -m mark ! --mark 0


#Activate IMQ Chain
/usr/sbin/iptables -t mangle -F SYSINGRESS
/usr/sbin/iptables -t mangle -F IMQTBL
</xsl:text>

  <xsl:choose>
    <xsl:when test="($extiface = 'Dialup') or ($extcon = 'ADSL')">
      <xsl:text>/usr/sbin/iptables -t mangle -A IMQTBL -j IMQ -i ppp0 --todev 0</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('/usr/sbin/iptables -t mangle -A IMQTBL -j IMQ -i ',$extiface,' --todev 0')"/>
    </xsl:otherwise>
  </xsl:choose>

<xsl:text>
/usr/sbin/iptables -t mangle -A IMQTBL -j IMQ -i ppp1 --todev 1
/usr/sbin/iptables -t mangle -A IMQTBL -j IMQ -i ppp2 --todev 2
/usr/sbin/iptables -t mangle -A IMQTBL -j IMQ -i ppp3 --todev 3
/usr/sbin/iptables -t mangle -A IMQTBL -j IMQ -i ppp4 --todev 4
/usr/sbin/iptables -t mangle -A IMQTBL -j IMQ -i ppp5 --todev 5
</xsl:text>

  <xsl:for-each select="/config/IP/Interfaces/Interface[(@bwout != '') and (@bwout &gt; 0) and not(contains(.,':')) and
       (@ipaddr != '0.0.0.0') and (@subnet != '32') and ((. != $extiface) or ($extcon = 'ADSL'))]">
    <xsl:value-of select="concat('/usr/sbin/iptables -t mangle -A IMQTBL -j IMQ -i ',.,' --todev ',position()-1+6,$nl)"/>
  </xsl:for-each>

  <xsl:text>&#xa;#Allow Higer Speed To Local Network From Wireless Range&#xa;</xsl:text>
  <xsl:for-each select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32')]">
    <xsl:value-of select="concat('/usr/sbin/iptables -t mangle -A PPPOECLAS -j CLASSIFY -s ',@nwaddr,'/',@subnet,' --set-class 1:5',$nl)"/>
  </xsl:for-each>
  <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:value-of select="concat('/usr/sbin/iptables -t mangle -A PPPOECLAS -j CLASSIFY -s ',@nwaddr,'/30 --set-class 1:5',$nl)"/>
  </xsl:for-each>

<xsl:text>
#Activate Classify Chains
/usr/sbin/iptables -t mangle -F POSTROUTING
/usr/sbin/iptables -t mangle -A POSTROUTING -j MARK --set-mark 0x102
/usr/sbin/iptables -t mangle -A POSTROUTING -j SYSEGRESS
/usr/sbin/iptables -t mangle -A POSTROUTING -j LOCALOUT
/usr/sbin/iptables -t mangle -A POSTROUTING -j SYSINGRESS
/usr/sbin/iptables -t mangle -A POSTROUTING -j LOCALIN
/usr/sbin/iptables -t mangle -A POSTROUTING -j LOCALOUT -m mark --mark 0x102
/usr/sbin/iptables -t mangle -A POSTROUTING -j CLASSIFY -m mark --mark 0x101 --set-class 1:10
/usr/sbin/iptables -t mangle -A POSTROUTING -j CLASSIFY -m mark --mark 0x102 --set-class 1:20
/usr/sbin/iptables -t mangle -A POSTROUTING -j CLASSIFY -m mark --mark 0x103 --set-class 1:30
</xsl:text>

  <xsl:if test="/config/Radius/Config/Option[@option = 'PPPoE'] != ''">
    <xsl:value-of select="concat('/usr/sbin/iptables -t mangle -A POSTROUTING -j PPPOECLAS -o ppp+ -d ',/config/Radius/Config/Option[@option = 'PPPoE'],$nl)"/>
  </xsl:if>

  <xsl:text>
#Activate Local Proxy Chain
/usr/sbin/iptables -t nat -A PREROUTING -j LOCALPROXY -m state --state ESTABLISHED,NEW
/usr/sbin/iptables -t nat -A PREROUTING -j MANGLEPROXY

#Dont Allow ICMP Fragments
/usr/sbin/iptables -A ICMP -j DENY -p icmp -f

#Allow ICMP redirect
/usr/sbin/iptables -A ICMP -j ACCEPT -p icmp --icmp-type redirect

#Allow ICMP destination-unreachable
/usr/sbin/iptables -A ICMP -j ACCEPT -p icmp --icmp-type destination-unreachable
/usr/sbin/iptables -A ICMP -j ACCEPT -p icmp --icmp-type port-unreachable

#Allow ICMP Ping
/usr/sbin/iptables -A ICMP -j ACCEPT -p icmp --icmp-type echo-reply
/usr/sbin/iptables -A ICMP -j ACCEPT -p icmp --icmp-type echo-request
/usr/sbin/iptables -A ICMP -j ACCEPT -p icmp --icmp-type 11
/usr/sbin/iptables -A ICMP -j ACCEPT -p icmp --icmp-type 4


#Deny Other ICMP Packets
/usr/sbin/iptables -A ICMP -j DENY -p icmp

#Configure Connection Marking
</xsl:text>

  <xsl:for-each select="/config/IP/ESP/Tunnels/ESPTunnel">
    <xsl:value-of select="concat('/usr/sbin/iptables -t mangle -A CMARK -j MARK -i ',$extiface,' -s ',.,' -d ',@nwaddr,' --set-mark ',position()-1+20,$nl)"/>
  </xsl:for-each>

  <xsl:text>/usr/sbin/iptables -t mangle -A CMARK -j MARK -i ppp1 --set-mark 1
/usr/sbin/iptables -t mangle -A CMARK -j MARK -i ppp2 --set-mark 2
/usr/sbin/iptables -t mangle -A CMARK -j MARK -i ppp3 --set-mark 3
/usr/sbin/iptables -t mangle -A CMARK -j MARK -i ppp4 --set-mark 4
/usr/sbin/iptables -t mangle -A CMARK -j MARK -i ppp5 --set-mark 5
/usr/sbin/iptables -t mangle -A CMARK -j MARK -i ppp6 --set-mark 6
/usr/sbin/iptables -t mangle -A CMARK -j MARK -i ppp7 --set-mark 7
/usr/sbin/iptables -t mangle -A CMARK -j MARK -i ppp8 --set-mark 8
/usr/sbin/iptables -t mangle -A CMARK -j MARK -i ppp9 --set-mark 9
/usr/sbin/iptables -t mangle -A CMARK -j MARK -i ppp10 --set-mark 10

/usr/sbin/iptables -t mangle -A CMARK -j CONNMARK --save-mark -m mark ! --mark 0


/usr/sbin/iptables -A VPNCIN -j RETURN
/usr/sbin/iptables -A VPNCOUT -j RETURN

#/usr/sbin/iptables -A INPUT -j VPNLIN
#/usr/sbin/iptables -A OUTPUT -j VPNLOUT
/usr/sbin/iptables -A INPUT -j VPNCIN
/usr/sbin/iptables -A OUTPUT -j VPNCOUT

#Hotspot Access Drop Packets That Dont Match
/usr/sbin/iptables -A HOTSPOTI -j ACCEPT -p udp -d </xsl:text><xsl:value-of select="$intip"/><xsl:text>/32 --dport 53
/usr/sbin/iptables -A HOTSPOTI -j ACCEPT -p tcp -d </xsl:text><xsl:value-of select="$intip"/><xsl:text>/32 --dport 53
/usr/sbin/iptables -A HOTSPOTI -j ACCEPT -p tcp -d </xsl:text><xsl:value-of select="$intip"/><xsl:text>/32 --dport 80
/usr/sbin/iptables -A HOTSPOTI -j ACCEPT -p tcp -d </xsl:text><xsl:value-of select="$intip"/><xsl:text>/32 --dport 443
/usr/sbin/iptables -A HOTSPOTI -j ACCEPT -p tcp -d </xsl:text><xsl:value-of select="$intip"/><xsl:text>/32 --dport 666
/usr/sbin/iptables -A HOTSPOTI -j ACCEPT -p tcp -d </xsl:text><xsl:value-of select="$intip"/><xsl:text>/32 --dport 443
/usr/sbin/iptables -A HOTSPOTI -j ACCEPT -p tcp -d </xsl:text><xsl:value-of select="$intip"/><xsl:text>/32 --dport 22
/usr/sbin/iptables -A HOTSPOTI -j ACCEPT -p tcp -d </xsl:text><xsl:value-of select="$intip"/><xsl:text>/32 --dport 25
/usr/sbin/iptables -A HOTSPOTI -j ACCEPT -p tcp -d </xsl:text><xsl:value-of select="$intip"/><xsl:text>/32 --dport 110
/usr/sbin/iptables -A HOTSPOTI -j ACCEPT -p tcp -d </xsl:text><xsl:value-of select="$intip"/><xsl:text>/32 --dport 143
/usr/sbin/iptables -A HOTSPOTI -j ACCEPT -p tcp -d </xsl:text><xsl:value-of select="$intip"/><xsl:text>/32 --dport 995
/usr/sbin/iptables -A HOTSPOTI -j ACCEPT -p tcp -d </xsl:text><xsl:value-of select="$intip"/><xsl:text>/32 --dport 993

#Allow Dynamic IP Traffic
/usr/sbin/iptables -A INPUT -j DYNAMICIPI -i </xsl:text><xsl:value-of select="$intiface"/><xsl:text>
/usr/sbin/iptables -A OUTPUT -j DYNAMICIPO -o </xsl:text><xsl:value-of select="$intiface"/><xsl:text>
</xsl:text>

  <xsl:for-each select="/config/IP/Interfaces/Interface">
    <xsl:variable name="iface" value="."/>
    <xsl:if test="/config/IP/WiFi[. = current()]/@type = 'Hotspot'">
      <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j HOTSPOTI -i tun+ -s ',@nwaddr,'/',@subnet,' -m state --state NEW,ESTABLISHED',$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j ACCEPT -o tun+ -d ',@nwaddr,'/',@subnet,' -m state --state ESTABLISHED',$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A HOTSPOTI -j ACCEPT -p tcp -d ',@ipaddr,'/32 --dport 3990',$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A HOTSPOTI -j ACCEPT -p udp -d ',@ipaddr,'/32 --dport 67:68',$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A HOTSPOTI -j ACCEPT -p tcp -d ',@ipaddr,'/32 --dport 3129',$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A LOOPOUT -j ACCEPT -s ',@ipaddr,'/32 -d ',@ipaddr,'/32 -p udp --dport ',$radacport,' -m state --state NEW,ESTABLISHED',$nl)"/> 
      <xsl:value-of select="concat('/usr/sbin/iptables -A LOOPOUT -j ACCEPT -s ',@ipaddr,'/32 -d ',$intip,'/32 -p udp --dport ',$radacport,' -m state --state NEW,ESTABLISHED',$nl)"/> 
    </xsl:if>
  </xsl:for-each>
  <xsl:if test="count(/config/IP/WiFi[@type = 'Hotspot']) &gt; 0">
    <xsl:text>/usr/sbin/iptables -A INPUT -j DENY -i tun+&#xa;</xsl:text>
    <xsl:text>/usr/sbin/iptables -A OUTPUT -j DENY -o tun+&#xa;</xsl:text>
  </xsl:if>

  <xsl:text>#Open VPN Tables&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -I VPNCIN -j OVPNIN -i vpn0 -d ',$intip,'/32 -m state --state NEW,RELATED,ESTABLISHED',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -I VPNCOUT -j OVPNOUT -o vpn0 -s ',$intip,'/32 -m state --state NEW,RELATED,ESTABLISHED',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -I VPNFWD -j ACCEPT -i ',$intiface,' -p gre -m state --state RELATED -s ',$loclan,$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -I VPNFWD -j OVPNFWD -i vpn0 -o ',$intiface,' -d ',$loclan,$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -I VPNFWD -j OVPNFWD -o vpn0 -i ',$intiface,' -s ',$loclan,$nl)"/>
  <xsl:text>/usr/sbin/iptables -I OVPNFWD -j ACCEPT&#xa;</xsl:text>
  <xsl:if test="/config/IP/SysConf/Option[@option = 'OVPNNet']">
    <xsl:value-of select="concat('/usr/sbin/iptables -I VPNFWD -j OVPNFWD -i vpn0 -s ',/config/IP/SysConf/Option[@option = 'OVPNNet'],$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -I VPNFWD -j OVPNFWD -o vpn0 -d ',/config/IP/SysConf/Option[@option = 'OVPNNet'],$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -t nat -I VPNNAT -j MASQUERADE -i vpn0 -s ',/config/IP/SysConf/Option[@option = 'OVPNNet'],$nl,$nl)"/>
  </xsl:if>

  <xsl:if test="/config/IP/SysConf/Option[@option = 'VPNNet']">
    <xsl:value-of select="concat('/usr/sbin/iptables -I VPNCIN -j SYSIN -s ',/config/IP/SysConf/Option[@option = 'VPNNet'],' -d ',$loclan,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -I VPNCOUT -j SYSOUT -d ',/config/IP/SysConf/Option[@option = 'VPNNet'],' -s ',$loclan,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -I VPNFWD -j ACCEPT -i ',$intiface,' -d ',/config/IP/SysConf/Option[@option = 'VPNNet'],' -s ',$loclan,' -m state --state NEW,RELATED,ESTABLISHED',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -I VPNFWD -j ACCEPT -o ',$intiface,' -s ',/config/IP/SysConf/Option[@option = 'VPNNet'],' -d ',$loclan,' -m state --state NEW,RELATED,ESTABLISHED',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -t nat -A VPNNAT -j ACCEPT -m state --state NEW,ESTABLISHED,RELATED -s ',/config/IP/SysConf/Option[@option = 'VPNNet'],' -d ',$loclan,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -t nat -A VPNNAT -j ACCEPT -m state --state NEW,ESTABLISHED,RELATED -d ',/config/IP/SysConf/Option[@option = 'VPNNet'],' -s ',$loclan,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -I TXPROXY -t nat -j ACCEPT -s ',/config/IP/SysConf/Option[@option = 'VPNNet'],$nl)"/>
    <xsl:value-of select="concat($nl,'/sbin/ip route add ',/config/IP/SysConf/Option[@option = 'VPNNet'],' src ',$intip,' dev ',$intiface,' table Ipsec',$nl)"/>
    <xsl:for-each select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32')]">
      <xsl:text>/usr/sbin/iptables -I VPNFWD -j ACCEPT -i </xsl:text>
      <xsl:call-template name="getbaseif"/>
      <xsl:value-of select="concat(' -p gre -m state --state RELATED -s ',@nwaddr,'/',@subnet,$nl)"/>
      <xsl:text>/usr/sbin/iptables -A VPNFWD -j ACCEPT -o </xsl:text>
      <xsl:call-template name="getbaseif"/>
      <xsl:value-of select="concat(' -d ',@nwaddr,'/',@subnet,' -s ',/config/IP/SysConf/Option[@option = 'VPNNet'],$nl)"/>
      <xsl:text>/usr/sbin/iptables -A VPNFWD -j ACCEPT -i </xsl:text>
      <xsl:call-template name="getbaseif"/>
      <xsl:value-of select="concat(' -s ',@nwaddr,'/',@subnet,' -d ',/config/IP/SysConf/Option[@option = 'VPNNet'],$nl)"/>
    </xsl:for-each>
    <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
      <xsl:text>/usr/sbin/iptables -I VPNFWD -j ACCEPT -i </xsl:text>
      <xsl:call-template name="getbaseif">
        <xsl:with-param name="iface" select="concat('gtun',position()-1)"/>
      </xsl:call-template>
      <xsl:value-of select="concat(' -p gre -m state --state RELATED -s ',@nwaddr,'/30',$nl)"/>
      <xsl:text>/usr/sbin/iptables -A VPNFWD -j ACCEPT -o </xsl:text>
      <xsl:call-template name="getbaseif">
        <xsl:with-param name="iface" select="concat('gtun',position()-1)"/>
      </xsl:call-template>
      <xsl:value-of select="concat(' -d ',@nwaddr,'/30',' -s ',/config/IP/SysConf/Option[@option = 'VPNNet'],$nl)"/>
      <xsl:text>/usr/sbin/iptables -A VPNFWD -j ACCEPT -i </xsl:text>
      <xsl:call-template name="getbaseif">
        <xsl:with-param name="iface" select="concat('gtun',position()-1)"/>
      </xsl:call-template>
      <xsl:value-of select="concat(' -s ',@nwaddr,'/30',' -d ',/config/IP/SysConf/Option[@option = 'VPNNet'],$nl)"/>
    </xsl:for-each>
  </xsl:if>
  <xsl:for-each select="/config/Radius/RAS/Modem">
    <xsl:value-of select="concat('#PPP Link ',.,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -I VPNCIN -j SYSIN -i ppp+ -s ',@remote,' -d 0/0 -m state --state NEW,ESTABLISHED',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -I VPNCOUT -j SYSOUT -o ppp+ -d ',@remote,' -s 0/0 -m state --state ESTABLISHED',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A TXPROXY -t nat -j REDIRECT -p tcp --to-port 3129 -i ppp+ -s ',@remote,$nl,$nl)"/>
  </xsl:for-each>
 
  <xsl:if test="/config/Radius/Config/Option[@option = 'PPPoE'] != ''">
    <xsl:text>#PPPoE Connections&#xa;</xsl:text>
    <xsl:value-of select="concat('/usr/sbin/iptables -A TXPROXY -p tcp -t nat -j REDIRECT --to-port 3129 -i ppp+ -s ',/config/Radius/Config/Option[@option = 'PPPoE'],$nl,$nl)"/>
  </xsl:if>

  <xsl:if test="/config/IP/SysConf/Option[@option = 'L2TPNet']">
    <xsl:text>#L2TP Connections&#xa;</xsl:text>
    <xsl:value-of select="concat('/usr/sbin/iptables -A TXPROXY -p tcp -t nat -j REDIRECT --to-port 3129 -i ppp+ -s ',/config/IP/SysConf/Option[@option = 'L2TPNet'],$nl,$nl)"/>
  </xsl:if>

  <xsl:text>#Protect The Loopback Interface
/usr/sbin/iptables -A INPUT -j DENY -i lo ! -s 127.0.0.0/8
/usr/sbin/iptables -A INPUT -j DENY ! -i lo -s 127.0.0.0/8
/usr/sbin/iptables -A OUTPUT -j DENY -o lo ! -d 127.0.0.0/8
/usr/sbin/iptables -A OUTPUT -j DENY ! -o lo -d 127.0.0.0/8

#Protect All Interface's
</xsl:text>
  <xsl:if test="/config/IP/SysConf/Option[@option = 'Bridge']">
    <xsl:value-of select="concat('/usr/sbin/iptables -A FORWARD -j ACCEPT -i ',$intiface,' -o ',$intiface,$nl)"/>
  </xsl:if>

  <xsl:text>#add MSS CLAMP&#xa;</xsl:text>
  <xsl:text>/usr/sbin/iptables -A FORWARD -j TCPMSS -o ppp+ -p tcp --tcp-flags SYN,RST SYN --clamp-mss-to-pmtu&#xa;</xsl:text>

  <xsl:if test="($extcon != 'ADSL') and ($extiface != 'Dialup')">
    <xsl:value-of select="concat('/usr/sbin/iptables -A FORWARD -j TCPMSS -o ',$extiface,' -p tcp --tcp-flags SYN,RST SYN --clamp-mss-to-pmtu',$nl,$nl)"/>
  </xsl:if>

  <xsl:text>#NAT ICMP Ping And Errors (Type 3/11)
/usr/sbin/iptables -A FORWARD -j ICMP -p icmp

#Returning Traffic
/usr/sbin/iptables -A FORWARD -j ACCEPT -m state --state ESTABLISHED,RELATED
/usr/sbin/iptables -A FORWARD -j SYSFWD
/usr/sbin/iptables -A FORWARD -j LOCALFWD
/usr/sbin/iptables -A FORWARD -j VPNFWD -m state --state NEW

#NAT ICMP Ping And Errors / Redirect (Type 3/11/5)
/usr/sbin/iptables -A SYSNAT -t nat -j NATOUT -p icmp --icmp-type echo-reply
/usr/sbin/iptables -A SYSNAT -t nat -j NATOUT -p icmp --icmp-type echo-request
/usr/sbin/iptables -A SYSNAT -t nat -j NATOUT -p icmp --icmp-type redirect

</xsl:text>

  <xsl:if test="/config/IP/SysConf/Option[@option = 'PrimaryDns'] != ''">
    <xsl:text>#NAT Access To Specified Primary Domain Server&#xa;</xsl:text>
    <xsl:value-of select="concat('/usr/sbin/iptables -A SYSNAT -t nat -j NATOUT -p udp --sport 1024:65535 -d ',/config/IP/SysConf/Option[@option = 'PrimaryDns'],' --dport 53',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A SYSNAT -t nat -j NATOUT -p udp --sport 53 -d ',/config/IP/SysConf/Option[@option = 'PrimaryDns'],' --dport 53',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A SYSNAT -t nat -j NATOUT -p tcp --sport 1024:65535 -d ',/config/IP/SysConf/Option[@option = 'PrimaryDns'],' --dport 53',$nl,$nl)"/>
  </xsl:if>

  <xsl:if test="/config/IP/SysConf/Option[@option = 'SecondaryDns'] != ''">
    <xsl:text>#NAT Access To Specified Secondary Domain Server&#xa;</xsl:text>
    <xsl:value-of select="concat('/usr/sbin/iptables -A SYSNAT -t nat -j NATOUT -p udp --sport 1024:65535 -d ',/config/IP/SysConf/Option[@option = 'SecondaryDns'],' --dport 53',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A SYSNAT -t nat -j NATOUT -p udp --sport 53 -d ',/config/IP/SysConf/Option[@option = 'SecondaryDns'],' --dport 53',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A SYSNAT -t nat -j NATOUT -p tcp --sport 1024:65535 -d ',/config/IP/SysConf/Option[@option = 'SecondaryDns'],' --dport 53',$nl,$nl)"/>
  </xsl:if>

  <xsl:text>#Allow SIP Out From Server&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SYSNAT -t nat -j NATOUT -p udp -s ',$intip,'/32 -d 0/0 --sport 5000 --dport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SYSNAT -t nat -j NATOUT -p udp -s ',$intip,'/32 -d 0/0 --sport 5060 --dport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A SYSNAT -t nat -j NATOUT -p tcp -s ',$intip,'/32 -d 0/0 --sport 5060:5061 --dport 1024:65535',$nl,$nl)"/>

  <xsl:for-each select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32')]">
    <xsl:value-of select="concat('/usr/sbin/iptables -A LOOPOUT -j ACCEPT -o lo -d ',@ipaddr,'/32  -s  ',@ipaddr,'/32',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A LOOPOUT -j ACCEPT -o lo -p tcp --tcp-flags RST,SYN RST -m length --length 40 -d ',@ipaddr,'/32 -s ',@nwaddr,'/',@subnet,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A LOOPIN -j ACCEPT -i lo -s ',@ipaddr,'/32  -d ',@ipaddr,'/32',$nl)"/>
    <xsl:text>/usr/sbin/iptables -A INPUT -j DENY ! -i </xsl:text>
    <xsl:call-template name="getbaseif"/>
    <xsl:value-of select="concat(' -s ',@nwaddr,'/',@subnet,$nl)"/>
    <xsl:text>/usr/sbin/iptables -A OUTPUT -j DENY ! -o </xsl:text>
    <xsl:call-template name="getbaseif"/>
    <xsl:value-of select="concat(' -d ',@nwaddr,'/',@subnet,$nl)"/>
  </xsl:for-each>

  <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:value-of select="concat('/usr/sbin/iptables -A LOOPOUT -j ACCEPT -o lo -d ',@local,'/32  -s  ',@local,'/32',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A LOOPOUT -j ACCEPT -o lo -p tcp --tcp-flags RST,SYN RST -m length --length 40 -d ',@local,'/32 -s ',@nwaddr,'/30',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A LOOPIN -j ACCEPT -i lo -s ',@local,'/32  -d ',@local,'/32',$nl)"/>
    <xsl:text>/usr/sbin/iptables -A INPUT -j DENY ! -i </xsl:text>
    <xsl:call-template name="getbaseif">
      <xsl:with-param name="iface" select="concat('gtun',position()-1)"/>
    </xsl:call-template>
    <xsl:value-of select="concat(' -s ',@nwaddr,'/30',$nl)"/>
    <xsl:text>/usr/sbin/iptables -A OUTPUT -j DENY ! -o </xsl:text>
    <xsl:call-template name="getbaseif">
      <xsl:with-param name="iface" select="concat('gtun',position()-1)"/>
    </xsl:call-template>
    <xsl:value-of select="concat(' -d ',@nwaddr,'/30',$nl)"/>
  </xsl:for-each>

  <xsl:text>#Zero Conf&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j DENY ! -i ',$intiface,'  -s 169.254.0.0/16',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j DENY ! -o ',$intiface,'  -d 169.254.0.0/16',$nl)"/>

  <xsl:text>#Allow All Established Connections

/usr/sbin/iptables -A INPUT -j ACCEPT -m state --state ESTABLISHED,RELATED
/usr/sbin/iptables -A OUTPUT -j ACCEPT -m state --state ESTABLISHED,RELATED

#Allow SCTP Traffic For Media Gateway
/usr/sbin/iptables -A LOOPIN -j ACCEPT -i lo -p sctp
/usr/sbin/iptables -A LOOPOUT -j ACCEPT -o lo -p sctp

#Allow mISDN
/usr/sbin/iptables -A LOOPIN -j ACCEPT -i lo -p 34
/usr/sbin/iptables -A LOOPOUT -j ACCEPT -o lo -p 34

</xsl:text>

  <xsl:for-each select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32') and
     ((. != $extiface) or ($extcon = 'ADSL') or ($extiface = $intiface))]"> 
    <xsl:value-of select="concat('#Allow Nmb/Wins Requests For ',@name,' (',.,')',$nl)"/>
    <xsl:text>/usr/sbin/iptables -A SMBIN -j ACCEPT -i </xsl:text>
    <xsl:call-template name="getbaseif"/>
    <xsl:value-of select="concat(' -p udp --sport 137:138 -d ',@bcaddr,'/32 --dport 137:138',$nl)"/>
    <xsl:text>/usr/sbin/iptables -A SMBIN -j ACCEPT -i </xsl:text>
    <xsl:call-template name="getbaseif"/>
    <xsl:value-of select="concat(' -p udp --sport 1024:65535 -d ',@bcaddr,'/32 --dport 137:138',$nl)"/>
    <xsl:text>/usr/sbin/iptables -A SMBOUT -j ACCEPT -o </xsl:text>
    <xsl:call-template name="getbaseif"/>
    <xsl:value-of select="concat(' -p udp --sport 137:138 -d ',@bcaddr,'/32 --dport 137:138',$nl)"/>
    <xsl:text>/usr/sbin/iptables -A SMBOUT -j ACCEPT -o </xsl:text>
    <xsl:call-template name="getbaseif"/>
    <xsl:value-of select="concat(' -p udp --sport 1024:65535 -d ',@bcaddr,'/32 --dport 137:138',$nl,$nl)"/>
  </xsl:for-each>
  <xsl:call-template name="smbads"/>

  <xsl:if test="/config/Radius/Config/Option[@option = 'PPPoE'] != ''">
    <xsl:value-of select="concat('/usr/sbin/iptables -A SMBIN -j ACCEPT -i ',$intiface,' -p udp -s ',/config/Radius/Config/Option[@option = 'PPPoE'],' --sport 137:138 -d ',$intip,'/32 --dport 137:138',$nl)"/>
  </xsl:if>

  <xsl:if test="(/config/IP/SysConf/Option[@option = 'PrimaryWins'] != '') and (/config/IP/SysConf/Option[@option = 'PrimaryWins'] != $intip)">
    <xsl:value-of select="concat('/usr/sbin/iptables -A SMBOUT -j ACCEPT -p udp -o ',$intiface,'  -s ',$intip,' -d ',/config/IP/SysConf/Option[@option = 'PrimaryWins'],' --sport 1024:65535 --dport 137:138 -m state --state NEW,ESTABLISHED',$nl)"/>
  </xsl:if>
  <xsl:if test="(/config/IP/SysConf/Option[@option = 'SecondaryWins'] != '')">
    <xsl:value-of select="concat('/usr/sbin/iptables -A SMBOUT -j ACCEPT -p udp -o ',$intiface,'  -s ',$intip,' -d ',/config/IP/SysConf/Option[@option = 'SecondaryWins'],' --sport 1024:65535 --dport 137:138 -m state --state NEW,ESTABLISHED',$nl)"/>
  </xsl:if>

  <xsl:text>#STUN Loopback&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A LOOPIN -j ACCEPT -p udp ',$sfnew,' --sport 3478:3479 --dport 10000:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A LOOPOUT -j ACCEPT -p udp ',$sfnew,' --sport 3478:3479 --dport 10000:65535',$nl,$nl)"/>

  <xsl:if test="/config/Radius/Config/Option[@option = 'PPPoE'] != ''">
    <xsl:text>#Allow Access Control For Wireless Range&#xa;</xsl:text>
    <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j PPPIN -i ppp+ -s ',/config/Radius/Config/Option[@option = 'PPPoE'],$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j PPPOUT -o ppp+ -d ',/config/Radius/Config/Option[@option = 'PPPoE'],$nl,$nl)"/>
  </xsl:if>

  <xsl:if test="/config/IP/SysConf/Option[@option = 'L2TPNet'] != ''">
    <xsl:text>&#xa;#Allow Access Control For L2TP Range&#xa;</xsl:text>
    <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j PPPIN -i ppp+ -s ',/config/IP/SysConf/Option[@option = 'L2TPNet'],$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j PPPOUT -o ppp+ -d ',/config/IP/SysConf/Option[@option = 'L2TPNet'],$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j PPPOUT -o ppp+ -d 224.0.0.0/3',$nl,$nl)"/>
  </xsl:if>

  <xsl:text>#Access From Interfaces For Web Proxy&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j WEBACCESSI -p tcp -d ',$intip,'/32 --dport 80',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j WEBACCESSO  -p tcp -s ',$intip,'/32 --sport 80',$nl)"/>

  <xsl:if test="($zcipaddr != '')">
    <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j WEBACCESSI -p tcp -d ',$zcipaddr,'/32 --dport 80',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j WEBACCESSO  -p tcp -s ',$zcipaddr,'/32 --sport 80',$nl,$nl)"/>
  </xsl:if>

  <xsl:text>#Allow SMB Broadcasts
/usr/sbin/iptables -A INPUT -j SMBNFSIN
/usr/sbin/iptables -A OUTPUT -j SMBNFSOUT
/usr/sbin/iptables -A SMBNFSIN -j SMBIN -p udp --sport 137:138
/usr/sbin/iptables -A SMBNFSIN -j SMBIN -p udp --dport 137:138
/usr/sbin/iptables -A SMBNFSOUT -j SMBOUT -p udp --dport 137:138
/usr/sbin/iptables -A SMBNFSOUT -j SMBOUT -p udp --dport 137:138
</xsl:text>

  <xsl:for-each select="/config/NFS/Mounts/Mount[contains(@mount,':/')]">
    <xsl:value-of select="concat('#Allowing NFS For ',.,' (',@folder,')',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A NFSIN -j ACCEPT -s ',substring-before(@mount,':'),$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A NFSOUT -j ACCEPT -s ',substring-before(@mount,':'),$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A NFSIN -j ACCEPT -d ',substring-before(@mount,':'),$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A NFSOUT -j ACCEPT -d ',substring-before(@mount,':'),$nl,$nl)"/>
  </xsl:for-each>

  <xsl:for-each select="/config/NFS/Shares/Share">
    <xsl:value-of select="concat('#Allowing NFS For ',@ipaddr,' (',.,')',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A NFSIN -j ACCEPT -s ',@ipaddr,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A NFSOUT -j ACCEPT -s ',@ipaddr,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A NFSIN -j ACCEPT -d ',@ipaddr,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A NFSOUT -j ACCEPT -d ',@ipaddr,$nl,$nl)"/>
  </xsl:for-each>

  <xsl:for-each select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32')]">
    <xsl:text>/usr/sbin/iptables -A NFSIN -j ACCEPT -i </xsl:text>
    <xsl:call-template name="getbaseif"/>
    <xsl:value-of select="concat(' -s ',@nwaddr,'/',@subnet,' -d ',@ipaddr,$nl)"/>
    <xsl:text>/usr/sbin/iptables -A NFSOUT -j ACCEPT -o </xsl:text>
    <xsl:call-template name="getbaseif"/>
    <xsl:value-of select="concat(' -d ',@nwaddr,'/',@subnet,' -s ',@ipaddr,$nl)"/>
  </xsl:for-each>

  <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:text>/usr/sbin/iptables -A NFSIN -j ACCEPT -i </xsl:text>
    <xsl:value-of select="concat('gtun',position()-1,' -s ',@nwaddr,'/30',' -d ',@local,$nl)"/>
    <xsl:text>/usr/sbin/iptables -A NFSOUT -j ACCEPT -o </xsl:text>
    <xsl:value-of select="concat('gtun',position()-1,' -d ',@nwaddr,'/30',' -s ',@local,$nl)"/>
  </xsl:for-each>

<xsl:text>
#Allow NFS/RPC
for port in "111" "2049" "32765:32769";do
  for proto in "udp" "tcp";do
    for direc in "IN" "OUT";do
      if [ "$direc" == "IN" ];then
        direc2="SMBNFSIN"
        iface="-i"
       else
        direc2="SMBNFSOUT"
        iface="-o"
      fi;
      for pflag in "--sport" "--dport";do
        iptables -A $direc2 -j NFS$direc -p $proto $pflag $port
      done;
    done;
  done;
done;

</xsl:text>
  <xsl:call-template name="sysinout"/>

  <xsl:text>#Allow Access To Specified Primary Domain Server&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DNSIN -j ACCEPT -p udp ',$sfnew,' --sport 1024:65535 --dport 53',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DNSOUT -j ACCEPT -p udp ',$sfold,' --dport 1024:65535 --sport 53',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DNSOUT -j ACCEPT -p udp ',$sfnrel,' --dport 1024:65535 --sport 53',$nl)"/>

  <xsl:value-of select="concat('/usr/sbin/iptables -A DNSIN -j ACCEPT -p udp ',$sfnew,' --sport 53 --dport 53',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DNSOUT -j ACCEPT -p udp ',$sfold,' --dport 53 --sport 53',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DNSOUT -j ACCEPT -p udp ',$sfnrel,' --dport 53 --sport 53',$nl)"/>

  <xsl:value-of select="concat('/usr/sbin/iptables -A DNSIN -j ACCEPT -p tcp ',$sfnew,' --sport 1024:65535 --dport 53',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DNSOUT -j ACCEPT -p tcp ',$sfold,' ! --syn --dport 1024:65535 --sport 53',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DNSOUT -j ACCEPT -p tcp ',$sfnrel,' --dport 1024:65535 --sport 53',$nl,$nl)"/>

  <xsl:text>#IGMP Multi Cast Packets&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A MCASTOUT -j ACCEPT ',$sfnew,' -p igmp -d 224.0.0.22',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A MCASTIN -j ACCEPT ',$sfnew,' -p igmp -d 224.0.0.22',$nl)"/>

  <xsl:text>#OSPF Multi Cast Packets&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A MCASTOUT -j ACCEPT ',$sfnew,' -p ospf -d 224.0.0.5',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A MCASTIN -j ACCEPT ',$sfnew,' -p ospf -d 224.0.0.5',$nl)"/>

  <xsl:text>#OSPF Multi Cast Packets&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A MCASTOUT -j ACCEPT ',$sfnew,' -p ospf -d 224.0.0.6',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A MCASTIN -j ACCEPT ',$sfnew,' -p ospf -d 224.0.0.6',$nl,$nl)"/>

  <xsl:text>#RIP Multi Cast Packets&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A MCASTOUT -j ACCEPT ',$sfnew,' -p udp --sport 520 --dport 520 -d 224.0.0.9',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A MCASTIN -j ACCEPT ',$sfnew,' -p udp --sport 520 --dport 520 -d 224.0.0.9',$nl,$nl)"/>

  <xsl:text>#Allow Access To External Nameservers For DNS ServerIP UDP Mode&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p udp ',$sfnew,' -d 0/0 --dport 53 --sport 53',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p udp ',$sfnew,' -d 0/0 --dport 53 --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -d 0/0 --dport 53 --sport 1024:65535',$nl,$nl)"/>

  <xsl:text>#Accept ingress on tcp flags
/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp --tcp-flags SYN,ACK SYN,ACK
/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp --tcp-flags SYN,ACK,RST RST
/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp --tcp-flags SYN,ACK,PSH ACK,PSH
/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp --tcp-flags SYN,ACK ACK
</xsl:text>

  <xsl:if test="/config/DNS/Config/Option[@option = 'ExtServ'] = 'true'">
    <xsl:text>#Allow Access To Nameserver Externaly UDP Mode&#xa;</xsl:text>
    <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p udp ',$sfnew,' -s 0/0 --sport 53 --dport 53',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p udp ',$sfnew,' -s 0/0 --sport 1024:65535 --dport 53',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -s 0/0 --sport 1024:65535 --dport 53',$nl)"/>
  </xsl:if>

  <xsl:text>&#xa;#SSH Access&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -d 0/0 --dport 22 --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -s 0/0 --dport 22 --sport 1024:65535',$nl,$nl)"/>

  <xsl:text>#OVPN Access&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -s 0/0 --dport 1194 --sport 1024:65535',$nl,$nl)"/>

  <xsl:text>#L2TP Access&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p udp ',$sfnew,' -s 0/0 --dport 1701 --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p esp -m state --state RELATED',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p udp -m state --state NEW --dport 1024:65535 --sport 1024:65535',$nl,$nl)"/>

  <xsl:text>#Allow Access To Time Server&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p udp ',$sfnew,' -d 0/0 --dport 123 --sport 123',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p udp ',$sfnew,' -d 0/0 --dport 123 --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p udp ',$sfnew,' -d 0/0 --dport 123 --sport 123',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p udp ',$sfnew,' -d 0/0 --dport 123 --sport 1024:65535',$nl,$nl)"/>

  <xsl:text>#Allow Access To IMAP/POP3 Remotely&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -s 0/0 --sport 1024:65535 --dport 143',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -s 0/0 --sport 1024:65535 --dport 110',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -s 0/0 --sport 1024:65535 --dport 993',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -s 0/0 --sport 1024:65535 --dport 995',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -s 0/0 --sport 1024:65535 --dport 143',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -s 0/0 --sport 1024:65535 --dport 110',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -s 0/0 --sport 1024:65535 --dport 993',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -s 0/0 --sport 1024:65535 --dport 995',$nl,$nl)"/>

  <xsl:text>#Allow Access To STUN Remotely&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p udp ',$sfnew,' -s 0/0 --sport 1024:65535 --dport 3478:3479',$nl,$nl)"/>

  <xsl:text>#Allow Remote SIP/IAX2/FOP&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p udp ',$sfnew,' -d 0/0 --sport 5000 --dport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p udp ',$sfnew,' -d 0/0 --sport 5060 --dport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -d 0/0 --sport 5060:5061 --dport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p udp ',$sfnew,' -d 0/0 --dport 4569 --sport 1024:65535',$nl,$nl)"/>

  <xsl:text>#Allow Remote H.323 Registrations&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p udp ',$sfnew,' -s 0/0 --sport 1024:65535 --dport 1719:1722',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p udp ',$sfnew,' -d 0/0 --dport 1719:1722 --sport 1024:65535',$nl,$nl)"/>

  <xsl:text>#Allow Remote H.323 Signaling&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -s 0/0 --sport 1024:65535 --dport 10000:20000',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -d 0/0 --sport 10000:12999 --dport 1024:65535',$nl,$nl)"/>

  <xsl:text>#Allow Access To LDAP/TLS Remotely&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp -m state --state ESTABLISHED,NEW,INVALID --sport 1024:65535 --dport 636',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp -m state --state ESTABLISHED,NEW,INVALID --dport 1024:65535 --sport 636',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp -m state --state ESTABLISHED,NEW,INVALID --sport 1024:65535 --dport 389',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp -m state --state ESTABLISHED,NEW,INVALID --dport 1024:65535 --sport 389',$nl,$nl)"/>

  <xsl:text>#Allow Remote SMTP Connections&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -d 0/0 --dport 25 --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -d 0/0 --dport 587 --sport 1024:65535',$nl)"/>
  <xsl:if test="/config/Email/Config/Option[@option = 'Delivery'] != 'Deffered'">
    <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -s 0/0 --dport 25 --sport 1024:65535',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -s 0/0 --dport 587 --sport 1024:65535',$nl)"/>
  </xsl:if>
  <xsl:text>&#xa;</xsl:text>

  <xsl:text>#Allow Remote ident Connections&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -d 0/0 --dport 113 --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -s 0/0 --sport 1024:65535 --dport 113',$nl,$nl)"/>

  <xsl:text>#Accept External Access To Web Server/FTP&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -s 0.0.0.0/0 --sport 1024:65535 --dport 80',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -s 0.0.0.0/0 --dport 1024:65535 --sport 80',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfrel,' -s 0.0.0.0/0 --sport 20 --dport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -s 0.0.0.0/0 --sport 989 --dport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -d 0.0.0.0/0 --dport 443 --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -d 0.0.0.0/0 --dport 666 --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -d 0.0.0.0/0 --dport 3128 --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp ',$sfnew,' -d 0.0.0.0/0 --dport 3129 --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p tcp --dport 8080 -m state --state ESTABLISHED,NEW',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -d 0.0.0.0/0 --dport 80 --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -d 0.0.0.0/0 --dport 21 --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -d 0.0.0.0/0 --dport 990 --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -d 0.0.0.0/0 --dport 443 --sport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfnew,' -d 0.0.0.0/0 --dport 666 --sport 1024:65535',$nl)"/>
  <xsl:text>&#xa;</xsl:text>

  <xsl:text>#Allow IKE Negotiation / NAT-T
/usr/sbin/iptables -A DEFIN -j ACCEPT -p udp -s 0/0 --sport 500 --dport 500
/usr/sbin/iptables -A DEFIN -j ACCEPT -p udp -s 0/0 --sport 1024:65535 --dport 500
/usr/sbin/iptables -A DEFIN -j ACCEPT -p udp -s 0/0 --sport 1024:65535 --dport 4500
/usr/sbin/iptables -A DEFOUT -j ACCEPT -p udp -d 0/0 --dport 500 --sport 500
/usr/sbin/iptables -A DEFOUT -j ACCEPT -p udp -d 0/0 --dport 1024:65535 --sport 500
/usr/sbin/iptables -A DEFOUT -j ACCEPT -p udp -d 0/0 --dport 1024:65535 --sport 4500

#Allow FTP Data&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p tcp ',$sfrel,' -d 0.0.0.0/0 --dport 1024:65535 --sport 1024:65535',$nl,$nl)"/>

  <xsl:text>#Allow Remote RTP&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p udp ',$sfnew,' --dport 1024:65535 --sport 10000:20000',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFOUT -j ACCEPT -p udp -s 0/0 --sport 10000:20000 --dport 1024:65535',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p udp ',$sfnre,' --sport 1024:65535 --dport 10000:20000',$nl)"/>
  <xsl:value-of select="concat('/usr/sbin/iptables -A DEFIN -j ACCEPT -p udp -s 0/0 --sport 1024:65535 --dport 10000:20000',$nl)"/>

  <xsl:text>
#Dont NAT Local/Tunnel Traffic And Allow Webserver On All Interfaces
/usr/sbin/iptables -A NONAT -t nat -j NOFWDNAT
/usr/sbin/iptables -A NONAT -t nat -j NOPPPNAT -o ppp+
/usr/sbin/iptables -A NONAT -t nat -j ACCEPT -o gtun+
/usr/sbin/iptables -A NONAT -t nat -j ACCEPT -o vpn+
</xsl:text>

  <xsl:for-each select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32')]">
    <xsl:text>/usr/sbin/iptables -A NONAT -t nat -j ACCEPT -o </xsl:text>
    <xsl:call-template name="getbaseif"/>
    <xsl:value-of select="concat(' -s ',@nwaddr,'/',@subnet,' -d ',@nwaddr,'/',@subnet,$nl)"/>
    <xsl:if test="(. != $intiface) and ((. != $extiface) or ($extcon = 'ADSL'))">
      <xsl:value-of select="concat('#Web Proxy For ',.,$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -t nat -A WEBACCESS -j DNAT -p tcp --to-destination ',$intip,':80 --dport 80 --sport 1024:65535 -d ',@ipaddr,'/32 -s ',@nwaddr,'/',@subnet,$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A WEBACCESSI -j ACCEPT -p tcp --dport 80 --sport 1024:65535 -d ',$intip,'/32 -s ',@nwaddr,'/',@subnet,$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A WEBACCESSO -j ACCEPT -p tcp --sport 80 --dport 1024:65535 -s ',$intip,'/32 -d ',@nwaddr,'/',@subnet,$nl,$nl)"/>

     <xsl:value-of select="concat('#SIP/RTP Proxy For ',.,$nl)"/>
     <xsl:text>/usr/sbin/iptables -t nat -A SIPMAP -j DEFPROXY -i </xsl:text>
     <xsl:call-template name="getbaseif"/>
     <xsl:value-of select="concat(' -d ',@ipaddr,'/32',$nl)"/>

     <xsl:text>/usr/sbin/iptables -t nat -I SIPNAT -j SNAT -o </xsl:text>
     <xsl:call-template name="getbaseif"/>
     <xsl:value-of select="concat(' -d ',@nwaddr,'/',@subnet,' --to-source ',@ipaddr,$nl,$nl)"/>
    </xsl:if>
  </xsl:for-each>

  <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:text>/usr/sbin/iptables -A NONAT -t nat -j ACCEPT -o </xsl:text>
    <xsl:value-of select="concat('gtun',position()-1,' -s ',@nwaddr,'/30',' -d ',@nwaddr,'/30',$nl)"/>
    <xsl:if test="(. != $intiface) and ((. != $extiface) or ($extcon = 'Dialup'))">
      <xsl:value-of select="concat('#Web Proxy For gtun',position()-1,$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -t nat -A WEBACCESS -j DNAT -p tcp --to-destination ',$intip,':80 --dport 80 --sport 1024:65535 -d ',@local,'/32 -s ',@nwaddr,'/30',$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A WEBACCESSI -j ACCEPT -p tcp --dport 80 --sport 1024:65535 -d ',$intip,'/32 -s ',@nwaddr,'/30',$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A WEBACCESSO -j ACCEPT -p tcp --sport 80 --dport 1024:65535 -s ',$intip,'/32 -d ',@nwaddr,'/30',$nl,$nl)"/>

     <xsl:value-of select="concat('#SIP/RTP Proxy For gtun',position()-1,$nl)"/>
     <xsl:value-of select="concat('/usr/sbin/iptables -t nat -A SIPMAP -j DEFPROXY -i gtun',position()-1,' -d ',@local,'/32',$nl)"/>
     <xsl:value-of select="concat('/usr/sbin/iptables -t nat -I SIPNAT -j SNAT -o gtun',position()-1,' -d ',@nwaddr,'/30 --to-source ',@local,$nl,$nl)"/>
    </xsl:if>
  </xsl:for-each>

  <xsl:if test="($zcipaddr != '')">
    <xsl:value-of select="concat('#SIP/RTP Proxy For ',$intiface,' ',$zcipaddr,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -t nat -A SIPMAP -j DEFPROXY -i ',$intiface,' -d ',$zcipaddr,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -t nat -I SIPNAT -j SNAT -o ',$intiface,' -d 169.254.0.0/16 --to-source ',$zcipaddr,$nl,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -t nat -A WEBACCESS -j DNAT -p tcp --to-destination ',$intip,':80 --dport 80 --sport 1024:65535 -d ',$zcipaddr,'/32 -s 169.254.0.0/16',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A NONAT -t nat -j ACCEPT -o ',$intiface,' -s 169.254.0.0/16 -d 169.254.0.0/16',$nl,$nl)"/>
  </xsl:if>

  <xsl:for-each select="/config/IP/Routes/Route">
    <xsl:value-of select="concat('/usr/sbin/iptables -A NONAT -t nat -j ACCEPT -i ',$intiface,' -s ',@network,'/',@subnet,' -d ',$intip,'/32',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A NONAT -t nat -j ACCEPT -o ',$intiface,' -d ',@network,'/',@subnet,' -s ',$intip,'/32',$nl)"/>
  </xsl:for-each>

  <xsl:text>#Allow Local Lan Access To Local Web Server&#xa;</xsl:text>
  <xsl:value-of select="concat('/usr/sbin/iptables -A PROXYBYPASS -j ACCEPT -t nat -p tcp ',$sfnew,' -i ',$intiface,' -s ',$loclan,' --sport 1024:65535 -d ',$intip,'/32',$nl)"/>

  <xsl:for-each select="/config/IP/Routes/Route">
    <xsl:value-of select="concat('#Allow ',.,' Wan Access To Local Web Server',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A PROXYBYPASS -j ACCEPT -t nat -p tcp ',$sfnew,' -i ',$intiface,' -s ',@network,'/',@subnet,' --sport 1024:65535 -d ',$intip,'/32',$nl,$nl)"/>
  </xsl:for-each>

  <xsl:for-each select="/config/Proxy/Redirect/WWW">
    <xsl:value-of select="concat('#Allow Redirect For ',.,' To ',@ipaddr,'/32',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A PROXYOUT -j ACCEPT -o ',@interface,' -p tcp -s 0/0 --sport 1024:65535 -d ',@ipaddr,'/32 --dport 80 ',$sfnew,$nl,$nl)"/>
  </xsl:for-each>

  <xsl:if test="($zcipaddr != '')">
    <xsl:text>#Zeroconf&#xa;</xsl:text>
    <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j SYSIN -i ',$intiface,' -p udp --dport 137:138 --sport 137:138 -d 169.254.255.255/16',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j SYSIN -i ',$intiface,' -p udp --dport 137:138 --sport 1024:65535 -d 169.254.255.255/16',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j MCASTIN -i ',$intiface,' -s 169.254.0.0/16',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j SYSOUT -o ',$intiface,' -d 169.254.0.0/16 -s ',$zcipaddr,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j SYSOUT -o ',$intiface,' -d 169.254.0.0/16 -s ',$intip,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j MCASTOUT -o ',$intiface,' -s 169.254.0.0/16',$nl,$nl)"/>
  </xsl:if>

  <xsl:for-each select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32')]">
    <xsl:call-template name="ifaceconf"/>
  </xsl:for-each>
  <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:call-template name="ifaceconf">
      <xsl:with-param name="iface" select="concat('gtun',position()-1)"/>
      <xsl:with-param name="subnet" select="30"/>
      <xsl:with-param name="ipaddr" select="@local"/>
    </xsl:call-template>
  </xsl:for-each>

  <xsl:for-each select="/config/IP/Routes/Route">
    <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j SYSIN -i ',$intiface,' -s ',@network,'/',@subnet,' -d ',$intip,'/32',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j SYSOUT -o ',$intiface,' -d ',@network,'/',@subnet,' -s ',$intip,'/32',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -I WANFWD -j ACCEPT -i ',$intiface,' -o ',$intiface,' -s ',$loclan,' -d ',@network,'/',@subnet,' -m state --state NEW,INVALID',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -I WANFWD -j ACCEPT -i ',$intiface,' -o ',$intiface,' -d ',$loclan,' -s ',@network,'/',@subnet,' -m state --state NEW,ESTABLISHED',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -I WANFWD -j ACCEPT -i ',$intiface,' -o ',$intiface,' -s ',$loclan,' -d ',@network,'/',@subnet,' -m state --state NEW,ESTABLISHED',$nl)"/>
    <xsl:if test="$pdns != ''">
      <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j DNSIN -i ',$intiface,' -s ',@network,'/',@subnet,' -d ',$pdns,$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j DNSOUT -o ',$intiface,' -d ',@network,'/',@subnet,' -s ',$pdns,$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A FORWARD -j DNSFWD -i ',$intiface,' -s ',@network,'/',@subnet,'  -d ',$pdns,$nl)"/>
    </xsl:if>
    <xsl:if test="$sdns != ''">
      <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j DNSIN -i ',$intiface,' -s ',@network,'/',@subnet,' -d ',$sdns,$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j DNSOUT -o ',$intiface,' -d ',@network,'/',@subnet,' -s ',$sdns,$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A FORWARD -j DNSFWD -i ',$intiface,' -s ',@network,'/',@subnet,'  -d ',$sdns,$nl)"/>
    </xsl:if>
    <xsl:value-of select="concat('/usr/sbin/iptables -A INPUT -j MCASTIN -i ',$intiface,' -s ',@network,'/',@subnet,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A OUTPUT -j MCASTOUT -o ',$intiface,' -s ',@network,'/',@subnet,$nl)"/>
  </xsl:for-each>

  <xsl:for-each select="/config/IP/ADSL/Links/Link">
    <xsl:if test="(@virtip != '') and (@remip != '')">
      <xsl:value-of select="concat('#Virtual IP Setup For ',.,' ',@virtip,'-->',@remip,' (ppp',position(),')',$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -t mangle -A SIPLB -j MARK -d ',@virtip,' --set-mark 0x',position(),$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -t nat -A SIPLB -j DNAT -d ',@virtip,' --to-destination ',@remip,$nl,$nl)"/>
    </xsl:if>
  </xsl:for-each>

  <xsl:text>#Return Mapped Address Space
/usr/sbin/iptables -t mangle -A SIPLB -j RETURN -m mark ! --mark 0

/usr/sbin/iptables -A DEFPROXY -j REDIRECT -t nat -p tcp --sport 1024:65535 --dport 80 --to-port 8080 -m state --state ESTABLISHED,NEW
#/usr/sbin/iptables -A DEFPROXY -j DNAT -t nat -p udp --to-destination </xsl:text>
  <xsl:value-of select="$intip"/><xsl:text> --sport 1024:65535 --dport 4569
#/usr/sbin/iptables -A DEFPROXY -j DNAT -t nat -p udp --to-destination </xsl:text>
  <xsl:value-of select="$intip"/><xsl:text> --sport 1024:65535 --dport 5000
/usr/sbin/iptables -A DEFPROXY -j DNAT -t nat -p udp --to-destination </xsl:text>
  <xsl:value-of select="$intip"/><xsl:text> --sport 1024:65535 --dport 5060
/usr/sbin/iptables -A DEFPROXY -j DNAT -t nat -p tcp --to-destination </xsl:text>
  <xsl:value-of select="$intip"/><xsl:text> --sport 1024:65535 --dport 5060:5061
#/usr/sbin/iptables -A DEFPROXY -j DNAT -t nat -p udp --to-destination </xsl:text>
  <xsl:value-of select="$intip"/><xsl:text> --sport 1024:65535 --dport 10000:20000
#/usr/sbin/iptables -A DEFPROXY -j DNAT -t nat -p udp --to-destination </xsl:text>
  <xsl:value-of select="$intip"/><xsl:text> --sport 1024:65535 --dport 1718:1720
/usr/sbin/iptables -A DEFPROXY -j EXTPROXY -t nat

</xsl:text>
  <xsl:call-template name="squidrules"/>

<!--
gnx
XXX
there 2 refs bellow why how what ??
  foreach $esplink (@esptunnels) {
    @espdat=split(/\|/,$esplink);
    if (@espdat[4] ne "") {
      if (@espdat[2] eq "") {
        @espdat[2]=$sysconf{'Internal'};
      }

      @espidata=@{$interface->{@espdat[3]}};
      $espintip="@espidata[1]/32";
      $dmznw=getnw(@espidata[2],@espidata[1]);
      $dmznw.="/" . @espidata[2];
    } else {
      @espdat[4]=@espdat[3];
      $dmznw=@espdat[1];
      @espdat[1]=@espdat[2];
    }
    print FW "\n#Traffic For $dmznw <-> @espdat[1] Encrypted Tunnel\n";
    print FW<<__EOB__;
/sbin/ip route del @espdat[1] table Ipsec
/sbin/ip route add @espdat[1] src $localip dev @espdat[4] table Ipsec
/usr/sbin/iptables -I VPNCIN -j SYSIN -i ppp+ -s @espdat[1] -d $localip/32
/usr/sbin/iptables -I VPNCOUT -j SYSOUT -o ppp+ -d @espdat[1] -s  $localip/32
/usr/sbin/iptables -I VPNCIN -j SYSIN -i @espdat[4] -s @espdat[1] -d $localip/32
/usr/sbin/iptables -I VPNCOUT -j SYSOUT -o @espdat[4] -d @espdat[1] -s  $localip/32
/usr/sbin/iptables -I VPNCIN -j ACCEPT -i @espdat[4] -s @espdat[1] -d $dmznw
/usr/sbin/iptables -I VPNCOUT -j ACCEPT -o @espdat[4] -d @espdat[1] -s $dmznw
/usr/sbin/iptables -A VPNFWD -j ACCEPT -i $sysconf{'Internal'}+ -s $dmznw -d @espdat[1]
/usr/sbin/iptables -A VPNFWD -j ACCEPT -o $sysconf{'Internal'}+ -d $dmznw -s @espdat[1]
/usr/sbin/iptables -t nat -A VPNNAT -j ACCEPT -s $dmznw -d @espdat[1] 
/usr/sbin/iptables -t nat -A VPNWEB -j ACCEPT -d $dmznw -s @espdat[1] 
__EOB__
  } 

  if (@espdat[4] ne "") {
    print FW <<__EOB__;
      /usr/sbin/iptables -t nat -A DMZNAT -j ACCEPT -o @espdat[4] ! -s $dmznw -d @espdat[0]
      /usr/sbin/iptables -t nat -A DMZNAT -j SNAT - -to-source @espidata[1] -o @espdat[4] ! -s $dmznw -d @espdat[1]
__EOB__
  }
-->
  <xsl:text>#SET Up A Tar Pit
/usr/sbin/iptables -A INPUT -j SIPIN
/usr/sbin/iptables -A INPUT -j TARPIT -p udp --dport 5000 -m state --state ESTABLISHED
/usr/sbin/iptables -A INPUT -j TARPIT -p udp --dport 5060 -m state --state ESTABLISHED
/usr/sbin/iptables -A INPUT -j TARPIT -p tcp --dport 5060:5061 -m state --state ESTABLISHED
/usr/sbin/iptables -A INPUT -j TARPIT -m state --state NEW
/usr/sbin/iptables -A TARPIT -j RETURN -p tcp --dport 3128
/usr/sbin/iptables -A TARPIT -j RETURN -p tcp --dport 3129
/usr/sbin/iptables -A TARPIT -j RETURN -p tcp --dport 8080
/usr/sbin/iptables -A TARPIT -j RETURN -p tcp --dport 443
/usr/sbin/iptables -A TARPIT -j RETURN -p tcp --dport 666
/usr/sbin/iptables -A TARPIT -j RETURN -p udp --dport 53
/usr/sbin/iptables -A TARPIT -j RETURN -p udp --dport 137:138
/usr/sbin/iptables -A TARPIT -j RETURN -p udp --sport 1024:65535 --dport 10000:20000
/usr/sbin/iptables -A TARPIT -j RETURN -m state --state ESTABLISHED -m limit --limit 2/s --limit-burst 5
/usr/sbin/iptables -A TARPIT -j RETURN -m state --state NEW -m limit --limit 2/s --limit-burst 5
/usr/sbin/iptables -A TARPIT -j LOG -m recent --rcheck --seconds 30 --hitcount 20 --name RATELIM -m limit --limit 6/minute --limit-burst 1 --log-prefix "RATELIM " --log-level debug
/usr/sbin/iptables -A TARPIT -j DENY -m recent --name RATELIM --update --seconds 30 --hitcount 20
/usr/sbin/iptables -A TARPIT -j RETURN -m recent --name RATELIM --set

#Default Incoming/Outgoing Rules
/usr/sbin/iptables -A INPUT -j LOCALIN
/usr/sbin/iptables -A OUTPUT -j LOCALOUT
#Forward Access To Specified Primary Domain Server
/usr/sbin/iptables -A DNSFWD -j ACCEPT -p udp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --sport 1024:65535 --dport 53
/usr/sbin/iptables -A DNSFWD -j ACCEPT -p udp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --sport 53 --dport 53
/usr/sbin/iptables -A DNSFWD -j ACCEPT -p tcp </xsl:text><xsl:value-of select="$sfnew"/><xsl:text> --sport 1024:65535 --dport 53

</xsl:text>

  <xsl:for-each select="/config/Radius/RAS/Modem">
    <xsl:value-of select="concat('#PPP Link ',.,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A SYSFWD -j GWOUT -i ppp+ -s ',@remote,' -d 0/0 -m state --state NEW,ESTABLISHED',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A SYSFWD -j GWIN -o ppp+ -d ',@remote,' -s 0/0 -m state --state RELATED,ESTABLISHED',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/iptables -A SYSNAT -t nat -j NATOUT -s ',@remote,$nl,$nl)"/>
  </xsl:for-each>

  <xsl:if test="/config/Radius/Config/Option[@option = 'PPPoE'] != ''">
    <xsl:text>#PPPoE Connections NAT&#xa;</xsl:text>
    <xsl:value-of select="concat('/usr/sbin/iptables -A SYSNAT -t nat -j NATOUT -s ',/config/Radius/Config/Option[@option = 'PPPoE'],$nl,$nl)"/>
  </xsl:if>

  <xsl:if test="count(/config/IP/WiFi[@type = 'Hotspot']) &gt; 0">
    <xsl:text>#Hotspot NAT&#xa;</xsl:text>
  </xsl:if>
  <xsl:for-each select="/config/IP/Interfaces/Interface">
    <xsl:variable name="iface" value="."/>
    <xsl:if test="/config/IP/WiFi[. = current()]/@type = 'Hotspot'">
      <xsl:value-of select="concat('/usr/sbin/iptables -A SYSFWD -j GWOUT -i tun+ -s ',@nwaddr,'/',@subnet,' -d 0/0 -m state --state NEW,ESTABLISHED',$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A SYSFWD -j GWIN -o tun+ -d ',@nwaddr,'/',@subnet,' -s 0/0 -m state --state RELATED,ESTABLISHED',$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -t nat -A SYSNAT -j NATOUT -s ',@nwaddr,'/',@subnet,$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A TXPROXY -t nat -j REDIRECT -p tcp --to-port 3129 -i ',.,' -s ',@nwaddr,'/',@subnet,$nl)"/>
      <xsl:value-of select="concat('/usr/sbin/iptables -A TXPROXY -t nat -j REDIRECT -p tcp --to-port 3129 -i tun+ -s ',@nwaddr,'/',@subnet,$nl,$nl)"/>
    </xsl:if>
  </xsl:for-each>

  <xsl:text>#Allow Transparent Proxy For Zero Conf/DHCP Lan
/usr/sbin/iptables -A TXPROXY -p tcp -t nat -j REDIRECT --to-port 3129 -i </xsl:text>
  <xsl:value-of select="$intiface"/><xsl:text> -s 169.254.0.0/16
/usr/sbin/iptables -A TXPROXY -j DYNAMICPRE -t nat
/usr/sbin/iptables -t nat -A SYSNAT -j NATOUT -s 169.254.0.0/16

/usr/sbin/iptables -A OUTPUT -j SIPOUT
/usr/sbin/iptables -A INPUT -j SBSRULESI
/usr/sbin/iptables -A OUTPUT -j SBSRULESO
/usr/sbin/iptables -A FORWARD -j WANFWD
/usr/sbin/iptables -A FORWARD -j PPPFWD
/usr/sbin/iptables -A INPUT -j 3GIN
/usr/sbin/iptables -A OUTPUT -j 3GOUT
/usr/sbin/iptables -A FORWARD -j MANGLEFWD
/usr/sbin/iptables -A INPUT -j MANGLEIN
/usr/sbin/iptables -A OUTPUT -j MANGLEOUT
#Reject And Log All Other Packets
/usr/sbin/iptables -A INPUT -j DENY
/usr/sbin/iptables -A OUTPUT -j DENY

#Drop Braindead Windows SMB Requests
/usr/sbin/iptables -A FORWARD -j DROP -p udp -s 0/0 --sport 137 -d 0/0 --dport 137
/usr/sbin/iptables -A FORWARD -j DENY

if [ "$1" == "startup" ];then
  /etc/rc.d/rc.firewall2 startup
  if [ -x /tmp/pppup/ext.ip-up ];then
    /tmp/pppup/ext.ip-up
  fi;
  for link in /tmp/pppup/ppp[1-9].ip-up ;do
    if [ -x $link ] &amp;&amp; [ -d /sys/class/net/${link:11:4} ];then
      $link
    fi;
  done;
fi;

if [ -x /etc/firewall.local ];then
  /etc/firewall.local
fi;
</xsl:text>
</xsl:template>
</xsl:stylesheet>

