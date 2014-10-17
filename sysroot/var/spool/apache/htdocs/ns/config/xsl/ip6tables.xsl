<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="fqdn" select="concat(/config/DNS/Config/Option[@option = 'Hostname'],'.',/config/DNS/Config/Option[@option = 'Domain'])"/>
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="extiface" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />
<xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'" />

<xsl:template name="firewall">
  <xsl:text>
if [ "$1" == "startup" ];then
  /usr/sbin/ip6tables -F
  /usr/sbin/ip6tables -N STATEOK
  /usr/sbin/ip6tables -N TCPCHECK
  /usr/sbin/ip6tables -N DENY
  /usr/sbin/ip6tables -N DEFIN
  /usr/sbin/ip6tables -N DEFOUT
  /usr/sbin/ip6tables -N EXTERNOUT
  /usr/sbin/ip6tables -N EXTERNIN
  /usr/sbin/ip6tables -N EXTERNFWD
  /usr/sbin/ip6tables -N INTERNOUT
  /usr/sbin/ip6tables -N INTERNIN
  /usr/sbin/ip6tables -N INTERNFWD
  /usr/sbin/ip6tables -N LOCALFWD
  /usr/sbin/ip6tables -N MCASTFWD
  /usr/sbin/ip6tables -N SYSIN
  /usr/sbin/ip6tables -N LOOPIN
  /usr/sbin/ip6tables -N LOOPOUT

  /usr/sbin/ip6tables -A INPUT -j STATEOK -m state --state RELATED,ESTABLISHED,INVALID
  /usr/sbin/ip6tables -A INPUT -j TCPCHECK -p tcp
  /usr/sbin/ip6tables -A INPUT -j ACCEPT -p icmpv6
  /usr/sbin/ip6tables -A INPUT -j ACCEPT -d ff02::/16 -s fe80::/64
  /usr/sbin/ip6tables -A INPUT -j ACCEPT -p udp --sport 547 --dport 546 -s fe80::/64 -d fe80::/64
  /usr/sbin/ip6tables -A INPUT -j LOOPIN -i lo
  /usr/sbin/ip6tables -A INPUT -j DROP ! -s fc00::/7 -d fc00::/7
  /usr/sbin/ip6tables -A INPUT -j EXTERNIN
  /usr/sbin/ip6tables -A INPUT -j INTERNIN
  /usr/sbin/ip6tables -A INPUT -j DEFIN -i sit0 -s 2002::/16
  /usr/sbin/ip6tables -A INPUT -j DEFIN -i sit1
</xsl:text>
  <xsl:for-each select="/config/IP/ADSL/Links/Link">
    <xsl:value-of select="concat('  /usr/sbin/ip6tables -A INPUT -j DEFIN -i sit1.',position(),$nl)"/>
  </xsl:for-each>
  <xsl:text>  /usr/sbin/ip6tables -A INPUT -j DENY

  /usr/sbin/ip6tables -A OUTPUT -j STATEOK -m state --state RELATED,ESTABLISHED,INVALID
  /usr/sbin/ip6tables -A OUTPUT -j TCPCHECK -p tcp
  /usr/sbin/ip6tables -A OUTPUT -j ACCEPT -p icmpv6
  /usr/sbin/ip6tables -A OUTPUT -j ACCEPT -d ff02::/16 -s fe80::/64
  /usr/sbin/ip6tables -A OUTPUT -j ACCEPT -p udp -s fe80::/64 -d fe80::/64 --sport 547 --dport 546
  /usr/sbin/ip6tables -A OUTPUT -j LOOPOUT -o lo
  /usr/sbin/ip6tables -A OUTPUT -j INTERNOUT
  /usr/sbin/ip6tables -A OUTPUT -j DROP -s fc00::/7 ! -d fc00::/7
  /usr/sbin/ip6tables -A OUTPUT -j EXTERNOUT
  /usr/sbin/ip6tables -A OUTPUT -j ACCEPT -o sit0 -d 2002::/16
  /usr/sbin/ip6tables -A OUTPUT -j ACCEPT -o sit1
