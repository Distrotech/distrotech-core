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
if (! $db) {
  include "/var/spool/apache/htdocs/cshop/auth.inc";
}
$act[t]="Yes";
$act[f]="No";

if (! $db) {
  print "<link rel=stylesheet type=text/css href=/netsentry.php>";
  include "/var/spool/apache/htdocs/cshop/auth.inc";
}

$exrate=6.2;

$rateq=pg_query($db,"SELECT country.countrycode,countryname,subcode,rate / 10000.00 * $exrate
                       FROM tariffrate LEFT OUTER JOIN
                         country ON (country.countrycode=tariffrate.countrycode) 
                       WHERE tariffcode='$tariffcode'
                       ORDER BY countryname,subcode");


$num=pg_num_rows($rateq); 


$bcolor[0]="list-color1";
$bcolor[1]="list-color2";
%>
<PRE>
<%

for ($i=0; $i < $num; $i++) {
  $r = pg_fetch_row($rateq,$i);
  $rem=$i % 2; 
  for ($j=0;$j < count($r);$j++) {
    if ($j >= "2") {
      $r[$j]=sprintf("%0.4f",$r[$j]);
    }
    print "\" . $r[$j] . "\",";
  }
  print "\n";
}
%>
</PRE>
