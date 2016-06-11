<?php
include_once "/var/spool/apache/htdocs/session.inc";
include_once "/var/spool/apache/htdocs/ldap/auth.inc";

$bcolor[0]=" CLASS=list-color2";
$bcolor[1]=" CLASS=list-color1";

$sessid=session_id();
if ($sessid == "") {
  ob_start('ob_gzhandler');
  session_name("callcentre");
  session_set_cookie_params(28800);
  session_start();
  $sessid=session_id();
}

if (($_POST['vboxlogoff'] == $sessid) && ($_POST['vboxlogoff'] != "")) {
  session_regenerate_id(TRUE);
  unset($_SESSION['uid']);
  $_SESSION=array();
  $_SESSION['auth']=true;
  $_SESSION['reload']=false;
  session_write_close();
  header("Location: " . $_SERVER['SCRIPT_URL']);
  exit;
} else if ((!isset($_SESSION['auth'])) || ($_SESSION['auth']) || ($r === false)) {
  $_SESSION['auth']=false;
  header( "WWW-Authenticate: Basic realm=\"User Login\"");
  header( "HTTP/1.0 401 Unauthorized");
  print "<link rel=stylesheet type=text/css href=/netsentry.php>";
  print "<CENTER><H1>You Need To Be A Authorised User";
  exit;
} else if (((!$_SESSION['auth']) && (!isset($_SESSION['uid']))) || ($_SESSION['reload'])) {
  $_SESSION['auth']=false;
  $_SESSION['seslimit']=1800;
  $_SESSION['reload']=false;
  $_SESSION['uid']=$_SERVER['PHP_AUTH_USER'];
  $_SESSION['showmenu']="admin";
  $sr=ldap_search($ds,"cn=Call Centre Admin,ou=Admin","(&(objectclass=groupOfNames)(member=" . $ldn . "))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($_SERVER['PHP_AUTH_USER'] == "admin")) {
    $_SESSION['auser']=2;
  } else {
    $_SESSION['auser']=1;
    $_SESSION['limitadmin']=" WHERE camp_admin.userid IS NOT NULL";
  }
}

if (isset($_COOKIE[session_name()])) {
  setcookie(session_name(), $_COOKIE[session_name()], time() + $_SESSION['seslimit'], "/");
}

/*
$memlr=ldap_search($ds,"cn=Call Centre Admin,ou=Admin","(&(objectclass=groupOfNames)(member=" . $ldn . "))",array('member'));
$ccadmin=ldap_get_entries($ds, $memlr);
unset($ccadmin[0]['member']['count']); 
while(list($memcnt,$amember) = each($ccadmin[0]['member'])) {
  $memget=ldap_search($ds,$amember,"(&(|(objectClass=officeperson)(objectclass=shadowAccount))(uid=*))",array("uid","cn","dn"));
  $usinfo = ldap_get_entries($ds, $memget);
  for($dncnt=0;$dncnt<$usinfo['count'];$dncnt++) {
    if (strtolower($usinfo[$dncnt]['dn']) == strtolower($amember)) {
      $validmem[$amember]=$usinfo[$dncnt]['uid'][0];
      if ($usinfo[$dncnt]['cn'][0] != "") {
        $validmem[$amember].=" (" . $usinfo[$dncnt]['cn'][0] . ")";
      }
    }  
  }    
}    
$_SESSION['valid']=$validmem;
*/
?>
