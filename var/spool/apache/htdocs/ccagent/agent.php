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

include_once "/var/spool/apache/htdocs/reception/auth.inc";

$bcolor[0]=" CLASS=list-color2";
$bcolor[1]=" CLASS=list-color1";

require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
$agi=new AGI_AsteriskManager();
$agi->connect("127.0.0.1","admin","admin");

function hangupactchan($callleg) {
  global $db,$agi;

  $actchan=pg_query($db,"SELECT channel FROM agent WHERE exten='" . $_SERVER['PHP_AUTH_USER'] . "'");
  list($agentchan)=pg_fetch_array($actchan,0);
  if ($agentchan != "") {
    $chans=$agi->command("soft hangup " . $agentchan);
  }
}

if ((isset($_POST['senddata'])) || (isset($_POST['transcall']))) {
  $datain_tb="contactdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid'];
  $testdb=pg_query($db,"SELECT * from information_schema.tables where table_catalog='asterisk' and table_name='" . $datain_tb . "'");
  if (pg_num_rows($testdb) > 0) {
    $testdbtbl=pg_query($db,"SELECT column_name,data_type,character_maximum_length from information_schema.columns where table_catalog='asterisk' and table_name='" .$datain_tb . "' AND (column_name != 'contid' AND column_name != 'leadid' AND column_name != 'osticket' AND column_name != 'id')");

    $numeric=array('smallint','integer','bigint','numeric','real','double precision');
    for($numcnt=0;$numcnt<count($numeric);$numcnt++) {
      $isnum[$numeric[$numcnt]]=true;
    }

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
        } else if ($coldata[1] == "timestamp with time zone") {
          if (ereg ("^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}:[0-9]{2}:[0-9]{2})([-\+0-9]+)$", $_POST[$pname], $dateinf)) {
            if (checkdate($dateinf[2],$dateinf[3],$dateinf[1])) {
              $qfields.="," . $coldata[0];
              $qvals.=",'" . $_POST[$pname] . "'"; 
              unset($_POST[$pname]);
            }
          } else if (ereg ("^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}:[0-9]{2}:[0-9]{2})$", $_POST[$pname], $dateinf)) {
            if (checkdate($dateinf[2],$dateinf[3],$dateinf[1])) {
              $qfields.="," . $coldata[0];
              $qvals.=",'" . $_POST[$pname] . "'";
              unset($_POST[$pname]);
            }
          } else if (ereg ("^([0-9]{4})-([0-9]{2})-([0-9]{2})$", $_POST[$pname], $dateinf)) {
            if (checkdate($dateinf[2],$dateinf[3],$dateinf[1])) {
              $qfields.="," . $coldata[0];
              $qvals.=",'" . $_POST[$pname] . "'";
              unset($_POST[$pname]);
            }
          }
        } else if ($isnum[$coldata[1]]) {
          $qfields.="," . $coldata[0];
          if (($_POST[$pname] > 0) || ($_POST[$pname] < 0)) {
            $qvals.="," . $_POST[$pname];
          } else {
            $qvals.=",0";
          }
          unset($_POST[$pname]);
        } else {
          print_r($coldata);
        }
      }
    }
    $contquery="INSERT INTO " . $datain_tb . " (leadid,contid" . $qfields . ") VALUES (" . $_SESSION['nextid'] . "," . $_SESSION['lastcon'] . $qvals . ")";
    pg_query($db,$contquery);
  }
  list($closed,$status)=explode("|",$_POST['CONT_status']);
  pg_query($db,"UPDATE contact SET closed='" . $closed . "',status='" . $status . "',followup='" . (($_POST['CONT_followup'] == "on")?"t":"f") . "',feedback='" . pg_escape_bytea($db,$_POST['CONT_feedback']) . "' WHERE contact.id=" . $_SESSION['lastcon']);
  if (isset($_POST['senddata'])) {
    hangupactchan(2);
  } else {
    hangupactchan(1);
  }
  sleep(1);
  unset($_SESSION['lastcon']);
  $incall=false;
  unset($_SESSION['nextnum']);
} else if (isset($_POST['abortcall'])) {
  hangupactchan(2);
  pg_query($db,"DELETE FROM contact WHERE contact.id=" . $_SESSION['lastcon']);
  unset($_SESSION['lastcon']);
  unset($_SESSION['nextnum']);
  $incall=false;
}  else if (isset($_SESSION['lastcon'])) {
  $actcall=pg_query($db,"SELECT contact.id FROM contact left outer join cdr using (uniqueid) where contact.uniqueid is not null and cdr.uniqueid is null and contact.id = " . $_SESSION['lastcon']);
  if (isset($_POST['nextcon'])) {
    hangupactchan();
    sleep(1);
    $incall=false;
  } else if (pg_num_rows($actcall) <= 0) {
    unset($_SESSION['lastcon']);
    $incall=false;
  } else {
    $incall=true;
  }
} else {
  $incall=false;
}

