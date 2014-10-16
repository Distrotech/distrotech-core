<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:param name="tsigkey"/>
<xsl:variable name="pdns" select="/config/IP/SysConf/Option[@option = 'PrimaryDns']"/>
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>

<xsl:template name="script">
  <xsl:text>
ISLDAPTLS="on";
VSUF=$(for dc in `echo ${DOMAIN} |sed "s/\./ /g"`;do echo -n ,dc=${dc};done)
FQDN="${HOSTNAME}.${DOMAIN}";
ANONUID="ldap_limited_${HOSTNAME}";
CONFUID="ldap_config_${HOSTNAME}";
LIMFILE="ldap_${HOSTNAME}.limited";
CONFFILE="ldap_${HOSTNAME}.config";
LDAPLIMLOGIN="uid=${ANONUID},${LDAPLOGIN}";
LDAPCONFLOGIN="uid=${CONFUID},${LDAPLOGIN}";

if [ -e /etc/mail/ldap ];then
  if [ ! -e /etc/ldap.secret ];then
    touch /etc/ldap.secret
    chmod 400 /etc/ldap.secret
    chown root.root /etc/ldap.secret
    ADMINPW="`cat /etc/mail/ldap`";
    echo -n "${ADMINPW}" > /etc/ldap.secret
  fi;
  rm /etc/mail/ldap
fi;

#Change the password if need be
if [ -e /var/spool/apache/htdocs/ns/config/ldap.newsecret ];then
  NEWROOTPW="`cat /var/spool/apache/htdocs/ns/config/ldap.newsecret`";
  CRYPT=`slappasswd -c '$1$%.8s' -s ${NEWROOTPW}`

  (cat &lt;&lt;EOF
dn: ${LDAPLOGIN}${VSUF}
changetype: modify
replace: userPassword
userPassword: ${CRYPT}
EOF
) |/usr/bin/ldapmodify -xD ${LDAPLOGIN} -y /etc/ldap.secret -h ${REPMASTER} -Z
	  
  if [ $? == 0 ];then
    ADMINPW="${NEWROOTPW}";
    touch /etc/ldap.secret
    chmod 400 /etc/ldap.secret
    chown root.root /etc/ldap.secret   
    echo -n "${ADMINPW}" > /etc/ldap.secret
    rm /var/spool/apache/htdocs/ns/config/ldap.newsecret
   else
    ADMINPW="`cat /etc/ldap.secret`";
  fi;
 else
  ADMINPW="`cat /etc/ldap.secret`";
fi;

/usr/sbin/setsmbpasswd

SNOMPW=`apg -M SNCL -n 1 -m 10 -E '!$%^~|\`\",'\'`

(cat &lt;&lt;EOF
dn: cn=Snom,ou=snom${VSUF}
changetype: modify
replace: userPassword
userPassword: ${SNOMPW}
EOF
) |/usr/bin/ldapmodify -xD ${LDAPLOGIN} -y /etc/ldap.secret -h ${REPMASTER} -Z

if [ ! -e /etc/.install ] &amp;&amp; [ -e /var/spool/pgsql/postmaster.pid ] &amp;&amp; [ -d /proc/`cat /var/spool/pgsql/postmaster.pid |head -1` ];then
  /usr/sbin/rebootphone &amp;
fi;

if [ ! -e /var/spool/apache/htdocs/ns/config/${LIMFILE} ];then
  ldapdelete -xD ${LDAPLOGIN} -y /etc/ldap.secret -h ${REPMASTER} -Z ${LDAPLIMLOGIN}${VSUF}
  LDAPLIMPW=`apg -M SNCL -n 1 -m 10 -E '!$%^~|\`\",'\'`
  CRYPT=`slappasswd -c '$1$%.8s' -s ${LDAPLIMPW}`

  (cat &lt;&lt;EOF
dn: ${LDAPLIMLOGIN}${VSUF}
uid: ${ANONUID}
objectClass: device
objectClass: uidObject
objectClass: simpleSecurityObject
cn: Priv seperation account (Limited)
userPassword: ${CRYPT}
EOF
) |/usr/bin/ldapadd -xD ${LDAPLOGIN} -y /etc/ldap.secret -h ${REPMASTER} -Z

  if [ $? == 0 ];then
    touch /var/spool/apache/htdocs/ns/config/${LIMFILE}
    chmod 600 /var/spool/apache/htdocs/ns/config/${LIMFILE}
    chown www.www /var/spool/apache/htdocs/ns/config/${LIMFILE}
    echo -n ${LDAPLIMPW} > /var/spool/apache/htdocs/ns/config/${LIMFILE}

    touch /etc/ldap.limited
    chmod 400 /etc/ldap.limited
    echo -n "${LDAPLIMPW}" > /etc/ldap.limited
  fi;
 else
  LDAPLIMPW=`cat /var/spool/apache/htdocs/ns/config/${LIMFILE}`
