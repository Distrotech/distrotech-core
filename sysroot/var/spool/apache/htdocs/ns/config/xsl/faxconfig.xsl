<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="/config">
  <xsl:text>LogFacility:		daemon
CountryCode:		</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'Country']"/><xsl:text>
AreaCode:		</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'AreaCode']"/><xsl:text>
LongDistancePrefix:	</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'LongDistPrefix']"/><xsl:text>
InternationalPrefix:	</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'InatPrefix']"/><xsl:text>
DialStringRules:	etc/dialrules
ServerTracing:		1</xsl:text>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>
</xsl:stylesheet>
