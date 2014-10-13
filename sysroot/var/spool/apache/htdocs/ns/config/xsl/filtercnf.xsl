<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="fqdn" select="concat(/config/DNS/Config/Option[@option = 'Hostname'],'.',/config/DNS/Config/Option[@option = 'Domain'])"/>

<xsl:template match="TimeGroup">
  <xsl:value-of select="concat('time ',@group,' {',$nl)"/>
  <xsl:value-of select="concat('  weekly ',.,' ',@start,'-',@end,$nl)"/>
  <xsl:text>}&#xa;&#xa;</xsl:text>
</xsl:template>

<xsl:template match="IPAddress">
  <xsl:value-of select="concat('  ip ',.,'/',@subnet,$nl)"/>  
</xsl:template>

<xsl:template match="SourceGroup">
  <xsl:value-of select="concat('src ',@group,' {',$nl)"/>
  <xsl:apply-templates select="IPAddress"/>
  <xsl:text>}&#xa;&#xa;</xsl:text>
</xsl:template>

<xsl:template match="BlackList">
  <xsl:choose>
    <xsl:when test="@deny = 'true'">
      <xsl:value-of select="concat(' !',.)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat(' ',.)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="ACL">
  <xsl:value-of select="concat('  ',@group)"/>
  <xsl:choose>
    <xsl:when test="@intime = 'true'">
      <xsl:text> within </xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text> outside </xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:value-of select="concat(@time,' {',$nl,'    pass')"/>
  <xsl:apply-templates select="BlackList"/>
  <xsl:choose>
    <xsl:when test="@policy != ''">
      <xsl:value-of select="concat(' ',@policy,$nl,'  }',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat($nl,'  }',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>dest local_allow {&#xa;</xsl:text>
  <xsl:if test="count(/config/Proxy/List/ProxyFilter[@filter = 'Allow' and @type = 'URL']) &gt; 0">
    <xsl:text>  urllist local_allow_urls&#xa;</xsl:text>
  </xsl:if>
  <xsl:if test="count(/config/Proxy/List/ProxyFilter[@filter = 'Allow' and @type = 'Keyword']) &gt; 0">
    <xsl:text>  expressionlist local_allow_exp&#xa;</xsl:text>
  </xsl:if>
  <xsl:if test="count(/config/Proxy/List/ProxyFilter[@filter = 'Allow' and @type = 'Domain']) &gt; 0">
    <xsl:text>  domainlist local_allow_domains&#xa;</xsl:text>
  </xsl:if>
  <xsl:text>}&#xa;&#xa;</xsl:text>

  <xsl:text>dest local_deny {&#xa;</xsl:text>
  <xsl:if test="count(/config/Proxy/List/ProxyFilter[@filter = 'Deny' and @type = 'URL']) &gt; 0">
    <xsl:text>  urllist local_deny_urls&#xa;</xsl:text>
  </xsl:if>
  <xsl:if test="count(/config/Proxy/List/ProxyFilter[@filter = 'Deny' and @type = 'Keyword']) &gt; 0">
    <xsl:text>  expressionlist local_deny_exp&#xa;</xsl:text>
  </xsl:if>
  <xsl:if test="count(/config/Proxy/List/ProxyFilter[@filter = 'Deny' and @type = 'Domain']) &gt; 0">
    <xsl:text>  domainlist local_deny_domains&#xa;</xsl:text>
  </xsl:if>
  <xsl:text>}&#xa;&#xa;</xsl:text>

  <xsl:apply-templates select="/config/Proxy/TimeGroups/TimeGroup"/>
  <xsl:apply-templates select="/config/Proxy/SourceGroups/SourceGroup"/>
  <xsl:value-of select="concat('acl {',$nl)"/>
  <xsl:apply-templates select="/config/Proxy/ACLS/ACL"/>
  <xsl:text>  default {&#xa;</xsl:text>
  <xsl:text>    pass all&#xa;</xsl:text>  
  <xsl:text>    redirect </xsl:text>  
  <xsl:choose>
    <xsl:when test="starts-with(/config/Proxy/Config/Option[@option = 'Redirect'],'http')">
      <xsl:value-of select="concat(/config/Proxy/Config/Option[@option = 'Redirect'],$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('http://',$fqdn,'/',/config/Proxy/Config/Option[@option = 'Redirect'],$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>  }&#xa;</xsl:text>  
  <xsl:text>}&#xa;</xsl:text>  
</xsl:template>
</xsl:stylesheet>
