<?php

if (! $db) {
  include "/var/spool/apache/htdocs/cshop/auth.inc";
}

if (isset($_POST['breakout'])) {
  $brout=pg_query($db,"SELECT subcode,trunkprefix FROM tariffrate WHERE id=" . $_POST['breakout']);
  list($_SESSION['subcode'],$_SESSION['trunkprefix'])=pg_fetch_array($brout,0,PGSQL_NUM);
}

$tariffq="SELECT exchangerate,(rate*exchangerate)/10000,tax,showtax FROM reseller LEFT OUTER JOIN tariffrate ON (buyrate=tariffrate.tariffcode) LEFT OUTER JOIN tariff ON (buyrate=tariff.tariffcode) 
                 WHERE reseller.id = " . $_SESSION['resellerid'] . " AND trunkprefix='" . $_SESSION['trunkprefix'] . "' AND countrycode='" . $_SESSION['country'] . "' AND subcode = '". $_SESSION['subcode'] . "'";
$exq=pg_query($tariffq);

$exratea=pg_fetch_row($exq,0);
$exrate=$_SESSION['rexrate'];
$purchrate=$exratea[1];

$_POST['rate']=$_POST['rate']/$exrate;

if (($_SESSION['tariffcode'] != "A") || ($_SESSION['resellerid'] != "0")) {
  $tarcode=$_SESSION['resellerid'] . "-" . $_SESSION['tariffcode'];
} else {
  $tarcode=$_SESSION['tariffcode'];
}

