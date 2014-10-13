<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="/config">
  <xsl:text>#!/bin/bash

#
#Update Cover Page
#

sed -e "s/XXXCNAMEXXX/</xsl:text><xsl:value-of select="/config/IP/Fax/Option[@option='TagName']"/><xsl:text>/ " /usr/lib/fax/faxcover.ps.in > /usr/lib/fax/faxcover.ps

if [ ! -d /var/spool/samba/share/fax-software ];then
  mkdir -p /var/spool/samba/share/fax-software
fi;

cp /usr/lib/fax/faxcover.ps /var/spool/samba/share/fax-software
rm /var/spool/hylafax/etc/config.*

/usr/sbin/hfaxd -i hylafax
/usr/sbin/faxq

</xsl:text>
</xsl:template>
</xsl:stylesheet>
