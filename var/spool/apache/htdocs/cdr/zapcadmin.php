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


$signalling['fxs_ks']=_("Kewl Start (FXO)");
$signalling['fxo_ks']=_("Kewl Start (FXS)");
$signalling['fxs_ls']=_("Loop Start (FXO)");
$signalling['fxo_ls']=_("Loop Start (FXS)");
$signalling['fxs_gs']=_("Ground Start");
$signalling['pri_cpe']=_("PRI (CPE)");
$signalling['pri_net']=_("PRI (NET)");
$signalling['bri_cpe_ptmp']=_("BRI (CPE)");
$signalling['bri_net_ptmp']=_("BRI (NET)");
$signalling['mfcr2']=_("MFC/R2");

$grplst=array();

$grplist=pg_query($db,"SELECT zaptrunk,signalling from zapgroup order by zaptrunk");
for($gcnt=0;$gcnt < pg_num_rows($grplist);$gcnt++) {
  $zgrp=pg_fetch_row($grplist,$gcnt);
  $grplst[$zgrp[0]]=$zgrp[0] . " - (" . $signalling[$zgrp[1]] . ")";
}
$grplst[0]=_("Not Used (FXO)");

function tgrplist($defin,$selname) {
  global $grplst;
  print "<SELECT NAME=" . $selname . ">";
  $grplist=$grplst;
  while(list($tgroup,$tgname) = each($grplist)) {
    print "<OPTION VALUE=" . $tgroup;
    if ($tgroup == $defin) {
      print " SELECTED";
    }
    print ">" . $tgname . "</OPTION>";
  }
  print "</SELECT>";
}

if ((isset($pbxupdate)) && ($newchan != "") && ($newrxgain != "") && ($newtxgain != "")) {

  pg_query($db,"INSERT INTO zapchan (zaptrunk,channel,rxgain,txgain) VALUES ('" . $zaptrunk . "','" . $newchan . "','" . $newrxgain . "','" . $newtxgain . "')");
} else if (($chanup != "") && (($update == "rxgain") || ($update == "channel") || ($update == "txgain"))) {
  pg_query($db,"UPDATE zapchan SET " . $update . "='" . $chandata . "' WHERE id=" . $chanup);
}

%>
<INPUT TYPE=HIDDEN NAME=chandata>
<INPUT TYPE=HIDDEN NAME=chanup>
<INPUT TYPE=HIDDEN NAME=update>

  <TH CLASS=heading-body COLSPAN=5><%print _("Channels For TDM Groups")%></TH>
</TR>
<TR CLASS=list-color1>
  <TH CLASS=heading-body2><%print _("Delete");%></TH>
  <TH CLASS=heading-body2><%print _("Channel(s)");%></TH>
  <TH CLASS=heading-body2><%print _("RX Gain");%></TH>
  <TH CLASS=heading-body2><%print _("TX Gain");%></TH>
  <TH CLASS=heading-body2><%print _("Trunk");%></TH>
</TR>
<%

$qgetzdata=pg_query($db,"SELECT channel,rxgain,txgain,zaptrunk,id FROM zapchan  ORDER BY zaptrunk,channel");
$col=1;
for($ccnt=0;$ccnt < pg_num_rows($qgetzdata);$ccnt++) {
  $zdata=pg_fetch_array($qgetzdata,$ccnt);
  $mtest="move" . $zdata[4];
  $dtest="del" . $zdata[4];
  if ((isset($pbxupdate)) && ($$dtest == "on")) {
    pg_query($db,"DELETE FROM zapchan WHERE id=" . $zdata[4]); 
    continue;
  } else if ((isset($pbxupdate)) && ($$mtest != $zdata[3])) {
    pg_query($db,"UPDATE zapchan SET zaptrunk=" . $$mtest . " WHERE id=" . $zdata[4]); 
    $zdata[3]=$$mtest;
  }
    
  print "<TR CLASS=list-color" . (($col % 2) +1) . ">\n  <TD ALIGN=MIDDLE>\n    ";
  print "<INPUT TYPE=CHECKBOX NAME=del" . $zdata[4] . ">";
  print "\n  </TD>\n  <TD>\n    ";
  print "<A HREF=\"javascript:adjchan('" . $zdata[4] . "','channel')\">" . $zdata[0] . "</A>";
  print "\n  </TD>\n  <TD>\n    ";
  print "<A HREF=\"javascript:adjchan('" . $zdata[4] . "','rxgain')\">" . $zdata[1] . "</A>";
  print "\n  </TD>\n  <TD>\n    ";
  print "<A HREF=\"javascript:adjchan('" . $zdata[4] . "','txgain')\">" . $zdata[2] . "</A>";
  print "\n  </TD>\n  <TD>\n    ";
  tgrplist($zdata[3],"move" . $zdata[4]);
  print "\n  </TD>\n</TR>";
  $col++;
}
%>
<TR CLASS=list-color<%print (($col % 2) +1);%>>
  <TH CLASS=heading-body2  onmouseover="myHint.show('Z3')" onmouseout="myHint.hide()">
    <%print _("Add New Channel");%>
  </TH>
  <TD ALIGN=MIDDLE>
    <INPUT TYPE=TEXT NAME=newchan>
  </TD>
  <TD ALIGN=MIDDLE>
    <INPUT TYPE=TEXT NAME=newrxgain VALUE=0>
  </TD>
  <TD ALIGN=MIDDLE>
    <INPUT TYPE=TEXT NAME=newtxgain VALUE=0>
  </TD>
  <TD>
<%
    tgrplist("","zaptrunk");
%>
  </TD>
</TR>
<%$col++%>
<TR CLASS=list-color<%print (($col % 2) +1);%>>
  <TD ALIGN=MIDDLE COLSPAN=5>
    <INPUT TYPE=SUBMIT NAME=pbxupdate VALUE="<%print _("Update");%>">
  </TD>
</TR>
</TABLE>
</FORM>
