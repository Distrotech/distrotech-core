<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<xsl:template match="Realm">
  <xsl:text>&#xa;</xsl:text>
  <xsl:value-of select="concat('realm ',.,' {',$nl)"/>
  <xsl:text>       type        = radius&#xa;</xsl:text>

  <xsl:choose>
    <xsl:when test="starts-with(@authhost,'127.0.0.1') or starts-with(@authhost,'::1')">
      <xsl:value-of select="concat('       authhost    = LOCAL',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('       authhost    = ',@authhost,$nl)"/>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:choose>
    <xsl:when test="starts-with(@accthost,'127.0.0.1') or starts-with(@accthost,'::1')">
      <xsl:value-of select="concat('       accthost    = LOCAL',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('       accthost    = ',@accthost,$nl)"/>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:if test="not(starts-with(@authhost,'127.0.0.1') or starts-with(@authhost,'::1'))">
    <xsl:if test="@secret != ''">
      <xsl:value-of select="concat('       secret      = ',@secret,$nl)"/>
    </xsl:if>

    <xsl:if test="@rrobin = 'true'">
      <xsl:text>       ldflag      = round_robin&#xa;</xsl:text>
    </xsl:if>
  </xsl:if>

  <xsl:if test="@nostrip = 'true'">
    <xsl:text>       nostrip&#xa;</xsl:text>
  </xsl:if>
  <xsl:text>}&#xa;</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>proxy server {
        synchronous = no
        retry_delay = 5
        retry_count = 3
        dead_time = 120
        servers_per_realm = 15
        default_fallback = yes
}

realm </xsl:text><xsl:value-of select="/config/DNS/Config/Option[@option = 'Domain']"/><xsl:text> {
       type        = radius
       authhost    = LOCAL
       accthost    = LOCAL
}
</xsl:text>
  <xsl:apply-templates select="/config/Radius/Realms/Realm"/>
</xsl:template>
</xsl:stylesheet>
