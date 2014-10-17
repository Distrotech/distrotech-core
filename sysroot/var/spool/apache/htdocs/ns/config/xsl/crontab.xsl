<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="is_modem" select="count(/config/IP/ADSL/Links/Link) or
                                      /config/IP/Sysonf/Option[@option = 'External'] = 'Dialup' or
                                      /config/IP/Dialup/Option[@option = 'Connection'] = 'ADSL'"/>

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

<xsl:template match="Tunnel">
  <xsl:text>0     */4    * * *        /usr/sbin/crlget </xsl:text>
  <xsl:value-of select="@crlurl"/>
  <xsl:text> > /dev/null 2>&amp;1&#xa;</xsl:text>
</xsl:template>

<xsl:template match="Link">
  <xsl:text>*/10  *      * * *        /usr/bin/nsupdate /tmp/dnsup.</xsl:text>
  <xsl:value-of select="."/>
  <xsl:text>.ppp > /dev/null 2>&amp;1&#xa;</xsl:text>
</xsl:template>

<xsl:template name="sysconfig">
  <xsl:choose>
    <xsl:when test="$is_modem">
      <xsl:text>/etc/rc.d/rc.ppp</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>(/usr/sbin/servconfig;/etc/rc.d/rc.tunnels)</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="setcmd">
  <xsl:choose>
    <xsl:when test=". = 'Send Queued Mail'">
      <xsl:text>(/usr/sbin/sendmail -q;sendmail -q -Ac)</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:choose>
        <xsl:when test=". = 'Fetch POP3 Mail'">
          <xsl:text>/usr/bin/fetchmail</xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:call-template name="sysconfig"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Event">
  <xsl:choose>
    <xsl:when test="@min != 60">
      <xsl:call-template name="append-pad">
        <xsl:with-param name="padChar" select="' '"/>
        <xsl:with-param name="padVar" select="concat('*/',@min)"/>
        <xsl:with-param name="length" select="'6'"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="append-pad">
        <xsl:with-param name="padChar" select="' '"/>
        <xsl:with-param name="padVar" select="'0'"/>
        <xsl:with-param name="length" select="'6'"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:call-template name="append-pad">
    <xsl:with-param name="padChar" select="' '"/>
    <xsl:with-param name="padVar" select="concat(@from,'-',@to)"/>
    <xsl:with-param name="length" select="'7'"/>
  </xsl:call-template>
  <xsl:text>* * </xsl:text>
  <xsl:call-template name="append-pad">
    <xsl:with-param name="padChar" select="' '"/>
    <xsl:with-param name="padVar" select="@days"/>
    <xsl:with-param name="length" select="'9'"/>
  </xsl:call-template>
  <xsl:call-template name="setcmd"/>
  <xsl:text> > /dev/null 2>&amp;1&#xa;</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>#User Set Options&#xa;</xsl:text>
  <xsl:apply-templates select="/config/Cron/Event"/>
  <xsl:text>
