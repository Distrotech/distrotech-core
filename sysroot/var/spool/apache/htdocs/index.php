<?php
/*
#    Copyright (C) 2002  <Gregory Hinton Nietsky>
#    Copyright (C) 2005  <ZA Telecomunications>
#
#    This program is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program; if not, write to the Free Software
#    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
 * Start the session if not started
 */
include_once "session.inc";
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

/*
 * Set the session name to pass back to the scripts
 */
if (!isset($_SESSION['sname'])) {
  $_SESSION['sname']="?sesname=" . urlencode(session_name());
}

/*
 * Mimic Register Globals
 */
if (!ini_get('register_globals')) {
  $superglobals = array($_SERVER,$_ENV,$_FILES,$_COOKIE,$_GET,$_POST);
  if (isset($_SESSION)) {
    array_unshift($superglobals, $_SESSION);
  }
  foreach ($superglobals as $superglobal) {
    extract($superglobal, EXTR_SKIP);
  }
}

/*
 * Set Loadpage Post variables as session variables
 */
if ((isset($_POST['utype'])) && ($_POST['utype'] == "")) {
  $_POST['utype']=$_SESSION['utype'];
}
if ((isset($_POST['print'])) && ($_POST['print'] != "")) {
  $_SESSION['print']=$_POST['print'];
}

if (($_POST['nomenu'] == 1) && (isset($_POST['disppage']))) {
  $_SESSION['poppage']=$_POST['disppage'];
  unset($_POST['disppage']);
}

$mastvar=array('disppage','showmenu','classi','style','utype' );
for($mvcnt=0;$mvcnt < count($mastvar);$mvcnt++) {
  if ((isset($_GET[$mastvar[$mvcnt]])) && 
      ((!isset($_POST[$mastvar[$mvcnt]])) || ($_POST[$mastvar[$mvcnt]] == ""))) {
    $_POST[$mastvar[$mvcnt]]=$_GET[$mastvar[$mvcnt]];
    $_SESSION[$mastvar[$mvcnt]]=$_POST[$mastvar[$mvcnt]];
  } else if (isset($_POST[$mastvar[$mvcnt]])) {
    $_SESSION[$mastvar[$mvcnt]]=$_POST[$mastvar[$mvcnt]];
  } else if (isset($_SESSION[$mastvar[$mvcnt]])) {
    $_POST[$mastvar[$mvcnt]]=$_SESSION[$mastvar[$mvcnt]];
  }
}

/*
 * Return the page as a CSV page correctly
 */
if (($_SERVER['PHP_AUTH_USER'] != "") && ($class != "") && ($_SESSION['showmenu'] == "vcsvdown")) {
  header( "Location: /csv/" . $class . ".csv");
}

/*
 * Bypass auth for the following pages in the /cdr sub
 */
$cdrauthok["cdr/elist.php"]=true;
$cdrauthok["cdr/cclist.php"]=true;
/*
 * Only Mozilla Firefox is supported
 */
include "/var/spool/apache/htdocs/browser.php";
$br = new Browser;
if (($br->Platform == "Windows") && ($br->Name != "Safari") && ($br->Name != "Firefox")){?>
  <SCRIPT>
    alert("Please install Mozilla Firefox the admin site only works with Mozilla.\nYou are about to be redierected to the download page.");
  </SCRIPT><?php
  if (is_file("/var/spool/samba/share/firefox.exe")) {?>
    <meta http-equiv="Refresh" content="1;url=/share/firefox.exe"><?php
  } else {?>
    <meta http-equiv="Refresh" content="1;url=http://www.mozilla.com"><?php
  }
  exit;
}

/*
 * Set the server to the right port and SSL status
 */

if (file_exists("/etc/.networksentry-lite")) {
  $_SESSION['server']="http://" . $_SERVER['SERVER_NAME'] . "/";
} else if (($_SERVER['SERVER_PORT'] == "80") || ($_SERVER['SERVER_PORT'] == "")) {
  $_SESSION['server']="https://" . $_SERVER['SERVER_NAME'] . ":666/";
} else {
  $_SESSION['server']="/";
}

/*
 * Set the default page to main.php if it exists
 */

if (($_SESSION['disppage'] == "") && (is_file("main.php"))) {
  $_SESSION['disppage']="main.php";
}

/*
 * Include aditional Authentication requirements and set the loadpage action
 */

$laction="";

if (($_POST['nomenu'] == 1) && (isset($_SESSION['poppage']))) {
  $showpage=$_SESSION['poppage'];
} else {
  $showpage=$_SESSION['disppage'];
}

