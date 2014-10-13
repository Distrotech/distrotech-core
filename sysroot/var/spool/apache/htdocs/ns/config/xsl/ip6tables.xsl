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
  /sbin/ip6tables -F
  /sbin/ip6tables -N STATEOK
  /sbin/ip6tables -N TCPCHECK
  /sbin/ip6tables -N DENY
  /sbin/ip6tables -N DEFIN
  /sbin/ip6tables -N DEFOUT
  /sbin/ip6tables -N EXTERNOUT
  /sbin/ip6tables -N EXTERNIN
  /sbin/ip6tables -N EXTERNFWD
  /sbin/ip6tables -N INTERNOUT
  /sbin/ip6tables -N INTERNIN
  /sbin/ip6tables -N INTERNFWD
  /sbin/ip6tables -N LOCALFWD
  /sbin/ip6tables -N MCASTFWD
  /sbin/ip6tables -N SYSIN
  /sbin/ip6tables -N LOOPIN
  /sbin/ip6tables -N LOOPOUT

  /sbin/ip6tables -A INPUT -j STATEOK -m state --state RELATED,ESTABLISHED,INVALID
  /sbin/ip6tables -A INPUT -j TCPCHECK -p tcp
  /sbin/ip6tables -A INPUT -j ACCEPT -p icmpv6
  /sbin/ip6tables -A INPUT -j ACCEPT -d ff02::/16 -s fe80::/64
  /sbin/ip6tables -A INPUT -j ACCEPT -p udp --sport 547 --dport 546 -s fe80::/64 -d fe80::/64
  /sbin/ip6tables -A INPUT -j LOOPIN -i lo
  /sbin/ip6tables -A INPUT -j DROP ! -s fc00::/7 -d fc00::/7
  /sbin/ip6tables -A INPUT -j EXTERNIN
  /sbin/ip6tables -A INPUT -j INTERNIN
  /sbin/ip6tables -A INPUT -j DEFIN -i sit0 -s 2002::/16
  /sbin/ip6tables -A INPUT -j DEFIN -i sit1
</xsl:text>
  <xsl:for-each select="/config/IP/ADSL/Links/Link">
    <xsl:value-of select="concat('  /sbin/ip6tables -A INPUT -j DEFIN -i sit1.',position(),$nl)"/>
  </xsl:for-each>
  <xsl:text>  /sbin/ip6tables -A INPUT -j DENY

  /sbin/ip6tables -A OUTPUT -j STATEOK -m state --state RELATED,ESTABLISHED,INVALID
  /sbin/ip6tables -A OUTPUT -j TCPCHECK -p tcp
  /sbin/ip6tables -A OUTPUT -j ACCEPT -p icmpv6
  /sbin/ip6tables -A OUTPUT -j ACCEPT -d ff02::/16 -s fe80::/64
  /sbin/ip6tables -A OUTPUT -j ACCEPT -p udp -s fe80::/64 -d fe80::/64 --sport 547 --dport 546
  /sbin/ip6tables -A OUTPUT -j LOOPOUT -o lo
  /sbin/ip6tables -A OUTPUT -j INTERNOUT
  /sbin/ip6tables -A OUTPUT -j DROP -s fc00::/7 ! -d fc00::/7
  /sbin/ip6tables -A OUTPUT -j EXTERNOUT
  /sbin/ip6tables -A OUTPUT -j ACCEPT -o sit0 -d 2002::/16
  /sbin/ip6tables -A OUTPUT -j ACCEPT -o sit1
</xsl:text>
  <xsl:for-each select="/config/IP/ADSL/Links/Link">
    <xsl:value-of select="concat('  /sbin/ip6tables -A OUTPUT -j ACCEPT -o sit1.',position(),$nl)"/>
  </xsl:for-each>
  <xsl:text>  /sbin/ip6tables -A OUTPUT -j DENY

  /sbin/ip6tables -A FORWARD -j STATEOK -m state --state RELATED,ESTABLISHED,INVALID
  /sbin/ip6tables -A FORWARD -j TCPCHECK -p tcp
  /sbin/ip6tables -A FORWARD -j ACCEPT -p icmpv6
  /sbin/ip6tables -A FORWARD -j INTERNFWD
  /sbin/ip6tables -A FORWARD -j DROP -s fc00::/7 ! -d fc00::/7
  /sbin/ip6tables -A FORWARD -j DROP -d fc00::/7 ! -s fc00::/7
  /sbin/ip6tables -A FORWARD -j ACCEPT -o sit0 -d 2002::/16
  /sbin/ip6tables -A FORWARD -j ACCEPT -o sit1
