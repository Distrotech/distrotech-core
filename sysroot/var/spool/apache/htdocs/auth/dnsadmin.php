<?php
if (!isset($_SESSION['auth'])) {
  exit;
}
?>
<CENTER>
<FORM METHOD=POST NAME=dnsadminf onsubmit="AJAX.senddata('main-body',this.name,'/cgi-perl/admin/dnsupdate.pl');return false">
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
  <INPUT TYPE=HIDDEN NAME=navbar VALUE="auth/dnsnav.inc">
  <INPUT TYPE=HIDDEN NAME=authtype VALUE="<?php print $authtype;?>">
  <INPUT TYPE=HIDDEN NAME=style VALUE="<?php print $style;?>">
  <INPUT TYPE=HIDDEN NAME=showmenu VALUE="<?php print $showmenu;?>">
  <TR CLASS=list-color2>
  <TH COLSPAN=2 CLASS=heading-body><?php print _("Select Domain To Edit (Administrator Mode)");?></TH></TR>      
  <TR CLASS=list-color1><TD onmouseover=myHint.show('DNS1') ONMOUSEOUT=myHint.hide()><?php print _("Domain To Modify");?></TD><TD><SELECT NAME=domain>
    <OPTION VALUE=internal><?php print _("Internal");?>
    <OPTION VALUE=external><?php print _("External");?>
    <OPTION VALUE=smart><?php print _("Smart DNS");?>
    <OPTION VALUE=reverse><?php print _("Internal Reverse");?>
    <OPTION VALUE=other><?php print _("Other");?>
  </SELECT></TR>
  <TR CLASS=list-color2><TD onmouseover=myHint.show('DNS2') ONMOUSEOUT=myHint.hide()><?php print _("Domain (If Other/Reverse Above)");?></TD><TD><INPUT TYPE=TEXT NAME=otherdns></TR>
  <TR CLASS=list-color1><TD COLSPAN=2 ALIGN=CENTER><INPUT TYPE=SUBMIT VALUE="<?php print _("Modify Domain");?>"></TR>
  </FORM>
</TABLE>
