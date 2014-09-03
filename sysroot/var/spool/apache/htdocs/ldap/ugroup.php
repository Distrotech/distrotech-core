<%
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
%>
<CENTER>
<FORM METHOD=POST NAME=ugrpform onsubmit="ajaxsubmit(this.name);return false">
<table border="0" width="90%" cellspacing="0" cellpadding="0">
<%
$abdn="ou=Groups";

if (($groupedit == _("Delete")) && ($group != "")){
  if (($group != "nowebaccess") && ($group != "fullwebaccess")) {
    ldap_delete($ds,"cn=$group,ou=Groups");
  }
} else if (($groupedit == _("Add")) && ($newgroup != "") && ($discrip != "")) {
  $gidarr=array("gidnumber");
  $usedgid=array();

  $sr=ldap_search($ds,"","objectClass=posixGroup",$gidarr);
  $ginfo = ldap_get_entries($ds, $sr);

  for ($i=0; $i<$ginfo["count"]; $i++) {
    $gidnum=$ginfo[$i]["gidnumber"][0];
    if ($gidnum >= 500) {
      $usedgid[$gidnum]=$gidnum;
    }
  }

  $gcnt=500;
  while($usedgid[$gcnt] != "") {
    $gcnt++;
  }

  $sr=ldap_search($ds,"","(&(objectClass=posixGroup)(gidnumber=$gcnt))",$gidarr);
  if (! ldap_count_entries($ds,$sr)) {

    if ($domain == "") {
      $domain="S-1-5-32";
    }

    $rid=1001+$gcnt*2;
    $addent=array();
    $addent["objectclass"][0]="posixGroup";
    $addent["objectclass"][1]="sambaGroupMapping";
    $addent["cn"][0]=$newgroup;
    $addent["description"][0]=$discrip;
    $addent["gidnumber"][0]=$gcnt;
    $addent["displayName"]=$newgroup;
    $addent["sambaSID"]=$domain . "-" . $rid;
    $addent["sambaGroupType"]="2";
    ldap_add($ds,"cn=$newgroup,ou=Groups",$addent);
  }
}

