<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="sdomain" select="/config/DNS/Config/Option[@option = 'Search']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>
<xsl:variable name="fqdn" select="concat($hname,'.',$domain)"/>
<xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'" />
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />
<xsl:param name="cacert"/>
<xsl:param name="crlcert"/>

<xsl:template match="/config">
  <xsl:text>divert(-1)

OSTYPE(linux)dnl

divert(0)dnl
VERSIONID(`$Id: Network Sentry Config,2007/09/13 Greg Nietsky Exp $')

define(`STATUS_FILE',`/var/db/sendmail.statistics')dnl
define(`confFORWARD_PATH',`$z/.forward.$w+$h:$z/.forward+$h:$z/.forward.$w:$z/.forward')dnl
define(`confMAX_HEADERS_LENGTH', `32768')dnl
define(`confLDAP_CLUSTER', `AllServers')
define(`confLDAP_DEFAULT_SPEC', `-h "</xsl:text>
  <xsl:value-of select="concat(/config/LDAP/Config/Option[@option = 'Server'],'&quot; -d ')"/>
  <xsl:choose>
    <xsl:when test="$hname != ''">
      <xsl:value-of select="concat('uid=ldap_limited_',$hname,',',/config/LDAP/Config/Option[@option = 'Login'])"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('uid=ldap_limited,',/config/LDAP/Config/Option[@option = 'Login'])"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text> -M simple -P /etc/ldap.limited')dnl
LDAPROUTE_DOMAIN_FILE(`@LDAP')dnl
FEATURE(`ldap_routing')dnl
VIRTUSER_DOMAIN_FILE(`@ldap:-k (&amp;(objectClass=sendmailMTAClass)(sendmailMTAClassName=LDAPRoute)) -v sendmailMTAClassValue')dnl
FEATURE(`virtusertable',`LDAP')dnl
RELAY_DOMAIN_FILE(`@LDAP')dnl
FEATURE(`access_db',`LDAP')dnl
FEATURE(`mailertable', `LDAP')dnl
FEATURE(`domaintable', `LDAP')dnl
FEATURE(`authinfo',`LDAP')dnl
define(`ALIAS_FILE', `ldap:')dnl
MASQUERADE_AS(`</xsl:text><xsl:value-of select="$domain"/><xsl:text>')dnl
MASQUERADE_DOMAIN_FILE(`@LDAP')dnl
FEATURE(`local_no_masquerade')
FEATURE(`limited_masquerade')
FEATURE(`masquerade_envelope')
FEATURE(`masquerade_entire_domain')
FEATURE(`no_default_msa', `dnl')dnl
DAEMON_OPTIONS(`Port=587, M=E, Name=MSA, Family=inet6')dnl
DAEMON_OPTIONS(`Port=smtp,Name=MTA, Family=inet6')dnl
define(`confDELIVERY_MODE',`</xsl:text><xsl:value-of select="translate(/config/Email/Config/Option[@option = 'Delivery'], $uppercase, $smallcase)"/><xsl:text>')dnl
define(`confMAX_MESSAGE_SIZE',`</xsl:text><xsl:value-of select="/config/Email/Config/Option[@option = 'MaxSize'] * 1024 * 1024"/><xsl:text>')
define(`SMART_HOST',`</xsl:text><xsl:value-of select="/config/Email/Config/Option[@option = 'Smarthost']"/><xsl:text>')
define(`confCACERT_PATH', `/etc/ipsec.d/certs/')
define(`confSERVER_CERT', `/etc/openssl/server.signed.pem')
define(`confSERVER_KEY', `/etc/openssl/serverkey.pem')
define(`confAUTH_MECHANISMS', `LOGIN PLAIN')dnl
TRUST_AUTH_MECH(`LOGIN PLAIN')dnl
define(`confCACERT', `</xsl:text><xsl:value-of select="$cacert"/><xsl:text>')
define(`confCRL', `</xsl:text><xsl:value-of select="$crlcert"/><xsl:text>')
define(`confDOMAIN_NAME',`</xsl:text><xsl:value-of select="$fqdn"/><xsl:text>')dnl
FEATURE(`always_add_domain',`</xsl:text><xsl:value-of select="$domain"/><xsl:text>')
define(`confPRIVACY_FLAGS',`goaway')

</xsl:text>
  <xsl:if test="/config/Email/Config/Option[@option = 'AntiSpam'] = 'true'">
    <xsl:text>FEATURE(`delay_checks')dnl&#xa;</xsl:text>
    <xsl:text>FEATURE(`dnsbl',`dnsbl.sorbs.net',`"554 Rejected " $&amp;{client_addr} " found in dnsbl.sorbs.net"')dnl&#xa;</xsl:text>
  </xsl:if>

<xsl:text>FEATURE(`redirect')dnl
FEATURE(`local_procmail',`/usr/libexec/dovecot/deliver',`/usr/libexec/dovecot/deliver -d $u')

MAILER(dovecot)dnl
MAILER(local)dnl
MAILER(smtp)dnl

LOCAL_CONFIG
H?M?Envelope-To: $u
F{w}@ldap:-k (&amp;(objectClass=sendmailMTAClass)(sendmailMTAClassName=LDAPRoute)) -v sendmailMTAClassValue
C{w}</xsl:text><xsl:value-of select="$fqdn"/><xsl:text>
</xsl:text>
 <xsl:if test="$domain != $fqdn">
    <xsl:value-of select="concat('C{M}',$fqdn,$nl)"/>
 </xsl:if>
</xsl:template>
</xsl:stylesheet>
