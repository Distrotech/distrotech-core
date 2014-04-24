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

$adescrip["cn"]="Name";
$adescrip["telephoneNumber"]="Number";

$atrib=array("cn","telephoneNumber");


if ($ds) {
  if (isset($submited)) {
    $dn="cn=$cn,ou=Snom";
    $info["objectClass"][0]="snomcontact";

    $natrib=$atrib;
    while(list($idx,$catt)=each($natrib)) {
      if ($$catt != "") {
        $info[$catt]=$$catt;
      }
    }


    if (!ldap_add($ds,$dn,$info)) {
      if (ldap_errno($ds) == "32") {
        $info2["objectclass"][0]="organizationalUnit";
        $info2["ou"]="snom";
        $dn2="ou=snom";
        ldap_add($ds,$dn2,$info2);
      }
      if (!ldap_add($ds,$dn,$info)) {
        print "<CENTER><B><H2><FONT COLOR=RED>Not Added</FONT></B></CENTER>";
      }
    } else {
     $_SESSION['classi']=$cn; 
     $_SESSION['utype']="snom";
     include "snominfo.php";
     return;
    }
  }
}
%>
<CENTER>
<FORM METHOD=POST NAME=snomadd onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<INPUT TYPE=HIDDEN NAME=classi VALUE="<%print $_POST['classi']%>">
<INPUT TYPE=HIDDEN NAME=disppage VALUE="ldap/addsnom.php">
<INPUT TYPE=HIDDEN NAME=utype VALUE="<%print $_POST['utype']%>">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>Adding Entry To Snom Global Phone Book</TH></TR>
<%

while(list($attr,$aname)=each($adescrip)) {
  $rem=$cnt % 2;
  if ($rem == 1) {
    $bcolor=" CLASS=list-color2";
  } else {
    $bcolor=" CLASS=list-color1";
  }

%>
  <TR <% print "$bcolor"%>><TD WIDTH=50% onmouseover="myHint.show('<%print $attr;%>')" onmouseout="myHint.hide()">
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
    print "<INPUT TYPE=SUBMIT VALUE=Modify onclick=this.name='update'>";
    print "<INPUT TYPE=SUBMIT VALUE=Delete onclick=this.name='delete'>";
    print "<INPUT TYPE=RESET VALUE=Reset>";
  } else {
    print "<INPUT TYPE=SUBMIT VALUE=Add onclick=this.name='submited'>";
  }
%>
    </TD></TR>
<INPUT TYPE=HIDDEN NAME=baseou VALUE="<%print $baseou;%>">
</FORM>
</TABLE>

