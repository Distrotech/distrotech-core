<%
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

include "/var/spool/apache/htdocs/ldap/auth.inc";

if ($_POST['search'] != "") {
  $search="(cn=*" . $_POST['search'] . "*)";
} else {
  $search="(cn=*)";
}

$search="(&(objectclass=officePerson)(uidNumber=*)" . $search . ")";

$alt="uid";

$sarr=array("uid","cn");
$sr=ldap_search($ds, $LDAP_BDN, $search,$sarr);
ldap_sort($ds,$sr,"cn");
$sinfo=ldap_get_entries($ds,$sr);


header('Content-type: text/xml');
print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<users>\n";

for($rcnt=0;$rcnt<$sinfo['count'];$rcnt++) {
  for ($elid=0;$elid < $sinfo[$rcnt]['cn']['count'];$elid++) {
    if ($sinfo[$rcnt]['cn'][$elid] != "") {
      print "  <user id=\"" . htmlentities($sinfo[$rcnt]['dn'], ENT_QUOTES );
      print "\">";
      print htmlentities($sinfo[$rcnt]['cn'][$elid] . " (" . $sinfo[$rcnt][$alt][0] . ")", ENT_QUOTES);
      print "</user>\n";
    }
  }
}
print "</users>\n";
%>
