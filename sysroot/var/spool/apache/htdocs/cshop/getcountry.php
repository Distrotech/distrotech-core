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

$country=pg_query($db,"SELECT countrycode,countryname
                     FROM country WHERE countrycode != '' AND countryname != ''
                     ORDER BY countryname");

$num=pg_num_rows($country); 

if (($_POST['tariffcode'] == 'A') && ((!isset($_POST['mkplan'])) || ($_POST['overwrite'] == "on"))) {
  unset($_POST['mkplan']);
  unset($_POST['newplan']);
  unset($_POST['overwrite']);
}

if (($_POST['overwrite'] == "on") && ($_POST['newplan'] == "") && (isset($_POST['mkplan'])) && ($_POST['tariffplan'] != 'A')) {
  $tname=pg_query($db,"SELECT tariffname FROM tariff WHERE tariffcode='" . $_SESSION['resellerid'] . "-" . $_SESSION['tariffcode'] . "'");
  $tdata=pg_fetch_array($tname,0);
  $_POST['newplan']=$tdata[0];
}

if (($_POST['newplan'] != "") && (isset($_POST['mkplan']))) {
  if ($_POST['addrate'] == ""){ 
    $_POST['addrate']=0;
  }
  if ($_POST['murate'] == ""){ 
    $_POST['murate']=0;
  }

  if ($_POST['overwrite'] != "on") {
    $tcount=pg_query("SELECT tariffcode FROM tariff WHERE tariffname = '" . $_POST['newplan'] . "' AND tariffcode LIKE '" . $_SESSION['resellerid'] . "-%'");
  } else {
    $tcount=pg_query("SELECT tariffcode,tariffname FROM tariff WHERE tariffcode = '" . $_SESSION['resellerid'] . "-" . $_SESSION['tariffcode'] . "'");
    if ($_POST['newplan'] == "") {
      $newplanar=pg_fetch_array($tcount,0);
      $_POST['newplan']=$newplanar[1];
    }
  }

  if (($_POST['tax'] < "0") || (!is_numeric($_POST['tax'])) || ($_POST['tax'] > "100")){
    $_POST['tax']="0";
  }
  if ($_POST['showtax'] == "on") {
    $_POST['showtax']="t";
  } else {
    $_POST['showtax']="f";
  }
  $_POST['minrate']=ceil(($_POST['minrate']/$_SESSION['rexrate'])*10000);
  if ((pg_num_rows($tcount) > 0 ) && ($_POST['overwrite'] != "on")){
    $tcode=pg_fetch_array($tcount,0);
    $_SESSION['tariffcode']=$tcode[0];
?>
      <SCRIPT>
        alert("Tariff Plan Already Exists Not Adding")
      </SCRIPT><?php
  } else {
    $tcount=pg_query("SELECT tariffcode FROM tariff WHERE tariffcode LIKE '" . $_SESSION['resellerid'] . "-%'");
    $ntcode=pg_num_rows($tcount);
    if (($_POST['overwrite'] != "on") && ($ntcode <= 99)) {
      $_SESSION['tariffcode']=$ntcode;
      pg_query("INSERT INTO tariff (tariffname,tariffcode,tax,showtax,minrate,margin,switchfee,resellerid) VALUES ('" . $_POST['newplan'] . "','" . $_SESSION['resellerid'] . "-" . $_SESSION['tariffcode'] . "','" . $_POST['tax'] . "','" . $_POST['showtax'] . "','" . $_POST['minrate'] . "'," . $_POST['murate'] . "*100," . $_POST['addrate'] . "*100," . $_SESSION['resellerid'] . ")");
    } else if ($_POST['overwrite'] == "on") {
      pg_query("DELETE FROM tariffrate WHERE tariffcode='" . $_SESSION['resellerid'] . "-" . $_SESSION['tariffcode'] . "'");
      pg_query("UPDATE tariff SET margin=" . $_POST['murate'] . "*100,switchfee=" . $_POST['addrate'] . " *100,tariffname='" . $_POST['newplan'] . "',tax='" . $_POST['tax'] . "',showtax='" . $_POST['showtax'] . "',minrate='" . $_POST['minrate'] . "' WHERE tariffcode='" . $_SESSION['resellerid'] . "-" . $_SESSION['tariffcode'] . "'");
    } else {
      header( "Location: " . $baseurl . "/cshop/index.php?disppage=gettplan.php");
    }
    if ($_POST['addrate'] == "") {
      $_POST['addrate']="0";
    }
    if ($_POST['murate'] == "") {
      $markup="1";
    } else {
      $markup=1 - $_POST['murate'] / 100;
    }
    $getrateq="SELECT buyrate,tax FROM reseller LEFT OUTER JOIN tariff on (tariffcode=buyrate) WHERE reseller.id = '" . $_SESSION['resellerid'] . "'";
    $getrate=pg_query($getrateq);
    $buyrate=pg_fetch_array($getrate,0);
    if ($buyrate[1] > $_POST['tax']) {
      $vmar=(100+$_POST['tax'])/(100+$buyrate[1]);
    } else {
      $vmar=1;
    }
    $rquery1="INSERT INTO tariffrate (countrycode,subcode,rate,trunkprefix,tariffcode) SELECT countrycode,subcode,
            ((rate / " . $vmar . ") / " . $markup . " + (" . $_POST['addrate'] . " * 100)),trunkprefix,'" . $_SESSION['resellerid'] . "-" . $_SESSION['tariffcode'] . "' 
           FROM tariffrate WHERE rate > 0 AND tariffcode = '" . $buyrate[0] . "' AND ((rate / " . $vmar . ") / " . $markup . " + (" . $_POST['addrate'] . " * 100)) > " . $_POST['minrate'];
    pg_query($rquery1);
    $rquery2="INSERT INTO tariffrate (countrycode,subcode,rate,trunkprefix,tariffcode) SELECT countrycode,subcode,
           " . $_POST['minrate'] . ",trunkprefix,'" . $_SESSION['resellerid'] . "-" . $_SESSION['tariffcode'] . "' 
           FROM tariffrate WHERE rate > 0 AND tariffcode = '" . $buyrate[0] . "' AND ((rate / " . $vmar . ") / " . $markup . " + " . $_POST['addrate'] . " * 100) < " . $_POST['minrate'];
    pg_query($rquery2);
  }
} else if ((!isset($_POST['mkplan'])) && (isset($_POST['tarriffcode']))) {
  $tcount=pg_query("SELECT tariffcode FROM tariff WHERE tariffcode LIKE '" . $_SESSION['resellerid'] . "-%'");
  $newcode=pg_num_rows($tcount);

  pg_query("DELETE FROM tariff WHERE tariffcode = '" . $_SESSION['resellerid'] . "-" . $_SESSION['tariffcode'] . "'");
  pg_query("DELETE FROM tariffrate WHERE tariffcode = '" . $_SESSION['resellerid'] . "-" . $_SESSION['tariffcode'] . "'");

  for ($cnt=$_SESSION['tariffcode']+1;$cnt<$newcode;$cnt++) {
    $new=$cnt-1;
    pg_query("UPDATE tariff SET tariffcode='" . $_SESSION['resellerid'] . "-" . $new . "' WHERE tariffcode='" . $_SESSION['resellerid'] . "-" . $cnt . "'");
    pg_query("UPDATE tariffrate SET tariffcode='" . $_SESSION['resellerid'] . "-" . $new . "' WHERE tariffcode='" . $_SESSION['resellerid'] . "-" . $cnt . "'");
  }
  include "/var/spool/apache/htdocs/cshop/gettplan.php";
  return;
}
$_SESSION['disppage']="cshop/getcsub.php";
?>
<CENTER>
<FORM NAME=getcountry METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Select Or Add A Country To Edit</TH>
</TR>
<TR CLASS=list-color1>
<TD WIDTH=50%>Select Country To Alter</TD>
<TD>
<SELECT NAME=country>
<?php

for ($i=0; $i < $num; $i++) {
  $r = pg_fetch_row($country,$i);
  print  "<OPTION VALUE=" . $r[0];
  if ($r[0] == $_SESSION['country']) {
    print " SELECTED";
  }
  print ">" . $r[1] . "</OPTION>\n";
}
?>
</SELECT></TD></TR>
<TR CLASS=list-color2><TH COLSPAN=2>
<INPUT TYPE=BUTTON ONCLICK=openpage('cshop/gettplan.php','tariffs') VALUE="<<">
<INPUT TYPE=SUBMIT onclick=this.name='getcountry' VALUE="Edit Country">
<?php  
if ($_SESSION['resellerid'] == 0) {?>
  <INPUT TYPE=BUTTON ONCLICK="deleteconf('This Country',document.getcountry,document.getcountry.delcountry)" VALUE="Delete Country">
<?php
}
if (isset($_SESSION['country'])) {?>
  <INPUT TYPE=BUTTON ONCLICK=openpage('cshop/getcsub.php','tariffs') VALUE=">>">
<?php 
}
?>
</TH></TR>
<?php
if ($_SESSION['resellerid'] == 0) {?>
<TR CLASS=list-color1>
<TH COLSPAN=2 CLASS=heading-body2>Add NEW Country</TH>
</TR>
<TR CLASS=list-color2>
<TD>Country Name</TD>
<TD><INPUT NAME=newcountryname VALUE=""></TD>
</TR>
<TR CLASS=list-color1>
<TD ALIGN=LEFT>Country Code</TD>
<TD><INPUT NAME=newcountrycode VALUE=""></TD>
</TR>
<TR CLASS=list-color2><TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT onclick=this.name='addcountry'></TD></TR>
<?php }?>
<INPUT TYPE=HIDDEN NAME=delcountry>
</FORM>
</TABLE>
