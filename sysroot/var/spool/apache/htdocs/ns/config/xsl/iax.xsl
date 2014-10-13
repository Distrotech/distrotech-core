<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="Peers">
  <xsl:text>&#xa;</xsl:text>
  <xsl:choose>
    <xsl:when test="@context != ''">
      <xsl:value-of select="concat('[',@context,']',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('[',.,']',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>type=friend&#xa;</xsl:text>
  <xsl:value-of select="concat('auth=',@auth,$nl)"/>
  <xsl:text>context=ddi&#xa;</xsl:text>
  <xsl:value-of select="concat('secret=',@password,$nl)"/>
  <xsl:value-of select="concat('username=',.,$nl)"/>
  <xsl:value-of select="concat('host=',@addr,$nl)"/>
  <xsl:text>qualify=yes&#xa;</xsl:text>
  <xsl:text>trunk=yes&#xa;</xsl:text>
</xsl:template>

<xsl:template name="registerpeer">
  <xsl:for-each select="/config/IP/VOIP/IAX/Peers[text() != '']">
    <xsl:choose>
      <xsl:when test="@password != ''">
        <xsl:value-of select="concat('register=>',.,':',@password,'@',@addr,$nl)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat('register=>',.,'@',@addr,$nl)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:for-each>
</xsl:template>

<xsl:template match="VOIP">
  <xsl:text>&#xa;</xsl:text>

  <xsl:if test="(@register = 'true') and (@username != '')">
    <xsl:choose>
      <xsl:when test="@secret != ''">
        <xsl:value-of select="concat('register=>',@username,':',@secret,'@',@server,$nl)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat('register=>',@username,'@',@server,$nl)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:if>
  <xsl:call-template name="registerpeer"/>

  <xsl:text>
[parent]
type=friend
auth=md5,plaintext</xsl:text>

  <xsl:if test="@username != ''">
    <xsl:value-of select="concat($nl,'username=',@username)"/>
    <xsl:if test="@secret != ''">
      <xsl:value-of select="concat($nl,'secret=',@secret)"/>
    </xsl:if>
  </xsl:if>
  <xsl:value-of select="concat($nl,'host=',@server)"/>

  <xsl:text>
qualify=yes
trunk=yes
context=ddi
</xsl:text>
  <xsl:if test="@username != ''">
    <xsl:value-of select="concat($nl,'[',@username,']',$nl)"/>
    <xsl:text>type=user&#xa;</xsl:text>
    <xsl:text>auth=md5,plaintext&#xa;</xsl:text>
    <xsl:value-of select="concat('username=',@username,$nl)"/>
    <xsl:if test="@secret != ''">
      <xsl:value-of select="concat('secret=',@secret,$nl)"/>
    </xsl:if>
    <xsl:text>context=ddi&#xa;</xsl:text>
    <xsl:text>trunk=yes&#xa;</xsl:text>
  </xsl:if>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>[general]
bindport=4569
bindaddr=0.0.0.0
;tos=none
bandwidth=low
dbuser=asterisk
dbpass=</xsl:text><xsl:value-of select="/config/SQL/Option[@option = 'Asterisk']"/><xsl:text>
dbhost=</xsl:text><xsl:value-of select="/config/SQL/Option[@option = 'AsteriskServ']"/><xsl:text>
dbname=asterisk
jitterbuffer=yes
transfer=mediaonly
trunkfreq=20
trunktimestamps=yes
iaxcompat=no
disallow=all
codecpriority=host
mohinterpret=default
mohsuggest=default
iaxthreadcount=16
iaxmaxthreadcount=512 
rtcachefriends=yes
calltokenoptional = 0.0.0.0/0.0.0.0
</xsl:text>
  <xsl:if test="$useg723 = '1'">
    <xsl:text>allow=g723.1&#xa;</xsl:text>
  </xsl:if>
  <xsl:if test="$useg729 = '1'">
    <xsl:text>allow=g729&#xa;</xsl:text>
  </xsl:if>
<xsl:text>allow=gsm
allow=speex
allow=ilbc
allow=g726
allow=alaw
allow=ulaw
allow=h263p
allow=h263
allow=h261

</xsl:text>
  <xsl:if test="$haslocal = '1'">
    <xsl:text>#include /etc/asterisk/iax.conf.local&#xa;</xsl:text>
  </xsl:if>

  <xsl:choose>
    <xsl:when test="/config/IP/VOIP/@protocol = 'IAX'">
      <xsl:apply-templates select="/config/IP/VOIP"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="registerpeer"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:apply-templates select="/config/IP/VOIP/IAX/Peers"/>

  <xsl:text>
[default]
requirecalltoken=no
type=user
auth=plaintext
context=ddi
trunk=yes

[guest]
requirecalltoken=no
type=user
auth=plaintext
context=ddi
trunk=yes
</xsl:text>
</xsl:template>
</xsl:stylesheet>
