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

  $month=preg_split("/\//",$date);
  include_once "uauth.inc";

  if ($ADMIN_USER != "admin") {
    return;
  }

  $getcdrq="SELECT date_part('month',calldate) AS month,
                               date_part('year',calldate) AS year
                             from cdr left outer join astdb as bgrp on (cdr.accountcode = bgrp.family and bgrp.key = 'BGRP')
                             where userfield != '' AND dstchannel != '' AND disposition='ANSWERED' AND 
                               length(accountcode) = 4";
  if (($TMS_USER == 1) && ($SUPER_USER != 1)) {
    $getcdrq.=" AND " . $clogacl;
  }
  $getcdrq.=" group by year,month order by year,month";
  $getcdr=pg_query($db,$getcdrq);

  $amon=array();
  for($i=0;$i < pg_fetch_row($getcdr);$i++) {
    $r=pg_fetch_row($getcdr,$i);
    if (($r[0] != "") && ($r[1] != "")) {
      array_push($amon,array('year'=>$r[1],'mon'=>$r[0]));
    }
  }
  $curdate=getdate();
  array_push($amon,array('year'=>$curdate['year'],'mon'=>$curdate['mon']+1));
?>
<CENTER>
<FORM METHOD=POST NAME=extenrepf onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=2>Extension Report</TH></TR>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="cdr/dextenr.php">
<TR CLASS=list-color1><TD WIDTH=50%>
  From Month
</TD><TD>
<SELECT NAME=dom>
<?php
  for($dcnt=1;$dcnt <= 31;$dcnt++) {
    print "<OPTION VALUE=" . $dcnt . ">" . $dcnt . "\n";
  }
?>
</SELECT>
<SELECT NAME=date>
<?php
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
</TD><TD>
<SELECT NAME=dom2>
<?php
  for($dcnt=31;$dcnt >= 1;$dcnt--) {
    print "<OPTION VALUE=" . $dcnt . ">" . $dcnt . "\n";
  }
?>
</SELECT>
<SELECT NAME=date2>
<?php
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
<TR CLASS=list-color1><TD>
  Extension
</TD><TD>
  <SELECT NAME=exten>
<?php
  $curextq="SELECT name,fullname||'('||name||')' AS fname from users 
                   left outer join astdb on (substr(name,0,3)=astdb.key)";
  if ($TMS_USER == 1) {
    $curextq.=" LEFT OUTER JOIN astdb AS bgrp ON (bgrp.family=name AND bgrp.key='BGRP')";
  }
  $curextq.=" WHERE length(name) = 4 AND astdb.family = 'LocalPrefix' AND astdb.value='1'";
  if (($TMS_USER == 1) && ($SUPER_USER != 1)) {
    $curextq.=" AND " . $clogacl;
  }
  $curextq.=" ORDER BY fname";
  $curext=pg_query($db,$curextq);
  $num=pg_num_rows($curext);
  for($i=0;$i < $num;$i++) {
    $r = pg_fetch_array($curext,$i,PGSQL_NUM);
    print "    <OPTION VALUE=\"" .  $r[0] . "\">" . $r[1] . "</OPTION>\n";
  }
?>
  </SELECT>
</TD></TR>
<TR CLASS=list-color2><TD>
Order Results By
</TD><TD>
<SELECT NAME=morder>
<OPTION VALUE="sum(cost)"<?php if (($morder == "sum(cost)") || ($morder == "")){print " SELECTED";}?>>Cost
<OPTION VALUE="tottime"<?php if ($morder == "tottime"){print " SELECTED";}?>>Total Time
<OPTION VALUE="avtime"<?php if ($morder == "avtime") {print " SELECTED";}?>>Average Time
<OPTION VALUE="dv8"<?php if ($morder == "dv8") {print " SELECTED";}?>>Standard Deviation
<OPTION VALUE="holdtime"<?php if ($morder == "holdtime") {print " SELECTED";}?>>Av. Hold Time
</SELECT></TD></TR>
<TR CLASS=list-color1><TD>
Descending Order</TD><TD>
<INPUT TYPE=CHECKBOX NAME=mweight CHECKED>
</TD></TR>
<TR CLASS=list-color2><TD COLSPAN=2 ALIGN=MIDDLE>
<INPUT TYPE=SUBMIT>
</TD></TR>
</FORM>
</td></tr>
</table>
