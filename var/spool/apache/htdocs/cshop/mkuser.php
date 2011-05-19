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
include "/var/spool/apache/htdocs/cshop/mkuser.inc";


if (isset($_POST['adduser'])) {
  if ($_POST['bulk'] <= 1) {
    $_POST['bulk']=1;
  } else {
    $_POST['email']="";
%>
<SCRIPT>
  alert("Caller ID And Voicemail To Email Disabled On Bulk Add");
</SCRIPT>
<%
  }

  if ($_POST['active'] == "on") {
    $_POST['active']="t";
  } else {
    $_POST['active']="f";
  }

  if ($_POST['nat'] == "on") {
    $_POST['nat']="yes";
  } else {
    $_POST['nat']="no";
  }

  if ($_POST['canreinvite'] == "on") {
    $_POST['canreinvite']="yes";
  } else {
    $_POST['canreinvite']="no";
  }
  
%>
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=5 CLASS=heading-body>New User(s) Created</TH>
</TR>
<TR CLASS=list-color1>
<TH CLASS=heading-body2 ALIGN=LEFT>Account Num</TH>
<TH CLASS=heading-body2 ALIGN=LEFT>Password</TH>
<TH CLASS=heading-body2 ALIGN=LEFT>VM Pin</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>Credit</TH>
<TH CLASS=heading-body2 ALIGN=RIGHT>Refund</TH>
</TR><%


  for($acnum=1;$acnum <= $_POST['bulk'];$acnum++) {
    $cno=cardnum();	
    $vmpass=cardpin();
    $lpass=randpwgen(8);
    if ($_POST['bulk'] > 1) {
      $bnameout=$_POST['bname'] . " - " . $cno;
    } else {
      $bnameout=$_POST['bname'];
    }
    pg_query($db,"INSERT INTO users (name,defaultuser,fromuser,mailbox,secret,password,credit,tariff,
                                     activated,usertype,fullname,email,agentid,qualify,nat,canreinvite) VALUES (
                                     '$cno','$cno','$cno','$cno','$lpass','$vmpass','0','" . $_POST['tariff'] . "','" . $_POST['active'] . "',
                                     '1','$bnameout','" . $_POST['email'] . "'," . $_SESSION['resellerid'] . ",'yes',
                                     '" . $_POST['nat'] . "','" . $_POST['canreinvite'] . "')");
    if ($_POST['aloccredit'] > 0) {
      $rcred=pg_query("SELECT credit,exchangerate,description,credit*oratio-rcallocated FROM reseller WHERE id='" . $_SESSION['resellerid'] . "'");
      $ccred=pg_fetch_row($rcred,0);

      $credin=$_POST['aloccredit'];
      $credit=floor($_POST['aloccredit']/$ccred[1]*10000);

      if (($ccred[3] < $credit) && ($ccred[3] > 0)){
        $credit=$ccred[3];
      } else if ($ccred[3] < 0) {
        $credit=0;
      }
      $credout=floor($credit*$ccred[1])/10000;
      $credin=sprintf("%0.2f",$credin-$credout);
      $udetail=pg_query("SELECT fullname,id FROM users WHERE name='" . $cno . "'");
      $r=pg_fetch_row($udetail,0);

      pg_query("UPDATE reseller SET rcallocated=rcallocated + " . $credit . " WHERE id = '" . $_SESSION['resellerid'] . "'");
      pg_query("INSERT INTO logrefill (credit,card_id,reseller_id) VALUES (" . $credit . "," . $r[1] . "," . $_SESSION['resellerid'] . ")");
      pg_query("UPDATE users SET credit=" . $credit . " WHERE name = '" . $cno . "'");
      pg_query("INSERT INTO sale (saletime,credit,username,cardid,saletype,discount) VALUES (now()," . $credit . ",'" . $r[0] . "'," . $r[1] . ",'Account Topup',0)");
    }

    if (($_POST['cbnum'] > 0) && ($_POST['bulk'] == 1)) {
      pg_query($db,"INSERT INTO callerid (cid,username) VALUES ('" . $_POST['cbnum'] . "','" . $cno . "')");
    }
    if ($_POST['iaxline'] == "on") {
      pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $cno . "','IAXLine','1')");
    }
    if ($_POST['DDIPASS'] == "on") {
      pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $cno . "','DDIPASS','1')");
    }
    $rowcol=$acnum%2;%>
      <TR CLASS=list-color<%print $rowcol+1;%>>
        <TD ALIGN=LEFT><%print $cno;%></TD>
        <TD ALIGN=LEFT><%print $lpass;%></TD>
        <TD ALIGN=LEFT><%print $vmpass;%></TD>
        <TD ALIGN=RIGHT><%printf("R%0.2f",$credout);%></TH>
        <TD ALIGN=RIGHT><%printf("R%0.2f",$credin);%></TD>
      </TR><%
  }
  print "</TABLE>";
} else {
  $tplan=pg_query($db,"SELECT tariffname,tariffcode FROM tariff WHERE tariffcode LIKE '" . $_SESSION['resellerid'] . "-%' ORDER BY tariffname");
%>
<FORM METHOD=POST NAME=adduf onsubmit="ajaxsubmit(this.name);return false">
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Create A New User Account</TH>
</TR>
<TR CLASS=list-color1>
  <TD WIDTH=50%>Tariff Plan</TD>
  <TD WIDTH=50% ALIGN=LEFT><SELECT NAME=tariff><%
  $num=pg_num_rows($tplan);
  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_array($tplan,$i,PGSQL_NUM);
    print "<OPTION VALUE=\"" . $r[1] . "\">" . $r[0] . "</OPTION>\n";
  }%>
</SELECT></TD></TR>
<TR CLASS=list-color2>
  <TD>Acount Holders Name</TD>
  <TD><INPUT TYPE=TEXT NAME=bname></TD></TR>
<TR CLASS=list-color1>
  <TD>Email Address To Send Voicemail</TD>
  <TD><INPUT TYPE=TEXT NAME=email></TD></TR>
<TR CLASS=list-color2>
  <TD>Initial Call Back Caller Id</TD>
  <TD><INPUT TYPE=TEXT NAME=cbnum></TD></TR>
<TR CLASS=list-color1>
  <TD>Initial Credit On Account</TD>
  <TD><INPUT TYPE=TEXT NAME=aloccredit></TD></TR>
<TR CLASS=list-color2>
  <TD>Bulk Create (No. Of Accounts)</TD>
  <TD><INPUT TYPE=TEXT NAME=bulk></TD></TR>
<TR CLASS=list-color1>
  <TD>Use IAX Protocol</TD>
  <TD><INPUT TYPE=CHECKBOX NAME=iaxline></TD></TR>
<TR CLASS=list-color2>
  <TD>Activate Account</TD>
  <TD><INPUT TYPE=CHECKBOX NAME=active></TD></TR>
<TR CLASS=list-color1>
  <TD>Set NAT Flag</TD>
  <TD><INPUT TYPE=CHECKBOX NAME=nat></TD></TR>
<TR CLASS=list-color2>
  <TD>Allow Reinvite</TD>
  <TD><INPUT TYPE=CHECKBOX NAME=canreinvite CHECKED></TD></TR>
<TR CLASS=list-color1>
  <TD>Use DDI Pass ON</TD>
  <TD><INPUT TYPE=CHECKBOX NAME=DDIPASS></TD></TR>
<TR CLASS=list-color2>
<TD COLSPAN=2 ALIGN=MIDDLE><INPUT TYPE=SUBMIT NAME=adduser VALUE="Add User"></TD></TR>
</TABLE>
</FORM><%
}%>