if (!isset($_SESSION['lastcon'])) {
   $getcallq="SELECT lead.id,list.id,campaign.id,list.information,agent.id,transfer,directdial,
                     CAST(random()*100 AS integer) AS rweight
                FROM lead 
                  LEFT OUTER JOIN list ON (lead.list=list.id) 
                  LEFT OUTER JOIN campaign ON (list.campaign = campaign.id)
                  LEFT OUTER JOIN contact ON (lead.lastcontact = contact.id) 
                  LEFT OUTER JOIN agentlist ON (agentlist.listid = list.id) 
                  LEFT OUTER JOIN agent ON (agent.id=agentlist.agentid) 
                WHERE  agent.exten='" . $exten . "' AND agentlist.active AND 
                       (contact.datetime + interval '1 second ' * list.dialretry < now() OR contact.datetime is null) AND 
                       lead.availfrom < CAST(now() AS time) AND lead.availtill >  CAST(now() AS time) AND
                       campaign.active AND list.active AND lead.active AND (contact.closed IS NULL OR NOT contact.closed)
               ORDER BY campaign.priority DESC,list.priority DESC,list.callbefore,contact.datetime DESC,rweight DESC";

//  print $getcallq . "\n";
  $callq=pg_query($db,$getcallq);
  list($_SESSION['nextid'],$_SESSION['listid'],$_SESSION['campid'],$script,$_SESSION['agentid'],$allowtrans,$allowdial)=pg_fetch_array($callq,0);
  if ($_SESSION['nextid'] != "") {
    pg_query($db,"INSERT INTO contact (lead,agent) VALUES (" . $_SESSION['nextid'] . "," . $_SESSION['agentid'] . ")");
    $qlastid=pg_query($db,"SELECT id from contact where lead=" . $_SESSION['nextid'] . " order BY datetime DESC  LIMIT 1");
    list($_SESSION['lastcon'])=pg_fetch_array($qlastid,0);
    pg_query("UPDATE lead SET lastcontact=" . $_SESSION['lastcon'] . " WHERE id=" . $_SESSION['nextid']);
  }
//  unset($_SESSION['nextnum']);
} else {
  $callq=pg_query($db,"SELECT information,transfer,directdial from list where id=" . $_SESSION['listid']);
  list($script,$allowtrans,$allowdial)=pg_fetch_array($callq,0);
}

$script = pg_unescape_bytea($script);	
$script = str_replace(array("\n","\"","<",">"), array("\\n","\\\"","\<","\>"), $script);

$data_tb="inputdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid'];
$c_plugin="plugin_" . $_SESSION['campid'] . "_" . $_SESSION['listid'] . ".inc";
$datain_tb="contactdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid'];

$testdb=pg_query($db,"SELECT * from information_schema.tables where table_catalog='asterisk' and table_name='" . $data_tb . "'");
$xtracol="";
$rows=array("Title","First Name","Surname","Number");

if (pg_num_rows($testdb) > 0) {
  $testdbtbl=pg_query($db,"SELECT column_name,data_type,character_maximum_length,fname from information_schema.columns left outer join field_names ON (tablename=table_name AND column_name=field) where table_catalog='asterisk' and table_name='" .$data_tb . "' AND (column_name != 'leadid' AND column_name != 'osticket' AND column_name != 'id')");
  for($dtrcnt=0;$dtrcnt < pg_num_rows($testdbtbl);$dtrcnt++) {
    list($trown,$trowt,$trows,$fname)=pg_fetch_array($testdbtbl,$dtrcnt);
    array_push($rows,($fname == "")?$trown:$fname);
    $xtracol.=",xtradata." . $trown;
  }
}

