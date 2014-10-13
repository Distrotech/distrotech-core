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
  <xsl:text>type=peer&#xa;</xsl:text>
  <xsl:value-of select="concat('secret=',@password,$nl)"/>
  <xsl:value-of select="concat('defaultuser=',.,$nl)"/>
  <xsl:value-of select="concat(';fromuser=',.,$nl)"/>
  <xsl:value-of select="concat('fromdomain=',@addr,$nl)"/>
  <xsl:value-of select="concat('host=',@addr,$nl)"/>
  <xsl:text>context=sipddi&#xa;</xsl:text>
  <xsl:text>qualify=yes&#xa;</xsl:text>
  <xsl:text>regexten=</xsl:text>
  <xsl:choose>
    <xsl:when test="@ext != ''">
      <xsl:value-of select="concat(@ext,$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat(.,$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>directmedia=no&#xa;</xsl:text>
  <xsl:text>nat=yes&#xa;</xsl:text>
  <xsl:text>insecure=port,invite&#xa;</xsl:text>
  <xsl:text>sendrpid=yes&#xa;</xsl:text>
  <xsl:text>trustrpid=yes&#xa;</xsl:text>
</xsl:template>

<xsl:template name="registerpeer">
  <xsl:for-each select="/config/IP/VOIP/SIP/Peers[text() != '']">
    <xsl:choose>
      <xsl:when test="@password != ''">
        <xsl:value-of select="concat('register=>',.,':',@password,'@',@addr,'/')"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat('register=>',.,'@',@addr,'/')"/>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:choose>
      <xsl:when test="@ext != ''">
        <xsl:value-of select="concat(@ext,$nl)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat(.,$nl)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:for-each>
</xsl:template>

<xsl:template match="VOIP">
  <xsl:if test="(@register = 'true') and (@username != '')">
    <xsl:choose>
      <xsl:when test="@secret != ''">
        <xsl:value-of select="concat('register=>',@username,':',@secret,'@',@server,'/',@username,$nl)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat('register=>',@username,'@',@server,'/',@username,$nl)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:if>
  <xsl:call-template name="registerpeer"/>

  <xsl:text>
[parent]
type=peer
</xsl:text>

  <xsl:if test="@username != ''">
    <xsl:if test="@secret != ''">
      <xsl:value-of select="concat('secret=',@secret,$nl)"/>
    </xsl:if>
    <xsl:value-of select="concat('defaultuser=',@username,$nl)"/>
    <xsl:if test="@fromuser = 'true'">
      <xsl:value-of select="concat('fromuser=',@username,$nl)"/>
    </xsl:if>
  </xsl:if>
  <xsl:value-of select="concat('fromdomain=',@server,$nl)"/>
  <xsl:value-of select="concat('host=',@server,$nl)"/>

  <xsl:text>context=sipddi
nat=yes
qualify=yes
directmedia=no
insecure=port,invite
sendrpid=yes
trustrpid=yes
t38pt_udptl=yes
t38pt_rtp=yes
</xsl:text>
  <xsl:value-of select="concat('dtmfmode=',@dtmf,$nl)"/>

  <xsl:choose>
    <xsl:when test="@srtp = 'true'">
      <xsl:text>encryption=yes&#xa;</xsl:text>
   </xsl:when>
   <xsl:otherwise>
      <xsl:text>encryption=no&#xa;</xsl:text>
   </xsl:otherwise>
  </xsl:choose>

  <xsl:choose>
    <xsl:when test="@novideo = 'true'">
      <xsl:text>videosupport=no&#xa;</xsl:text>
   </xsl:when>
   <xsl:otherwise>
      <xsl:text>videosupport=yes&#xa;</xsl:text>
   </xsl:otherwise>
  </xsl:choose>
  <xsl:text>t38pt_usertpsource=yes
</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>[general]
context=ddi
port=5060
srvlookup=yes
bindaddr=::
tcpbindaddr=::
dbuser=asterisk
dbpass=</xsl:text><xsl:value-of select="/config/SQL/Option[@option = 'Asterisk']"/><xsl:text>
dbhost=</xsl:text><xsl:value-of select="/config/SQL/Option[@option = 'AsteriskServ']"/><xsl:text>
dbname=asterisk
nat=yes
disallow=all
rtcachefriends=yes
vmexten=100
rtautoclear=yes
subscribecontext=busyline
notifyringing=yes
notifyhold=no
limitonpeer=yes
limitpeersonly=yes
videosupport=yes
;tos_sip=lowdelay
;tos_audio=lowdelay
;tos_video=lowdelay
mohinterpret=default
mohsuggest=default
encryption=try
tcpenable=yes
transport=udp,tcp,tls
t38pt_udptl=yes,redundancy
callcounter=yes
faxdetect=no
t38pt_rtp=yes
t38pt_tcp=no
rtptimeout=60
rtpholdtimeout=300
alwaysauthreject=yes
prematuremedia=no
insecure=no
</xsl:text>

  <xsl:choose>
    <xsl:when test="$usetls = '1'">
      <xsl:text>tlscertfile=/etc/openssl/voipca/server.pem&#xa;</xsl:text>
      <xsl:text>tlsprivatekey=/etc/openssl/voipca/private/serverkey.pem&#xa;</xsl:text>
      <xsl:text>tlscapath=/etc/openssl/voipca/cacerts&#xa;</xsl:text>
      <xsl:text>tlsenable=yes&#xa;</xsl:text>
      <xsl:text>tlsbindaddr=[::]:5061&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>tlsenable=no&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:if test="$useg729 = '1'">
    <xsl:text>allow=g729&#xa;</xsl:text>
  </xsl:if>
<xsl:text>allow=gsm
allow=speex
allow=ilbc
allow=g726
</xsl:text>
  <xsl:if test="$useg723 = '1'">
    <xsl:text>allow=g723.1&#xa;</xsl:text>
  </xsl:if>
<xsl:text>allow=alaw
allow=ulaw
allow=h263p
allow=h263
allow=h261

</xsl:text>

  <xsl:choose>
    <xsl:when test="/config/IP/VOIP[(@protocol = 'SIP') and (@server != '')]">
      <xsl:apply-templates select="/config/IP/VOIP"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="registerpeer"/>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:apply-templates select="/config/IP/VOIP/SIP/Peers"/>

  <xsl:text>
[guest]
insecure=invite
type=user
context=ddi
</xsl:text>
</xsl:template>
</xsl:stylesheet>
