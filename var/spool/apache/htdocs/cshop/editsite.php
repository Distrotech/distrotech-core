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

%>
<CENTER>
<FORM METHOD=POST NAME=editvsite onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<INPUT TYPE=HIDDEN NAME=account VALUE="">

<%
if (!isset($_POST['companyid'])) {
  $vc=pg_query("SELECT companyid,description||' ('||contact||' '||email||')' FROM virtualcompany WHERE
                       resellerid = " . $_SESSION['resellerid'] . "ORDER BY description");
%>
  <TR CLASS=list-color2>
  <TH COLSPAN=2 CLASS=heading-body>Edit Virtual PBX Nodes</TH>
  </TR>
  <TR CLASS=list-color1>
  <TD WIDTH=50%>Select Virtual Company To Edit</TD>
  <TD VALIGN=MIDDLE>
  <SELECT NAME=companyid><%
  for ($i=0; $i < pg_num_rows($vc); $i++) {
    $r = pg_fetch_row($vc,$i);
    print  "<OPTION VALUE=" . $r[0] . ">" . $r[1] . "</OPTION>\n";
  }%>
  </SELECT></TD></TR>
  <TR CLASS=list-color2><TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT></TH></TR>
<%
} elseif (isset($_POST['account']) && ($_POST['account'] != "")) {
  if ($_POST['destmatch'] != "") {
    if ($_POST['deststrip'] == "") {
      $_POST['deststrip']=0;
    }
    pg_query($db,"INSERT INTO intersite (destmatch,destpre,deststrip,companyid,dest) VALUES " .
                    "('" . $_POST['destmatch'] . "','" . $_POST['destpre'] . "'," . $_POST['deststrip'] . "," .
                    $_POST['companyid'] . ",'" . $_POST['account'] . "')");
  }

  $vc=pg_query("SELECT description||' ('||fullname||' ['||name||'])' FROM virtualcompany " .
                  "LEFT OUTER JOIN companysites USING (companyid) " .
                  "LEFT OUTER JOIN users ON (source=name) " .
                  "WHERE companyid = " . $_POST['companyid'] . " AND name='" . $_POST['account'] . "'");
  list($vcname) = pg_fetch_row($vc,$i);
  $vcname=htmlentities($vcname);

  $vsiteq="SELECT destmatch,destpre,deststrip,isiteid from intersite where companyid = " . $_POST['companyid'] . " and dest='" . $_POST['account'] . "'  ORDER BY destmatch,destpre,deststrip";
  $vsite=pg_query($db, $vsiteq);

  $bcolor[0]="list-color1";
  $bcolor[1]="list-color2";

  print "<INPUT TYPE=HIDDEN NAME=companyid VALUE=" . $_POST['companyid'] . ">";
  print "<INPUT TYPE=HIDDEN NAME=account VALUE=" . $_POST['account'] . ">";

%>
  <TR CLASS=list-color2>
  <TH COLSPAN=4 CLASS=heading-body>Editing Virtual Site For <%print $vcname;%></TH>
  </TR>
  <TR CLASS=list-color1>
  <TH ALIGN=LEFT CLASS=heading-body2>Delete</TH>
  <TH ALIGN=LEFT CLASS=heading-body2>Destination Match</TH>
  <TH ALIGN=LEFT CLASS=heading-body2>Add Prefix</TH>
  <TH ALIGN=LEFT CLASS=heading-body2>Strip Digits</TH></TR>
  <TR CLASS=list-color2>
  <TD>(Add)</TD>
    <TD><INPUT NAME=destmatch></TD>
    <TD><INPUT NAME=destpre></TD>
    <TD><INPUT NAME=deststrip></TD></TR>
<%
  $cnt=0;
  for ($i=0; $i < pg_num_rows($vsite); $i++) {
    $r = pg_fetch_row($vsite,$i);
    $todel="del" . $r[3];
    if (isset($_POST[$todel])) {
      pg_query($db, "DELETE FROM intersite WHERE isiteid=" . $r[3] . " AND companyid='" . $_POST['companyid'] . "'");
    } else {
      $rem=$cnt % 2;
      print "<TR CLASS=" . $bcolor[$rem] . ">";
      print "<TD><INPUT TYPE=CHECKBOX NAME=\"" . $todel . "\"></TD>";
      print "<TD>" . $r[0] . " </TD><TD>" . $r[1] . "" . "</TD><TD>" . $r[2] . "</TD></TR>";
      $cnt++;
    }
  }
  $rem=$cnt % 2;

  $rem=$cnt % 2;
  print "<TR CLASS=" . $bcolor[$rem] . "><TD ALIGN=MIDDLE COLSPAN=4><INPUT TYPE=SUBMIT></TH></TR>";
} else {
  if (isset($_POST['source']) && ($_POST['source'] != "")) {
    pg_query($db,"INSERT INTO companysites (companyid,source,creditpool) VALUES " .
                   "('" . $_POST['companyid'] . "','" . $_POST['source'] . "'," . $_POST['poolid'] . ")");
  }

  $vc=pg_query("SELECT description||' ('||contact||' <'||email||'>)' FROM virtualcompany WHERE companyid = " . $_POST['companyid']);
  list($vcname) = pg_fetch_row($vc,$i);
  $vcname=htmlentities($vcname);
  $vsiteq="SELECT source,fullname,cpool.description,cpool.poolid FROM companysites " .
                    "LEFT OUTER JOIN users ON (name=source) " .
                    "LEFT OUTER JOIN creditpool AS cpool ON (cpool.companyid=companysites.companyid AND cpool.poolid=companysites.creditpool) " .
                    "WHERE companysites.companyid = " . $_POST['companyid'] . " ORDER BY fullname";
  $vsite=pg_query($db, $vsiteq);

  $bcolor[0]="list-color1";
  $bcolor[1]="list-color2";

  print "<INPUT TYPE=HIDDEN NAME=companyid VALUE=" . $_POST['companyid'] . ">";

  $poollist=pg_query($db,"SELECT poolid,description FROM creditpool WHERE companyid='" . $_POST['companyid'] . "'");
%>
  <SCRIPT>
    var usersearch=new TextComplete(document.editvsite.source,ldapautodata,'vsitesxml.php',setautosearchurl,document.editvsite,usersearch);
  </SCRIPT>
  <TR CLASS=list-color2>
  <TH COLSPAN=3 CLASS=heading-body>Editing Virtual Sites For <%print $vcname;%></TH>
  </TR>
  <TR CLASS=list-color1>
  <TH ALIGN=LEFT CLASS=heading-body2>Delete</TH>
  <TH ALIGN=LEFT CLASS=heading-body2>Node Info</TH>
  <TH ALIGN=LEFT CLASS=heading-body2>Credit Pool</TH></TR>
  <TR CLASS=list-color2>
  <TD>(Add)</TD><TD><INPUT NAME=source autocomplete=off SIZE=50></TD><TD><SELECT NAME=poolid>
    <OPTION VALUE="">None</OPTION>
<%
  for($p=0;$p < pg_num_rows($poollist);$p++) {
    $r=pg_fetch_array($poollist,$p);
    print "<OPTION VALUE=" . $r[0] . ">" . $r[1] . "</OPTION>";
  }
  print "</SELECT></TD></TR>";
  $cnt=0;
  for ($i=0; $i < pg_num_rows($vsite); $i++) {
    $r = pg_fetch_row($vsite,$i);
    $todel="del" . $r[0];
    if (isset($_POST[$todel])) {
      pg_query($db, "DELETE FROM companysites WHERE source='" . $r[0] . "' AND companyid='" . $_POST['companyid'] . "'");
    } else {
      $rem=$cnt % 2;
      print "<TR CLASS=" . $bcolor[$rem] . ">";
      print "<TD><INPUT TYPE=CHECKBOX NAME=\"" . $todel . "\"></TD>";
      print "<TD><A HREF=javascript:voipvcroute('" . $r[0] . "')>"  . $r[1] . " (" . $r[0] . ")" . "</A></TD>";
      print "<TD>" . (($r[2] == "") ? "(None)" : $r[2]) . "</TD></TR>";
      $cnt++;
    }
  }
  $rem=$cnt % 2;
  print "<TR CLASS=" . $bcolor[$rem] . "><TD ALIGN=MIDDLE COLSPAN=3><INPUT TYPE=SUBMIT></TH></TR>";
}
%>

</FORM>
</TABLE>

