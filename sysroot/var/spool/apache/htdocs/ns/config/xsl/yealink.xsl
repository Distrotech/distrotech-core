<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>
<xsl:variable name="fqdn" select="concat($hname,'.',$domain)"/>

<xsl:template match="/config">
  <xsl:text>[ autop_mode ]
path = /config/Setting/autop.cfg
mode = 6
schedule_min = 240

[ cutom_option  ]
path = /config/Setting/autop.cfg
cutom_option_code0 = 250
cutom_option_type0 = 1

[ PNP ]
path = /config/Setting/autop.cfg
Pnp = 0

[ autoprovision ]
path = /config/Setting/autop.cfg
server_address = 

[ account ]
path = /config/voip/sipAccount0.cfg
Enable = 1
SIPServerHost = </xsl:text><xsl:value-of select="$domain"/><xsl:text>
SIPServerPort = 5060
UseOutboundProxy = 0
SubsribeRegister = 1
SubsribeMWI = 1
dialoginfo_callpickup = 1

[ NAT ]
path = /config/voip/sipAccount0.cfg
EnableUDPUpdate = 1

[ Time ]
path = /config/Setting/Setting.cfg
TimeZone = +2
TimeServer1 = </xsl:text><xsl:value-of select="$fqdn"/><xsl:text>
TimeServer2 = </xsl:text><xsl:value-of select="$fqdn"/><xsl:text>
SummerTime = 0
</xsl:text>
</xsl:template>
</xsl:stylesheet>
