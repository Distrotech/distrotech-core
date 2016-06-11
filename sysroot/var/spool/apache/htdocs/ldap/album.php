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

include_once "/var/spool/apache/htdocs/session.inc";
$sessid=session_id();
if ($sessid == "") {
  ob_start('ob_gzhandler');
  session_name("photo_album");
  session_set_cookie_params(28800);
  session_start();
}

if (preg_match("/^/photo/([a-zA-Z0-9\.-]+)_album.html/",$_SERVER['SCRIPT_URL'],$dispdata)) {
  $_SESSION['euser']=$dispdata[1];
  $algetvar=array('first','pstart','style','imlim','rcnt','phend','scount');
  for($gvcnt=0;$gvcnt<count($algetvar);$gvcnt++) {
    if (isset($_GET[$algetvar[$gvcnt]])) {
      ${$algetvar[$gvcnt]}=$_GET[$algetvar[$gvcnt]];
      $_SESSION[$algetvar[$gvcnt]]=$_GET[$algetvar[$gvcnt]];
    }
  }
  if (isset($_GET['style'])) {
    $_SESSION['style']=$_GET['style'];
  }
} else if (isset($_GET['euser'])) {
  $algetvar=array('euser','first','pstart','style','imlim','rcnt','phend','scount');
  for($gvcnt=0;$gvcnt<count($algetvar);$gvcnt++) {
    if (isset($_GET[$algetvar[$gvcnt]])) {
      $_SESSION[$algetvar[$gvcnt]]=$_GET[$algetvar[$gvcnt]];
      ${$algetvar[$gvcnt]}=$_GET[$algetvar[$gvcnt]];
    }
  }  
  if (isset($_GET['style'])) {
    $_SESSION['style']=$_GET['style'];
  }
}

if (!isset($_SESSION['sname'])) {
  $_SESSION['sname']="?sesname=" . urlencode(session_name());
}

if (!isset($_SESSION['disppage'])) {
  $_SESSION['disppage']="auth/photo.php";
}

$_GET=array();

if ($_SESSION['first'] == "") {
  $_SESSION['first']=0;
}
if ($_SESSION['pstart'] == "") {
  $_SESSION['pstart']=$_SESSION['first'];
}
if ($_SESSION['imlim'] == "") {
  $_SESSION['imlim']=300;
}
if ($_SESSION['rcnt'] == "") {
  $_SESSION['rcnt']=3;
}
include_once "ldapbind.inc";
$sr=ldap_search($ds,"","(&(objectClass=officeperson)(uid=" . $_SESSION['euser']  . "))", array("jpegPhoto","cn"));
$ei=ldap_first_entry($ds, $sr);
$cinf = ldap_get_values_len($ds, $ei,"jpegPhoto");
$uinf = ldap_get_values($ds, $ei,"cn");
if ($_SESSION['phend'] > $cinf["count"]) {
  $_SESSION['phend']=$cinf["count"];
}
$bcolor[1]=" class=list-color1"; 
$bcolor[0]=" class=list-color2"; 
if ($_SESSION['last'] == "") {
  $_SESSION['last']=$cinf["count"]-1;
}
if ($_SESSION['scount'] == "") {
  $_SESSION['scount']=9;
}
if ($_SESSION['last'] >= $cinf["count"]) {
  $_SESSION['last']=$cinf["count"]-1;
}
if ($_SESSION['phend'] == "") {
  if (($_SESSION['last']-$_SESSION['pstart']) >= $_SESSION['scount']) {
    $_SESSION['phend']=$_SESSION['pstart']+$_SESSION['scount'];
  } else {
    $_SESSION['phend']=$_SESSION['last']+1;
  }
}
?>
<html>
<script language="JavaScript" src="/hints.js" type="text/javascript"></script>
<script language="JavaScript" src="/hints_cfg.php<?php print $_SESSION['sname'];?>"></script>
<head>
<title><?php print _("Photo Album Of User") . " " . $uinf[0];?></title>
<base target="_self">
<link rel=stylesheet type=text/css href="/style.php<?php print $_SESSION['sname'];?>">
<TABLE cellspacing=0 cellpadding=10 WIDTH=100%>
  <TR class=list-color2>
    <TH COLSPAN=<?php print $_SESSION['rcnt'];?>><?php print _("Photo Album Of User") . " " . $uinf[0] . " " . _("Images") . " " . ($_SESSION['pstart']+1) . " " . _("Through") . " " . $_SESSION['phend'] . " " . _("Of") . " " . $cinf["count"]?></TH>
  </TR>
  <TR class=list-color1>
<?php

$row=0;
for ($pcnt=$_SESSION['pstart'];$pcnt < $_SESSION['phend'];$pcnt++){
  print "    <TD WIDTH=" . ($_SESSION['imlim']*1.05) . " ALIGN=CENTER VALIGN=MIDDLE>\n";
  print "      <A HREF=/photo/" . $_SESSION['euser'] . "_" . $pcnt . ".jpg TARGET=_BLANK>\n";
  print "        <IMG SRC=/photo/" . $_SESSION['euser'] ."_" . $pcnt . ".jpg&imlim=" . $_SESSION['imlim'] . " onmouseover=\"myHint.show('" . $pcnt . "')\" onmouseout=\"myHint.hide()\" BORDER=0>\n";
  print "      </A>\n";
  print "    </TD>\n";

  if (((($pcnt - $_SESSION['pstart']) % $_SESSION['rcnt']) == ($_SESSION['rcnt'] - 1)) && ($pcnt < ($_SESSION['phend']-1))){
    $rem=$row % 2;
    print "  </TR><TR" . $bcolor[$rem] . " >\n";
    $row++;
  }
}
if ((($pcnt - $_SESSION['pstart']) % $_SESSION['rcnt']) != 0) {
  for ($bcnt=(($pcnt - $_SESSION['pstart']) % $_SESSION['rcnt']);$bcnt < $_SESSION['rcnt'];$bcnt ++) {
    print "    <TD>\n";
    print "      &nbsp;\n";
    print "    </TD>\n";
  }
}
$rem=$row % 2;
?>
  </TR>
  <TR<?php print $bcolor[$rem];?> >
<?php
    if (($_SESSION['pstart']-$_SESSION['scount']) >= $_SESSION['first']) {
      $_SESSION['pstart']=$_SESSION['pstart']-$_SESSION['scount'];
?>
      <TD ALIGN=CENTER VALIGN=MIDDLE>
        <A HREF=/photo/<?php print $_SESSION['euser'];?>_album.html>Prev</A>
      </TD>
<?php
    } else {
      print "    <TD>&nbsp;</TD>\n";
    }
    print "    <TD>&nbsp;</TD>\n";
    if ($_SESSION['phend'] <= $_SESSION['last']) {
      $_SESSION['pstart']=$_SESSION['phend'];
?>
      <TD ALIGN=CENTER VALIGN=MIDDLE>
        <A HREF=/photo/<?php print $_SESSION['euser'];?>_album.html>Next</A>
      </TD>
<?php
    } else {
      for ($colsp=0;$colsp < $_SESSION['rcnt'] - 2;$colsp++) {
        print "    <TD>&nbsp;</TD>\n";
      }
    }
?>
  </TR>
</TABLE>
<?php
ldap_unbind($ds);
?>
