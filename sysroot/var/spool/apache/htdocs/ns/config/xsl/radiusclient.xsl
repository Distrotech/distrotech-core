<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="/config">auth_order	radius
login_tries	4
login_timeout	60
nologin		/etc/nologin
issue		/etc/issue
authserver	<xsl:value-of select="/config/Radius/Config/Option[@option='Server']"/>:<xsl:value-of select="/config/Radius/Config/Option[@option='AuthPort']"/>
acctserver	<xsl:value-of select="/config/Radius/Config/Option[@option='Server']"/>:<xsl:value-of select="/config/Radius/Config/Option[@option='AccPort']"/>
servers		/etc/radiusclient/servers
dictionary	/etc/radiusclient/dictionary
login_radius	/usr/sbin/login.radius
seqfile		/var/run/radius.seq
mapfile		/etc/radiusclient/port-id-map
default_realm
radius_timeout	10
radius_retries	3
login_local	/bin/login
</xsl:template>
</xsl:stylesheet>
