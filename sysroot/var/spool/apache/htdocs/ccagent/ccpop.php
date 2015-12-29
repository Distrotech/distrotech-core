<?php
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

include "/var/spool/apache/htdocs/reception/auth.inc";

include_once "/var/spool/apache/htdocs/session.inc";
$sessid=session_id();
if ($sessid == "") {
  ob_start('ob_gzhandler');
  session_name("agent_" . $_SERVER['PHP_AUTH_USER']);
  session_set_cookie_params(28800);
  session_start();
}

if (!isset($_SERVER['HTTP_REFERER'])) {
  pg_close($db);
  ?>
  <html><head>
      <link rel="stylesheet" href="/style.php?sesname=server_admin">
      <script type="text/javascript" src="/ajax.js"></script>
      <script language="JavaScript" src="/autocomplete.js" type="text/javascript"></script>
  </head>
  <body class=popup>
    <DIV ID=main-body CLASS=popup>
      <form name="pform2" method="POST">
        <input name="start" type="HIDDEN">
      </form>
      <div id=poll>
      </div>
      <script>
        var ajaxcaller=new XMLHttpRequest();
        AJAX.setupajax('poll','ccpop.php','pform','pform2');
        AJAX.start();
        function autocompletedata(dom) {
          var tmparray=new Array();
          var userdat=dom.getElementsByTagName("contact");
          for(var i=0;i < userdat.length;i++) {
            tmparray[userdat[i].getAttribute('id')]=userdat[i].firstChild.nodeValue;
          }
          return tmparray;
        }
        function ajaxsubmit() {
          acbox = document.getElementById('acshadow');
          if (acbox != null) {
            while (acbox.hasChildNodes()) {
              while (acbox.firstChild.hasChildNodes()) {
                noten=acbox.firstChild.firstChild;
                acbox.firstChild.removeChild(noten);
              }
              noten=acbox.firstChild;
              acbox.removeChild(noten);
            }
            acbox.style.visibility='hidden';
            document.body.removeChild(acbox);
          }
          document.getElementById('poll').style.cursor='wait';
          AJAX.senddata('poll','pform','ccpop.php');
        }
        function setcompleteurl(pform) {
          return 'fname='+pform.searchby.value+'&fvalue';
        }
        function directdial() {
          var extento=prompt('Enter number to dial ?')
          if ((extento != null) && (extento != '')) {
            document.getElementById('script').style.visibility='visible';
            document.getElementById('script').style.height=document.getElementById('stable').clientHeight;
            document.pform.dialbut.disabled=true;
            document.pform.ddialbut.disabled=true;
            document.pform.subme.disabled=true;
            document.pform.transbut.disabled=false;
            ajaxcall(document.caller,'directdial='+escape(extento),ajaxcaller);
          }
        }
        function ccdial() {
          document.getElementById('script').style.visibility='visible';
          document.getElementById('script').style.height=document.getElementById('stable').clientHeight;
          document.pform.dialbut.disabled=true;
          document.pform.ddialbut.disabled=true;
          document.pform.subme.disabled=false;
          document.pform.transbut.disabled=false;
          ajaxcall(document.caller,'numtocall='+escape(document.caller.numtocall.value),ajaxcaller);
        }
        function resetcall() {
          document.pform.dialbut.disabled=!document.pform.dialbut.disabled;
          document.pform.ddialbut.disabled=!document.pform.ddialbut.disabled;
          document.pform.transbut.disabled=!document.pform.transbut.disabled;
          document.pform.subme.disabled=!document.pform.subme.disabled;
        }
        function transcall(contid) {
          var extento=prompt('Enter extension to transfer to ?')
          if ((extento != null) && (extento != '')) {
            ajaxcall(document.caller,'transfer='+contid+'&extento='+escape(extento),ajaxcaller);
            if ((document.pform.dialbut.disabled) && (document.pform.ddialbut.disabled) && (document.pform.subme.disabled)) {
              document.pform.dialbut.disabled=false;
              document.pform.ddialbut.disabled=false;
            }
          }
        }
        function ajaxcallresp() {
          if ((ajaxcaller.readyState == 4) && (ajaxcaller.status == 200)) {
//            alert(ajaxcaller.responseText);
          }
        }
        function editcell(cellname) {
          editdiv = document.getElementById('edit_'+cellname);
          var newval=prompt('Enter New Value',editdiv.innerHTML)
          if ((newval != null) && (newval != '')) {
            document.celledit.newvalue.value=newval;
            document.celledit.cellname.value=cellname;
            AJAX.senddata('edit_'+cellname,'celledit','/ccagent/celledit.php')
          }
        }
        function editcon(cellname) {
          editdiv = document.getElementById('editcon_'+cellname);
          var newval=prompt('Enter New Value',editdiv.innerHTML)
          if ((newval != null) && (newval != '')) {
            document.celledit.newvalue.value=newval;
            document.celledit.cellname.value=cellname;
            AJAX.senddata('editcon_'+cellname,'celledit','/ccagent/conedit.php')
          }
        }

        function ajaxcall(callform,formdat,ajax) {
          if (ajax.readyState > 0) {
            ajax.abort();
          } 
          ajax.onreadystatechange=ajaxcallresp;
          ajax.open(callform.method,callform.action,true);
          ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
          ajax.setRequestHeader("Content-length", formdat.length);
          ajax.setRequestHeader("Connection", "close");
          ajax.send(formdat);
        } 
      </script>
    </div>
  </body>
  </html><?php
  exit();
}

    
$mydb = mysql_connect('localhost', 'admin', 'admin');
mysql_select_db("osticket",$mydb);

