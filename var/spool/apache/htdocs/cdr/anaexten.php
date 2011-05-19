<CENTER>
<FORM METHOD=POST NAME=sipinf ONSUBMIT="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=dbkey>
<INPUT TYPE=HIDDEN NAME=dbval>
<INPUT TYPE=HIDDEN NAME=dbfam>

<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<%
include_once "uauth.inc";
include_once "apifunc.inc";
include_once "autoadd.inc";

if (isset($_POST['classi'])) {
  $techname=$_POST['classi'];
} else {
  $techname="IAX";
}

if ($techname == "Dahdi") {
  $etype=_("Analogue Extensions");
  $apiquery="DAHDIShowChannels";
  $collen=10;
} else if ($techname == "SIP") {
  $etype=_("SIP Extensions");
  $apiquery="SIPPeers";
  $collen=13;
} else if ($techname == "IAX") {
  $etype=_("IAX Extensions");
  $apiquery="IAXPeers";
  $collen=13;
}


function getuphref($exten,$dbkey,$dbtype,$disp) {
  return "<A HREF=\"javascript:astdbupdate('" . $exten . "','" . $dbkey . "','" . $dbtype . "')\">" . $disp . "</A>";
}


if (($dbfam != "") && ($dbkey != "")) {
  if (($dbkey != "qualify") && ($dbkey != "nat")){
    pg_query($db,"UPDATE astdb SET value='" . strtolower($dbval) . "' WHERE family='" . $dbfam . "' AND key='" . $dbkey . "'");
  } else {
    if ($dbval == 0) {
      if ($dbkey == "nat") {
        $dbval="never";
      } else {
        $dbval="no";
      }
    } else {
      $dbval="yes";
    }
    pg_query($db,"UPDATE users SET " . $dbkey . "='" . $dbval . "' WHERE name='" . $dbfam . "'");
    if (! isset($agi)) {
      require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
      $agi=new AGI_AsteriskManager();
      $agi->connect("127.0.0.1","admin","admin");
    }
    if (isset($agi)) {
      $agi->command("sip prune realtime peer " . $dbfam);
      $agi->command("sip show peer " . $dbfam . " load");
      sleep(2);
    }
  }
}

if (($_POST['print'] != "1") &&  ($techname == "Dahdi") && ($SUPER_USER == 1)) {
  $colspan=$collen;
} else {
  $colspan=$collen-1;
}

print "<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=" . $colspan . ">" . $etype . "</TH></TR>";
print "<TR CLASS=list-color1>";

if (($_POST['print'] != "1") &&  ($techname == "Dahdi") && ($SUPER_USER == 1)) {
  print "<TH CLASS=heading-body2>Add/Delete</TH>";
}
print "<TH CLASS=heading-body2>" . _("Extension") . "</TH>";

if ($techname == "Dahdi") {
  print "<TH CLASS=heading-body2>" . _("Port") . "</TH>";
} else if ($techname == "SIP")  {
  print "<TH CLASS=heading-body2>" . _("Host") . "</TH>";
  print "<TH CLASS=heading-body2>" . _("NAT") . "</TH>";
  print "<TH CLASS=heading-body2>" . _("Port") . "</TH>";
  print "<TH CLASS=heading-body2>" . _("Status") . "</TH>";
}
%>


<TH CLASS=heading-body2>DND</TH>
<TH CLASS=heading-body2><%print _("Fwd. Imm.");%></TH>
<TH CLASS=heading-body2><%print _("Fwd. Busy");%></TH>
<TH CLASS=heading-body2><%print _("Fwd. NA");%></TH>
<TH CLASS=heading-body2><%print _("R. Time");%></TH>
<TH CLASS=heading-body2><%print _("V. Mail");%></TH>
<TH CLASS=heading-body2><%print _("C. Wait.");%></TH></TR>

<%
$apiinf=apiquery($apiquery);

$chaninf=array();
$chansort=array();
for ($pkt=0;$pkt < count($apiinf);$pkt++) {
  if ($techname == "Dahdi") {
    $zchan=$apiinf[$pkt];
    if ($zchan['Context'] != "ddi") {
      unset($zchan['Signalling']);   
      unset($zchan['Alarm']);   
      unset($zchan['Event']);   
      unset($zchan['Context']);   
      $port=$zchan['DAHDIChannel'];
      unset($zchan['DAHDIChannel']);
      $zchan['DND']=($zchan['DND'] == "Disabled")?"0":"1";
      $chaninf[$port]=$zchan;
      array_push($chansort,$port);
      $toadd="aadd" . $port;
      if ($$toadd == "on") {
        $nexten=createexten("","","","",$port);
        if ($nexten == "") {
%>
<SCRIPT>
alert('Error: Cannot Create A Extension Please Add Manualy.');
</SCRIPT>
<%
        }
      }
    }
  } else if ($techname == "SIP") {
    $schan=$apiinf[$pkt];
    $extenno=$schan['ObjectName'];
    unset($schan['ObjectName']);   
    unset($schan['Channeltype']);   
    unset($schan['ChanObjectType']);   
    unset($schan['Event']);   
    unset($schan['Dynamic']);
    unset($schan['VideoSupport']);
    unset($schan['ACL']);
    unset($schan['RealtimeDevice']);
    $schan['Natsupport']=($schan['Natsupport'] == "no")?"0":"1";
    $chaninf[$extenno]=$schan;
    array_push($chansort,$extenno);
  } else if ($techname == "IAX") {
    print_r($apiinf);
  }
}


