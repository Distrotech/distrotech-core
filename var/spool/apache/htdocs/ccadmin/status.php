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
  $data_tb=strtolower($_SESSION['campid'] . "_" . $_POST['listid']);
  $_SESSION['listid']=$_POST['listid'];
} else if (($_SESSION['campid'] != "") && ($_SESSION['listid'] != "") && (isset($_POST['updb']))) {
  if ($_POST['status'] != "") {
    $_POST['closed']=($_POST['closed'] == "on")?"t":"f";
    pg_query("INSERT INTO status (listid,campid,option,closed) VALUES (" . $_SESSION['listid'] . "," . $_SESSION['campid'] . ",'" . $_POST['status'] . "','" . $_POST['closed'] . "')");
  }
  $fieldnq="SELECT id FROM status WHERE listid=" . $_SESSION['listid'] . "AND campid=" . $_SESSION['campid'];
  $testdbtbl=pg_query($db,$fieldnq);
  for ($delcnt=0;$delcnt < pg_num_rows($testdbtbl);$delcnt++) {
    list($delid)=pg_fetch_array($testdbtbl,$delcnt,PGSQL_NUM);
    $delname="del" . $delid;
    if ($_POST[$delname] == "on") {
      pg_query("DELETE FROM status WHERE id=" . $delid);      
    }
  }
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
      <%print _("Select A Campaign To Edit Field Names");%>
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
} else if ((isset($_SESSION['campid'])) && (!isset($_SESSION['listid']))) {
    $getlist=pg_query($db,"SELECT id,description FROM list WHERE campaign=" . $_SESSION['campid'] . "ORDER by description");%>
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TH COLSPAN=2 CLASS=heading-body>
        <%print _("Select List To Edit Fields From Campaign") . " " . $_SESSION['campname'];%>
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
         <%print _("Editing Status Codes For") . " " . $_SESSION['listname'] . " " . _("List For Campaign") . " " . $_SESSION['campname'];%>
      </TH>
    </TR><%
    $testdbtbl=pg_query($db,"SELECT id,option,closed FROM status WHERE listid=" . $_SESSION['listid'] . "AND campid=" . $_SESSION['campid']);
    if (pg_num_rows($testdbtbl) > 0) {%>
      <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
        <TH COLSPAN=3 CLASS=heading-body2>Existing Information</TH>
      </TR>
      <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
        <TH ALIGN=LEFT WIDTH=15% CLASS=heading-body2>
          Delete
        </TH>
        <TH ALIGN=LEFT CLASS=heading-body2>
          Status Option
        </TH>
        <TH ALIGN=LEFT CLASS=heading-body2>
          Close Contact
        </TH>
      </TR><%
    }
    for($dtrcnt=0;$dtrcnt < pg_num_rows($testdbtbl);$dtrcnt++) {
      list($delid,$doption,$closec)=pg_fetch_array($testdbtbl,$dtrcnt);%>
      <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
        <TD>
          <INPUT TYPE=CHECKBOX NAME="del<%print $delid;%>">
        </TD>
        <TD>
          <%print $doption;%>
        </TD>
        <TD>
          <%print (($closec == "t")?"Yes":"No");%>
        </TD>
      </TR>
<%  }%>
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TH COLSPAN=3 CLASS=heading-body2>Add New Option</TH>
    </TR>
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TD WIDTH=50%>Add</TD>
      <TD>
        <INPUT TYPE=TEXT NAME=status>
      </TD>
        <TD>
          <INPUT TYPE=CHECKBOX NAME="closed">
        </TD>
    </TR>
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TD COLSPAN=3 ALIGN=MIDDLE>
        <INPUT TYPE=SUBMIT NAME=updb VALUE="<%print _("Update");%>">
      </TD>
    </TR>
<%
}
%>
</TABLE>
</FORM>

