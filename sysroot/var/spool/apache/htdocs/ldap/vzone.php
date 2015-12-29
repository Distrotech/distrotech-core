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
<FORM METHOD=POST NAME=vzone onsubmit="ajaxsubmit(this.name);return false">
<table border="0" width="90%" cellspacing="0" cellpadding="0">
<?php

$grpprop=array("quotaHomeDir"=>_("Home Directory Size Limit"),
               "quotaMailSpool"=>_("Mailbox Size Limit"),
               "quotaFileServer"=>_("File Server Size Limit"),
               "maxWebAliases"=>_("Maximum Allowed Web Aliases"),
               "maxAliases"=>_("Maximum Allowed Email Aliases"),
               "maxMailBoxes"=>_("Maximum Allowed Email Boxes"));

$grtextprop=array("radiusRealm"=>_("Radius Authentication Realm"));

$grchekprop=array("smbServerAccess"=>_("Allow File Server Access"),
                  "squidProxyAccess"=>_("Allow Proxy Server Authentication"));

$grattr=array();
$tmparr=$grpprop;

while(list($gattr)=each($tmparr)) {
  array_push($grattr,$gattr);
}

$tmparr=$grchekprop;

while(list($gattr)=each($tmparr)) {
  array_push($grattr,$gattr);
}

$tmparr=$grtextprop;

while(list($gattr)=each($tmparr)) {
  array_push($grattr,$gattr);
}

$abdn="ou=Vadmin";

if (($groupedit == _("Delete")) && ($group != "")){
  ldap_delete($ds,"cn=$group,ou=Vadmin");
  ldap_delete($ds,"o=$group,ou=Users");
} else if (($groupedit == _("Add")) && ($newgroup != "")) {
  $addent=array();
  $addent["objectclass"][0]="top";
  $addent["objectclass"][1]="virtZoneSettings";
  $addent["objectclass"][2]="radiusprofile";
  $addent["cn"][0]=$newgroup;
  $addent["radiusServiceType"][0]="Framed-User";
  $addent["radiusFramedProtocol"][0]="PPP";
  $addent["radiusAuthType"][0]="Pam";
  $addent["radiusFramedIPNetmask"][0]="255.255.255.255";
  $addent["radiusFramedIPAddress"][0]="255.255.255.254";

  $addent["radiusPortType"][0]="Wireless-802.11";
  $addent["radiusFramedMTU"][0]="1500";
  $addent["radiusFramedCompression"][0]="Van-Jacobson-TCP-IP";
  $addent["radiusSessionTimeout"][0]="86400";
  $addent["radiusAcctInterimInterval"][0]="600";
  $addent["radiusIdleTimeout"][0]="1800";
  $addent["radiusSimultaneousUse"][0]="1";

  for($cnt=0;$cnt<count($grattr);$cnt++) {
    $aattr=$grattr[$cnt];
    if ($grchekprop[$aattr] != "") {
      $addent[$aattr][0]="off";
    } else if ($grpprop[$aattr] != "") {
      $addent[$aattr][0]="0";
    }
  } 

  $addent["member"][0]="uid=admin,ou=users";

/*
  while(list($akey,$aval)=each($addent)) {
    for($vcnt=0;$vcnt<count($aval);$vcnt++) {
      print $akey . ": " . $aval[$vcnt] . "<BR>\n";
    }
  }
*/

  ldap_add($ds,"cn=$newgroup,ou=Vadmin",$addent);

  $addent=array();
  $addent["objectclass"][0]="organization";
  $addent["o"][0]="$newgroup";
  ldap_add($ds,"o=$newgroup,ou=Users",$addent);

  system("/usr/sbin/genconf");
}