fi;

if [ ! -e /var/spool/apache/htdocs/ns/config/${CONFFILE} ];then
  ldapdelete -xD ${LDAPLOGIN} -y /etc/ldap.secret -h ${REPMASTER} -Z ${LDAPCONFLOGIN}${VSUF}
  LDAPCONFPW=`apg -M SNCL -n 1 -m 10 -E '!$%^~|\`\",/'\'`
  CRYPT=`slappasswd -c '$1$%.8s' -s ${LDAPCONFPW}`

  (cat &lt;&lt;EOF
dn: ${LDAPCONFLOGIN}${VSUF}
uid: ${CONFUID}
objectClass: device
objectClass: uidObject
objectClass: simpleSecurityObject
cn: Priv seperation account (Config)
userPassword: ${CRYPT}
EOF
) |/usr/bin/ldapadd -xD ${LDAPLOGIN} -y /etc/ldap.secret -h ${REPMASTER} -Z

  if [ $? == 0 ];then
    touch /var/spool/apache/htdocs/ns/config/${CONFFILE}
    chmod 600 /var/spool/apache/htdocs/ns/config/${CONFFILE}
    chown www.www /var/spool/apache/htdocs/ns/config/${CONFFILE}
    echo -n ${LDAPCONFPW} > /var/spool/apache/htdocs/ns/config/${CONFFILE}
  fi;
 else
  LDAPCONFPW=`cat /var/spool/apache/htdocs/ns/config/${CONFFILE}`
fi;

if [ -s /var/spool/apache/htdocs/ns/config/ldap.replica ] &amp;&amp; [ ! -e /etc/ldap-${FQDN} ];then
  ldapdelete -xD ${LDAPLOGIN} -y /etc/ldap.secret -h ${REPMASTER} -Z uid=${FQDN},ou=servers${VSUF}
  LDAPREPPW=`apg -M SNCL -n 1 -m 10 -E '!$%^~|\`\",'\'`
  CRYPT=`slappasswd -c '$1$%.8s' -s ${LDAPREPPW}`

  (cat &lt;&lt;EOF
dn: uid=${FQDN},ou=servers${VSUF}
uid: ${FQDN}
objectClass: device
objectClass: uidObject
objectClass: simpleSecurityObject
cn: Priv seperation account (Replication/Local)
userPassword: ${CRYPT}
EOF
) |/usr/bin/ldapadd -xD ${LDAPLOGIN} -y /etc/ldap.secret -h ${REPMASTER} -Z
  if [ $? == 0 ];then
    touch /etc/ldap-${FQDN}
    chmod 400 /etc/ldap-${FQDN}
    chown root.root /etc/ldap-${FQDN}
    echo -n ${LDAPREPPW} > /etc/ldap-${FQDN}
  fi;
fi;

if [ -e /tmp/.firstconfig ];then
  (cat &lt;&lt; EOF
hosts:          files
passwd:		files
shadow:		files
group:		files
automount:	files
EOF
) > /etc/nsswitch.conf
 else
  (cat &lt;&lt; EOF
hosts:          files
passwd:		files
shadow:		files
group:		files
automount:	files
EOF
) > /etc/nsswitch.conf.boot
  (cat &lt;&lt; EOF
hosts:          ${HOSTS}
passwd:		files ldap winbind
shadow:		files ldap
group:		files ldap winbind
automount:	files
EOF
) > /etc/nsswitch.conf.ldap

  if [ ! -e /etc/nsswitch.conf.local ];then
    cp /etc/nsswitch.conf.ldap /etc/nsswitch.conf
   else
    cp /etc/nsswitch.conf.local /etc/nsswitch.conf
  fi;
fi;

(cat &lt;&lt;EOF
TLS_CERT	/etc/openssl/server.signed.pem
TLS_KEY		/etc/openssl/serverkey.pem
TLS_REQCERT     allow
URI             ldaps://${LDAPSERVER}
SASL_SECPROPS   none
EOF
)>/etc/openldap/ldap.conf

(cat &lt;&lt;EOF
&lt;%
  \$ds=ldap_connect("ldaps://${REPMASTER}");    
  ldap_set_option(\$ds, LDAP_OPT_PROTOCOL_VERSION,3);
  \$LDAP_LIMIT_DN="${LDAPLIMLOGIN}";
  \$LDAP_LIMIT_PW="${LDAPLIMPW}";
  \$LOCAL_DOMAIN="${DOMAIN}";
  \$LDAP_BDN="${VSUF:1}";
%&gt;
EOF
)>/var/spool/apache/htdocs/ldap/ldapcon.inc
chmod 600 /var/spool/apache/htdocs/ldap/ldapcon.inc
chown 80.80 /var/spool/apache/htdocs/ldap/ldapcon.inc