function setactcall($gotocall=false) {
  global $db;
  $agentinf=agentquery($_SERVER['PHP_AUTH_USER']);
  if ((is_array($agentinf)) && ($agentinf['Status'] >= 0)) {
    if ($agentinf['Status'] == 1) {
    $actcall=chanstatus("Agent/" . $_SERVER['PHP_AUTH_USER']);
      $_SESSION['incall']=true;
    } else {
      $_SESSION['incall']=false;
    }
  } else {
    $actcall=chanstatus($_SERVER['PHP_AUTH_USER']);
    if (is_array($actcall)) {
      $_SESSION['incall']=true;
    } else {
      $_SESSION['incall']=false;
    }
  }
  if (($_SESSION['incall']) && ($gotocall)) {
    $getact=pg_query($db,"SELECT lead,id from contact where uniqueid = '" . $actcall['Uniqueid'] . "'");
    $getact=pg_fetch_row($getact,0,PGSQL_NUM);
    showcontact($getact);
    return $getact;
  } else {
    return null;
  }
}

function hangupactchan() {
  $agentinf=agentquery($_SERVER['PHP_AUTH_USER']);
  if ((is_array($agentinf)) && ($agentinf['Status'] >= 0)) {
    if ($agentinf['Status'] == 1) {
      $apisock=apiopen();
      apihangupchan("Agent/" . $_SERVER['PHP_AUTH_USER'],$apisock);
      apiclose($apisock);
    }
  } else {
    $actcall=chanstatus($_SERVER['PHP_AUTH_USER']);
    if (is_array($actcall)) {
      $apisock=apiopen();
      apihangupchan(($actcall['Link'] != "")?$actcall['Link']:$actcall['Channel'],$apisock);
      apiclose($apisock);
    }
  }
}

function sendhtml($data) {
  global $rcol;
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TD COLSPAN=2><PRE>" . $data . "</PRE></TD></TR>";
  $rcol++;
}

$rcol=1;

function listsel($qno) {
  global $db,$rcol;

  if (setactcall(true)) {
    return -1;
  }

  if ($qno != "") {
    $getlistq="SELECT list.description||' ('||campaign.description||')',list.id,campaign.id from list 
                 left outer join campaign on (list.campaign=campaign.id) 
                 LEFT OUTER JOIN agentlist ON (list.id=agentlist.listid) 
                 left outer join agent ON (agent.id=agentlist.agentid) 
                where exten='" . $_SERVER['PHP_AUTH_USER'] . "' AND  list.active AND campaign.active AND agentlist.active AND ('" . $qno . "' ~ acdmatch) order by list.description";
  } else {
    $getlistq="SELECT list.description||' ('||campaign.description||')',list.id,campaign.id from list 
                 left outer join campaign on (list.campaign=campaign.id) 
                 LEFT OUTER JOIN agentlist ON (list.id=agentlist.listid) 
                 left outer join agent ON (agent.id=agentlist.agentid) 
                where exten='" . $_SERVER['PHP_AUTH_USER'] . "' AND  list.active AND campaign.active AND agentlist.active order by list.description";
  }
  $getlist=pg_query($db,$getlistq);
  $lcnt=pg_num_rows($getlist);
  if ($lcnt > 1) {
    print "<form name=pform method=POST onsubmit=\"ajaxsubmit();return false;\">\n";
    print "<CENTER><TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>";
    print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH CLASS=heading-body COLSPAN=2>Multiple Streams Available</TH></TR>";
    $rcol++;
    print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TD WIDTH=50%>Select Data Stream</TD><TD>";
    print "<INPUT TYPE=HIDDEN NAME=listsel VALUE=1>";
    print "<SELECT NAME=listid onchange=ajaxsubmit('pform')>\n<OPTION></OPTION>\n";
    $rcol++;
    for($rcnt=0;$rcnt<$lcnt;$rcnt++) {
      $r=pg_fetch_array($getlist,$rcnt,PGSQL_NUM);
      print "  <OPTION VALUE=\"" . $r[1] . "\">" . $r[0] . "</OPTION>\n";
    }
    print "</SELECT></TD></TR></TABLE></FORM>\n";
    return null;
  } else if ($lcnt == 1) {
    $r=pg_fetch_array($getlist,0,PGSQL_NUM);
    $_SESSION['listid']=$r[1];
    $_SESSION['campid']=$r[2];
    contactsearch();
    return $r[1];
  } else {
    print "<CENTER><TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>";
    print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH CLASS=heading-body COLSPAN=2>No Streams Available</TH></TR></TABLE>";
    $rcol++;
    return -1;
  }
}

