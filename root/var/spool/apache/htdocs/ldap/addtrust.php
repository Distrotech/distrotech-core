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

$adescrip["cn"]=_("Host Information");
$adescrip["buid"]=_("Computer Name");

$atrib=array("uid","cn","homeDirectory","uidNumber","gidNumber","loginShell");

if ($ds) {
  if (isset($submited)) {
    $uidarr=array("uidnumber");
    $sr=ldap_search($ds,"","objectClass=posixAccount",$uidarr);    
    $uinfo = ldap_get_entries($ds, $sr);
  
    $useduid=array();
    for ($i=0; $i<$uinfo["count"]; $i++) {
      $uidnum=$uinfo[$i]["uidnumber"][0];
      if ($uidnum >= 500) {
        $useduid[$uidnum]=$uidnum;
      }
    } 
    $ucnt=500;
    while($useduid[$ucnt] != "") {
      $ucnt++;
    }
    $sr=ldap_search($ds,"","(&(objectClass=posixAccount)(uidnumber=$ucnt))",$uidarr);
    if (! ldap_count_entries($ds,$sr)) {
      $uidNumber=$ucnt;
    }

    $uid="$buid\$";
    $dn="uid=$uid,ou=Trusts";
    $info["objectClass"][0]="device";
    $info["objectClass"][1]="posixAccount";
    $gidNumber=200;
    $homeDirectory="/dev/null";
    $loginShell="/usr/bin/false";

    $natrib=$atrib;
    while(list($idx,$catt)=each($natrib)) {
      if ($$catt != "") {
        $info[$catt]=$$catt;
      }
    }
    if (!ldap_add($ds,$dn,$info)) {
      print "<CENTER><B><H2><FONT COLOR=RED>Not Added</FONT></B></CENTER>";
    } else {
      $euser=$uid;
      $uid=$buid;
      include "trustinfo.php";
      return;
    }
  }
}

%>
<html>
<head>
<base target="_self">
</head>
<body>

<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<FORM METHOD=POST>

<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body><%print _("Adding New Trust Account");%></TH></TR>
<%

while(list($attr,$aname)=each($adescrip)) {
  $rem=$cnt % 2;
  if ($rem == 1) {
    $bcolor=" CLASS=list-color2";
  } else {
    $bcolor=" CLASS=list-color1";
  }

%>
  <TR <% print "$bcolor"%>><TD WIDTH=50% onmouseover="myHint.show('<%print strtolower($attr);%>')" onmouseout="myHint.hide()">
<%
      print $adescrip[$attr];
%>
    </TD><TD WIDTH=50%>
      <INPUT TYPE=TEXT SIZE=40 NAME=<%print $attr;%> VALUE="<%print $$attr;%>">
    </TD></TR>
<%
  $cnt ++;
}
  $rem=$cnt % 2;
  if ($rem != 1) {
    $bgcolor["0"]=" CLASS=list-color1";
    $bgcolor["1"]=" CLASS=list-color2";
  } else {
    $bgcolor["1"]=" CLASS=list-color1";
    $bgcolor["0"]=" CLASS=list-color2";
  }
%>
    <TR <% print $bgcolor["0"]%>><TD ALIGN=MIDDLE COLSPAN=2 WIDTH=50%>
<%
  if ($modify) {
    print "<INPUT TYPE=SUBMIT VALUE=Modify NAME=update>";
    print "<INPUT TYPE=SUBMIT VALUE=Delete NAME=delete>";
    print "<INPUT TYPE=RESET VALUE=Reset>";
  } else {
    print "<INPUT TYPE=SUBMIT VALUE=\"" . _("Add") . "\" NAME=submited>";
  }
%>
    </TD></TR>
</FORM>
</TABLE>