(cat &lt;&lt;EOF
function FindProxyForURL(url, host) {
  if (isPlainHostName(host) || dnsDomainIs(host,".${DOMAIN}"))
    return "DIRECT";
  if (isInNet(host,"192.168.0.0","255.255.0.0"))
    return "DIRECT";
  if (isInNet(host,"10.0.0.0","255.0.0.0"))
    return "DIRECT";
  if (isInNet(host,"172.16.0.0","255.224.0.0"))
    return "DIRECT";
  return "PROXY ${FQDN}:3128; DIRECT";
}
EOF
)>/var/spool/apache/htdocs/proxy.pac

sed -i -e "s/^\$whereami.*/\$whereami = \"${DOMAIN}\";/" /opt/majordomo/majordomo.cf.orig

sed -e "s/^binddn.*$/binddn ${LDAPLIMLOGIN}/" \
    -e "s/^rootbinddn.*$/rootbinddn ${LDAPLOGIN}/" \
    -e "s/^bindpw.*$/bindpw ${LDAPLIMPW}/" \
    -e "s/^base.*$/base ${VSUF:1}/" \
    -e "s/^port.*/port 636/" \
    -e "s/^master_host.*$/master_host ${REPMASTER}/" \
    -e "s/^uri.*$/uri ldaps:\/\/${LDAPSERVER} ldaps:\/\/${REPMASTER}/" /etc/distrotech/ldap.conf.orig >  /etc/ldap.conf

chmod 644 /etc/ldap.conf
chown root.smbadm /etc/ldap.conf

(cat &lt;&lt;EOF
&lt;%
  \$db=pg_connect("host=127.0.0.1 dbname=exchange user=exchange password=${EXCHANGEPASS} sslmode=allow");
%&gt;
EOF
) > /var/spool/apache/htdocs/ldap/pgauth.inc
chmod 600 /var/spool/apache/htdocs/ldap/pgauth.inc
chown www.root /var/spool/apache/htdocs/ldap/pgauth.inc

(cat &lt;&lt;EOF
&lt;%
  \$db=mysql_connect("${SQLSERVER}","admin","${SQLADMINPASS}");
  mysql_select_db("asterisk");
%&gt;
EOF
) > /var/spool/apache/htdocs/ldap/myauth.inc
chmod 600 /var/spool/apache/htdocs/ldap/myauth.inc
chown www.root /var/spool/apache/htdocs/ldap/myauth.inc


(cat &lt;&lt;EOF
&lt;%
  date_default_timezone_set("Africa/Johannesburg");
  \$dtime=getdate();
  \$syshname="${HOSTNAME}";
  \$db=pg_connect("host=${SQLSERVER} dbname=asterisk user=asterisk password=${SQLVOIPPASS} sslmode=allow");
%&gt;
EOF
) &gt; /var/spool/apache/htdocs/cdr/auth.inc
chmod 600 /var/spool/apache/htdocs/cdr/auth.inc
chown www.root /var/spool/apache/htdocs/cdr/auth.inc

(cat &lt;&lt;EOF
&lt;%
  date_default_timezone_set("Africa/Johannesburg");
  \$dtime=getdate();
  \$db=pg_connect("host=${SQLSERVER} dbname=asterisk user=asterisk password=${SQLVOIPPASS} sslmode=allow");
%&gt;
EOF
) &gt; /var/lib/asterisk/agi-bin/auth.inc
chmod 600 /var/lib/asterisk/agi-bin/auth.inc
chown root.root /var/lib/asterisk/agi-bin/auth.inc

(cat &lt;&lt;EOF
[global]
dsn=Asterisk
username=asterisk
password=${SQLVOIPPASS}
loguniqueid=yes
dispositionstring=yes
table=cdr
usegmtime=no
EOF
) &gt; /etc/asterisk/cdr_odbc.conf

(cat &lt;&lt;EOF
[Asterisk]
dsn => Asterisk
pre-connect => yes
username => asterisk
password => ${SQLVOIPPASS}
pooling => yes
limit => 1023

[Master]
dsn => Master
pre-connect => yes
username => asterisk
password => ${SQLMVOIPPASS}
pooling => yes
limit => 1023
EOF
) &gt; /etc/asterisk/res_odbc.conf

