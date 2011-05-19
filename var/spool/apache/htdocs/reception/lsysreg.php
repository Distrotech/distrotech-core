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

$qgetdata=pg_query($db,"SELECT key,value FROM astdb WHERE family='" . $PHP_AUTH_USER . "'");
$qgetudata=pg_query($db,"SELECT ipaddr FROM users WHERE name='" . $PHP_AUTH_USER . "'");
list($curipaddr)=pg_fetch_array($qgetudata,0);

$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  $origdata[$getdata[0]]=$getdata[1];
}

if (($SNOMMAC != "") && (isset($lsysreg))) {
  print "<script language=\"JavaScript\" src=\"/java_popups.php\" type=\"text/javascript\"></script>\n";
  if ($LSYSNAT == "on") {
    $LSYSNAT="NAT";
  } else {
    $LSYSNAT="Bridge";
  }
  pg_query($db,"UPDATE astdb SET value='" . strtoupper($SNOMMAC) . "' WHERE family='" . $exten . "' AND key='SNOMMAC'");
  for($lval=0;$lval < count($lsysconf);$lval++) {
    $lsyskey=$lsysconf[$lval];
    $ud=pg_query("UPDATE astdb SET value= '" . $$lsyskey . "' WHERE family='" . $SNOMMAC . "' AND key = '" . $lsyskey . "'");
    if (pg_affected_rows($ud) <= 0) {
      pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $SNOMMAC . "','" . $lsyskey . "','" . $$lsyskey . "')");
    }
  }
  if ($LSYSIPADDR != "") {%>
    <SCRIPT>
      atapopupwin('<%print $LSYSIPADDR;%>','<%print $LSYSPROFILE;%>');
    </SCRIPT>
<%
  }
}

$lsysgetconf=pg_query($db,"SELECT astdb.key,astdb.value FROM  astdb LEFT OUTER JOIN astdb AS exten ON (astdb.family=exten.value AND exten.key='SNOMMAC' AND astdb.family != '' AND exten.family='" . $PHP_AUTH_USER . "') WHERE astdb.family=exten.value AND astdb.family='" . $origdata['SNOMMAC'] . "'");
for($lsyscnt=0;$lsyscnt < pg_num_rows($lsysgetconf);$lsyscnt++) {
  $getdata=pg_fetch_array($lsysgetconf,$lsyscnt);
  $lsysdata[$getdata[0]]=$getdata[1];
}

$lsysconf=array("PROFILE","STUNSRV","LINKSYS","LSYSRXGAIN","LSYSTXGAIN","VLAN","NAT");
$lsysdef["PROFILE"]=$SERVER_NAME;
$lsysdef["STUNSERV"]="";
$lsysdef["LINKSYS"]="exten-" . $exten;
$lsysdef["LSYSRXGAIN"]="-3";
$lsysdef["LSYSTXGAIN"]="-3";
$lsysdef["VLAN"]="1";
$lsysdef["NAT"]="Bridge";

for($lval=0;$lval < count($lsysconf);$lval++) {
  if (! isset($lsysdata[$lsysconf[$lval]])) {
    $lsysdata[$lsysconf[$lval]]=$lsysdef[$lsysconf[$lval]];
  }
}

%>
<CENTER>
<link rel="stylesheet" type="text/css" href="/style.php?style=<%print $style;%>">
<FORM METHOD=POST>
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
<TR CLASS=list-color2>
  <TH COLSPAN=2>Linksys/Audiocodes MP-202 Settings (Shared By All Ports)</TH></TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT onmouseover=myHint.show('ES24') ONMOUSEOUT=myHint.hide()><%print _("Phones MAC Address");%><BR></TD>
  <TD><INPUT TYPE=TEXT NAME=SNOMMAC VALUE="<%print $origdata["SNOMMAC"];%>"></TD>
</TR>
<TR  CLASS=list-color2>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("Host Name");%></TD>
  <TD><INPUT TYPE=TEXT NAME=LSYSLINKSYS VALUE="<%print $lsysdata["LINKSYS"];%>"></TD>
</TR>
<TR  CLASS=list-color1>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("Settings Server");%></TD>
  <TD><INPUT TYPE=TEXT NAME=LSYSPROFILE VALUE="<%print $lsysdata["PROFILE"];%>"></TD>
</TR>
<TR  CLASS=list-color2>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("Stun Server");%></TD>
  <TD><INPUT TYPE=TEXT NAME=LSYSSTUNSRV VALUE="<%print $lsysdata["STUNSRV"];%>"></TD>
</TR>
<TR  CLASS=list-color1>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("VLAN ID (Handsets)") . "<BR>" . _("Set It On The Menu And Power Cycle The Device Before Sending The Config");%></TD>
  <TD><INPUT TYPE=TEXT NAME=LSYSVLAN VALUE="<%print $lsysdata["VLAN"];%>"></TD>
</TR>
<TR  CLASS=list-color2>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("RX/TX Gain (ATA's)");%></TD>
  <TD>
    <INPUT TYPE=TEXT NAME=LSYSLSYSRXGAIN SIZE=3 VALUE="<%print $lsysdata["LSYSRXGAIN"];%>">/
    <INPUT TYPE=TEXT NAME=LSYSLSYSTXGAIN SIZE=3 VALUE="<%print $lsysdata["LSYSTXGAIN"];%>">
  </TD>
</TR>
</TR>
<TR  CLASS=list-color1>
  <TD onmouseover=myHint.show('ESXX') ONMOUSEOUT=myHint.hide()><%print _("Current IP Address.") . "<BR>" . _("This Must Be Set And Reachable From Your Browser To Initilise The Phone Correctly.");%></TD>
  <TD><INPUT TYPE=TEXT NAME=LSYSIPADDR VALUE="<%print $curipaddr;%>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover=myHint.show('ES8') ONMOUSEOUT=myHint.hide()><%print _("Enable NAT/DHCP On Lan Port");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=LSYSNAT<%if ($lsysdata["NAT"] == "NAT") {print " checked";}%>></TD>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT NAME=lsysreg VALUE="<%print _("Save");%>"></TD>
</TR>

</TABLE>
