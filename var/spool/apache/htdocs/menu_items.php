<%
/*
#    Copyright (C) 2002  <Gregory Hinton Nietsky>
#    Copyright (C) 2006  <Superset>
#
#    This program is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program; if not, write to the Free Software
#    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (is_file("/var/spool/apache/htdocs/config.inc")) {
  include_once("/var/spool/apache/htdocs/config.inc");
}

if (! is_array($a_limit)) {
  $a_limit=array();
}

$sessid=session_id();
if ($sessid == "") {
  if (isset($_GET['sesname'])) {
    $_SESSION['sesname']=$_GET['sesname'];
  } else {
    $_SESSION['sesname']="server_admin";
  }
  ob_start('ob_gzhandler');
  include "/var/spool/apache/htdocs/session.inc";
  session_name($_SESSION['sesname']);
  session_set_cookie_params(28800);
  session_start();
}

if (!isset($authtype)) {
  $authtype=-1;
}

function openpage($menu,$page) {
  return "javascript:openpage(\'" . $page . "\',\'" . $menu . "\')";
}

function openapage($page) {
  return "javascript:openapage(\'" . $page . "\',\'" . $_SESSION['classi'] . "\')";
}

function openmclass($menu,$class) {
  return "javascript:openmclass(\'" . $menu . "\',\'" . $class . "\')";
}

function openmmap($menu,$mmapv) {
  return "javascript:openmmap(\'" . $menu . "\',\'" . $mmapv . "\')";
}



if ($authtype >= 0) {
  $menu=array("support","tickdep","apps","userssl","adminssl","setup","sslrev","ssl","email","aliases","groups","users","virtdom","inet","vcsvup","vcsvdown","callshop","vroute","snomc","zap","voip","radius","vstatus","status","main");
  $main[_("User Applications")]="include:apps";
  $main[_("SSL Certificates")]="include:ssl";
  if (($authtype >= 0) && ($_SESSION['userid'] != "admin")) {
    $main[_("User Settings")]="include:setup";
  }
  if (($authtype == 0) && ($voipauth > 0)) {
    $main[_("VOIP Config")]="include:voip";
  }
  if (($authtype == 0) && (($voipauth > 0) || ($tmsauth > 0) || ($clogauth > 0))) {
    $main[_("System Status/Logs")]="include:status";
  }
  if ($authtype > 0) {
    $main[_("System Config")]="include:inet";
    $main[_("VOIP Config")]="include:voip";
    $main[_("System Status/Logs")]="include:status";
  } else if ($vdoms["count"] > 0) {
    $main[_("System Config")]="include:inet";
  }
  $main[_("Logoff")]="javascript:vboxlogoff()";
  if ((isset($_SESSION['userid'])) && (is_file($_SESSION['userid'] . ".php"))) {
    $main[$_SESSION['userid']]="javascript:AJAX.senddata(\'main-body\',\'loadpage\',\'/auth/gregory.php\')";
  }

} else {
  $main[_("User Applications")]="include:apps";
  $main[_("SSL Certificates")]="include:ssl";
  $menu=array("apps","login","support","ssl","main");
  $main[_("Login")]="/auth";
}

mysql_connect('localhost', 'admin', 'admin');
mysql_select_db('osticket');
$tdepsq=mysql_query("select dept_name,dept_id from ost_department order by dept_name");
if ($_SESSION['showmenu'] == "tickdep") {
  $tickdep[_("User Applications ..")]=openpage("apps","");
}
while($tdeps=mysql_fetch_array($tdepsq,MYSQL_NUM)) {
  $tickdep[_($tdeps[0])]="javascript:openticket($tdeps[1],\'" . _($tdeps[0]) . "\')";
}

if ($authtype >= 0) {
  $apps[_("VOIP Console")]="javascript:openvoip()";
  $apps[_("Available State")]="javascript:openpbxstate()";
//  $apps[_("Todo List")]=openpage("apps","cdr/todolst.php");
  $apps[_("CRM")]="javascript:opencrm()";
//  $apps[_("Webmail")]="javascript:openmail()";
  $apps[_("Groupware")]="javascript:opensogo()";
  if ($authtype >= 0) {
    $apps[_("Call Centre Admin")]="javascript:openccadmin()";
    $apps[_("VOIP Billing")]="javascript:opencshop()";
  }
  $apps[_("CC. Agent Login")]="javascript:openccagent()";
  $apps[_("Message Board")]="javascript:openbb()";
  if (count($tickdep) > 0) {
    $apps[_("Ticket Status")]="include:tickdep";
  }
  $apps[_("Quick Call")]="javascript:openc2c()";
  $apps[_("Agent Login/Out")]="javascript:openagentapp()";
  //$apps[_("Google Talk Call")]="javascript:opengtalk()";
  $apps[_("Send Fax")]="javascript:openpsfax()";
  $apps[_("PS To PDF")]=openpage("apps","auth/ps2pdf.php");
  $apps[_("Register ATA")]="javascript:openlsysconf()";
  //$apps[_("Cubit Accounting")]="javascript:opencubit()";
  $apps[_("Extension List")]=openpage("apps","cdr/elist.php");
  $apps[_("Telephone Tarrifs")]=openpage("apps","lplist.php");
  $apps[_("Int. Dial Codes")]=openpage("apps","cdr/cclist.php");
  $apps[_("Office Hours")]=openpage("apps","pubhol.php");
  $apps[_("Support")]="include:support";
} else {
  $apps[_("VOIP Console")]="javascript:openvoip()";
  $apps[_("Available State")]="javascript:openpbxstate()";
  if ((! isset($a_limit['CRM'])) || ($a_limit['CRM'])) {
    $apps[_("CRM")]="javascript:opencrm()";
  }
  if ((! isset($a_limit['GWARE'])) || ($a_limit['GWARE'])) {
    $apps[_("Groupware")]="javascript:opensogo()";
  }	
  if ((! isset($a_limit['MBOARD'])) || ($a_limit['MBOARD'])) {
    $apps[_("Message Board")]="javascript:openbb()";
  }
/*
  if (count($tickdep) > 0) {
    $apps[_("Ticket Status")]="include:tickdep";
  }
*/
  
  if ((! isset($a_limit['ACD'])) || ($a_limit['ACD'])) {
    $apps[_("CC. Agent Login")]="javascript:openccagent()";
    $apps[_("Agent Login/Out")]="javascript:openagentapp()";
  }
  $apps[_("Quick Call")]="javascript:openc2c()";
  $apps[_("Extension List")]=openpage("apps","cdr/elist.php");
  $apps[_("Telephone Tarrifs")]=openpage("apps","lplist.php");
  $apps[_("Int. Dial Codes")]=openpage("apps","cdr/cclist.php");
  $apps[_("Office Hours")]=openpage("apps","pubhol.php");
  $apps[_("Support")]="include:support";
}

