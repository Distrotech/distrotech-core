<?php

include_once "/var/spool/apache/htdocs/cdr/auth.inc";

function authenticate() {
  header( "WWW-Authenticate: Basic realm=\"VOIP User Login\"");
  header( "HTTP/1.0 401 Unauthorized");
  print "<link rel=stylesheet type=text/css href=/style.php>";
  print "<CENTER><H1>You Need To Be A Authorised VOIP User";
  exit;
}

if ((isset($exten)) && ($_SERVER['PHP_AUTH_USER'] != $exten)) {
  $_SERVER['PHP_AUTH_USER']=$exten;
}

$authq="SELECT DISTINCT name FROM users LEFT OUTER JOIN voicemail ON (name=voicemail.mailbox) WHERE name='" . $_SERVER['PHP_AUTH_USER'] . "' AND (voicemail.password ='" . $_SERVER['PHP_AUTH_PW'] . "' OR secret='" . $_SERVER['PHP_AUTH_PW'] . "')";
$curset=pg_query($db,$authq);

if ((!isset($_SERVER['PHP_AUTH_USER'])) || (pg_num_rows($curset) == "0") || (!isset($_SERVER['PHP_AUTH_PW']))) {
  authenticate();
} else {
  $profile=pg_query("SELECT DISTINCT users.context,position,fullname,console.context,crmpop,dgroup
  FROM users 
    LEFT OUTER JOIN console USING (mailbox)
    LEFT OUTER JOIN features ON (exten=users.name)
    WHERE name='" . $_SERVER['PHP_AUTH_USER'] . "'");
  $msqldat=pg_fetch_array($profile,0,PGSQL_NUM);
  $exten=$_SERVER['PHP_AUTH_USER'];
}
?>
