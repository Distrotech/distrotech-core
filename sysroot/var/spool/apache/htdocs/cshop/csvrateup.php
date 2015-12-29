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

if ((isset($_POST['addreq'])) && ($_SESSION['auser'])) {
  if ($_FILES['topdf']['name'] != "") {
    $upat=array("'\..*'i");
    $rpat=array(".pdf");
    $fname1=preg_replace($upat,$rpat,$_FILES['topdf']['name']);
    $fname="/var/spool/apache/htdocs/pdf/" . $fname1;
    $tmprate=tempnam("/tmp","pdfin");

    if (move_uploaded_file($_FILES['topdf']['tmp_name'],$tmprate)) {
      $delquery="DELETE FROM tariffrate where tariffcode='" . $_POST['tarcode'] . "'";
      pg_query($db,$delquery);

      $ratefd=file($tmprate);
      
      $patterns=array("/^[\"]+([A-Z]{3})[\",]+.*[,\"]+([a-zA-Z0-9 \-\']+)[\",]+([0-9\.]+)/");
      while(list($lnum,$ldata)=each($ratefd)) {
        $ldata=preg_replace("/'/","\'", $ldata);
        $ldata="\"" . $ldata;
        if ($_POST['tarcode'] != "A") {
          $replace=array("INSERT INTO tariffrate (countrycode,subcode,rate,trunkprefix,tariffcode) SELECT 
                            countrycode,subcode,(10000*\\3)/" . $_SESSION['rexrate'] . " as rate,trunkprefix,'" . $_POST['tarcode'] . "' as tariffcode 
                          from tariffrate where 
                            tariffcode='" . $_SESSION['rbuyrate'] . "' and countrycode='\\1' AND subcode='\\2' and trunkprefix is not null;",
                         "INSERT INTO tariffrate (countrycode,subcode,rate,trunkprefix,tariffcode) SELECT 
                            countrycode,subcode,(10000*\\3)/" . $_SESSION['rexrate'] . " as rate,trunkprefix,'" . $_POST['tarcode'] . "' as tariffcode 
                          from tariffrate where 
                            tariffcode='" . $_SESSION['rbuyrate'] . "' and countrycode='\\1' AND subcode='\\2' and trunkprefix is not null;");
        } else {
          $replace=array("INSERT INTO tariffrate (countrycode,subcode,rate,trunkprefix,tariffcode) VALUES ('\\1','\\2',(10000*\\3)/" . $_SESSION['rexrate'] . ",'1','A');");
        }
        $sqlq=preg_replace ($patterns,$replace,$ldata);
        if ($ldata != $sqlq) {
          $pginserr=pg_query($db,$sqlq);
//          print $sqlq;

          $rescode=pg_affected_rows($pginserr);
          if (!$rescode) {
            print "Line " . $lnum . " " . $ldata . " <FONT COLOR=RED>FAILED</FONT><BR>\n";
          }
        } else {
          print "Line " . $lnum . " " . $ldata . " <FONT COLOR=RED>FAILED</FONT><BR>\n";
        }
      }
      
      unlink($tmprate);
    };
  };
}else {
  $tariff=pg_query($db,"SELECT tariffcode,tariffname
                        FROM tariff WHERE tariffcode LIKE '" . $_SESSION['resellerid'] . "-%' ORDER BY tariffname");
  $num=pg_num_rows($tariff);
?>
<CENTER>
  <FORM enctype="multipart/form-data" METHOD=POST>
  <TABLE WIDTH=90% cellspacing="0" cellpadding="0">
    <TR CLASS=list-color2>
    <TH COLSPAN=2 CLASS=heading-body>Select Rate Sheet To Load</TH>
    </TR>
    <TR CLASS=list-color1>
      <TD>Select Tariff Plan To Load</TD>
      <TD ALIGN=LEFT VALIGN=MIDDLE>
        <SELECT NAME=tarcode><?php
          print "\n";
          if ($_SESSION['resellerid'] == "0") {
            print "<OPTION VALUE=A>Master Rate</OPTION>";
          }
          for ($i=0; $i < $num; $i++) {
            $r = pg_fetch_row($tariff,$i);
            $r[0]=substr($r[0],strpos($r[0],"-")+1);
            print  "<OPTION VALUE=\"" . $_SESSION['resellerid'] . "-" . $r[0] . "\">" . $r[1] . "</OPTION>\n";
          }?>
        </SELECT>
      </TD>
    </TR>
    <TR CLASS=list-color2>
      <TD WIDTH=50%>
        CSV File To Be Loaded
      </TD>
      <TD WIDTH=50%>
        <INPUT TYPE=FILE NAME=topdf>
      </TD>
    </TR>
    <TR CLASS=list-color1>
      <TD COLSPAN=2 ALIGN=MIDDLE>
        <INPUT TYPE=SUBMIT NAME=addreq VALUE="Submit Request">
      </TD>
    <TR>
  </TABLE>
  </FORM>
<?php
}
?>
