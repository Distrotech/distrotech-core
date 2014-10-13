<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />
<xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'" />
<xsl:variable name="maildel" select="translate(/config/Email/Config/Option[@option = 'Delivery'], $uppercase, $smallcase)"/>
<xsl:param name="muser" select="'daemon'"/>

<xsl:template name="sharedbox">
  <xsl:param name="mbox"/>
  <xsl:param name="access"/>

  <xsl:text>if [ ! -d "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>" ] || [ "`ls -ld "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>" |awk '{print $3"."$4}'`" != "</xsl:text><xsl:value-of select="$access"/><xsl:text>" ];then
  if [ ! -d "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>" ];then
    mkdir -m 2770 "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>"
  fi;
  chown </xsl:text><xsl:value-of select="$access"/><xsl:text> "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>"

  touch "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>/dovecot-shared"
  chown </xsl:text><xsl:value-of select="$access"/><xsl:text> "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>/dovecot-shared"
  chmod 660 "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>/dovecot-shared"

  for mbox in cur new tmp;do
    if [ ! -d "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>/${mbox}" ];then
      mkdir -m 2770 "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>/${mbox}"
    fi;
    chown </xsl:text><xsl:value-of select="$access"/><xsl:text> "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>/${mbox}"
  done;
  if [ -e "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>/dovecot-acl" ];then
    rm "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>/dovecot-acl"
  fi;
fi;

if [ ! -e "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>/dovecot-acl" ];then
  echo "group=</xsl:text><xsl:value-of select="substring-after($access,'.')"/><xsl:text> lrwstipex" > "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>/dovecot-acl"
  chown </xsl:text><xsl:value-of select="$access"/><xsl:text> "/var/spool/mail/shared/.</xsl:text><xsl:value-of select="$mbox"/><xsl:text>/dovecot-acl"
  DROPACL=1
fi;

</xsl:text>
</xsl:template>

<xsl:template match="MailBox">
  <xsl:call-template name="sharedbox">
    <xsl:with-param name="mbox" select="."/>
    <xsl:with-param name="access" select="concat($muser,'.',@group)"/>
  </xsl:call-template>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>#!/bin/bash

/usr/sbin/socktest 127.0.0.1 25 >/dev/null 2>&amp;1
if [ $? != 0 ];then
  /usr/bin/killall -9 sendmail
fi

DROPACL=0;

#Create Public Mail Folders
if [ ! -d /var/spool/mail/shared ] || [ "`ls -ld /var/spool/mail/shared |awk '{print $3"."$4}'`" != "</xsl:text><xsl:value-of select="$muser"/><xsl:text>.users" ];then
  if [ ! -d /var/spool/mail/shared ];then
    mkdir -m 2775 /var/spool/mail/shared
  fi;
  chown </xsl:text><xsl:value-of select="$muser"/><xsl:text>.users /var/spool/mail/shared
  touch /var/spool/mail/shared/dovecot-shared
  chown </xsl:text><xsl:value-of select="$muser"/><xsl:text>.users /var/spool/mail/shared/dovecot-shared
  chmod 660 /var/spool/mail/shared/dovecot-shared
fi;

</xsl:text>

  <xsl:call-template name="sharedbox">
    <xsl:with-param name="mbox" select="'Administrators'"/>
    <xsl:with-param name="access" select="concat($muser,'.smbadm')"/>
  </xsl:call-template>

  <xsl:apply-templates select="/config/LDAP/PublicMail/MailBox[@address != 'root']"/>

<xsl:text>if [ ${DROPACL} == 1 ] &amp;&amp; [ -e /var/spool/mail/shared/dovecot-acl-list ];then
  rm /var/spool/mail/shared/dovecot-acl-list
fi;

if [ ! "`/bin/pidof sendmail`" ];then
  #Check Statistics File
  if [ ! -e /var/db/sendmail.statistics ];then
    mv /etc/mail/statistics /var/db/sendmail.statistics
    ln -s /var/db/sendmail.statistics /etc/mail/statistics
  fi;
  /usr/sbin/sendmail -bd -ODeliveryMode=</xsl:text>
  <xsl:choose>
    <xsl:when test="$maildel = 'background'">
      <xsl:text>queue</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$maildel"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text> -OQueueDirectory=/var/spool/mqueue.in > /dev/null 2>&amp;1 &amp;
fi;

#Startup POP3/IMAP Service
if [ ! -e /etc/dovecot.passwd ];then
  touch /etc/dovecot.passwd
fi

if [ /etc/dovecot/dovecot.conf -nt /var/run/dovecot/master.pid ];then
  (killall dovecot;
  sleep 3;
  killall -9 dovecot) > /dev/null 2>&amp;1
fi

if [ ! "`/bin/pidof dovecot`" ] &amp;&amp; [ "$1" != "sendmail" ];then
  if [ -e /var/run/dovecot/master.pid ];then
    rm /var/run/dovecot/master.pid
  fi;

  /usr/sbin/dovecot-wrap
fi;

if [ ! -e /etc/dovecot/dovecot-sogo.conf ] || [ /etc/dovecot/dovecot.conf -nt /etc/dovecot/dovecot-sogo.conf ];then
  sed -e "s/^[ \t]*ssl_listen.*$/#/" \
      -e "s/^\([ \t]*listen = \).*/\1127.0.0.1:286/" \
      -e "s/^\([ \t]*address = \).*/\1127.0.0.1,::1/" \
      -e "s/^\([ \t]*port = \)143/\1286/" \
      -e "s/^\([ \t]*port = \)993/\11986/" \
      -e "s/^\([ \t]*args = session=yes cache_key=\%u\).*/\1 sogo-%Ls/" \
      -e "s/^\(protocols = imap\).*$/\1/" \
      -e "s/^\(.*path = \/var\/run\/dovecot\)\/auth-master$/\1-sogo\/auth-master/" \
      -e "s/^\(base_dir.*\)/\1-sogo/" /etc/dovecot/dovecot.conf |grep -vE "^#" > /etc/dovecot/dovecot-sogo.conf
  (killall dovecot-sogo;
  sleep 3;
  killall -9 dovecot-sogo) > /dev/null 2>&amp;1
fi

if [ -e /etc/dovecot/dovecot-sogo.conf ] &amp;&amp; [ ! "`/bin/pidof dovecot-sogo`" ] &amp;&amp; [ "$1" != "sendmail" ];then
  if [ -e /var/run/dovecot-sogo/master.pid ];then
    rm /var/run/dovecot-sogo/master.pid
  fi;

  /usr/sbin/dovecot-wrap sogo
  /usr/sbin/safe_sogo
fi;
</xsl:text>
</xsl:template>
</xsl:stylesheet>
