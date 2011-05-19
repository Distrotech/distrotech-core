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
%>
<CENTER>
<FORM>
<TABLE border=0 width=90% cellspacing=0 cellpadding=0>
  <TR CLASS=list-color2>
    <TH CLASS=heading-body>
      Testing Script
    </TH>
  </TR>
  <TR CLASS=list-color1>
    <TD><DIV CLASS=ccscript>
<%
include "scriptp.inc";
print getscripthtml($_POST['mmap'],($_POST['utype'] == "true")?"t":"f");
%>
    </DIV></TD>
  </TR>
  <TR CLASS=list-color2>
    <TD ALIGN=MIDDLE>
      <INPUT TYPE=BUTTON ONCLICK=dumpform('') VALUE="Show Values">
    </TH>
  </TR>
</TABLE>
</FORM>
