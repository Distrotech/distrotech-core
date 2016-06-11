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

if (isset($_POST['addrealm'])) {
  pg_query($db,"INSERT INTO realm (domain,description) VALUES ('" . $_POST["realmdomain"] . "','" . $_POST['realm'] . "')");
?>
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH CLASS=heading-body COLSPAN=2>New Realm Created</TH>
</TR>
<TR CLASS=list-color1>
<TD WIDTH=50%>Realm</TD>
<TD WIDTH=50% ALIGN=LEFT><?php print $_POST['realm'];?></TD>
</TABLE>
<?php
} else {
?>
<FORM METHOD=POST NAME=addrealm onsubmit="ajaxsubmit(this.name);return false">
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Add A New Phone Realm</TH>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50%>Realm Description</TD>
  <TD><INPUT TYPE=TEXT NAME=realm></TD>
<TR CLASS=list-color2>
  <TD>Realm Domain</TD>
  <TD><INPUT TYPE=TEXT NAME=realmdomain></TD>
<TR CLASS=list-color1>
<TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT NAME=addrealm VALUE="Add Realm">
</TABLE>
</FORM><?php
}?>
