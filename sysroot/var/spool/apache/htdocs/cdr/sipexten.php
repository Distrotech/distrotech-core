<CENTER>
<FORM METHOD=POST NAME=sipinf ONSUBMIT="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=dbkey>
<INPUT TYPE=HIDDEN NAME=dbval>
<INPUT TYPE=HIDDEN NAME=dbfam>

<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<?php
include_once "uauth.inc";
include_once "apifunc.inc";
include_once "autoadd.inc";

function getuphref($exten,$dbkey,$dbtype,$disp) {
  return "<A HREF=\"javascript:astdbupdate('" . $exten . "','" . $dbkey . "','" . $dbtype . "')\">" . $disp . "</A>";
}

if (($dbfam != "") && ($dbkey != "")) {
  if (($dbkey != "qualify") && ($dbkey != "nat")){
    pg_query($db,"UPDATE features SET " . $dbkey . "='" . strtolower($dbval) . "' WHERE exten='" . $dbfam . "'");
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
    $agi->command("sip prune realtime peer " . $dbfam);
    $agi->command("sip show peer " . $dbfam . " load");
    sleep(2);
  }
}

if (($_POST['print'] != "1") &&  ($SUPER_USER == 1)) {
  $colspan=10;
} else {
  $colspan=9;
}

//$techname="Dahdi";
$techname="SIP";

if ($techname == "Dahdi") {
  $etype=_("Analogue Extensions");
  $apiquery="DAHDIShowChannels";
} else if ($techname == "SIP") {
  $etype=_("SIP Extensions");
  $apiquery="SIPPeers";
}

print "<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=" . $colspan . ">" . $etype . "</TH></TR>";
print "<TR CLASS=list-color1>";

if (($_POST['print'] != "1") &&  ($SUPER_USER == 1)) {
  print "<TH CLASS=heading-body2>Add/Delete</TH>";
}


?>
<TH CLASS=heading-body2>Extension</TH>
<TH CLASS=heading-body2>Port</TH>

<TH CLASS=heading-body2>DND</TH>
<TH CLASS=heading-body2><?php print _("Fwd. Imm.");?></TH>
<TH CLASS=heading-body2><?php print _("Fwd. Busy");?></TH>
<TH CLASS=heading-body2><?php print _("Fwd. NA");?></TH>
<TH CLASS=heading-body2><?php print _("R. Time");?></TH>
<TH CLASS=heading-body2><?php print _("V. Mail");?></TH>
<TH CLASS=heading-body2><?php print _("C. Wait.");?></TH></TR>

<?php
$apiinf=apiquery($apiquery);

$chaninf=array();
for ($pkt=0;$pkt < count($apiinf);$pkt++) {
  if ($techname == "Dahdi") {
    $zchan=$apiinf[$pkt];
    if ($zchan['Context'] != "ddi") {
      unset($zchan['Signalling']);   
      unset($zchan['Alarm']);   
      unset($zchan['Event']);   
      unset($zchan['Context']);   
      $port=$zchan['Channel'];
      unset($zchan['Channel']);
      $zchan['DND']=($zchan['DND'] == "Disabled")?"0":"1";
      $chaninf[$port]=$zchan;
      $toadd="aadd" . $port;
      if ($$toadd == "on") {
        $nexten=createexten("","","","",$port);
        if ($nexten == "") {
?>
<SCRIPT>
alert('Error: Cannot Create A Extension Please Add Manualy.');
</SCRIPT>
<?php
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
    print_r($schan);
  }
}


$getastq="SELECT name,fullname,zapline,cfim,cfbu,cfna,tout,wait,novmail,cdnd FROM users
            LEFT OUTER JOIN features ON (name = exten)";

if ($SUPER_USER != 1) {
  $getastq.=" LEFT OUTER JOIN astdb AS bgrp ON (bgrp.family=name AND bgrp.key='BGRP')";
} 
if ($techname == "Dahdi") {
  $getastq.=" WHERE zapline is not null and zapline != '0'";
} else if ($techname == "SIP") {
  $getastq.=" WHERE zapline is null or zapline = '0'";
}
if ($SUPER_USER != 1) {
  $getastq.=" AND " . $clogacl;
}
$getastq.=" order by name";
print $getastq .  "\n";
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
      pg_query($db,"DELETE FROM features WHERE exten='" . $r[0] . "'");
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
  }

  if (($techname == "Dahdi") && (is_array($chaninf[$r[2]]))) {
    $chaninf[$r[2]]=$chanarr;
  } else {
    $chaninf[$r[0]]=$chanarr;
  }
}

$rcol=1;
while(list($port,$r) = each($chaninf)) {  
  print "<TR CLASS=list-color" . (($rcol % 2 )+1) . ">\n  <TD>";

  if (($_POST['print'] != "1") &&  ($SUPER_USER == 1)) {
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

//DAHDI
  print "</TD><TD>" . $port . "</TD>";
  $dndval=$r['DND']?"On":"Off";
  print "<TD>" . $dndval . "</TD>\n";

//GENERIC
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
      print "<TD>" . getuphref($r['Exten'],"NOVMAIL",$r['NOVM'],($r['NOVM'])?_("No"):_("Yes")) . "</TD>";
      print "<TD>" . getuphref($r['Exten'],"WAIT",($r['CWAIT'])?"1":"0",($r['CWAIT'])?_("Yes"):_("No")) . "</TD>";
    } else {
      print "<TD>" . $r['CFIm'] . "</TD>";
      print "<TD>" . $r['CFBusy'] . "</TD>";
      print "<TD>" . $r['CFNA'] . "</TD>";
      print "<TD>" . $r['RTOUT'] . "</TD>";
      print "<TD>" . (($r['NOVM'])?_("No"):_("Yes")) . "</TD>";
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
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=" . $colspan . " CLASS=heading-body>
<INPUT TYPE=SUBMIT onclick=this.name='authexten' VALUE=\"Add/Delete\"><INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.ppage)\"></TH></TR>";
}
?>
</FORM>
</TABLE>
<FORM NAME=ppage METHOD=POST>
<INPUT TYPE=HIDDEN NAME=print>
</FORM>
