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

if ((isset($pbxdelete)) && ($key != "")){
  pg_query($db,"DELETE FROM provider WHERE trunkprefix='$key'");
  pg_query($db,"DELETE FROM trunk WHERE trunkprefix='$key'");
  $key="";

}

if ($key == "") {
  $qgetdata=pg_query($db,"SELECT trunkprefix,name FROM provider ORDER BY name");
%>

<CENTER>
<FORM METHOD=POST NAME=h323pform onsubmit="ajaxsubmit(this.name);return false;">
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH COLSPAN=2 CLASS=heading-body><%print _("Select Voip Provider");%></TH>
</TR>
<TR CLASS=list-color1>
<TD WIDTH=50%><%print _("Select Provider To Edit/Delete");%></TD>
<TD WIDTH=50%><SELECT NAME=key onchange=this.form.subme.click()>
<OPTION>Select Gateway Provider</OPTION>
<%
$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[1] . "</OPTION>"; 
}
%>
</SELECT>
</TR>
<TR CLASS=list-color2>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=SUBMIT NAME=subme onclick=this.name='pbxupdate' VALUE="<%print _("Edit Gateways");%>">
  </TD>
</TR>
</TABLE>
</FORM>
<%
} else {
  if ((isset($gwdelete)) && ($gwid != "")) {
    pg_query($db,"DELETE FROM trunk WHERE gwid='" . $gwid . "'");
    $gwid="";
    $description="";
    $providerip="";
    $h323gkid="";
    $h323prefix="";
    $h323reggk="";
    $protocol="";
  }
  if ((isset($gwupdate)) && ($gwidup != "")) {
    pg_query($db,"UPDATE trunk SET h323prefix='" . $h323prefix . "',description='" . $description . "',providerip='" . $providerip . "',h323gkid='" . $h323gkid . "',h323reggk='" . $h323reggk . "',protocol='" . $protocol . "' WHERE gwid='" . $gwidup . "'");
    $description="";
    $providerip="";
    $h323gkid="";
    $h323prefix="";
    $h323reggk="";
    $protocol="";
  }
  if ((isset($gwselect)) && ($gwid == "")) {
    pg_query($db,"INSERT INTO trunk (description,providerip,h323gkid,h323reggk,trunkprefix,h323prefix,protocol) VALUES " .
	"('$description','$providerip','$h323gkid','$h323reggk','$key','$h323prefix','$protocol')");
    $description="";
    $providerip="";
    $h323gkid="";
    $h323prefix="";
    $h323reggk="";
    $protocol="";
  }
  if ((isset($gwselect)) && ($gwid != "")) {
    $gwdataq=pg_query($db,"SELECT description,providerip,h323gkid,h323reggk,h323prefix,protocol " .
	"FROM trunk WHERE gwid='" . $gwid . "' LIMIT 1");
    $getgwdata=pg_fetch_array($gwdataq,0);
    $description=$getgwdata[0];
    $providerip=$getgwdata[1];
    $h323gkid=$getgwdata[2];
    $h323reggk=$getgwdata[3];
    $h323prefix=$getgwdata[4];
    $protocol=$getgwdata[5];
    $qgetdata=pg_query($db,"SELECT description||' ('||trunk.h323gkid||':'||providerip||' On '||users.h323gkid||':'||fullname||')' FROM trunk left outer join users on (h323reggk=users.h323gkid) WHERE h323neighbor = 't' AND gwid = '" . $gwid . "' AND trunkprefix='" . $key . "' ORDER BY description LIMIT 1");
  } else {
    $qgetdata=pg_query($db,"SELECT gwid,description||' ('||trunk.h323gkid||':'||providerip||' On '||users.h323gkid||':'||fullname||')' FROM trunk left outer join users on (h323reggk=users.h323gkid) WHERE h323neighbor = 't' AND trunkprefix='" . $key . "' AND protocol = 'OH323' ORDER BY description");
  }
%>

<CENTER>
<FORM METHOD=POST NAME=h323pform onsubmit="ajaxsubmit(this.name);return false;">
<INPUT TYPE=HIDDEN NAME=key VALUE="<%print $key;%>">
<%
if ($gwid != "") {
  print "<INPUT TYPE=HIDDEN NAME=gwidup VALUE=\"" . $gwid . "\">";
}
%>
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH COLSPAN=2 CLASS=heading-body><%print _("Gateway Configuration");%></TH>
</TR>
<TR CLASS=list-color1>
<%
if ($gwid == "") {
%>
<TD WIDTH=50%><%print _("Select Gateway To Edit/Delete");%></TD>
<TD>
<SELECT NAME=gwid>
<OPTION VALUE=""><%print _("Add New Gateway Bellow");%></OPTION>
<%
$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[1] . "</OPTION>"; 
}
$qgetdata=pg_query($db,"SELECT gwid,description||' ('||protocol||' ['||providerip||'])' from trunk where protocol != 'OH323' order by description,protocol,gwid");
$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[1] . "</OPTION>"; 
}

