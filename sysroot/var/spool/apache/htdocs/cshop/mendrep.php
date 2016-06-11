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

include_once "/var/spool/apache/htdocs/cdr/func.inc";

$month=preg_split("/\//",$date);

?>
<CENTER>
<FORM METHOD=POST NAME=mrepform onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=2>Reseller Report</TH></TR>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="cshop/csallrep.php">

<?php
  $getcdr=pg_query($db,"SELECT date_part('month',calldate) AS month,
                               date_part('year',calldate) AS year
                             from cdr where 
                               userfield != '' AND dstchannel != '' AND disposition='ANSWERED' AND dst != 's' AND
                               (length(accountcode) >= 4 OR accountcode = '')
                             group by year,month
                             order by year,month");

  $amon=array();
  for($i=0;$i<pg_num_rows($getcdr);$i++) {
    $r = pg_fetch_row($getcdr, $i);
    array_push($amon,array('year'=>$r[1],'mon'=>$r[0]));
  }
?>
<TR CLASS=list-color1><TD WIDTH=50%>
  From Month
</TD><TD WIDTH=50%>
<SELECT NAME=dom>
<?php
  for($dcnt=1;$dcnt <= 31;$dcnt++) {
    print "<OPTION VALUE=" . $dcnt . ">" . $dcnt . "\n";
  }
?>
</SELECT>
<SELECT NAME=date><?php
  for($rcnt=0;$rcnt < count($amon);$rcnt++) {
    print "<OPTION VALUE=\"" . $amon[$rcnt]['mon'] . "/" . $amon[$rcnt]['year'] . "\"";
    if ((($dtime['year'] == $amon[$rcnt]['year']) && ($dtime['mon'] == $amon[$rcnt]['mon']) && (! isset($date))) ||
        (($month[0] == $amon[$rcnt]['year']) && ($amon[$rcnt]['mon'] == $r[1]) && (isset($date)))) {
      print " SELECTED";
      $mqset="1";
    } else if (($dtime['year'] == $amon[$rcnt]['year']) && ($dtime['mon'] == $amon[$rcnt]['mon']) && ($mqset != "1")) {
      print " SELECTED";
    }
    print ">" . $amon[$rcnt]['mon'] . "/" . $amon[$rcnt]['year'] . "\n"; 
  }
?>
</SELECT></TD></TR>
<TR CLASS=list-color2><TD WIDTH=50%>
  To Month
</TD><TD WIDTH=50%>
<SELECT NAME=dom2>
<?php
  for($dcnt=31;$dcnt > 0;$dcnt--) {
    print "<OPTION VALUE=" . $dcnt . ">" . $dcnt . "\n";
  }
?>
</SELECT>
<SELECT NAME=date2><?php
  for($rcnt=0;$rcnt < count($amon);$rcnt++) {
    print "<OPTION VALUE=\"" . $amon[$rcnt]['mon'] . "/" . $amon[$rcnt]['year'] . "\"";
    if ((($dtime['year'] == $amon[$rcnt]['year']) && ($dtime['mon'] == $amon[$rcnt]['mon']) && (! isset($date))) ||
        (($month[0] == $amon[$rcnt]['year']) && ($amon[$rcnt]['mon'] == $r[1]) && (isset($date)))) {
      print " SELECTED";
      $mqset="1";
    } else if (($dtime['year'] == $amon[$rcnt]['year']) && ($dtime['mon'] == $amon[$rcnt]['mon']) && ($mqset != "1")) {
      print " SELECTED";
    }
    print ">" . $amon[$rcnt]['mon'] . "/" . $amon[$rcnt]['year'] . "\n"; 
  }
?>
</SELECT></TD></TR>

<TR CLASS=list-color1><TD WIDTH=50%>
  Sort By
</TD><TD WIDTH=50%>
<SELECT NAME=sortby>
  <OPTION VALUE="cost">Cost</OPTION>
  <OPTION VALUE="exten">Extension</OPTION>
  <OPTION VALUE="name">Name</OPTION>
</SELECT></TD></TR>

<TR CLASS=list-color2><TD>Sort Decending</TD><TD><INPUT TYPE=CHECKBOX NAME=sortdown CHECKED></TD></TR>
<TR CLASS=list-color1><TD COLSPAN=2 ALIGN=MIDDLE>
<INPUT TYPE=SUBMIT>
</TD></TR>
</FORM>
</TABLE>
