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
  <TR CLASS=list-color2><TH CLASS=heading-body><?php print _("System Status Graphs");?></TH></TR>
  <TR CLASS=list-color1><TH CLASS=heading-body2><?php print _("Firewall Violations");?></TH></TR>
  <tr CLASS=list-color2>
    <td ALIGN=MIDDLE>
      <DIV><A HREF="javascript:openpage('rrdgraph/showperlog.php','status')">
      <IMG BORDER=1 ALT="Firewall Exceptions" SRC="/mrtg/logcheck.php?time=<?php print $time?>"></A></DIV>
    </td>
  </tr>
<?php

  while (list($int,$speed)=each($ints)) {?>
    <TR CLASS=list-color1><TH CLASS=heading-body2><?php print _("Bandwidth Priority Usage Outgoing Interface");?> <?php print $int;?></TH></TR>
    <tr CLASS=list-color2>
      <td ALIGN=MIDDLE>
        <A HREF="javascript:openrrdgraph('<?php print $int;?>','<?php print $speed;?>')">
        <IMG BORDER=1 ALT="Bandwidth Priority" SRC="/mrtg/bwprio.php?gname=<?php print $int;?>&max=<?php print $speed;?>&time=<?php print $time?>"></A>
      </td>
    </tr>
    <TR CLASS=list-color1><TH CLASS=heading-body2><?php print _("Bandwidth Priority Usage Incoming Interface");?> imq<?php print $imq[$int]?></TH></TR>
    <tr CLASS=list-color2>
      <td ALIGN=MIDDLE>
        <A HREF="javascript:openrrdgraph('imq<?php print $imq[$int];?>','<?php print $speed;?>')">
        <IMG BORDER=1 ALT="Bandwidth Priority" SRC="/mrtg/bwprio.php?gname=imq<?php print $imq[$int];?>&max=<?php print $imqmax[$imq[$int]];?>&time=<?php print $time;?>"></A></DIV>
      </td>
    </tr><?php
  }?>
</TABLE>

<FORM NAME=openrrd METHOD=POST>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="rrdgraph/showper.php">
<INPUT TYPE=HIDDEN NAME=name>
<INPUT TYPE=HIDDEN NAME=max>
</FORM>