</xsl:text>
  <xsl:for-each select="/config/IP/ADSL/Links/Link">
    <xsl:value-of select="concat('  /usr/sbin/ip6tables -A OUTPUT -j ACCEPT -o sit1.',position(),$nl)"/>
  </xsl:for-each>
  <xsl:text>  /usr/sbin/ip6tables -A OUTPUT -j DENY

  /usr/sbin/ip6tables -A FORWARD -j STATEOK -m state --state RELATED,ESTABLISHED,INVALID
  /usr/sbin/ip6tables -A FORWARD -j TCPCHECK -p tcp
  /usr/sbin/ip6tables -A FORWARD -j ACCEPT -p icmpv6
  /usr/sbin/ip6tables -A FORWARD -j INTERNFWD
  /usr/sbin/ip6tables -A FORWARD -j DROP -s fc00::/7 ! -d fc00::/7
  /usr/sbin/ip6tables -A FORWARD -j DROP -d fc00::/7 ! -s fc00::/7
  /usr/sbin/ip6tables -A FORWARD -j ACCEPT -o sit0 -d 2002::/16
  /usr/sbin/ip6tables -A FORWARD -j ACCEPT -o sit1
</xsl:text>
  <xsl:for-each select="/config/IP/ADSL/Links/Link">
    <xsl:value-of select="concat('  /usr/sbin/ip6tables -A FORWARD -j ACCEPT -o sit1.',position(),$nl)"/>
  </xsl:for-each>
  <xsl:text>  /usr/sbin/ip6tables -A FORWARD -j EXTERNFWD
  /usr/sbin/ip6tables -A FORWARD -j DENY

  #Allow Related/Established Traffic
  /usr/sbin/ip6tables -A STATEOK -j DROP -m state --state INVALID
  /usr/sbin/ip6tables -A STATEOK -j ACCEPT

  /usr/sbin/ip6tables -A TCPCHECK -j DROP -p tcp --tcp-flags SYN,FIN SYN,FIN
  /usr/sbin/ip6tables -A TCPCHECK -j DROP -p tcp --tcp-flags SYN,RST SYN,RST
  /usr/sbin/ip6tables -A TCPCHECK -j ACCEPT -p tcp --tcp-flags SYN,ACK SYN,ACK
  /usr/sbin/ip6tables -A TCPCHECK -j ACCEPT -p tcp --tcp-flags SYN,ACK,RST RST
  /usr/sbin/ip6tables -A TCPCHECK -j ACCEPT -p tcp --tcp-flags SYN,ACK,PSH ACK,PSH
  /usr/sbin/ip6tables -A TCPCHECK -j ACCEPT -p tcp --tcp-flags SYN,ACK ACK

  #Multicast Forward
  /usr/sbin/ip6tables -A MCASTFWD -j ACCEPT -d ff02::/16
  /usr/sbin/ip6tables -A MCASTFWD -j ACCEPT -s ff02::/16

  #Deny / Reject
  /usr/sbin/ip6tables -A DENY -j ACCEPT -p tcp --tcp-flags SYN,PSH PSH -m length --length 40
  /usr/sbin/ip6tables -A DENY -j ACCEPT -p tcp --tcp-flags SYN,RST RST -m length --length 40
  /usr/sbin/ip6tables -A DENY -j ACCEPT -p tcp --tcp-flags SYN,FIN FIN -m length --length 40
  #/usr/sbin/ip6tables -A DENY -j ULOG
  /usr/sbin/ip6tables -A DENY -j LOG -p ! tcp --log-level info --log-ip-options --log-prefix "rejected packet "
  /usr/sbin/ip6tables -A DENY -j LOG -p tcp --log-level info --log-ip-options --log-tcp-options --log-tcp-sequence --log-prefix "rejected packet "
  /usr/sbin/ip6tables -A DENY -j REJECT -p tcp --reject-with tcp-reset
  /usr/sbin/ip6tables -A DENY -j REJECT
fi;

/usr/sbin/ip6tables -F DEFIN
/usr/sbin/ip6tables -F DEFOUT
/usr/sbin/ip6tables -F LOOPIN
/usr/sbin/ip6tables -F LOOPOUT

