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
include "../cdr/auth.inc";

$cdr=pg_query($db,"SELECT distinct date_part('year',starttime),date_part('month',starttime) from call");

$bcolor[0]="list-color1";
$bcolor[1]="list-color2";

%>
<SCRIPT>
</SCRIPT>
<CENTER>
<FORM METHOD=POST NAME=dlyreport onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=2>Daily Report</TH></TR>
<TR CLASS=list-color1>	
<TH ALIGN=LEFT>Select Month</TH>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="cshop/dlyrep.php">

<TD><SELECT NAME=bmon>
<%

$date = getdate();
$num=pg_num_rows($cdr);
$total="0";
for ($i=0; $i < $num; $i++) 
{
  	$r = pg_fetch_row($cdr,$i);
  	print "<OPTION VALUE=" . $r[0] . ":" . $r[1];
	if ($date['year'] == $r[0] AND $date['mon'] == $r[1]) 
	{
      		print " SELECTED";
    	}
	print ">" . $r[0] . "/" . $r[1] . "\n";
}

$rem=$i % 2; 

%>
</SELECT></TD></TR>
<TR CLASS=list-color2>
<TH ALIGN=LEFT>Select Day</TH>
<TD><SELECT NAME=bday>
<%
$num=pg_num_rows($cdr);
$total="0";
for ($i=1; $i <= 31; $i++) 
{
  	$r = pg_fetch_row($cdr,$i);
  	print "<OPTION VALUE=" . $i;
	if ($date['mday'] == $i) 
	{
      		print " SELECTED";
    	}
	print ">" . $i . "\n";

}
$rem=$i % 2; 
%>
</SELECT></TD></TR>
<TR CLASS=list-color1>
<TD COLSPAN=2 ALIGN=CENTER><INPUT TYPE=SUBMIT VALUE="Show Report"></TD></TR>
</FORM>
</TABLE>
