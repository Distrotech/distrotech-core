<CENTER>
<FORM METHOD=POST ACTION=/cgi-perl/dnsupdate.pl NAME=editdns>
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
  <INPUT TYPE=HIDDEN NAME=domain VALUE=other>
  <INPUT TYPE=HIDDEN NAME=authtype VALUE="<%print $authtype;%>">
  <INPUT TYPE=HIDDEN NAME=disppage VALUE=dnadmin.html>
  <INPUT TYPE=HIDDEN NAME=navbar VALUE=dnsnav.inc>
  <INPUT TYPE=HIDDEN NAME=style VALUE=<%print $style;%>>
  <INPUT TYPE=HIDDEN NAME=showmenu VALUE=<%print $showmenu;%>>
  <tr CLASS=list-color2>
    <TH COLSPAN=2 CLASS=heading-body><%print _("Modify A Hosted Domain");%></TH>
  </TR>
  <tr CLASS=list-color1>
    <td VALIGN=MIDDLE onmouseover=myHint.show('DNSU1') ONMOUSEOUT=myHint.hide()>
      <%print _("Hosted Domain To Modify");%>
    </TD>
    <td VALIGN=MIDDLE>
      <INPUT TYPE=TEXT NAME=otherdns>
    </TD>
  <tr CLASS=list-color2>
    <td VALIGN=MIDDLE onmouseover=myHint.show('DNSU2') ONMOUSEOUT=myHint.hide()>
      <%print _("Domain Password");%>
    </TD>
    <td VALIGN=MIDDLE>
      <INPUT TYPE=PASSWORD NAME=secret><BR>
   </tr>
   <tr CLASS=list-color1>
     <td colspan=2 valign=middle align=center>
       <INPUT TYPE=BUTTON VALUE="<%print _("Modify Domain");%>" ONCLICK=opendnsadmin(this.form)>
      </td>
  </tr>
</TABLE>
</FORM>
