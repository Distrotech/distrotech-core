<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="extiface" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="extip" select="/config/IP/Interfaces/Interface[text() = $extiface]/@ipaddr"/>
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>
<xsl:variable name="fqdn" select="concat($hname,'.',$domain)"/>

<xsl:template name="clessb2m">
  <xsl:param name="bits"/>
  <xsl:param name="output" select="'1'"/>

  <xsl:choose>
    <xsl:when test="$bits != 0">
      <xsl:call-template name="clessb2m">
        <xsl:with-param name="bits" select="$bits - 1"/>
        <xsl:with-param name="output" select="$output * 2"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="256 - $output"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="bitstomask">
  <xsl:param name="quad" select="0"/>
  <xsl:param name="bits"/>

  <xsl:if test="$quad &lt; 4">
    <xsl:if test="$quad &lt; floor($bits div 8)">
      <xsl:text>255</xsl:text>
    </xsl:if>
    <xsl:if test="$quad &gt; floor($bits div 8)">
      <xsl:text>0</xsl:text>
    </xsl:if>
    <xsl:if test="$quad = floor($bits div 8)">
      <xsl:call-template name="clessb2m">
        <xsl:with-param name="bits" select="8 - ($bits mod 8)"/>
      </xsl:call-template>
    </xsl:if>
    <xsl:if test="$quad &lt; 3">
      <xsl:text>.</xsl:text>
    </xsl:if>
    <xsl:call-template name="bitstomask">
      <xsl:with-param name="bits" select="$bits"/>
      <xsl:with-param name="quad" select="$quad+1"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="nmtocidr">
  <xsl:param name="netmask"/>
  <xsl:param name="value" select="0"/>

  <xsl:variable name="next" select="substring-after($netmask,'.')"/>
  <xsl:variable name="cur" select="substring-before($netmask,'.')"/>

  <xsl:choose>
    <xsl:when test="$cur != ''">
      <xsl:choose>
        <xsl:when test="$cur = '255'">
          <xsl:call-template name="nmtocidr">
            <xsl:with-param name="netmask" select="$next"/>
            <xsl:with-param name="value" select="$value+8"/>
          </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
          <xsl:choose>
            <xsl:when test="$cur = 0">
              <xsl:call-template name="nmtocidr">
                <xsl:with-param name="netmask" select="$next"/>
                <xsl:with-param name="value" select="$value"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
              <xsl:call-template name="nmtocidr">
                <xsl:with-param name="netmask" select="concat(256 - (256 - $cur) div 2,'.',$next)"/>
                <xsl:with-param name="value" select="$value - 1"/>
              </xsl:call-template>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$value"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Access">
  <xsl:choose>
    <xsl:when test=". = 'false'">
      <xsl:value-of select="concat('acl squiddeny src ',@ipaddr,'/')"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('acl squidaccess src ',@ipaddr,'/')"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:choose>
    <xsl:when test="contains(@subnet,'.')">
      <xsl:call-template name="nmtocidr">
        <xsl:with-param name="netmask" select="concat(@subnet,'.')"/>
      </xsl:call-template>
      <xsl:text>&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat(@subnet,$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Interface">
  <xsl:param name="subnet" select="@subnet"/>
  <xsl:param name="acl" select="'squidaccess src '"/>
  <xsl:param name="useipaddr"/>

  <xsl:choose>
    <xsl:when test="$useipaddr = 1">
      <xsl:value-of select="concat('acl ',$acl,@ipaddr,'/',$subnet,$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('acl ',$acl,@nwaddr,'/',$subnet,$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Tunnel">
  <xsl:param name="useipaddr"/>
  <xsl:param name="acl" select="'squidaccess src '"/>

  <xsl:choose>
    <xsl:when test="$useipaddr = 1">
      <xsl:value-of select="concat('acl ',$acl,@local,'/32',$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('acl ',$acl,@nwaddr,'/30',$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Route">
  <xsl:param name="acl" select="'squidaccess src '"/>

  <xsl:value-of select="concat('acl ',$acl,@network,'/',@subnet,$nl)"/>
</xsl:template>

<xsl:template match="WWW">
  <xsl:value-of select="concat('acl redirect dst ',@ipaddr,'/32',$nl)"/>
</xsl:template>

<xsl:template match="Bypass">
  <xsl:value-of select="concat('acl squidbypass dst ',.,'/')"/>
  <xsl:choose>
    <xsl:when test="contains(@subnet,'.')">
      <xsl:call-template name="nmtocidr">
        <xsl:with-param name="netmask" select="concat(@subnet,'.')"/>
      </xsl:call-template>
      <xsl:text>&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat(@subnet,$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Modem">
  <xsl:value-of select="concat('acl squidaccess src ',@remote,'/32',$nl)"/>
</xsl:template>

<xsl:template match="WiFi">
  <xsl:value-of select="concat('acl squidaccess src ',/config/IP/Interfaces/Interface[. = current()]/@nwaddr,'/',
       /config/IP/Interfaces/Interface[. = current()]/@subnet,$nl)"/>
</xsl:template>

<xsl:template name="cidraccess">
  <xsl:param name="cidr"/>
  <xsl:param name="action"/>
  <xsl:variable name="ipaddr" select="substring-before($cidr,'/')"/>
  <xsl:variable name="mask" select="substring-after($cidr,'/')"/>

  <xsl:if test="$cidr != ''">
    <xsl:value-of select="concat('acl squidaccess src ',$ipaddr,'/',$mask,$nl)"/>
  </xsl:if>
</xsl:template>

<xsl:template name="getaccess">
  <xsl:choose>
    <xsl:when test="count(/config/Proxy/SquidAccess/Access) != 0">
      <xsl:apply-templates select="/config/Proxy/SquidAccess/Access[. = 'false']"/>
      <xsl:text>acl squidaccess src 127.0.0.1/32&#xa;</xsl:text>
      <xsl:apply-templates select="/config/Proxy/SquidAccess/Access[. = 'true']"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>acl squidaccess src 127.0.0.1/32&#xa;</xsl:text>
      <xsl:apply-templates select="/config/IP/Interfaces/Interface[((. != $extiface) or ($extcon = 'ADSL')) and 
                                   (@ipaddr != '0.0.0.0') and (@subnet != '32')]"/>
      <xsl:apply-templates select="/config/IP/GRE/Tunnels/Tunnel"/>
      <xsl:apply-templates select="/config/IP/Routes/Route"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:apply-templates select="/config/IP/WiFi[@type = 'Hotspot']"/>
  <xsl:apply-templates select="/config/Radius/RAS/Modem"/>


  <xsl:apply-templates select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32')]">
    <xsl:with-param name="subnet" select="'32'"/>
    <xsl:with-param name="useipaddr" select="1"/>
  </xsl:apply-templates>

  <xsl:apply-templates select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:with-param name="useipaddr" select="1"/>
  </xsl:apply-templates>

  <xsl:call-template name="cidraccess">
    <xsl:with-param name="cidr" select="/config/Radius/Config/Option[@option = 'PPPoE']"/>
    <xsl:with-param name="action" select="'Allow'"/>
  </xsl:call-template>

  <xsl:call-template name="cidraccess">
    <xsl:with-param name="cidr" select="/config/IP/SysConf/Option[@option = 'L2TPNet']"/>
    <xsl:with-param name="action" select="'Allow'"/>
  </xsl:call-template>

  <xsl:apply-templates select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32')]">
    <xsl:with-param name="subnet" select="'32'"/>
    <xsl:with-param name="useipaddr" select="1"/>
    <xsl:with-param name="acl" select="'local_sites dst '"/>
  </xsl:apply-templates>

  <xsl:apply-templates select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:with-param name="useipaddr" select="1"/>
    <xsl:with-param name="acl" select="'local_sites dst '"/>
  </xsl:apply-templates>
  <xsl:text>acl local_sites dst 127.0.0.1/32&#xa;</xsl:text>

  <xsl:apply-templates select="/config/Proxy/Redirect/WWW"/>

  <xsl:apply-templates select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32')]">
    <xsl:with-param name="acl" select="'squidnoparent dst '"/>
  </xsl:apply-templates>
  <xsl:apply-templates select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:with-param name="acl" select="'squidnoparent dst '"/>
  </xsl:apply-templates>
  <xsl:apply-templates select="/config/IP/Routes/Route">
    <xsl:with-param name="acl" select="'squidnoparent dst '"/>
  </xsl:apply-templates>

  <xsl:apply-templates select="/config/Proxy/Bypass"/>
