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

$label['signalling']=_("Genral Settings");
$label['echocancel']=_("Echo Cancel No Of Taps");
$label['toneduration']=_("DTMF Settings");
$label['busydetect']=_("Call Progress Settings");
$label['usecallerid']=_("Caller ID Settings");
$label['answeronpolarityswitch']=_("Polarity Reversal Settings");
$label['switchtype']=_("ISDN Setings (Only Used On PRI Trunk)");

$yesno['echocancelwhenbridged']=1;
$yesno['relaxdtmf']=1;
$yesno['busydetect']=1;
$yesno['callprogress']=1;
$yesno['usecallerid']=1;
$yesno['answeronpolarityswitch']=1;
$yesno['hanguponpolarityswitch']=1;
$yesno['overlapdial']=1;
$yesno['usecallingpres']=1;
$yesno['restrictcid']=1;

$descrip['signalling']=_("Trunk Line Type");
$descrip['jitterbuffers']=_("No. Of Jitter Buffers To Use (20ms)");
$descrip['echocancel']=_("Echo Cancel No Of Taps");
$descrip['echotraining']=_("Echo Training Time (ms)");
$descrip['echocancelwhenbridged']=_("Use Echo Cancelation On Bridged Calls");
$descrip['toneduration']=_("Duration Of Tone Sent");
$descrip['relaxdtmf']=_("Relax Dtmf Detection");
$descrip['dtmfduplicatedelay']=_("Delay Before Passing On Duplicate Digit (ms)");
$descrip['busydetect']=_("Hangup Detection");
$descrip['busycount']=_("No. Of Busy Tones Before Hangning Up");
$descrip['busypattern']=_("Cadence Of Busy Signal");
$descrip['callprogress']=_("Use Call Progress Detection");
$descrip['progzone']=_("Call Progress Zone");
$descrip['ringtimeout']=_("Ring Timeout");
$descrip['usecallerid']=_("Use Callerid On Incoming Calls");
$descrip['cidsignalling']=_("Signaling Used By Callerid");
$descrip['cidstart']=_("Callerid Arrives On ..");
$descrip['sendcalleridafter']=_("If Caller Id Arrives On Ring After How Many Rings");
$descrip['answeronpolarityswitch']=_("Answer The Call On Polarity Reversal");
$descrip['polarityonanswerdelay']=_("Polarity Pulse Length (ms)");
$descrip['hanguponpolarityswitch']=_("Hangup On Polarity Reversal After Above Time");
$descrip['switchtype']=_("ISDN PRI Switch Type Used For Group");
$descrip['overlapdial']=_("Use Overlap Dialing");
$descrip['resetinterval']=_("Time To Reset Chanles (s)");
$descrip['priindication']=_("Progress Indication Is ..");
$descrip['usecallingpres']=_("Use Calling Number Presentation");
$descrip['restrictcid']=_("Do Not Send Any Caller ID");

if ((isset($pbxupdate)) && ($update == "seen")) {
  while(list($boolopt,$validbo)=each($yesno)) {
    if (($validbo) && ($$boolopt == "on")) {
      $$boolopt="yes";
    } else if ($validbo) {
      $$boolopt="no";
    }
  }
  if ($resetinterval == 0) {
     $resetinterval="never";
  }
  pg_query("UPDATE zapgroup SET signalling='" . $signalling . "',jitterbuffers='" . $jitterbuffers . "',
                                echocancel='" . $echocancel . "',echotraining='" . $echotraining . "',echocancelwhenbridged='" . $echocancelwhenbridged . "',
                                toneduration='" . $toneduration . "',relaxdtmf='" . $relaxdtmf . "',dtmfduplicatedelay='" . $dtmfduplicatedelay . "',
                                busydetect='" . $busydetect . "',busycount='" . $busycount . "',busypattern='" . $busypattern . "',
                                callprogress='" . $callprogress . "',progzone='" . $progzone . "',ringtimeout='" . $ringtimeout . "',
                                usecallerid='" . $usecallerid . "',cidsignalling='" . $cidsignalling . "',cidstart='" . $cidstart . "',
                                sendcalleridafter='" . $sendcalleridafter . "',answeronpolarityswitch='" . $answeronpolarityswitch . "',
                                polarityonanswerdelay='" . $polarityonanswerdelay . "',hanguponpolarityswitch='" . $hanguponpolarityswitch . "',
                                switchtype='" . $switchtype . "',overlapdial='" . $overlapdial . "',resetinterval='" . $resetinterval . "',
                                priindication='" . $priindication . "',usecallingpres='" . $usecallingpres . "',restrictcid='" . $restrictcid . "'
                            WHERE zaptrunk='" . $zaptrunk . "'");
  unset($signalling);
  unset($cidsignalling);
  unset($cidsignalling);
  unset($cidstart);
  unset($switchtype);
  unset($priindication);
}

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

$cidsignalling['bell']=_("Bell 202");
$cidsignalling['v23']=_("v.23");
$cidsignalling['dtmf']=_("DTMF");

$cidstart['polarity']=_("Polarity Reversal");
$cidstart['ring']=_("Ringing");

$switchtype['ni1']=_("National ISDN 1 (ni1)");
$switchtype['national']=_("National ISDN 2 (national)");
$switchtype['dms100']=_("Nortel DMS100");
$switchtype['4ess']=_("AT&T 4ESS");
$switchtype['5ess']=_("Lucent 5ESS");
$switchtype['euroisdn']=_("Euro ISDN");
$switchtype['qsig']=_("Q.SIG");

$priindication['inband']=_("In Band");
$priindication['outofband']=_("Out Of Band");

$echocancel['no']=_("Off");
$echocancel['32']=_("32");
$echocancel['64']=_("64");
$echocancel['128']=_("128");
$echocancel['256']=_("256");
$echocancel['512']=_("512");


$qgetzdata=pg_query($db,"SELECT signalling,jitterbuffers,echocancel,echotraining,echocancelwhenbridged,toneduration,relaxdtmf,
                                dtmfduplicatedelay,busydetect,busycount,busypattern,callprogress,progzone,ringtimeout,usecallerid,cidsignalling,
                                cidstart,sendcalleridafter,answeronpolarityswitch,polarityonanswerdelay,hanguponpolarityswitch,switchtype,
                                overlapdial,resetinterval,priindication,usecallingpres,restrictcid
                              FROM zapgroup where zaptrunk='" . $zaptrunk . "'");

$zdata=pg_fetch_array($qgetzdata,0,PGSQL_ASSOC);
if ($zdata['resetinterval'] == "never") {
  $zdata['resetinterval']="0";
}
if ($zdata['echocancel'] == "yes") {
  $zdata['echocancel']="128";
}


?>
<INPUT TYPE=HIDDEN NAME=zaptrunk VALUE=<?php print $zaptrunk;?>>
<INPUT TYPE=HIDDEN NAME=update VALUE=seen>
  <TH CLASS=heading-body COLSPAN=2><?php print _("Configuration For Digium Trunk Group") . " " . $zaptrunk;?></TH>
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
    if ((strtolower($zapval) == "yes") || (strtolower($zapval) == "on")) {
      print " CHECKED";
    }
    print ">";
  } else if (is_array($$zapopt)) {
    print "<SELECT NAME=\"" . $zapopt . "\">\n";
    while(list($optval,$optname)=each($$zapopt)) {
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
    <INPUT TYPE=RESET>
    <INPUT TYPE=SUBMIT NAME=pbxupdate VALUE="<?php print _("Save");?>">
  </TD>
</TR>
</TABLE>
</FORM>
