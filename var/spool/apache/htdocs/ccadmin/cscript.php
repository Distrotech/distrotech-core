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

if ($_POST['mmap'] != "") {
  $getscript=pg_query($db,"SELECT campaign.description,campaign.name,list.description,information,htmlscript FROM list LEFT OUTER JOIN campaign ON (list.campaign = campaign.id) WHERE list.id=" . $_POST['mmap']);
  list($camp,$camps,$listn,$text,$htmlscript)=pg_fetch_array($getscript);
  $text=stripslashes(pg_unescape_bytea($text));
  $data_tb=strtolower("contactdata_" . $camps . "_" . $listn);
}

%>
<CENTER>
<TABLE border=0 width=90% cellspacing=0 cellpadding=0>
  <TR CLASS=list-color2>
    <TH CLASS=heading-body>
      <%print _("Script For") . " " . $camp . " (" . $camps . ") " . _("Campaign") . " [" . $listn . " " . _("List") . "]";%>
    </TH>
  </TR>
  <TR CLASS=list-color1>
    <TD>
<%
include "scriptp.inc";
print getscripthtml($text,$htmlscript);
%>
    </TD>
  </TR>
  <TR CLASS=list-color2>
    <TH CLASS=heading-body2>
      Database Table Setup <%print $data_tb;%>
    </TD>
  </TR>
  <TR CLASS=list-color1>
    <TD><%
      $testdb=pg_query($db,"SELECT * from information_schema.tables where table_catalog='asterisk' and table_name='" . $data_tb . "'");
      if (pg_num_rows($testdb) == 1) {
        print "Database Table Exists<BR>";
      } else {
        print "Creating Database Table<BR>";
        pg_query($db,"CREATE TABLE " . $data_tb . " (id bigserial,leadid bigint)");
      }
      $testdbtbl=pg_query($db,"SELECT column_name,data_type,column_default from information_schema.columns where table_catalog='asterisk' and table_name='" . $data_tb . "'");
      for($dtrcnt=0;$dtrcnt < pg_num_rows($testdbtbl);$dtrcnt++) {
        list($trown,$trowt,$trowd)=pg_fetch_array($testdbtbl,$dtrcnt);
        if ($trowt == "character varying") {
          $trowt=$varchart;
        }
        if ($dbrows[$trown] == $trowt) {
          unset($dbrows[$trown]);
          print "Database Field " . $trown . " Is Ok<BR>";
        } else {
          print "Database Field " . $trown . " Is Bad Type " . $trowt . "<BR>";
        }
      }
      while(list($trow,$ttype) = each($dbrows)) {
        pg_query("ALTER TABLE " . $data_tb . " ADD " . $trow . " " . $ttype);
        print "Adding Database Field " . $trow . "<BR>";
      }
%>
    </TD>
  </TR>
</TABLE>
