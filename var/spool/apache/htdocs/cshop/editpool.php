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

<%
if (!isset($_POST['companyid'])) {
  $vc=pg_query("SELECT companyid,description||' ('||contact||' '||email||')' FROM virtualcompany WHERE
                       resellerid = " . $_SESSION['resellerid'] . "ORDER BY description");
%>
  <TR CLASS=list-color2>
  <TH COLSPAN=2 CLASS=heading-body>Edit Virtual PBX Credit Pools</TH>
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
} else {
  if (isset($_POST['description']) && ($_POST['description'] != "")) {
    pg_query($db,"INSERT INTO creditpool (companyid,description) VALUES ('" . $_POST['companyid'] . "','" . $_POST['description'] . "')");
  }

  $vc=pg_query("SELECT description||' ('||contact||' <'||email||'>)' FROM virtualcompany WHERE companyid = " . $_POST['companyid']);
  list($vcname) = pg_fetch_row($vc,$i);
  $vcname=htmlentities($vcname);
  $vsite=pg_query("SELECT poolid,description FROM creditpool WHERE companyid = " . $_POST['companyid'] . "ORDER BY description");

  $bcolor[0]="list-color1";
  $bcolor[1]="list-color2";

  print "<INPUT TYPE=HIDDEN NAME=companyid VALUE=" . $_POST['companyid'] . ">";
%>
  <TR CLASS=list-color2>
  <TH COLSPAN=2 CLASS=heading-body>Editing Credit Pools For <%print $vcname;%></TH>
  </TR>
  <TR CLASS=list-color1>
  <TH ALIGN=LEFT CLASS=heading-body2>Delete</TH>
  <TH ALIGN=LEFT CLASS=heading-body2>Pool Description</TH></TR>
  <TR CLASS=list-color2>
  <TD>(Add)</TD><TD><INPUT NAME=description SIZE=50></TD>
  </TR>
<%
  $cnt=0;
  for ($i=0; $i < pg_num_rows($vsite); $i++) {
    $r = pg_fetch_row($vsite,$i);
    $todel="del" . $r[0];
    if (isset($_POST[$todel])) {
      pg_query($db, "DELETE FROM creditpool WHERE poolid='" . $r[0] . "' AND companyid='" . $_POST['companyid'] . "'");
    } else {
      $rem=$cnt % 2;
      print "<TR CLASS=" . $bcolor[$rem] . ">";
      print "<TD><INPUT TYPE=CHECKBOX NAME=\"" . $todel . "\"></TD><TD>"  . $r[1] . "</TD></TR>";
      $cnt++;
    }
  }
  $rem=$cnt % 2;
  print "<TR CLASS=" . $bcolor[$rem] . "><TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT></TH></TR>";
}
%>

</FORM>
</TABLE>

