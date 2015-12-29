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

$descrip['timingsource']=_("Timing Priority");
$descrip['lbo']=_("Line Build Out");
$descrip['framing']=_("Framing");
$descrip['coding']=_("Coding");
$descrip['crc4']=_("CRC4 Checking (E1 Only)");
$descrip['yalarm']=_("Transmit Yellow Alarm");
$descrip['hwdchan']=_("Hardware D Channel");
$descrip['dchannel']=_("D Channel If Required");

$yesno['crc4']=1;
$yesno['hwdchan']=1;
$yesno['yalarm']=1;

if ((isset($pbxupdate)) && ($update == "seen")) {
  while(list($boolopt,$validbo)=each($yesno)) {
    if (($validbo) && (${$boolopt} == "on")) {
      ${$boolopt}="t";
    } else if ($validbo) {
      ${$boolopt}="f";
    }
  }
  if ($dchannel == "") {
    $dchannel="null";
  }
  pg_query($db,"UPDATE zapspan SET dchannel=" . $dchannel . ",timingsource='" . $timingsource . "',lbo='" . $lbo . "',framing='" . $framing . "',
                                   coding='" . $coding . "',crc4='" . $crc4 . "',hwdchan='" . $hwdchan . "',yalarm='" . $yalarm . "' WHERE spannum='" . $zapspan . "'");
  unset($framing);
  unset($coding);
  unset($lbo);
}

$qgetzdata=pg_query($db,"SELECT timingsource,lbo,framing,coding,crc4,yalarm,hwdchan,dchannel FROM zapspan where spannum='" . $zapspan . "'");
$zdata=pg_fetch_array($qgetzdata,0,PGSQL_ASSOC);

$framing['d4']="d4/sf/superframe";
$framing['esf']="esf";
$framing['cas']="cas";
$framing['ccs']="ccs";

$coding['ami']="ami";
$coding['b8zs']="b8zs";
$coding['hdb3']="hdb3";

$lbo['0']="0 db (CSU) / 0-133 feet (DSX-1)";
$lbo['1']="133-266 feet (DSX-1)";
$lbo['2']="266-399 feet (DSX-1)";
$lbo['3']="399-533 feet (DSX-1)";
$lbo['4']="533-655 feet (DSX-1)";
$lbo['5']="-7.5db (CSU)";
$lbo['6']="-15db (CSU)";
$lbo['7']="-22.5db (CSU)";

?>
<INPUT TYPE=HIDDEN NAME=zapspan VALUE=<?php print $zapspan;?>>
<INPUT TYPE=HIDDEN NAME=update VALUE=seen>
  <TH CLASS=heading-body COLSPAN=2><?php print _("Configuration For Span") . " " . $zapspan?></TH>
</TR>
<?php
$col=0;
while(list($zapopt,$zapval)=each($zdata)) {
  if ($label[$zapopt] != "") {
    print "<TR CLASS=list-color" . (($col % 2) +1) . "><TH COLSPAN=2 CLASS=heading-body2>" . $label[$zapopt] . "</TH></TR>\n";
    $col++;
  }
  print "<TR CLASS=list-color" . (($col % 2) +1) . ">\n  <TD WIDTH=50% onmouseover=\"myHint.show('" . $zapopt . "')\" onmouseout=\"myHint.hide()\">\n    ";
  if ($descrip[$zapopt] != "") {
    print $descrip[$zapopt];
  } else {
    print $zapopt;
  }
  print "  </TD>\n  <TD>\n    ";
  if ($yesno[$zapopt]) {
    print "<INPUT TYPE=CHECKBOX NAME=\"" . $zapopt . "\"";
    if ((strtolower($zapval) == "t") || (strtolower($zapval) == "on")) {
      print " CHECKED";
    }
    print ">";
  } else if (is_array(${$zapopt})) {
    print "<SELECT NAME=\"" . $zapopt . "\">\n";
    while(list($optval,$optname)=each(${$zapopt})) {
      print "      <OPTION VALUE=\"" . $optval . "\"";
      if ($zapval == $optval) {
        print " SELECTED";
      }
      print ">" . $optname . "</OPTION>\n";
    }
    print "    </SELECT>";
  } else {
    print "<INPUT TYPE=TEXT NAME=\"" . $zapopt . "\" VALUE=\"" . $zapval . "\">";
  }
  print "\n  </TD>\n</TR>";
  $col++;
}
?>
<TR CLASS=list-color<?php print (($col % 2) +1);?>>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=SUBMIT onclick=this.name='pbxupdate' VALUE="<?php print _("Save");?>">
    <INPUT TYPE=RESET>
    <INPUT TYPE=SUBMIT onclick=this.name='pbxdelete' VALUE="<?php print _("Delete");?>">
  </TD>
</TR>
</TABLE>
</FORM>
