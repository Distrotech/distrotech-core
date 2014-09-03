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

require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
$agi=new AGI_AsteriskManager();
$agi->connect("127.0.0.1","admin","admin");

function hangupactchan($callleg) {
  global $db,$agi;

  $actchan=pg_query($db,"SELECT contact.channel from contact left outer join cdr using (uniqueid) where cdr.uniqueid is null and contact.channel is not null AND contact.id=" . $_SESSION['lastcon']);
  list($agentchan)=pg_fetch_array($actchan,0);
  if ($agentchan != "") {
    $chans=$agi->command("soft hangup " . $agentchan . ",$callleg");
  }
}

if ((isset($_POST['senddata'])) || (isset($_POST['transcall']))) {
  $datain_tb="contactdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid'];
  $testdb=pg_query($db,"SELECT * from information_schema.tables where table_catalog='asterisk' and table_name='" . $datain_tb . "'");
  if (pg_num_rows($testdb) > 0) {
    $testdbtbl=pg_query($db,"SELECT column_name,data_type,character_maximum_length from information_schema.columns where table_catalog='asterisk' and table_name='" .$datain_tb . "' AND (column_name != 'contid' AND column_name != 'leadid' AND column_name != 'id')");
    $qvals="";
    $qfields="";
    for($dtrcnt=0;$dtrcnt < pg_num_rows($testdbtbl);$dtrcnt++) {
      $coldata=pg_fetch_array($testdbtbl,$dtrcnt,PGSQL_NUM);
      $pname="SCRIPT_" . $coldata[0];
      if (isset($_POST[$pname])) {
        if ($coldata[1] == "character varying") {
          $qvals.=",'" . pg_escape_string($db,substr($_POST[$pname],0,$coldata[2])) . "'";
          $qfields.="," . $coldata[0];
          unset($_POST[$pname]);
        } else if ($coldata[1] == "text") {
          $qvals.=",'" . pg_escape_bytea($db,$_POST[$pname]) . "'";
          $qfields.="," . $coldata[0];
          unset($_POST[$pname]);
        } else if ($coldata[1] == "boolean") {
          if ($_POST[$pname] == "on") {
            $qvals.=",'t'";
          } else {
            $qvals.=",'f'";
          }
          unset($_POST[$pname]);
          $qfields.="," . $coldata[0];
        } else {
          print_r($coldata);
        }
      }
    }
    pg_query($db,"INSERT INTO " . $datain_tb . " (leadid,contid" . $qfields . ") VALUES (" . $_SESSION['nextid'] . "," . $_SESSION['lastcon'] . $qvals . ")");
  }
  pg_query($db,"UPDATE contact SET status='" . $_POST['CONT_status'] . "',followup='" . (($_POST['CONT_followup'] == "on")?"t":"f") . "',feedback='" . pg_escape_bytea($db,$_POST['CONT_feedback']) . "' WHERE id=" . $_SESSION['lastcon']);
  if (isset($_POST['senddata'])) {
    hangupactchan(2);
  } else {
    hangupactchan(1);
  }
} else if (isset($_POST['abortcall'])) {
  hangupactchan(2);
  pg_query($db,"DELETE FROM contact WHERE id=" . $_SESSION['lastcon']);
}
%>

<%
/*
 * lead.availfrom < now() AND lead.availtill < now() AND lead.active
 */
