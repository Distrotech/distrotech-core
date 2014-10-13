<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:variable name='nl'><xsl:text>&#xa;</xsl:text></xsl:variable>
<xsl:output method="text" omit-xml-declaration="yes" indent="no" />
<xsl:variable name="domain" select="/config/DNS/Config/Option[@option = 'Domain']"/>
<xsl:variable name="hname" select="/config/DNS/Config/Option[@option = 'Hostname']"/>
<xsl:variable name="fqdn" select="concat($hname,'.',$domain)"/>

<xsl:template match="/config">
  <xsl:text>%etc-dir% = /opt/MailScanner/etc
%mcp-dir% = /opt/MailScanner/etc/mcp
%org-long-name% = </xsl:text><xsl:value-of select="$fqdn"/><xsl:text>
%org-name% = </xsl:text><xsl:value-of select="$domain"/><xsl:text>
%report-dir% = /opt/MailScanner/etc/reports/en
%rules-dir% = /opt/MailScanner/etc/rules
%web-site% = </xsl:text><xsl:value-of select="concat('www.',$domain)"/><xsl:text>
Add Envelope From Header = yes
Add Envelope To Header = no
Add Watermark = yes
Allow External Message Bodies = no
Allow Filenames =
Allow Filetypes =
Allow Form Tags = yes
Allow IFrame Tags = </xsl:text>
  <xsl:choose>
    <xsl:when test="/config/Email/Config/Option[@option = 'IFrame'] = 'true'">
      <xsl:text>yes</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>no</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>
Allow Object Codebase Tags = </xsl:text>
  <xsl:choose>
    <xsl:when test="/config/Email/Config/Option[@option = 'Object'] = 'true'">
      <xsl:text>yes</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>no</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>
Allow Partial Messages = no
Allow Password-Protected Archives = no
Allow Script Tags = disarm
Allow WebBugs = disarm
Allowed Sophos Error Messages =
Also Find Numeric Phishing = yes
Always Include MCP Report = no
Always Include SpamAssassin Report = no
Always Looked Up Last = no
Always Looked Up Last After Batch = no</xsl:text>
  <xsl:if test="/config/Email/Config/Option[@option = 'Archive'] = 'true'">
    <xsl:text>'Archive Mail = /var/spool/mailscanner/archive&#xa;</xsl:text>
  </xsl:if>
  <xsl:text>
Attach Image To HTML Message Only = yes
Attach Image To Signature = no
Attachment Encoding Charset = ISO-8859-1
Attachment Extensions Not To Zip = .zip .rar .gz .tgz .jpg .jpeg .mpg .mpe .mpeg .mp3 .rpm .htm .html .eml
Attachment Warning Filename = %org-name%-Attachment-Warning.txt
Attachments Min Total Size To Zip = 100k
Attachments Zip Filename = MessageAttachments.zip
Block Encrypted Messages = no
Block Unencrypted Messages = no
Bounce MCP As Attachment = no
Bounce Spam As Attachment = no
Cache SpamAssassin Results = yes
Check SpamAssassin If On Spam List = yes
Check Watermarks To Skip Spam Checks = yes
Check Watermarks With No Sender = yes
ClamAV Full Message Scan = yes
ClamAVmodule Maximum Compression Ratio = 250
ClamAVmodule Maximum File Size = 10000000 # (10 Mbytes)
ClamAVmodule Maximum Files = 1000
ClamAVmodule Maximum Recursion Level = 8
Clamd Lock File = # /var/lock/subsys/clamd
Clamd Port = 3310
Clamd Socket = /tmp/clamd
Clamd Use Threads = yes
Clean Header Value       = Found to be clean
Content Modify Subject = start
Content Subject Text = {BLOCKED}
Convert Dangerous HTML To Text = </xsl:text>
  <xsl:choose>
    <xsl:when test="/config/Email/Config/Option[@option = 'HTML'] = 'true'">
      <xsl:text>yes</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>no</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>