if (preg_match("/(^[a-zA-Z]+)\//",$showpage,$dispdata)) {
  if ((!isset($_SERVER['PHP_AUTH_USER'])) || (!isset($_SERVER['PHP_AUTH_PW']))) {
    exit;
  }
  if (($dispdata[1] != "cshop") && ($dispdata[1] != "ccadmin")) {
    if (($ambox == "") && ($newambox != "") && (isset($amboxup)) || ($showpage == "ldap/adduser.php")) {
      $getuid=true;
    }
    include "/var/spool/apache/htdocs/ldap/auth.inc";
    if ($dispdata[1] == "cdr") {
      include "/var/spool/apache/htdocs/cdr/uauth.inc";
      if ((!$cdrauthok[$showpage]) && ($ADMIN_USER == "pleb")) {
        $showpage="";
      }
    }
    $laction=$_SESSION['server'] . "auth/";
  }
} else if (!preg_match("/(^[a-zA-Z]+)/",$showpage,$dispdata)) {
  exit;
}

/*
 * This is a AJAX submit include and exit
 */
if ($_POST['ajax']) {
  unset($_POST['ajax']);
  if (($_SESSION['showmenu'] == "asetup") && ($_SESSION['classi'] != "")) {?>
    <SCRIPT>
    <?php include "/var/spool/apache/htdocs/auth/menu_items_user.php";?>
    </SCRIPT><?php
  } else if ($showpage == "mrtg/index.html") {?>
<SCRIPT>
  var alinks = outputdiv.getElementsByTagName('A');
  for (var i=0;i<alinks.length;i++) {
    alinks[i].href="javascript:AJAX.senddata('main-body','loadpage','"+alinks[i].href+"')";
  }
</SCRIPT><?php
  }
  if (is_file("/var/spool/apache/htdocs/" . $showpage)) {
    include "/var/spool/apache/htdocs/" . $showpage;
  }
  exit;
}

/*
 * This is not a CSV return print < 2 but could be a print page popup == 1
 */
