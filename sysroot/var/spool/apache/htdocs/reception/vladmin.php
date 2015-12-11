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

/*
TIMEON=${RTDB(${CALLERIDNUM}/LastCall)})
NUMBER=${RTDB(${CALLERIDNUM}/RepeatDial)})
*/

$astpaths=array("lib64/x86_64", "libx32/i686", "lib/i686", "lib64", "lib");
while(list($astidx, $astlib) = each($astpaths)) {
  if (is_dir("/usr/" . $astlib . "/asterisk/modules-10/")) {
    $astmodpath="/usr/" . $astlib . "/asterisk/modules-10/";
  }
}

$codec[0]="g723.1";
$codec[1]="g729";
$codec[2]="gsm";
$codec[3]="speex";
$codec[4]="ilbc";
$codec[5]="g726";
$codec[6]="ulaw";
$codec[7]="alaw";
$codec[8]="h263p";
$codec[9]="h263";
$codec[10]="h261";

$codecd[0]=_("G723 Low Bandwidth");
$codecd[1]=_("G729 Low Bandwidth");
$codecd[2]=_("GSM Medium Bandwidth");
$codecd[3]=_("SPEEX Medium Bandwidth");
$codecd[4]=_("ILBC Medium Bandwidth");
$codecd[5]=_("G726 Medium Bandwidth");
$codecd[6]=_("uLAW High Bandwidth");
$codecd[7]=_("aLAW High Bandwidth");
$codecd[8]=_("h263+ Video Codec");
$codecd[9]=_("h263 Video Codec");
$codecd[10]=_("h261 Video Codec");