if (isset($_POST['saverate'])) {
  pg_query("UPDATE tariffrate SET rate=" . $_POST['rate'] . " * 10000" . 
        " WHERE countrycode='" . $_SESSION['country'] . "' AND subcode = '". $_SESSION['subcode'] . "'
        AND tariffcode = '" . $tarcode . "' AND trunkprefix='" . $_SESSION['trunkprefix'] . "'");
//  pg_query("UPDATE countryprefix SET subcode='" . $_SESSION['subcode'] . "' WHERE subcode='" . $_SESSION['subcode'] ."'");
} else if ((isset($_POST['changeprefix'])) && ($_SESSION['resellerid'] == 0)){
  $cpre=pg_query($db,"SELECT prefix FROM countryprefix WHERE countrycode = '" . $_SESSION['country'] . "' AND subcode = '" . $_SESSION['subcode'] . "' AND trunkprefix='" . $_SESSION['trunkprefix'] . "'");
  $num=pg_num_rows($cpre);
  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_array($cpre,$i,PGSQL_NUM);
    $test="del" . $r[0];
    if ($_POST[$test] != "") {
      pg_query("DELETE FROM countryprefix WHERE prefix = " . $r[0] . 
                       " AND countrycode='" . $_SESSION['country'] . "' AND subcode = '" . $_SESSION['subcode'] . "' AND trunkprefix='" . $_SESSION['trunkprefix'] . "'");
    }
  }
  if ($_POST['newprefix'] != "") {
    $addpre="INSERT INTO countryprefix (prefix,countrycode,subcode,trunkprefix) VALUES (" . 
                          $_POST['newprefix'] . ",'" . $_SESSION['country'] . "','" . $_SESSION['subcode'] . "','" . $_SESSION['trunkprefix'] ."')";
    pg_query($db,$addpre);
    $_SESSION['reload']=true;
  }
} else if ((isset($_POST['addbreak'])) && ($_POST['newbreakout'] != "") && ($_SESSION['resellerid'] == 0)) {
  $_SESSION['subcode']=$_POST['newbreakout'];
  $_SESSION['trunkprefix']=$_POST['trunkprefix'];
  $addtest=pg_query("SELECT * FROM tariffrate WHERE tariffcode = '" . $tarcode . "' AND countrycode = '" . $_SESSION['country'] . "' AND subcode = '" . $_SESSION['subcode'] . "' AND trunkprefix='" . $_POST['trunkprefix'] . "'");
  $num=pg_num_rows($addtest);
  if ($num == 0) {
    pg_query("INSERT INTO tariffrate (startdate,countrycode,subcode,rate,tariffcode,trunkprefix) VALUES
                          (now(),'" . $_SESSION['country'] . "','" . $_SESSION['subcode'] . "'," . $_POST['newrate'] . " * 10000 / $exrate,'" . $tarcode . "','" . $_SESSION['trunkprefix'] . "')");
    $_SESSION['reload']=true;
  }
} else if (($_POST['delbreak'] == "1") && ($_SESSION['resellerid'] == "0")) {
  pg_query($db,"DELETE FROM tariffrate WHERE countrycode='" . $_SESSION['country'] . "' AND subcode = '" . $_SESSION['subcode'] . "' AND trunkprefix='" . $_SESSION['trunkprefix'] . "'");
  pg_query($db,"DELETE FROM countryprefix WHERE countrycode='" . $_SESSION['country'] . "' AND subcode = '" . $_SESSION['subcode'] . "' AND trunkprefix='" . $_SESSION['trunkprefix'] . "'");
  include "/var/spool/apache/htdocs/cshop/getcsub.php";
  return;
}

$boutqs="SELECT subcode,rate / 10000.00,trunkprefix,countryname,tax,minrate FROM tariffrate
                     LEFT OUTER JOIN country ON (tariffrate.countrycode = country.countrycode)
                     LEFT OUTER JOIN tariff ON (tariffrate.tariffcode = tariff.tariffcode)
                     WHERE tariffrate.tariffcode = '" . $tarcode . "' AND subcode = '" . $_SESSION['subcode'] . "' AND 
                           tariffrate.trunkprefix= '" . $_SESSION['trunkprefix'] . "' AND 
                           tariffrate.countrycode = '" . $_SESSION['country'] . "' LIMIT 1";

$minbuy=pg_query("SELECT minrate/1000,tax FROM tariff LEFT OUTER JOIN reseller ON (buyrate = tariffcode) 
                    WHERE reseller.id= '" . $_SESSION['resellerid'] . "' AND buyrate = tariffcode  LIMIT 1");

$boutq=pg_query($db,$boutqs);

$num=pg_num_rows($boutq);
if ($num == 0) {
  pg_query("INSERT INTO tariffrate (startdate,countrycode,subcode,rate,tariffcode,trunkprefix) VALUES
                        (now(),'" . $_SESSION['country'] . "','" . $_SESSION['subcode'] . "',(" . $_POST['newrate'] . "*10000)/" . $_SESSION['rexrate'] . ",'" . $tarcode . "','" . $_SESSION['trunkprefix'] . "')");
  $boutq=pg_query($db,$boutqs);
}

$bout=pg_fetch_row($boutq,0);
$mrate=pg_fetch_row($minbuy,0);

if (($exratea[2] > 0) && (($bout[4] == 0) || ($exratea[2] == 't'))) {
//  print $exratea[2] . " "  . $purchrate . " " . $bout[1] . " " . $exrate . "<BR>\n";
//  $margin=1 - $purchrate/($bout[1]*$exrate);

  $purchrate=$purchrate*(1+$exratea[2]/100);
}


if ($purchrate < $mrate[0]) {
  $purchrate=$mrate[0];
}

?>
<CENTER>
<FORM METHOD=POST NAME=editrate onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%> 
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Editing <?php printf("%s (Cost %0.2f %s)",$_SESSION['subcode'],$purchrate,$bout[3]);?></TH></TR>
<TR CLASS=list-color1>
<?php
$margin=1 - $purchrate/($bout[1]*$exrate);
?>
<TD WIDTH=50%>
<?php
if ($margin < 0) {
  print "<FONT COLOR=RED>";
  $margin=abs($margin);
}
?>
Rate (R/m Ex Vat) Current Margin <?php printf("%0.0f",100*$margin);?>%</TD><TD><INPUT TYPE=TEXT NAME=rate VALUE="<?php printf("%0.2f",$bout[1]*$exrate);?>"></TD></TR>
<TR CLASS=list-color2>
<TD>Vat/Tax</TD><TD ALIGN=LEFT><?php printf("%0.2f",$bout[1]*$exrate*($bout[4]/100));?>
</TD></TR>
<TR CLASS=list-color1>
<TD>Total</TD><TD ALIGN=LEFT><?php printf("%0.2f",$bout[1]*$exrate*(1+$bout[4]/100));?>
</TD></TR>
<TR CLASS=list-color2>
<TD ALIGN=MIDDLE COLSPAN=2>
<INPUT TYPE=BUTTON ONCLICK=openpage('cshop/getcsub.php','tariffs') VALUE="<<">
<INPUT TYPE=SUBMIT onclick=this.name='saverate' VALUE="Save Changes"></TD></TR>
<TR CLASS=list-color1><TH COLSPAN=2 CLASS=heading-body2>Prefixes</TH>
<?php
 print "\n";
 $bcolor[0]="list-color2";
 $bcolor[1]="list-color1";

  $cpre=pg_query($db,"SELECT prefix FROM countryprefix WHERE trunkprefix='" . $_SESSION['trunkprefix'] . "' AND countrycode = '" . $_SESSION['country'] . "' AND subcode = '" . $_SESSION['subcode'] . "' ORDER BY prefix");
  $num=pg_num_rows($cpre);
  $cnt=2;
  for ($i=0; $i < $num; $i++) {
    $rem=$i % 2;
    if (($rem == 0) && ($cnt == 2)) {
      print "</TR>\n<TR CLASS=" . $bcolor[$col % 2] . ">";
      $cnt=0;
      $col++;
    }
    $r = pg_fetch_array($cpre,$i,PGSQL_NUM);
    print "<TD>";
    if ($_SESSION['resellerid'] == 0) {
      print "<INPUT TYPE=CHECKBOX NAME=del" . $r[0] . ">";
    }
    print $r[0] . "</TD>";
    $cnt++;
  }
  if ($cnt != 2) {
    print "<TD>&nbsp;</TD></TR>";
  } else {
    print "</TR>";
  } 
  if ($_SESSION['resellerid'] == 0) {
    $rem=$col % 2;
    print "\n<TR CLASS=" . $bcolor[$rem] . "><TD>Add New Prefix</TD><TH>";
    print "<INPUT TYPE=TEXT NAME=newprefix></TH></TR>";
    $col++;
    $rem=$col % 2;
    print "<TR CLASS=" . $bcolor[$rem] . "><TD ALIGN=MIDDLE COLSPAN=2>";
    print "<INPUT TYPE=SUBMIT onclick=this.name='changeprefix' VALUE=\"Update Prefix[s]\"></TD></TR>";
  }
?>


</TABLE>
</FORM>