$utsr=@ldap_search($ds,"ou=Idmap","(&(uid=" . $_SESSION['userid'] . ")(objectclass=sambaIdmapEntry))",array("objectclass"));

$setup[_("Personal Details")]="javascript:openupage(\'ldap/userinfo.php\',\'setup\')";
$setup[_("Email Aliases")]="javascript:openidata(\'email\')";
$setup[_("Extra Mail Boxes")]="javascript:openupage(\'auth/mailbox.php\',\'setup\')";
$setup[_("Mail Delivery")]="javascript:openupage(\'auth/maildel.php\',\'setup\')";
$setup[_("Hosted Web Sites")]="javascript:openidata(\'www\')";
$setup[_("Auth. Profiles")]="javascript:openupage(\'auth/radius.php\',\'setup\')";
$setup[_("SSL Certificate")]="include:userssl";
$setup[_("Edit Photo Album")]="javascript:openupage(\'auth/photo.php\',\'setup\')";
$setup[_("Photo Album")]="javascript:openualbum(\'" . $_SESSION['userid'] . "\')";
$setup[_("Disk Quotas")]="javascript:openupage(\'auth/quota.php\',\'setup\')";

$setup[_("Access Control")]="javascript:openupage(\'auth/access.php\',\'setup\')";

if (! @ldap_count_entries($ds,$utsr)) {
  $setup[_("Password Expiry")]="javascript:openupage(\'auth/pwexp.php\',\'setup\')";
}

$setup[_("Extension Details")]="javascript:openvoipconf()";
$setup[_("Hosted DNS Admin")]="javascript:openupage(\'dnsadmin.php\',\'setup\')";


