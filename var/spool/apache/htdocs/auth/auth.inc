<%
include_once "/var/spool/apache/htdocs/session.inc";

if (!isset($_SERVER['SERVER_NAME'])) {
  include_once "/var/spool/apache/htdocs/ldap/auth.inc";
  return;
};

$sessid=session_id();

if ($sessid == "") {
  ob_start('ob_gzhandler');
  session_name("server_admin");
  session_set_cookie_params(28800);
  session_start();
}

if (!isset($_COOKIE[session_name()])) {
  if (isset($_SERVER['HTTP_REFERER'])) {
    $_SESSION['auth']=true;
  } else {
    $_SESSION['auth']=false;
  }
} else if (!isset($_SESSION['auth'])) {
  $_SESSION['auth']=false;
}

$sestime="600";

if ((!$_SESSION['auth']) && (isset($_SERVER['PHP_AUTH_USER'])) && (!isset($_SESSION['userid']))) {
  $_SESSION['userid']=$_SERVER['PHP_AUTH_USER'];
}

if (($_POST['vboxlogoff'] == session_id()) && ($_POST['vboxlogoff'] != "")) {
  setcookie(session_name(), $_COOKIE[session_name()], time() - 3600, "/");
  session_regenerate_id(TRUE);
  $_SESSION=array();
  $_SESSION['auth']=true;
  session_write_close();
  header("Location: /");
  exit;
} else if (($_SESSION['auth']) || (!isset($_SESSION['userid']))) {
  session_regenerate_id(TRUE);
  $_SESSION=array();
  $_SESSION['auth']=false;
  session_write_close();
  header('WWW-Authenticate: Basic Realm="User Login"');
  header('HTTP/1.0 401 Unauthorized');
  exit;
}

include_once "/var/spool/apache/htdocs/ldap/auth.inc";
%>
