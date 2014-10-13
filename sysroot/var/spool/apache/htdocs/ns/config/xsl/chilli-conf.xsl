<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="fqdn" select="concat(/config/DNS/Config/Option[@option = 'Hostname'],'.',/config/DNS/Config/Option[@option = 'Domain'])"/>
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>

<xsl:template match="Interface">dhcpif <xsl:value-of select="."/>
net <xsl:value-of select="@nwaddr"/>/<xsl:value-of select="@subnet"/>
pidfile /var/run/chilli.<xsl:value-of select="."/>
radiuslisten <xsl:value-of select="@ipaddr"/>
radiusnasid <xsl:value-of select="$fqdn"/>
radiusserver1 <xsl:value-of select="@ipaddr"/>
radiusserver2 <xsl:value-of select="$intip"/>
radiusauthport <xsl:value-of select="/config/Radius/Config/Option[@option='AuthPort']"/>
radiusacctport <xsl:value-of select="/config/Radius/Config/Option[@option='AccPort']"/>
radiussecret <xsl:value-of select="/config/Radius/Config/Option[@option='Secret']"/>
uamserver https://<xsl:value-of select="$fqdn"/>/hotspot
uamallowed 192.168.0.0/16
uamallowed 172.16.0.0/12
uamallowed 10.0.0.0/8
dns1 <xsl:value-of select="$intip"/>
domain <xsl:value-of select="/config/DNS/Config/Option[@option = 'Domain']"/>
uamsecret <xsl:value-of select="$uamsecret"/>
eapolenable
ipup /etc/chilli/<xsl:value-of select="."/>.up
<xsl:if test="$uamhome != ''">
  <xsl:value-of select="concat($uamhome,$nl)"/>
</xsl:if>
</xsl:template>

<xsl:template match="/config">
  <xsl:apply-templates select="/config/IP/Interfaces/Interface[ . = $hspot]"/>
</xsl:template>
</xsl:stylesheet>
