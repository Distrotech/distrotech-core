<?php
include "../session.inc";

session_name("hotspot_auth");

if ((isset($_COOKIE[session_name()])) && (isset($_SESSION['save_login']))) {
  setcookie(session_name(), $_COOKIE[session_name()], time() + 864000, "/");
  session_set_cookie_params(864000);
}

session_start();

if (($_SESSION['uamip'] == "") && ($_GET['uamip'] != "")) {
  $_SESSION['uamip']=$_GET['uamip'];
}

if (($_SESSION['uamport'] == "") && ($_GET['uamport'] != "")) {
  $_SESSION['uamport']=$_GET['uamport'];
}

if ($_SESSION['uam_url'] == "") {
  if ($_GET['uam_url'] != "") {
    $_SESSION['uam_url']=$_GET['uam_url'];
  } else if (($_SESSION['uamip'] != "") && ($_SESSION['uamport'] != "")) {
    $_SESSION['uam_url']="http://" . $_SESSION['uamip'] . ":" . $_SESSION['uamport'];
  }
}

if ($_GET['save_login'] == "on") {
  $_SESSION['username']=$_GET['uid'];
  $_SESSION['password']=$_GET['pwd'];
  $_SESSION['save_login']=true;
} elseif (isset($_GET['login'])) {
  $_SESSION['username']="";
  $_SESSION['password']="";
  $_SESSION['save_login']=false;
}
?>
