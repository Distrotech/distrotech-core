<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>
<xsl:variable name="fqdn" select="concat($hname,'.',$domain)"/>
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
<xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'"/>

<xsl:template name="domtosuf">   
  <xsl:param name="domsuf"/>
  <xsl:param name="dom"/>
  
  <xsl:choose>
    <xsl:when test="$dom = ''">  
      <xsl:value-of select="$domsuf"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:choose>
        <xsl:when test="$domsuf = ''">
          <xsl:call-template name="domtosuf">
            <xsl:with-param name="dom" select="substring-after($dom,'.')"/>
            <xsl:with-param name="domsuf" select="concat('dc=',substring-before($dom,'.'))"/>
          </xsl:call-template>
       </xsl:when> 
       <xsl:otherwise>
          <xsl:call-template name="domtosuf">
            <xsl:with-param name="dom" select="substring-after($dom,'.')"/>
            <xsl:with-param name="domsuf" select="concat($domsuf,',dc=',substring-before($dom,'.'))"/>
          </xsl:call-template>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="rootdn">
  <xsl:value-of select="concat(/config/LDAP/Config/Option[@option = 'Login'],',')"/>
  <xsl:call-template name="domtosuf">
    <xsl:with-param name="dom" select="concat($domain,'.')"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="anondn">
  <xsl:choose>
    <xsl:when test="$hname != ''">
      <xsl:value-of select="concat('uid=ldap_limited_',$hname,',',/config/LDAP/Config/Option[@option = 'Login'])"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('uid=ldap_limited',/config/LDAP/Config/Option[@option = 'Login'])"/>
    </xsl:otherwise>
  </xsl:choose>
<!--
  <xsl:text>,</xsl:text>
  <xsl:call-template name="domtosuf">
    <xsl:with-param name="dom" select="concat($domain,'.')"/>
  </xsl:call-template>
-->
</xsl:template>

<xsl:template name="confdn">
  <xsl:choose>
    <xsl:when test="$hname != ''">
      <xsl:value-of select="concat('uid=ldap_config_',$hname,',',/config/LDAP/Config/Option[@option = 'Login'])"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('uid=ldap_config',/config/LDAP/Config/Option[@option = 'Login'])"/>
    </xsl:otherwise>
  </xsl:choose>
<!--
  <xsl:text>,</xsl:text>
  <xsl:call-template name="domtosuf">
    <xsl:with-param name="dom" select="concat($domain,'.')"/>
  </xsl:call-template>
-->
</xsl:template>

<xsl:template name="ureadaccess">
  <xsl:choose>
    <xsl:when test="/config/LDAP/@AnonRead = 'true'">
      <xsl:text>by dn="</xsl:text>
      <xsl:call-template name="confdn"/>
      <xsl:text>"&#xa;    none&#xa;  by dn.regex="^uid=.*,ou=Users"&#xa;    read&#xa;</xsl:text>
      <xsl:text>  by dn.regex="^sambaSID=.*,ou=Idmap"</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>by group="cn=User Read Access,ou=Admin"</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Directory">
  <xsl:text>

access to dn="cn=Users,cn=</xsl:text><xsl:value-of select="."/><xsl:text>"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group="cn=Users,cn=</xsl:text><xsl:value-of select="."/><xsl:text>"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by anonymous
    auth

access to dn="ou=Entries,cn=</xsl:text><xsl:value-of select="."/><xsl:text>"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by dn="cn=Snom,ou=Snom"
    read
  by group="cn=Admin Access,ou=Admin"
    write
  by group="cn=Users,cn=</xsl:text><xsl:value-of select="."/><xsl:text>"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
</xsl:text>
  <xsl:if test="@open = 'true'">
    <xsl:text>  by dn.regex="uid=.*,ou=Users"&#xa;    read&#xa;</xsl:text>
  </xsl:if>
<xsl:text>  by anonymous
    auth

access to dn="cn=</xsl:text><xsl:value-of select="."/><xsl:text>"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group="cn=Users,cn=</xsl:text><xsl:value-of select="."/><xsl:text>"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by anonymous
    auth
</xsl:text>
</xsl:template>

<xsl:template name="repurl">
  <xsl:choose>
    <xsl:when test="@usessl = 'true'">
      <xsl:value-of select="concat('ldaps://',.)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('ldap://',.)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Replica">
  <xsl:text>
