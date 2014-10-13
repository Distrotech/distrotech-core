<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="Share">
  <xsl:text>/var/spool/samba/</xsl:text>
  <xsl:value-of select="@folder"/> "<xsl:value-of select="."/>" allow:@<xsl:value-of select="@group"/>
  <xsl:if test="@uread = 'true'">
    <xsl:if test="@group != 'users'">
      <xsl:value-of select="concat(',@',@group)"/>
    </xsl:if>
    <xsl:text> rolist:@users</xsl:text>
  </xsl:if>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>:DEFAULT: options:noadouble,mswindows,usedots</xsl:text>
/var/spool/samba/netlogon NETLOGON allow:@smbadm,@users,nobody rwlist:@smbadm
/var/spool/samba/profiles PROFILES allow:@users
/var/spool/samba/share SHAREDFILES allow:@users,@smbadm rwlist:@smbadm
/var/spool/samba/ftp FTP allow:@users,nobody rolist:nobody
~ $u allow:@users
/var/spool/apache/vhosts WEBSITES allow:@www
<xsl:apply-templates select="/config/FileServer/Shares/Share"/>
</xsl:template>
</xsl:stylesheet>