if ($_POST['print'] !=2) {?>
  <html>
  <head>
  <TITLE>Server Administration Interface (<?php print $_SERVER['SERVER_NAME'];?>)</TITLE>
  <link rel="stylesheet" href="/style.php<?php print $_SESSION['sname'];?>">
      <script language="JavaScript" src="/ajax.js" type="text/javascript"></script>
      <script language="JavaScript" src="/autocomplete.js" type="text/javascript"></script>
      <script language="JavaScript" src="/formsubmit.js" type="text/javascript"></script>
      <script language="JavaScript" src="/java_popups.php<?php print $_SESSION['sname'];?>" type="text/javascript"></script><?php

  /*
   * This is not a print popup page and needs a menu/logo structure,forms and JScript to support it
   */
  if (($_POST['print'] != "1") && ($_POST['nomenu'] != "1")) {
//    print_r($_SERVER);
    if ((is_file(".." . $_SERVER['SCRIPT_URL'] . "menu_items.php")) || ($_SERVER['SCRIPT_FILENAME'] == "/var/spool/apache/htdocs/index.php")) {?>

      <script language="JavaScript" src="<?php print $_SERVER['SCRIPT_URL'];?>menu_items.php" type="text/javascript"></script><?php
    } else if ($_SERVER['PHP_AUTH_USER'] == "") {?>

      <script language="JavaScript" src="/menu_items.php" type="text/javascript"></script><?php
    } else {?>
      <script language="JavaScript" src="/auth/menu_items.php" type="text/javascript"></script><?php

    }?>

      <script language="JavaScript" src="/menu_tpl.js" type="text/javascript"></script>
      <script language="JavaScript" src="/menu.js" type="text/javascript"></script>
    </head>
    <body onload=mbresize() onresize=mbresize()>
    <SCRIPT language="JavaScript">
      self.resizeTo(screen.width,screen.height);
      self.moveTo(0,0);
    </SCRIPT>
      <DIV ID=side-bar CLASS=side-bar></DIV><?php

    /*
     * Load the side menu based on context
     */
    if (!isset($_SESSION['showmenu'])) {
      if (isset($_SERVER['PHP_AUTH_USER'])) {
        $_SESSION['showmenu']="apps";
      } else {
        $_SESSION['showmenu']="ssl";
      }
    }

    /*
     * Header Logo A Html Include Above the Menu Bar
     */

    if ((file_exists("/var/spool/apache/htdocs/" . $_SESSION['style'] . "/header.html")) && ($_SESSION['style'] != "")) {
      print "<DIV ID=header CLASS=head-logo>";
      include $_SESSION['style'] . "/header.html";
      print "</DIV>\n";
    } else if (file_exists("/var/spool/apache/htdocs/header.html")) {
      print "<DIV ID=header CLASS=head-logo>";
      include "header.html";
      print "</DIV>\n";
    }?>

      <DIV ID=blanket></DIV>
      <div id="popUpDiv">
        <div>
          <div id=popUpDivTitle border=0 height=20 width=100% align=RIGHT>
            <a href=javascript:popdown()><IMG SRC=/images/exit.png HEIGHT=20></A>
          </div>
          <div id=popUpDivContent width=100% height=100?></div>
        </div>
      </div>
      <DIV ID=menu-bar CLASS=menu-bar></DIV>
      <DIV ID=main-body CLASS=main-scroll>
<?php

    /*
     * Load the appropriate body
     */

    if (is_file("/var/spool/apache/htdocs/" . $showpage)) {
      include "/var/spool/apache/htdocs/" . $showpage;
    }?>

    </DIV>
    <FORM NAME=vboxlogout METHOD=POST>
      <INPUT TYPE=HIDDEN NAME=vboxlogoff VALUE="<?php print session_id();?>">
    </FORM>
      <script language="JavaScript" src="/hints.js" type="text/javascript"></script>
      <script language="JavaScript" src="/hints_cfg.php<?php print $_SESSION['sname'];?>" type="text/javascript"></script><?php

    /*
     * Allow a logo on the left sidebar or on bottom right not both
     */

    if ((is_file("/var/spool/apache/htdocs/" . $_SESSION['style'] . "/logo-left.gif")) || (is_file("/var/spool/apache/htdocs/" . $_SESSION['style'] . "/logo-left.png"))) {
      print "\n<DIV ID=logo-left CLASS=logo-left>";
      if (($_SESSION['style'] != "") && (is_file("/var/spool/apache/htdocs/" . $_SESSION['style'] . "/logo-left.gif"))) {
        print "<IMG SRC=/" . $_SESSION['style'] . "/logo-left.gif width=130>";
      }
      if (($_SESSION['style'] != "") && (is_file("/var/spool/apache/htdocs/" . $_SESSION['style'] . "/logo-left.png"))) {
        print "<IMG SRC=/" . $_SESSION['style'] . "/logo-left.png width=130>";
      }
      print "</DIV>";
      print "<DIV ID=logo CLASS=logo>&nbsp;\n</DIV>";
    } else {
      if (($_SESSION['style'] != "") && (is_file("/var/spool/apache/htdocs/" . $_SESSION['style'] . "/banner.png"))) {
        print "\n<DIV ID=logo CLASS=logo>\n  <IMG SRC=/" . $_SESSION['style'] . "/images/banner.png>\n</DIV>";
      } else if (($_SESSION['style'] != "") && (is_file("/var/spool/apache/htdocs/" . $_SESSION['style'] . "/banner.gif"))) {
        print "\n<DIV ID=logo CLASS=logo>\n  <IMG SRC=/" . $_SESSION['style'] . "/banner.gif>\n</DIV>";
      } else if (is_file("/var/spool/apache/htdocs/images/banner.png")) {
        print "\n<DIV ID=logo CLASS=logo>\n  <IMG SRC=/images/banner.png>\n</DIV>";
      } else if (is_file("/var/spool/apache/htdocs/images/digium-the-asterisk-company.gif")) {
        print "\n<DIV ID=logo CLASS=logo>\n  <A HREF=http://www.asterisk.org TARGET=_blank><IMG SRC=/images/digium-the-asterisk-company.gif BORDER=0></A>\n</DIV>";
      } else {
        print "\n<DIV ID=logo CLASS=logo>\n</DIV>";
      }
    }
  } else {
    /*
     * This is a print form popup or a no menu page we need to set it up
     */
    print "</head>\n<body class=popup>\n<DIV ID=main-body CLASS=popup>\n";
?>
      <DIV ID=blanket></DIV>
      <div id="popUpDiv">
        <div>
          <div id=popUpDivTitle border=0 height=20 width=100% align=RIGHT>
            <a href=javascript:popdown()><IMG SRC=/images/exit.png HEIGHT=20></A>
          </div>
          <div id=popUpDivContent width=100% height=100?></div>
        </div>
      </div>
<?php
    include "/var/spool/apache/htdocs/" . $showpage;?>
    </DIV><?php
    if ($_POST['print'] == "1") {?>

      <SCRIPT>
        window.print();
      </SCRIPT><?php
    } else {?>
      <script language="JavaScript" src="/hints.js" type="text/javascript"></script>
      <script language="JavaScript" src="/hints_cfg.php<?php print $_SESSION['sname'];?>" type="text/javascript"></script><?php
    }
  }?>

  </body>
  </html><?php
} else {
  /*
   * This CSV Page Return CSV
   */
  header("Content-type: application/ms-excel");
  include "/var/spool/apache/htdocs/" . $showpage;
}
if ($_POST['print'] != 2) {?>
  <FORM METHOD=POST NAME=loadpage<?php if ($laction != "") {print " ACTION=" . $laction;}?>>
    <INPUT TYPE=HIDDEN NAME=utype>
    <INPUT TYPE=HIDDEN NAME=showmenu>
    <INPUT TYPE=HIDDEN NAME=classi>
    <INPUT TYPE=HIDDEN NAME=disppage>
    <INPUT TYPE=HIDDEN NAME=style>
    <INPUT TYPE=HIDDEN NAME=mmap>
    <INPUT TYPE=HIDDEN NAME=print>
    <INPUT TYPE=HIDDEN NAME=nomenu>
  </FORM>
<?php
}
ob_end_flush();
?>