syncrepl        rid=</xsl:text><xsl:value-of select="@sid"/><xsl:text>
                provider=</xsl:text><xsl:call-template name="repurl"/><xsl:text>
                type=refreshAndPersist
                retry="60 +"
                searchbase=""
                schemachecking=on
                bindmethod=simple
                binddn="uid=</xsl:text><xsl:value-of select="$fqdn"/><xsl:text>,ou=servers"
                credentials=T0pS3kr1T
                logbase="ou=Log"
                logfilter="(&amp;(objectClass=auditWriteObject)(reqResult=0))"
                syncdata=accesslog
		tls_reqcert=never

updateref       </xsl:text><xsl:call-template name="repurl"/><xsl:text>
</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>include         /etc/openldap/schema/core.schema
include         /etc/openldap/schema/cosine.schema
include         /etc/openldap/schema/nis.schema
include         /etc/openldap/schema/inetorgperson.schema
include         /etc/openldap/schema/misc.schema

include         /etc/openldap/schema/netscape.schema
include         /etc/openldap/schema/extension.schema
include         /etc/openldap/schema/radius.schema
include         /etc/openldap/schema/samba.schema
include         /etc/openldap/schema/sendmail.schema

modulepath /usr/libexec/openldap

moduleload back_mdb.la
moduleload syncprov.la
moduleload accesslog.la
moduleload unique.la
moduleload refint.la
moduleload rwm.la

loglevel 0
#threads 128
idletimeout 30
timelimit 180
sizelimit 1000
conn_max_pending 256

limits dn.exact="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>" time=unlimited size=unlimited
limits dn.regex="^uid=.*,ou=servers" time=unlimited size=unlimited
limits group/groupOfNames/member="cn=Admin Access,ou=Admin" time=unlimited size=unlimited

sasl-secprops none
authz-regexp uid=(.*),cn=.*,cn=.* ldap:///</xsl:text>
  <xsl:call-template name="domtosuf"> 
    <xsl:with-param name="dom" select="concat($domain,'.')"/>
  </xsl:call-template>
  <xsl:text>??subtree?uid=$1

allow bind_v2 bind_anon_cred bind_anon_cred

TLSCACertificatePath /etc/ipsec.d/certs
TLSCertificateKeyFile /etc/openssl/serverkey.pem
TLSCertificateFile /etc/openssl/server.signed.pem
TLSVerifyClient never

#System Restricted Attributes
access to  attrs=olduid,exchangeServerAccess,sambaPasswordHistory,sambaLMPassword,sambaNTPassword,sambaAcctFlags,sambaLogonTime,sambaKickoffTime,sambaSID,sambaHomePath,sambaUserWorkstations,sambaLogoffTime,sambaHomeDrive,sambaLogonScript,sambaprofilePath,sambaPrimaryGroupSID
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by anonymous
    auth

access to  attrs=loginShell,homeDirectory,shadowMin,shadowMax,shadowInactive,shadowWarning,shadowExpire,uidNumber
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
   write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

access to dn="ou=Snom"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group="cn=Voip Admin,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="cn=Snom,ou=Snom"
    search
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    search
  by anonymous
    auth

access to dn.regex="^cn=(.*),ou=Snom"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group="cn=Voip Admin,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="cn=Snom,ou=Snom"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),ou=Snom"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group="cn=Voip Admin,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="cn=Snom,ou=Snom"
    read
  by anonymous
    auth

access to dn.regex="^cn=(.*),ou=Admin"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group.regex="cn=$1,ou=Admin"
   read
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

#Radius Profiles
access to dn.regex="^radiusrealm=(.*),(.*),ou=(idmap|users)" attrs=objectclass
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by *
    search
  by anonymous
    auth

access to dn.regex="^radiusrealm=(.*),(.*),ou=(idmap|users)" attrs=radiusrealm
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^$2,ou=$3"
    read
  by *
    search
  by anonymous
    auth

access to dn.regex="^radiusrealm=(.*),(.*),ou=(idmap|users)" attrs=radiusProfileDN,dialupaccess,radiusporttype,radiusframedipaddress,radiusframedmtu,radiusframedcompression,radiussimultaneoususe,radiussessiontimeout,radiusidletimeout,radiusacctinteriminterval,radiusreplyitem,radiuscheckitem,radiusServiceType,radiusFramedProtocol,radiusFramedIPNetmask,radiusAuthType,maxwebaliases,maxaliases,maxmailboxes,quotaHomeDir,quotaFileServer,quotaMailSpool,squidProxyAccess,smbServerAccess,sambapwdlastset,sambapwdmustchange,sambapwdcanchange
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^$2,ou=$3"
    read
  by anonymous
    auth