if (($group != "") && ($groupedit == _("Modify"))){
  if ($groupmod == _("Delete")) {
    $addent=array();
    $addent["memberUid"]=$$group;
    ldap_mod_del($ds,"cn=$group,ou=Groups",$addent);
  } else if (($groupmod == _("Add")) && ($add != "")) {
    $addent=array();
    $addent["memberUid"]=$add;
    ldap_mod_add($ds,"cn=$group,ou=Groups",$addent);
  } else if ($grouprepair = _("Set SMB Domain")) {
    $addent=array();

    $sidarr=array("sambaSID","gidNumber");
    $usidsr=ldap_search($ds,"ou=groups","(&(objectClass=sambaGroupMapping)(cn=$group))",$sidarr);
    $usidinf = ldap_get_entries($ds, $usidsr);
    $userid=$usidinf[0]['gidnumber'][0]*2+1001;
    $gsid=$usidinf[0]['sambasid'][0];

    if ($gsid != $domain . "-" . $userid) {
      $addent["sambaSID"]=$domain . "-" . $userid;
    }
    $addent["sambaGroupType"]="2";
    ldap_modify($ds,$usidinf[0]['dn'],$addent);
  }

  $sobj="(&(objectClass=posixGroup)(cn=$group))";

  $sr=ldap_search($ds,"","(objectClass=radiusprofile)");
  $info = ldap_get_entries($ds, $sr);

  $allusers=array();

  unset($info["count"]);

  for($ucnt=0;$ucnt < count($info);$ucnt++) {
    array_push($allusers,$info[$ucnt]["uid"][0]);
    $username[$info[$ucnt]["uid"][0]]=$info[$ucnt]["cn"][0];
  }


  $sr=ldap_search($ds,"$abdn",$sobj);
  $info = ldap_get_entries($ds, $sr);

  $dn=$info[0]["dn"];  
  ereg("^(S-1-5-21-[0-9]+-[0-9]+-[0-9]+)-[0-9]+",$info[0]["sambasid"][0],$data);
  $localsid=$data[1];

  print "<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>" . _("Adding Entries To") . " " . $group . "</TH></TR>";
  print "<TR CLASS=list-color1><TD WIDTH=50% onmouseover=\"myHint.show('SG4')\" onmouseout=myHint.hide()>" . _("Existing Members") . "</TD><TD><SELECT NAME=\"" . $group . "\">\n";

  $allent=$info[0]["memberuid"];
  unset($allent["count"]);

  sort($allent);
  reset($allent);

  for($dcnt=0;$dcnt < count($allent);$dcnt++) {
    print "<OPTION VALUE=\"" . $allent[$dcnt] . "\">" . $username[$allent[$dcnt]] . " (" . $allent[$dcnt] .")\n";
    $useduid[$allent[$dcnt]]=true;
  }
  print "</SELECT></TD></TR>";
  print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('SG5')\" onmouseout=myHint.hide()>" . _("Possible Members") . "</TD><TD>\n";

  print "<INPUT TYPE=HIDDEN NAME=group VALUE=" . $group . ">\n";
  print "<INPUT TYPE=HIDDEN NAME=groupedit VALUE=" . $groupedit . ">\n";

  sort($allusers);
  reset($allusers);

  print "<SELECT NAME=add>";


  for($dcnt=0;$dcnt < count($allusers);$dcnt++) {
    if ((! $useduid[$allusers[$dcnt]] ) && ($allusers[$dcnt] != "")){
      print "<OPTION VALUE=\"" . $allusers[$dcnt] . "\">" . $username[$allusers[$dcnt]] . " (" . $allusers[$dcnt] . ")\n";
    }
  }
  print "</SELECT></TD></TR>";

  print "<TR CLASS=list-color1><TD onmouseover=\"myHint.show('SG4')\" onmouseout=myHint.hide()>" . _("SMB Domain") . "</TD><TD><SELECT NAME=domain>";


  $sr=ldap_search($ds,"","(&(sambadomainname=*)(sambasid=*))",array("sambaSID","sambaDomainName"));
  $info = ldap_get_entries($ds, $sr);

  for ($i=0; $i<$info["count"]; $i++) {
    $windom[$i]=$info[$i]["sambadomainname"][0];
  }
  asort($windom);
  reset($windom);

  while (list($i,$val) = each($windom)) {
    $disc=$info[$i]["sambadomainname"][0];
    $cname=$info[$i]["sambasid"][0];
    if (!$siddone[$cname]) {
      print "<OPTION VALUE=\"" . $cname . "\""; 
      if ($localsid == $cname) {
        print " SELECTED";
      }
      print ">" . $disc . "\n";
      $siddone[$cname]=true;
    }
  }

  print "</SELECT></TD></TR>";


  print "<TR CLASS=list-color2><TD COLSPAN=2 ALIGN=CENTER><INPUT TYPE=SUBMIT onclick=this.name='groupmod' VALUE=\"" . _("Add") ."\">\n";
  print "<INPUT TYPE=SUBMIT onclick=this.name='grouprepair' VALUE=\"" . _("Set SMB Domain") . "\">\n";
  print "<INPUT TYPE=SUBMIT onclick=this.name='groupmod' VALUE=\"" . _("Delete") . "\">\n";
} else {
  $sobj="objectClass=posixGroup";
  $sr=ldap_search($ds,"$abdn",$sobj);
  $info = ldap_get_entries($ds, $sr);

  for ($i=0; $i<$info["count"]; $i++) {
    $srsort[$i]=$info[$i]["description"][0];
  }
  asort($srsort);
  reset($srsort);

  print "<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>" . _("System Group Administration") . "</TH></TR>";
  print "<TR CLASS=list-color1><TD onmouseover=\"myHint.show('SG1')\" onmouseout=myHint.hide() WIDTH=50%>" . _("Select User Group To Edit") . "</TD><TD><SELECT NAME=group>\n";
  while (list($i,$val) = each($srsort)) {
    $dn=$info[$i]["dn"];
    $disc=$info[$i]["description"][0];
    $cname=$info[$i]["cn"][0];
    if (($cname != "smbadm") && ($cname != "localadmin") && 
        ($cname != "users" ) && ($cname != "domusers") &&
        ($cname != "nogroup" )) {
      print "<OPTION VALUE=\"" . $cname . "\">" . $disc . "\n";
    }
  }
  print "</SELECT></TD></TR>\n";

  print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('SG2')\" onmouseout=myHint.hide()>" . _("Group To Add") . "</TD><TD><INPUT TYPE=TEXT NAME=newgroup></TD></TR>";
  print "<TR CLASS=list-color1><TD onmouseover=\"myHint.show('SG3')\" onmouseout=myHint.hide()>" . _("Description") . "</TD><TD><INPUT TYPE=TEXT NAME=discrip></TD></TR>";
  print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('SG4')\" onmouseout=myHint.hide()>" . _("SMB Domain") . "</TD><TD><SELECT NAME=domain>";


  $sr=ldap_search($ds,"","(&(sambadomainname=*)(sambasid=*))",array("sambaSID","sambaDomainName"));
  $info = ldap_get_entries($ds, $sr);

  for ($i=0; $i<$info["count"]; $i++) {
    $windom[$i]=$info[$i]["sambadomainname"][0];
  }
  asort($windom);
  reset($windom);

  while (list($i,$val) = each($windom)) {
    $disc=$info[$i]["sambadomainname"][0];
    $cname=$info[$i]["sambasid"][0];
    if (!$siddone[$cname]) {
      print "<OPTION VALUE=\"" . $cname . "\">" . $disc . "\n";
      $siddone[$cname]=true;
    }
  }

  print "</SELECT></TD></TR>";
  print "<TR CLASS=list-color1><TD COLSPAN=2 ALIGN=CENTER><INPUT TYPE=SUBMIT onclick=this.name='groupedit' VALUE=\"" . _("Add") . "\">\n";
  print "<INPUT TYPE=SUBMIT onclick=this.name='groupedit' VALUE=\"" . _("Modify") . "\">\n";
  print "<INPUT TYPE=SUBMIT onclick=this.name='groupedit' VALUE=\"" . _("Delete") . "\">\n";
}
%>
</TD></TR>
</FORM>
</table>