if ($_SESSION['showmenu'] == "userssl") {
  $userssl[_("User Settings ..")]=openpage("setup","");
}

$userssl[_("View Certificate")]=openpage("userssl","auth/sslcert.php");
$userssl[_("Certificate Store")]=openpage("userssl","auth/sslstore.php");
$userssl[_("Update Certificate")]=openpage("userssl","auth/sslcreate.php");

for($vdcnt=0;$vdcnt < $vdoms["count"];$vdcnt++) {
  $virtdom[$vdoms[$vdcnt][_("cn")][0]]="javascript:openvirtrealm(\'" . $vdoms[$vdcnt]['cn'][0] . "\')";
  if ($vdoms[$vdcnt][_("cn")][0] == $_SESSION['utype']) {
    $virtuser="javascript:openvirtrealm(\'" . $vdoms[$vdcnt][_("cn")][0] . "\')";
  }
}

$iarr=array("userPKCS12","usercertificate;binary","userSMIMECertificate");
$cdescrip["userPKCS12"]="Private Key Chain";
$cdescrip["userSMIMECertificate"]="Public Key Chain";
$cdescrip["userCertificate;binary"]="Public Certificate";

$sr=@ldap_search($ds,"","(&(objectClass=officePerson)(uid=" . $_SESSION['userid'] . "))",$iarr);

$centry = @ldap_first_entry($ds,$sr);
$attrs=@ldap_get_attributes($ds,$centry);

if ($attrs["count"] > 0) {
  asort($attrs);
  reset($attrs);
  $cgeturl[_("userPKCS12")]="/cert/" . $_SESSION['userid'] . ".p12";
  $cgeturl[_("userCertificate;binary")]="/cert/" . $_SESSION['userid'] . ".crt";
  $cgeturl[_("userSMIMECertificate")]="/cert/" . $_SESSION['userid'] . ".p7b";

  for ($acnt=0;$acnt < $attrs["count"];$acnt++) {
    $userssl[$cdescrip[$attrs[$acnt]]]=$cgeturl[$attrs[$acnt]];
    if ($attrs[$acnt] == "userCertificate;binary") {
      $userssl[_("Public Key File")]="/cert/" . $_SESSION['userid'] . ".pub";
      $userssl[_("Public SSH Key")]="/cert/" . $_SESSION['userid'] . ".ssh";
    }
    if ($attrs[$acnt] == "userPKCS12") {
      $userssl[_("Private Key File")]="javascript:getrsakey(\'" . $_SESSION['userid'] . "\')";
      $userssl[_("Open VPN Config")]="javascript:getovpnconf(\'" . $_SESSION['userid'] . "\')";
    }
  }
}

$ssl[_("CA Certificate")]="/cert/ca.pem";
$ssl[_("CRL Certificate")]="/cert/crl.pem";

if ($authtype > 0) {
  $ssl[_("View/Revoke Certs")]="include:sslrev";
  $ssl[_("Sign SSL Request")]=openpage("ssl","ssl/index.php");
  $ssl[_("Server Request")]="/ssl/server.req";
  $ssl[_("CA Request")]="/ssl/ca.req";
}

/*
$aliases[_("Email Aliases")]=openpage("aliases","ldap/maliases.php");
$aliases[_("Mailing Lists")]=openpage("aliases","ldap/maliases.php");
*/

if ($_SESSION['showmenu'] == "email") {
$email[_(".. System Config")]=openpage("inet","");
}
$email[_("Virtual Alias")]=openmmap("email","virtuser");
$email[_("SMTP Redirect")]=openmmap("email","mailer");
$email[_("Access Control")]=openmmap("email","access");
$email[_("Relay Domains")]=openmclass("email","R");
$email[_("Accepted Domains")]=openmclass("email","LDAPRoute");
$email[_("Masqueradeing")]=openmclass("email","M");
$email[_("Domain Rewriting")]=openmmap("email","domain");
$email[_("SMTP Client Auth.")]=openmmap("email","authinfo");
$email[_("Mail Routing")]=openpage("email","ldap/mailroute.php");
$email[_("Spam Check Bypass")]=openmclass("email","WhiteList");
$email[_("Virus Check Bypass")]=openmclass("email","VirusSafe");
$email[_("Horde Domain Map")]=openmmap("email","horde");

if ($_SESSION['showmenu'] == "users") {
  $users[_(".. System Config")]=openpage("inet","");
}

