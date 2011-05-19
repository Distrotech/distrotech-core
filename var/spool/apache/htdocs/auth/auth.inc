<%
if (isset($_SERVER['SERVER_NAME'])) {
  include_once "/var/spool/apache/htdocs/session.inc";
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
}

$sestime="600";

if (($_POST['vboxlogoff'] == session_id()) && ($_POST['vboxlogoff'] != "")) {
  if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), $_COOKIE[session_name()], time() - 3600, "/");
  }
  session_regenerate_id(TRUE);
  $_SESSION=array();
  $_SESSION['auth']=true;
  session_write_close();
  header("Location: /");
  exit;
} else if (($_SESSION['auth']) || (!isset($_COOKIE[session_name()]))) {
  $_SESSION['auth']=false;
  if (isset($_SERVER['SERVER_NAME'])) {
    setcookie(session_name(), session_id(), time() + $sestime, "/");
    header('WWW-Authenticate: Basic Realm="User Login"');
    header('HTTP/1.0 401 Unauthorized');
    exit;
  }
} else {
  if ((!isset($_SESSION['userid'])) && (isset($_SERVER['PHP_AUTH_USER']))) {
    $_SESSION['userid']=$_SERVER['PHP_AUTH_USER'];
  }
  if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), $_COOKIE[session_name()], time() + $sestime, "/");
  }
}
include_once "/var/spool/apache/htdocs/ldap/auth.inc";
%>
