<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="extiface" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="extip" select="/config/IP/Interfaces/Interface[text() = $extiface]/@ipaddr"/>
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>

<xsl:template name="clessb2m">
  <xsl:param name="bits"/>
  <xsl:param name="output" select="'1'"/>

  <xsl:choose>
    <xsl:when test="$bits != 0">
      <xsl:call-template name="clessb2m">
        <xsl:with-param name="bits" select="$bits - 1"/>
        <xsl:with-param name="output" select="$output * 2"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="256 - $output"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="bitstomask">
  <xsl:param name="quad" select="0"/>
  <xsl:param name="bits"/>

  <xsl:if test="$quad &lt; 4">
    <xsl:if test="$quad &lt; floor($bits div 8)">
      <xsl:text>255</xsl:text>
    </xsl:if>
    <xsl:if test="$quad &gt; floor($bits div 8)">
      <xsl:text>0</xsl:text>
    </xsl:if>
    <xsl:if test="$quad = floor($bits div 8)">
      <xsl:call-template name="clessb2m">
        <xsl:with-param name="bits" select="8 - ($bits mod 8)"/>
      </xsl:call-template>
    </xsl:if>
    <xsl:if test="$quad &lt; 3">
      <xsl:text>.</xsl:text>
    </xsl:if>
    <xsl:call-template name="bitstomask">
      <xsl:with-param name="bits" select="$bits"/>
      <xsl:with-param name="quad" select="$quad+1"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>  

<xsl:template match="Access">
  <xsl:choose>
    <xsl:when test=". = 'false'">
      <xsl:value-of select="concat('ACL Deny ',@ipaddr,'/',@subnet,' - *',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('ACL Allow ',@ipaddr,'/',@subnet,' - *',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Interface">
  <xsl:param name="netmask" select="@netmask"/>
  <xsl:param name="useipaddr"/>

  <xsl:choose>
    <xsl:when test="$useipaddr = 1">
      <xsl:value-of select="concat('ACL Allow ',@ipaddr,'/',$netmask,' - *',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('ACL Allow ',@nwaddr,'/',$netmask,' - *',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Tunnel">
  <xsl:param name="useipaddr"/>

  <xsl:choose>
    <xsl:when test="$useipaddr = 1">
      <xsl:value-of select="concat('ACL Allow ',@local,'/255.255.255.255 - *',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('ACL Allow ',@nwaddr,'/255.255.255.252 - *',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Route">
  <xsl:value-of select="concat('ACL Allow ',@network,'/',@netmask,' - *',$nl)"/>
</xsl:template>

<xsl:template match="Modem">
  <xsl:value-of select="concat('ACL Allow ',@remote,'/255.255.255.255 - *',$nl)"/>
</xsl:template>

<xsl:template match="WiFi">
  <xsl:value-of select="concat('ACL Allow ',/config/IP/Interfaces/Interface[. = current()]/@nwaddr,'/',
       /config/IP/Interfaces/Interface[. = current()]/@netmask,' - *',$nl)"/>
</xsl:template>

<xsl:template name="cidraccess">
  <xsl:param name="cidr"/>
  <xsl:param name="action"/>
  <xsl:variable name="ipaddr" select="substring-before($cidr,'/')"/>
  <xsl:variable name="mask" select="substring-after($cidr,'/')"/>

  <xsl:if test="$cidr != ''">
    <xsl:value-of select="concat('ACL ',$action,' ',$ipaddr,'/')"/>
    <xsl:choose>
      <xsl:when test="contains($mask,'.')">
        <xsl:value-of select="$mask"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="bitstomask">
         <xsl:with-param name="bits" select="$mask"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:text> - *&#xa;</xsl:text>
  </xsl:if>
</xsl:template>

<xsl:template name="getaccess">
  <xsl:choose>
    <xsl:when test="count(/config/Proxy/SquidAccess/Access) != 0">
      <xsl:apply-templates select="/config/Proxy/SquidAccess/Access[. = 'false']"/>
      <xsl:text>ACL Allow 127.0.0.1/255.255.255.255 - *&#xa;</xsl:text>
      <xsl:apply-templates select="/config/Proxy/SquidAccess/Access[. = 'true']"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>ACL Allow 127.0.0.1/255.255.255.255 - *&#xa;</xsl:text>
      <xsl:apply-templates select="/config/IP/Interfaces/Interface[((. != $extiface) or ($extcon = 'ADSL')) and
                                     (@ipaddr != '0.0.0.0') and (@subnet != '32')]"/>
      <xsl:apply-templates select="/config/IP/GRE/Tunnels/Tunnel"/>
      <xsl:apply-templates select="/config/IP/Routes/Route"/>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:apply-templates select="/config/IP/WiFi[@type = 'Hotspot']"/>
  <xsl:apply-templates select="/config/Radius/RAS/Modem"/>

  <xsl:apply-templates select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32')]">
    <xsl:with-param name="netmask" select="'255.255.255.255'"/>
    <xsl:with-param name="useipaddr" select="1"/>
  </xsl:apply-templates>

  <xsl:apply-templates select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:with-param name="useipaddr" select="1"/>
  </xsl:apply-templates>

  <xsl:call-template name="cidraccess">
    <xsl:with-param name="cidr" select="/config/Radius/Config/Option[@option = 'PPPoE']"/>
    <xsl:with-param name="action" select="'Allow'"/>
  </xsl:call-template>

  <xsl:call-template name="cidraccess">
    <xsl:with-param name="cidr" select="/config/IP/SysConf/Option[@option = 'L2TPNet']"/>
    <xsl:with-param name="action" select="'Allow'"/>
  </xsl:call-template>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>Port 2121
ResolvLoadHack wontresolve.doesntexist.abc
LogFile /var/log/frox
WorkingDir /var/spool/frox
PidFile /var/run/frox.pid
BounceDefend yes
MaxForks 10
MaxForksPerHost 4
CacheModule http
HTTPProxy 127.0.0.1:3128
MinCacheSize 4096
User nobody
Group nogroup
CacheOnFQDN yes
StrictCaching yes
</xsl:text>
  <xsl:call-template name="getaccess"/>

</xsl:template>
</xsl:stylesheet>