#External Multicast Traffic
/usr/sbin/ip6tables -I DEFIN -j ACCEPT -d ff02::/16
</xsl:text>
  <xsl:for-each select="/config/IP/Interfaces/Interface">
    <xsl:value-of select="concat($nl,'#',@name,$nl)"/>
    <xsl:choose>
      <xsl:when test="(. != $extiface) or ($extcon = 'ADSL')">
        <xsl:value-of select="concat('/usr/sbin/ip6tables -A INTERNIN -i ',.,' -j SYSIN -m state --state NEW',$nl)"/>
        <xsl:value-of select="concat('/usr/sbin/ip6tables -A INTERNOUT -o ',.,' -j ACCEPT',$nl)"/>
        <xsl:value-of select="concat('/usr/sbin/ip6tables -A INTERNFWD -i ',.,' -o ',.,' -j MCASTFWD',$nl)"/>
        <xsl:value-of select="concat('/usr/sbin/ip6tables -A INTERNFWD -i ',.,' -j RETURN',$nl)"/>
        <xsl:value-of select="concat('/usr/sbin/ip6tables -A INTERNFWD -j LOCALFWD -o ',.,$nl)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat('/usr/sbin/ip6tables -A EXTERNIN -i ',.,' -j DEFIN',$nl)"/>
        <xsl:value-of select="concat('/usr/sbin/ip6tables -A EXTERNOUT -o ',.,' -j ACCEPT',$nl)"/>
        <xsl:value-of select="concat('/usr/sbin/ip6tables -A EXTERNFWD -o ',.,' -j ACCEPT',$nl)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:for-each>

  <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:value-of select="concat($nl,'#',.,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/ip6tables -A INTERNIN -i gtun',position()-1,' -j SYSIN -m state --state NEW',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/ip6tables -A INTERNOUT -o gtun',position()-1,' -j ACCEPT',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/ip6tables -A INTERNFWD -i gtun',position()-1,' -o gtun',position()-1,' -j MCASTFWD',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/ip6tables -A INTERNFWD -i gtun',position()-1,' -j RETURN',$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/ip6tables -A INTERNFWD -j LOCALFWD -o gtun',position()-1,$nl)"/>
  </xsl:for-each>

  <xsl:text>&#xa;#Deny All non internal traffic&#xa;</xsl:text>
  <xsl:text>/usr/sbin/ip6tables -A INTERNFWD -j DENY&#xa;&#xa;</xsl:text>

  <xsl:for-each select="/config/IPv6/IPv6to4/SIT">
    <xsl:value-of select="concat('/usr/sbin/ip6tables -I EXTERNIN -j DEFIN -i sit',position()+1,' -s 2002:',@ipv6to4pre,'::/',@subnet,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/ip6tables -I EXTERNOUT -j ACCEPT -o sit',position()+1,' -d 2002:',@ipv6to4pre,'::/',@subnet,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/ip6tables -I EXTERNFWD -j ACCEPT -o sit',position()+1,' -d 2002:',@ipv6to4pre,'::/',@subnet,$nl)"/>
  </xsl:for-each>

  <xsl:text>
#Activate Loopback Chain's
/usr/sbin/ip6tables -A LOOPIN -j ACCEPT -i lo -s ::1 -d ::1
/usr/sbin/ip6tables -A LOOPOUT -j ACCEPT -o lo -s ::1 -d ::1

#Allow SCTP Traffic For Media Gateway
/usr/sbin/ip6tables -A LOOPIN -j ACCEPT -i lo -p 132
/usr/sbin/ip6tables -A LOOPOUT -j ACCEPT -o lo -p 132

#Allow mISDN
/usr/sbin/ip6tables -A LOOPIN -j ACCEPT -p 34
/usr/sbin/ip6tables -A LOOPOUT -j ACCEPT -p 34

#STUN Loopback
/usr/sbin/ip6tables -A LOOPIN -j ACCEPT -p udp -m state --state ESTABLISHED,NEW --sport 3478:3479 --dport 10000:65535
/usr/sbin/ip6tables -A LOOPOUT -j ACCEPT -p udp -m state --state ESTABLISHED,NEW --sport 3478:3479 --dport 10000:65535

#Traffic to link local addresses
/usr/sbin/ip6tables -A LOOPIN -j ACCEPT -d fe80::/64
/usr/sbin/ip6tables -A LOOPOUT -j ACCEPT -d fe80::/64
</xsl:text>
  <xsl:for-each select="/config/IP/Interfaces/Interface6">
    <xsl:value-of select="concat($nl,'#Loopback for ',.,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/ip6tables -A LOOPIN -j ACCEPT -s ',@prefix,@ipaddr,'/',@subnet,' -d ',@prefix,@ipaddr,'/',@subnet,$nl)"/>
    <xsl:value-of select="concat('/usr/sbin/ip6tables -A LOOPOUT -j ACCEPT -s ',@prefix,@ipaddr,'/',@subnet,' -d ',@prefix,@ipaddr,'/',@subnet,$nl)"/>
  </xsl:for-each>

  <xsl:text>
#RIP
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -m state --state NEW  -p udp --sport 520 --dport 520