function contactsearch() {
  global $db,$rcol,$mydb;

  if (setactcall(true)) {
    return -1;
  }

  print "<form name=pform method=POST onsubmit=\"ajaxsubmit();return false;\">\n";
  print "<CENTER><TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>";
  $campid=pg_query($db,"SELECT campaign,osticket,defsearch from list where id='" . $_SESSION['listid'] . "'");
  list($_SESSION['campid'],$osticket,$defsearch)=pg_fetch_array($campid,0,PGSQL_NUM);
  $dbname="inputdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid'];
  $fieldq="SELECT column_name,case when (field_names.fname is null) then column_name ELSE field_names.fname END from 
                          information_schema.columns left outer join field_names ON (tablename=table_name AND column_name=field) where table_catalog='asterisk' AND 
                          table_name='" . $dbname . "' AND column_name != 'leadid' AND column_name != 'osticket' AND column_name != 'id' AND data_type != 'boolean' ORDER BY field_names.fname,column_name";

  $fields=pg_query($db,$fieldq);
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH CLASS=heading-body COLSPAN=2>Search For Contact</TH></TR>";
  $rcol++;
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TD WIDTH=50%>Search By ...</TD><TD>";
  print "<SELECT NAME=searchby>\n";
  $rcol++;
  $cdata['number']="Contact Number";
  $cdata['fname']="First Name";
  $cdata['sname']="Last Name";
  $cdata['title']="Title";
  $cdata2=$cdata;
  while(list($field,$descrip)=each($cdata)) {
    print "<OPTION VALUE=\"lead." . $field .  "\"";
    if ($field == $defsearch) {
      print " SELECTED";
    }
    print ">" . $descrip . "</OPTION>\n";
  }

  for($rcnt=0;$rcnt<pg_num_rows($fields);$rcnt++) {
    $r=pg_fetch_array($fields,$rcnt,PGSQL_NUM);
    print "<OPTION VALUE=\"input." . $r[0] . "\">" . $r[1] . "</OPTION>\n";
  }
  print "</SELECT></TD></TR>";
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TD WIDTH=50%>Search For ...</TD><TD>";
  $rcol++;
  print "<INPUT NAME=searchfor SIZE=50 autocomplete=off></TD></TR>";
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TD CLASS=heading-body COLSPAN=2 ALIGN=MIDDLE>";
  $rcol++;
  print "<INPUT TYPE=SUBMIT onclick=this.name='abortcall' VALUE=\"Hangup/Cancel\">";
  print "<INPUT TYPE=SUBMIT onclick=this.name='cancelcall' VALUE=\"Cancel\">";
  print "</TD></TR>";

  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH CLASS=heading-body COLSPAN=2>Add New Contact</TH></TR>";
  $rcol++;

  while(list($field,$descrip)=each($cdata2)) {
    print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TD>" . $descrip . "</TD>";
    print "<TD><INPUT NAME=" . $field . "></TD></TR>";
    $rcol++;
  }

  for($rcnt=0;$rcnt<pg_num_rows($fields);$rcnt++) {
    $r=pg_fetch_array($fields,$rcnt,PGSQL_NUM);
    print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TD>" . $r[1] . "</TD>";
    print "<TD><INPUT NAME=" . $r[0] . "></TD></TR>";
    $rcol++;
  }

  if ($osticket == "t") {
    print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TD>OS Ticket Department</TD>";
    print "<TD><SELECT NAME=osticket>";
    $ost_dept = mysql_query("SELECT dept_id,dept_name FROM ost_department",$mydb);
    while($ostdat=mysql_fetch_array($ost_dept,MYSQL_NUM)) {
      print "<OPTION VALUE=\"" . $ostdat[0] . "\">" . $ostdat[1] . "</OPTION>\n";
    }
    print "</SELECT></TD></TR>";
    $rcol++;
  }

  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT ONCLICK=this.name='newcontact' VALUE=\"Add Contact\"></TD></TR>";
  $rcol++;

  print "</TABLE></FORM>\n";?>
  <SCRIPT>
    var contsearch=new TextComplete(document.pform.searchfor,autocompletedata,'contactxml.php',setcompleteurl,document.pform,ajaxsubmit);
  </SCRIPT><?php
}

include_once "/var/spool/apache/htdocs/cdr/apifunc.inc";
$sockf="/tmp/ccsocks/" . $_SERVER['PHP_AUTH_USER'] . ".sock";

