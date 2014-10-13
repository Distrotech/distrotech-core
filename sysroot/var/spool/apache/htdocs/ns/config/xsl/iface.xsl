<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>

<xsl:template name="showtable">
  <xsl:param name="path" />
  <xsl:param name="heading" />
  <tr>
    <th><xsl:value-of select="$heading"/>
    </th>
  </tr><xsl:value-of select="$nl"/>
  <tr width="100%"><td width="100%">
    <table border="1" cellpadding="0" cellspacing="0" width="100%">
      <xsl:apply-templates select="$path"/>
    </table>
  </td></tr><xsl:value-of select="$nl"/>
</xsl:template>

<xsl:template name="getifname">
  <xsl:param name="iface"/>
  <xsl:param name="ifname"/>
  <xsl:for-each select="/config/IP/Interfaces/Interface[.=$iface]">
    <xsl:value-of select="@name" />
  </xsl:for-each>
</xsl:template>

<xsl:template match="Option">
  <xsl:value-of select="." />
</xsl:template>

<xsl:template match="Host">
  <xsl:if test="position()=1">
    <tr bgcolor="#9acd32">
      <th>Host</th>
      <th>IP Addr</th>
      <th>MAC Addr</th>
    </tr><xsl:value-of select="$nl"/>
  </xsl:if>
  <tr>
    <td><xsl:value-of select="." /></td>
    <td><xsl:value-of select="@ipaddr" /></td>
    <td>
      <xsl:choose>
        <xsl:when test="@macaddr=''">
          <br></br>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="@macaddr"/>
        </xsl:otherwise>
      </xsl:choose>
    </td>
  </tr>
</xsl:template>

<xsl:template match="Dialup">
  <tr bgcolor="#9acd32">
    <th>Type</th>
    <th>Service/Number</th>
    <th>Username</th>
    <th>Password</th>
    <th>MTU</th>
  </tr><xsl:value-of select="$nl"/>
  <tr>
    <td>
      <xsl:apply-templates select="Option[@option='Connection']"/>
    </td>
    <td>
      <xsl:apply-templates select="Option[@option='Number']"/>
    </td>
    <td>
      <xsl:apply-templates select="Option[@option='Username']"/>
    </td>
    <td>
      <xsl:apply-templates select="Option[@option='Password']"/>
    </td>
    <td>
      <xsl:apply-templates select="Option[@option='MTU']"/>
    </td>
  </tr>
</xsl:template>

<xsl:template match="WiFi">
  <xsl:if test="position()=1">
    <tr bgcolor="#9acd32">
      <th>SSID</th>
      <th>Interface</th>
      <th>Type</th>
      <th>Mode</th>
      <th>Auth</th>
      <th>Channel</th>
      <th>Power</th>
      <th>Key</th>
    </tr><xsl:value-of select="$nl"/>
  </xsl:if>
  <tr>
    <td>
      <xsl:call-template name="getifname">
        <xsl:with-param name="iface" select="." />
        <xsl:with-param name="ifname"/>
      </xsl:call-template>
    </td>
    <td><xsl:value-of select="." /></td>
    <td><xsl:value-of select="@type" /></td>
    <td><xsl:value-of select="@mode" /></td>
    <td><xsl:value-of select="@auth" /></td>
    <td><xsl:value-of select="@channel" /></td>
    <td><xsl:value-of select="@txpower" /></td>
    <td>
      <xsl:choose>
        <xsl:when test="@key=''">
          <br></br>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="@key"/>
        </xsl:otherwise>
      </xsl:choose>
    </td>
  </tr><xsl:value-of select="$nl"/>
</xsl:template>

<xsl:template match="Interface">
  <xsl:if test="position()=1">
    <tr bgcolor="#9acd32">
      <th>Name</th>
      <th>Interface</th>
      <th>Macaddr</th>
      <th>IP addr</th>
      <th>SN</th>
      <th>DHCP Start</th>
      <th>DHCP End</th>
      <th>DHCP GW</th>
      <th>BW In</th>
      <th>BW Out</th>
    </tr><xsl:value-of select="$nl"/>
  </xsl:if>
  <tr>
    <td><xsl:value-of select="@name" /></td>
    <td><xsl:value-of select="text()" /></td>
    <td><xsl:value-of select="@macaddr" /></td>
    <td><xsl:value-of select="@ipaddr" /></td>
    <td><xsl:value-of select="@subnet" /></td>
    <td>
      <xsl:choose>
        <xsl:when test="@dhcpstart='-'">
          <br></br>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="@dhcpstart"/>
        </xsl:otherwise>
      </xsl:choose>
    </td>
    <td>
      <xsl:choose>
        <xsl:when test="@dhcpend='-'">
          <br></br>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="@dhcpend"/>
        </xsl:otherwise>
      </xsl:choose>
    </td>
    <td>
      <xsl:choose>
        <xsl:when test="@gateway=''">
          <br></br>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="@gateway"/>
        </xsl:otherwise>
      </xsl:choose>
    </td>
    <td>
      <xsl:choose>
        <xsl:when test="@bwin=''">
          <br></br>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="@bwin"/>
        </xsl:otherwise>
      </xsl:choose>
    </td>
    <td>
      <xsl:choose>
        <xsl:when test="@bwout=''">
          <br></br>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="@bwout"/>
        </xsl:otherwise>
      </xsl:choose>
    </td>
  </tr><xsl:value-of select="$nl"/>
</xsl:template>

<xsl:template match="IP">
  <xsl:if test="Interfaces/Interface">
    <xsl:call-template name="showtable">
      <xsl:with-param name="heading" select="'Interfaces'"/>
      <xsl:with-param name="path" select="Interfaces/Interface" />
    </xsl:call-template>
  </xsl:if>
  <xsl:if test="WiFi">
    <xsl:call-template name="showtable">
      <xsl:with-param name="heading" select="'Wi-Fi Interfaces'"/>
      <xsl:with-param name="path" select="WiFi" />
    </xsl:call-template>
  </xsl:if>
  <xsl:if test="Dialup">
    <xsl:call-template name="showtable">
      <xsl:with-param name="heading" select="'Connection (DSL/3G/..)'"/>
      <xsl:with-param name="path" select="Dialup" />
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template match="DNS">
  <xsl:if test="Hosts/Host">
    <xsl:call-template name="showtable">
      <xsl:with-param name="heading" select="'Host Declerations'"/>
      <xsl:with-param name="path" select="Hosts/Host" />
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template match="/config">
  <table border="1" cellpadding="0" cellspacing="0" width="90%">
    <xsl:apply-templates select="IP"/>
    <xsl:apply-templates select="DNS"/>
  </table>
</xsl:template>
</xsl:stylesheet>
