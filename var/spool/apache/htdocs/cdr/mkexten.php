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

if (! $db) {
  include "auth.inc";
}
%>
<FORM METHOD=POST NAME=extenbatch onsubmit="ajaxsubmit(this.name);return false">
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<%
if ((isset($pbxupdate)) && ($addcnt > 0)) {
  include "autoadd.inc";
  $bcolor[0]="list-color1";
  $bcolor[1]="list-color2";
  print "<INPUT TYPE=HIDDEN NAME=disppage VALUE=cdr/authexten.php>";

  print "<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=4>" . _("Created Extensions") . "</TH></TR>";
  print "<TR CLASS=list-color1><TH ALIGN=LEFT WIDTH=20% CLASS=heading-body2>Authorize</TH>";
  print "<TH ALIGN=LEFT CLASS=heading-body2>" . _("Extension") . "</TH><TH ALIGN=LEFT CLASS=heading-body2>";
  print _("TDM Port") . "</TH><TH ALIGN=LEFT CLASS=heading-body2>";
  print _("Configure") . "</TH>";
  print "</TR>\n";
  for ($ncnt=1;$ncnt <= $addcnt;$ncnt++) {
    if ($start == "") {
      $start="1";
    }
    if ($tdmport <= 0) {
      $tdmport="0";
    }
    $phone=createexten("","",$prefix,$start,$tdmport);
    if ($phone != "") {
      print "<TR CLASS=" . $bcolor[$ncnt %2] . ">";
      print "<TD><INPUT TYPE=CHECKBOX  NAME=auth" . $phone . "></TD>";
      print "<TD>Exten " . $phone . " (" . $ncnt . ")</TD>";
      print "<TD>" . $tdmport . "</TD>";
      print "<TD><A HREF=javascript:openextenedit('" . $phone . "')>" . $phone . "</A></TD></TR>";
      if ($tdmport > 0) {
        $tdmport++;
      }
    } else {
      print "<TR CLASS=" . $bcolor[$ncnt %2] . "><TD COLSPAN=4>Error Adding Requested Extension (" . $ncnt . ")</TD></TR>\n";
    }
  }
  print "<TR CLASS=" . $bcolor[$ncnt %2] . "><TH COLSPAN=4 CLASS=heading-body><INPUT TYPE=SUBMIT onclick=this.name='authexten' VALUE=\"Authorise\"></TH></TR>\n";
} else {
%>
<TR CLASS=list-color2>
  <TH CLASS=heading-body COLSPAN=2><%print _("Batch Create Extensions");%></TH>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT WIDTH=50% onmouseover="myHint.show('QS1')" onmouseout="myHint.hide()"><%print _("No. Of Extensions To Create");%></TH>
  <TD WIDTH=50% ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=addcnt MAXLENGTH=2 SIZE=2>
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT WIDTH=50% onmouseover="myHint.show('QS1')" onmouseout="myHint.hide()"><%print _("Starting Extension Create");%></TH>
  <TD WIDTH=50% ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=start MAXLENGTH=2 SIZE=2>
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT WIDTH=50% onmouseover="myHint.show('QS1')" onmouseout="myHint.hide()"><%print _("Starting TDM Port To Assign");%></TH>
  <TD WIDTH=50% ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=tdmport MAXLENGTH=2 SIZE=2>
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT WIDTH=50% onmouseover="myHint.show('QS1')" onmouseout="myHint.hide()"><%print _("Prefix To Create Extensions In");%></TD>
  <TD><SELECT NAME=prefix>
<%
  $qgetdata=pg_query($db,"SELECT key FROM astdb WHERE family='LocalPrefix' AND value='1' ORDER BY key;");
  $dnum=pg_num_rows($qgetdata);
  for($i=0;$i<$dnum;$i++){
    $getdata=pg_fetch_array($qgetdata,$i);
    print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[0] . "</OPTION>";
  }
%>
    </SELECT>
  </TD>
</TR>
<TR CLASS=list-color1>
  <TH COLSPAN=2>
    <INPUT TYPE=SUBMIT onclick=this.name='pbxupdate' VALUE="<%print _("Add/Edit");%>">
    <INPUT TYPE=SUBMIT onclick=this.name='delext' VALUE="<%print _("Delete");%>"></TH>
  </TR>
<%
}
%>
  </TABLE>
  </FORM>
