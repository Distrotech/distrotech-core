<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="extiface" select="/config/IP/SysConf/Option[@option = 'External']"/>

<xsl:template match="/config"> 
  <xsl:apply-templates select="/config/IP/Interfaces/Interface[. = $iface]"/>
  <xsl:call-template name="getroutes"/>
  <xsl:call-template name="getvlan"/>
  <xsl:call-template name="getalias"/>
  <xsl:apply-templates select="/config/IP/SysConf/Option[@option='Internal']"/>
  <xsl:call-template name="getwifi"/>
  <xsl:call-template name="getlinksetup"/>
  <xsl:choose>
    <xsl:when test="(/config/IP/Dialup/Option[@option='Connection'] = 'ADSL') and 
                    (/config/IP/SysConf/Option[@option='External'] = $iface)">
      <xsl:text>RP_FIL=1;&#xa;</xsl:text>
      <xsl:call-template name="getbwlim"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="getrpfil"/>
      <xsl:if test="count(/config/IP/SysConf/Option[((@option='Internal') and (.=$iface)) or ((@option='External') and (.=$iface))]) = 0">
        <xsl:call-template name="getbwlim"/>
      </xsl:if>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Interface">
  <xsl:text>IFNAME="</xsl:text><xsl:value-of select="$iface"/><xsl:text>";&#xa;</xsl:text>
  <xsl:text>MADDR=</xsl:text><xsl:value-of select="@macaddr"/><xsl:text>;&#xa;</xsl:text>
  <xsl:text>ADDRESS="</xsl:text><xsl:value-of select="@ipaddr"/><xsl:text>";&#xa;</xsl:text>
  <xsl:text>NETMASK="</xsl:text><xsl:value-of select="@subnet"/><xsl:text>";&#xa;</xsl:text>
  <xsl:text>BROADCAST="</xsl:text><xsl:value-of select="@bcaddr"/><xsl:text>";&#xa;</xsl:text>
  <xsl:text>NETWORK="</xsl:text><xsl:value-of select="@nwaddr"/><xsl:text>";&#xa;</xsl:text>
  <xsl:if test="$iface = $extiface">
    <xsl:apply-templates select="/config/IP/SysConf/Option[@option='Nexthop']"/>
  </xsl:if>
</xsl:template>

<xsl:template match="Option[@option='Bridge']">
  <xsl:if test="contains(.,$iface)">
    <xsl:text>BRIDGE="</xsl:text>
    <xsl:value-of select="$intiface"/>
    <xsl:text>";&#xa;</xsl:text>
  </xsl:if>
</xsl:template>

<xsl:template match="Option[@option='Nexthop']">
  <xsl:text>DEFAULT="</xsl:text>
    <xsl:value-of select="."/>
  <xsl:text>";&#xa;</xsl:text>
</xsl:template>

<xsl:template name="getroutes">
  <xsl:variable name="rtcnt" select="count(/config/IP/*/Route[@iface = $iface])"/>
  <xsl:for-each select="/config/IP/*/Route[@iface = $iface]">
    <xsl:choose>
      <xsl:when test="position() = 1">
        <xsl:text>ROUTES="</xsl:text>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text> </xsl:text>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:value-of select="concat(@network,'/',@subnet,':',@gateway)"/>
  </xsl:for-each>
  <xsl:if test="$rtcnt > 0">
    <xsl:text>";&#xa;</xsl:text>
  </xsl:if>
</xsl:template>

<xsl:template name="getvlan">
  <xsl:for-each select="/config/IP/Interfaces/Interface[(substring(.,1,string-length($iface)+1)=concat($iface,'.')) and (.!=$iface)]">
    <xsl:choose>
      <xsl:when test="position()=1">
        <xsl:text>VLANS="</xsl:text>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text> </xsl:text>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:value-of select="substring(.,string-length($iface)+2)"/>
  </xsl:for-each>
  <xsl:if test="count(/config/IP/Interfaces/Interface[(substring(.,1,string-length($iface)+1)=concat($iface,'.')) and (.!=$iface)]) > 0">
    <xsl:text>";&#xa;</xsl:text>
  </xsl:if>
</xsl:template>

<xsl:template name="getalias">
  <xsl:for-each select="/config/IP/Interfaces/Interface[(substring(.,1,string-length($iface)+1)=concat($iface,':')) and (.!=$iface)]">
    <xsl:choose>
      <xsl:when test="position()=1">
        <xsl:text>ALIASES="</xsl:text>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text> </xsl:text>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:value-of select="concat(@ipaddr,':',@subnet,':',@nwaddr,':',@bcaddr)"/>
  </xsl:for-each>
  <xsl:if test="count(/config/IP/Interfaces/Interface[(substring(.,1,string-length($iface)+1)=concat($iface,':')) and (.!=$iface)]) > 0">
    <xsl:text>";&#xa;</xsl:text>
  </xsl:if>
</xsl:template>

<xsl:template match="Option[@option='Internal']">
  <xsl:choose>
    <xsl:when test=".!= $iface">
      <xsl:apply-templates select="/config/IP/SysConf/Option[@option='Bridge']"/>
      <xsl:apply-templates select="/config/IP/SysConf/Option[@option='External']"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>FIREWALL="INTERNAL";&#xa;</xsl:text>
      <xsl:text>FORWARD=1;&#xa;</xsl:text>
      <xsl:value-of select="concat('ZCONFIP=&quot;',$zconfip,'&quot;;',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Option[@option='External']">
  <xsl:choose>
    <xsl:when test="(.!=$iface) or (/config/IP/Dialup/Option[@option='Connection'] = 'ADSL')">
      <xsl:text>FIREWALL="OTHER";&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>FIREWALL="EXTERNAL";&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="getwifi">
  <xsl:choose>
    <xsl:when test="count(/config/IP/WiFi[.=$iface]) = 0">
      <xsl:text>CHILLI=0;&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:apply-templates select="/config/IP/WiFi[.=$iface]"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="WiFi">
  <xsl:choose>
    <xsl:when test="@type='Hotspot'">
      <xsl:text>CHILLI=1;&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>CHILLI=0;&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>POWER=</xsl:text><xsl:value-of select="@txpower"/><xsl:text>mW;&#xa;</xsl:text>
  <xsl:text>CHANNEL=</xsl:text><xsl:value-of select="@channel"/><xsl:text>;&#xa;</xsl:text>
  <xsl:text>REGDOM=</xsl:text><xsl:value-of select="@regdom"/><xsl:text>;&#xa;</xsl:text>
