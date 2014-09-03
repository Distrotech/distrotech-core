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

$prios=array("Low","Medium","High","Urgent");

if ($_POST['active'] == "on") {
  $_POST['active']='t';
} else if (isset($_POST['active'])) {
  $_POST['active']='f';
}

if ((isset($_POST['id'])) && (!isset($_POST['listid'])) && (!isset($_POST['updb']))) {
  $getid=pg_query($db,"SELECT id,description||' ('||name||')' FROM campaign LEFT OUTER JOIN camp_admin ON (campaign.id=camp_admin.campaign AND camp_admin.userid='" . $_SERVER['PHP_AUTH_USER'] . "') WHERE id=" .  $_POST['id']);
  list($_SESSION['campid'],$_SESSION['campname'])=pg_fetch_array($getid,0);
} else if (($_SESSION['campid'] != "") && ($_POST['listid'] != "") && (!isset($_POST['updb']))) {
  $data_tb=strtolower("inputdata_" . $_SESSION['campid'] . "_" . $_POST['listid']);
  $testdb=pg_query($db,"SELECT * from information_schema.tables where table_catalog='asterisk' and table_name='" . $data_tb . "'");
  if (pg_num_rows($testdb) < 1) {
    pg_query($db,"CREATE TABLE " . $data_tb . " (id bigserial,leadid bigint)");
    pg_query($db,"ALTER TABLE " . $data_tb . " ADD CONSTRAINT key_" . $data_tb . " PRIMARY KEY (id)");
    pg_query($db,"CREATE UNIQUE INDEX " . $data_tb . "_contact ON " . $data_tb . " USING btree (leadid)");
  }
  $_SESSION['listid']=$_POST['listid'];
} else if (($_SESSION['campid'] != "") && ($_SESSION['listid'] != "") && (isset($_POST['updb']))) {
  $data_tb=strtolower("inputdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid']);
  $testdbtbl=pg_query($db,"SELECT column_name,data_type,character_maximum_length from information_schema.columns where table_catalog='asterisk' and table_name='" . $data_tb . "' AND (column_name != 'osticket' AND column_name != 'leadid' AND column_name != 'id')");
  for($dtrcnt=0;$dtrcnt < pg_num_rows($testdbtbl);$dtrcnt++) {
    list($trown,$trowt,$trows)=pg_fetch_array($testdbtbl,$dtrcnt);
    $delname="del" . $trown;
    if (isset($_POST[$delname])) {
      pg_query($db,"ALTER TABLE " . $data_tb . " DROP " . $trown);
    }
  }
  pg_query($db,"ALTER TABLE " . $data_tb . " ADD " . $_POST['newfieldn'] . " " . $_POST['newfieldt']);
}


%>
<FORM NAME=ladmin METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<CENTER>
<TABLE border=0 width=90% cellspacing=0 cellpadding=0>
<%

if ((!isset($_POST['id'])) && (!isset($_POST['listid'])) && (!isset($_POST['updb']))) {
  unset($_SESSION['campid']);
  unset($_SESSION['listid']);
  $getcamp=pg_query($db,"SELECT id,description||' ('||name||')' FROM campaign LEFT OUTER JOIN camp_admin ON (campaign.id=camp_admin.campaign AND camp_admin.userid='" . $_SERVER['PHP_AUTH_USER'] . "')" . $_SESSION['limitadmin'] . " ORDER by description,name");%>
  <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
    <TH COLSPAN=2 CLASS=heading-body>
      <%print _("Select A Campaign To Modify");%>
    </TH>
  </TR>
  <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
    <TD WIDTH=50%>
      <%print _("Select Campaign To Configure Lists");%>
    </TD>
    <TD>
      <SELECT NAME=id onchange=ajaxsubmit(this.form.name)>
        <OPTION VALUE=""></OPTION><%
        for($ccnt=0;$ccnt<pg_num_rows($getcamp);$ccnt++) {
          list($cid,$cname)=pg_fetch_array($getcamp,$ccnt);%>
          <OPTION VALUE="<%print $cid;%>"><%print $cname%></OPTION><%
        }%>
    </TD>
  </TR><%
} else {
  if ((isset($_SESSION['campid'])) && (!isset($_SESSION['listid']))) {
    $getlist=pg_query($db,"SELECT id,description FROM list WHERE campaign=" . $_SESSION['campid'] . "ORDER by description");%>
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TH COLSPAN=2 CLASS=heading-body>
        <%print _("Select Input Format To Edit For Campaign") . " " . $_SESSION['campname'];%>
      </TH>
    </TR>
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TD WIDTH=50%>
        <%print _("Select List");%>
      </TD>
      <TD>
        <SELECT NAME=listid onchange=ajaxsubmit(this.form.name)>
          <OPTION VALUE=""></OPTION><%
          for($ccnt=0;$ccnt<pg_num_rows($getlist);$ccnt++) {
            list($cid,$cname)=pg_fetch_array($getlist,$ccnt);%>
            <OPTION VALUE="<%print $cid;%>"><%print $cname%></OPTION><%
          }%>
      </TD>
    </TR><%
  } else {%>
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TH COLSPAN=3 CLASS=heading-body>
         <%print _("Editing") . " " . $_SESSION['listname'] . " " . _("List For Campaign") . " " . $_SESSION['campname'];%>
      </TH>
    </TR><%
    $testdbtbl=pg_query($db,"SELECT column_name,data_type,character_maximum_length from information_schema.columns where table_catalog='asterisk' and table_name='" . $data_tb . "' AND (column_name != 'osticket' AND column_name != 'leadid' AND column_name != 'id')");
    if (pg_num_rows($testdbtbl) > 0) {%>
      <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
        <TH COLSPAN=3 CLASS=heading-body2>Existing Information</TH>
      </TR>
      <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
        <TH ALIGN=LEFT WIDTH=15% CLASS=heading-body2>
          Delete
        </TH>
        <TH ALIGN=LEFT CLASS=heading-body2>
          Field Name
        </TH>
        <TH ALIGN=LEFT CLASS=heading-body2>
          Data Type
        </TH>
      </TR><%
    }
    for($dtrcnt=0;$dtrcnt < pg_num_rows($testdbtbl);$dtrcnt++) {
      list($trown,$trowt,$trows)=pg_fetch_array($testdbtbl,$dtrcnt);
      if ($trowt == "character varying") {
        $trowt.="(" . $trows . ")";
      }%>
      <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
        <TD>
          <INPUT TYPE=CHECKBOX NAME="del<%print $trown;%>">
        </TD>
        <TD>
          <%print $trown;%>
        </TD>
        <TD>
          <%print $trowt;%>
        </TD>
      </TR>
<%  }%>
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TH COLSPAN=3 CLASS=heading-body2>Add New Information To Table</TH>
    </TR>
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TH ALIGN=LEFT WIDTH=15% CLASS=heading-body2>&nbsp;
      </TH>
      <TH ALIGN=LEFT CLASS=heading-body2>
        Field Name
      </TH>
      <TH ALIGN=LEFT CLASS=heading-body2>
        Data Type
      </TH>
    </TR>
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TD ALIGN=MIDDLE>&nbsp;
      </TD>
      <TD ALIGN=LEFT>
        <INPUT TYPE=TEXT NAME=newfieldn>
      </TD>
      <TD ALIGN=LEFT>
        <SELECT NAME=newfieldt>
          <OPTION VALUE="varchar(512)">Text <512 Characters</OPTION>
          <OPTION VALUE="varchar(256)">Text <256 Characters</OPTION>
          <OPTION VALUE="varchar(128)">Text <128 Characters</OPTION>
          <OPTION VALUE="varchar(64)">Text <64 Characters</OPTION>
          <OPTION VALUE="varchar(32)">Text <32 Characters</OPTION>
          <OPTION VALUE="varchar(16)">Text <16 Characters</OPTION>
          <OPTION VALUE="text" SELECTED>Text (Variable)</OPTION>
          <OPTION VALUE="smallint">Integer +/- 32768</OPTION>
          <OPTION VALUE="integer">Integer +/- 2147483647</OPTION>
          <OPTION VALUE="bigint">Integer +/- 9223372036854775807</OPTION>
          <OPTION VALUE="numeric">Variable Numeric</OPTION>
          <OPTION VALUE="decimal">Variable Decimal</OPTION>
          <OPTION VALUE="real">Floating Point 6 decimial Precision</OPTION>
          <OPTION VALUE="float8">Floating Point 15 decimial Precision</OPTION>
          <OPTION VALUE="boolean">Yes/No</OPTION>
          <OPTION VALUE="time">Time Without Zone (00:00:00)</OPTION>
          <OPTION VALUE="time with time zone">Time With Zone (00:00:00)</OPTION>
          <OPTION VALUE="timestamp">Date And Time Without Zone (YYYY-MM-DD 00:00:00)</OPTION>
          <OPTION VALUE="timestamp with time zone">Date And Time With Zone (YYYY-MM-DD 00:00:00)</OPTION>
        </SELECT>
      </TD>
    </TR> 
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TD COLSPAN=3 ALIGN=MIDDLE>
        <INPUT TYPE=SUBMIT NAME=updb VALUE="<%print _("Update");%>">
      </TD>
    </TR>
<%}
}
%>
</TABLE>
</FORM>

