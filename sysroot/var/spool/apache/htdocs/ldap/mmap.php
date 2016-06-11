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
include "auth.inc";
?>

<html>

<head>

<base target="_self">
<link rel=stylesheet type=text/css href=/style.php>

</head>
<script language="JavaScript">
  NavOver=(((navigator.appName == "Netscape") &&
    (parseInt(navigator.appVersion) >= 3 )) ||
    ((navigator.appName == "Microsoft Internet Explorer") &&
    (parseInt(navigator.appVersion) >= 4 )));

  function IMGLoad(img) {
    var a=new Image();
    a.src=img;
    return a;
  }
  if(NavOver) {
    mclass1=IMGLoad("/images/mailmaps_a.gif");
    mclass2=IMGLoad("/images/mailmaps.gif");
    mconf1=IMGLoad("/images/mailconfig_a.gif");
    mconf2=IMGLoad("/images/mailconfig.gif");
  }
</script>
<body>
<table border="0" width="170" cellspacing="0" cellpadding="0">
  <tr>
    <td width="190">
      <p align="center"><img border="0" src="../images/Sentry.gif" width="129" height="181"></td>
  </tr><TR><TD ALIGN=MIDDLE>
<FORM METHOD=POST>
<?php
$abdn="ou=Email";

$ccname["R"]="Relay Domains";
$ccname["LDAPRoute"]="Accepted Domains";

if ($_SESSION['classi'] == "") {
  $sobj="objectClass=sendmailMTAClass";
} else {
  $sobj="(&(objectClass=sendmailMTAClass)(sendmailMTAClassName=" . $_SESSION['classi'] . "))";
}

if ($_SESSION['classi'] != "") {
  if ($classedit == "Delete") {
    $addent=array();
    $addent["sendmailmtaclassvalue"]=$_POST[$_SESSION['classi']];
    ldap_mod_del($ds,"sendmailMTAClassName=" . $_SESSION['classi'] . ",ou=Email",$addent);
  } else if (($classedit == "Add") && ($add != "")) {
    $addent=array();
    $addent["sendmailmtaclassvalue"]=$add;
    ldap_mod_add($ds,"sendmailMTAClassName=" . $_SESSION['classi'] . ",ou=Email",$addent);
  }

  $sr=ldap_search($ds,"$abdn",$sobj);
  $info = ldap_get_entries($ds, $sr);

  $dn=$info[0]["dn"];  

  $cname=$info[0]["sendmailmtaclassname"][0];
  print $ccname[$cname] . "<P><SELECT NAME=\"" . $cname . "\">\n";

  $allent=$info[0]["sendmailmtaclassvalue"];
  unset($allent["count"]);

  sort($allent);
  reset($allent);

  for($dcnt=0;$dcnt < count($allent);$dcnt++) {
    print "<OPTION VALUE=\"" . $allent[$dcnt] . "\">" . $allent[$dcnt] . "\n";
  }
  print "</SELECT><P>\n";

  print "<INPUT TYPE=TEXT NAME=add><P>";
  print "<INPUT TYPE=SUBMIT NAME=classedit VALUE=\"Add\">\n";
  print "<INPUT TYPE=SUBMIT NAME=classedit VALUE=\"Delete\"><P>\n";
} else {
  $sr=ldap_search($ds,"$abdn",$sobj);
  $info = ldap_get_entries($ds, $sr);

  for ($i=0; $i<$info["count"]; $i++) {
    $srsort[$i]=$info[$i]["sendmailMTAClassName"][0];
  }
  asort($srsort);
  reset ($srsort);

  print "Select Email Class To Edit<P><SELECT NAME=classi>\n";
  while (list($i,$val) = each($srsort)) {
    $dn=$info[$i]["dn"];
    $cname=$info[$i]["sendmailmtaclassname"][0];
    print "<OPTION VALUE=\"" . $cname . "\">" . $ccname[$cname] . "\n";
  }
  print "</SELECT><P>\n";
  print "<INPUT TYPE=SUBMIT NAME=classsel><P>\n";
}
?>
</FORM>
  </TD></TR>
  <tr>
    <td width="190" ALIGN=MIDDLE>
<?php
    if ($_SESSION['classi'] != "") {

      ?><p align="center"><a href=mclass.php" language="JavaScript"
      onmouseover="if(NavOver) document['mmaps'].src=mmaps1.src"
      onmouseout="if(NavOver) document['mmaps'].src=maps2.src"><img src="/images/mailmaps.gif" 
      width="140" height="35" border="0" alt="Mail Classes" name="mmaps"></a><?php } else {?><a href="/ldap" language="JavaScript"
      onmouseover="if(NavOver) document['mconf'].src=mconf1.src"
      onmouseout="if(NavOver) document['mconf'].src=mconf2.src"><img src="/images/mailconfig.gif" width="140" height="35" border="0" alt="Mail Admin" name="mconf"></a><?php };?></p>
    </td>
  </tr>
</table>
</body>

