<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="/config">LogFile /var/log/clamd.log
LogFileMaxSize 0
LogVerbose yes
LogTime yes
PidFile /var/run/clamd.pid
DatabaseDirectory /var/spool/avirus
LocalSocket /tmp/clamd
FixStaleSocket yes
#TCPSocket 3310
TemporaryDirectory /tmp
MaxConnectionQueueLength <xsl:value-of select="10 * /config/FileServer/ClamAV/Option[@option = 'AVMaxThread']"/>
MaxThreads <xsl:value-of select="/config/FileServer/ClamAV/Option[@option = 'AVMaxThread']"/>
MaxDirectoryRecursion 0
#ThreadTimeout 14400
#FollowDirectorySymlinks
FollowFileSymlinks yes
User root
#Foreground
ScanArchive yes
MaxFileSize <xsl:value-of select="/config/FileServer/ClamAV/Option[@option = 'AVMaxSize']"/>
MaxRecursion 5
MaxFiles 1000
IdleTimeout 5
ReadTimeout 5
</xsl:template>
</xsl:stylesheet>
