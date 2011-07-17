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

include "auth.inc";

$qgetdata=pg_query($db,"SELECT ipaddr,snommac,atatable.id FROM users
  LEFT OUTER JOIN features ON (name=exten) LEFT OUTER JOIN atatable ON (mac=snommac) WHERE name='" . $PHP_AUTH_USER . "'");
list($curipaddr,$snommac,$ataid)=pg_fetch_array($qgetdata,0,PGSQL_NUM);

$lsysarr=array("profile","stunsrv","hostname","rxgain","txgain","vlan","nat","mac");

if (($_POST['mac'] != "") && (isset($lsysreg))) {
  if ($_POST['nat'] == "on") {
    $_POST['nat']="NAT";
  } else {
    $_POST['nat']="Bridge";
  }

  $_POST['mac']=strtoupper($_POST['mac']);
  if ($_POST['mac'] != $snommac ) {
    pg_query($db,"UPDATE features SET snommac='" . $_POST['mac'] . "' WHERE exten='" . $exten . "'");
  }
  if ($ataid == "") {
    pg_query($db,"INSERT INTO atatable (mac) VALUES ('" . $_POST['mac'] . "')");
    $getid=pg_query($db,"SELECT id FROM atatable WHERE mac='" . $_POST['mac'] . "'");
    list($ataid)=pg_fetch_array($getid,0,PGSQL_NUM);
  }
  $upq="";
  for($lval=0;$lval < count($lsysarr);$lval++) {
    $upq.=$lsysarr[$lval] . "= '" . $_POST[$lsysarr[$lval]] . "',";
  }
  $upq=substr($upq,0,-1);
  pg_query($db, "UPDATE atatable SET " . $upq . " WHERE id='" . $ataid . "'");
  if ($LSYSIPADDR != "") {%>
    <SCRIPT>
      atapopupwin('<%print $LSYSIPADDR;%>','<%print $LSYSPROFILE;%>');
    </SCRIPT>
<%
  }
}

$lsysgetconfq="SELECT profile,atatable.nat,rxgain,txgain,atatable.hostname,stunsrv,atatable.vlan
    FROM users
       LEFT OUTER JOIN features ON (name=exten)
       LEFT OUTER JOIN atatable ON (snommac=mac)
    WHERE atatable IS NOT NULL AND ptype='LINKSYS' AND name='" . $PHP_AUTH_USER . "'";
$lsysgetconf=pg_query($db, $lsysgetconfq);

if (pg_num_rows($lsysgetconf) > 0) {
  $lsysdata=pg_fetch_array($lsysgetconf,0,PGSQL_ASSOC);
} else {
  $lsysdata["profile"]=$SERVER_NAME;
  $lsysdata["hostname"]="exten-" . $exten;
  $lsysdata["rxgain"]="-3";
  $lsysdata["txgain"]="-3";
  $lsysdata["vlan"]="1";
  $lsysdata["nat"]="Bridge";
}

%>
<CENTER>
<link rel="stylesheet" type="text/css" href="/style.php?style=<%print $style;%>">
<FORM METHOD=POST NAME=lsysreg onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
<TR CLASS=list-color2>
  <TH COLSPAN=2>Linksys/Audiocodes MP-202 Settings (Shared By All Ports)</TH></TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES24') ONMOUSEOUT=myHint.hide()><%print _("Phones MAC Address");%><BR></TD>
  <TD><INPUT TYPE=TEXT NAME=mac VALUE="<%print $snommac;%>"></TD>
</TR>
<TR  CLASS=list-color2>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("Host Name");%></TD>
  <TD><INPUT TYPE=TEXT NAME=hostname VALUE="<%print $lsysdata["hostname"];%>"></TD>
</TR>
<TR  CLASS=list-color1>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("Settings Server");%></TD>
  <TD><INPUT TYPE=TEXT NAME=profile VALUE="<%print $lsysdata["profile"];%>"></TD>
</TR>
<TR  CLASS=list-color2>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("Stun Server");%></TD>
  <TD><INPUT TYPE=TEXT NAME=stunsrv VALUE="<%print $lsysdata["stunsrv"];%>"></TD>
</TR>
<TR  CLASS=list-color1>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("VLAN ID (Handsets)") . "<BR>" . _("Set It On The Menu And Power Cycle The Device Before Sending The Config");%></TD>
  <TD><INPUT TYPE=TEXT NAME=vlan VALUE="<%print $lsysdata["vlan"];%>"></TD>
</TR>
<TR  CLASS=list-color2>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("RX/TX Gain (ATA's)");%></TD>
  <TD>
    <INPUT TYPE=TEXT NAME=rxgain SIZE=3 VALUE="<%print $lsysdata["rxgain"];%>">/
    <INPUT TYPE=TEXT NAME=txgain SIZE=3 VALUE="<%print $lsysdata["txgain"];%>">
  </TD>
</TR>
</TR>
<TR  CLASS=list-color1>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("Current IP Address.") . "<BR>" . _("This Must Be Set And Reachable From Your Browser To Initilise The Phone Correctly.");%></TD>
  <TD><INPUT TYPE=TEXT NAME=LSYSIPADDR VALUE="<%print $curipaddr;%>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES8') ONMOUSEOUT=myHint.hide()><%print _("Enable NAT/DHCP On Lan Port");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=nat<%if ($lsysdata["nat"] == "NAT") {print " checked";}%>></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT NAME=lsysreg VALUE="<%print _("Save");%>"></TD>
</TR>

</TABLE>
