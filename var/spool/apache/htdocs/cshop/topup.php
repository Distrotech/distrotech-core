<CENTER>
<FORM METHOD=POST NAME=credtx onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
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

$rlab[0]="Full Name";
$rlab[1]="Email Address";
$rlab[2]="Credit Available";

$rcnt=0;


if (isset($_POST['conftx'])) {
  $credin=$_SESSION['credit'];
  $bcolor[0]="CLASS=list-color1";
  $bcolor[1]="CLASS=list-color2";

  $rcred=pg_query("SELECT (credit*oratio)-rcallocated,exchangerate,description FROM reseller WHERE id='" . $_SESSION['resellerid'] . "'");
  $ccred=pg_fetch_row($rcred,0);

  if ($_SESSION['credit'] > 0) {
    $_SESSION['credit']=floor($_SESSION['credit']/$ccred[1]*10000);
  } else {
    $_SESSION['credit']=ceil($_SESSION['credit']/$ccred[1]*10000);
  }

  if ($ccred[0] < $_SESSION['credit']){
    $_SESSION['credit']=$ccred[0];
  }

  $udetail=pg_query("SELECT fullname,id FROM users WHERE name='" . $_SESSION['acnum'] . "'");
  $r=pg_fetch_row($udetail,0);

  pg_query("UPDATE reseller SET rcallocated=rcallocated + " . $_SESSION['credit'] . " WHERE id = '" . $_SESSION['resellerid'] . "'");
  pg_query("INSERT INTO logrefill (credit,card_id,reseller_id) VALUES (" . $_SESSION['credit'] . "," . $r[1] . "," . $_SESSION['resellerid'] . ")");

  $credout=($_SESSION['credit']*$ccred[1])/10000;

  $credin=sprintf("%0.2f",$credin-$credout);

  pg_query("UPDATE users SET credit=credit + " . $_SESSION['credit'] . " WHERE name = '" . $_SESSION['acnum'] . "'");
  pg_query("INSERT INTO sale (saletime,credit,username,cardid,saletype,discount) VALUES (now()," . $_SESSION['credit'] . ",'" . $r[0] . "'," . $r[1] . ",'Account Topup',0)");


  print "<TR " . $bcolor[1] . "><TH COLSPAN=2 CLASS=heading-body>Transaction Completed</TH></TR>";
  print "<TR " . $bcolor[0] . "><TD WIDTH=50%>Transfered</TD><TD>";
  printf("R%0.2f",abs($credout));
  print "</TD></TR>";
  print "<TR " . $bcolor[1] . "><TD>Refund</TD><TD>";
  printf("R%0.2f",abs($credin));
  print "</TD></TR>";

  $udetail=pg_query("SELECT fullname,email,credit FROM users WHERE name='" . $_SESSION['acnum'] . "'");
  $r=pg_fetch_row($udetail,0);

  $r[2]=sprintf("R%0.2f",($r[2]*$ccred[1])/10000);
  for ($j=0;$j < count($r);$j++) {
    print "<TR " . $bcolor[($j) % 2] . "><TD>" . $rlab[$j] . "</TD><TD>" . $r[$j]  . "</TD></TR>";
  }
  print "</TABLE>";
  return;
} else if (isset($_POST['ctfx'])) {
  $udetail=pg_query("SELECT fullname,email,credit FROM users WHERE name='" . $_SESSION['acnum'] . "' AND agentid = " . $_SESSION['resellerid']);
  $isuser=pg_num_rows($udetail);
  $isudat=pg_fetch_row($udetail,0);

  $rcred=pg_query("SELECT (((credit*oratio)-rcallocated)*exchangerate)/10000,exchangerate FROM reseller WHERE id='" . $_SESSION['resellerid'] . "'");
  $ccred=pg_fetch_row($rcred,0);
  $cavail=$ccred[0];


  if (($isuser > 0) && ((($cavail >= $_SESSION['credit']) && ($_SESSION['credit'] > 0)) || (($isudat[2] + $_SESSION['credit'] > 0) && ($_SESSION['credit'] < 0))) && ($_SESSION['credit'] != 0)){
    $r=pg_fetch_row($udetail,0);

    $bcolor[0]="CLASS=list-color1";
    $bcolor[1]="CLASS=list-color2";

    print "<TR " . $bcolor[1] . "><TH COLSPAN=2 CLASS=heading-body>Confirm Account Details</TH></TR>"; 

    $r[2]=sprintf("R%0.2f",($r[2]*$ccred[1])/10000);
    for ($j=0;$j < count($r);$j++) {
      print "<TR " . $bcolor[$j % 2] . "><TD WIDTH=50%>" . $rlab[$j] . "</TD><TD>" . $r[$j]  . "</TD></TR>";
    }
%>
    <TR <%print $bcolor[1];%>>    
    <TD>Account/Card/Phone Number</TD>
    <TD><%print $_SESSION['acnum'];%>
    </TD></TR>
    <TR <%print $bcolor[0];%>>
      <TD>Ammount To Transfer (R) <%printf("%0.2f",$cavail);%> Avail.</TD>
      <TD><%printf("R%0.2f",$_SESSION['credit']);%></TD></TR>
    <TR <%print $bcolor[1];%>><TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT VALUE="Topup Account" NAME=conftx></TD></TR>
    </FORM>
  </TABLE>
<%
    return;
  } else {
    $bcolor[0]="CLASS=list-color2";
    $bcolor[1]="CLASS=list-color1";
    if ($isuser <= 0) {
      print "<TR " . $bcolor[$rcnt % 2] . "><TH COLSPAN=2>Account Does Not Exist</TH></TR>";
      $rcnt++;
    }
    if (($cavail < $_SESSION['credit']) && ($_SESSION['credit'] > 0)){
      print "<TR " . $bcolor[$rcnt %2] . "><TH COLSPAN=2>Transfer Failed Insufficient Funds</TH></TR>";
      $rcnt++;
    }
    if ($_SESSION['credit'] <= 0) {
      print "<TR " . $bcolor[$rcnt %2] . "><TH COLSPAN=2>Please Enter A Valid Credit Ammount</TH></TR>";
      $rcnt++;
    }
  };
} else {
  $bcolor[0]="CLASS=list-color2";
  $bcolor[1]="CLASS=list-color1";
}

  $rcred=pg_query("SELECT (((credit*oratio)-rcallocated)*exchangerate)/10000,exchangerate FROM reseller WHERE id='" . $_SESSION['resellerid'] . "'");
  $ccred=pg_fetch_row($rcred,0);
  $cavail=$ccred[0];

%>
  <TR <%print $bcolor[$rcnt % 2];$rcnt ++;%>>
  <TH COLSPAN=2 CLASS=heading-body>Transfer Credit To A Account</TH>
  <TR>
  <TR <%print $bcolor[$rcnt % 2];$rcnt ++;%>>
  <TD>Account/Card/Phone Number</TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=acnum>
  </TD></TR>
  <TR <%print $bcolor[$rcnt % 2];$rcnt++;%>>
    <TD>Ammount To Transfer (R) <%printf("%0.2f",$cavail);%> Avail.</TD>
    <TD><INPUT TYPE=TEXT NAME=credit></TD></TR>
  <TR <%print $bcolor[$rcnt % 2];%>><TD COLSPAN=2 ALIGN=MIDDLE><INPUT TYPE=SUBMIT VALUE="Confirm Details" NAME=ctfx></TD></TR>
  </FORM>
</TABLE>
