<% 
if (! $db) {
  include "/var/spool/apache/htdocs/cshop/auth.inc";
}

if ((isset($_POST['vladmin'])) || ((isset($_POST['pbxupdate'])) && (isset($_POST['exten'])))){
  if (!isset($_POST['exten'])) {
    $_POST['exten']=$_SESSION['number'];
  }
  $SUPER_USER=1;
%>
<FORM METHOD=POST NAME=extenform>
<INPUT TYPE=HIDDEN NAME=delext>
<%
  extract($_POST,  EXTR_OVERWRITE);
  include "/var/spool/apache/htdocs/cdr/vladmin.php";
  return;
}

if (isset($_POST['saveuser'])) {
  if ($_POST['acact'] == "on") {
    $_POST['acact']="t";
  } else {
    $_POST['acact']="f";
  }

  if (!$_POST['ivrwarn'] > 0) {
    $_POST['ivrwarn']=-1;
  } else {
    $_POST['ivrwarn']=sprintf("%d",$_POST['ivrwarn']*10000);
  }
  pg_query("UPDATE users SET password='" . $_POST['userpass'] . "',secret='" . $_POST['linepass'] . "',fullname='" . $_POST['firstname'] . "',ivrwarn=" . $_POST['ivrwarn'] .
        ",tariff='" . $_POST['tariff'] . "',callerid= '" . $_POST['defcli'] . "',email= '" . $_POST['email'] . "',activated = '" . $_POST['acact'] . 
        "',simuse='" .  $_POST['simuse'] . "' WHERE name='" . $_SESSION['number'] . "'");
} else if ($_POST['deluser'] == "1") {
  $boothq=pg_query($db,"SELECT id,credit
                     FROM users 
                     WHERE usertype=1 AND name='" . $_SESSION['number'] . "'
                           AND agentid = " . $_SESSION['resellerid'] . " LIMIT 1");
  $booth=pg_fetch_row($boothq,0);
  pg_query($db,"UPDATE reseller set rcallocated=rcallocated-" . $booth[1] . " WHERE id = " . $_SESSION['resellerid']);
  pg_query("DELETE FROM users WHERE name='" . $_SESSION['number'] . "'");
  pg_query("DELETE FROM astdb WHERE family='" . $_SESSION['number'] . "'");
  pg_query("DELETE FROM features WHERE exten='" . $_SESSION['number'] . "'");
  include "getbooth.php";
  return;
}


$boothq=pg_query($db,"SELECT password,fullname,tariff,email,activated,simuse,ivrwarn,callerid,secret
                     FROM users
                     WHERE usertype=1 AND users.name='" . $_SESSION['number'] . "'
                           AND agentid = " . $_SESSION['resellerid'] . " LIMIT 1");

$user=pg_fetch_row($boothq,0);
%>
<CENTER> 
<FORM NAME=edituser METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%> 
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Editing <%print $_SESSION['number'];%></TH></TR>
<TR CLASS=list-color1>
<TD>Pin</TD><TD><INPUT TYPE=TEXT NAME=userpass VALUE="<%print $user[0];%>"></TD></TR>
<TR CLASS=list-color2>
<TD>Line Password</TD><TD><INPUT TYPE=TEXT NAME=linepass VALUE="<%print $user[8];%>"></TD></TR>
<TR CLASS=list-color1>
<TD>User Name</TD><TD><INPUT TYPE=TEXT NAME=firstname VALUE="<%print $user[1];%>"></TD></TR>
<TR CLASS=list-color2>
<TD>Email Address</TD><TD><INPUT TYPE=TEXT NAME=email VALUE="<%print $user[3];%>"></TD></TR>
<TR CLASS=list-color1>
<TD>Default Caller ID</TD><TD><INPUT TYPE=TEXT NAME=defcli VALUE="<%print $user[7];%>"></TD></TR>
<TR CLASS=list-color2>
<TD>Simuse</TD><TD><INPUT TYPE=TEXT NAME=simuse VALUE="<%print $user[5];%>"></TD></TR>
<TR CLASS=list-color1>
<TD>Warn User When Call Goes Bellow</TD><TD>
<INPUT TYPE=TEXT NAME=ivrwarn VALUE="<%if ($user[6] > 0) {printf("%0.2f",$user[6]/10000);} else if ($user[6] < 0) {print "";} else { print "0";}%>"></TD></TR>
<TR CLASS=list-color2>
<TD>Activated</TD><TD><INPUT TYPE=CHECKBOX NAME=acact<%if ($user[4] == "t") {print " CHECKED";};%>></TD></TR>
<TR CLASS=list-color1>
<TD>Rate Plan</TD><TD><SELECT NAME=tariff><%
  $tplan=pg_query($db,"SELECT tariffname,tariffcode FROM tariff WHERE tariffcode LIKE '" .
                       $_SESSION['resellerid'] . "-%' ORDER BY tariffname");
  $num=pg_num_rows($tplan);
  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_array($tplan,$i,PGSQL_NUM);
    print "<OPTION VALUE=\"" . $r[1] . "\"";
    if ($user[2] == "$r[1]") {
      print " SELECTED";
    }
    print ">" . $r[0] . "</OPTION>\n";
  }%>
<%
%>
</SELECT>
</TD></TR>
<TR CLASS=list-color2>
<TD ALIGN=MIDDLE COLSPAN=2>
<INPUT TYPE=SUBMIT onclick=this.name='saveuser' VALUE="Save Changes">
<INPUT TYPE=SUBMIT onclick=this.name='vladmin' VALUE="Advanced">
<INPUT TYPE=BUTTON ONCLICK="deleteconf('This User',document.edituser,document.edituser.deluser)" VALUE="Delete">
<INPUT TYPE=HIDDEN NAME=deluser>
</TD></TR>
</TABLE>
</FORM>
