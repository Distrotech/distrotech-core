<%

include_once "/var/spool/apache/htdocs/cdr/auth.inc";

function authenticate() {
  header( "WWW-Authenticate: Basic realm=\"VOIP User Login\"");
  header( "HTTP/1.0 401 Unauthorized");
  print "<link rel=stylesheet type=text/css href=/style.php>";
  print "<CENTER><H1>You Need To Be A Authorised VOIP User";
  exit;
}

if ((isset($exten)) && ($PHP_AUTH_USER != $exten)) {
  $PHP_AUTH_USER=$exten;
}

$curset=pg_query("SELECT DISTINCT name FROM users WHERE name='" . $PHP_AUTH_USER . "' AND (password ='" . $PHP_AUTH_PW . "' OR secret='" . $PHP_AUTH_PW . "')");
	

if ((!isset($PHP_AUTH_USER)) || (pg_num_rows($curset) == "0") || (!isset($PHP_AUTH_PW))) {
  authenticate();
} else {
  $profile=pg_query("SELECT DISTINCT users.context,position,fullname,console.context,crmpop,dgroup
  FROM users 
    LEFT OUTER JOIN console USING (mailbox)
    LEFT OUTER JOIN features ON (exten=users.name)
    WHERE name='" . $PHP_AUTH_USER . "'");
  $msqldat=pg_fetch_array($profile,0,PGSQL_NUM);
  $exten=$PHP_AUTH_USER;
}
%>