</xsl:template>

<xsl:template match="/config">
  <xsl:variable name="squiderrdir" select="'/usr/share/squid/errors/en'"/>

  <xsl:text>http_port 3128
http_port 8080 accel
http_port 3129 transparent
</xsl:text>
  <xsl:for-each select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet != '32') and (. != $intiface)]">
    <xsl:value-of select="concat('http_port ',@ipaddr,':80 transparent',$nl)"/>
  </xsl:for-each>
  <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:value-of select="concat('http_port ',@local,':80 transparent',$nl)"/>
  </xsl:for-each>
  <xsl:text>tcp_outgoing_address [::]
hosts_file /etc/hosts
cache_swap_low  80
cache_swap_high 90
maximum_object_size 4096 KB
minimum_object_size 0 KB
acl snmppublic snmp_community public
snmp_port 3401
ipcache_size 4096
ipcache_low  90
ipcache_high 95
fqdncache_size 4096
cache_mem </xsl:text><xsl:value-of select="concat(floor(/config/Proxy/Config/Option[@option = 'CacheSize'] div 10),' MB')"/><xsl:text>
cache_log /var/log/squid/cache.log
cache_dir ufs /var/spool/squid </xsl:text><xsl:value-of select="/config/Proxy/Config/Option[@option = 'CacheSize']"/><xsl:text> 16 256
cache_access_log /var/log/squid/access.log
cache_store_log none
#emulate_httpd_log off
mime_table /etc/squid/mime.conf
log_mime_hdrs off
pid_filename /var/run/squid.pid
error_directory </xsl:text><xsl:value-of select="$squiderrdir"/><xsl:text>
debug_options ALL,1
#log_fqdn on
client_netmask 255.255.255.255
ftp_user root@</xsl:text><xsl:value-of select="$domain"/><xsl:text>
#ftp_list_width 32
ftp_passive on
append_domain .</xsl:text><xsl:value-of select="$domain"/><xsl:text>
unlinkd_program </xsl:text><xsl:value-of select="$unlinkd"/><xsl:text>
request_header_max_size 10 KB
refresh_pattern         ^ftp:           1440    20%     10080
refresh_pattern         ^gopher:        1440    0%      1440
refresh_pattern         ^http:          0	0%      10
refresh_pattern         .               0       20%     4320
negative_ttl 2 minutes
positive_dns_ttl 6 hours
negative_dns_ttl 5 minutes
connect_timeout 120 seconds
peer_connect_timeout 30 seconds
read_timeout 15 minutes
request_timeout 30 seconds
client_lifetime 1 day
half_closed_clients on
pconn_timeout 120 seconds
shutdown_lifetime 30 seconds
auth_param basic realm Access To Internet
auth_param basic program /usr/libexec/pam_auth
authenticate_ip_ttl 300 second
auth_param basic children 10
external_acl_type unix_group %LOGIN /usr/libexec/squid_unix_group -p
acl duplicate max_user_ip -s 1
</xsl:text>
  <xsl:call-template name="getaccess"/>
  <xsl:text>acl authenticate proxy_auth REQUIRED
