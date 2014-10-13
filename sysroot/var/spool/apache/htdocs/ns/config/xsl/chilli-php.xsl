<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="fqdn" select="concat(/config/DNS/Config/Option[@option = 'Hostname'],'.',/config/DNS/Config/Option[@option = 'Domain'])"/>

<xsl:template match="/config">
<xsl:text>&lt;?php
define('HOTSPOT_NAME', 'dns Telecom Hotspot');
$lg = 'en';
define('BASE_URL', 'https://</xsl:text><xsl:value-of select="$fqdn"/><xsl:text>/hotspot/');
define('LOGINPATH', BASE_URL);
define('ENABLE_LOGIN_COOKIE', true);
define('DEBUG_MODE', false);
define('UAMSECRET', '</xsl:text><xsl:value-of select="$uamsecret"/><xsl:text>');
define('USERPASSWORD', true);
?&gt;
</xsl:text>
</xsl:template>
</xsl:stylesheet>
