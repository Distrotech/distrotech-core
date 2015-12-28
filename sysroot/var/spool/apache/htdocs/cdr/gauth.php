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

if (isset($pbxupdate)) {
  if ($key == "") {
    $eddi=pg_query($db,"SELECT id FROM astdb WHERE value='GAUTH' AND family='" . $newkey . "' AND key='" . $newval . "'");
    if (pg_num_rows($eddi)) {
      $ddiid=pg_fetch_row($eddi,0);
      pg_query($db,"UPDATE astdb SET key='" . $newval . "' WHERE value='GAUTH' AND family='" . $newkey . "'");
    } else {
      pg_query($db,"INSERT INTO astdb (value,family,key) VALUES ('GAUTH','$newkey','$newval')");
    }
  } else if ($key != "") {
    list($dbkey,$dbfam)=split(":",$key);
    pg_query($db,"DELETE FROM astdb WHERE value='GAUTH' AND key='" . $dbkey . "' AND family='" . $dbfam . "'");
  }
}

$qgetdata=pg_query($db,"SELECT astdb.key,astdb.family FROM astdb  WHERE astdb.value='GAUTH' ORDER by family,key");


?>

<CENTER>
<FORM METHOD=POST NAME=pbxgaform onsubmit="ajaxsubmit(this.name);return false;">
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH CLASS=heading-body COLSPAN=2><?php print _("Asterisk PBX Virtual PBX Access");?></TH>
</TR>
<TR CLASS=list-color1>
<TD onmouseover="myHint.show('DA0')" onmouseout="myHint.hide()"><?php print _("Select Access To Delete");?></TD>
<TD><SELECT NAME=key>
<OPTION VALUE=""><?php print _("Add New Access Control Below");?></OPTION>
<?php
  $sr=ldap_search($ds,"ou=Admin","(|(cn=Call Logging)(cn=Voip Admin)(cn=TMS Access))",array("member"));
  $info = ldap_get_entries($ds, $sr);

  $uarray=array();
  $usern=array();
  $udnarr=array();

  for($grpcnt=0;$grpcnt<$info["count"];$grpcnt++) {
    unset($info[$grpcnt]["member"]["count"]);
    for($ucnt=0;$ucnt < count($info[$grpcnt]["member"]);$ucnt++) {
      print $info[$grpcnt]["member"][$ucnt] . "\n";
      $md=ldap_search($ds,$info[$grpcnt]["member"][$ucnt],"(|(objectclass=officeperson)(cn=*))",array("cn"));
      $mdr=ldap_first_entry($ds,$md);
      $dn=ldap_get_dn($ds,$mdr);
      if (($dn != "") && ($done[$dn] != 1)) {
        $mdinf=ldap_get_attributes($ds,$mdr);
        $done[$dn]=1;
        $uarray[$mdinf["cn"][0]]=$dn;
        $udnarr[$dn]=$mdinf["cn"][0];
        array_push($usern,$mdinf["cn"][0]);
      }
    }
  }
  $dnum=pg_num_rows($qgetdata);
  for($i=0;$i<$dnum;$i++){
    $getdata=pg_fetch_array($qgetdata,$i);
    $done[$getdata[0]]=1;
    print "      <OPTION VALUE=\"" . $getdata[0] . ":" . $getdata[1] . "\">" . $udnarr[$getdata[0]] . "->" . $getdata[1] . "</OPTION>\n";
  }
?>
</SELECT>
</TR>
<TR CLASS=list-color2>
<TD onmouseover="myHint.show('DA1')" onmouseout="myHint.hide()">Group To Configure</TD>
<TD>
    <SELECT NAME=newkey>
<?php
  $bgroups=pg_query("SELECT DISTINCT value FROM astdb WHERE key='BGRP' AND value != '' ORDER BY value;");
  $bgnum=pg_num_rows($bgroups);
  for($i=0;$i<$bgnum;$i++){
    $getbgdata=pg_fetch_array($bgroups,$i);
    if ($done[$getbgdata[0]] != 1) {
      print "<OPTION VALUE=\"" . $getbgdata[0] . "\">" . $getbgdata[0] . "</OPTION>\n";
    }
  }
?>
    </SELECT><BR>
</TD>
</TR>
<TR CLASS=list-color1>
<TD onmouseover="myHint.show('DA2')" onmouseout="myHint.hide()"><?php print _("Users To Allow Access");?></TD>
<TD>
    <SELECT NAME=newval>
<?php
sort($usern);
while(list($name,$uname)=each($usern)) {
  print "<OPTION VALUE=\"" . $uarray[$uname] . "\">" . $uname . "</OPTION>";
}
?>
  </SELECT>
</TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=RESET>
    <INPUT TYPE=SUBMIT onclick=this.name='pbxupdate' VALUE="<?php print _("Save Changes");?>">
  </TD>
</TR>
</TABLE>
</FORM>
