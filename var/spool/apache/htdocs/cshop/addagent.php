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

if ($_SESSION['classi'] != "") {
  $admin=$_SESSION['classi'];
}

$speriod=array(1,2,3,4,5,6,10,12,15,20,30,60);
$mperiod=array(0,5,10,15,20,30,40,45,60,90,120);
$action="Adding ";

if (isset($_POST['addagent'])) {
  if ($_POST['userpass1'] == $_POST['userpass2']) {
    $pass=$_POST['userpass1'];
  }
  $uncheck=pg_query($db,"SELECT realmid FROM reseller 
                                   WHERE username = '" . $_POST['username'] . "' AND
                                         realmid = " . $_SESSION['realmid'] . " LIMIT 1");
  if (pg_num_rows($uncheck) != 0) {%>
    <SCRIPT>
      alert("Username In Use.\nNot Added!");
    </SCRIPT><%
    $_POST['username']="";
  } else if ($pass == "") {%>
    <SCRIPT>
      alert("Password Mismatch.\nNot Added!");
    </SCRIPT><%
  } else {
    $action="Editing ";
    $_POST['edituser']=$_POST['username'];
    if (($_SESSION['resellerid'] != "0") || ($editid == $_SESSION['resellerid'])){
      $_POST['userrealm']=$_SESSION['realmid'];
    }    
    if (($admin == "t") && ($_SESSION['rlevel'] < 4))  {
      $_POST['buyrate']=$_SESSION['resellerid'] . "-" . $_POST['buyrate'];
      pg_query($db,"INSERT INTO reseller (username,userpass,description,admin,credit,discount,
                                          sellperiod,buyperiod,realmid,buyrate,minperiod,buyminperiod,owner,rlevel,exchangerate,seslimit) 
                      VALUES ('" . $_POST['username'] . "','" . $pass . "','" . $_POST['description'] . "','" .
                              $admin . "',0,0," . $_POST['sellperiod'] . "," . $_POST['buyperiod'] . "," . $_POST['userrealm'] . ",'" .
                              $_POST['buyrate'] . "','" . $_POST['minperiod'] . "','" . $_POST['buyminperiod'] . "','" . $_SESSION['resellerid'] . "'," .
                              $_SESSION['rlevel'] . "+1," . $_SESSION['rexrate'] . "," . $_POST['seslimit'] . ")");
    } else {
      pg_query($db,"INSERT INTO reseller (username,userpass,description,credit,discount,realmid,owner,rlevel,seslimit) 
                      VALUES ('" . $_POST['username'] . "','" . $pass . "','" . $_POST['description'] . "',
                              0,0,'" . $_SESSION['realmid'] . "','" . $_SESSION['resellerid'] . "'," . $_SESSION['rlevel'] . "+1," . $_POST['seslimit'] . ")");
    }
  }
} else if ($_POST['edituser'] != "") {
  $action="Editing ";
  $aguser=pg_query($db,"SELECT username,userpass,description,admin,sellperiod,buyperiod,
                               exchangerate,minperiod,buyrate,id,buyminperiod,realmid,seslimit FROM reseller
                          WHERE (id = " . $_SESSION['resellerid'] . " OR
                                 owner = " . $_SESSION['resellerid'] . ") AND
                                 id = '" . $_POST['edituser'] . "'  LIMIT 1");
  $r=pg_fetch_array($aguser,0,PGSQL_NUM);
  $_POST['edituser']=$r[0];
  $pass=$r[1];
  $_POST['description']=$r[2];
  $admin=$r[3];
  $_SESSION['classi']=$admin;
  $_POST['sellperiod']=$r[4];
  $_POST['buyperiod']=$r[5];
  $exchangerate=$r[6];
  $_POST['minperiod']=$r[7];
  $_POST['buyrate']=$r[8];
  $editid=$r[9];
  $_POST['buyminperiod']=$r[10];
  $_POST['userrealm']=$r[11];
  $_POST['seslimit']=$r[12];
} else if (isset($_POST['updateuser'])) {
  $action="Editing ";
  $_POST['edituser']=$_POST['username'];
  if ($_POST['userpass1'] == $_POST['userpass2']) {
    $pass=$_POST['userpass1'];
    $uncheck=pg_query($db,"SELECT id FROM reseller 
                           WHERE (id = " . $_SESSION['resellerid'] . " OR owner = " . $_SESSION['resellerid'] . ") AND
                                 username='" . $_POST['username'] . "' AND (realmid =  " . $_SESSION['realmid'] . " OR owner = " . $_SESSION['resellerid'] . ") LIMIT 1");
    if (pg_num_rows($uncheck) != 0) {
      $getid=pg_fetch_array($uncheck,0,PGSQL_NUM);
      $editid=$getid[0];
      $tbuyrate="";
      $tbuyperiod="";
      if ($editid != $_SESSION['resellerid']) {
        $_POST['buyrate']=$_SESSION['resellerid'] . "-" . $_POST['buyrate'];
        $tbuyrate=",buyrate='" . $_POST['buyrate'] . "'";
        $tbuyperiod=",buyperiod='" . $_POST['buyperiod'] . "'";
      } else if ($_SESSION['resellerid'] == "0") {
        $tbuyperiod=",buyperiod='" . $_POST['buyperiod'] . "'";
        $_POST['buyrate']="";
      } else {
        $_POST['buyrate']="";
        $_POST['buyperiod']="";
      }
      if (($_SESSION['resellerid'] != "0") || ($editid == $_SESSION['resellerid'])){
        $_POST['userrealm']=$_SESSION['realmid'];
      }
      if ($admin == "t") {
        pg_query($db,"UPDATE reseller SET userpass='" . $pass . "',description = '" . $_POST['description'] . "',
                                          sellperiod = '" . $_POST['sellperiod'] . "'" . $tbuyrate . $tbuyperiod . ",
                                          minperiod = '" . $_POST['minperiod'] . "',realmid = " . $_POST['userrealm'] . ",
                                          buyminperiod = '" . $_POST['buyminperiod'] . "',seslimit=" . $_POST['seslimit'] . "
                                        WHERE id = '" . $editid . "'");
      } else {
        pg_query($db,"UPDATE reseller SET username = '" . $_POST['username'] . "',userpass='" . $pass . "',
                                          description = '" . $_POST['description'] . "',seslimit=" . $_POST['seslimit'] . "
                                      WHERE id = '" . $editid . "'");
      }
    }
  } else {%>
    <SCRIPT>
      alert("Password Mismatch.\nNot Modified!");
    </SCRIPT><%
    $action="Adding ";
    $admin="";
    $_POST['description']="";
    $_POST['seslimit']="180";
    $_POST['username']="";
    $_POST['edituser']="";
    $pass="";
    $exchangerate="";
    $_POST['buyperiod']="";
    $_POST['sellperiod']="";
    $_POST['buyrate']="";
    $_POST['minperiod']="";
    $_POST['buyminperiod']="";
    $_POST['userrealm']="";
 }
} else if (isset($_POST['deluser'])) {
    $uncheck=pg_query($db,"SELECT id,buyrate FROM reseller 
                           WHERE id  != " . $_SESSION['resellerid'] . " AND owner  = " . $_SESSION['resellerid'] . " AND
                                 username='" . $_POST['username'] . "' LIMIT 1");
  if (pg_num_rows($uncheck) != 0) {
    $r=pg_fetch_array($uncheck,0,PGSQL_NUM);
    $dresbalq=pg_query("SELECT credit FROM reseller WHERE username = '" . $_POST['username'] . "'");
    $dresbal=pg_fetch_row($dresbalq,0);
    pg_query("DELETE FROM reseller WHERE username = '" . $_POST['username'] . "'");
    pg_query("DELETE FROM tariffrate where tariffcode like '" . $r[0] . "-%'");
    pg_query("DELETE FROM tariff where tariffcode like '" . $r[0] . "-%'");
    $resupq="UPDATE reseller SET rcallocated=rcallocated-" . $dresbal[0] . " WHERE id= " . $_SESSION['resellerid'];
    pg_query($resupq);
  }
  $action="Adding ";
  $admin="";
  $_POST['description']="";
  $_POST['seslimit']="180";
  $_POST['username']="";
  $_POST['edituser']="";
  $pass="";
  $exchangerate="";
  $_POST['buyperiod']="";
  $_POST['sellperiod']="";
  $_POST['buyrate']="";
  $_POST['minperiod']="";
  $_POST['buyminperiod']="";
  $_POST['userrealm']="";
}
if (!isset($_POST['seslimit'])) {
  $_POST['seslimit']="180";
}
%>
<FORM NAME=editagent METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body><%print $action;%> <%if ($admin == "t") {print "Reseller";} else {print "Operator";}%>
<%if ($_POST['edituser'] != "") {print " (" . $_POST['edituser'] . ")";}%></TH>
</TR>
<%if ($_POST['edituser'] == "") {
  $bcolor[0]="2";
  $bcolor[1]="1";
%><TR CLASS=list-color1>
  <TD WIDTH=50%>Username</TD>
  <TD WIDTH=50% ALIGN=LEFT>
  <INPUT TYPE=TEXT NAME=username VALUE="<%print $_POST['username'];%>">
</TD></TR>
<%} else {
  $bcolor[0]="1";
  $bcolor[1]="2";
%>
  <INPUT TYPE=HIDDEN NAME=username VALUE="<%print $_POST['edituser'];%>">
<%
}
if (($_POST['edituser'] != "admin") || ($_SESSION['resellerid'] == 0)) {
%>
<TR CLASS=list-color<%print $bcolor[0];%>>
  <TD WIDTH=50%>Password</TD>
  <TD WIDTH=50% ALIGN=LEFT><INPUT TYPE=PASSWORD NAME=userpass1  VALUE="<%print $pass;%>"></TD></TR>
<TR CLASS=list-color<%print $bcolor[1];%>>
  <TD WIDTH=50%>Confirm Password</TD>
  <TD WIDTH=50% ALIGN=LEFT><INPUT TYPE=PASSWORD NAME=userpass2  VALUE="<%print $pass;%>"></TD></TR>
<TR CLASS=list-color<%print $bcolor[0];%>>
  <TD>Description</TD>
  <TD><INPUT TYPE=TEXT NAME=description  VALUE="<%print $_POST['description'];%>"></TD>
<TR CLASS=list-color<%print $bcolor[1];%>>
  <TD>Session Limit</TD>
  <TD><INPUT TYPE=TEXT NAME=seslimit  VALUE="<%print $_POST['seslimit'];%>"></TD>
<%
  if (isset($_POST['edituser'])) {
    $bcolor[1]="2";
    $bcolor[0]="1";
  }
} else {
  $bcolor[0]="2";
  $bcolor[1]="1";
}
if ($admin == "t") {
%>
<TR CLASS=list-color<%print $bcolor[0];%>>
  <TD>Answer Charge Increment Sold To End User(s)</TD>
  <TD><SELECT NAME=minperiod>
<%
  for ($i=0;$i<count($mperiod);$i++) {
    print "<OPTION VALUE=" . $mperiod[$i];
    if ($_POST['minperiod'] == $mperiod[$i]) {
      print " SELECTED";
    }
    print ">" . $mperiod[$i] . "</OPTION>";
  }
%>
</SELECT></TD></TR>
<TR CLASS=list-color<%print $bcolor[1];%>>
  <TD>Call Time Increments Sold To End User(s)</TD>
  <TD><SELECT NAME=sellperiod>
<%
  for ($i=0;$i<count($speriod);$i++) {
    print "<OPTION VALUE=" . $speriod[$i];
    if ($_POST['sellperiod'] == $speriod[$i]) {
      print " SELECTED";
    }
    print ">" . $speriod[$i] . "</OPTION>";
  }
%>
</SELECT></TD></TR><%
  if (($editid != $_SESSION['resellerid']) || ($_SESSION['resellerid'] == "0")) {%>
  <TR CLASS=list-color<%print $bcolor[0];%>>
    <TD>Answer Charge Increment Bought</TD>
    <TD><SELECT NAME=buyminperiod><%
    for ($i=0;$i<count($mperiod);$i++) {
      print "<OPTION VALUE=" . $mperiod[$i];
      if ($_POST['buyminperiod'] == $mperiod[$i]) {
        print " SELECTED";
      }
      print ">" . $mperiod[$i] . "</OPTION>";  
    }%>
  </SELECT></TD></TR>
  <TR CLASS=list-color<%print $bcolor[1];%>>
    <TD>Call Time Increments Bought</TD>
    <TD><SELECT NAME=buyperiod>
  <%
    for ($i=0;$i<count($speriod);$i++) {
      print "<OPTION VALUE=" . $speriod[$i];
      if ($_POST['buyperiod'] == $speriod[$i]) {
        print " SELECTED";
      }
      print ">" . $speriod[$i] . "</OPTION>";
    }
    print "</SELECT></TD></TR>";
    if ($editid != $_SESSION['resellerid']) {
      $tariff=pg_query($db,"SELECT tariffcode,tariffname
                         FROM tariff WHERE tariffcode LIKE '" . $_SESSION['resellerid'] . "-%' ORDER BY tariffname");
      $num=pg_num_rows($tariff);%>
    <TR CLASS=list-color<%print $bcolor[0];%>>
      <TD>Supplied Rate</TD>
      <TD><SELECT NAME=buyrate><%
      for ($i=0; $i < $num; $i++) {
        $r = pg_fetch_row($tariff,$i);
        $rcode=substr($r[0],strpos($r[0],"-")+1);
        print  "<OPTION VALUE=" . $rcode;
        if ($_POST['buyrate'] == $r[0]) {
          print " SELECTED";
        }
        print ">" . $r[1] . "</OPTION>\n";
      }
      print "</SELECT></TD></TR>";
      if (($_SESSION['resellerid'] == "0") && ($editid != $_SESSION['resellerid'])) {
        $realms=pg_query($db,"SELECT id,description FROM realm ORDER BY description");
        $num=pg_num_rows($realms);%>
      <TR CLASS=list-color<%print $bcolor[1];%>>
        <TD>Authentication Realm</TD>
        <TD><SELECT NAME=userrealm><%
        for ($i=0; $i < $num; $i++) {
          $r = pg_fetch_row($realms,$i);
          print  "<OPTION VALUE=" . $r[0];
          if ($_POST['userrealm'] == $r[0]) {
            print " SELECTED";
          }
          print ">" . $r[1] . "</OPTION>\n";
        }
        print "</SELECT></TD></TR>";
        $tmp=$bcolor[0];
        $bcolor[0]=$bcolor[1];
        $bcolor[1]=$tmp;
      }
    } else {
      $tmp=$bcolor[0];
      $bcolor[0]=$bcolor[1];
      $bcolor[1]=$tmp;
    }
/*Editing Reseller Self / Admin*/
    if ($editid == $_SESSION['resellerid']){
      print "<TR CLASS=list-color" . $bcolor[1] . ">";
    } else {
      print "<TR CLASS=list-color" . $bcolor[1] . ">";
    }
  } else {
/*Editing non admin self*/
    print "<TR CLASS=list-color" . $bcolor[0] . ">"; 
  }
} else {
  /*Add/Edit Operator*/
  if (isset($_POST['edituser'])) {
%>
    <TR CLASS=list-color<%print $bcolor[0];%>><%
  } else {%>
    <TR CLASS=list-color<%print $bcolor[0];%>><%
  }
}

if ($_POST['edituser'] == "") {%>
  <TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT onclick=this.name='addagent' VALUE="Add">
<%} else {%>
  <TD ALIGN=MIDDLE COLSPAN=2>
  <INPUT TYPE=SUBMIT onclick=this.name='updateuser' VALUE="Update">
<%
    if ($editid != $_SESSION['resellerid']) {
%>
      <INPUT TYPE=HIDDEN NAME=deluser>
      <INPUT TYPE=BUTTON ONCLICK="deleteconf('This <%if ($admin == "t") {print "Reseller";} else {print "Operator";}%>',document.editagent,document.editagent.deluser)" VALUE="Delete">
  
<%
    }
  }
%>
  </TD></TR>
</TABLE>
</FORM>
