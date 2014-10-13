<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="fqdn" select="concat(/config/DNS/Config/Option[@option = 'Hostname'],'.',/config/DNS/Config/Option[@option = 'Domain'])"/>
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>

<xsl:template match="/config">HOME                   = .
RANDFILE               = $ENV::HOME/.rnd

[ req ]
default_bits           = 2048
distinguished_name     = req_dn
prompt                 = no
encrypt_key            = no
req_extensions         = v3_req
default_md             = sha1

[ req_dn ]
C                      = <xsl:value-of select="/config/X509/Option[@option = 'Country']"/>
ST                     = <xsl:value-of select="/config/X509/Option[@option = 'State']"/>
L                      = <xsl:value-of select="/config/X509/Option[@option = 'City']"/>
O                      = <xsl:value-of select="/config/X509/Option[@option = 'Company']"/>
OU                     = <xsl:value-of select="/config/X509/Option[@option = 'Division']"/>
CN                     = Network Sentry VOIP CA Of <xsl:value-of select="/config/X509/Option[@option = 'Company']"/>

[ v3_req ]
nsComment              = "Generated On Network Sentinel Solutions Firewall"
subjectAltName         = DNS:<xsl:value-of select="$fqdn"/>,email:root@<xsl:value-of select="$fqdn"/>,IP:<xsl:value-of select="$intip"/>
nsSslServerName        = <xsl:value-of select="$fqdn"/>
extendedKeyUsage       = 1.3.6.1.5.5.7.3.2
extendedKeyUsage       = 1.3.6.1.5.5.7.3.1
<xsl:text></xsl:text>
</xsl:template>
</xsl:stylesheet>
