<?php
include "../cdr/auth.inc";
include "../cdr/autoadd.inc";
include "../ldap/ldapbind.inc";

header('Content-type: text/xml');

$mac=strtoupper($_GET['mac']);

$auth_ussr=@ldap_search($ds,"ou=snom","(&(objectClass=person)(cn=snom))");

$uadata=explode(";",$_SERVER['HTTP_USER_AGENT']);
$pdata=explode(" ",trim($uadata[1]));
$sipver=explode(".",trim($pdata[1]));

if (@ldap_count_entries($ds,$auth_ussr) <= 0 ) {
  $dn="cn=Snom,ou=Snom";
  $info["objectclass"][0]="person";
  $info["cn"]="snom";
  $info["sn"]="Snom Global Phone Book";
  $info["userpassword"]="snom";

  @ldap_add($ds,$dn,$info);

  if (@ldap_errno($ds) == "32") {
    $info2["objectclass"][0]="organizationalUnit";
    $info2["ou"]="snom";
    $dn2="ou=snom";
    @ldap_add($ds,$dn2,$info2);
    @ldap_add($ds,$dn,$info);
  }
  $auth_ussr=@ldap_search($ds,"ou=snom","(&(objectClass=person)(cn=snom))");
}

$auth_ures=@ldap_first_entry($ds,$auth_ussr);
$suser=@ldap_get_attributes($ds,$auth_ures);

$pwlen=8;
$getphoneq="SELECT name,secret,fullname,registrar,snomlock,nat,dtmfmode,vlan,cdnd,
                   (name=secret OR length(secret) != " . $pwlen . " OR secret='' OR secret IS NULL OR
                    secret !~ '[0-9]' OR secret !~ '[a-z]' OR secret !~ '[A-Z]'),
		   case when (encryption_taglen = '32') then encryption||',32bit' else encryption end,
                   transport,dispname
              FROM users 
                LEFT OUTER JOIN features ON (name=exten)
              WHERE snommac='" . $mac . "' LIMIT 1";
$getphone=pg_query($db,$getphoneq);

if (pg_num_rows($getphone) == 0) {
  if (createexten($mac,"SNOM","","","") > 0) {
    $getphone=pg_query($db,$getphoneq);
  }
}

list($exten,$pass,$name,$domain,$usermode,$nat,$dtmfmode,$vlantag,$dndsetting,$pwchange,$encrypt,$transport,$dispname)=@pg_fetch_array($getphone,0);

if ($pwchange == "t") {
  if (! isset($agi)) {
    require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
    $agi=new AGI_AsteriskManager();
    $agi->connect("127.0.0.1","admin","admin");
  }
  $agi->command("sip prune realtime peer " . $exten);
  $agi->command("sip prune realtime user " . $exten);
  $pass=randpwgen($pwlen);
  pg_query($db,"UPDATE users SET secret='" . $pass . "' WHERE name='" . $exten . "'");
  $agi->disconnect();
}

$getnetp=pg_query($db,"SELECT value FROM astdb WHERE family='Setup' AND key='SnomNet'");

list($netport)=pg_fetch_array($getnetp,0);

if ($exten == "") {
  $usermode="0";
}

if ($netport == "") {
  $netport="auto";
}

if ($domain == "" ) {
  $domain=$SERVER_NAME;
}

$uadata=explode(";",$_SERVER['HTTP_USER_AGENT']);
$curver=trim($uadata[3]," )");
$linver="snom" . $_GET['phone'] . " linux 3.38";

if ($sipver[0] < 8) {
  print "language&: English(UK)\n";
  print "web_language&: English\n";
  print "admin_mode_password&: 1234\n";
  print "update_policy&: auto_update\n";

  if (($SERVER_NAME != "") && ($_GET['phone'] != "") && (is_file("snom" . $_GET['phone'] . "-fw.php"))) {
    print "firmware_status&: http://" . $SERVER_NAME . "/snom/snom" . $_GET['phone'] . "-fw.php\n";
  };
  if (($vlantag != "") && ($vlantag > 1)) {
    print "vlan&: " . $vlantag . " 5\n";
  } else {
    print "vlan!: \n";
  }
} else {
  include "snomxml.php";
}
