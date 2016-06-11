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

if (isset($_POST['ctfx'])) {
  $credin=$_SESSION['credit'];
  $bcolor[0]="CLASS=list-color1";
  $bcolor[1]="CLASS=list-color2";
  if ($_SESSION['credit'] > 0) {
    $rcred=pg_query("SELECT credit,exchangerate,description,oratio,rcallocated FROM reseller WHERE id='" . $_SESSION['resellerid'] . "'");
    $pcred=pg_query("SELECT credit,exchangerate,description,oratio,rcallocated FROM reseller WHERE id='" . $_POST['edituser'] . "'");
  } else {
    $rcred=pg_query("SELECT credit,exchangerate,description,oratio,rcallocated FROM reseller WHERE id='" . $_POST['edituser'] . "'");
    $pcred=pg_query("SELECT credit,exchangerate,description,oratio,rcallocated FROM reseller WHERE id='" . $_SESSION['resellerid'] . "'");
  }
  $ccred=pg_fetch_row($rcred,0);
  $pcred=pg_fetch_row($pcred,0);

  if ($_SESSION['credit'] > 0) {
    $_SESSION['credit']=floor($_SESSION['credit']/$ccred[1]*10000);
    $maxcred=$ccred[3]*$ccred[0]-$ccred[4];
  } else {
    $_SESSION['credit']=ceil($_SESSION['credit']/$ccred[1]*10000);
    $maxcred=$ccred[0];
  }

  if ($maxcred >= abs($_SESSION['credit'])) {
    $newrate=(($pcred[0]*$pcred[1]) + (abs($_SESSION['credit'])*$ccred[1]))/($pcred[0]+abs($_SESSION['credit']));

    pg_query("UPDATE reseller SET rcallocated=rcallocated + " . $_SESSION['credit'] . " WHERE id = '" . $_SESSION['resellerid'] . "'");
    pg_query("UPDATE reseller SET resetallocated=resetallocated + " . $_SESSION['credit'] . " WHERE id = '" . $_SESSION['resellerid'] . "'");
    pg_query("UPDATE reseller SET credit=credit + " . $_SESSION['credit'] . " WHERE id = '" . $_POST['edituser'] . "'");
    pg_query("UPDATE reseller SET resetcredit=resetcredit + " . $_SESSION['credit'] . " WHERE id = '" . $_POST['edituser'] . "'");

    if ($_SESSION['credit'] > 0) {
      $_SESSION['credit']=floor($_SESSION['credit']*$ccred[1])/10000;
      pg_query("UPDATE reseller SET exchangerate=" . $newrate . " WHERE id = '" . $_POST['edituser'] . "'");
    } else {
      $_SESSION['credit']=ceil($_SESSION['credit']*$ccred[1])/10000;
      pg_query("UPDATE reseller SET exchangerate=" . $newrate . " WHERE id = '" . $_SESSION['resellerid'] . "'");
    }

    pg_query("INSERT INTO logtransfer (date,payment,owner_id,reseller_id,exchangerate) VALUES
                                     (now()," . $_SESSION['credit'] . "," . $_SESSION['resellerid'] . "," . $_POST['edituser'] . "," . $newrate . ")");

    $credin=sprintf("%0.2f",$credin-$_SESSION['credit']);
    print "<TR " . $bcolor[1] . "><TH COLSPAN=2 CLASS=heading-body>Transfered R";
    printf("%0.2f",abs($_SESSION['credit']));
    print " Refund/Left R(";
    printf("%0.2f",abs($credin));
    print ")</TH></TR>";  
  } else {
    print "<TR " . $bcolor[1] . "><TH COLSPAN=2 CLASS=heading-body>Transfer Failed Insufficient Funds</TH></TR>";
  }
  print "</TABLE>";
  return;
} else {
  $bcolor[0]="CLASS=list-color2";
  $bcolor[1]="CLASS=list-color1";
}
 
  $rcred=pg_query("SELECT (((credit*oratio)-rcallocated)*exchangerate)/10000,exchangerate,(credit*exchangerate)/10000 FROM reseller WHERE id='" . $_SESSION['resellerid'] . "'");
  $ccred=pg_fetch_row($rcred,0);
  
  $cavail=floor($ccred[0]*100)/100;

/*
  if ($cavail < 0) {
    $cavail=0;
  }

*/

  $users=pg_query("SELECT id,username,description,admin,credit,exchangerate FROM reseller WHERE admin AND owner = " . $_SESSION['resellerid'] . " ORDER BY description,username");

  $num=pg_num_rows($users); 
?>
<CENTER>
<FORM METHOD=POST NAME=credform onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
  <TR <?php print $bcolor[0];?>>
  <TH COLSPAN=2 CLASS=heading-body>Transfer Credit To A Reseller</TH>
  </TR>
  <TR <?php print $bcolor[1];?>>
  <TD WIDTH=50%>Select Reseller To Transfer Credit To</TD>
  <TD>
  <SELECT NAME=edituser>
  <?php

  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_row($users,$i);
    $curcred=sprintf("R%0.2f",floor($r[4]*$r[5])/10000);
    print  "<OPTION VALUE=" . $r[0] . ">" . $r[2] . " - " . $r[1] . " (" . $curcred . ")</OPTION>\n";
  }
?>
  </SELECT></TH></TR>
  <TR <?php print $bcolor[0];?>>
    <TD>Ammount To Transfer (R)<BR>
      R<?php printf("%0.2f",$cavail);?> Avail.<BR>
      R<?php printf("%0.2f",$ccred[2]);?> Balance.
    </TD>
    <TD><INPUT TYPE=TEXT NAME=credit></TD></TR>
  <TR <?php print $bcolor[1];?>><TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT NAME=ctfx></TD></TR>
  </FORM>
</TABLE>