Convert HTML To Text = no
Country Sub-Domains List = %etc-dir%/country.domains.conf
Custom Functions Dir = /opt/MailScanner/lib/MailScanner/CustomFunctions
Custom Spam Scanner Timeout = 20
Custom Spam Scanner Timeout History = 20
Dangerous Content Scanning = yes
Debug = no
Debug SpamAssassin = no
Definite MCP Is High Scoring = no
Definite Spam Is High Scoring = no
Deleted Bad Content Message Report  = %report-dir%/deleted.content.message.txt
Deleted Bad Filename Message Report = %report-dir%/deleted.filename.message.txt
Deleted Size Message Report        = %report-dir%/deleted.size.message.txt
Deleted Virus Message Report        = %report-dir%/deleted.virus.message.txt
Deliver Cleaned Messages = yes
Deliver Disinfected Files = no
Deliver In Background = yes
Deliver Unparsable TNEF = no
Delivery Method = </xsl:text>
  <xsl:choose>
    <xsl:when test="(/config/Email/Config/Option[@option = 'Delivery'] = 'Deffered') or (/config/Email/Config/Option[@option = 'Delivery'] = 'Queue')">
      <xsl:text>queue</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>batch</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>
Deny Filenames =
Deny Filetypes =
Detailed MCP Report = yes
Detailed Spam Report = yes
Disarmed Modify Subject = start
Disarmed Subject Text = {DISARMED}
Disinfected Header Value = Disinfected
Disinfected Report = %report-dir%/disinfected.report.txt
Enable Spam Bounce = %rules-dir%/bounce.rules
Envelope From Header = X-%org-name%-MailScanner-From:
Envelope To Header = X-%org-name%-MailScanner-To:
Expand TNEF = yes
File Command = #/usr/bin/file
File Timeout = 20
Filename Modify Subject = start
Filename Rules = %etc-dir%/filename.rules.conf
Filename Subject Text = {BLOCKED}
Filetype Rules = %etc-dir%/filetype.rules.conf
Find Archives By Content = yes
Find Phishing Fraud = yes
Find UU-Encoded Files = yes
First Check = mcp
Gunzip Command = /usr/bin/gunzip
Gunzip Timeout = 50
Hide Incoming Work Dir = yes
Hide Incoming Work Dir in Notices = yes
High Scoring MCP Actions = deliver
High Scoring MCP Modify Subject = start
High Scoring MCP Subject Text = {MCP?}
High Scoring Spam Actions = delete
High Scoring Spam Modify Subject = start
High Scoring Spam Subject Text = {SPAM}
High SpamAssassin Score = </xsl:text>
  <xsl:choose>
    <xsl:when test="/config/Email/Config/Option[@option = 'MaxScore'] != ''">
      <xsl:value-of select="/config/Email/Config/Option[@option = 'MaxScore']"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>5</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>
