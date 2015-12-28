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
if (! $db) {
  include "/var/spool/apache/htdocs/cshop/auth.inc";
}

?>
<FORM METHOD=POST NAME=adduf onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=disppage VALUE=cshop/editvirt.php>
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Create A New Virtual Company</TH>
</TR>
<TR CLASS=list-color1>
  <TD>Company Name</TD>
  <TD><INPUT TYPE=TEXT NAME=description></TD></TR>
<TR CLASS=list-color2>
  <TD>Contact Name</TD>
  <TD><INPUT TYPE=TEXT NAME=contact></TD></TR>
<TR CLASS=list-color1>
  <TD>Email Contact Address</TD>
  <TD><INPUT TYPE=TEXT NAME=email></TD></TR>
<TR CLASS=list-color2>
  <TD>Contact Number</TD>
  <TD><INPUT TYPE=TEXT NAME=altnumber></TD></TR>
<TR CLASS=list-color1>
<TD COLSPAN=2 ALIGN=MIDDLE><INPUT TYPE=SUBMIT NAME=addcompany VALUE="Add Company"></TD></TR>
</TABLE>
</FORM><?php
?>
