<%
  include_once "/var/spool/apache/htdocs/auth/auth.inc";
  include_once "/var/spool/apache/htdocs/ldap/auth.inc";
  include_once "/var/spool/apache/htdocs/cdr/auth.inc";

  if (count($_GET) > 0) {
    unset($_GET["disppage"]);
    unset($_GET["disppage"]);
    unset($_GET["pbxupdate"]);
  }

  $clogacl="(bgrp.value != '' AND bgrp.value IS NOT NULL AND (";
  if ($PHP_AUTH_USER != "admin") {
    $grpsq=pg_query($db,"SELECT astdb.family FROM astdb  WHERE astdb.value='GAUTH' AND key='" . $ldn . "'");
    $numrow=pg_num_rows($grpsq);
    for($i=0;$i < $numrow;$i++){
      list($grpacc)=pg_fetch_array($grpsq,$i);
      $clogacl.=" bgrp.value = '" . $grpacc . "'";
      if ($i < ($numrow-1)) {
       $clogacl.= " OR";
      }
    }
  }
  $clogacl.="))";

  $allowgrp['cdr/routingrep.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/rrepstat.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/mendrep.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/allrep.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/dextenr.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/extenrep.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/list.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/allext.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/aclist.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/allcalls.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/queuerep.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/queueslrep.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/qagntrep.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/acdstat.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/qstatus.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/qslstat.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/qagntstat.php']="(cn=Call Logging)(cn=TMS Access)(cn=Voip Admin)";
  $allowgrp['cdr/calllog.php']="(cn=Call Logging)(cn=Voip Admin)";
  $allowgrp['cdr/logged.php']="(cn=Call Logging)(cn=Voip Admin)";
  $allowgrp['cdr/calllist.php']="(cn=Call Logging)(cn=Voip Admin)";
  $allowgrp['cdr/gauth.php']="(cn=Voip Admin)";
  $allowgrp['cdr/csprovider.php']="(cn=Voip Admin)";
  $allowgrp['cdr/h323peer.php']="(cn=Voip Admin)";
  $allowgrp['cdr/h323neigh.php']="(cn=Voip Admin)";
  $allowgrp['cdr/snomload.php']="(cn=Admin Access)(cn=Voip Admin)";

  //must check both ...
  $allowgrp['cdr/getrep.php']="(cn=Call Logging)(cn=TMS Access)";

  if ($_SESSION['disppage'] != "cdr/gauth.php") {
    $allowgrp[$_SESSION['disppage']]="(|(cn=Admin Access)" . $allowgrp[$_SESSION['disppage']] . "(cn=Voip Admin))";
  }

  $grpquery="(&(objectclass=groupofnames)(member=" . $ldn . ")" . $allowgrp[$_SESSION['disppage']] . ")";
  $sr=ldap_search($ds,"ou=Admin",$grpquery,array("cn"));

  $SUPER_USER=0;
  $TMS_USER=0;
  $ugrpcnt=ldap_count_entries($ds,$sr);
  if (($ugrpcnt >= 1) || ($PHP_AUTH_USER == "admin")) {
    $snauth=ldap_get_entries($ds,$sr);
    for($grcnt=0;$grcnt<$ugrpcnt;$grcnt++) {
      if ($snauth[$grcnt]["cn"][0] == "Admin Access") {
        $SUPER_USER=1;
        $TMS_USER=1;
      } else if ($snauth[$grcnt]["cn"][0] == "TMS Access") {
        $TMS_USER=1;
      } else if ($snauth[$grcnt]["cn"][0] == "Voip Admin") {
        $TMS_USER=1;
      }
    }
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }
%>
