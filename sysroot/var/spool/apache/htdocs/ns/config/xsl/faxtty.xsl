<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="/config">
  <xsl:text># $Id$
#
# HylaFAX configuration for a T38FAX Pseudo Modem
#
# This file was originally sourced with permission from 
# Vyacheslav Frolov's t38modem software in OpenH323 package.
#


#
CountryCode:		</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'Country']"/><xsl:text>
AreaCode:		</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'AreaCode']"/><xsl:text>
FAXNumber:		</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'TagNum']"/><xsl:text>
LongDistancePrefix:	</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'LongDistPrefix']"/><xsl:text>
InternationalPrefix:	</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'InatPrefix']"/><xsl:text>
DialStringRules:	etc/dialrules
ServerTracing:		1
SessionTracing:		11
RecvFileMode:		0600
LogFileMode:		0600
DeviceMode:		0600
RingsBeforeAnswer:	</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'RingDelay']"/><xsl:text>
SpeakerVolume:		off
GettyArgs:		"-h %l dx_%s"
LocalIdentifier:	"</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'TagName']"/><xsl:text>"
TagLineFont:		etc/lutRS18.pcf
TagLineFormat:		"From %%l|%%n|%d/%m/%y %H:%M:%S Page %%P of %%T"
MaxRecvPages:		</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'MaxPages']"/><xsl:text>
JobReqOther:		</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'TimeOut']"/><xsl:text>
NoCarrierRetrys:	</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'Retry']"/><xsl:text>
JobReqBusy:		</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'TimeOut']"/><xsl:text>
JobReqNoCarrier:	</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'TimeOut']"/><xsl:text>
JobReqNoAnswer:		</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'TimeOut']"/><xsl:text>
JobReqDataConn:		</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'TimeOut']"/><xsl:text>
JobReqNoFCon:		</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'TimeOut']"/><xsl:text>
JobReqProto:		</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'TimeOut']"/><xsl:text>
#PercentGoodLines:       90
#MaxConsecutiveBadLines: 10
#QualifyCID:             etc/cid
#CIDNumber:              "</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'CIDNumber']"/><xsl:text>"
#CIDName:                "</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option = 'CIDNumber']"/><xsl:text>"
#

ModemType:		Class1		# use class 1 interface
ModemFlowControl:	rtscts		# default
ModemRevQueryCmd:	AT+FREV?
#ModemDialCmd:		ATDF%s</xsl:text>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>
</xsl:stylesheet>