if (($group != "") && ($groupedit == _("Modify"))){
  if (($groupmod == _("Delete")) && (strtolower($ldn) != strtolower(${$group}))) {
    if (ereg("(sambasid=s-1-5-21-.*,ou=idmap)",strtolower(${$group}),$olddn)) {
      $addent["member"]=$olddn[0];
      $addent2["member"]=${$group};
    } else {
      ereg("(uid=.*,ou=users)",strtolower(${$group}),$olddn);
      $addent["member"]=$olddn[0];
      $addent2["member"]=${$group};
    }
    ldap_mod_del($ds,"cn=" . $group . ",ou=Vadmin",$addent);
    ldap_mod_del($ds,"cn=" . $group . ",ou=Vadmin",$addent2);
  } else if (($groupmod == _("Add")) && ($add != "")) {
    if (ereg("(sambasid=s-1-5-21-.*,ou=idmap)",strtolower($add),$olddn)) {
      $addent["member"]=$olddn[0];
      $addent2["member"]=$add;
    } else {
      ereg("(uid=.*,ou=users)",strtolower($add),$olddn);
      $addent["member"]=$olddn[0];
      $addent2["member"]=$add;
    }
    ldap_mod_add($ds,"cn=" . $group . ",ou=Vadmin",$addent);
    ldap_mod_add($ds,"cn=" . $group . ",ou=Vadmin",$addent2);
  } else if ($groupmod == _("Update")) {
    $addent=array();
    $todel=array();
    for($cnt=0;$cnt<count($grattr);$cnt++) {
      $aattr=$grattr[$cnt];
      if (${$aattr} != "") {
        $addent[$aattr]=${$aattr};
      } else if ($grchekprop[$aattr] != "") {
        $addent[$aattr]="off";
      } else if ($grtextprop[$aattr] != "") {
        array_push($todel,$aattr);
      } else {
       $addent[$aattr]="0";
      }
    }
    
    $rchk=ldap_search($ds,"cn=" . $group . ",ou=Vadmin","(&(objectClass=virtZoneSettings)(cn=" . $group . "))",array("radiuscheckitem"));
    $chkinf=ldap_get_entries($ds,$rchk);
    $addci=1;
    for($ccnt=0;$ccnt<$chkinf[0]["radiuscheckitem"]["count"];$ccnt++) {
      eregi("^(.*) = \"(.*)\"$",$chkinf[0]["radiuscheckitem"][$ccnt],$ciarr);
      if (($ciarr[1] == "Realm") && ($ciarr[2] != $radiusRealm)){
        $checkdel["radiuscheckitem"]=$chkinf[0]["radiuscheckitem"][$ccnt];
        ldap_mod_del($ds,"cn=" . $group . ",ou=Vadmin",$checkdel);
      } else if (($ciarr[1] == "Realm") && ($ciarr[2] == $radiusRealm)){
        $addci=0;    
      }
    }

    if (($radiusRealm != "") && ($addci)){
      $checkadd["radiuscheckitem"]="Realm = \"" . $radiusRealm . "\"";
      ldap_mod_add($ds,"cn=" . $group . ",ou=Vadmin",$checkadd);
    }

    if (count($todel) > 0) {
      $delsr=ldap_search($ds,"cn=" . $group . ",ou=Vadmin","(&(objectClass=virtZoneSettings)(cn=" . $group . "))",$todel);
      $info = ldap_get_entries($ds, $delsr);
      for($cnt=0;$cnt < count($todel);$cnt++) {
        $delinf[$todel[$cnt]]=$info[0][strtolower($todel[$cnt])][0];
      }
      ldap_mod_del($ds,"cn=$group,ou=Vadmin",$delinf);
    }

    ldap_modify($ds,"cn=$group,ou=Vadmin",$addent);
  } else if ($groupmod == _("Delete")) {
?>
    <SCRIPT>alert("You May Not Delete Yourself !!!");</SCRIPT>
<?php
  }

  $sobj="(&(objectClass=virtZoneSettings)(cn=$group))";

  $sr=ldap_search($ds,"","(&(objectClass=officeperson)(uid=*))",array("cn","dn","uid"));
  $info = ldap_get_entries($ds, $sr);

  $allusers=array();

  unset($info["count"]);

  for($ucnt=0;$ucnt < count($info);$ucnt++) {
    if ($info[$ucnt]["uid"][0] != "") {
      $username[$info[$ucnt]["uid"][0]]=$info[$ucnt]["dn"];
      $dnmap[$info[$ucnt]["dn"]]=$info[$ucnt]["cn"][0] . " (" . $info[$ucnt]["uid"][0] . ")";
      array_push($allusers,$info[$ucnt]["uid"][0]);
    }
  }


  $sr=ldap_search($ds,"$abdn",$sobj);
  $info = ldap_get_entries($ds, $sr);

  $dn=$info[0]["dn"];  

  print "<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>" . _("Adding Entries To") . " " . $group . " " . _("Virtual Zone") . "</TH>";

  $allent=$info[0]["member"];
  unset($allent["count"]);

  while(list($ent,$gattr)=each($grattr)) {
    $giattr=strtolower($gattr);
    ${$gattr}=$info[0][$giattr][0];
  }

  $allcn=array();
  for($mcnt=0;$mcnt < count($allent);$mcnt++) {
    if ($dnmap[$allent[$mcnt]] != "") {
      array_push($allcn,$allent[$mcnt]);
    }
  }

  sort($allcn);
  reset($allcn);

  print "<TR CLASS=list-color1><TD WIDTH=50% onmouseover=\"myHint.show('VZ2')\" onmouseout=\"myHint.hide()\">" . _("Existing Members") . "</TD><TD><SELECT NAME=\"" . $group . "\">\n";
  for($dcnt=0;$dcnt < count($allcn);$dcnt++) {
    $pudn=$username[$allcn[$dcnt]];
    if (($pudn != "uid=admin,ou=users") && ($allcn[$dcnt] != "")) {
      print "<OPTION VALUE=\"" . $allcn[$dcnt] . "\">" . $dnmap[$allcn[$dcnt]] . "\n";
      $useddn[$allcn[$dcnt]]=true;
    }
  }
  print "</SELECT></TD></TR>";
  print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('VZ3')\" onmouseout=\"myHint.hide()\">Possible Members</TD><TD>\n";

  print "<INPUT TYPE=HIDDEN NAME=group VALUE=" . $group . ">\n";
  print "<INPUT TYPE=HIDDEN NAME=groupedit VALUE=" . $groupedit . ">\n";

  sort($allusers);
  reset($allusers);

  print "<SELECT NAME=add>";

  for($dcnt=0;$dcnt < count($allusers);$dcnt++) {
    $pudn=$username[$allusers[$dcnt]];
    if ((! $useddn[$pudn] ) && ($allusers[$dcnt] != "uid=admin,ou=users")){
      print "<OPTION VALUE=\"" . $pudn . "\">" . $dnmap[$pudn] . "\n";
    }
  }
  print "</SELECT></TD></TR>\n";

  $bcol[0]=" CLASS=list-color1";
  $bcol[1]=" CLASS=list-color2";

  $bcnt=0;

  while(list($var,$disc)=each($grpprop)) {
    $btm=$bcnt % 2;

    if (${$var} == ""){
      ${$var}="0";
    }
    print "<TR " . $bcol[$btm] . "><TD onmouseover=\"myHint.show('" . strtolower($var) . "')\" onmouseout=\"myHint.hide()\">$disc</TD><TD><INPUT TYPE=TEXT NAME=\"" . $var . "\" VALUE=\"" . ${$var} . "\" SIZE=4></TD></TR>\n";
    $bcnt++;
  }

  while(list($var,$disc)=each($grchekprop)) {
    $btm=$bcnt % 2;

    print "<TR " . $bcol[$btm] . "><TD onmouseover=\"myHint.show('" . strtolower($var) . "')\" onmouseout=\"myHint.hide()\">$disc</TD><TD><INPUT TYPE=CHECKBOX NAME=\"" . $var ."\"";
    if (${$var} == "on") {
      print " CHECKED";
    }
    print "></TD></TR>\n";
    $bcnt++;
  }

  while(list($var,$disc)=each($grtextprop)) {
    $btm=$bcnt % 2;
    print "<TR " . $bcol[$btm] . "><TD onmouseover=\"myHint.show('" . strtolower($var) . "')\" onmouseout=\"myHint.hide()\">$disc</TD><TD><INPUT TYPE=TEXT NAME=\"" . $var . "\" VALUE=\"" . ${$var} . "\"></TD></TR>\n";
    $bcnt++;
  }

  $btm=$bcnt % 2;

  print "<TR " . $bcol[$btm] . "><TD COLSPAN=2 ALIGN=CENTER><INPUT TYPE=SUBMIT onclick=this.name='groupmod' VALUE=\"" . _("Add") . "\">\n";
  print "<INPUT TYPE=SUBMIT onclick=this.name='groupmod' VALUE=\"" . _("Update") . "\">\n";
  print "<INPUT TYPE=BUTTON onclick=javascript:openvradconf() VALUE=\"" . _("Radius") . "\">\n";
  print "<INPUT TYPE=SUBMIT onclick=this.name='groupmod' VALUE=\"" . _("Delete")  . "\">\n";
} else {
  $sr=ldap_search($ds,"ou=Vadmin","(objectclass=virtZoneSettings)");
  $info = ldap_get_entries($ds, $sr);

  for ($i=0; $i<$info["count"]; $i++) {
    $srsort[$i]=$info[$i]["cn"][0];
  }
  asort($srsort);
  reset ($srsort);

  print "<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>" . _("Virtual Zone Administration") . "</TH></TR>";
  print "<TR CLASS=list-color1><TD WIDTH=50% onmouseover=\"myHint.show('VZ0')\" onmouseout=\"myHint.hide()\">" . _("Select Virtual Zone To Edit") . "</TD><TD><SELECT NAME=group>\n";
  while (list($i,$val) = each($srsort)) {
    $dn=$info[$i]["dn"];
    $cname=$info[$i]["cn"][0];
    if ($cname != "Virtual Admin Access") {
      print "<OPTION VALUE=\"" . $cname . "\">" . $cname . "\n";
    }
  }
  print "</SELECT></TD></TR>\n";

  print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('VZ1')\" onmouseout=\"myHint.hide()\">New Zone To Add</TD><TD><INPUT TYPE=TEXT NAME=newgroup></TD></TR>";
  print "<TR CLASS=list-color1><TD COLSPAN=2 ALIGN=CENTER><INPUT TYPE=SUBMIT onclick=this.name='groupedit' VALUE=\"" . _("Add") . "\">\n";
  print "<INPUT TYPE=SUBMIT onclick=this.name='groupedit' VALUE=\"" . _("Modify") . "\">\n";
  print "<INPUT TYPE=SUBMIT onclick=this.name='groupedit' VALUE=\"" . _("Delete") . "\"><P>\n";
}
?>
</TD></TR>
</FORM>
</table>

