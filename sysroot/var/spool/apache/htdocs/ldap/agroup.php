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
if ( ! $rdn) {
  include "auth.inc";
}

if ($_POST['classi'] != "") {
  $group=$_POST['classi'];
  $groupedit="Modify";
}

?>
<CENTER>
<FORM METHOD=POST NAME=agrpfrm onsubmit="ajaxsubmit(this.name);return false">
<table border="0" width="90%" cellspacing="0" cellpadding="0">
<?php
$abdn="ou=Admin";


if (($group != "") && ($groupedit == "Modify")){
  if (($groupmod == "Delete") && (strtolower($ldn) != strtolower($active))) {
    $addent=array();
    $dnarr=ldap_explode_dn($active,1);
    if (preg_match("/(sambasid=s-1-5-21-.*,ou=idmap)/i",$active,$olddn)) {
      $oldent["member"][0]=$olddn[0];
      $addent["member"][0]=$active;
      $usr=ldap_search($ds,"ou=idmap","(&(objectClass=radiusprofile)(sambaSID=" . $dnarr[0] . "))",array("uid"));
      $uidinfo = ldap_get_entries($ds, $usr);
      $addent3["memberUid"]=$uidinfo[0]["uid"][0];
    } else {
      preg_match("/(uid=.*,ou=users)/i",$active,$olddn);
      $oldent["member"][0]=$olddn[0];
      $addent["member"][0]=$active;
      $addent3["memberUid"]=$dnarr[0];
    }
    if ($_POST['classi'] == "cn=Admin Access,ou=Admin") {
      touch("/var/spool/apache/htdocs/ns/config/gensshauth");
      ldap_mod_del($ds,"cn=smbadm,ou=Groups",$addent3);
      ldap_mod_del($ds,"cn=localadmin,ou=Groups",$addent3);
    }
    ldap_mod_del($ds,$group,$oldent);
    ldap_mod_del($ds,$group,$addent);
  } else if (($groupmod == _("Add")) && ($add != "")) {
    $addent=array();
    $add=strtolower($add);
    $dnarr=ldap_explode_dn($add,1);
    if (preg_match("/(sambasid=s-1-5-21-.*,ou=idmap),(.*)/i",$add,$olddn)) {
      $addent["member"][0]=$olddn[1];
      ldap_mod_del($ds,$group,$addent);
      $addent["member"][1]=$add;
      $usr=ldap_search($ds,"ou=idmap","(&(objectClass=radiusprofile)(sambaSID=" . $dnarr[0] . "))",array("uid"));
      $uidinfo = ldap_get_entries($ds, $usr);
      $addent3["memberUid"]=$uidinfo[0]["uid"][0];
    } else {
      preg_match("/(uid=.*,ou=users),(.*)/i",$add,$olddn);
      $addent["member"][0]=$olddn[1];
      ldap_mod_del($ds,$group,$addent);
      $addent["member"][1]=$add;
      $addent3["memberUid"]=$dnarr[0];
    }
    if ($_POST['classi'] == "cn=Admin Access,ou=Admin") {
      touch("/var/spool/apache/htdocs/ns/config/gensshauth");
      ldap_mod_add($ds,"cn=smbadm,ou=Groups",$addent3);
      ldap_mod_add($ds,"cn=localadmin,ou=Groups",$addent3);
    }
    ldap_mod_add($ds,$group,$addent);
    $isadmin=ldap_search($ds,$group,"member=" . $LDAP_ROOT_DN,array("dn"));
    if (! ldap_count_entries($ds,$isadmin)) {
      $addent["member"]=$LDAP_ROOT_DN;
      ldap_mod_add($ds,$group,$addent);
    }
    $isadmin=ldap_search($ds,$group,"member=" . $LDAP_ROOT_DN . "," . $olddn[2],array("dn"));
    if (! ldap_count_entries($ds,$isadmin)) {
      $addent["member"]=$LDAP_ROOT_DN . "," . $olddn[2];;
      ldap_mod_add($ds,$group,$addent);
    }
  } else if ($groupmod == _("Delete")) {
?>
    <SCRIPT>alert("<?php print _("You May Not Delete Yourself !!!");?>");</SCRIPT>
<?php
  }

  $sobj="objectClass=groupofnames";

  $sr=ldap_search($ds,"","(|(objectClass=officeperson)(objectclass=shadowAccount))",array("uid","cn","dn"));
  $info = ldap_get_entries($ds, $sr);

  $allusers=array();
  $allact=array();

  unset($info["count"]);

  for($ucnt=0;$ucnt < count($info);$ucnt++) {
    $userdn=strtolower($info[$ucnt]["dn"]);
    if ((!$userdone[$userdn]) && (substr($info[$ucnt]["uid"][0],strlen($info[$ucnt]["uid"][0])-1) != "\$") && ($info[$ucnt]["uid"][0] != "")) {
      array_push($allusers,$info[$ucnt]["uid"][0]);
      $username[$info[$ucnt]["uid"][0]]=$userdn;
      $uidmap[$userdn]=$info[$ucnt]["uid"][0];
      $usermap[$userdn]=$info[$ucnt]["cn"][0];
      $userdone[$userdn]=1;
    }
  }

/*
  $sr=ldap_search($ds,"ou=Servers","objectClass=ipHost");
  $info = ldap_get_entries($ds, $sr);
  unset($info["count"]);
  for($ucnt=0;$ucnt < count($info);$ucnt++) {
    $userdn=strtolower($info[$ucnt]["dn"]);
    array_push($allusers,$info[$ucnt]["cn"][0]);
    $username[$info[$ucnt]["cn"][0]]=$userdn;
    $usermap[$userdn]=$info[$ucnt]["cn"][0];
  }
*/

  $sr=ldap_search($ds,$group,$sobj);
  if (! $sr) {
    $dndat=split(",",$group);
    $cndat=split("=",$dndat[0]);
    $infoa["objectclass"][0]="top";
    $infoa["objectclass"][1]="groupOfNames";
    $infoa["cn"]=$cndat[1];
    $infoa["member"]="uid=admin,ou=Users";
    ldap_add($ds,$group,$infoa);
    $sr=ldap_search($ds,$group,$sobj);
  }
  $gdn=ldap_explode_dn($group,1);

  $sp=ldap_search($ds,"cn=" . $gdn[1],"objectClass=device");
  if ( ! $sp ) {
    $rpent["objectClass"]="top";
    $rpent["objectClass"]="device";
    $rpent["cn"]=$gdn[1];
    $r=ldap_add($ds,"cn=" . $gdn[1],$rpent);
  }

  $info = ldap_get_entries($ds, $sr);
  $dn=$info[0]["dn"];

  $allent=$info[0]["member"];
  unset($allent["count"]);

  for($dcnt=0;$dcnt < count($allent);$dcnt++) {
    $actdn=strtolower($allent[$dcnt]);
    array_push($allact,$actdn);
    $unact[$usermap[$actdn]]=$actdn;
    $useduid[$actdn]=true;
  }

  sort($allact);
  reset($allact);

  print "<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>" . _("Adding Entries To") . " " . $info[0]["cn"][0] . "</TH></TR>";
  print "<TR CLASS=list-color1><TD WIDTH=50% onmouseover=\"myHint.show('SG4')\" onmouseout=myHint.hide()>" . _("Existing Members") . "</TD><TD><SELECT NAME=\"" . active . "\">";


  for($dcnt=0;$dcnt < count($allact);$dcnt++) {
    if ($usermap[$allact[$dcnt]] != "") {
      print "\n<OPTION VALUE=\"" . $allact[$dcnt] . "\">" . $usermap[$allact[$dcnt]] . " (" . $uidmap[$allact[$dcnt]] . ")";
    }
  }

  print "\n</SELECT></TD></TR>";
  print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('SG5')\" onmouseout=myHint.hide()>" . _("Possible Members") . "</TD>";

  print "<INPUT TYPE=HIDDEN NAME=group VALUE=\"" . $group . "\">\n";
  print "<INPUT TYPE=HIDDEN NAME=groupedit VALUE=" . $groupedit . ">\n";

  sort($allusers);
  reset($allusers);

  print "<TD><INPUT TYPE=TEXT NAME=add autocomplete=off></TD></TR>";

//  print "<TD><SELECT NAME=add>";
//  for($dcnt=0;$dcnt < count($allusers);$dcnt++) {
//    if ((! $useduid[$username[$allusers[$dcnt]]] ) && ($allusers[$dcnt] != "")) {
//      print "\n<OPTION VALUE=\"" . $username[$allusers[$dcnt]] . "\">" . $usermap[$username[$allusers[$dcnt]]]  . " (" . $uidmap[$username[$allusers[$dcnt]]] . ")";;
//    }
//  }
//  print "\n</SELECT></TD></TR>";

  print "<TR CLASS=list-color1><TD COLSPAN=2 ALIGN=CENTER><INPUT TYPE=SUBMIT onclick=this.name='groupmod' VALUE=\"" . _("Add") . "\">\n";
  print "<INPUT TYPE=SUBMIT onclick=this.name='groupmod' VALUE=\"" . _("Delete") . "\">\n";
} else {

  print "Select Access Group To Edit<P><SELECT NAME=group>\n";
?>
    <OPTION VALUE="cn=Admin Access,ou=Admin">Admin Access
    <OPTION VALUE="cn=User Read Access,ou=Admin">Access To User Information
    <OPTION VALUE="cn=Call Shop Access,ou=Admin">Call Shop Users
<?php
  if (! file_exists("/etc/.networksentry-lite")) {
    $sr=ldap_search($ds,"cn=Addressbooks","objectClass=groupofnames");
    $info = ldap_get_entries($ds, $sr);
    for($mcnt=0;$mcnt<=$info[0]["member"]["count"] -1;$mcnt++) {
      $dn=$info[0]["member"][$mcnt];
      $dn=split("=",$dn);
      $abookdn=$dn[1];
      if ($abookdn != "admin" ) {
        $dnact[$abookdn]=true;
        print "<OPTION VALUE=\"cn=Users,cn=$abookdn\">$abookdn</OPTION>\n";
      }
    }
  }

  print "\n</SELECT><P>\n";
  print "<INPUT TYPE=SUBMIT onclick=this.name='groupedit' VALUE=\"Modify\">\n";
}
?>
  </TD></TR>
</table>
</FORM>

<script>
var ldappop=new TextComplete(document.agrpfrm.add,ldapautodata,'/auth/agrpxml.php',setautosearchurl,document.agrpfrm,ldappop);
</script>