if (isset($pbxupdate)) {
  if ($CFIM == "") {
    $CFIM="0";
  }
  if ($CFBU == "") {
    $CFBU="0";
  }
  if ($CFNA == "") {
    $CFNA="0";
  }

  if ($CDND == "on") {
    $CDND="1";
  } else {
    $CDND="0";
  }

  if ($WAIT == "on") {
    $WAIT="1";
  } else {
    $WAIT="0";
  }

  if ($IAXLine == "on") {
    $IAXLine="1";
  } else {
    $IAXLine="0";
  }

  if ($Locked == "on") {
    $Locked="1";
  } else {
    $Locked="0";
  }

  if ($NOVMAIL == "on") {
    $NOVMAIL="1";
  } else {
    $NOVMAIL="0";
  }

  if ($qualify == "on") {
    $qualify="yes";
  } else {
    $qualify="";
  }

  if ($canreinvite == "on") {
    $canreinvite="yes";
  } else {
    $canreinvite="no";
  }

  pg_query($db,"UPDATE astdb SET value='" . $CDND . "' WHERE family='" . $exten . "' AND key='CDND'");
  pg_query($db,"UPDATE astdb SET value='" . $CFBU . "' WHERE family='" . $exten . "' AND key='CFBU'");
  pg_query($db,"UPDATE astdb SET value='" . $CFIM . "' WHERE family='" . $exten . "' AND key='CFIM'");
  pg_query($db,"UPDATE astdb SET value='" . $CFNA . "' WHERE family='" . $exten . "' AND key='CFNA'");
  pg_query($db,"UPDATE astdb SET value='" . $WAIT . "' WHERE family='" . $exten . "' AND key='WAIT'");
  pg_query($db,"UPDATE astdb SET value='" . $NOVMAIL . "' WHERE family='" . $exten . "' AND key='NOVMAIL'");
  pg_query($db,"UPDATE astdb SET value='" . $TOUT . "' WHERE family='" . $exten . "' AND key='TOUT'");
  pg_query($db,"UPDATE astdb SET value='" . $Locked . "' WHERE family='" . $exten . "' AND key='Locked'");



  if (strlen($exten) > 4) {
    pg_query($db,"UPDATE astdb SET value='" . $IAXLine . "' WHERE family='" . $exten . "' AND key='IAXLine'");
    $codecs=$codec[$acodec1] . ";" . $codec[$acodec2] . ";" . $codec[$acodec3] . ";" . $codec[$vcodec1] . ";" . $codec[$vcodec2] . ";" . $codec[$vcodec3];
    pg_query($db,"UPDATE users SET nat='$nat',dtmfmode='$dtmfmode',fullname='$fullname',email='$email',
                                canreinvite='$canreinvite',qualify='$qualify',allow='$codecs',activated='t'
                                WHERE name='$exten'");
  } else {
    pg_query($db,"UPDATE users SET fullname='$fullname',email='$email',activated='t'
                                  WHERE name='$exten'");
  }                      
  if (($pass1 == $pass2) && ($pass1 != "")){
    pg_query($db,"UPDATE users SET secret='$pass1',password='$pass1' WHERE name='$exten'");
  } else if ($pass1 != "") {
%>
    <SCRIPT>
      alert("Password Mismach/Unset.Password Unchanged");
    </SCRIPT>
<%
  }
}

$qgetdata=pg_query($db,"SELECT key,value FROM astdb WHERE family='" . $exten . "'");
$qgetudata=pg_query($db,"SELECT nat,dtmfmode,fullname,email,canreinvite,qualify,password,allow FROM users WHERE name='" . $exten . "'");

$udata=pg_fetch_array($qgetudata,0);
$nat=$udata[0];
$dtmfmode=$udata[1];
$fullname=$udata[2];
$email=$udata[3];
$canreinvite=$udata[4];
$qualify=$udata[5];
$password=$udata[6];
$codecs=split(";",$udata[7]);

$acodec=array();
$vcodec=array();

while($icodec=array_shift($codecs)) {
  if (strstr($icodec,"h26")) {
    array_push($vcodec,$icodec);
  } else {
    array_push($acodec,$icodec);
  }
}

$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  $origdata[$getdata[0]]=$getdata[1]; 
}

if ($origdata["CDND"] == "") {
  $origdata["CDND"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','CDND','0')");
}
if ($origdata["CFBU"] == "") {
  $origdata["CFBU"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','CFBU','0')");
}
if ($origdata["CFIM"] == "") {
  $origdata["CFIM"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','CFIM','0')");
}
if ($origdata["CFNA"] == "") {
  $origdata["CFNA"]="";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','CFNA','0')");
}

if ($origdata["WAIT"] == "") {
  $origdata["WAIT"]="1";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','WAIT','1')");
}

if ($origdata["IAXLine"] == "") {
  $origdata["IAXLine"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','IAXLine','0')");
}

if ($origdata["Locked"] == "") {
  $origdata["Locked"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','Locked','0')");
}

if ($origdata["NOVMAIL"] == "") {
  $origdata["NOVMAIL"]="0";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','NOVMAIL','0')");
}

if ($origdata["TOUT"] == "") {
  $origdata["TOUT"]="40";
  pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','TOUT','40')");
}

%>

<link rel="stylesheet" type="text/css" href="/style.php?style=<%print $style;%>">
<script language="JavaScript" src="/java_popups.php" type="text/javascript"></script>
<script language="JavaScript" src="/hints.js" type="text/javascript"></script>
<script language="JavaScript" src="/hints_cfg.php?disppage=reception%2Fmkuser.php" type="text/javascript"></script>

<CENTER>
<FORM METHOD=POST ACTION=/reception/vladmin.php>
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<%
  if (strlen($exten) > 4) {
%>
<TR CLASS=list-color2>
  <TH COLSPAN=2 CLASS=heading-body><%print _("Configuration For Account") . " " . $exten%></TH>
</TR>
<TR CLASS=list-color1>
  <TD><%print _("NAT Handling");%></TD>
  <TD>
    <SELECT NAME=nat>
      <OPTION VALUE=no <%if ($nat == "no") {print " SELECTED";}%>><%print _("Use NAT If Required");%></OPTION>
      <OPTION VALUE=yes <%if ($nat == "yes") {print " SELECTED";}%>><%print _("Always Use Nat");%></OPTION>
      <OPTION VALUE=never <%if ($nat == "never") {print " SELECTED";}%>><%print _("Never Use NAT");%></OPTION>
      <OPTION VALUE=route <%if ($nat == "route") {print " SELECTED";}%>><%print _("Assume NAT Dont Send Port");%></OPTION>
    </SELECT>
</TR>
<TR CLASS=list-color2>
  <TD><%print _("DTMF Handling");%></TD>
  <TD>
    <SELECT NAME=dtmfmode>
      <OPTION VALUE=rfc2833 <%if ($dtmfmode == "rfc2833") {print " SELECTED";}%>><%print _("Use Standard DTMF");%></OPTION>
      <OPTION VALUE=info <%if ($dtmfmode == "info") {print " SELECTED";}%>><%print _("Send DTMF In SIP INFO");%></OPTION>
      <OPTION VALUE=inband <%if ($dtmfmode == "inband") {print " SELECTED";}%>>print _("Send DTMF Inband");%></OPTION>
    </SELECT>
</TR>
<TR CLASS=list-color1>
  <TD><%print _("First Audio Codec Choice");%></TD>
  <TD>
    <SELECT NAME=acodec1>
      <%if (is_file("$astmodpath/codec_g723.so")) {
          print "<OPTION VALUE=0";
          if ($acodec[0] == $codec[0]) {
            print " SELECTED";
          }
          print ">" . $codecd[0] . "</OPTION>\n";
        }
        if (is_file("$astmodpath/codec_g729.so")) {
          print "<OPTION VALUE=1";
          if ($acodec[0] == $codec[1]) {
            print " SELECTED";
          }
          print ">" . $codecd[1] . "</OPTION>\n";
        }
        for ($i=2;$i<=7;$i++) {
          print "<OPTION VALUE=" . $i;
          if ($acodec[0] == $codec[$i]) {
            print " SELECTED";
          }
          print ">" . $codecd[$i] . "</OPTION>\n";          
        }%>
    </SELECT>
  </TD>
</TR>

<TR CLASS=list-color2>
  <TD><%print _("Second Audio Codec Choice");%></TD>
  <TD>
    <SELECT NAME=acodec2>
      <%if (is_file("$astmodpath/codec_g723.so")) {
          print "<OPTION VALUE=0";
          if ($acodec[1] == $codec[0]) {
            print " SELECTED";
          }
          print ">" . $codecd[0] . "</OPTION>\n";
        }
        if (is_file("$astmodpath/codec_g729.so")) {
          print "<OPTION VALUE=1";
          if ($acodec[1] == $codec[1]) {
            print " SELECTED";
          }
          print ">" . $codecd[1] . "</OPTION>\n";
        }
        for ($i=2;$i<=7;$i++) {
          print "<OPTION VALUE=" . $i;
          if ($acodec[1] == $codec[$i]) {
            print " SELECTED";
          }
          print ">" . $codecd[$i] . "</OPTION>\n";          
        }%>
    </SELECT>
  </TD>
</TR>

<TR CLASS=list-color1>
  <TD><%print _("Third Audio Codec Choice");%></TD>
  <TD>
    <SELECT NAME=acodec3>
      <%if (is_file("$astmodpath/codec_g723.so")) {
          print "<OPTION VALUE=0";
          if ($acodec[2] == $codec[0]) {
            print " SELECTED";
          }
          print ">" . $codecd[0] . "</OPTION>\n";
        }
        if (is_file("$astmodpath/codec_g729.so")) {
          print "<OPTION VALUE=1";
          if ($acodec[2] == $codec[1]) {
            print " SELECTED";
          }
          print ">" . $codecd[1] . "</OPTION>\n";
        }
        for ($i=2;$i<=7;$i++) {
          print "<OPTION VALUE=" . $i;
          if ($acodec[2] == $codec[$i]) {
            print " SELECTED";
          }
          print ">" . $codecd[$i] . "</OPTION>\n";          
        }%>
    </SELECT>
  </TD>
</TR>

<TR CLASS=list-color2>
  <TD><%print _("First Video Codec Choice");%></TD>
  <TD>
    <SELECT NAME=vcodec1><%
        for ($i=8;$i<=10;$i++) {
          print "<OPTION VALUE=" . $i;
          if ($vcodec[0] == $codec[$i]) {
            print " SELECTED";
          }
          print ">" . $codecd[$i] . "</OPTION>\n";          
        }%>
    </SELECT>
  </TD>
</TR>

<TR CLASS=list-color1>
  <TD><%print _("Second Video Codec Choice");%></TD>
  <TD>
    <SELECT NAME=vcodec2><%
        for ($i=8;$i<=10;$i++) {
          print "<OPTION VALUE=" . $i;
          if ($vcodec[1] == $codec[$i]) {
            print " SELECTED";
          }
          print ">" . $codecd[$i] . "</OPTION>\n";          
        }%>
    </SELECT>
  </TD>
</TR>

<TR CLASS=list-color2>
  <TD><%print _("Third Video Codec Choice");%></TD>
  <TD>
    <SELECT NAME=vcodec3><%
        for ($i=8;$i<=10;$i++) {
          print "<OPTION VALUE=" . $i;
          if ($vcodec[2] == $codec[$i]) {
            print " SELECTED";
          }
          print ">" . $codecd[$i] . "</OPTION>\n";          
        }%>
    </SELECT>
  </TD>
</TR>
<%
} else {
%>
<TR CLASS=list-color2>
  <TH COLSPAN=2 CLASS=heading-body><%print _("Configuration For Account") . " " . $exten%></TH>
</TR>
<%
}
%>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('ES1')" onmouseout="myHint.hide()"><%print _("Fullname");%></TD>
  <TD><INPUT TYPE=TEXT NAME=fullname VALUE="<%print $fullname;%>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('ES2')" onmouseout="myHint.hide()"><%print _("Email Address");%></TD>
  <TD><INPUT TYPE=TEXT NAME=email VALUE="<%print $email;%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('ES3')" onmouseout="myHint.hide()"><%print _("Call Forward Immeadiate");%></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=CFIM VALUE="<%if ($origdata["CFIM"] != "0") {print $origdata["CFIM"];}%>">
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('ES4')" onmouseout="myHint.hide()"><%print _("Call Forward On Busy");%></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=CFBU VALUE="<%if ($origdata["CFBU"] != "0") {print $origdata["CFBU"];}%>">
  </TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('ES5')" onmouseout="myHint.hide()"><%print _("Call Forward On No Answer");%></TD>
  <TD>
     <INPUT TYPE=TEXT NAME=CFNA VALUE="<%if ($origdata["CFNA"] != "0") {print $origdata["CFNA"];}%>">
  </TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('ES7')" onmouseout="myHint.hide()"><%print _("Ring Timeout");%></TD>
  <TD><INPUT TYPE=TEXT NAME=TOUT VALUE="<%print $origdata["TOUT"];%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('ES14')" onmouseout="myHint.hide()"><%print _("Password");%></TD>
  <TD><INPUT TYPE=PASSWORD NAME=pass1 VALUE="<%print $password;%>"></TD>
</TR>
<TR CLASS=list-color2>
  <TD  onmouseover="myHint.show('ES15')" onmouseout="myHint.hide()"><%print _("Confirm Password");%></TD>
  <TD><INPUT TYPE=PASSWORD NAME=pass2 VALUE="<%print $password;%>"></TD>
</TR>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('ES8')" onmouseout="myHint.hide()"><%print _("Call Waiting");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=WAIT <%if ($origdata["WAIT"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('ES9')" onmouseout="myHint.hide()"><%print _("Do Not Disturb");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=CDND <%if ($origdata["CDND"] == "1") {print "CHECKED";}%>></TD>
</TR>
<%
  if (strlen($exten) > 4) {
%>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('ES37')" onmouseout="myHint.hide()"><%print _("Allow Peer To Peer Connections (Reinvite)");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=canreinvite <%if ($canreinvite == "yes") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('ES39')" onmouseout="myHint.hide()"><%print _("Send Nat Keep Alive Packets");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=qualify <%if ($qualify == "yes") {print "CHECKED";}%>></TD>
</TR>
<%
}
%>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('ES20')" onmouseout="myHint.hide()"><%print _("Disable Voice Mail");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=NOVMAIL <%if ($origdata["NOVMAIL"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color2>
  <TD onmouseover="myHint.show('ES21')" onmouseout="myHint.hide()"><%print _("Lock Extension");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=Locked <%if ($origdata["Locked"] == "1") {print "CHECKED";}%>></TD>
</TR>
<%
  if (strlen($exten) > 4) {
%>
<TR CLASS=list-color1>
  <TD onmouseover="myHint.show('ES33')" onmouseout="myHint.hide()"><%print _("Use IAX As VOIP Protocol");%></TD>
  <TD><INPUT TYPE=CHECKBOX NAME=IAXLine <%if ($origdata["IAXLine"] == "1") {print "CHECKED";}%>></TD>
</TR>
<TR CLASS=list-color2>
<%
} else {
%>
<TR CLASS=list-color1>
  <TD COLSPAN=2 ALIGN=CENTER><A HREF=javascript:snomkeyview('<%print $exten;%>','kp') onmouseover="myHint.show('ES27')" onmouseout="myHint.hide()"><%print _("Snom320 Keypad Template Keys 1-12");%></A></TD>
</TR>
<TR CLASS=list-color2>
  <TD COLSPAN=2 ALIGN=CENTER><A HREF=javascript:snomkeyview('<%print $exten;%>','xp') onmouseover="myHint.show('ES27')" onmouseout="myHint.hide()"><%print _("Snom320 Keypad Template Keys 13-54");%></A></TD>
</TR>
<TR CLASS=list-color1>
  <TD COLSPAN=2 ALIGN=CENTER>
<A HREF=/reception/pbook.php?style=<%print $style;%>><%print _("Snom Phone Book");%></A>
<A HREF=/reception/sdial.php?style=<%print $style;%>><%print _("Snom Speed Dials");%></A>
</TD>
</TR>
<TR CLASS=list-color2>
<%
}
%>
  <TD ALIGN=MIDDLE COLSPAN=2>
    <INPUT TYPE=RESET>
    <INPUT TYPE=SUBMIT NAME=pbxupdate VALUE="<%print _("Save Changes");%>">
  </TD>
</TR>
<%
  if (strlen($exten) > 4) {
%>
<TR CLASS=list-color1>
  <TD ALIGN=MIDDLE COLSPAN=2>
  <A HREF=/reception/callerid.php><%print _("Call Back Caller ID");%></A>
  </TD>
</TR>
<%
}
%>
</TABLE>
</FORM>