access to dn.regex="^radiusrealm=(.*),(.*),ou=(idmap|users)"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^$2,ou=$3"
    read
  by anonymous
    auth

#Email Boxes
access to dn.regex="^uid=(.*),(uid|sambasid)=(.*),ou=(users|idmap)" attrs=userPassword,shadowLastChange
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=Servers"
    read
  by dn.regex="^$2=$3,ou=$4"
    write
  by dn.regex="^uid=$1,$2=$3,ou=$4"
    write
  by anonymous
    auth

access to dn.regex="^uid=(.*),(uid|sambasid)=(.*),ou=(users|idmap)" attrs=objectclass,gidnumber,uid
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=Servers"
    read
  by dn.regex="^uid=$1,$2=$3,ou=$4"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by *
    search
  by anonymous
    auth

access to dn.regex="^uid=(.*),(uid|sambasid)=(.*),ou=(users|idmap)" attrs=mailLocalAddress,mailRoutingAddress,mailHost
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^$2=$3,ou=$4"
    write
  by dn.regex="^uid=$1,$2=$3,ou=$4"
    write
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),(uid|sambasid)=(.*),ou=(users|idmap)"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=Servers"
    read
  by dn.regex="^$2=$3,ou=$4"
    write
  by dn.regex="^uid=$1,$2=$3,ou=$4"
    write
  </xsl:text><xsl:call-template name="ureadaccess"/><xsl:text>
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

#Virtual Realms
access to dn.regex="^cn=(.*),ou=VAdmin" attrs=objectclass,cn
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$1,ou=VAdmin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^uid=.*,o=$1,ou=users"
    search
  by anonymous
    auth

access to dn.regex="^cn=(.*),ou=VAdmin" attrs=maxwebaliases,maxaliases,maxmailboxes,squidProxyAccess,smbServerAccess,radiusRealm,quotaFileServer,quotaMailSpool,quotaHomeDir,dialupaccess,radiusporttype,radiusframedipaddress,radiusframedmtu,radiusframedcompression,radiussimultaneoususe,radiussessiontimeout,radiusidletimeout,radiusacctinteriminterval,radiusreplyitem,radiuscheckitem,radiusServiceType,radiusFramedProtocol,radiusFramedIPNetmask,radiusAuthType
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$1,ou=VAdmin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^uid=.*,o=$1,ou=users"
    read
  by anonymous
    auth

access to dn.regex="^cn=(.*),ou=VAdmin" attrs=member
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$1,ou=VAdmin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by anonymous
    auth

access to dn.regex="^cn=(.*),ou=VAdmin"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$1,ou=VAdmin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^uid=.*,o=$1,ou=users"
    read
  by anonymous
    auth

access to dn.regex="^o=(.*),ou=Users"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$1,ou=VAdmin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),o=(.*),ou=Users" attrs=objectclass
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$2,ou=VAdmin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^uid=$1,o=$2,ou=users"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by *
    search
  by anonymous
    auth

