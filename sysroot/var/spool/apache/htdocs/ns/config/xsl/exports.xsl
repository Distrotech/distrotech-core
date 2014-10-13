<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />

<!--
http://www.dpawson.co.uk/xsl/sect2/padding.html
-->

<xsl:template name="prepend-pad">
  <xsl:param name="padChar"/>
  <xsl:param name="padVar"/>
  <xsl:param name="length"/>
  <xsl:choose>
    <xsl:when test="string-length($padVar) &lt; $length">
      <xsl:call-template name="prepend-pad">
        <xsl:with-param name="padChar" select="$padChar"/>
        <xsl:with-param name="padVar" select="concat($padChar,$padVar)"/>
        <xsl:with-param name="length" select="$length"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="substring($padVar,string-length($padVar) - $length + 1)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="append-pad">
  <xsl:param name="padChar"/>
  <xsl:param name="padVar"/>
  <xsl:param name="length"/>
  <xsl:choose>
    <xsl:when test="string-length($padVar) &lt; $length">
      <xsl:call-template name="append-pad">
        <xsl:with-param name="padChar" select="$padChar"/>
        <xsl:with-param name="padVar" select="concat($padVar,$padChar)"/>
        <xsl:with-param name="length" select="$length"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="substring($padVar,string-length($padVar) - $length +1)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="sysshares">
  <xsl:param name="share"/>
  <xsl:call-template name="append-pad">
    <xsl:with-param name="padChar" select="' '"/>
    <xsl:with-param name="padVar" select="$share"/>
    <xsl:with-param name="length" select="'50'"/>
  </xsl:call-template>
  <xsl:text>*(sync,no_subtree_check,ro,insecure,no_root_squash,insecure_locks)&#xa;</xsl:text>
</xsl:template>

<xsl:template match="Share">
  <xsl:call-template name="append-pad">
    <xsl:with-param name="padChar" select="' '"/>
    <xsl:with-param name="padVar" select="."/>
    <xsl:with-param name="length" select="'50'"/>
  </xsl:call-template>

  <xsl:value-of select="@ipaddr"/>
  <xsl:text>(sync,no_subtree_check</xsl:text>

  <xsl:choose>
    <xsl:when test="@uid != '-'">
      <xsl:value-of select="concat(',anonuid=',@uid)"/>
      <xsl:if test="@gid != '-'">
        <xsl:value-of select="concat(',anongid=',@gid)"/>
      </xsl:if>
      <xsl:choose>
        <xsl:when test="@squash = 'true'">
          <xsl:text>,all_squash</xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>,root_squash</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>,no_root_squash</xsl:text>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:choose>
    <xsl:when test="@ro = 'true'">
      <xsl:text>,ro</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>,rw</xsl:text>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:text>)&#xa;</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:apply-templates select="/config/NFS/Shares/Share"/>

  <xsl:if test="$ubuntud != ''">
    <xsl:call-template name="sysshares">
      <xsl:with-param name="share" select="$ubuntud"/>
    </xsl:call-template>
  </xsl:if>

  <xsl:if test="$ubuntus != ''">
    <xsl:call-template name="sysshares">
      <xsl:with-param name="share" select="$ubuntus"/>
    </xsl:call-template>
  </xsl:if>

  <xsl:if test="$tinycore != ''">
    <xsl:call-template name="sysshares">
      <xsl:with-param name="share" select="$tinycore"/>
    </xsl:call-template>
  </xsl:if>

  <xsl:if test="$install != ''">
    <xsl:call-template name="sysshares">
      <xsl:with-param name="share" select="$install"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>
