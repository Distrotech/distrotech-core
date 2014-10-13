<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="/config">[general]
format=wav49
serveremail=asterisk
attach=yes
maxsecs=180
minsecs=2
maxgreet=60
skipms=3000
maxsilence=3
silencethreshold=128
maxlogins=3
sendvoicemail=yes
mailcmd=/usr/sbin/sendmail -f asterisk@<xsl:value-of select="/config/DNS/Config/Option[@option = 'Domain']"/> -t
dbuser=asterisk
externpass=/usr/sbin/voippass
operator=yes
dbpass=<xsl:value-of select="/config/SQL/Option[@option = 'Asterisk']"/>
dbhost=<xsl:value-of select="/config/SQL/Option[@option = 'AsteriskServ']"/>
dbname=asterisk
imapfolder=INBOX
imapserver=127.0.0.1
authuser=asterisk
authpassword=password
imapflags=novalidate-cert

[zonemessages]
eastern=America/New_York|'vm-received' Q 'digits/at' IMp
central=America/Chicago|'vm-received' Q 'digits/at' IMp
central24=America/Chicago|'vm-received' q 'digits/at' H 'digits/hundred' M 'hours'

</xsl:template>
</xsl:stylesheet>