access to dn.regex="^uid=(.*),o=(.*),ou=Users" attrs=sn,givenName,mail,preferredLanguage,initials,ipHostNumber,description,physicalDeliveryOfficeName,postalAddress,postalCode,title,homePostalAddress,pager,conferenceInformation,ou,o,st,l,comment,URL,c
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$2,ou=VAdmin"
    write
  by dn.regex="^uid=$1,o=$2,ou=users"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    none
  </xsl:text><xsl:call-template name="ureadaccess"/><xsl:text>
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),o=(.*),ou=Users" attrs=telephoneNumber,facsimileTelephoneNumber,homePhone,otherFacsimiletelephoneNumber,mobile,IPPhone
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$2,ou=VAdmin"
    write
  by dn.regex="^uid=$1,o=$2,ou=users"
    write
  by dn="cn=Snom,ou=Snom"
    read
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    none
  </xsl:text><xsl:call-template name="ureadaccess"/><xsl:text>
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),o=(.*),ou=Users" attrs=displayName,cn,jpegPhoto
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$2,ou=VAdmin"
    write
  by dn.regex="^uid=$1,o=$2,ou=users"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  </xsl:text><xsl:call-template name="ureadaccess"/><xsl:text>
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),o=(.*),ou=Users" attrs=radiusProfileDN,dialupaccess,radiusRealm,quotaFileServer,quotaMailSpool,quotaHomeDir,dialupaccess,radiusporttype,radiusframedipaddress,radiusframedmtu,radiusframedcompression,radiussimultaneoususe,radiussessiontimeout,radiusidletimeout,radiusacctinteriminterval,radiusreplyitem,radiuscheckitem,radiusServiceType,radiusFramedProtocol,radiusFramedIPNetmask,radiusAuthType,maxwebaliases,maxaliases,maxmailboxes,quotaHomeDir,quotaFileServer,quotaMailSpool,squidProxyAccess,smbServerAccess,sambapwdlastset,sambapwdmustchange,sambapwdcanchange
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$2,ou=VAdmin"
    write
  by dn.regex="^uid=.*,ou=Servers"
    read
  by dn.regex="^uid=$1,o=$2,ou=users"
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),o=(.*),ou=Users" attrs=mailLocalAddress,mailRoutingAddress,mailHost
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$2,ou=VAdmin"
    write
  by dn.regex="^uid=$1,o=$2,ou=users"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),o=(.*),ou=Users" attrs=outOfOfficeMSG,outOfOfficeActive,hostedSite,clearpassword,hostedFPSite
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$2,ou=VAdmin"
    write
  by dn.regex="^uid=$1,o=$2,ou=users"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),o=(.*),ou=Users" attrs=sambapwdcanchange,sambapwdlastset,sambapwdmustchange,accountSuspended,quotachanged
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$2,ou=VAdmin"
    write
  by dn.regex="^uid=.*,ou=Servers"
    read
  by dn.regex="^uid=$1,o=$2,ou=users"
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),o=(.*),ou=Users" attrs=userCertificate,userSMIMECertificate
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$2,ou=VAdmin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^uid=$1,o=$2,ou=users"
    write
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),o=(.*),ou=Users" attrs=certificateRequest,certificatesign,userPassword,shadowLastChange,userPKCS12
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$2,ou=VAdmin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^uid=$1,o=$2,ou=users"
    write
  by anonymous
    auth

access to dn.regex="^uid=(.*),o=(.*),ou=Users" attrs=gidnumber,uid
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$2,ou=VAdmin"
    read
  by dn.regex="^uid=.*,ou=Servers"
    read
  by dn.regex="^uid=$1,o=$2,ou=Users"
    read
  by *
    search
  by anonymous
    auth

access to dn.regex="^uid=(.*),o=(.*),ou=Users"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by group/virtZoneSettings.regex="cn=$2,ou=VAdmin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="cn=Snom,ou=Snom"
    read
  by dn.regex="^uid=$1,o=$2,ou=users"
    write
  </xsl:text><xsl:call-template name="ureadaccess"/><xsl:text>
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

#PDC Users
access to dn.regex="^sambaSID=(.*),ou=Idmap" attrs=objectClass
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^sambaSID=$1,ou=Idmap"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by *
    search
  by anonymous
    auth

access to dn.regex="^sambaSID=(.*),ou=Idmap" attrs=sn,givenName,mail,preferredLanguage,initials,ipHostNumber,description,physicalDeliveryOfficeName,postalAddress,postalCode,title,homePostalAddress,pager,conferenceInformation,ou,o,st,l,comment,URL,c
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^sambaSID=$1,ou=Idmap"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    none
  </xsl:text><xsl:call-template name="ureadaccess"/><xsl:text>
    read
  by anonymous
    auth

access to dn.regex="^sambaSID=(.*),ou=Idmap" attrs=telephoneNumber,facsimileTelephoneNumber,homePhone,otherFacsimiletelephoneNumber,mobile,IPPhone
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn="cn=Snom,ou=Snom"
    read
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^sambaSID=$1,ou=Idmap"
    write
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    none
  </xsl:text><xsl:call-template name="ureadaccess"/><xsl:text>
    read
  by anonymous
    auth