</xsl:template>

<xsl:template name="getppp">
  <xsl:param name="links"/>
  <xsl:for-each select="/config/IP/ADSL/Links/Link[@interface=$iface]">
    <xsl:value-of select="@id"/>
    <xsl:if test="position() &lt; $links">
      <xsl:text> </xsl:text>
    </xsl:if>
  </xsl:for-each>
</xsl:template>

<xsl:template name="getmtu">
  <xsl:param name="sysmtu"/>
  <xsl:if test="count(/config/IP/Interfaces/Interface[(substring(.,1,string-length($iface)+1)=concat($iface,'.')) and (.!=$iface)]) > 0">
    <xsl:text>VMTU=</xsl:text>
    <xsl:value-of select="number($sysmtu) - 4"/>
    <xsl:text>;&#xa;</xsl:text>
  </xsl:if>
  <xsl:text>MTU=</xsl:text>
  <xsl:value-of select="$sysmtu"/>
  <xsl:text>;&#xa;</xsl:text>
</xsl:template>

<xsl:template name="getlinksetup">
  <xsl:variable name="pppcnt" select="count(/config/IP/ADSL/Links/Link[@interface=$iface])"/>
  <xsl:variable name="sysmtu" select="/config/IP/Dialup/Option[@option='MTU']"/>
  <xsl:choose>
    <xsl:when test="(/config/IP/Dialup/Option[@option='Connection'] = 'ADSL') and
                    (/config/IP/SysConf/Option[@option='External'] = $iface)">
      <xsl:text>PPPLINKS=0</xsl:text>
      <xsl:if test="$pppcnt > 0">
        <xsl:text> </xsl:text>
        <xsl:call-template name="getppp">
          <xsl:with-param name="links" select="$pppcnt" />
        </xsl:call-template>
      </xsl:if>
      <xsl:text>&#xa;</xsl:text>
      <xsl:call-template name="getmtu">
        <xsl:with-param name="sysmtu" select="$sysmtu" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:choose>
        <xsl:when test="$pppcnt > 0">
          <xsl:text>PPPLINKS=</xsl:text>
          <xsl:call-template name="getppp">
            <xsl:with-param name="links" select="$pppcnt" />
          </xsl:call-template>
          <xsl:text>&#xa;</xsl:text>
          <xsl:call-template name="getmtu">
            <xsl:with-param name="sysmtu" select="$sysmtu" />
          </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
          <xsl:call-template name="getmtu">
            <xsl:with-param name="sysmtu" select="1500" />
          </xsl:call-template>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="getrpfil">
  <xsl:text>RP_FIL=</xsl:text>
  <xsl:choose>
    <xsl:when test="count(/config/IP/SysConf/Option[(@option='External') and (.=$iface)]) = 1">
      <xsl:text>0</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>1</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>;&#xa;</xsl:text>
</xsl:template>

<xsl:template name="getbwlim">
  <xsl:for-each select="/config/IP/Interfaces/Interface[.=$iface]">
    <xsl:text>OLIMIT=</xsl:text><xsl:value-of select="@bwin"/><xsl:text>;&#xa;</xsl:text>
    <xsl:choose>
      <xsl:when test="@bwin = ''">
        <xsl:call-template name="setbwlim">
          <xsl:with-param name="limit" select="'0'" />
          <xsl:with-param name="direction" select="'O'" />
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="setbwlim">
          <xsl:with-param name="limit" select="@bwin" />
          <xsl:with-param name="direction" select="'O'" />
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:text>ILIMIT=</xsl:text><xsl:value-of select="@bwout"/><xsl:text>;&#xa;</xsl:text>
    <xsl:choose>
      <xsl:when test="@bwin = ''">
        <xsl:call-template name="setbwlim">
          <xsl:with-param name="limit" select="'0'" />
          <xsl:with-param name="direction" select="'I'" />
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="setbwlim">
          <xsl:with-param name="limit" select="@bwout" />
          <xsl:with-param name="direction" select="'I'" />
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:for-each>
</xsl:template>

<xsl:template name="setbwlim">
  <xsl:param name="limit"/>
  <xsl:param name="direction"/>
  <xsl:value-of select="$direction"/><xsl:text>LIMITK=</xsl:text>
    <xsl:value-of select="(($limit div 8)*1024)"/><xsl:text>;&#xa;</xsl:text>
  <xsl:value-of select="$direction"/><xsl:text>LIMIT50=</xsl:text>
    <xsl:value-of select="($limit * 0.5)"/><xsl:text>;&#xa;</xsl:text>
  <xsl:value-of select="$direction"/><xsl:text>LIMIT30=</xsl:text>
    <xsl:value-of select="($limit * 0.3)"/><xsl:text>;&#xa;</xsl:text>
  <xsl:value-of select="$direction"/><xsl:text>LIMIT20=</xsl:text>
    <xsl:value-of select="($limit * 0.2)"/><xsl:text>;&#xa;</xsl:text>
</xsl:template>
</xsl:stylesheet>