$getastq="SELECT name,fullname,astdb.value,cfim.value,cfbu.value,cfna.value,tout.value,cwait.value,novmail.value,dnd.value FROM users
            LEFT OUTER JOIN astdb ON (name = astdb.family and astdb.key='ZAPLine')
            LEFT OUTER JOIN astdb AS novmail ON (novmail.family=name AND novmail.key='NOVMAIL')
            LEFT OUTER JOIN astdb AS dnd ON (dnd.family=name AND dnd.key='CDND')
            LEFT OUTER JOIN astdb AS tout ON (tout.family=name AND tout.key='TOUT')
            LEFT OUTER JOIN astdb AS cfim ON (cfim.family=name AND cfim.key='CFIM')
            LEFT OUTER JOIN astdb AS cfbu ON (cfbu.family=name AND cfbu.key='CFBU')
            LEFT OUTER JOIN astdb AS cwait ON (cwait.family=name AND cwait.key='WAIT')
            LEFT OUTER JOIN astdb AS cfna ON (cfna.family=name AND cfna.key='CFNA')";

if ($SUPER_USER != 1) {
  $getastq.=" LEFT OUTER JOIN astdb AS bgrp ON (bgrp.family=name AND bgrp.key='BGRP')";
} 
if ($techname == "Dahdi") {
  $getastq.=" WHERE astdb.value is not null and astdb.value != '0'";
} else if ($techname == "SIP") {
  $getastq.=" WHERE astdb.value is null or astdb.value = '0'";
}
if ($SUPER_USER != 1) {
  $getastq.=" AND " . $clogacl;
}
$getastq.=" order by name";
//print $getastq .  "\n";
$extens=pg_query($db,$getastq);

for($tcnt=0;$tcnt<pg_num_rows($extens);$tcnt++) {
  $r=pg_fetch_array($extens,$tcnt,PGSQL_NUM);

  if ($techname == "Dahdi") {
    $todel="adel" . $r[2];
  } else {
    $todel="adel" . $r[0];
  }

  if ($$todel == "on") {
      pg_query($db,"DELETE FROM users WHERE name='" . $r[0] . "'");
      pg_query($db,"DELETE FROM astdb WHERE family='" . $r[0] . "'");
      pg_query($db,"DELETE FROM console WHERE mailbox='" . $r[0] . "'");
      $delpre=pg_query($db,"SELECT name from users where name ~ '^" . substr($r[0],0,2) . "'");
      if (pg_num_rows($delpre) <= 0) {
        $delpre2=pg_query($db,"SELECT value from astdb where family = 'Setup' AND key = 'DefaultPrefix' AND value = '" . substr($r[0],0,2) . "'");
        if (pg_num_rows($delpre2) <= 0) {
          pg_query($db,"DELETE FROM astdb WHERE family = 'LocalPrefix' and key = '" . substr($r[0],0,2) . "'");
        }
      }
      continue;
  }

  if (($techname == "Dahdi") && (is_array($chaninf[$r[2]]))) {
    $chanarr=$chaninf[$r[2]];
  } else {
    $chanarr=$chaninf[$r[0]];
  }

  if (is_array($chanarr)) {
    $chanarr['Exten']=$r[0];
    $chanarr['Name']=$r[1];
    $chanarr['CFIm']=$r[3];
    $chanarr['CFBusy']=$r[4];
    $chanarr['CFNA']=$r[5];
    $chanarr['RTOUT']=$r[6];
    $chanarr['CWAIT']=$r[7];
    $chanarr['NOVM']=$r[8];
    $chanarr['DND']=$r[9];
  }

  if (($techname == "Dahdi") && (is_array($chaninf[$r[2]]))) {
    $chaninf[$r[2]]=$chanarr;
  } else {
    $chaninf[$r[0]]=$chanarr;
  }
}

sort($chansort);

