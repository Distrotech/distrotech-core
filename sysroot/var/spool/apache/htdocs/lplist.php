<?php

if ($_POST['print'] < 2) {
?>
<CENTER>
<CENTER>
<FORM NAME=ppage METHOD=POST>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<?php print $_SESSION['disppage'];?>">
<INPUT TYPE=HIDDEN NAME=print>
</FORM>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<?php
}
$tariffq1=pg_query($db,"SELECT description,peakstart,peakend,peakmin,peaksec,peakperiod,offpeakmin,offpeaksec,offpeakperiod,index,distance,match,peakdays from localrates where validfrom < now() AND validto > now() AND peakstart != peakend  AND index > 0 order by peakmin");
$heading="<TH CLASS=heading-body2>" . _("Min Charge") . "</TH><TH CLASS=heading-body2>" . _("Per Sec.") . "</TH><TH CLASS=heading-body2>" . _("Period (s)") . "</TH><TH CLASS=heading-body2>" . _("Min Charge") . "</TH><TH CLASS=heading-body2>" . _("Per Sec.") . "</TH><TH CLASS=heading-body2>" . _("Period (s)") . "</TH>";
$heading2="<TH CLASS=heading-body2 COLSPAN=2>" . _("Min Charge") . "</TH><TH CLASS=heading-body2 COLSPAN=2>" . _("Per Sec.") . "</TH><TH CLASS=heading-body2 COLSPAN=2>" . _("Period (s)") . "</TH>";

if ($_POST['print'] == 2) {
  $heading=array("ID","Discription","Dist.","Match","Days","Peak Start","Min.","Per s","Unit","Off Peak","Min.","Per s","Unit");
  $dataout="\"" . str_replace(array("\"","--!@#--"),array("\"\"","\",\""),implode("--!@#--",$heading)). "\"\n";
  print $dataout;
}

$rcol=1;
for($tcnt=0;$tcnt<pg_num_rows($tariffq1);$tcnt++) {
  $r=pg_fetch_array($tariffq1,$tcnt);
  if ($_POST['print'] < 2) {
    print "<TR CLASS=list-color" . (($rcol % 2 )+1) . "><TH COLSPAN=6 CLASS=heading-body>" . _($r[0]) . "</TH></TR>\n";
    $rcol++;
    print "<TR CLASS=list-color" . (($rcol % 2 )+1) . "><TH COLSPAN=3 CLASS=option-red>" . $r[1] . " - " . $r[2] . " Mon-Fri</TH><TH COLSPAN=3  CLASS=option-green>Off Peak</TH></TR>\n";
    $rcol++;
    print "<TR CLASS=list-color" . (($rcol % 2 )+1) . ">" . $heading . "</TR>\n";
    $rcol++;
    print "<TR CLASS=list-color" . (($rcol % 2 )+1) . "><TD ALIGN=MIDDLE>" . sprintf("R%0.4f",$r[3]/100000) . "</TD><TD ALIGN=MIDDLE>";
    print sprintf("R%0.4f",$r[4]/100000) . "</TD><TD ALIGN=MIDDLE>" . $r[5] . "</TD><TD ALIGN=MIDDLE>";
    print sprintf("R%0.4f",$r[6]/100000) . "</TD><TD ALIGN=MIDDLE>" . sprintf("R%0.4f",$r[7]/100000) . "</TD><TD ALIGN=MIDDLE>" . $r[8] . "</TD></TR>\n";
    $rcol++;
  } else {
    $data=array($r[9],_($r[0]),$r[10],$r[11],$r[12],$r[1],sprintf("%0.4f",$r[3]/100000),sprintf("%0.4f",$r[4]/100000),$r[5],$r[2],sprintf("%0.4f",$r[6]/100000),sprintf("%0.4f",$r[7]/100000),$r[8]);
    $dataout="\"" . str_replace(array("\"","--!@#--"),array("\"\"","\",\""),implode("--!@#--",$data)). "\"\n";
    print $dataout;
  }
}

$tariffq1=pg_query($db,"SELECT description,peakstart,peakend,peakmin,peaksec,peakperiod,index,distance,match,peakdays from localrates where validfrom < now() AND validto > now() AND peakstart = peakend order 
by peakmin");
for($tcnt=0;$tcnt<pg_num_rows($tariffq1);$tcnt++) {
  $r=pg_fetch_array($tariffq1,$tcnt);
  if ($_POST['print'] < 2) {
    print "<TR CLASS=list-color" . (($rcol % 2 )+1);
    if ($tcnt == "0") {
      print " STYLE=\"page-break-before: always\"";
    }
    print "><TH COLSPAN=6 CLASS=heading-body>" . $r[0] . "</TH></TR>\n";
    $rcol++;
    print "<TR CLASS=list-color" . (($rcol % 2 )+1) . ">" . $heading2 . "</TR>\n";
    $rcol++;
    print "<TR CLASS=list-color" . (($rcol % 2 )+1) . "><TD COLSPAN=2 ALIGN=MIDDLE>" . sprintf("R%0.4f",$r[3]/100000) . "</TD><TD ALIGN=MIDDLE COLSPAN=2>";
    print sprintf("R%0.4f",$r[4]/100000) . "</TD><TD COLSPAN=2 ALIGN=MIDDLE>" . $r[5] . "</TD></TR>\n";
    $rcol++;
  } else {
    $data=array($r[6],_($r[0]),$r[7],$r[8],$r[9],$r[1],sprintf("%0.4f",$r[3]/100000),sprintf("%0.4f",$r[4]/100000),$r[5]);
    $dataout="\"" . str_replace(array("\"","--!@#--"),array("\"\"","\",\""),implode("--!@#--",$data)). "\"\n";
    print $dataout;
  }
}

if ($_POST['print'] < "1") {
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=6 CLASS=heading-body>";
  print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.ppage)\">";
  print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("CSV Export") . "\" ONCLICK=\"csvpage(document.ppage)\">";
  print "</TH></TR>";
}
if ($_POST['print'] < 2) {
?>
</TABLE>
<?php
}
?>