#if [ -e /etc/asterisk/res_odbc.conf.local ];then
#  cat /etc/asterisk/res_odbc.conf.local >> /etc/asterisk/res_odbc.conf
#fi;

(cat &lt;&lt; EOF
#!/usr/bin/perl

use DBI;

\$dbh = DBI->connect("DBI:ODBC:Asterisk","asterisk","${SQLVOIPPASS}");
\$dbh->do("UPDATE users SET password='@ARGV[2]' WHERE mailbox = '@ARGV[1]' AND context='@ARGV[0]'");

EOF
)>/usr/sbin/voippass
chmod 700 /usr/sbin/voippass
chown root.root /usr/sbin/voippass


(cat &lt;&lt; EOF
#!/usr/bin/perl

use DBI;

\$dbh = DBI->connect("DBI:mysql:database=radius;host=${SQLRADIUSSERV};port=3306","radius","${SQLRADIUSPASS}");
\$asel=\$dbh->prepare("select CallingStationId  from radacct where FramedIPAddress='@ARGV[0]' AND AcctStopTime=''");
\$asel->execute;
@row=\$asel->fetchrow_array;

print @row[0] . "\n";

EOF
)>/usr/sbin/getmacfromip
chmod 700 /usr/sbin/getmacfromip
chown root.root /usr/sbin/getmacfromip


echo "ALTER USER exchange WITH PASSWORD '${EXCHANGEPASS}';" |/usr/bin/psql -h 127.0.0.1 -U pgsql exchange -f - > /dev/null 2>&amp;1
echo "ALTER USER asterisk WITH PASSWORD '${SQLVOIPPASS}';" |/usr/bin/psql -h 127.0.0.1 -U pgsql asterisk -f - > /dev/null 2>&amp;1
echo "ALTER USER radius WITH PASSWORD '${SQLRADIUSPASS}';" |/usr/bin/psql -h 127.0.0.1 -U pgsql template1 -f - > /dev/null 2>&amp;1
echo "ALTER USER pgsql WITH PASSWORD '${PGADMINPASS}';" |/usr/bin/psql -h 127.0.0.1 -U pgsql template1 -f - > /dev/null 2>&amp;1
echo "UPDATE realm SET domain='${DOMAIN}' WHERE id=0;" |/usr/bin/psql -h 127.0.0.1 -U pgsql asterisk -f - > /dev/null 2>&amp;1

(cat &lt;&lt; EOF
127.0.0.1:5432:*:pgsql:${PGADMINPASS}
127.0.0.1:5432:*:exchange:${EXCHANGEPASS}
127.0.0.1:5432:*:asterisk:${SQLVOIPPASS}
127.0.0.1:5432:*:phpgw:phpgw
EOF
)> /root/.pgpass
chmod 600 /root/.pgpass
chown root.root /root/.pgpass

#kill `cat /var/run/exchange4linux/exchange4linux.pid 2>/dev/null` > /dev/null 2>&amp;1
#/usr/bin/python /usr/local/exchange4linux/Server.pyc > /dev/null 2>&amp;1 &amp;

