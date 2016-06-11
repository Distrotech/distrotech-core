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

if ($_SESSION['resellerid'] != 0) {
  exit;
}

if ((isset($_POST['addcredit'])) && ($_POST['exchangerate'] > 0)){
  $curcred=pg_query($db,"SELECT credit,exchangerate FROM reseller WHERE id = 0");
  $credinfo=pg_fetch_array($curcred,0);
  $_SESSION['rexrate']=($credinfo[0]*$credinfo[1]+$_POST['credit']*$_POST['exchangerate']*10000)/($credinfo[0]+$_POST['credit']*10000);

  $_POST['credit']=$credinfo[0]+$_POST['credit']*10000;
  pg_query($db,"UPDATE reseller SET credit=" . $_POST['credit'] . ",exchangerate=" . $_SESSION['rexrate'] . " WHERE id= 0 ");
  pg_query($db,"UPDATE reseller SET resetcredit=" . $_POST['credit'] . ",exchangerate=" . $_SESSION['rexrate'] . " WHERE id= 0 ");
  $_POST['credit']=sprintf("%0.2f",$_POST['credit']/10000);
?>
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Credit Added</TH>
</TR>
<TR CLASS=list-color1>
<TD WIDTH=50%>Credit Avaialable</TD>
<TD WIDTH=50% ALIGN=LEFT><?php print $_POST['credit'];?></TD>
<TR CLASS=list-color2>
<TD WIDTH=50%>Exchange Rate</TD>
<TD WIDTH=50% ALIGN=LEFT><?php print $_POST['exchangerate'];?></TD>
</TR>
</TABLE>
<?php
} else {
?>
<CENTER>
<FORM METHOD=POST NAME=acredit onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Add Purchaced Credit</TH>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50%>Credit Purchaced ($)</TD>
  <TD WIDTH=50% ALIGN=LEFT><INPUT TYPE=TEXT NAME=credit VALUE="">
</TD></TR>
<TR CLASS=list-color2>
  <TD>Exchange Rate</TD>
  <TD><INPUT TYPE=TEXT NAME=exchangerate VALUE="<?php print $_SESSION['rexrate'];?>"></TD>
<TR CLASS=list-color1>
<TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT NAME=addcredit VALUE="Add Credit"></TD>
</TABLE>
</FORM><?php
}?>
