<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="fqdn" select="concat(/config/DNS/Config/Option[@option = 'Hostname'],'.',/config/DNS/Config/Option[@option = 'Domain'])"/>
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>

<xsl:template match="/config">HOME                   = .
RANDFILE               = $ENV::HOME/.rnd

oid_section            = new_oids

[ new_oids ]

[ req ]
default_bits           = 2048
distinguished_name     = req_distinguished_name
attributes             = req_attributes
prompt                 = no
encrypt_key            = no
req_extensions         = v3_req
default_md             = sha1

[ ca ]
default_ca             = ca_func

[ ca_func ]
unique_subject         = yes
default_days           = 1460
dir                    = /etc/openssl
certs                  = $dir/certs
database               = $dir/index.txt
new_certs_dir          = $dir/newcerts
certificate            = /etc/ipsec.d/cacerts/cacert.pem
serial                 = $dir/serial
crl                    = $dir/crl.pem
private_key            = $dir/private/cakey.pem
RANDFILE               = $dir/private/.rand
default_crl_days       = 30
default_bits           = 2048
default_md             = sha1
preserve               = no
email_in_dn            = no
policy                 = policy_match
x509_extensions        = user_cert
crl_extensions         = crl_ext
copy_extensions        = copy

[ policy_match ]
countryName             = optional
stateOrProvinceName     = optional
localityName            = optional
organizationName        = optional
organizationalUnitName  = optional
commonName              = supplied
emailAddress            = optional

[ req_distinguished_name ]
C                      = <xsl:value-of select="/config/X509/Option[@option = 'Country']"/>
ST                     = <xsl:value-of select="/config/X509/Option[@option = 'State']"/>
L                      = <xsl:value-of select="/config/X509/Option[@option = 'City']"/>
O                      = <xsl:value-of select="/config/X509/Option[@option = 'Company']"/>
OU                     = <xsl:value-of select="/config/X509/Option[@option = 'Division']"/>
CN                     = <xsl:value-of select="/config/X509/Option[@option = 'Name']"/>

[ req_attributes ]
#challengePassword      = A challenge password
#challengePassword_min  = 4
#challengePassword_max  = 20

[ v3_req ]
nsComment              = "Generated On Network Sentinel Solutions Firewall"
subjectAltName         = DNS:<xsl:value-of select="$fqdn"/>,email:root@<xsl:value-of select="$fqdn"/>,IP:<xsl:value-of select="$intip"/>
nsSslServerName        = <xsl:value-of select="$fqdn"/>

[ user_cert ]
subjectKeyIdentifier   = hash
basicConstraints       = CA:FALSE
nsCertType             = client, email, server, objsign
keyUsage               = nonRepudiation, digitalSignature, keyEncipherment
authorityKeyIdentifier = keyid:always,issuer:always
nsCaRevocationUrl      = http://<xsl:value-of select="$fqdn"/>/cert/crl.pem
issuerAltName          = DNS:<xsl:value-of select="$fqdn"/>,email:root@<xsl:value-of select="$fqdn"/>,IP:<xsl:value-of select="$intip"/>
nsBaseUrl              = http://<xsl:value-of select="$fqdn"/>
nsRenewalUrl           = /cert/renew?cert=
nsCaRevocationUrl      = /cgi-perl/cert-rev.pl?certid=
nsRevocationUrl        = /cgi-perl/cert-rev.pl?certid=

[ ca_cert ]
subjectKeyIdentifier   = hash
basicConstraints       = CA:TRUE, pathlen:1
nsCertType             = client, email, server, objsign, sslCA, emailCA, objCA
keyUsage               = cRLSign,keyCertSign
authorityKeyIdentifier = keyid:always,issuer:always
issuerAltName          = DNS:<xsl:value-of select="$fqdn"/>,email:root@<xsl:value-of select="$fqdn"/>,IP:<xsl:value-of select="$intip"/>
nsBaseUrl              = http://<xsl:value-of select="$fqdn"/>
nsRenewalUrl           = /cert/renew?cert=
nsCaRevocationUrl      = /cgi-perl/cert-rev.pl?certid=
nsRevocationUrl        = /cgi-perl/cert-rev.pl?certid=

[ v3_ca ]
#nsCaPolicyUrl
nsBaseUrl              = http://<xsl:value-of select="$fqdn"/>
nsRenewalUrl           = /cert/renew?cert=
nsCaRevocationUrl      = /cgi-perl/cert-rev.pl?certid=
nsRevocationUrl        = /cgi-perl/cert-rev.pl?certid=
nsSslServerName        = <xsl:value-of select="$fqdn"/>
subjectKeyIdentifier   = hash
basicConstraints       = CA:TRUE, pathlen:2
nsComment              = "Generated On Network Sentinel Solutions Firewall"
nsCertType             = client, email, server, objsign, sslCA, emailCA, objCA
keyUsage               = cRLSign,keyCertSign
subjectAltName         = DNS:<xsl:value-of select="$fqdn"/>,email:root@<xsl:value-of select="$fqdn"/>,IP:<xsl:value-of select="$intip"/>
authorityKeyIdentifier = keyid:always,issuer:always
issuerAltName          = DNS:<xsl:value-of select="$fqdn"/>,email:root@<xsl:value-of select="$fqdn"/>,IP:<xsl:value-of select="$intip"/>
extendedKeyUsage       = serverAuth,clientAuth,emailProtection

[ crl_ext ]
authorityKeyIdentifier = keyid:always,issuer:always
</xsl:template>
</xsl:stylesheet>
