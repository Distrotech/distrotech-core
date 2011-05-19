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

if (is_file("auth.inc")) {
  include_once "auth.inc";
  include "csvfunc.inc";
} else {
  include_once "reception/auth.inc";  
  $SUPER_USER=1;
  $dgroup=$msqldat[5];
}

$cclistq="SELECT name,fullname,email,altc.value,office.value FROM users
                         LEFT OUTER JOIN astdb ON (key=substr(name,1,2) AND Family='LocalPrefix')
                         LEFT OUTER JOIN astdb AS altc ON (name = altc.family AND altc.key = 'ALTC')
                         LEFT OUTER JOIN astdb AS office ON (name = office.family AND office.key = 'OFFICE')
                         LEFT OUTER JOIN astdb AS dgroup ON (name = dgroup.family AND dgroup.key = 'DGROUP')";
if ($SUPER_USER != 1) {
 $cclistq.=" LEFT OUTER JOIN astdb AS bgrp ON (bgrp.family=name AND bgrp.key='BGRP')";
}

$cclistq.=" WHERE astdb.value=1";
if ($dgroup != "") {
  $cclistq.=" AND dgroup.value='" . $dgroup . "'";
}
if ($SUPER_USER != 1) {
  $cclistq.=" AND " . $clogacl;
}
$cclistq.=" ORDER BY name";


$cclist=pg_query($db,$cclistq);

$bcolor[0]="list-color2";
$bcolor[1]="list-color1";

if ($_POST['print'] < 2) {
%>
<CENTER>
<FORM METHOD=POST NAME=printform>
<INPUT TYPE=HIDDEN NAME=print>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<%print $_SESSION['disppage'];%>">
</FORM>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>

</FORM>
<TR CLASS=list-color2>
<TH CLASS=heading-body COLSPAN=5><%print _("Extension List");%></TH>
</TR>
<TR CLASS=list-color1>
<TH CLASS=heading-body2 WIDTH=12%><%print _("Exten.");%></TH>
<TH CLASS=heading-body2 WIDTH=22%><%print _("User");%></TH>
<TH CLASS=heading-body2 WIDTH=22%><%print _("Email");%></TH>
<TH CLASS=heading-body2 WIDTH=22%><%print _("Alt Contact");%></TH>
<TH CLASS=heading-body2 WIDTH=22%><%print _("Office/Loc.");%></TH>
</TR>
<%
} else {
    $data=array(_("Exten."),_("User"),_("Email"),_("Alt Contact"),_("Office/Loc."));
    $dataout="\"" . str_replace(array("\"","--!@#--"),array("\"\"","\",\""),implode("--!@#--",$data)). "\"\n";
    print $dataout;
}
$num=pg_num_rows($cclist);
for ($i=0; $i < $num; $i++) {
  $r = pg_fetch_row($cclist,$i);
  $rem=$i % 2; 
  if ($_POST['print'] < 2) {
    print "<TR CLASS=" . $bcolor[$rem] . ">";
    if ($r[1] == "") {
      $r[1]="&nbsp;";
    }
    if ($r[2] == "") {
      $r[2]="&nbsp;";
    }
    if (($r[3] == "") || ($r[3] == "0")){
      $r[3]="&nbsp;";
    }
    if (($r[4] == "") || ($r[4] == "0")){
      $r[4]="&nbsp;";
    }
  }
  if ($_POST['print'] < 2) {
    print "<TD>" . $r[0] . "</TD><TD>" . $r[1] . "</TD><TD>" . $r[2] . "</TD><TD>" . $r[3] . "</TD><TD>" . $r[4] . "</TD>";
    print "</TR>\n";
  } else {
    if ($r[3] == "0") {
      $r[3]="";
    } else {
      $r[3]=telformat($r[3]);
      $r[3]=nozeroout($r[3]);
    }
    $data=array($r[0],"'" . $r[1],"'" . $r[2],$r[3],"'" . $r[4]);
    $dataout="\"" . str_replace(array("\"","--!@#%^&--"),array("\"\"","\",\""),implode("--!@#%^&--",$data)). "\"\n";
    print $dataout;
  }
}
  if ($_POST['print'] < "1") {
    $rem=$i % 2; 
    print "<TR CLASS=" . $bcolor[$rem] . "><TH COLSPAN=5 CLASS=heading-body>";
    print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("CSV Export") . "\" ONCLICK=\"csvpage(document.printform)\">";
    print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.printform)\">";
    print "</TH></TR>";
  }
if ($_POST['print'] < 2) {
  print "</TABLE>\n";
}
%>