$users[_("System Users")]="javascript:usersetup(\'system\')";
$users[_("PDC Users")]="javascript:usersetup(\'pdc\')";
$users[_("Trust Accounts")]="javascript:usersetup(\'trust\')";
/*
$users[_("Email Routing")]="javascript:usersetup(\'mserver\')";
$users[_("Server Accounts")]="javascript:usersetup(\'server\')";
*/
$groups[_("Administrators")]="javascript:openagroup(\'cn=Admin Access,ou=Admin\')";
$groups[_("VOIP Administrator")]="javascript:openagroup(\'cn=Voip Admin,ou=Admin\')";
$groups[_("Access To TMS")]="javascript:openagroup(\'cn=TMS Access,ou=Admin\')";
$groups[_("Call Logging Access")]="javascript:openagroup(\'cn=Call Logging,ou=Admin\')";
$groups[_("Access To User Info.")]="javascript:openagroup(\'cn=User Read Access,ou=Admin\')";
$groups[_("Call Centre Admin.")]="javascript:openagroup(\'cn=Call Centre Admin,ou=Admin\')";

/*
$groups[_("Addressbook Access")]="javascript:openagroup(\'cn=Users,cn=Addressbook\')";
*/

if ($authtype > 0) {
  $inet[_("Admin Console")]="javascript:openns()";
  $inet[_("Admin Password")]=openpage("inet","auth/passwd.php");
  if ($authtype > 1) {
//    $inet[_("Admin SSH Key")]="/auth/sshkey.ppk";
    $inet[_("Certificate Store")]=openpage("inet","auth/sslstore.php");
  }
  $inet[_("Email Server Setup")]="include:email";
  $inet[_("Aliases/Mailing Lists")]=openpage("inet","ldap/maliases.php");
//"include:aliases";
  $inet[_("User Accounts")]="include:users";
}
if (($authtype > 0) ||  ($vdoms["count"] > 0)) {
  if ($vdoms["count"] > 0) {
    $inet[_("Virtual Realm Users")]="include:virtdom";
   }
  if ($authtype > 0) {
    $inet[_("System Groups")]=openpage("inet","ldap/ugroup.php");
    $inet[_("Access Groups")]="include:groups";
  }
  $inet[_("Virtual Realm Admin")]=openpage("inet","ldap/vzone.php");
}

if ($authtype > 0) {
  $inet[_("DNS Administration")]=openpage("inet","auth/dnsadmin.php");
  $inet[_("MySQL Admin")]="javascript:opendbadmin()";
  if (is_readable("/var/spool/apache/htdocs/ns/config/sysvars")) {
    $inet[_("Reconfigure System")]=openpage("inet","auth/reconf.php");
  }
}



$voip[_("Extension Setup")]=openpage("voip","cdr/mkuser.php");
if ($authtype > 0) {
  $voip[_("PABX Setup")]=openpage("voip","cdr/vadmin.php");
  $voip[_("Routing Setup")]="include:vroute";
}
$voip[_("ACD Setup")]=openpage("voip","cdr/mkacd.php");
$voip[_("ACD Agents")]=openpage("voip","cdr/agents.php");
if ($authtype > 0) {
  $voip[_("Auto Attendant")]="javascript:openreception()";
  $voip[_("IVR Admin")]=openpage("voip","cdr/ivradmin.php");
}
$voip[_("Web Phone Setup")]=openpage("voip","cdr/htmlphone.php");
if ($authtype > 0) {
  $voip[_("Snom Config")]="include:snomc";
  $voip[_("Inter Branch Routing")]=openpage("voip","cdr/routesetup.php");
  $voip[_("Batch Create Exten.")]=openpage("voip","cdr/mkexten.php");
  $voip[_("GSM Channel Setup")]=openpage("voip","cdr/gsmroute.php");
  $voip[_("Trunk Configuration")]="include:zap";
  $voip[_("CSV Upload")]="include:vcsvup";
  $voip[_("CSV Download")]="include:vcsvdown";
  $voip[_("Extension Template")]="/cdr/extenimport.zip";
  $voip[_("Group Access Control")]=openpage("voip","cdr/gauth.php");
  $voip[_("Callshop Setup")]="include:callshop";
  $callshop[_("VOIP Providers")]=openpage("callshop","cdr/csprovider.php");
  $callshop[_("VOIP Gateways")]=openpage("callshop","cdr/h323peer.php");
  $callshop[_("VOIP Neighbours")]=openpage("callshop","cdr/h323neigh.php");
};