if [ -e /var/spool/apache/htdocs/horde/config/horde.php ];then
  sed -e "s/\$conf\['prefs'\]\['params'\]\['password'\] = .*;/\$conf['prefs']['params']['password'] = '${HORDEPASS}';/" \
      -e "s/\$conf\['prefs'\]\['params'\]\['hostspec'\] = .*;/\$conf['prefs']['params']['hostspec'] = '${SQLSERVER}';/" \
         /var/spool/apache/htdocs/horde/config/horde.php.orig > /var/spool/apache/htdocs/horde/config/horde.php
  chown www.www /var/spool/apache/htdocs/horde/config/horde.php

  if [ -e /var/spool/apache/htdocs/horde/kronolith/config/conf.php ];then
    sed -e "s/\$conf\['calendar'\]\['params'\]\['password'\] = .*;/\$conf['calendar']['params']['password'] = '${HORDEPASS}';/" \
        -e "s/\$conf\['calendar'\]\['params'\]\['hostspec'\] = .*;/\$conf['calendar']['params']['hostspec'] = '${SQLSERVER}';/" \
           /var/spool/apache/htdocs/horde/kronolith/config/conf.php.orig > /var/spool/apache/htdocs/horde/kronolith/config/conf.php
    chown www.www /var/spool/apache/htdocs/horde/kronolith/config/conf.php
  fi;
  if [ -e /var/spool/apache/htdocs/horde/mnemo/config/conf.php ];then
    sed -e "s/\$conf\['storage'\]\['params'\]\['password'\] = .*;/\$conf['storage']['params']['password'] = '${HORDEPASS}';/" \
        -e "s/\$conf\['storage'\]\['params'\]\['hostspec'\] = .*;/\$conf['storage']['params']['hostspec'] = '${SQLSERVER}';/" \
           /var/spool/apache/htdocs/horde/mnemo/config/conf.php.orig > /var/spool/apache/htdocs/horde/mnemo/config/conf.php
    chown www.www /var/spool/apache/htdocs/horde/mnemo/config/conf.php
  fi;
  if [ -e /var/spool/apache/htdocs/horde/nag/config/conf.php ];then
    sed -e "s/\$conf\['storage'\]\['params'\]\['password'\] = .*;/\$conf['storage']['params']['password'] = '${HORDEPASS}';/" \
        -e "s/\$conf\['storage'\]\['params'\]\['hostspec'\] = .*;/\$conf['storage']['params']['hostspec'] = '${SQLSERVER}';/" \
           /var/spool/apache/htdocs/horde/nag/config/conf.php.orig > /var/spool/apache/htdocs/horde/nag/config/conf.php
    chown www.www /var/spool/apache/htdocs/horde/nag/config/conf.php
  fi;
  if [ -e /var/spool/apache/htdocs/horde/turba/config/sources.php ];then
    sed -e "s/.*'password'.*'.*'/        'password' => '${HORDEPASS}'/" \
        -e "s/.*'server'.*'.*'/        'server' => '${LDAPSERVER}'/" \
        -e "s/.*'hostspec'.*/        'hostspec' => '${SQLSERVER}',/" \
           /var/spool/apache/htdocs/horde/turba/config/sources.php.orig > /var/spool/apache/htdocs/horde/turba/config/sources.php
    chown www.www /var/spool/apache/htdocs/horde/turba/config/sources.php
  fi;
fi;

if [ -e /var/lib/asterisk/agi-bin/db_php_lib/defines.php.orig ];then
  sed -e "s/SQLPASS/${SQLVOIPPASS}/" /var/lib/asterisk/agi-bin/db_php_lib/defines.php.orig > /var/lib/asterisk/agi-bin/db_php_lib/defines.php
fi;

sed -e "s/pass=\"\*\*\*\*\"/pass=\"admin\"/" /etc/ulogd.conf.orig > /etc/ulogd.conf

if [ -e /var/spool/apache/htdocs/dbadmin/config.inc.php.orig ];then
  sed -e "s/\*\*\*\*\*/${SQLCTRLPASS}/" /var/spool/apache/htdocs/dbadmin/config.inc.php.orig > /var/spool/apache/htdocs/dbadmin/config.inc.php
fi;


(cat &lt;&lt;EOF
&lt;%
  \$link = mysql_connect("localhost", "logview", "${SQLULOGDPASS}");
  mysql_select_db("networksentry_log");
%&gt;
EOF
)> /var/spool/apache/htdocs/logs/ulogauth.php
chmod 640 /var/spool/apache/htdocs/logs/ulogauth.php
chown www.www /var/spool/apache/htdocs/logs/ulogauth.php

(cat &lt;&lt;EOF
&lt;%
\$db=pg_connect("host=${SQLRADIUSSERV} dbname=radius user=radius password=${SQLRADIUSPASS} sslmode=allow")
%&gt;
EOF
)> /var/spool/apache/htdocs/radius/opendb.inc
chown www.www /var/spool/apache/htdocs/radius/opendb.inc
chmod 640 /var/spool/apache/htdocs/radius/opendb.inc

sed -e "s/\$password=.*;/\$password=\"${SQLULOGDPASS}\";/" /etc/procmlog.orig > /usr/sbin/procmlog
chmod 750 /usr/sbin/procmlog
chown root.root /usr/sbin/procmlog

sed -e "s/\$password=.*;/\$password=\"${SQLULOGDPASS}\";/" /etc/rrdlog.orig > /usr/bin/rrdlog
chmod 750 /usr/bin/rrdlog
chown root.root /usr/bin/rrdlog

sed -e "s/^\$password=.*;/\$password=\"${HORDEPASS}\";/" \
    -e "s/^\$hostname=.*;/\$hostname=\"${SQLSERVER}\";/" /etc/procmfilter.orig > /usr/sbin/procmfilter
chmod 750 /usr/sbin/procmfilter
chown root.root /usr/sbin/procmfilter

sed -e "s/DOMAIN/${DOMAIN}/" -e "s/TSIGKEY/${DOMTSIGKEY}/" \
    -e "s/SERVER/${DNSSERV}/" /etc/wins_hook.orig > /usr/sbin/wins_hook
chmod 700 /usr/sbin/wins_hook
chown root.root /usr/sbin/wins_hook

