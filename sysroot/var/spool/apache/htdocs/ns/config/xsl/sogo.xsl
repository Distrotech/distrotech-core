<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="sogoprefrk" select="/config/Email/Config/Option[@option = 'ScanChildren'] * 4"/>
<xsl:param name="sqlserver" select="'127.0.0.1'"/>
<xsl:param name="exchangepass" select="/config/SQL/Option[@option = 'PGExchange']"/>
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>

<xsl:template name="domtosuf">
  <xsl:param name="domsuf"/>
  <xsl:param name="dom"/>

  <xsl:choose>
    <xsl:when test="$dom = ''">
      <xsl:value-of select="$domsuf"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="domtosuf">
        <xsl:with-param name="dom" select="substring-after($dom,'.')"/>
        <xsl:with-param name="domsuf" select="concat($domsuf,',dc=',substring-before($dom,'.'))"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="sogoldap">
  <xsl:text>	    {
		CNFieldName = cn;
		IDFieldName = uid;
		UIDFieldName = uid;
		baseDN = "ou=users</xsl:text>
    <xsl:call-template name="domtosuf">
      <xsl:with-param name="dom" select="concat($domain,'.')"/>
    </xsl:call-template>
<xsl:text>";
		bindDN = "</xsl:text>
    <xsl:value-of select="concat('uid=ldap_limited_',$hname,',',/config/LDAP/Config/Option[@option = 'Login'])"/>
    <xsl:call-template name="domtosuf">
      <xsl:with-param name="dom" select="concat($domain,'.')"/>
    </xsl:call-template>
<xsl:text>";
		bindPassword = </xsl:text><xsl:value-of select="$ldaplimpw"/><xsl:text>;
		canAuthenticate = YES;
		bindAsCurrentUser = YES;
		bindFields = (uid);
		displayName = "Corporate Directory";
		hostname = 127.0.0.1:389;
		id = public;
		isAddressBook = YES;
	    }
</xsl:text>
</xsl:template>


<xsl:template name="sogoad">
<xsl:text>            {   
                CNFieldName = cn;
                IDFieldName = cn;
                UIDFieldName = sAMAccountName;
                baseDN = "$sogo{'sbase'}$sogo{'base'}";
                bindDN = "$dn";
                bindFields = sAMAccountName;
                bindPassword = $sogo{'password'};
                searchFields = "($sogo{'sfilter'})";
                filter = "$sogo{'filter'}";
                canAuthenticate = YES;
                displayName = "Global Address Book";
                hostname = $sogo{'host'};
                id = public;
                isAddressBook = YES;
            }
</xsl:text>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>{
    NSGlobalDomain = {
    };
    gdnc = {
    };
    sogod = {
	OCSFolderInfoURL = "postgresql://exchange:</xsl:text>
    <xsl:value-of select="concat($exchangepass,'@',$sqlserver)"/><xsl:text>:5432/exchange/sogo_folder_info";
	OCSSessionsFolderURL = "postgresql://exchange:</xsl:text>
    <xsl:value-of select="concat($exchangepass,'@',$sqlserver)"/><xsl:text>:5432/exchange/sogo_sessions_folder";
	SOGoAppointmentSendEMailNotifications = YES;
	SOGoDraftsFolderName = Drafts;
	SOGoIMAPAclConformsToIMAPExt = YES;
	SOGoIMAPAclStyle = rfc4314;
	SOGoIMAPAclUsernamesAreQuoted = YES;
	SOGoIMAPServer = 127.0.0.1:286;
	SOGoLDAPQueryLimit = 10;
	SOGoLDAPQueryTimeout = 20;
	SOGoLanguage = English;
	SOGoLoginModule = Mail;
	SOGoMailDomain = </xsl:text><xsl:value-of select="$domain"/><xsl:text>;
	SOGoMailShowSubscribedFoldersOnly = NO;
	SOGoMailSpoolPath = "/tmp";
	SOGoMailingMechanism = smtp;
	SOGoOtherUsersFolderName = Shared;
	SOGoProfileURL = "postgresql://exchange:</xsl:text>
    <xsl:value-of select="concat($exchangepass,'@',$sqlserver)"/><xsl:text>:5432/exchange/sogo_user_profile";
	SOGoSMTPServer = 127.0.0.1;
	SOGoSentFolderName = Sent;
	SOGoSharedFolderName = Public;
	SOGoSpecialFoldersInRoot = YES;
	SOGoTimeZone = Africa/Johannesburg;
	SOGoTrashFolderName = Trash;
	SOGoUseLocationBasedSentFolder = YES;
	SOGoUserSources = (
</xsl:text>
  <xsl:call-template name="sogoldap"/>
  <xsl:text>	);
	WOMessageUseUTF8 = YES;
	WOParsersUseUTF8 = YES;
	WOUseRelativeURLs = YES;
	WOWorkersCount = </xsl:text><xsl:value-of select="$sogoprefrk"/><xsl:text>;
    };
}</xsl:text>
</xsl:template>
</xsl:stylesheet>