if ($_SESSION['showmenu'] == "vroute") {
  $vroute[_(".. Voip Config")]=openpage("voip","");
}
$vroute[_("DDI Forwarding")]=openpage("vroute","cdr/ddiadmin.php");
$vroute[_("DDI Port Forwarding")]=openpage("vroute","cdr/ddipfwd.php");
$vroute[_("DDI FAX Box")]=openpage("vroute","cdr/ddifax.php");
//$vroute[_("DDI IVR Defaults")]=openpage("vroute","cdr/ddidefault.php");
$vroute[_("Speed Dials")]=openpage("vroute","cdr/sdial.php");
$vroute[_("Group Routing")]=openpage("vroute","cdr/grproute.php");
$vroute[_("Local Prefix Setup")]=openpage("vroute","cdr/lpreadmin.php");
$vroute[_("Local Area Code(s)")]=openpage("vroute","cdr/lareaadmin.php");
$vroute[_("Emergancy Numbers")]=openpage("vroute","cdr/emergancy.php");
$vroute[_("Trunk Forward Map")]=openpage("vroute","cdr/trunkfwd.php");
$vroute[_("CLI Trunk Rewrite")]=openpage("vroute","cdr/clifwd.php");
$vroute[_("ACD Prefix Setup")]=openpage("vroute","cdr/acdpreadmin.php");

if ($_SESSION['showmenu'] == "snomc") {
  $snomc[_(".. Voip Config")]=openpage("voip","");
}
$snomc[_("Snom Phonebook")]="javascript:usersetup(\'snom\')";
//$snomc[_("Snom P.Book Pass.")]=openpage("snomc","auth/snompass.php");
$snomc[_("Snom Firmware")]=openpage("snomc","cdr/snomload.php");

if ($_SESSION['showmenu'] == "zap") {
  $zap[_(".. Voip Config")]=openpage("voip","");
}
$zap[_("Trunk Group Setup")]=openpage("zap","cdr/mkzap.php");
$zap[_("Trunk Group Channels")]=openpage("zap","cdr/mkzapc.php");
$zap[_("Trunk Spans (TDM)")]=openpage("zap","cdr/mkspan.php");
$zap[_("Trunk Spans (Dyn.)")]=openpage("zap","cdr/mkdspan.php");

$vcsvup[_("Extensions")]="javascript:voipcsvup(\'vcsvup\',\'exten\')";
$vcsvup[_("Contact Details")]="javascript:voipcsvup(\'vcsvup\',\'elist\')";
$vcsvup[_("Exten. Protocol")]="javascript:voipcsvup(\'vcsvup\',\'protocol\')";
$vcsvup[_("Snom Mac Map")]="javascript:voipcsvup(\'vcsvup\',\'snommac\')";
$vcsvup[_("Snom Phone Book")]="javascript:voipcsvup(\'vcsvup\',\'snompbook\')";
$vcsvup[_("Call Queues (ACD)")]="javascript:voipcsvup(\'vcsvup\',\'acd\')";
$vcsvup[_("ACD Agents")]="javascript:voipcsvup(\'vcsvup\',\'agents\')";
$vcsvup[_("Inter Branch Routing")]="javascript:voipcsvup(\'vcsvup\',\'ibroute\')";
$vcsvup[_("Operator Pannel")]="javascript:voipcsvup(\'vcsvup\',\'console\')";
$vcsvup[_("Genral Config")]="javascript:voipcsvup(\'vcsvup\',\'setup\')";
$vcsvup[_("GSM Channels")]="javascript:voipcsvup(\'vcsvup\',\'gsmchan\')";
$vcsvup[_("ZAP Setup")]="javascript:voipcsvup(\'vcsvup\',\'zapsetup\')";
$vcsvup[_("Tel. Costs")]="javascript:voipcsvup(\'vcsvup\',\'telcosts\')";
$vcsvup[_("Speed Dials")]="javascript:voipcsvup(\'vcsvup\',\'speeddial\')";