</xsl:text>
  <xsl:for-each select="/config/IP/ADSL/Links/Link">
    <xsl:value-of select="concat('  /sbin/ip6tables -A FORWARD -j ACCEPT -o sit1.',position(),$nl)"/>
  </xsl:for-each>
  <xsl:text>  /sbin/ip6tables -A FORWARD -j EXTERNFWD
  /sbin/ip6tables -A FORWARD -j DENY

  #Allow Related/Established Traffic
  /sbin/ip6tables -A STATEOK -j DROP -m state --state INVALID
  /sbin/ip6tables -A STATEOK -j ACCEPT

  /sbin/ip6tables -A TCPCHECK -j DROP -p tcp --tcp-flags SYN,FIN SYN,FIN
  /sbin/ip6tables -A TCPCHECK -j DROP -p tcp --tcp-flags SYN,RST SYN,RST
  /sbin/ip6tables -A TCPCHECK -j ACCEPT -p tcp --tcp-flags SYN,ACK SYN,ACK
  /sbin/ip6tables -A TCPCHECK -j ACCEPT -p tcp --tcp-flags SYN,ACK,RST RST
  /sbin/ip6tables -A TCPCHECK -j ACCEPT -p tcp --tcp-flags SYN,ACK,PSH ACK,PSH
  /sbin/ip6tables -A TCPCHECK -j ACCEPT -p tcp --tcp-flags SYN,ACK ACK

  #Multicast Forward
  /sbin/ip6tables -A MCASTFWD -j ACCEPT -d ff02::/16
  /sbin/ip6tables -A MCASTFWD -j ACCEPT -s ff02::/16

  #Deny / Reject
  /sbin/ip6tables -A DENY -j ACCEPT -p tcp --tcp-flags SYN,PSH PSH -m length --length 40
  /sbin/ip6tables -A DENY -j ACCEPT -p tcp --tcp-flags SYN,RST RST -m length --length 40
  /sbin/ip6tables -A DENY -j ACCEPT -p tcp --tcp-flags SYN,FIN FIN -m length --length 40
  #/sbin/ip6tables -A DENY -j ULOG
  /sbin/ip6tables -A DENY -j LOG -p ! tcp --log-level info --log-ip-options --log-prefix "rejected packet "
  /sbin/ip6tables -A DENY -j LOG -p tcp --log-level info --log-ip-options --log-tcp-options --log-tcp-sequence --log-prefix "rejected packet "
  /sbin/ip6tables -A DENY -j REJECT -p tcp --reject-with tcp-reset
  /sbin/ip6tables -A DENY -j REJECT
fi;

/sbin/ip6tables -F DEFIN
/sbin/ip6tables -F DEFOUT
/sbin/ip6tables -F LOOPIN
/sbin/ip6tables -F LOOPOUT