sed -e "s/^\$password=.*;/\$password=\"${SQLRADIUSPASS}\";/" \
    -e "s/^\$hostname=.*;/\$hostname=\"${SQLRADIUSSERV}\";/" /etc/radcheck.orig > /usr/sbin/radcheck
chmod 750 /usr/sbin/radcheck
chown root.root /usr/sbin/radcheck

sed -e "s/^\$password=.*;/\$password=\"${SQLVOIPPASS}\";/" /etc/asterisk/pannel/genbut.pl.orig > /etc/asterisk/pannel/genbut.pl
chmod 750 /etc/asterisk/pannel/genbut.pl
chown root.root /etc/asterisk/pannel/genbut.pl

webserver stopsql > /dev/null 2>&amp;1

if [ -e /tmp/mysql.sock ];then
  rm /tmp/mysql.sock
fi;

(/usr/libexec/mysqld --basedir=/usr --datadir=/var/spool/mysql --user=mysql --pid-file=/var/spool/mysql/mysqld.pid --skip-external-locking --port=3306 --socket=/tmp/mysql.sock --skip-grant-tables > /dev/null 2>&amp;1) &amp;
sleep 4
(cat &lt;&lt;_EOF_
USE mysql;
UPDATE user SET Password=PASSWORD('${SQLADMINPASS}') WHERE User='admin';
FLUSH PRIVILEGES;
_EOF_
) |mysql

killall mysqld > /dev/null 2>&amp;1
sleep 2
if [ "`/bin/pidof mysqld`" ];then
  killall -9 mysqld
fi;
#webserver stopsql > /dev/null 2>&amp;1

webserver startsql > /dev/null 2>&amp;1
sleep 2

(cat &lt;&lt;_EOF_
GRANT ALL ON *.* TO 'admin'@'localhost' IDENTIFIED BY '${SQLADMINPASS}' WITH GRANT OPTION;
GRANT ALL ON *.* TO 'admin'@'%' IDENTIFIED BY '${SQLADMINPASS}' WITH GRANT OPTION;
GRANT SELECT,INSERT,UPDATE,DELETE ON networksentry_log.* TO 'logview'@'%' IDENTIFIED BY '${SQLULOGDPASS}';
GRANT SELECT,INSERT,UPDATE,DELETE ON networksentry_log.* TO 'logview'@'localhost' IDENTIFIED BY '${SQLULOGDPASS}';
GRANT SELECT,INSERT,UPDATE,DELETE ON horde.* TO 'horde'@'%' IDENTIFIED BY '${HORDEPASS}';
GRANT ALL ON phpBB2.* TO 'phpBB2'@'localhost' IDENTIFIED BY '${SQLFORUMPASS}';
GRANT SELECT ON mysql.db TO 'control'@'localhost' IDENTIFIED BY '${SQLCTRLPASS}';
GRANT SELECT (Host,Db,User,Table_name,Table_priv,Column_priv) ON mysql.tables_priv TO 'control'@'localhost';
GRANT SELECT (Host,User,Select_priv,Insert_priv,Update_priv,Delete_priv,Create_priv,Drop_priv,Reload_priv,Shutdown_priv,Process_priv,File_priv,Grant_priv,References_priv,Index_priv,Alter_priv) ON mysql.user TO 'control'@'localhost';
GRANT SELECT,INSERT,UPDATE,DELETE ON phpmyadmin.* to 'control'@'localhost';
FLUSH PRIVILEGES;
_EOF_
) |mysql -u admin -p${SQLADMINPASS}

if [ ! -d /var/spool/mysql/phpmyadmin ] &amp;&amp; [ -e /usr/bin/mysql_pmadb.sql ];then
  mysql -u admin -p${SQLADMINPASS} &lt; /usr/bin/mysql_pmadb.sql
fi;

if [ ! -d /var/spool/mysql/phpBB2 ];then
  mysql -u admin -p${SQLADMINPASS} &lt; /var/spool/apache/htdocs/phpBB2/schema/phpBB2_struct.sql
  mysql -u admin -p${SQLADMINPASS} &lt; /var/spool/apache/htdocs/phpBB2/schema/phpBB2_data.sql
fi;

#if [ ! -d /var/spool/mysql/storephront ];then
#  mysql -u admin -p${SQLADMINPASS} &lt; /usr/bin/mysql_sfront.sql
#fi;

sed -e "s/.*[^_]server.*=.*/                server  = ${LDAPSERVER}/" \
  -e "s/.*identity.*=.*/                identity = ${LDAPLOGIN}/" \
  -e "s/.*password.*=.*/                password = ${ADMINPW}/" \
  -e "s/.*basedn.*=.*/                basedn = ${VSUF:1}/" \
 /etc/raddb/radiusd.conf.orig > /etc/raddb/radiusd.conf