access to dn.regex="^sambaSID=(.*),ou=Idmap" attrs=displayName,cn,jpegPhoto
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^sambaSID=$1,ou=Idmap"
    write
  </xsl:text><xsl:call-template name="ureadaccess"/><xsl:text>
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

access to dn.regex="^sambaSID=(.*),ou=Idmap" attrs=radiusProfileDN,radiusrealm,dialupaccess,radiusporttype,radiusframedipaddress,radiusframedmtu,radiusframedcompression,radiussimultaneoususe,radiussessiontimeout,radiusidletimeout,radiusacctinteriminterval,radiusreplyitem,radiuscheckitem,radiusServiceType,radiusFramedProtocol,radiusFramedIPNetmask,radiusAuthType,maxwebaliases,maxaliases,maxmailboxes,quotaHomeDir,quotaFileServer,quotaMailSpool,squidProxyAccess,smbServerAccess,sambapwdlastset,sambapwdmustchange,sambapwdcanchange
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^sambaSID=$1,ou=Idmap"
    read
  by anonymous
    auth

access to dn.regex="^sambaSID=(.*),ou=Idmap" attrs=mailLocalAddress,mailRoutingAddress,mailHost
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=Servers"
    read
  by dn.regex="^sambaSID=$1,ou=Idmap"
    write
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

access to dn.regex="^sambaSID=(.*),ou=Idmap" attrs=outOfOfficeMSG,outOfOfficeActive,hostedSite,clearpassword,hostedFPSite
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^sambaSID=$1,ou=Idmap"
    write
  by anonymous
    auth

access to dn.regex="^sambaSID=(.*),ou=Idmap" attrs=sambapwdcanchange,sambapwdlastset,sambapwdmustchange,accountSuspended,quotachanged
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^sambaSID=$1,ou=Idmap"
    read
  by anonymous
    auth

access to dn.regex="^sambaSID=(.*),ou=Idmap" attrs=certificateRequest,certificatesign,userPassword,shadowLastChange,userPKCS12
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^sambaSID=$1,ou=Idmap"
    write
  by anonymous
    auth

access to dn.regex="^sambaSID=(.*),ou=Idmap" attrs=userSMIMECertificate,userCertificate
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^sambaSID=$1,ou=Idmap"
    write
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

access to dn.regex="^sambaSID=(.*),ou=Idmap" attrs=gidnumber,uid
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=Servers"
    read
  by dn.regex="^sambaSID=$1,ou=Idmap"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by *
    search
  by anonymous
    auth

access to dn.regex="^sambaSID=(.*),ou=Idmap"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="cn=Snom,ou=Snom"
    read
  by dn.regex="^sambaSID=$1,ou=Idmap"
    write
  </xsl:text><xsl:call-template name="ureadaccess"/><xsl:text>
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

#System Users
access to dn.regex="^uid=(.*),ou=Users" attrs=objectClass
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^uid=$1,ou=Users"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by *
    search
  by anonymous
    auth

access to dn.regex="^uid=(.*),ou=Users" attrs=sn,givenName,mail,initials,preferredLanguage,ipHostNumber,description,physicalDeliveryOfficeName,postalAddress,postalCode,title,homePostalAddress,pager,conferenceInformation,ou,o,st,l,comment,URL,c
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=$1,ou=Users"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    none
  </xsl:text><xsl:call-template name="ureadaccess"/><xsl:text>
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),ou=Users" attrs=telephoneNumber,facsimileTelephoneNumber,homePhone,otherFacsimiletelephoneNumber,mobile,IPPhone
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn="cn=Snom,ou=Snom"
    read
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^uid=$1,ou=Users"
    write
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    none
  </xsl:text><xsl:call-template name="ureadaccess"/><xsl:text>
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),ou=Users" attrs=displayName,cn,jpegPhoto
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^uid=$1,ou=Users"
    write
  </xsl:text><xsl:call-template name="ureadaccess"/><xsl:text>
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),ou=Users" attrs=radiusProfileDN,radiusrealm,dialupaccess,radiusporttype,radiusframedipaddress,radiusframedmtu,radiusframedcompression,radiussimultaneoususe,radiussessiontimeout,radiusidletimeout,radiusacctinteriminterval,radiusreplyitem,radiuscheckitem,radiusServiceType,radiusFramedProtocol,radiusFramedIPNetmask,radiusAuthType,maxwebaliases,maxaliases,maxmailboxes,quotaHomeDir,quotaFileServer,quotaMailSpool,squidProxyAccess,smbServerAccess,sambapwdlastset,sambapwdmustchange,sambapwdcanchange
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^uid=$1,ou=Users"
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),ou=Users" attrs=mailLocalAddress,mailRoutingAddress,mailHost
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=Servers"
    read
  by dn.regex="^uid=$1,ou=Users"
    write
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),ou=Users" attrs=outOfOfficeMSG,outOfOfficeActive,hostedSite,clearpassword,hostedFPSite
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^uid=$1,ou=Users"
    write
  by anonymous
    auth