#acl all src all
#acl manager proto cache_object
#acl localhost src 127.0.0.1/32
acl SSL_ports port 443 563 666 990 5222 1863
acl Safe_ports port 80 21 990 443 563 70 210 3128 3129 8080 5222 1863
acl Safe_ports port 280         # http-mgmt
acl Safe_ports port 488         # gss-http
acl Safe_ports port 591         # filemaker
acl Safe_ports port 777         # multiling http
acl Safe_ports port 666         # network sentry admin
#Redirected Ports
acl CONNECT method CONNECT
acl norewrite external unix_group fullwebaccess
http_access allow manager localhost
http_access deny manager
http_access deny !Safe_ports
http_access deny CONNECT !SSL_ports
http_access allow squidaccess
</xsl:text>
  <xsl:if test="count(/config/Proxy/Bypass) &gt; 0">
    <xsl:text>always_direct allow squidbypass&#xa;</xsl:text>
  </xsl:if>
  <xsl:if test="count(/config/Proxy/SquidAccess/Access[. = 'false']) &gt; 0">
    <xsl:text>http_access deny squiddeny&#xa;</xsl:text>
  </xsl:if>
  <xsl:if test="count(/config/Proxy/Redirect/WWW) &gt; 0">
    <xsl:text>http_access allow redirect&#xa;</xsl:text>
  </xsl:if>
  <xsl:text>http_access allow local_sites
http_access deny duplicate
http_access allow authenticate
http_access deny all
http_access allow all
icp_access allow all
miss_access allow all
snmp_access allow snmppublic all
url_rewrite_program /usr/bin/squidGuard -c /etc/squid/filter.conf
url_rewrite_children </xsl:text><xsl:value-of select="/config/Proxy/Config/Option[@option = 'Redir']"/><xsl:text>
url_rewrite_access allow squidaccess
url_rewrite_access allow !norewrite
url_rewrite_access deny all
cache_mgr webmaster
cache_effective_user nobody
cache_effective_group nogroup
visible_hostname </xsl:text><xsl:value-of select="$fqdn"/><xsl:text>
#httpd_accel_host virtual
#httpd_accel_port 80
#httpd_accel_with_proxy on
#httpd_accel_uses_host_header on
logfile_rotate 10
forwarded_for off
log_icp_queries on
icp_hit_stale off
always_direct allow squidnoparent
always_direct allow local_sites
</xsl:text>
</xsl:template>
</xsl:stylesheet>