$rcol=1;
for($ecnt=0;$ecnt < count($chansort);$ecnt++) {
  $port=$chansort[$ecnt];
  $r=$chaninf[$port];

  if (($techname == "SIP") && ($r['Exten'] == "")) {
    continue;
  }

  print "<TR CLASS=list-color" . (($rcol % 2 )+1) . ">\n  <TD>";

  if (($_POST['print'] != "1") &&  ($techname == "Dahdi") && ($SUPER_USER == 1)) {
    print "<INPUT TYPE=CHECKBOX NAME=a";
    if ($r['Exten'] == "")
      print "add" . $port;
    else
      print "del" . $port;
    print "></TD><TD>";
  }

  if ($r['Exten'] == "") {
    print "&lt;NOT CONFIGURED&gt;";
  } else {
    if ($_POST['print'] != "1") {
      print "<A HREF=javascript:openextenedit('" . $r['Exten'] . "')>";
    }
    print $r['Exten'] . " (" . $r['Name'] . ")";
    if ($_POST['print'] != "1") {
      print "</A>";
    }
  }

  if ($techname == "Dahdi") {
    print "</TD><TD>" . $port . "</TD>";
    $dndval=$r['DND']?"On":"Off";
    print "<TD>" . $dndval . "</TD>\n";
  } else if ($techname == "SIP") {
    if ($_POST['print'] != "1") {
      print "<TD><A HREF=\"javascript:openphone('http://" . $r['IPaddress'] . "')\">" . $r['IPaddress'] . "</A>";
      print "<TD>" . getuphref($r['Exten'],"nat",$r['Natsupport']?"1":"0",$r['Natsupport']?_("Yes"):_("No"));
      print "<TD>" . $r['IPport'] . "</TD>";
      print "<TD>" . getuphref($r['Exten'],"qualify",($r['Status'] != "Unmonitored")?"1":"0",$r['Status']);
      if ($r['DND'] >= 0) {
        print "<TD>" . getuphref($r['Exten'],"CDND",($r['DND'])?"1":"0",($r['DND'])?_("Yes"):_("No")) . "</TD>";
      } else {
        print "<TD>N/A</TD>";
      }
    } else {
      print "<TD>" . $r['IPaddress'] . "</TD><TD>" . ($r['Natsupport']?_("Yes"):_("No")) . "</TD><TD>" . $r['IPport'] . "</TD><TD>" . $r['Status'] . "</TD><TD>" . ($r['DND']?_("Yes"):_("No")) . "</TD>";
    }
  }

  if ($r['Exten'] != "") {
    if ($r['CFIm'] == 0) {
      $r['CFIm']="None";
    }
    if ($r['CFBusy'] == 0) {
      $r['CFBusy']="None";
    }
    if ($r['CFNA'] == 0) {
      $r['CFNA']="None";
    }

    if ($_POST['print'] != "1") {
      print "<TD>" . getuphref($r['Exten'],"CFIM",_("Immeadiate Call Forwarding"),$r['CFIm']) . "</TD>";
      print "<TD>" . getuphref($r['Exten'],"CFBU",_("Call Forwarding On Busy"),$r['CFBusy']) . "</TD>";
      print "<TD>" . getuphref($r['Exten'],"CFNA",_("Call Forwarding On No Answer"),$r['CFNA']) . "</TD>";
      print "<TD>" . getuphref($r['Exten'],"TOUT",_("Ring Timeout"),$r['RTOUT']) . "</TD>";
      if (($r['CFIm'] != "None" ) || (($r['CFBusy'] != "None") && ($r['CFNA'] != "None"))) { 
        print "<TD>N/A</TD>";
      } else {
        print "<TD>" . getuphref($r['Exten'],"NOVMAIL",$r['NOVM'],($r['NOVM'])?_("No"):_("Yes")) . "</TD>";
      }
      print "<TD>" . getuphref($r['Exten'],"WAIT",($r['CWAIT'])?"1":"0",($r['CWAIT'])?_("Yes"):_("No")) . "</TD>";
    } else {
      print "<TD>" . $r['CFIm'] . "</TD>";
      print "<TD>" . $r['CFBusy'] . "</TD>";
      print "<TD>" . $r['CFNA'] . "</TD>";
      print "<TD>" . $r['RTOUT'] . "</TD>";

      if (($r['CFIm'] != "None" ) || (($r['CFBusy'] != "None") && ($r['CFNA'] != "None"))) { 
        print "<TD>N/A</TD>";
      } else {
        print "<TD>" . (($r['NOVM'])?_("No"):_("Yes")) . "</TD>";
      }

      print "<TD>" . (($r['CWAIT'])?_("Yes"):_("No")) . "</TD>";
    }
  } else {
    print "<TD COLSPAN=6>&nbsp;</TD>";
  }

  print "</TR>";
  $rcol++;
}

if ($_POST['print'] != "1") {
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TD COLSPAN=" . $colspan . " ALIGN=LEFT>" . ($rcol - 1) . " Extensions Affected</TH></TR>";
  $rcol++;
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=" . $colspan . " CLASS=heading-body>";
  if (($techname == "Dahdi") && ($SUPER_USER == 1)) {
    print "<INPUT TYPE=SUBMIT onclick=this.name='authexten' VALUE=\"Add/Delete\">";
  }
  print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.ppage)\"></TH></TR>";
}
%>
</FORM>
</TABLE>
<FORM NAME=ppage METHOD=POST>
<INPUT TYPE=HIDDEN NAME=print>
</FORM>
