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
include "../cdr/auth.inc";

$cdrq="SELECT description,id,credit,resetcredit FROM reseller WHERE owner = " . $_SESSION['resellerid'] . "";

$cdrqusr="SELECT fullname,name,credit,resetcredit FROM users WHERE usertype = 1 AND agentid = " . $_SESSION['resellerid'] . " ORDER BY fullname;";

//print $cdrq . "<P>";
$cdr=pg_query($db,$cdrq);
$cdrusr=pg_query($db,$cdrqusr);

$bcolor[0]="list-color1";
$bcolor[1]="list-color2";

?>
<link rel=stylesheet type=text/css href=/style.php>
<DIV CLASS=popup>
<CENTER>
<form action='creditreset.php' method='post'>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH ALIGN=LEFT><FONT SIZE=1>Reseller Name</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Reseller ID</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Credit Left</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Credit assigned</TH>
<TH ALIGN=LEFT><FONT SIZE=1></TH>
<?php

$num=pg_num_rows($cdr);
for ($i=0; $i < $num; $i++) {
  $r = pg_fetch_row($cdr,$i);
  $rem=$i % 2; 
  print "<TR CLASS=" . $bcolor[$rem] . ">";

  for ($j=0;$j < count($r);$j++) {
    if ($j == "2" || $j == "3") {
      $r[$j]=sprintf("%0.2f",($r[$j])/10000);
    }
    print  "<TD><FONT SIZE=1>" . $r[$j] . "</TD>";
  }
  if ($r[1] != $_SESSION['resellerid'])
  {
     print "<td><INPUT TYPE=CHECKBOX NAME=" .  $r[1] . "></TD>";
  } else {
     print "<td></TD>";
  }

  print "</TR>\n";
}
  $rem=$i % 2; 
  print "<TR CLASS=" . $bcolor[$rem] . ">";
?>
<TH ALIGN=LEFT><FONT SIZE=1>Users Name</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Users ID</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Credit Left</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Credit assigned</TH>
<TH ALIGN=LEFT><FONT SIZE=1></TH>
<?php

$num=pg_num_rows($cdrusr);
for ($i=0; $i < $num; $i++) {
  $r = pg_fetch_row($cdrusr,$i);
  if ($rem == 0)
  {
    $rem = 1;
  }
  else 
  {
    $rem = 0;
  }
  print "<TR CLASS=" . $bcolor[$rem] . ">";

  for ($j=0;$j < count($r);$j++) {
      if ($j == "2" || $j == "3") {
      $r[$j]=sprintf("%0.2f",($r[$j])/10000);
    }
    print  "<TD><FONT SIZE=1>" . $r[$j] . "</TD>";
  }
  print "<td><INPUT TYPE=CHECKBOX NAME=" .  $r[1] . "></TD>";
  print "</TR>\n";
}
if ($rem == 0)
  {
    $rem = 1;
  }  
  else
  {
    $rem = 0;
  }
?>
<TR CLASS="<?php print $bcolor[$rem] ?>">
	<TH COLSPAN=5 CLASS=heading-body>
		<INPUT TYPE=SUBMIT NAME=Submit VALUE=Submit>
	</th>
</tr>
</TABLE>
</form>
</DIV>
