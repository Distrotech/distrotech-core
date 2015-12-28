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
  if (! $rdn) {
    include "auth.inc";
  }
?>
<CENTER>
<FORM METHOD=POST NAME=mclassf onsubmit="ajaxsubmit(this.name);return false">
<table border="0" width="90%" cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=2>
<?php
$abdn="ou=Email";

$ccname["R"]=_("Relay Domains (Enter Domains To Allow Relaying Or Spooling)");
$ccname["LDAPRoute"]=_("Accepted Domains (Enter Domains To Accept Mail For Delivery)");
if (! file_exists("/etc/.networksentry-lite")) {
 $ccname["M"]=_("Masqueraded Domains (Aliases To Be Treated As Local For Masquerading)");
 $ccname["WhiteList"]=_("No Spam Checks (Enter Email Address/Domain)");
 $ccname["VirusSafe"]=_("No Virus Checks (Enter Email Address/Domain)");
}

if ($_POST['classi'] == "") {
  $sobj="(&(objectClass=sendmailMTAClass)(sendmailMTAClassName=*))";
} else {
  $sobj="(&(objectClass=sendmailMTAClass)(sendmailMTAClassName=" . $_POST['classi'] . "))";
}

if ($_POST['classi'] != "") {
  if ($classedit == _("Delete")) {
    $addent=array();
    $addent["sendmailmtaclassvalue"]=$_POST[$_POST['classi']];
    ldap_mod_del($ds,"sendmailMTAClassName=" . $_POST['classi'] . ",ou=Email",$addent);
  } else if (($classedit == "Add") && ($add != "")) {
    $addent=array();
    $addent["sendmailmtaclassvalue"]=$add;
    ldap_mod_add($ds,"sendmailMTAClassName=" . $_POST['classi'] . ",ou=Email",$addent);
  }

  $sr=ldap_search($ds,"$abdn",$sobj);
  $info = ldap_get_entries($ds, $sr);

  $dn=$info[0]["dn"];  

  $cname=$info[0]["sendmailmtaclassname"][0];
  if (isset($ccname[$cname])) {
    print $ccname[$cname] . "</TD>";
    print "<TR CLASS=list-color1><TD onmouseover=\"myHint.show('exist')\" onmouseout=myHint.hide() WIDTH=50%>" . _("Existing Entries") . "</TD><TD><SELECT NAME=\"" . $cname . "\">";
  }

  $allent=$info[0]["sendmailmtaclassvalue"];
  unset($allent["count"]);

  sort($allent);
  reset($allent);

  for($dcnt=0;$dcnt < count($allent);$dcnt++) {
    if ($allent[$dcnt] != "127.0.0.1") {
      print "<OPTION VALUE=\"" . $allent[$dcnt] . "\">" . $allent[$dcnt] . "\n";
    }
  }
  print "</SELECT></TD></TR><TR  CLASS=list-color2><TD onmouseover=\"myHint.show('" . $_POST['classi'] . "')\" onmouseout=myHint.hide() WIDTH=50%>" . _("New Entry") . "</TD><TD>\n";

  print "<INPUT TYPE=HIDDEN NAME=classi VALUE=" . $_POST['classi'] . ">";
  print "<INPUT TYPE=TEXT NAME=add></TD></TR><TR  CLASS=list-color1><TD ALIGN=CENTER COLSPAN=2>\n";
  print "<INPUT TYPE=SUBMIT onclick=this.name='classedit' VALUE=\"" . _("Add") . "\">\n";
  print "<INPUT TYPE=SUBMIT onclick=this.name='classedit' VALUE=\"" . _("Delete") . "\"><P>\n";
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
    if (isset($ccname[$cname])) {
      print "<OPTION VALUE=\"" . $cname . "\">" . $ccname[$cname] . "\n";
    }
  }
  print "</SELECT><P>\n";
  print "<INPUT TYPE=SUBMIT NAME=classsel><P>\n";
}
?>
</FORM>
  </TD></TR>
</table>
