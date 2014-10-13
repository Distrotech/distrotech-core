<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="fqdn" select="concat(/config/DNS/Config/Option[@option = 'Hostname'],'.',/config/DNS/Config/Option[@option = 'Domain'])"/>
<xsl:param name="cacert" select="'/etc/ipsec.d/cacerts/cacert.pem'"/>
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>

<!--
XXXX
  http://www.netbsd.org/docs/network/ipsec/rasvpn.html

  Should enable per connection certificates
  Xauth / Hybrid for more ciphers and test
  Allow saving passwords in psk file
  Allow racoon as a client ??

-->

<xsl:template match="ESPTunnel">
  <xsl:text>remote </xsl:text><xsl:value-of select="@dest"/><xsl:text>
{
	initial_contact on;
	lifetime time 86400 sec;
        exchange_mode main;
        verify_identifier off;
        certificate_type x509 "/etc/openssl/server.signed.pem" "/etc/openssl/serverkey.pem";
        ca_type x509 "/etc/ipsec.d/cacerts/cacert.pem";
        generate_policy on;
        passive off;
        dpd_delay 30;
        dpd_maxfail 3;
        nat_traversal on;
        proposal_check obey;
        ike_frag on;
        mode_cfg on;
        proposal {
                encryption_algorithm </xsl:text><xsl:value-of select="@cipher"/><xsl:text>;
                hash_algorithm 5;
                authentication_method rsasig;
                dh_group </xsl:text><xsl:value-of select="@dhgroup"/><xsl:text>;
        }
}
</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>path certificate "/etc/ipsec.d/certs" ;
path pre_shared_key "/etc/ipsec.d/psk.txt";
listen {
	adminsock "/var/racoon/racoon.sock" "root" "root" 0660;
}
</xsl:text>
  <xsl:apply-templates select="/config/IP/ESP/Tunnels/ESPTunnel"/>
  <xsl:text>remote anonymous
{
	initial_contact on;
	lifetime time 86400 sec;
#	situation identity_only;
        exchange_mode main;
#        my_identifier asn1dn;
        my_identifier fqdn "</xsl:text><xsl:value-of select="$fqdn"/><xsl:text>";
        verify_identifier off;
#        peers_identifier asn1dn "C=*,ST=*,L=*,O=*,OU=*,CN=*";	
#        peers_identifier asn1dn "C=*,ST=*,L=*,O=*,OU=*,CN=*,emailAddress=*";	
#        peers_identifier address;
        certificate_type x509 "/etc/openssl/server.signed.pem" "/etc/openssl/serverkey.pem";
#        ca_type x509 "</xsl:text><xsl:value-of select="$cacert"/><xsl:text>";
        generate_policy on;
        passive off;
        dpd_delay 30;
        dpd_maxfail 3;
        nat_traversal on;
#        script "/etc/racoon/phase1-down.sh" phase1_down;
        proposal_check obey;
        ike_frag on;
        mode_cfg on;
        proposal {
                encryption_algorithm cast128;
                hash_algorithm sha1;
                authentication_method rsasig;
                dh_group 5;
        }
        proposal {
                encryption_algorithm cast128;
                hash_algorithm md5;
                authentication_method rsasig;
                dh_group 5;
        }
        proposal {
                encryption_algorithm cast128;
                hash_algorithm sha1;
                authentication_method rsasig;
                dh_group 2;
        }
        proposal {
                encryption_algorithm cast128;
                hash_algorithm md5;
                authentication_method rsasig;
                dh_group 2;
        }
        proposal {
                encryption_algorithm aes;
                hash_algorithm sha1;
                authentication_method rsasig;
                dh_group 5;
        }
        proposal {
                encryption_algorithm aes;
                hash_algorithm md5;
                authentication_method rsasig;
                dh_group 5;
        }
        proposal {
                encryption_algorithm aes;
                hash_algorithm sha1;
                authentication_method rsasig;
                dh_group 2;
        }
        proposal {
                encryption_algorithm aes;
                hash_algorithm md5;
                authentication_method rsasig;
                dh_group 2;
        }
        proposal {
                encryption_algorithm blowfish;
                hash_algorithm sha1;
                authentication_method rsasig;
                dh_group 5;
        }
        proposal {
                encryption_algorithm blowfish;
                hash_algorithm md5;
                authentication_method rsasig;
                dh_group 5;
        }
        proposal {
                encryption_algorithm blowfish;
                hash_algorithm sha1;
                authentication_method rsasig;
                dh_group 2;
        }
        proposal {
                encryption_algorithm blowfish;
                hash_algorithm md5;
                authentication_method rsasig;
                dh_group 2;
        }
        proposal {
                encryption_algorithm 3des;
                hash_algorithm sha1;
                authentication_method rsasig;
                dh_group 5;
        }
        proposal {
                encryption_algorithm 3des;
                hash_algorithm md5;
                authentication_method rsasig;
                dh_group 5;
        }
        proposal {
                encryption_algorithm 3des;
                hash_algorithm sha1;
                authentication_method rsasig;
                dh_group 2;
        }
        proposal {
                encryption_algorithm 3des;
                hash_algorithm md5;
                authentication_method rsasig;
                dh_group 2;
        }
        proposal {
                encryption_algorithm des;
                hash_algorithm sha1;
                authentication_method rsasig;
                dh_group 5;
        }
        proposal {
                encryption_algorithm des;
                hash_algorithm md5;
                authentication_method rsasig;
                dh_group 5;
        }
        proposal {
                encryption_algorithm des;
                hash_algorithm sha1;
                authentication_method rsasig;
                dh_group 2;
        }
        proposal {
                encryption_algorithm des;
                hash_algorithm md5;
                authentication_method rsasig;
                dh_group 2;
        }
        proposal {
                encryption_algorithm 3des;
                hash_algorithm sha1;
                authentication_method hybrid_rsa_server;
                dh_group 2;
        }
        proposal {
                encryption_algorithm 3des;
                hash_algorithm sha1;
                authentication_method pre_shared_key;
                dh_group 5;
        }
        proposal {
                encryption_algorithm 3des;
                hash_algorithm md5;
                authentication_method pre_shared_key;
                dh_group 5;
        }
        proposal {
                encryption_algorithm 3des;
                hash_algorithm sha1;
                authentication_method pre_shared_key;
                dh_group 2;
        }
        proposal {
                encryption_algorithm 3des;
                hash_algorithm md5;
                authentication_method pre_shared_key;
                dh_group 2;
        }
        proposal {
                encryption_algorithm 3des;
                hash_algorithm sha1;
                authentication_method pre_shared_key;
                dh_group 1;
        }
        proposal {
                encryption_algorithm 3des;
                hash_algorithm md5;
                authentication_method pre_shared_key;
                dh_group 1;
        }
        proposal {
                encryption_algorithm des;
                hash_algorithm sha1;
                authentication_method pre_shared_key;
                dh_group 5;
        }
        proposal {
                encryption_algorithm des;
                hash_algorithm md5;
                authentication_method pre_shared_key;
                dh_group 5;
        }
        proposal {
                encryption_algorithm des;
                hash_algorithm sha1;
                authentication_method pre_shared_key;
                dh_group 2;
        }
        proposal {
                encryption_algorithm des;
                hash_algorithm md5;
                authentication_method pre_shared_key;
                dh_group 2;
        }
        proposal {
                encryption_algorithm des;
                hash_algorithm sha1;
                authentication_method pre_shared_key;
                dh_group 1;
        }
        proposal {
                encryption_algorithm des;
                hash_algorithm md5;
                authentication_method pre_shared_key;
                dh_group 1;
        }
}
</xsl:text>
  <xsl:if test="/config/IP/SysConf/Option[@option = 'VPNNet'] != ''">
    <xsl:text>mode_cfg {&#xa;</xsl:text>
    <xsl:value-of select="concat('        network4 ',$vpnnwaddr,';',$nl)"/>
    <xsl:value-of select="concat('        pool_size ',$vpnpool,';',$nl)"/>
    <xsl:value-of select="concat('        netmask4 ',$vpnsubnet,';',$nl)"/>
    <xsl:value-of select="concat('        auth_source system;',$nl)"/>
    <xsl:value-of select="concat('        dns4 ',$intip,';',$nl)"/>
    <xsl:value-of select="concat('        wins4 ',$intip,';',$nl)"/>
    <xsl:value-of select="concat('        banner &quot;/etc/racoon/motd&quot;;',$nl)"/>
    <xsl:text>}&#xa;</xsl:text>
  </xsl:if>
<xsl:text>
sainfo anonymous
{
	lifetime time 3600 sec;
        encryption_algorithm  aes,3des,des,des_iv64,des_iv32,blowfish,null_enc,twofish,rijndael;
        authentication_algorithm hmac_sha1,hmac_md5,hmac_sha256,non_auth;
        compression_algorithm deflate;
}
</xsl:text>
</xsl:template>
</xsl:stylesheet>