access to dn.regex="^uid=(.*),ou=Users" attrs=sambapwdcanchange,sambapwdlastset,sambapwdmustchange,accountSuspended,quotachanged
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^uid=$1,ou=Users"
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),ou=Users" attrs=certificateRequest,certificatesign,userPassword,shadowLastChange,userPKCS12
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^uid=$1,ou=Users"
    write
  by anonymous
    auth

access to dn.regex="^uid=(.*),ou=Users" attrs=userSMIMECertificate,userCertificate
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn.regex="^uid=$1,ou=Users"
    write
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

access to dn.regex="^uid=(.*),ou=Users" attrs=gidnumber,uid
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=Servers"
    read
  by dn.regex="^uid=$1,ou=Users"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by *
    search
  by anonymous
    auth

access to dn.regex="^uid=(.*),ou=users"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="cn=Snom,ou=Snom"
    read
  by dn.regex="^uid=$1,ou=users"
    write
  </xsl:text><xsl:call-template name="ureadaccess"/><xsl:text>
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

#Restrict Access To Objectclass
access to attrs=objectclass
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by *
    search
  by anonymous
    auth


access to dn="cn=Domain"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by anonymous
    auth

access to dn.regex="^sambaDomainName=.*"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by anonymous
    auth

access to dn.regex="^uid=.*,ou=trusts"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by anonymous
    auth

access to dn.regex="^uid=.*,ou=servers"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by anonymous
    auth

access to dn.regex="^sendmailMTAClassName=(LDAPRoute|R),ou=Email"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by dn="</xsl:text><xsl:call-template name="confdn"/><xsl:text>"
    write
  by *
    read
  by anonymous
    auth

access to dn.regex="^.*,ou=Email"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

access to dn.regex="^.*,ou=Groups"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth

access to dn.regex="^.*,sendmailMTAMapName=.*,ou=Email"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="</xsl:text><xsl:call-template name="anondn"/><xsl:text>"
    read
  by anonymous
    auth</xsl:text>

  <xsl:apply-templates select="/config/LDAP/Directories/Directory"/>

  <xsl:text>
access to dn.regex=".*,ou=hosts"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by anonymous
    auth
  by dn="</xsl:text><xsl:call-template name="confdn"/><xsl:text>"
    none
  by *
    read

access to dn.regex="^ou=[a-zA-Z0-9]+" attrs=entry
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by *
    search
  by anonymous
    auth

access to dn.regex="^cn=(Domain|Admin)" attrs=entry
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by *
    search
  by anonymous
    auth

access to dn="cn=Addressbooks"
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by dn="</xsl:text><xsl:call-template name="confdn"/><xsl:text>"
    write
  by *
    read

access to *
  by dn="</xsl:text><xsl:call-template name="rootdn"/><xsl:text>"
    write
  by group="cn=Admin Access,ou=Admin"
    write
  by dn.regex="^uid=.*,ou=servers"
    read
  by anonymous
    auth
  by *
    read

pidfile		/var/run/slapd.pid
argsfile	/var/run/slapd.args

password-hash {CRYPT}
password-crypt-salt-format $6$%.12s

database        mdb
suffix          "ou=Log"
directory       /var/log/ldap
rootdn          uid=admin,ou=Log
index           default eq
index           entryCSN,objectClass,reqEnd,reqResult,reqStart eq
index           reqDN               eq
maxsize         2097152

overlay syncprov
syncprov-nopresent TRUE
syncprov-reloadhint TRUE

database        mdb
suffix		""
directory	/var/spool/ldap
lastmod         on
rootdn          </xsl:text><xsl:value-of select="/config/LDAP/Config/Option[@option = 'Login']"/><xsl:text>
maxsize         2097152

