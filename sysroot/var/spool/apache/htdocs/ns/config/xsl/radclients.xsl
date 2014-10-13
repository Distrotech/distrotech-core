<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="fqdn" select="concat(/config/DNS/Config/Option[@option = 'Hostname'],'.',/config/DNS/Config/Option[@option = 'Domain'])"/>
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>
<xsl:variable name="radsec" select="/config/Radius/Config/Option[@option='Secret']"/>

<xsl:template match="Interface">client <xsl:value-of select="@ipaddr"/> {
	secret      = <xsl:value-of select="$radsec"/>
	proto       = *
	shortname   = <xsl:value-of select="@name"/>
	nastype     = other
}

</xsl:template>

<xsl:template match="Client">client <xsl:value-of select="@hostname"/> {
	secret      = <xsl:value-of select="@secret"/>
	proto       = *
	shortname   = <xsl:value-of select="."/>
	nastype     = other
}

</xsl:template>

<xsl:template match="WiFi">
  <xsl:variable name="iname" select="."/>
  <xsl:apply-templates select="/config/IP/Interfaces/Interface[. = $iname]"/>
</xsl:template>

<xsl:template match="/config">client localhost-v4 {
	ipaddr      = 127.0.0.1
	proto       = *
	secret      = <xsl:value-of select="$radsec"/>
	nastype     = other
}

client localhost-v6 {
	ipv6addr    = ::1
	proto       = *
	secret      = <xsl:value-of select="$radsec"/>
	nastype     = other
}

client localip-v4 {
	ipaddr      = <xsl:value-of select="$intip"/>
	proto       = *
	secret      = <xsl:value-of select="$radsec"/>
	nastype     = other
}

<xsl:apply-templates select="/config/IP/WiFi[@type = 'Hotspot']"/>
<xsl:apply-templates select="/config/Radius/Clients/Client"/>
</xsl:template>
</xsl:stylesheet>
