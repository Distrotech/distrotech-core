<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="MailBox">
:0 H
* (Envelope-To):.*\&lt;<xsl:value-of select="@address"/>@.*
"/var/spool/mail/shared/.<xsl:value-of select="."/>/"
</xsl:template>

<xsl:template match="/config">MAILDIR=/var/spool/mail/shared/.Administrators/
DEFAULT=$MAILDIR
UMASK=0007
<xsl:apply-templates select="/config/LDAP/PublicMail/MailBox[@address != 'root']"/>
</xsl:template>
</xsl:stylesheet>