index           uid                     pres,sub,eq
index           uidNumber               pres,eq
index           gidNumber               pres,eq
index           memberUid               eq
index	        radiusRealm		eq
index           cn                      pres,eq,sub
index           sn                      pres,eq,sub
index           displayName             pres,sub,eq
index           email                   pres,sub,eq
index           givenName               pres,sub,eq
index           objectClass             eq
index           entryUUID               eq
index           contextCSN              eq
index           entryCSN                eq
index           sambaSID                pres,sub,eq
index           sambaPrimaryGroupSID    eq
index           sambaDomainName         eq
index           default                 sub
index           certificateSign         eq
index           certificateRequest      pres
index           outOfOfficeActive       eq
index           exchangeserveraccess    eq
index           sendmailMTAKey          eq,sub
index           accountSuspended        eq
index           mailLocalAddress        eq,sub
index           sambaGroupType          eq
index           sambaSIDList            eq
index		sendmailMTAClassName    eq
index		sendmailMTACluster      eq
index		sendmailMTAHost         eq
index		sendmailMTAMapName      eq
index           sendmailMTAAliasGrouping eq
index           sendmailMTAAliasValue  eq
index           member                  pres,eq

overlay refint
refint_attributes member
refint_nothing uid=admin,ou=Users

overlay unique
unique_base </xsl:text>
  <xsl:call-template name="domtosuf">
    <xsl:with-param name="dom" select="concat($domain,'.')"/>
  </xsl:call-template>
  <xsl:text>
unique_attributes uid uidNumber mailLocalAddress
#unique_strict

overlay rwm
rwm-rewriteEngine on
rwm-rewriteContext defaulti
rwm-rewriteContext searchDN alias defaulti
rwm-rewriteContext bindDN alias defaulti
rwm-rewriteContext searchFilterAttrDN alias defaulti
rwm-rewriteContext compareDN alias defaulti
rwm-rewriteContext compareAttrDN alias defaulti
rwm-rewriteContext addDN alias defaulti
rwm-rewriteContext modifyDN alias defaulti
rwm-rewriteContext modrDN alias defaulti
rwm-rewriteContext newSuperiorDN alias defaulti
rwm-rewriteContext deleteDN alias defaulti
rwm-rewriteContext exopPasswdDN alias defaulti
#rwm-rewriteContext addAttrDN alias defaulti
#rwm-rewriteContext modifyAttrDN alias defaulti
#rwm-rewriteContext searchFilter alias defaulti
#rwm-rewriteContext referralAttrDN alias defaulti
rwm-rewriteRule "(.+),</xsl:text>
  <xsl:call-template name="domtosuf">
    <xsl:with-param name="dom" select="concat($domain,'.')"/>
  </xsl:call-template>
  <xsl:text>$" "$1" ":@"
rwm-rewriteRule "^</xsl:text>
  <xsl:call-template name="domtosuf">
    <xsl:with-param name="dom" select="concat($domain,'.')"/>
  </xsl:call-template>
  <xsl:text>$" "" ":@"

rwm-rewriteContext defaulto
rwm-rewriteContext searchEntryDN alias defaulto
rwm-rewriteContext matchedDN alias defaulto
#rwm-rewriteContext searchAttrDN alias defaulto
#rwm-rewriteContext referralDN alias defaulto
rwm-rewriteRule "^dc=(.+)" "dc=$1" ":@"
rwm-rewriteRule "^(.+),</xsl:text>
  <xsl:call-template name="domtosuf">
    <xsl:with-param name="dom" select="concat($domain,'.')"/>
  </xsl:call-template>
  <xsl:text>$" "$1,</xsl:text>
  <xsl:call-template name="domtosuf">
    <xsl:with-param name="dom" select="concat($domain,'.')"/>
  </xsl:call-template>
  <xsl:text>" ":@"
rwm-rewriteRule "(.+)" "$1,</xsl:text>
  <xsl:call-template name="domtosuf">
    <xsl:with-param name="dom" select="concat($domain,'.')"/>
  </xsl:call-template>
  <xsl:text>" ":@"

overlay syncprov
syncprov-checkpoint 100 10

overlay accesslog
logdb ou=Log
logops writes
logsuccess FALSE
logpurge 7+00:00 00+04:00
</xsl:text>
  <xsl:apply-templates select="/config/LDAP/Replica"/>
</xsl:template>
</xsl:stylesheet>