$vcsvdown[_("Extentions")]="/csv/exten.csv";
$vcsvdown[_("Contact Details")]="/csv/elist.csv";
$vcsvdown[_("Exten. Protocol")]="/csv/protocol.csv";
$vcsvdown[_("Snom Mac Map")]="/csv/snommac.csv";
$vcsvdown[_("Snom Phone Book")]="/csv/snompbook.csv";
$vcsvdown[_("Call Queues (ACD)")]="/csv/acd.csv";
$vcsvdown[_("ACD Agents")]="/csv/agents.csv";
$vcsvdown[_("Inter Branch Routing")]="/csv/ibroute.csv";
$vcsvdown[_("Operator Pannel")]="/csv/console.csv";
$vcsvdown[_("Genral Config")]="/csv/setup.csv";
$vcsvdown[_("GSM Channels")]="/csv/gsmchan.csv";
$vcsvdown[_("ZAP Setup")]="/csv/zapsetup.csv";
$vcsvdown[_("Speed Dials")]="/csv/speeddial.csv";

$sslrev[_("Valid Certificates")]="javascript:ajaxssl(\'V\')";
$sslrev[_("Revoked Certificates")]="javascript:ajaxssl(\'R\')";
$sslrev[_("All Certificates")]="javascript:ajaxssl(\'ALL\')";

$support[_("Web Server Manual")]="javascript:openman(\'/manual\')";
$support[_("Mod. SSL Manual")]="javascript:openman(\'/manual/mod/mod_ssl\')";
$support[_("PHP Manual")]="javascript:openman(\'/phpmanual\')";
$support[_("MySQL Manual")]="javascript:openman(\'/mysqlmanual\')";
$support[_("PostgreSQL Manual")]="javascript:openman(\'/postgresql\')";

$radius[_("User Usage")]=openpage("radius","radius/getuse.php");
$radius[_("Open Sessions")]=openpage("radius","radius/radses.php");
$radius[_("Abuse Tracker")]=openpage("radius","radius/abuse.php");

if ($authtype > 0){
  $vstatus[_("PBX Call Graphs")]=openpage("vstatus","rrdgraph/voip.php");
}

if (($tmsauth > 0) || ($authtype > 0) || ($clogauth > 0) || ($voipauth > 0 )){
  $vstatus[_("Queue Status")]=openpage("vstatus","cdr/qstatus.php");
  $vstatus[_("Call Reports")]=openpage("vstatus","cdr/list.php");
  if (($clogauth > 0) || ($authtype > 0) || ($voipauth > 0 )) {
    $vstatus[_("Call Logging")]=openpage("vstatus","cdr/calllog.php");
  }
  $vstatus[_("ACD Report")]=openpage("vstatus","cdr/queuerep.php");
  $vstatus[_("ACD SL Report")]=openpage("vstatus","cdr/queueslrep.php");
  $vstatus[_("Usage Report")]=openpage("vstatus","cdr/qagntrep.php");
  $vstatus[_("Extension Report")]=openpage("vstatus","cdr/extenrep.php");
  $vstatus[_("Month End Report")]=openpage("vstatus","cdr/mendrep.php");
  $vstatus[_("Monthly Call List")]=openpage("vstatus","cdr/aclist.php");
  $vstatus[_("Routing Report")]=openpage("vstatus","cdr/routingrep.php");
}

if (($authtype > 0) || ($voipauth > 0 )){
  if ($authtype > 0) {
    $vstatus[_("GSM Channel Status")]=openpage("vstatus","cdr/gsmreport.php");
    if (!is_dir("/etc/lcr")) {
      $vstatus[_("ISDN Status")]=openpage("vstatus","cdr/getisdn.php");
    }
  }
  if ($authtype > 0) {
    if (!is_dir("/etc/lcr")) {
      $vstatus[_("BRI Error Log")]=openpage("vstatus","auth/isdnlog.php");
    }
    $vstatus[_("PRI Error Log")]=openpage("vstatus","auth/zaplog.php");
  }
  $vstatus[_("SIP Extensions")]="javascript:openexten(\'SIP\')";
  $vstatus[_("Analogue Exten.")]="javascript:openexten(\'Dahdi\')";
//  $vstatus[_("IAX Extensions")]="javascript:openexten(\'IAX\')";

  $vstatus[_("Pin Code List")]=openpage("vstatus","cdr/pinlist.php");
  $vstatus[_("Weak Passwords")]=openpage("vstatus","cdr/cracklist.php");
  $vstatus[_("Monthly Limit Topup")]=openpage("vstatus","cdr/pursetopup.php");
  if ($authtype > 0) {
    $vstatus[_("Active Channels")]=openpage("vstatus","cdr/getchan.php");
    $vstatus[_("Unauthorised Exten.")]=openpage("vstatus","cdr/authexten.php");
  }
  $vstatus[_("Unused/Unreg Exten.")]=openpage("vstatus","cdr/badexten.php");
  if ($authtype > 0) {
    $vstatus[_("Codec Translation")]=openpage("vstatus","cdr/gettrans.php");
    if (is_dir("/dev/dahdi")) {
      $vstatus[_("DAHDI Configuration")]=openpage("vstatus","cdr/getdahdiconf.php");
    } else {
      $vstatus[_("Zaptel Configuration")]=openpage("vstatus","cdr/getzapconf.php");
    }
  }
}