if ($xtracol != "") {
  if (!isset($_SESSION['nextnum'])) {
    $numtouse="number";
    $_SESSION['nextnum']=0;
  } else {
    $numtouse="number" .  $_SESSION['nextnum'];
  }
  $leadinf=pg_query($db,"SELECT number1,number2,number3,number4,title,fname,sname," . $numtouse . $xtracol . " FROM lead LEFT OUTER JOIN " . $data_tb . " AS xtradata ON (lead.id = xtradata.leadid) WHERE lead.id=" . $_SESSION['nextid']);
}
$leaddata=pg_fetch_array($leadinf,0,PGSQL_NUM);
$numdata=array_slice($leaddata,0,4);
array_splice($leaddata,0,4);

if ($numdata[$_SESSION['nextnum']] != "") {
  $_SESSION['nextnum']++;
} else {
  unset($_SESSION['nextnum']);
}

%><CENTER>
<FORM NAME=ccagentf METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<%print $showpage;%>">
<INPUT TYPE=HIDDEN NAME=nomenu VALUE="<%print $_POST['nomenu'];%>">
<TABLE border=0 width=90% cellspacing=0 cellpadding=0><%
  if ($_SESSION['nextid'] != "") {%>
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
<%
    if (is_file("/var/spool/apache/htdocs/ccagent/" . $c_plugin)) {
      include "/var/spool/apache/htdocs/ccagent/" . $c_plugin;%>
      <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
        <TH CLASS=heading-body2 COLSPAN=4>
          <%print $plug_t;%>
        </TH>
      </TR>
      <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
        <TD COLSPAN=4>
            <DIV ID=plugin>
            <%plug_div();%>
            </DIV>
        </TD>
      </TR><%
    }
%>
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TH CLASS=heading-body2 COLSPAN=4>
        Contact Script
      </TH>
    </TR>
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TD COLSPAN=4>
          <DIV ID=script></DIV>
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
        <SELECT NAME=CONT_status><%
          $statusq=pg_query($db,"SELECT option,closed FROM status WHERE listid=" . $_SESSION['listid'] . " AND campid=" . $_SESSION['campid'] . " ORDER BY closed,option");
          for($statcnt=0;$statcnt < pg_num_rows($statusq);$statcnt++) {
            list($status,$statusid)=pg_fetch_array($statusq,$statcnt,PGSQL_NUM);
            print "<OPTION VALUE=\"" . $statusid . "|" . $status . "\">" . $status . "</OPTION>\n"; 
          }
%>
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
        <TEXTAREA NAME=CONT_feedback ROWS=8 COLS=60></TEXTAREA>
      </TD>
    </TR>
<%} else {%>
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TH COLSPAN=4 CLASS=heading-body>
        There Are Currently No Calls
      </TH>
    </TR>
<%}%>
  <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
    <TD ALIGN=MIDDLE COLSPAN=4><%
      if ($_SESSION['nextid'] != "") {
        $jtrans=($allowtrans == "t")?"true":"false"; 
        $jdial=($allowdial == "t")?"true":"false";%>
        <INPUT TYPE=BUTTON NAME=dialbut onclick=ccdial(<%print $jtrans . "," . $jdial;%>) VALUE="Dial"><%
      }%>
      <INPUT TYPE=SUBMIT onclick=this.name='abortcall' VALUE="Hangup/Cancel"><%
      if ($allowtrans == "t") {%>
        <INPUT TYPE=BUTTON NAME=transbut onclick=transcall('<%print $_SESSION['lastcon'];%>') VALUE="Transfer"><%
      }
      if ($allowdial == "t") {%>
        <INPUT TYPE=BUTTON NAME=ddialbut onclick=directdial() VALUE="Direct Dial"><%
      }
      if ($_SESSION['nextid'] != "") {%>
        <INPUT TYPE=SUBMIT NAME=subme onclick=this.name='senddata' VALUE="Complete"><%
      }
      if ($_SESSION['nextnum'] != "") {%>
        <INPUT TYPE=SUBMIT NAME=nextconbut onclick=this.name='nextcon' VALUE="Next Contact"><%
      }%>

    </TD>
  </TR><%
  if ($_SESSION['nextid'] != "") {%>
    <TR<%print $bcolor[$rcnt % 2];$rcnt++;%>>
      <TH CLASS=heading-body2 COLSPAN=4>
        Previous 20 Contacts
      </TH>
    </TR><%

    $testdb=pg_query($db,"SELECT * from information_schema.tables where table_catalog='asterisk' and table_name='" . $datain_tb . "'");
    $xtracol="";
    $outrows=array();
    $xdatt=array();
    if (pg_num_rows($testdb) > 0) {
      $testdbtbl=pg_query($db,"SELECT column_name,data_type,character_maximum_length,fname from information_schema.columns left outer join field_names ON (tablename=table_name AND column_name=field) where table_catalog='asterisk' and table_name='" . $datain_tb . "' AND column_name != 'contid' AND column_name != 'leadid' AND column_name != 'id'");
      for($dtrcnt=0;$dtrcnt < pg_num_rows($testdbtbl);$dtrcnt++) {
        list($trown,$trowt,$trows,$fname)=pg_fetch_array($testdbtbl,$dtrcnt);
        array_push($outrows,($fname == "")?$trown:$fname);
        array_push($xdatt,$trowt);
        $xtracol.=",xtradata." . $trown;
      }
    }
    if ($xtracol == "") {
      $conthistq="SELECT status,followup,feedback,date_trunc('second',datetime),fullname||' ('||name||')' from contact LEFT OUTER JOIN agent ON (contact.agent = agent.id) LEFT OUTER JOIN users ON (agent.exten = users.name) where status != 'INIT' AND lead=" . $_SESSION['nextid'] . " and contact.id != " . $_SESSION['lastcon'] . " order by datetime  DESC LIMIT 20";
    } else {
      $conthistq="SELECT status,followup,feedback,date_trunc('second',datetime),fullname||' ('||name||')'" . $xtracol . " from contact LEFT OUTER JOIN agent ON (contact.agent = agent.id) LEFT OUTER JOIN users ON (agent.exten = users.name) LEFT OUTER JOIN " . $datain_tb . " AS xtradata ON (xtradata.contid = contact.id) where status != 'INIT' AND lead=" . $_SESSION['nextid'] . " and contact.id != " . $_SESSION['lastcon'] . " order by datetime  DESC LIMIT 20";
    }
    $lastcontq=pg_query($db,$conthistq);

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
          Agent :
          <%print $contd[4];%><BR><%
          for($xdat=5;$xdat<count($outrows)+5;$xdat++) {
            if ($xdatt[($xdat-5)] == "boolean") {
              $contd[$xdat]=($contd[$xdat] == "t")?"Yes":"No";
            }
            print $outrows[($xdat-5)] . " : " . $contd[$xdat] . "<BR>";
          }
      %></TH>
        <TD  COLSPAN=2 VALIGN=TOP ALIGN=LEFT>
          <%print htmlspecialchars($contd[2]);%>
        </TH>
      </TR><%
    }
  }