%>
</SELECT>
<%
} else {
  $getdata=pg_fetch_array($qgetdata,$i);
%>
<TH COLSPAN=2 CLASS=heading-body2>Editing <%print $getdata[0]%></TH>
<%
}
%>
</TR>

<TR CLASS=list-color2>
<TD WIDTH=50%><%print _("Description");%></TD>
<TD><INPUT TYPE=TEXT NAME=description VALUE="<%print $description;%>"></TD>
</TR>
<TR CLASS=list-color1>
<TD><%print _("I.P. Address/Peer Name");%></TD>
<TD><INPUT TYPE=TEXT NAME=providerip VALUE="<%print $providerip;%>"></TD>
</TR>
<TR CLASS=list-color2>
<TD><%print _("Gateway ID");%></TD>
<TD><INPUT TYPE=TEXT NAME=h323gkid VALUE="<%print $h323gkid;%>"></TD>
</TR>
<TR CLASS=list-color1>
<TD><%print _("Gateway Prefix (Sent)");%></TD>
<TD><INPUT TYPE=TEXT NAME=h323prefix VALUE="<%print $h323prefix;%>"></TD>
</TR>
<TR CLASS=list-color2>
<TD><%print _("Connect Endpoint To") . " ...";%></TD>
<TD>
  <SELECT NAME=h323reggk>
<%
    $qgetdata=pg_query($db,"SELECT h323gkid,fullname||' ('||h323gkid||')' from users where h323neighbor = 't'");
    $dnum=pg_num_rows($qgetdata);
    for($i=0;$i<$dnum;$i++){
      $getdata=pg_fetch_array($qgetdata,$i);
      print "<OPTION VALUE=" . $getdata[0];
      if ($h323reggk == $getdata[0]) {
        print " SELECTED";
      }
      print ">" . $getdata[1] . "</OPTION>"; 
    }
%>
  </SELECT>
</TD>
<TR CLASS=list-color1>
<TD><%print _("Protocol");%></TD>
<TD>
  <SELECT NAME=protocol>
    <OPTION VALUE="OH323"<%if ($protocol == "OH323") {print " SELECTED";}%>>H323
    <OPTION VALUE="SIP"<%if ($protocol == "SIP") {print " SELECTED";}%>>SIP
    <OPTION VALUE="IAX2"<%if ($protocol == "IAX2") {print " SELECTED";}%>>IAX
    <OPTION VALUE="Local"<%if ($protocol == "Local") {print " SELECTED";}%>>Local
    <OPTION VALUE="Peer"<%if ($protocol == "Peer") {print " SELECTED";}%>>Peer
<%
 
%>
  </SELECT>
</TD>
<TR CLASS=list-color2>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=RESET>
<%
  if ($gwid == "") {
    print "<INPUT TYPE=SUBMIT onclick=this.name='gwdelete' VALUE=\"" . _("Delete Gateway") . "\">";
    print "<INPUT TYPE=SUBMIT name=subme onclick=this.name='gwselect' VALUE=\"" . _("Add/Edit Gateway") . "\">";
  } else {
    print "<INPUT TYPE=SUBMIT onclick=this.name='gwupdate' VALUE=\"" . _("Save Changes") . "\">";
  }
%>
  </TD>
</TR>
</TABLE>
</FORM>

<%
}
%>
