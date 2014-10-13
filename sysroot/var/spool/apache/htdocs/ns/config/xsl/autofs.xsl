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

<xsl:template name="smbopts">
  <xsl:param name="option"/>
  <xsl:param name="count"/>
  <xsl:param name="mount"/>

  <xsl:if test="$count = 0">
    <xsl:choose>
      <xsl:when test="@username != '-'">
        <xsl:variable name="newopt" select="concat($option,',username=',@username)"/>
        <xsl:call-template name="smbopts" match="/config/NFS/Mounts/Mount[. = $mount]">
          <xsl:with-param name="count" select="$count+1"/>
          <xsl:with-param name="mount" select="$mount"/>
          <xsl:with-param name="option" select="$newopt"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:variable name="newopt" select="concat($option,',guest')"/>
        <xsl:call-template name="smbopts">
          <xsl:with-param name="count" select="'0'"/>
          <xsl:with-param name="mount" select="$mount"/>
          <xsl:with-param name="option" select="$newopt"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:if>

  <xsl:if test="$count = 1">
    <xsl:variable name="optval" select="/config/NFS/Mounts/Mount[text() = $mount]/@password"/>
    <xsl:choose>
      <xsl:when test="$optval != '-'">
        <xsl:variable name="newopt" select="concat($option,',password=',$optval)"/>
        <xsl:call-template name="smbopts">
          <xsl:with-param name="count" select="$count+1"/>
          <xsl:with-param name="mount" select="$mount"/>
          <xsl:with-param name="option" select="$newopt"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="smbopts">
          <xsl:with-param name="count" select="$count+1"/>
          <xsl:with-param name="mount" select="$mount"/>
          <xsl:with-param name="option" select="$option"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:if>

  <xsl:if test="$count = 2">
    <xsl:variable name="optval" select="/config/NFS/Mounts/Mount[text() = $mount]/@uid"/>
    <xsl:choose>
      <xsl:when test="$optval != '-'">
        <xsl:variable name="newopt" select="concat($option,',uid=',$optval)"/>
        <xsl:call-template name="smbopts">
          <xsl:with-param name="count" select="$count+1"/>
          <xsl:with-param name="mount" select="$mount"/>
          <xsl:with-param name="option" select="$newopt"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="smbopts">
          <xsl:with-param name="count" select="$count+1"/>
          <xsl:with-param name="mount" select="$mount"/>
          <xsl:with-param name="option" select="$option"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:if>

  <xsl:if test="$count = 3">
    <xsl:variable name="optval" select="/config/NFS/Mounts/Mount[text() = $mount]/@gid"/>
    <xsl:choose>
      <xsl:when test="$optval != '-'">
        <xsl:variable name="newopt" select="concat($option,',gid=',$optval)"/>
        <xsl:call-template name="smbopts">
          <xsl:with-param name="count" select="$count+1"/>
          <xsl:with-param name="mount" select="$mount"/>
          <xsl:with-param name="option" select="$newopt"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="smbopts">
          <xsl:with-param name="count" select="$count+1"/>
          <xsl:with-param name="mount" select="$mount"/>
          <xsl:with-param name="option" select="$option"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:if>

  <xsl:if test="$count = 4">
    <xsl:variable name="optval" select="/config/NFS/Mounts/Mount[text() = $mount]/@ro"/>
    <xsl:choose>
      <xsl:when test="$optval = 'true'">
        <xsl:variable name="newopt" select="concat($option,',ro')"/>
        <xsl:call-template name="smbopts">
          <xsl:with-param name="count" select="$count+1"/>
          <xsl:with-param name="mount" select="$mount"/>
          <xsl:with-param name="option" select="$newopt"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="smbopts">
          <xsl:with-param name="count" select="$count+1"/>
          <xsl:with-param name="mount" select="$mount"/>
          <xsl:with-param name="option" select="$option"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:if>

  <xsl:if test="$count = 5">
    <xsl:call-template name="append-pad">
      <xsl:with-param name="padChar" select="' '"/>
      <xsl:with-param name="padVar" select="$option"/>
      <xsl:with-param name="length" select="'75'"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template match="Mount">
  <xsl:call-template name="append-pad">
    <xsl:with-param name="padChar" select="' '"/>
    <xsl:with-param name="padVar" select="@folder"/>
    <xsl:with-param name="length" select="'15'"/>
  </xsl:call-template>
  <xsl:choose>
    <xsl:when test="@type = 'nfs'">
      <xsl:call-template name="append-pad">
        <xsl:with-param name="padChar" select="' '"/>
        <xsl:with-param name="padVar" select="'-fstype=nfs,vers=3,acl,lock,rsize=1048576,wsize=1048576,hard,intr'"/>
        <xsl:with-param name="length" select="'75'"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="smbopts" match="/config/NFS/Mounts/Mount[text() = .]">
        <xsl:with-param name="count" select="'0'"/>
        <xsl:with-param name="option" select="'-fstype=smb'"/>
        <xsl:with-param name="mount" select="."/>
      </xsl:call-template>
      <xsl:text>:</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:value-of select="concat(@mount,$nl)"/>
</xsl:template>

<xsl:template match="/config">
  <xsl:apply-templates select="/config/NFS/Mounts/Mount"/>
  <xsl:if test="$cd = '1'">
    <xsl:call-template name="append-pad">
      <xsl:with-param name="padChar" select="' '"/>
      <xsl:with-param name="padVar" select="'cd'"/>
      <xsl:with-param name="length" select="'15'"/>
    </xsl:call-template>
    <xsl:call-template name="append-pad">
      <xsl:with-param name="padChar" select="' '"/>
      <xsl:with-param name="padVar" select="'-fstype=auto,nosuid,nodev,ro'"/>
      <xsl:with-param name="length" select="'75'"/>
    </xsl:call-template>
    <xsl:text>:/dev/cdrom&#xa;</xsl:text>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>
