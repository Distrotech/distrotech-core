<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="dom" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>
<xsl:variable name="defttl" select="/config/DNS/Config/Option[@option = 'DefaultTTL']"/>
<xsl:variable name="refresh" select="/config/DNS/Config/Option[@option = 'Refresh']"/>
<xsl:variable name="retry" select="/config/DNS/Config/Option[@option = 'Retry']"/>
<xsl:variable name="expire" select="/config/DNS/Config/Option[@option = 'Expire']"/>
<xsl:variable name="fqdn" select="concat($hname,'.',$dom)"/>
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>
<xsl:param name="serial"/>

<!--
http://www.dpawson.co.uk/xsl/sect2/padding.html
-->

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

<xsl:template name="addzrec">
  <xsl:param name="entry"/>
  <xsl:param name="ttl"/>
  <xsl:param name="type"/>
  <xsl:param name="value"/>

  <xsl:call-template name="append-pad">
    <xsl:with-param name="padChar" select="' '"/>
    <xsl:with-param name="padVar" select="$entry"/>
    <xsl:with-param name="length" select="'64'"/>
  </xsl:call-template>

  <xsl:call-template name="append-pad">
    <xsl:with-param name="padChar" select="' '"/>
    <xsl:with-param name="padVar" select="$ttl"/>
    <xsl:with-param name="length" select="'8'"/>
  </xsl:call-template>

  <xsl:text> IN     </xsl:text>

  <xsl:call-template name="append-pad">
    <xsl:with-param name="padChar" select="' '"/>
    <xsl:with-param name="padVar" select="$type"/>
    <xsl:with-param name="length" select="'8'"/>
  </xsl:call-template>
  <xsl:value-of select="concat(' ',$value,$nl)"/>
</xsl:template>

<xsl:template name="loopptr">
  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="'1'"/>
    <xsl:with-param name="type" select="'PTR'"/>
    <xsl:with-param name="value" select="concat('loopback.',$dom,'.')"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>
  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="'2'"/>
    <xsl:with-param name="type" select="'PTR'"/>
    <xsl:with-param name="value" select="concat('loopback2.',$dom,'.')"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="intrecords">
  <xsl:choose>
    <xsl:when test="/config/Mail/Config/Option[@option = 'MailExchange1'] != ''">
      <xsl:call-template name="addzrec">
        <xsl:with-param name="entry" select="''"/>
        <xsl:with-param name="type" select="'MX'"/>
        <xsl:with-param name="value" select="concat('0 ',/config/Mail/Config/Option[@option = 'MailExchange1'],'.')"/>
        <xsl:with-param name="ttl" select="$defttl"/>
      </xsl:call-template>
      <xsl:if test="/config/Mail/Config/Option[@option = 'MailExchange2'] != ''">
        <xsl:call-template name="addzrec">
          <xsl:with-param name="entry" select="''"/>
          <xsl:with-param name="type" select="'MX'"/>
          <xsl:with-param name="value" select="concat('5 ',/config/Mail/Config/Option[@option = 'MailExchange2'],'.')"/>
          <xsl:with-param name="ttl" select="$defttl"/>
        </xsl:call-template>
      </xsl:if>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="addzrec">
        <xsl:with-param name="entry" select="''"/>
        <xsl:with-param name="type" select="'MX'"/>
        <xsl:with-param name="value" select="concat('0 ',$fqdn,'.')"/>
        <xsl:with-param name="ttl" select="$defttl"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="$hname"/>
    <xsl:with-param name="type" select="'A'"/>
    <xsl:with-param name="value" select="$intip"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>

  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="'localhost'"/>
    <xsl:with-param name="type" select="'CNAME'"/>
    <xsl:with-param name="value" select="concat('loopback.',$dom,'.')"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>

  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="'loopback'"/>
    <xsl:with-param name="type" select="'A'"/>
    <xsl:with-param name="value" select="'127.0.0.1'"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>

  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="'loopback2'"/>
    <xsl:with-param name="type" select="'A'"/>
    <xsl:with-param name="value" select="'127.0.0.2'"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>

  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="'loopback'"/>
    <xsl:with-param name="type" select="'AAAA'"/>
    <xsl:with-param name="value" select="'::1'"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>

  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="'loopback2'"/>
    <xsl:with-param name="type" select="'AAAA'"/>
    <xsl:with-param name="value" select="'::127.0.0.2'"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="records">
  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="'www'"/>
    <xsl:with-param name="type" select="'CNAME'"/>
    <xsl:with-param name="value" select="concat($fqdn,'.')"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>

  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="'mail'"/>
    <xsl:with-param name="type" select="'CNAME'"/>
    <xsl:with-param name="value" select="concat($fqdn,'.')"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>

  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="'_iax._udp'"/>
    <xsl:with-param name="type" select="'SRV'"/>
    <xsl:with-param name="value" select="concat('0 1 4569 ',$fqdn,'.')"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>

  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="'_sip._udp'"/>
    <xsl:with-param name="type" select="'SRV'"/>
    <xsl:with-param name="value" select="concat('0 1 5060 ',$fqdn,'.')"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>

  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="'_sip._tcp'"/>
    <xsl:with-param name="type" select="'SRV'"/>
    <xsl:with-param name="value" select="concat('0 1 5060 ',$fqdn,'.')"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>

  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="'_sips._tcp'"/>
    <xsl:with-param name="type" select="'SRV'"/>
    <xsl:with-param name="value" select="concat('0 1 5061 ',$fqdn,'.')"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>

  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="'_stun._udp'"/>
    <xsl:with-param name="type" select="'SRV'"/>
    <xsl:with-param name="value" select="concat('0 1 3478 ',$fqdn,'.')"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>
</xsl:template>

<xsl:template match="/config">
  <xsl:value-of select="concat('@                        ',$defttl,' IN     SOA   ',$domain,'.     info (',$nl)"/>
  <xsl:value-of select="concat('                                           ',$serial,' ; serial',$nl)"/>
  <xsl:value-of select="concat('                                           ',$refresh,$nl)"/>
  <xsl:value-of select="concat('                                           ',$retry,$nl)"/>
  <xsl:value-of select="concat('                                           ',$expire,$nl)"/>
  <xsl:value-of select="concat('                                           ',$defttl,')',$nl)"/>

  <xsl:call-template name="addzrec">
    <xsl:with-param name="entry" select="''"/>
    <xsl:with-param name="type" select="'NS'"/>
    <xsl:with-param name="value" select="concat($fqdn,'.')"/>
    <xsl:with-param name="ttl" select="$defttl"/>
  </xsl:call-template>

  <xsl:if test="$addrec = '1'">
    <xsl:call-template name="records"/>
  </xsl:if>

  <xsl:if test="$addrec = '2'">
    <xsl:call-template name="loopptr"/>
  </xsl:if>

  <xsl:if test="$addrec = '3'">
    <xsl:if test="$domain = $dom">
      <xsl:call-template name="intrecords"/>
    </xsl:if>
    <xsl:call-template name="records"/>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>