#Server Set Options
0     *      * * *        /usr/bin/nice -n 20 /usr/bin/logcheck.sh > /dev/null 2>&amp;1
0     0      * * *        /usr/sbin/logrotate /etc/logrotate.conf > /dev/null 2>&amp;1
0     0      * * *        (killall dhcpd;sleep 2;killall -9 dhcpd;/usr/sbin/dhcpd;if [ -s /etc/dhcpd6.conf ];then /usr/sbin/dhcpd -6 -cf /etc/dhcpd6.conf; fi) > /dev/null 2>&amp;1
0     0      * * *        /usr/bin/updatedb --output=/var/db/locate --prunepaths='/proc /dev /sys /tmp /var/tmp /mnt/dev /var/spool/mail /var/spool/mail /var/spool/mailscanner /var/web' > /dev/null 2>&amp;1
0     0      * * *        (/usr/sbin/rndc flush;/usr/sbin/rndc stop;rm /var/named/*.jnl;killall -9 named;/usr/sbin/named) > /dev/null 2>&amp;1
0     */1    * * *        /usr/sbin/genwebmap > /dev/null 2>&amp;1
*/10  *      * * *        /usr/sbin/cshopfix > /dev/null 2>&amp;1
0     */1    * * *        /usr/bin/db_checkpoint -1 -h /var/spool/ldap > /dev/null 2>&amp;1
0     0      * * *        /usr/bin/db_archive -d -h /var/spool/ldap > /dev/null 2>&amp;1
0     */1    * * *        /usr/bin/db_checkpoint -1 -h /var/log/ldap > /dev/null 2>&amp;1
0     0      * * *        /usr/bin/db_archive -d -h /var/log/ldap > /dev/null 2>&amp;1
0     0      * * *        /usr/bin/vacuumdb -azfU pgsql -h 127.0.0.1 > /dev/null 2>&amp;1
0     0      * * *        /usr/bin/mysql_dbmaint > /dev/null 2>&amp;1
0     3      * * *        (sync ;echo 3 > /proc/sys/vm/drop_caches) > /dev/null 2>&amp;1
0     1-23   * * *        /usr/sbin/tmsupdate > /dev/null 2>&amp;1
0     0      * * 1-6      /usr/sbin/tmsupdate 1 > /dev/null 2>&amp;1
0     0      * * 0        /usr/sbin/tmsupdate 365 > /dev/null 2>&amp;1
0     2-23   * * *        /usr/sbin/procmfilter > /dev/null 2>&amp;1
*/15  *      * * *        /usr/sbin/radcheck > /dev/null 2>&amp;1
*/15  *      * * *        /etc/asterisk/pannel/genbut.pl > /dev/null 2>&amp;1
0     2,4,6  * * *        (if [ "&#96;asterisk -rx "module show like chan_sip.so" |awk '$1 == "chan_sip.so" {print $1}'&#96;" != "chan_sip.so" ];then kill -9 &#96;cat /var/run/asterisk.pid&#96;; fi;) > /dev/null 2>&amp;1
0     4      * * *        /usr/sbin/rebootphone > /dev/null 2>&amp;1
0     2-23   * * *        (rm /var/spool/apache/htdocs/ns/config/ifup.*;/var/spool/apache/htdocs/ns/config/bwup.*;/usr/sbin/genconf) > /dev/null 2>&amp;1
0     */4    * * *        /usr/sbin/quotasetup > /dev/null 2>&amp;1
0     0      * * *        (openssl ca -gencrl -out /etc/ipsec.d/crls/crl.pem -config /etc/openssl/ca.conf;cp /etc/ipsec.d/crls/crl.pem /etc/ipsec.d/certs/`openssl crl -noout -hash -in /etc/ipsec.d/crls/crl.pem`.r0;openssl crl -in /etc/ipsec.d/crls/crl.pem -text  > /var/spool/apache/htdocs/ns/config/crl.txt;/usr/sbin/pkistore) > /dev/null 2>&amp;1
0     0      * * *        (killall -9 smbd nmbd;/usr/sbin/nmbd -D;/usr/sbin/smbd -D) > /dev/null 2>&amp;1
0     */1    * * *        (ulimit -n 65535;/usr/sbin/nscd -i passwd -i group -i hosts) > /dev/null 2>&amp;1
*     *      * * *        (if [ ! "`pidof slapd`" ];then /etc/rc.d/rc.ldap ;fi) > /dev/null 2>&amp;1
*     *      * * *        (if [ ! "'/usr/bin/whomami'" ];then /usr/sbin/nscd -i passwd -i group -i hosts;/usr/sbin/asterisk -rx "odbc show" ;/usr/bin/whoami;fi) > /dev/null 2>&amp;1
0     0      * * *        /usr/sbin/warnquota > /dev/null 2>&amp;1
0     0      * * *        /usr/sbin/uexpired > /dev/null 2>&amp;1
0     0      * * *        (mv /tmp/core* /var/cores/asterisk;rm -rf /tmp/clamav-* /tmp/php.log  /tmp/ps2fax*) > /dev/null 2>&amp;1
0     0      * * 1        rm -rf /var/cores/asterisk/* > /dev/null 2>&amp;1
*/5   *      * * *        /usr/bin/mrtg /etc/mrtg.conf --confcache-file /tmp/mrtg.ok > /dev/null 2>&amp;1
*/5   *      * * *        /usr/bin/rrdtc </xsl:text>
  <xsl:choose>
    <xsl:when test="/config/IP/Dialup/Option[@option = 'Connection'] = 'ADSL'">
      <xsl:value-of select="'Dialup'"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="/config/IP/SysConf/Option[@option = 'External']"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text> > /dev/null 2>&amp;1
*/5   *      * * *        /usr/bin/rrdlog > /dev/null 2>&amp;1
*/5   *      * * *        (/usr/bin/rrdvoip |/bin/bash) > /dev/null 2>&amp;1
0     0      * * *        rm /var/spool/apache/htdocs/ssl/req/* > /dev/null 2>&amp;1
0     0      * * *        rm /var/spool/apache/htdocs/pdf/* > /dev/null 2>&amp;1
0     2      * * *        /usr/bin/backup > /dev/null 2>&amp;1
0     2      * * 1        /usr/bin/pbx_reports > /dev/null 2>&amp;1
0     2      1 * *        /usr/bin/pbx_reports - > /dev/null 2>&amp;1
30    1      * * *        /usr/sbin/cfrestore > /dev/null 2>&amp;1
*/5   *      * * *        /usr/sbin/asterisk -rx "odbc connect Asterisk" > /dev/null 2>&amp;1
*/</xsl:text>
    <xsl:value-of select="/config/Email/Config/Option[@option = 'Rescan']"/>
  <xsl:text>   *      * * *        (/usr/sbin/sendmail -q -qR\@</xsl:text>
    <xsl:value-of select="$domain"/>
  <xsl:text>\> -qS\@</xsl:text>
    <xsl:value-of select="$domain"/>
  <xsl:text>\>;sendmail -q -Ac -qR\@</xsl:text>
    <xsl:value-of select="$domain"/>
  <xsl:text>\> -qS\@</xsl:text>
    <xsl:value-of select="$domain"/>
  <xsl:text>\>) > /dev/null 2>&amp;1</xsl:text>
  <xsl:if test="count(/config/Cron/Event[. = 'System Update']) = 0">
    <xsl:text>
*/2   2-23   * * *        </xsl:text>
      <xsl:call-template name="sysconfig"/>
      <xsl:text> > /dev/null 2>&amp;</xsl:text>
  </xsl:if>
<xsl:text>
0     */6    * * *        (ps ax |grep op_server.pl |grep -v grep |awk '{print "kill "$1}'|sh;/etc/asterisk/pannel/op_server.pl &amp;) > /dev/null 2>&amp;1
</xsl:text>
  <xsl:if test="$is_modem">
    <xsl:text>*/20  *      * * *        /usr/sbin/linkup > /dev/null 2>&amp;1
*/10  *      * * *        /usr/bin/nsupdate /tmp/dnsup.ppp > /dev/null 2>&amp;1&#xa;</xsl:text>
    <xsl:text>*/10  *      * * *        if [ -e /etc/dyndnsconf ];then /usr/sbin/dnsupdate;fi > /dev/null 2>&amp;1&#xa;</xsl:text>
    <xsl:apply-templates select="/config/IP/ADSL/Links/Link"/>
    <xsl:text>
#Shutdown ADSL PPP Link
0     1      * * *        (sleep 30;killall pppd;rm /var/run/netsentry* /var/lock/ppp*.lock) > /dev/null 2>&amp;1
</xsl:text>
  </xsl:if>
  <xsl:text>
#Clam Anti Virus Scan
0     9,12,18,23 * * *    /usr/sbin/clamupdate > /dev/null 2>&amp;1
</xsl:text>

  <xsl:if test="/config/FileServer/Setup/Option[@option = 'Security'] != 'USER'">
    <xsl:text>
#Update IDMAP
0     *      * * *        /usr/sbin/idmapsync > /dev/null 2>&amp;1
</xsl:text>
  </xsl:if>
  <xsl:if test="count(/config/IP/GRE/Tunnels/Tunnel[@crlurl != '']) > 0">
    <xsl:text>&#xa;#Fetch CRL's&#xa;</xsl:text>
    <xsl:apply-templates select="/config/IP/GRE/Tunnels/Tunnel"/>
  </xsl:if>
<xsl:text>
#Backup Asterisk Recordings
*/10  *      * * *        if [ -d /var/spool/asterisk/monitor.bak ];then /usr/bin/rsync -a --exclude=*-in.WAV --exclude=*-out.WAV /var/spool/asterisk/monitor/`/usr/bin/date +%Y-%m-%d` /var/spool/asterisk/monitor.bak/;fi > /dev/null 2>&amp;1</xsl:text>
  <xsl:text>&#xa;</xsl:text>
</xsl:template>
</xsl:stylesheet>
