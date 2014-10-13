<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="/config">
  <xsl:text>[server]
allow-interfaces=</xsl:text><xsl:value-of select="/config/IP/SysConf/Option[@option = 'Internal']"/><xsl:text>
use-ipv4=yes
use-ipv6=yes

[reflector]
enable-reflector=yes

[publish]
add-service-cookie=yes
publish-a-on-ipv6=yes
</xsl:text>
</xsl:template>
</xsl:stylesheet>
