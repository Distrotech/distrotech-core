<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="Interface">
  <xsl:value-of select="concat('push &quot;route ',@nwaddr,' ',@netmask,'&quot;',$nl)"/>
</xsl:template>

<xsl:template name="validconf">
  <xsl:value-of select="concat('server ',substring-before(/config/IP/SysConf/Option[@option = 'OVPNNet'],'/'),' ',$netmask,$nl)"/>
  <xsl:text>dev vpn0
dev-type tun
dh /etc/openvpn/serverdh.pem
ca /etc/ipsec.d/cacerts/cacert.pem
crl-verify /etc/ipsec.d/crls/crl.pem
cert /etc/openssl/server.signed.pem
key /etc/openssl/serverkey.pem
proto tcp-server
keepalive 10 60
script-security 2
daemon
up /etc/openvpn/updev
client-connect /etc/openvpn/client-connect
client-disconnect /etc/openvpn/client-disconnect
</xsl:text>
  <xsl:apply-templates select="/config/IP/Interfaces/Interface[@ovpn = '1']"/>
<xsl:text>push "route 172.16.0.0 255.240.0.0"
push "route 192.168.0.0 255.255.0.0"
push "route 10.0.0.0 255.0.0.0"
</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:if test="(/config/IP/SysConf/Option[@option = 'OVPNNet'] != '')">
    <xsl:call-template name="validconf"/>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>