if ($authtype > 0) {
  $vstatus[_("PBX Info/License")]=openpage("vstatus","cdr/getpbxinf.php");
  $vstatus[_("Loaded Modules")]=openpage("vstatus","cdr/getmods.php");
  $status[_("PPP Connections")]=openpage("status","auth/pppdlog.php");
  $status[_("DHCP (Last 100)")]=openpage("status","auth/dhcplog.php");
  $status[_("MAC Scan (ARP List)")]=openpage("status","auth/macscan.php");
  $status[_("Rate Lim. (Last 100)")]=openpage("status","auth/ratelimlog.php");
}
$status[_("Voip Information")]="include:vstatus";
if ($authtype > 0) {
  $status[_("Radius Information")]="include:radius";
}
if (($authtype > 0) || ($voipauth > 0 )){
  $status[_("System Usage")]=openpage("status","rrdgraph/status.php");
  $status[_("MRTG Graphs")]=openpage("status","mrtg/index.html");
}

if ($authtype > 0) {
  $status[_("Mail Logs")]=openpage("status","logs/mnav.php");
  $status[_("Firewall Logs")]=openpage("status","logs/unav.php");
  $status[_("Web Server Status")]="javascript:openstatus(\'server-status\')";
  $status[_("Web Load Balance")]="javascript:openstatus(\'server-balance\')";
  $status[_("Web Server Config")]="javascript:openstatus(\'server-info\')";
  $status[_("PHP Information")]=openpage("status","phpinfo.php");
  $status[_("Cache Info")]="javascript:openstatus(\'cgi-bin/admin/cachemgr.cgi?host=localhost\')";
  $status[_("Cache Usage")]="javascript:openstatus(\'cgi-bin/admin/cachemgr.cgi?host=localhost&operation=client_list\')";
  $status[_("Active Requests")]="javascript:openstatus(\'cgi-bin/admin/cachemgr.cgi?host=localhost&operation=active_requests\')";
}

for($mcnt=0;$mcnt < count($menu);$mcnt++) {
  $subout[$menu[$mcnt]]="";
  if (is_array($$menu[$mcnt])) {
    while(list($item,$action)=each($$menu[$mcnt])) {
      if (substr($action,0,7) == "include") {
         $include=substr($action,8);
        if ($include == "login") {
          $include2="apps";
        } else {
	  $include2=$include;
        }
        $subout[$menu[$mcnt]].="['" . $item . "', 'javascript:openpage(\'\',\'" . $include2 . "\')', null,\n\t" . $subout[$include] . "\n\t],\n\t";
      } else {
        $subout[$menu[$mcnt]].="\t['" . $item . "', '" . $action . "'],\n\t"; 
      }
    }
  }
  $subout[$menu[$mcnt]]=substr($subout[$menu[$mcnt]],0,-3);
}
print "var menu_items_list = new Array();\n";
print "var menu_list=new Array();\n";
print "var activemenu=''\n";
print "var activeuser=''\n\n";
for($mcnt=0;$mcnt < count($menu);$mcnt++) {
  print "menu_items_list['" . $menu[$mcnt] . "_menu']=[\n" . $subout[$menu[$mcnt]] . "];\n\n";
  if ($menu[$mcnt] == "main") {
    print "menu_list['main_menu']=new menu (menu_items_list['main_menu'],menu_horiz);\n\n";
  }
}
%>
