<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />
<xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'" />
<xsl:variable name="defdom" select="translate(/config/FileServer/Setup/Option[@option = 'ADSRealm'],$smallcase,$uppercase)"/>
<xsl:variable name="workgroup" select="translate(/config/FileServer/Setup/Option[@option = 'Domain'],$smallcase,$uppercase)"/>
<xsl:variable name="security" select="/config/FileServer/Setup/Option[@option = 'Security']"/>
<xsl:variable name="intiface" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="intip" select="/config/IP/Interfaces/Interface[text() = $intiface]/@ipaddr"/>
<xsl:variable name="extint" select="/config/IP/SysConf/Option[@option = 'External']"/>
<xsl:variable name="intint" select="/config/IP/SysConf/Option[@option = 'Internal']"/>
<xsl:variable name="extcon" select="/config/IP/Dialup/Option[@option = 'Connection']"/>
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>

<xsl:template match="Share">
  <xsl:value-of select="concat($nl,'[',.,']',$nl)"/>
  <xsl:if test="@av = 'true'">
    <xsl:text>        vfs objects = clamav&#xa;</xsl:text>
  </xsl:if>
  <xsl:value-of select="concat('        comment = &quot;',.,'&quot;',$nl)"/>
  <xsl:value-of select="concat('        path = /var/spool/samba/',@folder,$nl)"/>
  <xsl:text>        read only = No&#xa;</xsl:text>
  <xsl:text>        create mask = </xsl:text>
  <xsl:choose>
    <xsl:when test="@grw = 'true'">
      <xsl:text>066</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>064</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:choose>
    <xsl:when test="@uread = 'true'">
      <xsl:text>4&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>0&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>        directory mask = </xsl:text>
  <xsl:choose>
    <xsl:when test="@grw = 'true'">
      <xsl:text>077</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>0755</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:choose>
    <xsl:when test="@uread = 'true'">
      <xsl:text>5&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>0&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>        hide unreadable = Yes&#xa;</xsl:text>
  <xsl:choose>
    <xsl:when test="@uread = 'false'">
      <xsl:value-of select="concat('        valid users = +',@group,$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>        valid users = +users&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:value-of select="concat('        force group = +',@group,$nl)"/>
  <xsl:text>        guest ok = No&#xa;</xsl:text>
</xsl:template>

<xsl:template name="usergrp">
  <xsl:choose>
    <xsl:when test="$security != 'USER'">
      <xsl:value-of select="concat('&quot;+',$workgroup,'\Domain Users&quot;')"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>+users</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="admingrp">
  <xsl:choose>
    <xsl:when test="$security != 'USER'">
      <xsl:choose>
        <xsl:when test="$linadmin = '1'">
          <xsl:value-of select="concat('&quot;+',$workgroup,'\Linux Admin Users&quot;')"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="concat('&quot;+',$workgroup,'\Domain Admins&quot;')"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>+smbadm</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

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

<xsl:template match="Interface">
  <xsl:if test="($extint = $intint) or (. != $extint)">
    <xsl:if test="position() &gt; 0">
      <xsl:text> </xsl:text>
    </xsl:if>
    <xsl:value-of select="."/>
  </xsl:if>
</xsl:template>

