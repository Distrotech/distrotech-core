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

include_once "auth.inc";

function getuphref($exten,$dbkey,$dbtype,$disp) {
  return "<A HREF=\"javascript:astdbupdate('" . $exten . "','" . $dbkey . "','" . $dbtype . "')\">" . $disp . "</A>";
}

if (! isset($agi)) {
  require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
  $agi=new AGI_AsteriskManager();
  $agi->connect("127.0.0.1","admin","admin");
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

$speer=$agi->command("sip show peers");
%>

<CENTER>
<FORM METHOD=POST NAME=sipinf>
<INPUT TYPE=HIDDEN NAME=dbkey>
<INPUT TYPE=HIDDEN NAME=dbval>
<INPUT TYPE=HIDDEN NAME=dbfam>

<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
  <TR CLASS=list-color2>
    <TH CLASS=heading-body COLSPAN=12><%print _("Sip Extensions (Peers)");%></TH>
  </TR>

<TR CLASS=list-color1>
<TH CLASS=heading-body2><%print _("Extension");%></TH><TH CLASS=heading-body2><%print _("Host");%></TH>
<TH CLASS=heading-body2><%print _("NAT");%></TH><TH CLASS=heading-body2><%print _("Port");%></TH><TH CLASS=heading-body2><%print _("Status");%></TH>
<TH CLASS=heading-body2><%print _("DND");%></TH><TH CLASS=heading-body2><%print _("Fwd. Imm.");%></TH><TH CLASS=heading-body2><%print _("Fwd. Busy");%></TH>
<TH CLASS=heading-body2><%print _("Fwd. NA");%></TH>
<TH CLASS=heading-body2><%print _("R. Time");%></TH>
<TH CLASS=heading-body2><%print _("V. Mail");%></TH>
<TH CLASS=heading-body2><%print _("C. Wait.");%></TH></TR>
<%
$cnt=1;
$allexten=array();
foreach(explode("\n",$speer['data']) as $line) {
  if (! ereg("(^[a-zA-Z])|(^$)|(^[0-9]+ sip peers \[)",$line)) {
    if (ereg("^([0-9]{4})/[0-9]{4}[ ]+([0-9]*\.[0-9]*\.[0-9]*\.[0-9]*|\(Unspecified\))[ ]+(D|[ ])[ ]+(N|[ ])[ ]+([0-9]*)[ ]+(UNKNOWN|Unmonitored|OK \([0-9]+ ms\)|LAGGED \([0-9]+ ms\))",$line,$data)) {
      $getastq="SELECT cdnd,cfim,cfbu,cfna,fullname,tout,novmail,wait FROM 
                  users 
                  LEFT OUTER JOIN features ON (exten=name)";
      if ($SUPER_USER != 1) {
                  $getastq.=" LEFT OUTER JOIN astdb AS bgrp ON (bgrp.family=name AND bgrp.key='BGRP')";
      }
                $getastq.=" WHERE name='" . $data[1] . "'";
      if ($SUPER_USER != 1) {
                $getastq.=" AND " . $clogacl;
      }
      $getast=pg_query($db,$getastq);
      if (pg_num_rows($getast) < 1) {
        continue;
      }

      $dbinf=pg_fetch_array($getast,0);

      if ($data[2] != "(Unspecified)") {
        $data[2]="<A HREF=\"javascript:openphone('http://" . $data[2] . "')\">" . $data[2] . "</A>";
      }

      if ($data[3] == "D") {
        $data[3]=_("Yes");
      } else {
        $data[3]=_("No");
      }

      if ($data[4] == "N") {
        $data[4]=_("Yes");
      } else {
        $data[4]=_("No");
      }
      array_push($data,$dbinf[0]);
      array_push($data,$dbinf[1]);
      array_push($data,$dbinf[2]);
      array_push($data,$dbinf[3]);
      array_push($data,$dbinf[4]);
      array_push($data,$dbinf[5]);
      array_push($data,$dbinf[6]);
      array_push($data,$dbinf[7]);
      array_push($allexten,$data[1]);
      $sexten[$data[1]]=$data;
      $cnt++;
    }
  }
}

sort($allexten);
reset($allexten);

for($ecnt=0;$ecnt<count($allexten);$ecnt++) {
  $data=$sexten[$allexten[$ecnt]];
  if ($data[7] == "1") {
    $data[7]=getuphref($data[1],"CDND",$data[7],_("Yes"));
  } else if ($data[7] == "0") {
    $data[7]=getuphref($data[1],"CDND",$data[7],_("No"));
  } else {
    $data[7]=_("N/A");
  }
  
  if (($data[8] != 0 ) || (($data[9] != 0) && ($data[10] != 0))) {
    $data[13]="N/A";
  } else if ($data[13]) {
    $data[13]=getuphref($data[1],"NOVMAIL",$data[13],_("No"));
  } else {
    $data[13]=getuphref($data[1],"NOVMAIL",$data[13],_("Yes"));
  }

  if ($data[8] == 0) {
    $data[8]="None";
  }
  $data[8]=getuphref($data[1],"CFIM",_("Immeadiate Call Forwarding"),$data[8]);

  if ($data[9] == 0) {
    $data[9]="None";
  }
  $data[9]=getuphref($data[1],"CFBU",_("Call Forwarding On Busy"),$data[9]);
  if ($data[10] == 0) {
    $data[10]="None";
  } 
  $data[10]=getuphref($data[1],"CFNA",_("Call Forwarding On No Answer"),$data[10]);

  $data[12]=getuphref($data[1],"TOUT",_("Ring Timeout"),$data[12]);

  if ($data[14]) {
    $data[14]=getuphref($data[1],"WAIT",$data[14],_("Yes"));
  } else {
    $data[14]=getuphref($data[1],"WAIT",$data[14],_("No"));
  }

  if (($data[6] == "Unmonitored") || ($data[6] == "Unmonitored           ")){
    $data[6]=getuphref($data[1],"qualify","0",$data[6]);
  } else {
    $data[6]=getuphref($data[1],"qualify","1",$data[6]);
  }

  if ($data[4] == "No") {
    $data[4]=getuphref($data[1],"nat","0",_($data[4]));
  } else {
    $data[4]=getuphref($data[1],"nat","1",_($data[4]));
  }
  
  $data[1]="<A HREF=\"javascript:openextenedit('" . $data[1] . "')\">" . $data[1] . " (" . $data[11] . ")</A>";

  print "<TR CLASS=list-color" . ((($ecnt + 1) % 2) + 1). ">\n";
  print "<TD ALIGN=LEFT>" . $data[1] . "</TD><TD ALIGN=LEFT>" . $data[2] . "</TD>\n";
  print "<TD ALIGN=MIDDLE>" . $data[4] . "</TD><TD ALIGN=LEFT>" . $data[5] . "</TD><TD ALIGN=LEFT>" . $data[6] . "</TD>\n";
  print "<TD ALIGN=MIDDLE>" . $data[7] . "</TD><TD ALIGN=LEFT>" . $data[8] . "</TD><TD ALIGN=LEFT>" . $data[9] . "</TD>\n";
  print "<TD ALIGN=LEFT>" . $data[10] . "</TD><TD ALIGN=LEFT>" . $data[12] . "</TD><TD ALIGN=MIDDLE>" . $data[13] . "</TD>\n";
  print "<TD ALIGN=MIDDLE>" . $data[14] . "</TD></TR>\n";
}

$agi->disconnect();
%>
<TR CLASS=list-color<%print ((($ecnt + 1) % 2) + 1);%>><TH ALIGN=LEFT CLASS=heading-body2 COLSPAN=12><%print $ecnt. " " . _("Active Extensions");%></TH></TR>
</TABLE>
</FORM>