%>
</TABLE>
</FORM>
<FORM NAME=caller METHOD=POST ACTION=/ccagent/caller.php>
<INPUT TYPE=HIDDEN NAME=numtocall VALUE="<%print $_SESSION['lastcon'];%>">
<INPUT TYPE=HIDDEN NAME=contactnum VALUE="<%print $leaddata[3];%>">
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<%print $showpage;%>">
<INPUT TYPE=HIDDEN NAME=nomenu VALUE="<%print $_POST['nomenu'];%>">
</FORM>
<%
if (function_exists("plug_form")) {
  plug_form();
}
%>
<script language="JavaScript" src="/xmlscript.js" type="text/javascript"></script>
<SCRIPT>
<%
if (function_exists("plug_js")) {
  plug_js();
}
%>
  parser=new DOMParser();
  var xmlDoc = parser.parseFromString("<%print $script;%>",'text/xml');
  loadhtml("script", xmlDoc, false);
<%
if ($incall) {
  print "document.ccagentf.dialbut.disabled=true;\n";
  print "document.ccagentf.subme.disabled=false;\n";
  if ($allowdial == "t") {
    print "document.ccagentf.ddialbut.disabled=true;\n";
  }
  if ($allowtrans == "t") {
    print "document.ccagentf.transbut.disabled=false;\n";
  }
} else {
  print "document.ccagentf.dialbut.disabled=false;\n";
  print "document.ccagentf.subme.disabled=true;\n";
  if ($allowdial == "t") {
    print "document.ccagentf.ddialbut.disabled=false;\n";
  }
  if ($allowtrans == "t") {
    print "document.ccagentf.transbut.disabled=true;\n";
  }
}
%>
</SCRIPT>
