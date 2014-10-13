<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>

<xsl:template name="setgkid">
  <xsl:choose>
    <xsl:when test="/config/IP/Voip/@gkid != ''">
      <xsl:value-of select="/config/IP/Voip/@gkid"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="/config/DNS/Config/Option[@option = 'Hostname']"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="/config">
<xsl:text>[general]
port=1721   
;bindaddr=::
tos=lowdelay 
amaflags = default
;accountcode=h3230101
disallow=all
allow=g723 
allow=g729 
allow=gsm  
allow=ulaw
allow=alaw
t38support=yes
faxdetect=no
dtmfmode=rfc2833
gatekeeper=</xsl:text><xsl:value-of select="$intip"/><xsl:text>
h323id=</xsl:text><xsl:call-template name="setgkid"/><xsl:text>
gateway=yes
faststart=no
h245tunneling=yes   
mediawaitforconnect=yes
;e164=100
;callerid=asterisk
context=h323 
rtptimeout=60
;t35country=9F
;manufacturer=Distrotech Solutions PTY (LTD)
;vendorid=Asterisk

</xsl:text>
</xsl:template>
</xsl:stylesheet>