#BGP
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -m state --state NEW  -p tcp --sport 1024:65535 --dport 179

#OSPF
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -m state --state NEW -p ospf

#HylaFax
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 4559

#FTP
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 21

#FTPS
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 990

#DNS
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW  --sport 1024:65535 --dport 53
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW  --sport 53 --dport 53
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 53

#LDAP
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 389
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 636

#NTP
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW  --sport 1024:65535 --dport 123
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW  --sport 123 --dport 123

#MySQL
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 3306

#PGSQL
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 5432

#Orb
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 2809

#SMTP
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 25
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 587

#POP3
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 110

#POP3S
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 995

#SSH
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 0:65535 --dport 22

#IDENT
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --dport 113 --sport 1024:65535

#IMAP
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 143
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 993

#Trend
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 1812

#Asterisk FOP
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 4445

#HTTP
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 80

#HTTPS
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 443

#HTTPS Management
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 666

#Proxy Server
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 3128
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 3129
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 8080

#NFS TCP/UDP
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 2049
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 2049

#LPD
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 515

#IPSEC
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -m state --state NEW  -p udp --sport 500 --dport 500
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -m state --state NEW  -p udp --sport 1024:65535 --dport 500

#SMB
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 137:138 --dport 137:138
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 137:138
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 139
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 445
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 548
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 873

#SIP
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 5000
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 5060
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 5060:5061

#H323
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 1720:1722
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 1718:1729
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp --sport 1719 --dport 1719 -m state --state NEW

#MGCP
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 2727

#IAX
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 4569

#IAX2
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 5036

#STUN
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state ESTABLISHED,NEW --sport 3478:3479 --dport 1024:65535

#MDNS
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp --sport 5353 --dport 1024:65535

#RTP
/usr/sbin/ip6tables -A SYSIN -j ACCEPT -p udp --sport 1024:65535 --dport 10000:20000
</xsl:text>

  <xsl:if test="/config/DNS/Config/Option[@option = 'ExtServ'] = 'true'">
    <xsl:text>&#xa;#Allow Access To Nameserver Externaly UDP Mode&#xa;</xsl:text>
    <xsl:text>/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 53 --dport 53&#xa;</xsl:text>
    <xsl:text>/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 53&#xa;</xsl:text>
    <xsl:text>/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 53&#xa;</xsl:text>
  </xsl:if>

  <xsl:text>
#SSH/Rsync Access
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 22 --sport 1024:65535
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 873 --sport 1024:65535

#OVPN Access
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 1194 --sport 1024:65535

#L2TP Access
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --dport 1701 --sport 1024:65535

#Allow Access To Time Server
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --dport 123 --sport 123
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --dport 123 --sport 1024:65535

#Allow Access To IMAP/POP3 Remotely
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 143
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 110
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 993
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 995

#Allow Access To STUN Remotely
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 3478:3479

#Allow Remote SIP/IAX2/FOP
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 4445
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 5000
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 5060
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 5060:5061
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 4569

#Allow Remote H.323 Registrations
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 1719:1722

#Allow Remote H.323 Signaling
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 10000:20000

#Allow Access To LDAP/TLS Remotely
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 636
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 389
</xsl:text>
  <xsl:if test="translate(/config/Email/Config/Option[@option = 'Delivery'],$uppercase,$smallcase) != 'deffered'">
    <xsl:text>&#xa;#Allow Remote SMTP Connections&#xa;</xsl:text>
    <xsl:text>/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 25 --sport 1024:65535&#xa;</xsl:text>
    <xsl:text>/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 587 --sport 1024:65535&#xa;</xsl:text>
  </xsl:if>

  <xsl:text>
#Allow Remote ident Connections
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 113

#Accept External Access To Web Server/FTP
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 80
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 1024:65535 --sport 80
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 20 --dport 1024:65535
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 989 --dport 1024:65535
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 443 --sport 1024:65535
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 666 --sport 1024:65535
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 3128 --sport 1024:65535
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 8080 -m state --state ESTABLISHED,NEW

#Allow IKE Negotiation / NAT-T
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 500 --dport 500
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 500
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 4500

#Allow Remote RTP
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 10000:20000
/usr/sbin/ip6tables -A DEFIN -j ACCEPT -p udp -s 0/0 --sport 1024:65535 --dport 10000:20000

</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>#!/bin/bash&#xa;&#xa;</xsl:text>

  <xsl:if test="$intiface != $extiface">
    <xsl:call-template name="firewall"/>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>