$callq=pg_query($db,"
SELECT lead.id,list.id,campaign.id,list.information,CAST(random()*100 AS integer) as rweight from lead left outer join list on (lead.list=list.id) left outer join campaign on (list.campaign = campaign.id) left outer join contact on (lead.lastcontact = contact.id) where (contact.datetime + interval '30 minutes' < now() OR contact.datetime is null) AND campaign.active AND list.active order by campaign.priority DESC,list.priority DESC,list.callbefore,contact.datetime DESC,rweight DESC");
$status=array("OPEN","CLOSED");


list($_SESSION['nextid'],$_SESSION['listid'],$_SESSION['campid'],$script)=pg_fetch_array($callq,0);
pg_query($db,"INSERT INTO contact (lead) VALUES (" . $_SESSION['nextid'] . ")");
$qlastid=pg_query($db,"SELECT id from contact where lead=" . $_SESSION['nextid'] . " order BY datetime DESC  LIMIT 1");
list($_SESSION['lastcon'])=pg_fetch_array($qlastid,0);
//pg_query("UPDATE lead SET lastcontact=" . $_SESSION['lastcon'] . " WHERE id=" . $_SESSION['nextid']);

$data_tb="inputdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid'];
$testdb=pg_query($db,"SELECT * from information_schema.tables where table_catalog='asterisk' and table_name='" . $data_tb . "'");
$xtracol="";
$rows=array("Title","First Name","Surname","Number");
if (pg_num_rows($testdb) > 0) {
  $testdbtbl=pg_query($db,"SELECT column_name,data_type,character_maximum_length from information_schema.columns where table_catalog='asterisk' and table_name='" .$data_tb . "' AND (column_name != 'leadid' AND column_name != 'id')");
  for($dtrcnt=0;$dtrcnt < pg_num_rows($testdbtbl);$dtrcnt++) {
    list($trown,$trowt,$trows)=pg_fetch_array($testdbtbl,$dtrcnt);
    array_push($rows,$trown);
    $xtracol.=",xtradata." . $trown;
  }
}
if ($xtracol != "") {
  $leadinf=pg_query($db,"SELECT title,fname,sname,number" . $xtracol . " FROM lead LEFT OUTER JOIN " . $data_tb . " AS xtradata ON (lead.id = xtradata.leadid) WHERE lead.id=" . $_SESSION['nextid']);
}
$leaddata=pg_fetch_array($leadinf,0,PGSQL_NUM);%>

<CENTER>
<FORM NAME=ccagentf METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<TABLE border=0 width=90% cellspacing=0 cellpadding=0>
  <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
    <TH COLSPAN=4 CLASS=heading-body>
      <%print $leaddata[0] . " " . $leaddata[1] . "  " . $leaddata[2] . " [" . $leaddata[3] . "]";%>
    </TH><%
  for ($cell=4;$cell < count($leaddata);$cell++) {
    if ((($cell-4) % 2) == 0) {
      print "</TR><TR" . $bcolor[$rcnt % 2] . ">";
      $rcnt++;
    }
    print "<TH CLASS=heading-body2 ALIGN=LEFT WIDTH=15%>" . $rows[$cell] . "</TH><TD WIDTH=35%>" . $leaddata[$cell] . "</TD>";
  }
  if ((($cell-4) % 2) == 1) {
    print "<TD COLSPAN=2>&nbsp</TD></TR>";
  } else {
    print "</TR>";
  }%>
  <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
    <TH CLASS=heading-body2 COLSPAN=4>
      Contact Script
    </TH>
  </TR>
  <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
    <TD COLSPAN=4>
<%
      include "/var/spool/apache/htdocs/ccadmin/scriptp.inc";
      print getscripthtml(stripslashes(pg_unescape_bytea($script)));
%>  
    </TD>
  </TR>
  <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
    <TH CLASS=heading-body2 COLSPAN=4>
      Contact Information
    </TH>
  </TR>
  <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
    <TH CLASS=heading-body2 COLSPAN=2 ALIGN=LEFT>
      Status
    </TH>
    <TD COLSPAN=2>
      <SELECT NAME=CONT_status>
        <OPTION VALUE="OPEN">OPEN</OPTION>
        <OPTION VALUE="CLOSED">CLOSED</OPTION>
      </SELECT>
    </TD>
  </TR>
  <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
    <TD CLASS=heading-body2 COLSPAN=2 ALIGN=LEFT>
      Further Followup Required
    </TH>
    <TD COLSPAN=2>
      <INPUT TYPE=CHECKBOX NAME=CONT_followup>
    </TD>
  </TR>
  <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
    <TH CLASS=heading-body2 COLSPAN=2 ALIGN=LEFT>
      Date/Time Of Next Contact (Leave Blank For Default) [YYYY-MM-DD HH:MM:SS] 
    </TH>
    <TD COLSPAN=2>
      <INPUT TYPE=INPUT NAME=CONT_nextcall>
    </TD>
  </TR>
  <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
    <TH CLASS=heading-body2 COLSPAN=2 ALIGN=LEFT>
      Feedback
    </TH>
    <TD COLSPAN=2>
      <TEXTAREA NAME=CONT_feedback ROWS=8 COLS=80></TEXTAREA>
    </TD>
  </TR>
  <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
    <TD ALIGN=MIDDLE COLSPAN=4>
      <INPUT TYPE=SUBMIT onclick=this.name='senddata' VALUE="Process">
      <INPUT TYPE=SUBMIT NAME=subme onclick=this.name='abortcall' VALUE="Hangup">
      <INPUT TYPE=BUTTON onclick=transcall('<%print $_SESSION['lastcon'];%>') VALUE="Transfer">
      <INPUT TYPE=BUTTON onclick=directdial() VALUE="Direct Dial">
    </TD>
  </TR>
  <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
    <TH CLASS=heading-body2 COLSPAN=4>
      Previous 20 Contacts
    </TH>
  </TR><%
  $lastcontq=pg_query($db,"SELECT status,followup,feedback,date_trunc('second',datetime) from contact where status != 'INIT' AND lead=" . $_SESSION['nextid'] . " and id != " . $_SESSION['lastcon'] . " order by datetime  DESC LIMIT 20");
  for($pcont=0;$pcont < pg_num_rows($lastcontq);$pcont++) {
    $contd=pg_fetch_array($lastcontq,$pcont,PGSQL_NUM);%>
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TD COLSPAN=2>
        Date :
        <%print $contd[3];%><BR>
        Status :
        <%print $contd[0];%><BR>
        Followup :
        <%print ($contd[1] == "t")?"Yes":"No";%><BR>
      </TH>
      <TD  COLSPAN=2 VALIGN=TOP ALIGN=LEFT>
        <%print htmlspecialchars($contd[2]);%>
      </TH>
    </TR>

<%
  }
%>
</TABLE>
</FORM>
<FORM NAME=caller METHOD=POST ACTION=/ccagent/caller.php>
<INPUT TYPE=HIDDEN NAME=numtocall VALUE="<%print $_SESSION['lastcon'];%>">
</FORM>
<SCRIPT>
ccaccept();
</SCRIPT>
