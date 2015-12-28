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
$tariff=pg_query($db,"SELECT tariffcode,tariffname
                     FROM tariff WHERE tariffcode LIKE '" . $_SESSION['resellerid'] . "-%' ORDER BY tariffname");
$num=pg_num_rows($tariff); 
$_SESSION['disppage']="cshop/getcountry.php";
?>
<CENTER>
<FORM NAME=rateform METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% CELLSPACING=0 CELLPADDING=0>
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Edit/Create A Rate Sheet</TH>
</TR>
<TR CLASS=list-color1>
<TD ALIGN=LEFT WIDTH=50%>Select Tariff Plan To Alter/Delete</TD>
<TH ALIGN=LEFT VALIGN=MIDDLE>
<SELECT NAME=tariffcode>
<?php
if ($_SESSION['resellerid'] == "0") {
  print "<OPTION VALUE=A>Buy Rate</OPTION>";
}
for ($i=0; $i < $num; $i++) {
  $r = pg_fetch_row($tariff,$i);
  $r[0]=substr($r[0],strpos($r[0],"-")+1);
  print  "<OPTION VALUE=" . $r[0];
  if ($r[0] == $_SESSION['tariffcode']) {
    print " SELECTED";
  }
  print ">" . $r[1] . "</OPTION>\n";
}
?>
</SELECT></TH></TR>
<TR CLASS=list-color2>
  <TH COLSPAN=2 CLASS=heading-body2>Or Create New Plan Bellow ...</TH>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT>New Tariff Plan Name</TD>
  <TD><INPUT TYPE=TEXT NAME=newplan></TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT>Ammount To Add To Buy Rate (USD c/m)</TD>
  <TD><INPUT TYPE=TEXT NAME=addrate></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT>Margin To Add To Cost (%)</TD>
  <TD><INPUT TYPE=TEXT NAME=murate></TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT>Minimum Billed Rate<BR>(R/m EX. VAT At Current Ex. Rate <?php print $_SESSION['rexrate'];?>)</TD>
  <TD><INPUT TYPE=TEXT NAME=minrate></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT>VAT/TAX Rate (%)</TD>
  <TD><INPUT TYPE=TEXT NAME=tax></TD>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT>List Prices VAT/TAX Inclusive</TD>
  <TD><INPUT TYPE=CHECKBOX NAME=showtax></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT>Overwrite The Plan Selected Above</TD>
  <TD><INPUT TYPE=CHECKBOX NAME=overwrite></TD>
</TR>
<TR CLASS=list-color2><TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT VALUE="Alter/Create" NAME=mkplan>
<INPUT TYPE=BUTTON ONCLICK="checkrate()" VALUE="Delete"></TD></TR>
</FORM>
</TABLE>
<SCRIPT>
function checkrate() {
  if (document.rateform.tariffcode.value == 'A') {
    alert("You Cannot Delete The Master Rates");
  } else {
    if (confirm("Are You Sure You Want To Delete This Rate ?")) {
      document.rateform.submit();
    }
  }
}
</SCRIPT>
