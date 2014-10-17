<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="/config">
<xsl:text>[Asterisk]
Description             = Asterisk Database DSN
Driver                  = </xsl:text><xsl:value-of select="$pgsqlodbc"/><xsl:text>
Trace                   = Yes
TraceFile               = /var/log/asterisksql.log
Database                = asterisk
Servername              = </xsl:text><xsl:value-of select="/config/SQL/Option[@option = 'AsteriskServ']"/><xsl:text>
Port                    = 5432
Protocol                = 6.4
ReadOnly                = No
RowVersioning           = No
ShowSystemTables        = No
ShowOidColumn           = No
FakeOidIndex            = No
Pooling                 = Yes
CPTimeout               = 120
SSL			= Yes
SSLMode			= require

[Master]
Description             = Asterisk Master Database DSN
Driver                  = </xsl:text><xsl:value-of select="$pgsqlodbc"/><xsl:text>
Trace                   = Yes
TraceFile               = /var/log/mastersql.log
Database                = asterisk
Servername              = </xsl:text><xsl:value-of select="/config/SQL/Option[@option = 'AsteriskServ']"/><xsl:text>
Port                    = 5432
Protocol                = 6.4
ReadOnly                = No
RowVersioning           = No
ShowSystemTables        = No
ShowOidColumn           = No
FakeOidIndex            = No
Pooling                 = Yes
CPTimeout               = 120
SSL			= Yes
SSLMode			= require

</xsl:text>
</xsl:template>
</xsl:stylesheet>
