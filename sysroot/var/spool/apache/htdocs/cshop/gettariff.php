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
$act[t]="Yes";
$act[f]="No";

$bcolor[0]="list-color1";
$bcolor[1]="list-color2";

$getownq=pg_query($db,"SELECT owner,admin FROM reseller WHERE
                         id=" . $_SESSION['resellerid']);
$getown=pg_fetch_array($getownq,0);
if ($getown[1] == "f") {
  $_SESSION['resellerid']=$getown[0];
}

if ((isset($_POST['tariffcode']) || ((isset($_POST['print'])) && $_POST['print'] != ""))) {
  $getexr=pg_query($db,"SELECT exchangerate,reseller.id,(minrate / 10000.00)*exchangerate FROM reseller 
                           LEFT OUTER JOIN tariff ON (buyrate=tariffcode) WHERE
                           reseller.id=" . $_SESSION['resellerid']);
  $exr=pg_fetch_array($getexr,0);
  if ($_POST['country'] != "") {
    $country=" AND country.countrycode='" . $_POST['country'] . "'";
  }
  if ($_POST['sdollar'] == "on") {
    $exr[0]=100;
  }
  if ($_SESSION['tariffcode'] != $_SESSION['rbuyrate']) {
    $tariffcode=$exr[1] . "-"  . $_SESSION['tariffcode'];
  } else {
    $tariffcode=$_SESSION['tariffcode'];
  }
  $rateqq="SELECT countryname,subcode,(rate / 10000.00 * $exr[0]),tax,showtax,
                              (minrate / 10000.00 * $exr[0]),
                              country.countrycode,minrate
                         FROM tariffrate LEFT OUTER JOIN
                           country ON (country.countrycode=tariffrate.countrycode) 
                         LEFT OUTER JOIN tariff on (tariffrate.tariffcode=tariff.tariffcode)
                         WHERE tariffrate.tariffcode='" . $tariffcode . "'$country
                         ORDER BY countryname,subcode";
  $rateq=pg_query($db,$rateqq);
  $num=pg_num_rows($rateq);
  if (($country == "") && ($tariffcode == "A") && ($num == 0)) {
    pg_query($db,"INSERT INTO tariffrate (tariffcode,countrycode,subcode,rate) SELECT DISTINCT 'A',countrycode,subcode,0 from countryprefix");
    pg_query($db,"INSERT INTO tariff (tariffname,tariffcode) VALUES ('Master Rate','A')");
    $rateq=pg_query($db,$rateqq);
    $num=pg_num_rows($rateq);
  }
if ($_POST['print'] < 2) {
?>
<CENTER>
<FORM METHOD=POST NAME=csvform>
<INPUT TYPE=HIDDEN NAME=tariffcode VALUE="<?php print $_POST['tariffcode'];?>">
<INPUT TYPE=HIDDEN NAME=country VALUE="<?php print $_POST['country'];?>">
<INPUT TYPE=HIDDEN NAME=sdollar VALUE="<?php print $_POST['sdollar'];?>">
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<?php print $_SESSION['disppage'];?>">
<INPUT TYPE=HIDDEN NAME=print>
</FORM>
<FORM METHOD=POST NAME=ltariff onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% CELLSPACING=0 CELLPADDING=0>

<TR CLASS=list-color2>
<TH ALIGN=LEFT CLASS=heading-body2>Country</TH><TH ALIGN=LEFT CLASS=heading-body2>Break Out</TH>
<TH ALIGN=LEFT CLASS=heading-body2><?php if ($_POST['sdollar'] == "on") {print "\$c/m";} else {print "Rate R/m";}?></TH>
</TR>
<?php
}
  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_row($rateq,$i);
    $rem=$i % 2; 
    if ($_POST['print'] < 2) {
      print "<TR CLASS=" . $bcolor[$rem] . ">";
    } else {
      print "\"" . $r[6] . "\",";
    }
    for ($j=0;$j < 3;$j++) {
      if ($j == 2) {
        if (($r[$j] <= $r[5]) && ($r[6] != "ZAF")) {
          $r[$j]=$r[5];
        }
        if (($r[$j] <= $exr[2]) && ($r[6] != "ZAF")) {
          $r[$j]=$exr[2];
        }
        if ($r[4] == "t") {
          $r[$j]=$r[$j]*(1+$r[3]/100);
        }
	$r[$j]=ceil($r[$j]*100);
        $r[$j]=sprintf("%0.2f",$r[$j]/100);
      }
      if ($_POST['print'] < 2) {
        print  "<TD>" . $r[$j] . "</TD>";
      } else {
        if ($j == 2) {
          print  $r[$j];
        } else {
          print  "\"" . $r[$j] . "\"";
        }
        if ($j < 2) {
          print ",";
        }
      }
    }
    if ($_POST['print'] < 2) {
      print "</TR>\n";
    } else {
      print "\n";
    }
  }
  if ($_POST['print'] < 2) {
    if ($_POST['print'] != "1") {
      $rem=$i%2;
      print "<TR CLASS=" . $bcolor[$rem] . "><TH COLSPAN=7 CLASS=heading-body>";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("CSV Export") . "\" ONCLICK=\"csvpage(document.csvform)\">";
      print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.csvform)\">";
      print "</TH></TR>";
    }
    print "</TABLE></FORM>";
  }
} else {
  $tariff=pg_query($db,"SELECT tariffcode,tariffname
                       FROM tariff WHERE tariffcode LIKE '" . $_SESSION['resellerid'] . "-%' ORDER BY tariffname");
  $num=pg_num_rows($tariff);
  unset($_SESSION['tariffcode']);
  unset($_SESSION['country']);
?>
<CENTER>
<FORM METHOD=POST NAME=ltariff onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% CELLSPACING=0 CELLPADDING=0>
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Select A Rate Sheet To View</TH>
<TR CLASS=list-color1>
<TD ALIGN=LEFT WIDTH=50%>Rate Sheet</TD>
<TH ALIGN=LEFT VALIGN=MIDDLE>

<SELECT NAME=tariffcode>
<?php
  if ($_SESSION['auser'] == "1") {
    print "<OPTION VALUE=\"" . $_SESSION['rbuyrate'] . "\">Buy Rate</OPTION>\n";
  }
  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_row($tariff,$i);
    $r[0]=substr($r[0],strpos($r[0],"-")+1);
    print  "<OPTION VALUE=" . $r[0] . ">" . $r[1] . "</OPTION>\n";
  }
?>
</SELECT></TH></TR>
<?php
$country=pg_query($db,"SELECT country.countrycode,countryname
                     FROM country WHERE countrycode != '' AND countryname != ''
                     ORDER BY countryname");

$num=pg_num_rows($country);
/*
if ($newplan != "") {
  $tcount=pg_query("SELECT tariffcode FROM tariff WHERE tariffcode LIKE '" . $_SESSION['resellerid'] . "-%'");
  $_SESSION['tariffcode']=pg_num_rows($tcount);
  $newcode=$_SESSION['resellerid'] . "-" . $_SESSION['tariffcode'];
  pg_query("INSERT INTO tariff (tariffname,tariffcode) VALUES ('" . $newplan . "','" . $newcode . "')");
}
*/
?>
<TR CLASS=list-color2>
<TD>Select Country To View</TD>
<TH ALIGN=LEFT VALIGN=MIDDLE>
<SELECT NAME=country>
<OPTION VALUE="">All
<?php

for ($i=0; $i < $num; $i++) {
  $r = pg_fetch_row($country,$i);
  print  "<OPTION VALUE=" . $r[0] . ">" . $r[1] . "</OPTION>\n";
}

?>
</SELECT></TH></TR>
<TR CLASS=list-color1>
<TD>Show Price In Dollar</TD>
<TH ALIGN=LEFT VALIGN=MIDDLE>
<INPUT TYPE=CHECKBOX NAME=sdollar>
</TH></TR>
<TR CLASS=list-color2><TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT></TH></TR>
</FORM>
</TABLE>
<?php
}
?>