if (isset($_POST['start'])) {
/*
 * Ajax Trigger Got
 */
  pg_close($db);
  unset($_POST['start']);
  if (file_exists($sockf)) {
    if (@socket_connect($socket,$sockf)) {
      exit();
    } else {
      $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
      unlink($sockf);
    }
  } else {
    $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
  }
  
  socket_bind($socket,$sockf);
  socket_listen($socket);

  socket_set_nonblock($socket);
  for($cnt=0;$cnt < 1800;$cnt++) {
    $sock2=socket_accept($socket);
    if ((!($sock2 === false)) || (socket_last_error($socket) > 0)) {
      break;
    }
    usleep(100000);
  }

  for(;;) {
    $wline=@socket_read($sock2,8192,PHP_NORMAL_READ);
    if ($wline == "") {
      break;
    }
    $wgets=rtrim($wline);
    if ($wgets != "") {
      list($key,$val)=preg_split("/: /",$wgets);
      $datout[$key]=$val;
    }
  }
  socket_close($socket);
  socket_close($sock2);
  unlink($sockf);
  if (count($datout) > 0) {
    include "/var/spool/apache/htdocs/cdr/auth.inc"; 
    listsel($datout['Queue']);
    exit();
  } else {
    exit();
  }
} else if (count($_POST) == 0) {
/*
 * UnSeen
 */
  if (file_exists($sockf)) {
    $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
    while(@socket_connect($socket,$sockf)) {
      socket_close($socket);
      $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
    }
    socket_close($socket);
  }
  listsel("");
} else if ((isset($_POST['listid'])) && (isset($_POST['listsel']))) {
  /*
  * Contact Search Form
  */
  $_SESSION['listid']=$_POST['listid'];
  contactsearch();
} else if (isset($_POST['newcontact'])) {
  if (($_POST['number'] == "") || ($_POST['fname'] == "") || ($_POST['sname'] == "") || ($_POST['title'] == "")) {
    contactsearch();
    exit();
  }

  unset($_POST['newcontact']);
  unset($_POST['searchby']);
  unset($_POST['searchfor']);

  $leaddat=array("number","fname","sname","title");
  $datv="";
  $datf="";
  $selq="";
  for($cnt=0;$cnt<count($leaddat);$cnt++) {
    $datf.=$leaddat[$cnt] . ",";
    $datv.="'" . $_POST[$leaddat[$cnt]] . "',";
    $selq.=" AND " . $leaddat[$cnt] . "='" . $_POST[$leaddat[$cnt]] . "'";
    unset($_POST[$leaddat[$cnt]]);
  }
  pg_query($db,"INSERT INTO lead (" . $datf . "list) VALUES (" . $datv . $_SESSION['listid'] . ")");
  $getlid=pg_query($db,"SELECT id FROM lead WHERE list=" . $_SESSION['listid'] . $selq);
  list($_SESSION['nextid'])=pg_fetch_array($getlid,0,PGSQL_NUM);

  $datv="";
  $datf="";
  while(list($key,$val)=each($_POST)) {
    if ($val != "") {
      $datf.=$key . ",";
      $datv.="'" . $val . "',";
    }
  }
  pg_query($db,"INSERT INTO inputdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid'] . " (" . $datf . "leadid) VALUES (" . $datv . $_SESSION['nextid'] . ")");

  showcontact();
} else if ((isset($_POST['searchby'])) && (isset($_POST['searchfor']))) {
  if (isset($_POST['abortcall'])) {
    $_POST=array();
    callhangup();
  } else if (isset($_POST['cancelcall'])) {
    $_POST=array();
    listsel("");
  } else {
    $_SESSION['nextid']=$_POST['searchfor'];
    unset($_POST['searchfor']);
    unset($_POST['searchby']);
    showcontact();
  }
} else if (isset($_POST['abortcall'])) {
  callhangup();
} else if ((isset($_POST['senddata'])) || (isset($_POST['transcall']))) {
  completecall();
} else {
  sendhtml(print_r($_POST,TRUE));
}

function callhangup() {
  hangupactchan();
  pg_query($db,"DELETE FROM contact WHERE contact.id=" . $_SESSION['lastcon']);
  unset($_SESSION['lastcon']);
  $_SESSION['incall']=false;
  listsel("");
}

function showcontact($condata=null) {
  global $db;

  if (!isset($_SESSION['agentid'])) {
    $agentq=pg_query($db,"SELECT agentid FROM agentlist LEFT OUTER join agent ON (agent.id=agentid) WHERE exten='" . $_SERVER['PHP_AUTH_USER'] . "' AND listid=" . $_SESSION['listid']);
    list($_SESSION['agentid'])=pg_fetch_array($agentq,0,PGSQL_NUM);
  }


  if (!$condata) {
    setactcall(false);
    pg_query($db,"INSERT INTO contact (lead,agent) VALUES (" . $_SESSION['nextid'] . "," . $_SESSION['agentid'] . ")");
    $qlastid=pg_query($db,"SELECT id from contact where lead=" . $_SESSION['nextid'] . " order BY datetime DESC  LIMIT 1");
    list($_SESSION['lastcon'])=pg_fetch_array($qlastid,0);
    pg_query("UPDATE lead SET lastcontact=" . $_SESSION['lastcon'] . " WHERE id=" . $_SESSION['nextid']);
  } else {
    list($_SESSION['nextid'],$_SESSION['lastcon'])=$condata;
    $_SESSION['incall']=true;
  }

  $bcolor[0]=" CLASS=list-color2";
  $bcolor[1]=" CLASS=list-color1";

  $data_tb="inputdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid'];
  $datain_tb="contactdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid'];

  $scriptq=pg_query($db,"SELECT information,htmlscript,osticket from list where id=" . $_SESSION['listid']);
  list($script,$htmlscript,$osticket)=pg_fetch_array($scriptq,0);
  unset($_SESSION['osticket']);

  $testdb=pg_query($db,"SELECT * from information_schema.tables where table_catalog='asterisk' and table_name='" . $data_tb . "'");
  $xtracol="";
  $rows=array("Title","First Name","Surname","Number");
  $rnme=array("title","fname","sname","number");

  if (pg_num_rows($testdb) > 0) {
    $testdbtbl=pg_query($db,"SELECT column_name,data_type,character_maximum_length,fname from information_schema.columns left outer join field_names ON (tablename=table_name AND column_name=field) where table_catalog='asterisk' and table_name='" .$data_tb . "' AND (column_name != 'leadid' AND column_name != 'id')");
    for($dtrcnt=0;$dtrcnt < pg_num_rows($testdbtbl);$dtrcnt++) {
      list($trown,$trowt,$trows,$fname)=pg_fetch_array($testdbtbl,$dtrcnt);
      array_push($rows,($fname == "")?$trown:$fname);
      array_push($rnme,$trown);
      $xtracol.=",xtradata." . $trown;
    }
  }

  if ($xtracol != "") {
    $leadinf=pg_query($db,"SELECT title,fname,sname,number" . $xtracol . " FROM lead LEFT OUTER JOIN " . $data_tb . " AS xtradata ON (lead.id = xtradata.leadid) WHERE lead.id=" . $_SESSION['nextid']);
  }
  $leaddata=pg_fetch_array($leadinf,0,PGSQL_NUM);?>

  <CENTER>
  <FORM NAME=pform METHOD=POST onsubmit="ajaxsubmit();return false">
  <TABLE border=0 width=90% cellspacing=0 cellpadding=0><?php

  if ($_SESSION['nextid'] != "") {?>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TH COLSPAN=4 CLASS=heading-body ALIGN=MIDDLE><TABLE CELLPADDING=0 CELLSPACING=0><TR><?php
        for($cell=0;$cell<4;$cell++) {
          $leddat[$cell]="<TH CLASS=heading-body><DIV ONCLICK=\"editcon('" . $rnme[$cell] . "')\" ID=editcon_" . $rnme[$cell] . " STYLE=\"cursor: pointer;text-decoration : underline;\" CLASS=heading-body>" . $leaddata[$cell] . "</DIV>";
          if ($cell < 2) {
            $leddat[$cell].="</TH><TH CLASS=heading-body>&nbsp;</TH>";
          } else if ($cell == 2) {
            $leddat[$cell].="</TH><TH CLASS=heading-body>&nbsp;[</TH>";
          } else if ($cell == 3) {
            $leddat[$cell].="</TH><TH CLASS=heading-body>]</TH>";
          }
        }
        print $leddat[0] . $leddat[1] . $leddat[2] . $leddat[3];
      ?></TR></TABLE></TH><?php
    $celloff=0;
    for ($cell=4;$cell < count($leaddata);$cell++) {
      if (($rnme[$cell] == "osticket") && ($osticket == "t")){
        $_SESSION['osticket']=$leaddata[$cell];
        $celloff=1;
        continue;
      }
      if ((($cell-4-$celloff) % 2) == 0) {
        print "</TR><TR" . $bcolor[$rcnt % 2] . ">";
        $rcnt++;
      }
      print "<TH CLASS=heading-body2 ALIGN=LEFT WIDTH=15%><A HREF=\"javascript:editcell('" . $rnme[$cell] . "')\">" . $rows[$cell] . "</A></TH>\n  <TD WIDTH=35%>\n";
      print "<DIV ID=edit_" . $rnme[$cell] . ">" . $leaddata[$cell] . "</DIV></TD>\n";
    }
    if ((($cell-4-$celloff) % 2) == 1) {
      print "<TD COLSPAN=2>&nbsp</TD></TR>";
    } else {
      print "</TR>";
    }?>
    </TABLE>
    <DIV ID=script>
    <TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90% ID=stable>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TH CLASS=heading-body2 COLSPAN=4>
        Contact Script
      </TH>
    </TR>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TD COLSPAN=4><?php
        include "/var/spool/apache/htdocs/ccadmin/scriptp.inc";
        print getscripthtml(stripslashes(pg_unescape_bytea($script)),$htmlscript);?> 
      </TD>
    </TR>
    </TABLE>
    </DIV>
    <TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TH CLASS=heading-body2 COLSPAN=4>
        Contact Information
      </TH>
    </TR>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
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
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TD CLASS=heading-body2 COLSPAN=2 ALIGN=LEFT>
        Further Followup Required
      </TH>
      <TD COLSPAN=2>
        <INPUT TYPE=CHECKBOX NAME=CONT_followup>
      </TD>
    </TR>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TH CLASS=heading-body2 COLSPAN=2 ALIGN=LEFT>
        Date/Time Of Next Contact (Leave Blank For Default) [YYYY-MM-DD HH:MM:SS] 
      </TH>
      <TD COLSPAN=2>
        <INPUT TYPE=INPUT NAME=CONT_nextcall>
      </TD>
    </TR>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TH CLASS=heading-body2 COLSPAN=2 ALIGN=LEFT>
        Feedback
      </TH>
      <TD COLSPAN=2>
        <TEXTAREA NAME=CONT_feedback ROWS=8 COLS=60></TEXTAREA>
      </TD>
    </TR><?php
  } else {?>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TH COLSPAN=4 CLASS=heading-body>
        There Are Currently No Calls
      </TH>
    </TR><?php
  }?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD ALIGN=MIDDLE COLSPAN=4>
      <INPUT TYPE=BUTTON NAME=dialbut onclick=ccdial() VALUE="Dial">
      <INPUT TYPE=SUBMIT onclick=this.name='abortcall' VALUE="Hangup/Cancel">
      <INPUT TYPE=BUTTON NAME=transbut onclick=transcall('<?php print $_SESSION['lastcon'];?>') VALUE="Transfer">
      <INPUT TYPE=BUTTON NAME=ddialbut onclick=directdial() VALUE="Direct Dial">
      <INPUT TYPE=SUBMIT NAME=subme onclick=this.name='senddata' VALUE="Complete">
      <INPUT TYPE=BUTTON NAME=reset onclick=resetcall() VALUE="Reset">
    </TD>
  </TR><?php
  if ($_SESSION['nextid'] != "") {?>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TH CLASS=heading-body2 COLSPAN=4>
        Previous 20 Contacts
      </TH>
    </TR><?php

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
      $contd=pg_fetch_array($lastcontq,$pcont,PGSQL_NUM);?>
      <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
        <TD COLSPAN=2>
          Date :
          <?php print $contd[3];?><BR>
          Status :
          <?php print $contd[0];?><BR>
          Followup :
          <?php print ($contd[1] == "t")?"Yes":"No";?><BR>
          Agent :
          <?php print $contd[4];?><BR><?php
          for($xdat=5;$xdat<count($outrows)+5;$xdat++) {
            if ($xdatt[($xdat-5)] == "boolean") {
              $contd[$xdat]=($contd[$xdat] == "t")?"Yes":"No";
            }
            print $outrows[($xdat-5)] . " : " . $contd[$xdat] . "<BR>";
          }
      ?></TH>
        <TD  COLSPAN=2 VALIGN=TOP ALIGN=LEFT>
          <?php print htmlspecialchars($contd[2]);?>
        </TH>
      </TR><?php
    }
  }?>
  </TABLE></FORM>
  <FORM NAME=caller METHOD=POST ACTION=/ccagent/caller.php>
    <INPUT TYPE=HIDDEN NAME=numtocall VALUE="<?php print $_SESSION['lastcon'];?>">
    <INPUT TYPE=HIDDEN NAME=disppage VALUE="<?php print $showpage;?>">
    <INPUT TYPE=HIDDEN NAME=nomenu VALUE="<?php print $_POST['nomenu'];?>">
  </FORM>
  <FORM NAME=celledit METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=newvalue>
    <INPUT TYPE=HIDDEN NAME=cellname>
  </FORM>
  <SCRIPT><?php
  if ($_SESSION['incall']) {?>
    document.getElementById('script').style.visibility='visible';
    document.getElementById('script').style.height=document.getElementById('stable').clientHeight;
    document.pform.ddialbut.disabled=true;
    document.pform.dialbut.disabled=true;
    document.pform.subme.disabled=false;
    document.pform.transbut.disabled=false;<?php
  } else {?>
    document.getElementById('script').style.visibility='hidden';
    document.getElementById('script').style.height='0px';
    document.pform.ddialbut.disabled=false;
    document.pform.dialbut.disabled=false;
    document.pform.subme.disabled=true;
    document.pform.transbut.disabled=true;<?php
  }?>
  </SCRIPT><?php
}

