<%
if (!isset($_SESSION['auth'])) {
  exit;
}
%>
<CENTER>
<FORM METHOD=POST NAME=dnsadminf onsubmit="AJAX.senddata('main-body',this.name,'/cgi-perl/admin/dnsupdate.pl');return false">
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
  <INPUT TYPE=HIDDEN NAME=navbar VALUE="auth/dnsnav.inc">
  <INPUT TYPE=HIDDEN NAME=authtype VALUE="<%print $authtype;%>">
  <INPUT TYPE=HIDDEN NAME=style VALUE="<%print $style;%>">
  <INPUT TYPE=HIDDEN NAME=showmenu VALUE="<%print $showmenu;%>">
  <TR CLASS=list-color2>
  <TH COLSPAN=2 CLASS=heading-body><%print _("Select Domain To Edit (Administrator Mode)");%></TH></TR>      
  <TR CLASS=list-color1><TD onmouseover=myHint.show('DNS1') ONMOUSEOUT=myHint.hide()><%print _("Domain To Modify");%></TD><TD><SELECT NAME=domain>
    <OPTION VALUE=internal><%print _("Internal");%>
    <OPTION VALUE=external><%print _("External");%>
    <OPTION VALUE=smart><%print _("Smart DNS");%>
    <OPTION VALUE=reverse><%print _("Internal Reverse");%>
    <OPTION VALUE=other><%print _("Other");%>
  </SELECT></TR>
  <TR CLASS=list-color2><TD onmouseover=myHint.show('DNS2') ONMOUSEOUT=myHint.hide()><%print _("Domain (If Other/Reverse Above)");%></TD><TD><INPUT TYPE=TEXT NAME=otherdns></TR>
  <TR CLASS=list-color1><TD COLSPAN=2 ALIGN=CENTER><INPUT TYPE=SUBMIT VALUE="<%print _("Modify Domain");%>"></TR>
  </FORM>
</TABLE>