chmod 640 /etc/raddb/radiusd.conf
chown root.root /etc/raddb/radiusd.conf

sed -e "s/server.*=.*/server = \"${SQLRADIUSSERV}\"/" \
  -e "s/password.*=.*/password = \"${SQLRADIUSPASS}\"/" \
 /etc/raddb/sql.conf.orig > /etc/raddb/sql.conf
chmod 640 /etc/raddb/sql.conf
chown root.root /etc/raddb/sql.conf

sed -e "s/^\$dbpasswd = '.*'/\$dbpasswd = '${SQLFORUMPASS}'/"\
       /var/spool/apache/htdocs/phpBB2/config.php.orig >\
       /var/spool/apache/htdocs/phpBB2/config.php

kill -9 `cat /var/run/radiusd.pid 2>/dev/null` > /dev/null 2>&amp;1
sleep 5
if [ -e /var/log/radutmp ];then
  rm /var/log/radutmp
fi
/usr/sbin/radiusd > /dev/null 2>&amp;1

if [ ! -e /etc/apache/sogo.conf ];then
  touch /etc/apache/sogo.conf
fi;

if [ ! -d /opt/apache2 ];then
  touch /tmp/httpd.conf
  chmod 600 /tmp/httpd.conf
  chown root.root /tmp/httpd.conf
  sed -e "s/^Listen 0.0.0.0:80.*/Listen ${INTIP}:80/"  \
      -e "s/^Listen 80.*/Listen ${INTIP}:80/" \
      -e "s/AuthLDAPUrl ldap:\/\/127.0.0.1/AuthLDAPUrl ldap:\/\/${LDAPSERVER}/" \
      -e "s/AuthLDAPBindDN.*/AuthLDAPBindDN ${LDAPLIMLOGIN}/" \
      -e "s/AuthLDAPBindPassword admin/AuthLDAPBindPassword ${LDAPLIMPW}/" \
      -e "s/AuthLDAPStartTLS on/AuthLDAPStartTLS ${ISLDAPTLS}/" \
      -e "s/(^|^#)ServerName.*/ServerName ${FQDN}/" \
      -e "s/LOCAL_SERVER_NAME/${FQDN}/" /etc/apache/httpd.conf.orig | grep -vE "(^#)" >  /tmp/httpd.conf
  HCDIFF=`diff /tmp/httpd.conf /etc/apache/httpd.conf`
 else
  touch /tmp/httpd.conf.1 /tmp/httpd.conf
  chmod 600 /tmp/httpd.conf.1 /tmp/httpd.conf
  chown root.root /tmp/httpd.conf.1 /tmp/httpd.conf
  sed -e "s/^Listen 0.0.0.0:80.*/Listen ${INTIP}:80/"  \
      -e "s/^Listen 80.*/Listen ${INTIP}:80/" \
      -e "s/AuthLDAPUrl ldap:\/\/127.0.0.1/AuthLDAPUrl ldap:\/\/${LDAPSERVER}/" \
      -e "s/AuthLDAPBindDN.*/AuthLDAPBindDN ${LDAPLIMLOGIN}/" \
      -e "s/AuthLDAPBindPassword admin/AuthLDAPBindPassword ${LDAPLIMPW}/" \
      -e "s/AuthLDAPStartTLS on/AuthLDAPStartTLS ${ISLDAPTLS}/" \
      -e "s/LOCAL_SERVER_NAME/${FQDN}/" /opt/apache2/conf/httpd.conf.orig >  /tmp/httpd.conf.1
  if [ ! -e /opt/apache2/conf/httpd.conf ];then
    cp /opt/apache2/conf/httpd.conf.orig /opt/apache2/conf/httpd.conf
  fi;
  if [ -e /etc/httpd.conf.local ];then
     cat /tmp/httpd.conf.1 /etc/httpd.conf.local > /tmp/httpd.conf
     rm /tmp/httpd.conf.1
    else
     mv /tmp/httpd.conf.1 /tmp/httpd.conf
  fi;
  HCDIFF=`diff /tmp/httpd.conf /opt/apache2/conf/httpd.conf`
fi;

