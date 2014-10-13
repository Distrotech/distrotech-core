<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template name="setapn">
  <xsl:choose>
    <xsl:when test="/config/IP/Dialup/Option[@option = 'Number'] = ''">
      <xsl:text>internet</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="/config/IP/Dialup/Option[@option = 'Number']"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="apn3gipw">
  <xsl:text>AT+CGDCONT=1,"PPP","</xsl:text>
  <xsl:call-template name="setapn"/>
  <xsl:text>"</xsl:text>
  <xsl:if test="(/config/IP/Dialup/Option[@option = 'Username'] != '') and
                (/config/IP/Dialup/Option[@option = 'Password'] != '')">
    <xsl:text>,"</xsl:text>
    <xsl:value-of select="concat(/config/IP/Dialup/Option[@option = 'Username'],',',/config/IP/Dialup/Option[@option = 'Password'])"/>
    <xsl:text>",0,0</xsl:text>
  </xsl:if>
</xsl:template>

<xsl:template name="apn3g">
  <xsl:text>AT+CGDCONT=1,"IP","</xsl:text>
  <xsl:call-template name="setapn"/>
  <xsl:text>"</xsl:text>
</xsl:template>

<xsl:template name="config3g">
  <xsl:text>OK </xsl:text>
  <xsl:choose>
    <xsl:when test="/config/IP/Dialup/Option[@option = 'Connection'] = '3G'">
      <xsl:call-template name="apn3g"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="apn3gipw"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template name="getnum">
  <xsl:choose>
    <xsl:when test="(/config/IP/Dialup/Option[@option = 'Connection'] = '3G') or
                    (/config/IP/Dialup/Option[@option = 'Connection'] = '3GIPW')">
      <xsl:text>*99#</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:if test="/config/IP/Dialup/Option[@option = 'Number'] != 'true'">
        <xsl:value-of select="/config/IP/Dialup/Option[@option = 'Number']"/>
      </xsl:if>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="/config">
  <xsl:if test="/config/IP/Dialup/Option[@option = 'NoCarrier'] = 'true'">
    <xsl:text>ABORT 'NO CARRIER'&#xa;</xsl:text>
  </xsl:if>

  <xsl:if test="/config/IP/Dialup/Option[@option = 'NoDialtone'] = 'true'">
    <xsl:text>ABORT 'NO DIALTONE'&#xa;</xsl:text>
  </xsl:if>

  <xsl:if test="/config/IP/Dialup/Option[@option = 'Busy'] = 'true'">
    <xsl:text>ABORT 'BUSY'&#xa;</xsl:text>
  </xsl:if>

  <xsl:if test="/config/IP/Dialup/Option[@option = 'Error'] = 'true'">
    <xsl:text>ABORT 'ERROR'&#xa;</xsl:text>
  </xsl:if>

  <xsl:text>'' </xsl:text>
  <xsl:choose>
    <xsl:when test="/config/IP/Dialup/Option[@option = 'Init1'] != ''">
      <xsl:value-of select="/config/IP/Dialup/Option[@option = 'Init1']"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>ATZ</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>&#xa;</xsl:text>

  <xsl:if test="/config/IP/Dialup/Option[@option = 'Init2'] != ''">
    <xsl:text>OK </xsl:text>
    <xsl:value-of select="/config/IP/Dialup/Option[@option = 'Init2']"/>
    <xsl:text>&#xa;</xsl:text>
  </xsl:if>

  <xsl:if test="(/config/IP/Dialup/Option[@option = 'Connection'] = '3G') or
                (/config/IP/Dialup/Option[@option = 'Connection'] = '3GIPW')">
    <xsl:call-template name="config3g"/>
  </xsl:if>

  <xsl:text>OK </xsl:text>
  <xsl:choose>
    <xsl:when test="/config/IP/Dialup/Option[@option = 'DialString'] != ''">
      <xsl:value-of select="/config/IP/Dialup/Option[@option = 'DialString']"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>ATD</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:call-template name="getnum"/>
  <xsl:text>&#xa;</xsl:text>

  <xsl:text>CONNECT&#xa;</xsl:text>
</xsl:template>
</xsl:stylesheet>
