<?php
include "intinfo.inc";
if ($time == "") {
  $time=28800;
}
?>

<META HTTP-EQUIV="Refresh" CONTENT="300;url=/auth">
<META HTTP-EQUIV="Cache-Control" content="no-cache">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="Mon, 29 Nov 2004 11:23:11 GMT">

<CENTER>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
  <TR CLASS=list-color2><TH CLASS=heading-body><?php print _("Voip Status Graphs");?></TH></TR>
  <TR CLASS=list-color1><TH CLASS=heading-body2><?php print _("Concurrent Calls");?></TH></TR>
  <tr CLASS=list-color2>
    <td ALIGN=MIDDLE>
      <DIV><A HREF="javascript:openpage('rrdgraph/showperv.php','vstatus')">
      <IMG BORDER=1 ALT="All Calls" SRC="/mrtg/vstatus.php?time=<?php print $time?>"></A></DIV>
    </td>
  </tr>
  <TR CLASS=list-color1><TH CLASS=heading-body2><?php print _("Concurrent TDM Calls");?></TH></TR>
  <tr CLASS=list-color2>
    <td ALIGN=MIDDLE>
      <DIV><A HREF="javascript:openvgraph('tdm')">
      <IMG BORDER=1 ALT="TDM Calls" SRC="/mrtg/vstatus.php?time=<?php print $time?>&type=tdm"></A></DIV>
    </td>
  </tr>
  <TR CLASS=list-color1><TH CLASS=heading-body2><?php print _("Concurrent LCR Calls");?></TH></TR>
  <tr CLASS=list-color2>
    <td ALIGN=MIDDLE>
      <DIV><A HREF="javascript:openvgraph('lcr')">
      <IMG BORDER=1 ALT="LCR Calls" SRC="/mrtg/vstatus.php?time=<?php print $time?>&type=lcr"></A></DIV>
    </td>
  </tr>
  <TR CLASS=list-color1><TH CLASS=heading-body2><?php print _("GSM Router Channels");?></TH></TR>
  <tr CLASS=list-color2>
    <td ALIGN=MIDDLE>
      <DIV><A HREF="javascript:openpage('rrdgraph/showpervg.php','vstatus')">
      <IMG BORDER=1 ALT="GSM Calls" SRC="/mrtg/vgstatus.php?time=<?php print $time?>"></A></DIV>
    </td>
  </tr>
</TABLE>

<FORM NAME=openrrd METHOD=POST>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="rrdgraph/showperv.php">
<INPUT TYPE=HIDDEN NAME=name>
<INPUT TYPE=HIDDEN NAME=max>
</FORM>