if [ "$HCDIFF" ] &amp;&amp; [ ! -e /etc/.install ] &amp;&amp; [ ! -e /etc/.cdrom ];then
  if [ ! -d /opt/apache2 ];then
    apachectl stop > /dev/null 2>&amp;1
   else
    /usr/sbin/apachectl stop > /dev/null 2>&amp;1
  fi;
  sleep 1;
  while [ -s /var/run/httpd.pid -a "$tflag" != "xxxxxxxxx" ] ;do 
    if [ ! -d /opt/apache2 ];then
      apachectl stop > /dev/null 2>&amp;1;
     else
      /opt/apache2/bin/apachectl stop > /dev/null 2>&amp;1
    fi;
    sleep 2;
    tflag=x$tflag;
  done

  tflag="";
  while [ "`pidof httpd`" ] &amp;&amp; [ "$tflag" != "xxxxxxxxx" ];do 
    for htpid in `pidof httpd`;do
      kill -9 ${htpid};
    done
    sleep 1;
    tflag=x$tflag;
  done

  if [ -e /var/run/httpd.pid ];then
    rm /var/run/httpd.pid
  fi;

  if [ ! -d /opt/apache2 ];then
    mv /tmp/httpd.conf /etc/apache
   else
    mv /tmp/httpd.conf /opt/apache2/conf/httpd.conf
  fi;

  if [ -e /etc/ipsec.d/cacerts/cacert.pem ] &amp;&amp; [ -e /etc/openssl/server.signed.pem ] &amp;&amp; [ ! -e /etc/.networksentry-lite ];then
    if [ ! -d /opt/apache2 ];then
      /usr/sbin/apachectl startssl  > /dev/null 2>&amp;1
     else
      if [ ! -e /etc/apache/vhosts2 ];then
        /usr/sbin/genwebmap
      fi;
      /opt/apache2/bin/apachectl start  > /dev/null 2>&amp;1
    fi;
   else
    /usr/sbin/apachectl stop > /dev/null 2>&amp;1
  fi

fi;
</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>#!/bin/bash&#xa;&#xa;</xsl:text>
  <xsl:value-of select="concat('DOMAIN=&quot;',/config/DNS/Config/Option[@option = 'Domain'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('HOSTNAME=&quot;',/config/DNS/Config/Option[@option = 'Hostname'],'&quot;;',$nl)"/>
  <xsl:choose>
    <xsl:when test="/config/LDAP/Replica != ''">
      <xsl:value-of select="concat('REPMASTER=&quot;',/config/LDAP/Replica,'&quot;;',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('REPMASTER=&quot;',/config/LDAP/Config/Option[@option = 'Server'],'&quot;;',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:value-of select="concat('LDAPLOGIN=&quot;',/config/LDAP/Config/Option[@option = 'Login'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('LDAPSERVER=&quot;',/config/LDAP/Config/Option[@option = 'Server'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('SQLSERVER=&quot;',/config/SQL/Option[@option = 'Server'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('SQLRADIUSSERV=&quot;',/config/SQL/Option[@option = 'RadiusServ'],'&quot;;',$nl)"/>
  <xsl:choose>
    <xsl:when test="($pdns != '') and ($pdns != $intip)">
      <xsl:value-of select="concat('DNSSERV=&quot;',$pdns,'&quot;;',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('DNSSERV=&quot;','127.0.0.1','&quot;;',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:value-of select="concat('SQLADMINPASS=&quot;',/config/SQL/Option[@option = 'Password'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('SQLFORUMPASS=&quot;',/config/SQL/Option[@option = 'Forum'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('SQLRADIUSPASS=&quot;',/config/SQL/Option[@option = 'Radius'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('SQLVOIPPASS=&quot;',/config/SQL/Option[@option = 'Asterisk'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('HORDEPASS=&quot;',/config/SQL/Option[@option = 'WebmailPass'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('SQLMVOIPPASS=&quot;',/config/SQL/Option[@option = 'MAsterisk'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('EXCHANGEPASS=&quot;',/config/SQL/Option[@option = 'PGExchange'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('PGADMINPASS=&quot;',/config/SQL/Option[@option = 'PGAdmin'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('SQLCTRLPASS=&quot;',/config/SQL/Option[@option = 'Control'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('SQLULOGDPASS=&quot;',/config/SQL/Option[@option = 'IDPass'],'&quot;;',$nl)"/>
  <xsl:value-of select="concat('DOMTSIGKEY=&quot;',$tsigkey,'&quot;;',$nl)"/>
  <xsl:value-of select="concat('INTIP=&quot;',$intip,'&quot;;',$nl)"/>
  <xsl:choose>
    <xsl:when test="contains(/config/DNS/Config/Option[@option = 'Domain'],'.local')">
      <xsl:value-of select="concat('HOSTS=&quot;','files dns ldap wins','&quot;;',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('HOSTS=&quot;','files mdns_minimal [NOTFOUND=return] dns ldap wins mdns','&quot;;',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:call-template name="script"/>
</xsl:template>
</xsl:stylesheet>