#External Multicast Traffic
/sbin/ip6tables -I DEFIN -j ACCEPT -d ff02::/16
</xsl:text>
  <xsl:for-each select="/config/IP/Interfaces/Interface">
    <xsl:value-of select="concat($nl,'#',@name,$nl)"/>
    <xsl:choose>
      <xsl:when test="(. != $extiface) or ($extcon = 'ADSL')">
        <xsl:value-of select="concat('/sbin/ip6tables -A INTERNIN -i ',.,' -j SYSIN -m state --state NEW',$nl)"/>
        <xsl:value-of select="concat('/sbin/ip6tables -A INTERNOUT -o ',.,' -j ACCEPT',$nl)"/>
        <xsl:value-of select="concat('/sbin/ip6tables -A INTERNFWD -i ',.,' -o ',.,' -j MCASTFWD',$nl)"/>
        <xsl:value-of select="concat('/sbin/ip6tables -A INTERNFWD -i ',.,' -j RETURN',$nl)"/>
        <xsl:value-of select="concat('/sbin/ip6tables -A INTERNFWD -j LOCALFWD -o ',.,$nl)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat('/sbin/ip6tables -A EXTERNIN -i ',.,' -j DEFIN',$nl)"/>
        <xsl:value-of select="concat('/sbin/ip6tables -A EXTERNOUT -o ',.,' -j ACCEPT',$nl)"/>
        <xsl:value-of select="concat('/sbin/ip6tables -A EXTERNFWD -o ',.,' -j ACCEPT',$nl)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:for-each>

  <xsl:for-each select="/config/IP/GRE/Tunnels/Tunnel">
    <xsl:value-of select="concat($nl,'#',.,$nl)"/>
    <xsl:value-of select="concat('/sbin/ip6tables -A INTERNIN -i gtun',position()-1,' -j SYSIN -m state --state NEW',$nl)"/>
    <xsl:value-of select="concat('/sbin/ip6tables -A INTERNOUT -o gtun',position()-1,' -j ACCEPT',$nl)"/>
    <xsl:value-of select="concat('/sbin/ip6tables -A INTERNFWD -i gtun',position()-1,' -o gtun',position()-1,' -j MCASTFWD',$nl)"/>
    <xsl:value-of select="concat('/sbin/ip6tables -A INTERNFWD -i gtun',position()-1,' -j RETURN',$nl)"/>
    <xsl:value-of select="concat('/sbin/ip6tables -A INTERNFWD -j LOCALFWD -o gtun',position()-1,$nl)"/>
  </xsl:for-each>

  <xsl:text>&#xa;#Deny All non internal traffic&#xa;</xsl:text>
  <xsl:text>/sbin/ip6tables -A INTERNFWD -j DENY&#xa;&#xa;</xsl:text>

  <xsl:for-each select="/config/IPv6/IPv6to4/SIT">
    <xsl:value-of select="concat('/sbin/ip6tables -I EXTERNIN -j DEFIN -i sit',position()+1,' -s 2002:',@ipv6to4pre,'::/',@subnet,$nl)"/>
    <xsl:value-of select="concat('/sbin/ip6tables -I EXTERNOUT -j ACCEPT -o sit',position()+1,' -d 2002:',@ipv6to4pre,'::/',@subnet,$nl)"/>
    <xsl:value-of select="concat('/sbin/ip6tables -I EXTERNFWD -j ACCEPT -o sit',position()+1,' -d 2002:',@ipv6to4pre,'::/',@subnet,$nl)"/>
  </xsl:for-each>

  <xsl:text>
#Activate Loopback Chain's
/sbin/ip6tables -A LOOPIN -j ACCEPT -i lo -s ::1 -d ::1
/sbin/ip6tables -A LOOPOUT -j ACCEPT -o lo -s ::1 -d ::1

#Allow SCTP Traffic For Media Gateway
/sbin/ip6tables -A LOOPIN -j ACCEPT -i lo -p 132
/sbin/ip6tables -A LOOPOUT -j ACCEPT -o lo -p 132

#Allow mISDN
/sbin/ip6tables -A LOOPIN -j ACCEPT -p 34
/sbin/ip6tables -A LOOPOUT -j ACCEPT -p 34

#STUN Loopback
/sbin/ip6tables -A LOOPIN -j ACCEPT -p udp -m state --state ESTABLISHED,NEW --sport 3478:3479 --dport 10000:65535
/sbin/ip6tables -A LOOPOUT -j ACCEPT -p udp -m state --state ESTABLISHED,NEW --sport 3478:3479 --dport 10000:65535

#Traffic to link local addresses
/sbin/ip6tables -A LOOPIN -j ACCEPT -d fe80::/64
/sbin/ip6tables -A LOOPOUT -j ACCEPT -d fe80::/64
</xsl:text>
  <xsl:for-each select="/config/IP/Interfaces/Interface6">
    <xsl:value-of select="concat($nl,'#Loopback for ',.,$nl)"/>
    <xsl:value-of select="concat('/sbin/ip6tables -A LOOPIN -j ACCEPT -s ',@prefix,@ipaddr,'/',@subnet,' -d ',@prefix,@ipaddr,'/',@subnet,$nl)"/>
    <xsl:value-of select="concat('/sbin/ip6tables -A LOOPOUT -j ACCEPT -s ',@prefix,@ipaddr,'/',@subnet,' -d ',@prefix,@ipaddr,'/',@subnet,$nl)"/>
  </xsl:for-each>

  <xsl:text>
#RIP
/sbin/ip6tables -A SYSIN -j ACCEPT -m state --state NEW  -p udp --sport 520 --dport 520

#BGP
/sbin/ip6tables -A SYSIN -j ACCEPT -m state --state NEW  -p tcp --sport 1024:65535 --dport 179

#OSPF
/sbin/ip6tables -A SYSIN -j ACCEPT -m state --state NEW -p ospf

#HylaFax
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 4559

#FTP
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 21

#FTPS
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 990

#DNS
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW  --sport 1024:65535 --dport 53
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW  --sport 53 --dport 53
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 53

#LDAP
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 389
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 636

#NTP
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW  --sport 1024:65535 --dport 123
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW  --sport 123 --dport 123

#MySQL
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 3306

#PGSQL
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 5432

#Orb
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 2809