Highlight Phishing Fraud = yes
Hostname = the %org-name% ($HOSTNAME) MailScanner
Ignore Spam Whitelist If Recipients Exceed = 20
Ignored Web Bug Filenames = spacer pixel.gif pixel.png gap
Include Scanner Name In Reports = yes
Include Scores In MCP Report = no
Include Scores In SpamAssassin Report = yes
Incoming Queue Dir = /var/spool/mqueue.in
Incoming Work Dir = /var/spool/mailscanner/incoming
Incoming Work Group =
Incoming Work Permissions = 0600
Incoming Work User =
Infected Header Value    = Found to be infected
Information Header = X-%org-name%-MailScanner-Information:
Information Header Value = Please contact Network Sentry  for more information (www.networksentry.co.za)
Inline HTML Signature = %report-dir%/inline.sig.html
Inline HTML Warning = %report-dir%/inline.warning.html
Inline Spam Warning = %report-dir%/inline.spam.warning.txt
Inline Text Signature = %report-dir%/inline.sig.txt
Inline Text Warning = %report-dir%/inline.warning.txt
Is Definitely MCP = no
Is Definitely Not MCP = no
Is Definitely Not Spam = %rules-dir%/spam.whitelist.rules
Is Definitely Spam = no
Keep Spam And MCP Archive Clean = no
Known Web Bug Servers = msgtag.com
Language Strings = %report-dir%/languages.conf
Local Postmaster = postmaster
Lock Type = flock
Lockfile Dir = /var/lock/mailscanner
Log Dangerous HTML Tags = no
Log MCP = no
Log Non Spam = yes
Log Permitted Filenames = no
Log Permitted Filetypes = no
Log Silent Viruses = yes
Log Spam = yes
Log Speed = yes
MCP Actions = deliver
MCP Checks = no
MCP Error Score = 1
MCP Header = X-%org-name%-MailScanner-MCPCheck:
MCP High SpamAssassin Score = 10
MCP Max SpamAssassin Size = 100k
MCP Max SpamAssassin Timeouts = 20
MCP Modify Subject = start
MCP Required SpamAssassin Score = 1
MCP SpamAssassin Default Rules Dir = %mcp-dir%
MCP SpamAssassin Install Prefix = %mcp-dir%
MCP SpamAssassin Local Rules Dir = %mcp-dir%
MCP SpamAssassin Prefs File = %mcp-dir%/mcp.spam.assassin.prefs.conf
MCP SpamAssassin Timeout = 10
MCP SpamAssassin User State Dir =
MCP Subject Text = {MCP?}
MTA = sendmail
Mail Header = X-%org-name%-MailScanner:
MailScanner Version Number = 4.62.9
Mark Infected Messages = yes
Mark Unscanned Messages = yes
Max Children = </xsl:text><xsl:value-of select="/config/Email/Config/Option[@option = 'ScanChildren']"/><xsl:text>
Max Custom Spam Scanner Size = 20k
Max Custom Spam Scanner Timeouts = 10
Max Normal Queue Size = 800
Max Spam Check Size = 150000
Max Spam List Timeouts = 7
Max SpamAssassin Size = 100k
Max SpamAssassin Timeouts = 10
Max Unsafe Bytes Per Scan = 50m
Max Unsafe Messages Per Scan = 30
Max Unscanned Bytes Per Scan = 100m
Max Unscanned Messages Per Scan = 30
Maximum Archive Depth = </xsl:text><xsl:value-of select="/config/Email/Config/Option[@option = 'ZipLevel']"/><xsl:text>
Maximum Attachment Size = -1
Maximum Attachments Per Message = 200
Maximum Message Size = %rules-dir%/max.message.size.rules
Minimum Attachment Size = -1
Minimum Code Status = supported
Minimum Stars If On Spam List = 0
Monitors For Sophos Updates = /usr/local/Sophos/ide/*ides.zip
Monitors for ClamAV Updates = /var/spool/avirus/*.inc/* /var/spool/avirus/*.cvd
Multiple Headers = replace
Never Notify Senders Of Precedence = list bulk
Non MCP Actions = deliver
Non Spam Actions = deliver header "X-Spam-Status: No"
Non-Forging Viruses = Joke/ OF97/ WM97/ W97M/ eicar
Notice Signature = \n---\nNetwork Sentry Email Protection Administrator Alert\n
Notices From = MailScanner
Notices Include Full Headers = no
Notices To = postmaster
Notify Senders = no
Notify Senders Of Blocked Filenames Or Filetypes = yes
Notify Senders Of Blocked Size Attachments = no
Notify Senders Of Other Blocked Content = yes
Notify Senders Of Viruses = no
Outgoing Queue Dir = /var/spool/mqueue
PID file = /var/run/mailscanner.pid
Phishing Modify Subject = yes
Phishing Safe Sites File = %etc-dir%/phishing.safe.sites.conf
Phishing Subject Text = {FRAUD}
Quarantine Dir = /var/spool/mailscanner/quarantine
Quarantine Group =
Quarantine Infections = </xsl:text>
  <xsl:choose>
    <xsl:when test="/config/Email/Config/Option[@option = 'Quarantine'] = 'true'">
      <xsl:text>yes</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>no</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>
Quarantine Modified Body = no
Quarantine Permissions = 0600
Quarantine Silent Viruses = no
Quarantine User =
Quarantine Whole Message = no
Quarantine Whole Messages As Queue Files = no
Queue Scan Interval = </xsl:text>
  <xsl:choose>
    <xsl:when test="/config/Email/Config/Option[@option = 'Rescan'] &gt; 0">
      <xsl:choose>
        <xsl:when test="/config/Email/Config/Option[@option = 'Rescan'] &lt; 5">
          <xsl:value-of select="/config/Email/Config/Option[@option = 'Rescan'] * 15"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="/config/Email/Config/Option[@option = 'Rescan']"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>10</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>
Rebuild Bayes Every = 0
Recipient MCP Report = %report-dir%/recipient.mcp.report.txt
Recipient Spam Report = %report-dir%/recipient.spam.report.txt
Reject Message = no
Rejection Report = %report-dir%/rejection.report.txt
Remove These Headers = X-Mozilla-Status: X-Mozilla-Status2:
Required SpamAssassin Score = </xsl:text>
  <xsl:choose>
    <xsl:when test="/config/Email/Config/Option[@option = 'MinScore'] != ''">
      <xsl:value-of select="/config/Email/Config/Option[@option = 'MinScore']"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>0</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>
Restart Every = 3600
Run As Group =
Run As User =
Run In Foreground = no
Scan Messages = yes
Scanned Modify Subject = no # end
Scanned Subject Text = {Scanned}
Send Notices = yes
Sender Bad Filename Report = %report-dir%/sender.filename.report.txt
Sender Content Report        = %report-dir%/sender.content.report.txt
Sender Error Report        = %report-dir%/sender.error.report.txt
Sender MCP Report = %report-dir%/sender.mcp.report.txt
Sender Size Report         = %report-dir%/sender.size.report.txt
Sender Spam List Report    = %report-dir%/sender.spam.rbl.report.txt
Sender Spam Report         = %report-dir%/sender.spam.report.txt
Sender SpamAssassin Report = %report-dir%/sender.spam.sa.report.txt
Sender Virus Report        = %report-dir%/sender.virus.report.txt
Sendmail = /usr/sbin/sendmail
Sendmail2 = /usr/sbin/sendmail
Sign Clean Messages = </xsl:text><xsl:value-of select="$msgsign"/><xsl:text>
Sign Messages Already Processed = no
Signature Image &lt;img&gt; Filename = signature.jpg
Signature Image Filename = %report-dir%/sig.jpg
Silent Viruses = HTML-IFrame All-Viruses
Size Modify Subject = start
Size Subject Text = {SIZE}
Sophos IDE Dir = /usr/local/Sophos/ide
Sophos Lib Dir = /usr/local/Sophos/lib
Spam Actions = striphtml deliver header "X-Spam-Status: Yes"
Spam Checks = yes
Spam Domain List =
Spam Header = X-%org-name%-MailScanner-SpamCheck:
Spam List = </xsl:text>
  <xsl:choose>
    <xsl:when test="/config/Email/Config/Option[@option = 'AntiSpam'] = 'true'">
      <xsl:text>SORBS-DNSBL</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:text></xsl:text>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>
Spam List Definitions = %etc-dir%/spam.lists.conf
Spam List Timeout = 10
Spam List Timeouts History = 10
Spam Lists To Be Spam = 1
Spam Lists To Reach High Score = 3
Spam Modify Subject = start
Spam Score = yes
Spam Score Character = s
Spam Score Header = X-%org-name%-MailScanner-SpamScore:
Spam Score Number Format = %d
Spam Subject Text = {SPAM}
SpamAssassin Auto Whitelist = no
SpamAssassin Cache Database File = /var/spool/mailscanner/incoming/SpamAssassin.cache.db
SpamAssassin Cache Timings = 1800,300,10800,172800,600
SpamAssassin Default Rules Dir =
SpamAssassin Install Prefix =
SpamAssassin Local Rules Dir =
SpamAssassin Local State Dir = # /var/lib/spamassassin
SpamAssassin Rule Actions =
SpamAssassin Site Rules Dir = /etc/mail/spamassassin
SpamAssassin Temporary Dir = /var/spool/mailscanner/incoming/SpamAssassin-Temp
SpamAssassin Timeout = 75
SpamAssassin Timeouts History = 30
SpamAssassin User State Dir =
SpamScore Number Instead Of Stars = no
Split Exim Spool = no
Still Deliver Silent Viruses = no
Stored Bad Content Message Report  = %report-dir%/stored.content.message.txt
Stored Bad Filename Message Report = %report-dir%/stored.filename.message.txt
Stored Size Message Report        = %report-dir%/stored.size.message.txt
Stored Virus Message Report        = %report-dir%/stored.virus.message.txt
Syslog Facility = mail
TNEF Expander = internal
#/opt/MailScanner/bin/tnef --maxsize=100000000
TNEF Timeout = 120
Treat Invalid Watermarks With No Sender as Spam = nothing
Unrar Command = /usr/bin/unrar
Unrar Timeout = 50
Unscanned Header Value = Not scanned: please contact your Internet E-Mail Service Provider for details
Use Custom Spam Scanner = no
Use Default Rules With Multiple Recipients = no
Use SpamAssassin = yes
Use Stricter Phishing Net = yes
Use TNEF Contents = replace
Use Watermarking = yes
Virus Modify Subject = start
Virus Scanner Definitions = %etc-dir%/virus.scanners.conf
Virus Scanner Timeout = 300
Virus Scanners = clamd
Virus Scanning = yes
Virus Subject Text = {VIRUS}
Wait During Bayes Rebuild = no
Warning Is Attachment = yes
Watermark Header = X-%org-name%-MailScanner-Watermark:
Watermark Lifetime = 604800
Watermark Secret = %org-name%-WilliWonka
Web Bug Replacement = http://www.sng.ecs.soton.ac.uk/mailscanner/images/1x1spacer.gif
Zip Attachments = no
</xsl:text>
</xsl:template>
</xsl:stylesheet>
