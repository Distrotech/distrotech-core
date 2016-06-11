<?php
include_once "../session.inc";
$sessid=session_id();
if ($sessid == "") {
  ob_start('ob_gzhandler');
  session_name("server_admin");
  session_set_cookie_params(28800);
  session_start();
  if (!isset($_SESSION['auth'])) {
    $_SESSION['auth']=false;
  }
}

if ((isset($_POST['utype'])) && (!isset($_SESSION['utype']))) {
  $_SESSION['utype']=$_POST['utype'];
} else {
  $_SESSION['utype']="system";
}

include "/var/spool/apache/htdocs/ldap/auth.inc";

$_POST['search']=rtrim($_POST['search']);
if (($_POST['search'] != "") && ($_POST['type'] != "")) {
  if ($_POST['type'] == "in") {
    $_POST['search']="*" . $_POST['search'] . "*";
  } elseif ($_POST['type'] == "end") {
    $_POST['search']="*" . $_POST['search'];
  } elseif ($_POST['type'] == "start") {
    $_POST['search']=$search . "*";
  }
} else if ($_POST['search'] == "") {
  $_POST['search']="*";
} else if ($_POST['type'] == "") {
  $_POST['search']=$_POST['search'] . "*";
}

$search="(" . $_POST['what'] . "=" . $_POST['search'] . ")";

if ($_POST['baseou'] == "pdc") {
  $search="(&(objectclass=officePerson)(uidNumber=*)" . $search . ")";
} else if ($_POST['baseou'] == "system") {
  $search="(&(objectclass=posixaccount)(uidNumber=*)" . $search . ")";
} else if ($_POST['baseou'] == "snom") {
  $search="(&(objectclass=snomcontact)" . $search . ")";
} else if ($_POST['baseou'] == "trust") {
  $search="(&(objectclass=posixaccount)" . $search . ")";
}

if ($_POST['what'] == "cn") {
  $alt="uid";
} else {
  $alt="cn";
}

$sarr=array("uid","cn",$_POST['what']);
$sr=ldap_list($ds, $abdn, $search,$sarr);
ldap_sort($ds,$sr,$_POST['what']);
$sinfo=ldap_get_entries($ds,$sr);


header('Content-type: text/xml');
print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<users>\n";

//print "  <user id=\"testing\">" . $_POST['search'] . " - " . $_POST['type'] . " - " . $abdn . " - " . $search . "</user>\n";

for($rcnt=0;$rcnt<$sinfo['count'];$rcnt++) {
/*
  if ($sinfo[$rcnt]['cn'][0] == "") {
    if ($sinfo[$rcnt][$_POST['what']][0] != "") {
      $sinfo[$rcnt][$_POST['what']][0]=$sinfo[$rcnt][$_POST['what']][0];
    } else if ($sinfo[$rcnt]['uid'][0] != "") {
      $sinfo[$rcnt][$_POST['what']][0]=$sinfo[$rcnt]['uid'][0];
    } else {
      $sinfo[$rcnt][$_POST['what']][0]="-";
    }
  }
*/
  for ($elid=0;$elid < $sinfo[$rcnt][$_POST['what']]['count'];$elid++) {
    if ($sinfo[$rcnt][$_POST['what']][$elid] != "") {
      print "  <user id=\"" . htmlentities($sinfo[$rcnt][$_POST['what']][$elid], ENT_QUOTES );
      print "\">";
      print htmlentities($sinfo[$rcnt][$_POST['what']][$elid] . " (" . $sinfo[$rcnt][$alt][0] . ")", ENT_QUOTES);
      print "</user>\n";
    }
  }
}
print "</users>\n";
?>