#SMTP
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 25
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 587

#POP3
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 110

#POP3S
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 995

#SSH
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 0:65535 --dport 22

#IDENT
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --dport 113 --sport 1024:65535

#IMAP
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 143
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 993

#Trend
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 1812

#Asterisk FOP
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 4445

#HTTP
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 80

#HTTPS
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 443

#HTTPS Management
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 666

#Proxy Server
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 3128
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 3129
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 8080

#NFS TCP/UDP
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 2049
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 2049

#LPD
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 515

#IPSEC
/sbin/ip6tables -A SYSIN -j ACCEPT -m state --state NEW  -p udp --sport 500 --dport 500
/sbin/ip6tables -A SYSIN -j ACCEPT -m state --state NEW  -p udp --sport 1024:65535 --dport 500

#SMB
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 137:138 --dport 137:138
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 137:138
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 139
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 445
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 548
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW  --sport 1024:65535 --dport 873

#SIP
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 5000
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 5060
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 5060:5061

#H323
/sbin/ip6tables -A SYSIN -j ACCEPT -p tcp -m state --state NEW --sport 1024:65535 --dport 1720:1722
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 1718:1729
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp --sport 1719 --dport 1719 -m state --state NEW

#MGCP
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 2727

#IAX
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 4569

#IAX2
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state NEW --sport 1024:65535 --dport 5036

#STUN
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp -m state --state ESTABLISHED,NEW --sport 3478:3479 --dport 1024:65535

#MDNS
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp --sport 5353 --dport 1024:65535

#RTP
/sbin/ip6tables -A SYSIN -j ACCEPT -p udp --sport 1024:65535 --dport 10000:20000
</xsl:text>

  <xsl:if test="/config/DNS/Config/Option[@option = 'ExtServ'] = 'true'">
    <xsl:text>&#xa;#Allow Access To Nameserver Externaly UDP Mode&#xa;</xsl:text>
    <xsl:text>/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 53 --dport 53&#xa;</xsl:text>
    <xsl:text>/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 53&#xa;</xsl:text>
    <xsl:text>/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 53&#xa;</xsl:text>
  </xsl:if>

  <xsl:text>
#SSH/Rsync Access
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 22 --sport 1024:65535
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 873 --sport 1024:65535

#OVPN Access
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 1194 --sport 1024:65535

#L2TP Access
/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --dport 1701 --sport 1024:65535

#Allow Access To Time Server
/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --dport 123 --sport 123
/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --dport 123 --sport 1024:65535

#Allow Access To IMAP/POP3 Remotely
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 143
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 110
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 993
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 995

#Allow Access To STUN Remotely
/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 3478:3479

#Allow Remote SIP/IAX2/FOP
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 4445
/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 5000
/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 5060
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 5060:5061
/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 4569

#Allow Remote H.323 Registrations
/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 1719:1722

#Allow Remote H.323 Signaling
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 10000:20000

#Allow Access To LDAP/TLS Remotely
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 636
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 389
</xsl:text>
  <xsl:if test="translate(/config/Email/Config/Option[@option = 'Delivery'],$uppercase,$smallcase) != 'deffered'">
    <xsl:text>&#xa;#Allow Remote SMTP Connections&#xa;</xsl:text>
    <xsl:text>/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 25 --sport 1024:65535&#xa;</xsl:text>
    <xsl:text>/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 587 --sport 1024:65535&#xa;</xsl:text>
  </xsl:if>

  <xsl:text>
#Allow Remote ident Connections
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 113

#Accept External Access To Web Server/FTP
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 1024:65535 --dport 80
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 1024:65535 --sport 80
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 20 --dport 1024:65535
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --sport 989 --dport 1024:65535
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 443 --sport 1024:65535
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 666 --sport 1024:65535
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 3128 --sport 1024:65535
/sbin/ip6tables -A DEFIN -j ACCEPT -p tcp --dport 8080 -m state --state ESTABLISHED,NEW

#Allow IKE Negotiation / NAT-T
/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 500 --dport 500
/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 500
/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 4500

#Allow Remote RTP
/sbin/ip6tables -A DEFIN -j ACCEPT -p udp --sport 1024:65535 --dport 10000:20000
/sbin/ip6tables -A DEFIN -j ACCEPT -p udp -s 0/0 --sport 1024:65535 --dport 10000:20000

</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>#!/bin/bash&#xa;&#xa;</xsl:text>

  <xsl:if test="$intiface != $extiface">
    <xsl:call-template name="firewall"/>
  </xsl:if>
</xsl:template>
</xsl:stylesheet>