<xsl:template name="addcomma">
  <xsl:param name="items"/>
  <xsl:variable name="curitem" select="substring-before($items,' ')"/>
  <xsl:variable name="nextitem" select="substring-after($items,' ')"/>

  <xsl:if test="$curitem != ''">
    <xsl:value-of select="$curitem"/>
    <xsl:if test="$nextitem != ''">
      <xsl:value-of select="', '"/>
    </xsl:if>
  </xsl:if>

  <xsl:if test="$nextitem != ''">
    <xsl:call-template name="addcomma">
      <xsl:with-param name="items" select="$nextitem"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="browsesrvs">
  <xsl:param name="servers"/>
  <xsl:param name="output"/>

  <xsl:variable name="cur" select="substring-before($servers,' ')"/>
  <xsl:variable name="next" select="substring-after($servers,' ')"/>

  <xsl:variable name="active">
    <xsl:choose>
      <xsl:when test="$cur != ''">
        <xsl:value-of select="$cur"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$servers"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:choose>
    <xsl:when test="$active != ''">
      <xsl:call-template name="browsesrvs">
        <xsl:with-param name="servers" select="$next"/>
        <xsl:with-param name="output" select="concat($output,' ',$active,'/',$workgroup)"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat($output,$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="remsync">
  <xsl:param name="servers"/>
  <xsl:param name="output"/>

  <xsl:variable name="cur" select="substring-before($servers,' ')"/>
  <xsl:variable name="next" select="substring-after($servers,' ')"/>

  <xsl:variable name="active">
    <xsl:choose>
      <xsl:when test="$cur != ''">
        <xsl:value-of select="$cur"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$servers"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:choose>
    <xsl:when test="$active != ''">
      <xsl:call-template name="remsync">
        <xsl:with-param name="servers" select="$next"/>
        <xsl:with-param name="output" select="concat($output,' ',$active)"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat($output,$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="/config/FileServer/Config/Option">
  <xsl:choose>
    <xsl:when test="@option = 'netbios name'">
      <xsl:value-of select="concat('        netbios aliases = ',.,$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('        ',@option,' = ',.,$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="/config/FileServer/Config/Item">
  <xsl:choose>
    <xsl:when test="starts-with(.,'netbios name = ')">
      <xsl:value-of select="concat('        netbios aliases = ',substring(.,16),$nl)"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('        ',.,$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="/config">
  <xsl:text>[global]&#xa;</xsl:text>
  <xsl:value-of select="concat('        workgroup = ',/config/FileServer/Setup/Option[@option = 'Domain'],$nl)"/>

  <xsl:apply-templates select="/config/FileServer/Config/Option"/>
  <xsl:apply-templates select="/config/FileServer/Config/Item"/>

  <xsl:value-of select="concat('        netbios name = ',translate(/config/DNS/Config/Option[@option = 'Hostname'],$smallcase,$uppercase),$nl)"/>
  <xsl:choose>
    <xsl:when test="(/config/FileServer/@homedir != '') and (/config/FileServer/@sharedir != '')">
      <xsl:text>        domain logons = Yes&#xa;</xsl:text>
      <xsl:value-of select="concat('        logon drive = ',/config/FileServer/@homedir,':',$nl)"/>
      <xsl:text>        logon script = logon.bat&#xa;</xsl:text>
      <xsl:text>        logon path = \\%L\%U\.ntprofile&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('        logon drive = U:',$nl)"/>
      <xsl:text>        logon path = \\%L\%U\.ntprofile&#xa;</xsl:text>
      <xsl:text>        logon script = logon.bat&#xa;</xsl:text>
      <xsl:text>        domain logons = No&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:choose>
    <xsl:when test="$security = 'ADS'">
      <xsl:text>        password server = </xsl:text>
      <xsl:call-template name="addcomma">
        <xsl:with-param name="items" select="concat(translate(/config/FileServer/Setup/Option[@option = 'ADSServer'],$smallcase,$uppercase),' ')"/>
      </xsl:call-template>
      <xsl:text>&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:if test="$security != 'USER'">
        <xsl:text>        password server = *&#xa;</xsl:text>
      </xsl:if>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:text>        log file = /var/log/samba
        preserve case = Yes
        case sensitive = No
        name resolve order = lmhosts host wins bcast
        guest account = nobody
        hide dot files = Yes
        hide unreadable = Yes
        deadtime = 5&#xa;</xsl:text>

  <xsl:value-of select="concat('        security = ',$security,$nl)"/>
  <xsl:text>        obey pam restrictions = Yes&#xa;</xsl:text>
  <xsl:text>        message command = /usr/sbin/smbim %s %t %f &amp;&#xa;</xsl:text>

  <xsl:if test="$security = 'ADS'">
    <xsl:value-of select="concat('        realm = ',$defdom,$nl)"/>
  </xsl:if>

  <xsl:choose>
    <xsl:when test="(/config/IP/SysConf/Option[@option = 'PrimaryWins'] = $intip) or 
                    (count(/config/IP/SysConf/Option[@option = 'PrimaryWins']) = 0)">
      <xsl:text>        wins support = Yes&#xa;</xsl:text>
      <xsl:text>        wins hook = /usr/sbin/wins_hook&#xa;</xsl:text>
      <xsl:if test="/config/FileServer/Setup/Option[@option = 'RemoteSync'] != ''">
        <xsl:text>        remote announce = </xsl:text>
        <xsl:call-template name="browsesrvs">
          <xsl:with-param name="servers" select="/config/FileServer/Setup/Option[@option = 'RemoteSync']"/>
        </xsl:call-template>
        <xsl:text>        remote browse sync = </xsl:text>
        <xsl:call-template name="remsync">
          <xsl:with-param name="servers" select="/config/FileServer/Setup/Option[@option = 'RemoteSync']"/>
        </xsl:call-template>
      </xsl:if>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat('        wins server = ',/config/IP/SysConf/Option[@option = 'PrimaryWins'])"/>
      <xsl:if test="/config/IP/SysConf/Option[@option = 'SecondaryWins'] != ''">
        <xsl:value-of select="concat(' ',/config/IP/SysConf/Option[@option = 'SecondaryWins'])"/>
      </xsl:if>
      <xsl:text>&#xa;</xsl:text>
      <xsl:text>        remote announce = </xsl:text>
      <xsl:value-of select="concat(/config/IP/SysConf/Option[@option = 'PrimaryWins'],' ',/config/FileServer/Setup/Option[@option = 'RemoteSync'],$nl)"/>
      <xsl:text>        remote browse sync = </xsl:text>
      <xsl:value-of select="concat(/config/IP/SysConf/Option[@option = 'PrimaryWins'],' ',/config/FileServer/Setup/Option[@option = 'RemoteSync'],$nl)"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>        interfaces = </xsl:text>
  <xsl:apply-templates select="/config/IP/Interfaces/Interface[(@ipaddr != '0.0.0.0') and (@subnet &lt;= 30)]"/>
  <xsl:if test="$avahi = '1'">
    <xsl:value-of select="concat(' ',$intint,':avahi')"/>
  </xsl:if>
  <xsl:if test="$dhcp = '1'">
    <xsl:value-of select="concat(' ',$intint,':dhcp')"/>
  </xsl:if>
  <xsl:text> 127.0.0.1</xsl:text>
  <xsl:text>&#xa;</xsl:text>
  <xsl:text>        bind interfaces only = Yes&#xa;</xsl:text>
  <xsl:text>        strict allocate = Yes&#xa;</xsl:text>
  <xsl:text>        ldap ssl = off&#xa;</xsl:text>
  <xsl:value-of select="concat('        ldap admin dn = ',/config/LDAP/Config/Option[@option = 'Login'],$nl)"/>
  <xsl:text>        map to guest = Bad User&#xa;</xsl:text>

  <xsl:text>        ldap suffix = </xsl:text>
  <xsl:call-template name="domtosuf">
    <xsl:with-param name="dom" select="concat($domain,'.')"/>
  </xsl:call-template>
  <xsl:text>&#xa;</xsl:text>

  <xsl:text>        ldap idmap suffix = ou=Idmap&#xa;</xsl:text>
  <xsl:text>        ldap user suffix = ou=Users&#xa;</xsl:text>
  <xsl:text>        ldap group suffix = ou=Groups&#xa;</xsl:text>
  <xsl:text>        ldap machine suffix = ou=Trusts&#xa;</xsl:text>

  <xsl:text>        idmap config * : backend      = ldap&#xa;</xsl:text>
  <xsl:text>        idmap config * : range        = 524288-589824&#xa;</xsl:text>
  <xsl:text>        idmap config * : ldap_url     = ldaps://</xsl:text>
  <xsl:value-of select="concat(/config/LDAP/Config/Option[@option = 'Server'],$nl)"/>
  <xsl:text>        idmap config * : ldap_base_dn = ou=idmap,</xsl:text>
  <xsl:call-template name="domtosuf">
    <xsl:with-param name="dom" select="concat($domain,'.')"/>
  </xsl:call-template>
  <xsl:text>&#xa;</xsl:text>

  <xsl:text>        idmap config * : ldap_user_dn = </xsl:text>
  <xsl:value-of select="concat(/config/LDAP/Config/Option[@option = 'Login'],$nl)"/>

  <xsl:choose>
    <xsl:when test="(/config/FileServer/Setup/Option[@option = 'Winbind'] = 'Both') or (/config/FileServer/Setup/Option[@option = 'Winbind'] = 'Users Only')">
      <xsl:text>        winbind enum users = yes&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>        winbind enum users = no&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:choose>
    <xsl:when test="(/config/FileServer/Setup/Option[@option = 'Winbind'] = 'Both') or (/config/FileServer/Setup/Option[@option = 'Winbind'] = 'Groups Only')">
      <xsl:text>        winbind enum groups = yes&#xa;</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>        winbind enum groups = no&#xa;</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>        winbind cache time = 600&#xa;</xsl:text>
  <xsl:text>        winbind use default domain = yes&#xa;</xsl:text>
  <xsl:text>        winbind refresh tickets = No&#xa;</xsl:text>
  <xsl:text>        winbind offline logon = Yes&#xa;</xsl:text>
  <xsl:text>        template homedir = hash2&#xa;</xsl:text>
  <xsl:text>        template primary group = users&#xa;</xsl:text>
  <xsl:text>        template shell = /usr/sbin/smrsh&#xa;</xsl:text>
  <xsl:text>        ldapsam:trusted = yes&#xa;</xsl:text>
  <xsl:text>        ldap passwd sync = Yes&#xa;</xsl:text>
  <xsl:text>        passdb backend = ldapsam:ldaps://</xsl:text>
  <xsl:value-of select="/config/LDAP/Config/Option[@option = 'Server']"/>
  <xsl:text>&#xa;</xsl:text>

  <xsl:if test="($security = 'USER') or ($security = 'SHARE')">
    <xsl:text>        add machine script = /usr/sbin/addtrust %u %M&#xa;</xsl:text>
    <xsl:text>        add user script = /usr/sbin/adduser %u&#xa;</xsl:text>
    <xsl:text>        delete user script = /usr/sbin/deluser %u&#xa;</xsl:text>
    <xsl:text>        add user to group script = /usr/sbin/addugroup %u %g&#xa;</xsl:text>
    <xsl:text>        delete user from group script = /usr/sbin/delugroup %u %g&#xa;</xsl:text>
    <xsl:text>        add group script = /usr/sbin/addgroup %g&#xa;</xsl:text>
    <xsl:text>        delete group script = /usr/sbin/delgroup %g&#xa;</xsl:text>
    <xsl:text>        set primary group script = /usr/sbin/usergroup %u %g&#xa;</xsl:text>
  </xsl:if>

  <xsl:text>
[NETLOGON]
        comment = Net Logon
        path = /var/spool/samba/netlogon
        writeable = yes
        locking = No
        public = No
        create mask = 0664
        directory mask = 0775
        guest ok = No

[SHAREDFILES]
        comment = Open File Share
        path = /var/spool/samba/share
        read only = No
        create mask = 0664
        directory mask = 0775
        hide unreadable = Yes
        guest ok = No
        valid users = </xsl:text>
  <xsl:call-template name="usergrp"/>

<xsl:text>

[FTP]
        comment = Files Avail. Via FTP
        path = /var/spool/samba/ftp
        read only = No
        create mask = 0664
        directory mask = 0775
        guest ok = No
        hide unreadable = Yes
        force group = nogroup
        force user = nobody
        valid users = </xsl:text>
  <xsl:call-template name="usergrp"/>

<xsl:text>

[homes]
        comment = Home Directory
        profile acls = yes
        read only = No
        browsable = No
        writeable = Yes
        create mask = 0640
        directory mask = 0750
        hide unreadable = Yes
        guest ok = No

[TFTPBOOT]
        comment = TFTP Directory
        path = /tftpboot
        read only = No
        create mask = 0644
        directory mask = 0755
        hide unreadable = Yes
        force group = nogroup
        force user = nobody
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>
  <xsl:text>
        guest ok = No
</xsl:text>

  <xsl:if test="$cdrom = '1'">
    <xsl:text>
[DVDDRIVE]
        comment = CD/DVD
        path = /mnt/autofs/cd
        read only = Yes
        create mask = 0664
        directory mask = 0775
        hide unreadable = Yes
        force user = admin
        valid users = </xsl:text>
  <xsl:call-template name="usergrp"/>
  <xsl:text>
        guest ok = No&#xa;</xsl:text>
  </xsl:if>

  <xsl:if test="$torrent = '1'">
    <xsl:text>
[TORRENTS]
        comment = Wharez baby wharez !!!!
        path = /root/torrent
        read only = Yes
        create mask = 0664
        directory mask = 0775
        hide unreadable = Yes
        force user = nobody
        valid users = </xsl:text>
  <xsl:call-template name="usergrp"/>
  <xsl:text>
        hide files = /*.meta
        guest ok = No&#xa;</xsl:text>
  </xsl:if>

  <xsl:text>
[MAILSCANNER]
	comment = Files Used By Mailscanner
	path = /opt/MailScanner/etc/reports/en
        read only = No
        create mask = 0664
        directory mask = 0775
        guest ok = No
        force user = admin
        hide unreadable = Yes
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>
  <xsl:text>

[MAILLISTS]
	comment = Majordomo Mailing Lists
	path = /opt/majordomo/lists
        read only = No
        create mask = 0644
        directory mask = 0755
        guest ok = No
        hide unreadable = Yes
        force group = majordomo
        force user = majordomo
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>
  <xsl:text>

[QUARANTINE]
	comment = Files Blocked By Mailscanner
	path = /var/spool/mailscanner/quarantine/
        read only = No
        create mask = 0664
        directory mask = 0775
        guest ok = No
        hide unreadable = Yes
        force user = admin
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>
  <xsl:text>

[ARCHIVE]
        comment = Email Archived By Mailscanner
        path = /var/spool/mailscanner/archive/
        read only = No
        create mask = 0660
        directory mask = 0770
        guest ok = No
        hide unreadable = Yes
        force user = admin
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>
  <xsl:text>

[RECORDINGS]
        comment = VOIP Recordings
        path = /var/spool/asterisk/monitor
        read only = No
        create mask = 0660
        directory mask = 0770
        guest ok = No
        hide unreadable = Yes
        force user = admin
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>
  <xsl:text>

[FAX]
        comment = Recived Faxes
        path = /var/spool/asterisk/fax
        read only = No
        create mask = 0660
        directory mask = 0770
        guest ok = No
        hide unreadable = Yes
        force user = admin
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>
  <xsl:text>

[ASTFIRMWARE]
        comment = VOIP Firmware
        path = /var/lib/asterisk/firmware
        read only = No
        create mask = 0660
        directory mask = 0770
        guest ok = No
        hide unreadable = Yes
        force user = admin
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>
  <xsl:text>

[MUSICONHOLD]
        comment = VOIP Music On Hold
        path = /var/lib/asterisk/moh
        read only = No
        create mask = 0660
        directory mask = 0770
        guest ok = No
        hide unreadable = Yes
        force user = admin
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>
  <xsl:text>

[ASTKEYS]
        comment = VOIP Public Keys
        path = /var/lib/asterisk/keys
        read only = No
        create mask = 0660
        directory mask = 0770
        guest ok = No
        hide unreadable = Yes
        force user = admin
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>
  <xsl:text>

[VOICEMAIL]
        comment = VOIP Voice Mail Folder
        path = /var/spool/asterisk/voicemail/6
        read only = No
        create mask = 0660
        directory mask = 0770
        guest ok = No
        hide unreadable = Yes
        force user = admin
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>
  <xsl:text>

[ASTSOUNDS]
        comment = VOIP IVR Sounds And Prompts
        path = /var/lib/asterisk/sounds
        read only = No
        create mask = 0660
        directory mask = 0770
        guest ok = No
        hide unreadable = Yes
        force user = admin
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>
  <xsl:text>

[USERS]
        comment = All Users Home Dorectories
        path = /var/home
        read only = No
        create mask = 0660
        directory mask = 0770
        guest ok = No
        hide unreadable = Yes
        force group = users
        force user = admin
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>
  <xsl:text>

[EMAIL]
        comment = All Users Inboxes
        path = /var/spool/mail
        read only = No
        create mask = 0660
        directory mask = 0770
        guest ok = No
        hide unreadable = Yes
        force user = admin
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>
  <xsl:text>

[DNS]
        comment = DNS Zone Files
        path = /var/named
        read only = No
        create mask = 0660
        directory mask = 0770
        guest ok = No
        hide unreadable = Yes
        force user = admin
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>
  <xsl:text>

[VPNCERTS]
        comment = VPN Certificates
        path = /etc/ipsec.d
        read only = No
        create mask = 0660
        directory mask = 0770
        guest ok = No
        hide unreadable = Yes
        force user = admin
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/>

  <xsl:text>

[POLYCOM]
        comment = "Polycom Firmware"
        path = /var/spool/apache/htdocs/polycom
        read only = No
        guest ok = No
        hide unreadable = Yes
        create mask = 0640
        directory mask = 0750
        hide files = /*.php/*.inc/
        force user = admin
        valid users = </xsl:text>
  <xsl:call-template name="admingrp"/><xsl:text>&#xa;</xsl:text>

  <xsl:if test="((/config/FileServer/@homedir != '') and (/config/FileServer/@sharedir != '')) or ($security != 'USER')">
    <xsl:text>
[SRVTOOLS]
        comment = Domain Administration Tools
        path = /var/spool/samba/dadmin
        read only = Yes
        create mask = 0660
        directory mask = 0770
        guest ok = No
        hide unreadable = Yes
        force group = smbadm
        force user = admin
        valid users = </xsl:text>
    <xsl:call-template name="admingrp"/><xsl:text>&#xa;</xsl:text>
  </xsl:if>

  <xsl:if test="$backup = '1'">
    <xsl:text>
[BACKUP]
        comment = Backup Sync
        path = /var/spool/backup
        read only = No
        create mask = 0660
        directory mask = 0770
        guest ok = No
        hide unreadable = Yes
        force user = admin
        valid users = </xsl:text>
    <xsl:call-template name="admingrp"/><xsl:text>&#xa;</xsl:text>
  </xsl:if>

  <xsl:apply-templates select="/config/FileServer/Shares/Share"/>
<!--
XXX
/etc/printers
name driver ip port
HP4200:pxlmono:192.168.25.144:9100

  if (-e "/etc/printers") {
    open(NETP,"/etc/printers");
    while(<NETP>) {
      chop($_);
      @pdata=split(/:/,$_);
[@pdata[0]]
        comment = @pdata[0]
        path = /tmp
        printable = Yes
        postscript = No
        print command = lpr -r -P@pdata[0] %s
        lpq command = lpq -P@pdata[0]
        lprm command = lprm -P@pdata[0] %j
    }
  }

if ($printer{'LPT1'}) {
[$printer{'LPT1'}]
        comment = $printer{'LPT1'}
        path = /tmp
        printable = Yes
        postscript = No
        print command = lpr -r -Plp0 %s
        lpq command = lpq -Plp0
        lprm command = lprm -Plp0 %j
  }

if ($printer{'LPT2'}) {
[$printer{'LPT2'}]
        comment = $printer{'LPT2'}
        path = /tmp
        printable = Yes
        postscript = No
        print command = lpr -r -Plp1 %s
        lpq command = lpq -Plp1
        lprm command = lprm -Plp1 %j

  }
-->
</xsl:template>
</xsl:stylesheet>
