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
if ((isset($_POST['startses'])) && ($_POST['cno'] != "")) {
  $_SESSION['credit']=abs($_SESSION['credit']);
  $creditin=$_SESSION['credit'];
  $cardid=pg_query($db,"SELECT users.uniqueid,agentid,exchangerate,(reseller.credit*oratio)-rcallocated FROM users LEFT OUTER join reseller ON (agentid=reseller.id) WHERE name = '" . $_POST['cno'] . "'");
  $r=pg_fetch_array($cardid,0);

  $rcredit=(10000*$_SESSION['credit'])/$r[2];

  if ($r[3] < 0) {
    $r[3]=0;
  }

  if ($rcredit > $r[3]) {
    $rcredit=$r[3];
    $_SESSION['credit']=($rcredit*$r[2])/10000;
  }

  pg_query($db,"INSERT INTO sale (cardid,username,saletime,saletype,credit,discount)
                       VALUES ('" . $r[0] . "','" . $_POST['sesname'] . "',localtimestamp,'Session Start'," . $_SESSION['credit'] . " * 100,'0')");
  pg_query($db,"UPDATE users SET  activated='t',credit = credit + " . $rcredit . " WHERE name='" . $_POST['cno'] . "'");
  pg_query("UPDATE reseller SET rcallocated=rcallocated+" . $rcredit . " WHERE id=" . $r[1]);
  $sesboothq=pg_query($db,"SELECT fullname FROM users WHERE name='" . $_POST['cno'] . "' LIMIT 1");
  $sesbooth=pg_fetch_array($sesboothq,0,PGSQL_NUM);

%>
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=2 class="heading-body">Session Started</TH>
</TR>
<TR CLASS=list-color1>
<TD ALIGN=LEFT WIDTH=50%>Booth</TD>
<TD WIDTH=50% ALIGN=LEFT><%print $sesbooth[0];%></TD>
<TR CLASS=list-color2>
<TD ALIGN=LEFT WIDTH=50%>Number</TD>
<TD WIDTH=50% ALIGN=LEFT><%print $_POST['cno'];%></TD>
<TR CLASS=list-color1>
<TD ALIGN=LEFT WIDTH=50%>Credit Assigned</TD>
<TD WIDTH=50% ALIGN=LEFT><%printf("R%0.2f",$_SESSION['credit']);%></TD>
<TR CLASS=list-color2>
<TD ALIGN=LEFT WIDTH=50%>Refund</TD>
<TD WIDTH=50% ALIGN=LEFT><%printf("R%0.2f",$creditin-$_SESSION['credit']);%></TD>
<TR CLASS=list-color1>
<TD ALIGN=LEFT WIDTH=50%>Session ID</TD>
<TD WIDTH=50% ALIGN=LEFT><%print $_POST['sesname'];%></TD>
</TR>
</TABLE>
<%
} else {
  $credavail=pg_query($db,"SELECT (((credit*oratio)-rcallocated)*exchangerate)/10000 FROM reseller WHERE id = '" . $resellownid . "'");
  $cav=pg_fetch_array($credavail,0);
/*
  if ($cav[0] < 0) {
    $cav[0]=0;
  }
*/
%>
<FORM METHOD=POST>
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=2 class="heading-body">Start A New Session</TH>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT WIDTH=50%>Available Booth</TD>
  <TD WIDTH=50% ALIGN=LEFT><SELECT NAME=cno><%
  $sesbooth=pg_query($db,"SELECT users.name,fullname 
                          FROM users 
                          LEFT OUTER JOIN reseller ON (reseller.id = " . $_SESSION['resellerid'] . " OR owner = " . $_SESSION['resellerid'] . ") 
                          WHERE ((agentid=reseller.id AND admin = 't' AND reseller.id=" . $_SESSION['resellerid'] . ") OR
                                 (agentid=owner AND admin = 'f' AND reseller.id=" . $_SESSION['resellerid'] . ")) 
                                 AND activated='f' AND usertype=2
                            ORDER BY fullname");
  $num=pg_num_rows($sesbooth);
  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_array($sesbooth,$i,PGSQL_NUM);
    print "<OPTION VALUE=\"" . $r[0] . "\">Booth " . $r[1] . " (" . $r[0] . ")</OPTION>\n";
  }%>
</SELECT></TD></TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT>Customer Name (Session ID)</TD>
  <TD><INPUT TYPE=TEXT NAME=sesname></TD>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT>Credit To Assign (R)<BR>R<%printf("%0.2f",$cav[0]);%> Avail.</TD>
  <TD><INPUT TYPE=TEXT NAME=credit></TD>
<TR CLASS=list-color2>
<TD COLSPAN=2 ALIGN=MIDDLE><INPUT TYPE=SUBMIT NAME=startses VALUE="Start Session">
</TABLE>
</FORM><%
}%>