function completecall() {
  global $db,$mydb,$cfg;
  $datain_tb="contactdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid'];
  $testdb=pg_query($db,"SELECT * from information_schema.tables where table_catalog='asterisk' and table_name='" . $datain_tb . "'");
  if (pg_num_rows($testdb) > 0) {
    $testdbtbl=pg_query($db,"SELECT column_name,data_type,character_maximum_length from information_schema.columns where table_catalog='asterisk' and table_name='" .$datain_tb . "' AND (column_name != 'contid' AND column_name != 'leadid' AND column_name != 'id')");

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
          if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}:[0-9]{2}:[0-9]{2})([-\+0-9]+)$/", $_POST[$pname], $dateinf)) {
            if (checkdate($dateinf[2],$dateinf[3],$dateinf[1])) {
              $qfields.="," . $coldata[0];
              $qvals.=",'" . $_POST[$pname] . "'"; 
              unset($_POST[$pname]);
            }
          } else if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}:[0-9]{2}:[0-9]{2})$/", $_POST[$pname], $dateinf)) {
            if (checkdate($dateinf[2],$dateinf[3],$dateinf[1])) {
              $qfields.="," . $coldata[0];
              $qvals.=",'" . $_POST[$pname] . "'";
              unset($_POST[$pname]);
            }
          } else if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $_POST[$pname], $dateinf)) {
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
    pg_query($db,"INSERT INTO " . $datain_tb . " (leadid,contid" . $qfields . ") VALUES (" . $_SESSION['nextid'] . "," . $_SESSION['lastcon'] . $qvals . ")");
    pg_query($db,"UPDATE contact SET status='" . $_POST['CONT_status'] . "',followup='" . (($_POST['CONT_followup'] == "on")?"t":"f") . "',feedback='" . pg_escape_bytea($db,$_POST['CONT_feedback']) . "' WHERE contact.id=" . $_SESSION['lastcon']);
    if (isset($_SESSION['osticket'])) {
      $getto=mysql_query("SELECT CONCAT(dept_name,' <',email,'>') FROM ost_department LEFT OUTER JOIN ost_email USING (email_id) WHERE dept_id=" . $_SESSION['osticket'],$mydb);
      list($emailto)=mysql_fetch_array($getto,MYSQL_NUM);

      $data_tb="inputdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid'];
      $emaildat="";
      $testdb=pg_query($db,"SELECT * from information_schema.tables where table_catalog='asterisk' and table_name='" . $data_tb . "'");
      $xtracol="";
      $rows=array("Title","First Name","Surname","Number");
      $rnme=array("","","","");

      if (pg_num_rows($testdb) > 0) {
        $testdbtbl=pg_query($db,"SELECT column_name,data_type,character_maximum_length,fname from information_schema.columns left outer join field_names ON (tablename=table_name AND column_name=field) where table_catalog='asterisk' and table_name='" .$data_tb . "' AND (column_name != 'osticket' AND column_name != 'leadid' AND column_name != 'id')");
        for($dtrcnt=0;$dtrcnt < pg_num_rows($testdbtbl);$dtrcnt++) {
          list($trown,$trowt,$trows,$fname)=pg_fetch_array($testdbtbl,$dtrcnt);
          array_push($rows,($fname == "")?$trown:$fname);
          array_push($rnme,$trown);
          $xtracol.=",xtradata." . $trown;
        }
      }

      if ($xtracol != "") {
       $leadinf=pg_query($db,"SELECT title,fname,sname,number" . $xtracol . " FROM lead LEFT OUTER JOIN " . $data_tb . " AS xtradata ON (lead.id = xtradata.leadid) WHERE lead.id=" . $_SESSION['nextid']);
      } else {
       $leadinf=pg_query($db,"SELECT title,fname,sname,number FROM lead WHERE lead.id=" . $_SESSION['nextid']);
      }

      $scriptq=pg_query($db,"SELECT defemail,defsubject from list where id=" . $_SESSION['listid']);
      list($emailfromf,$emailsubjectf)=pg_fetch_array($scriptq,0);
      $leaddata=pg_fetch_array($leadinf,0,PGSQL_NUM);

      $emailname=$leaddata[0] . " " . $leaddata[1] . "  " . $leaddata[2];
      $emailphone=$leaddata[3];
      $emaildat.=$leaddata[0] . " " . $leaddata[1] . "  " . $leaddata[2] . " [" . $leaddata[3] . "]\n";

      for ($cell=4;$cell < count($leaddata);$cell++) {
        if ($rnme[$cell] == $emailfromf) {
          $emailfrom=$leaddata[$cell];
        } else if ($rnme[$cell] == $emailsubjectf) {
          $emailsubject=$leaddata[$cell];
        } else {
          $emaildat.=$rows[$cell] . " : " . $leaddata[$cell] . "\n";
        }
      }

      $testdb=pg_query($db,"SELECT * from information_schema.tables where table_catalog='asterisk' and table_name='" . $datain_tb . "'");
      $xtracol="";
      $outrows=array();
      $outrnme=array();
      $xdatt=array();
      if (pg_num_rows($testdb) > 0) {
        $testdbtbl=pg_query($db,"SELECT column_name,data_type,character_maximum_length,fname from information_schema.columns left outer join field_names ON (tablename=table_name AND column_name=field) where table_catalog='asterisk' and table_name='" . $datain_tb . "' AND column_name != 'contid' AND column_name != 'leadid' AND column_name != 'id'");
        for($dtrcnt=0;$dtrcnt < pg_num_rows($testdbtbl);$dtrcnt++) {
          list($trown,$trowt,$trows,$fname)=pg_fetch_array($testdbtbl,$dtrcnt);
          array_push($outrows,($fname == "")?$trown:$fname);
          array_push($xdatt,$trowt);
          array_push($outrnme,$trown);
          $xtracol.=",xtradata." . $trown;
        }
      }
      if ($xtracol == "") {
        $conthistq="SELECT status,followup,feedback,date_trunc('second',datetime),fullname||' ('||name||')' from contact LEFT OUTER JOIN agent ON (contact.agent = agent.id) LEFT OUTER JOIN users ON (agent.exten = users.name) where status != 'INIT' AND lead=" . $_SESSION['nextid'] . " and contact.id = " . $_SESSION['lastcon'];
      } else {
        $conthistq="SELECT status,followup,feedback,date_trunc('second',datetime),fullname||' ('||name||')'" . $xtracol . " from contact LEFT OUTER JOIN agent ON (contact.agent = agent.id) LEFT OUTER JOIN users ON (agent.exten = users.name) LEFT OUTER JOIN " . $datain_tb . " AS xtradata ON (xtradata.contid = contact.id) where status != 'INIT' AND lead=" . $_SESSION['nextid'] . " and contact.id = " . $_SESSION['lastcon'];
      }
      $sesdat=pg_query($db,$conthistq);
      $contd=pg_fetch_array($sesdat,0,PGSQL_NUM);
      $flwup=($contd[1] == "t")?"Yes":"No";
      $emaildat.="\nDate : " . $contd[3] . "\nStatus : " . $contd[0] . "\nFollowup : " . $flwup  . "\nAgent : " . $contd[4];
      $emaildat.="\nAgent Feedback:\n" . $contd[2] . "\n\n";
      for($xdat=5;$xdat<count($outrows)+5;$xdat++) {
        if ($outrnme[($xdat-5)] == $emailsubjectf) {
          $emailsubject=$contd[$xdat];
        } else  if ($outrnme[($xdat-5)] == $emailfromf) {
          $emailfrom=$contd[$xdat];
        }
        if ($xdatt[($xdat-5)] == "boolean") {
          $contd[$xdat]=($contd[$xdat] == "t")?"Yes":"No";
        }
        if (($contd[$xdat] != "") && ($contd[$xdat] != "0")) {
          $emaildat.=$outrows[($xdat-5)] . " : " . $contd[$xdat] . "\n";
        }
      }
      $cwd=getcwd();
      chdir("/var/spool/apache/htdocs/ticket/api/");
      $remaddr=$_SERVER['REMOTE_ADDR'];
      $_SERVER['REMOTE_ADDR']=$_SERVER['SERVER_ADDR'];
      $origua=$_SERVER['HTTP_USER_AGENT'];
      $getkey=mysql_query("SELECT api_key FROM ost_config",$mydb);
      list($_SERVER['HTTP_USER_AGENT'])=mysql_fetch_array($getkey,MYSQL_NUM);
      $_SERVER['HTTP_USER_AGENT']=md5($_SERVER['HTTP_USER_AGENT']);
      require("api.inc.php");
      require_once(INCLUDE_DIR."class.ticket.php");

      $var=array();
      $errors=array();
      $var['email']=$emailfrom;
      $var['name']=$emailname;
      $var['deptId']=$_SESSION['osticket'];
      $var['source']="phone";
      if (strlen($emailphone) >= 7) {
        $var['phone']=$emailphone;
      }
      $var['subject']=($emailsubject == "")?"[No Subject]":$emailsubject;
      $var['message']=$emaildat;
      $ticket=Ticket::create($var,$errors,'staff');
      chdir($cwd);
      $_SERVER['HTTP_USER_AGENT']=$origua;
      $_SERVER['REMOTE_ADDR']=$remaddr;
    }
  } else {
    pg_query($db,"UPDATE contact SET status='" . $_POST['CONT_status'] . "',followup='" . (($_POST['CONT_followup'] == "on")?"t":"f") . "',feedback='" . pg_escape_bytea($db,$_POST['CONT_feedback']) . "' WHERE contact.id=" . $_SESSION['lastcon']);
  }
  hangupactchan();
  sleep(1);
  unset($_SESSION['lastcon']);
  $_SESSION['incall']=false;
  listsel("");
}
