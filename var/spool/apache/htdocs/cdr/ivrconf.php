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
require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");

if ($_POST['ivr'] == "") {
  $_POST['ivr'] = $_POST['newivr'];
}

$ifile=$_POST['ivr'];
$ifile.=($_POST['officehours'] == "on") ? "1" : "2";
$codech=array("wav","alaw","ulaw","g722","sln");
$codecl=array("WAV","g723","g729","gsm","ilbc");
$codecs=array_merge($codech,$codecl);

if (!file_exists("/var/lib/asterisk/sounds/custom/" . $ifile . ".WAV")) {
  if (!isset($agi)) {
    $agi=new AGI_AsteriskManager();
  }
  $agi->connect("127.0.0.1","admin","admin");

  if ((file_exists("/var/lib/asterisk/sounds/custom/" . $ifile . ".ulaw")) ||
      (!file_exists("/var/lib/asterisk/sounds/custom/" . $ifile . ".alaw"))) {
    $agi->command("file convert custom/" . $ifile . ".ulaw custom/" . $ifile . ".alaw");
  }

  if ((file_exists("/var/lib/asterisk/sounds/custom/" . $ifile . ".alaw")) ||
      (!file_exists("/var/lib/asterisk/sounds/custom/" . $ifile . ".gsm"))) {
    $agi->command("file convert custom/" . $ifile . ".alaw custom/" . $ifile . ".gsm");
  }

  if (file_exists("/var/lib/asterisk/sounds/custom/" . $ifile . ".gsm")) {
    for ($ccnt=0;$ccnt < count($codecl);$ccnt++) {
      if ($codecl[$ccnt] == "gsm") {
        continue;
      }
      $conv[$codecl[$ccnt]]=$agi->command("file convert custom/" . $ifile . ".gsm custom/" . $ifile . "." . $codecl[$ccnt]);
    }
  }
  $agi->disconnect();
}

if ($_POST[officehours] != on) {
  $myddi=$_POST['ivr'] . "-ah";
} else {
  $myddi = $_POST['ivr'];
}

$maxopt=13;

//print "<pre>" . print_r($_POST,TRUE) . "</pre>";

function getdialplan($action, $data) {
  global $ifile;
  switch ($action) {
    case "Speeddial":
    case "Extension": return "Goto(userout," . $data . ",1)";
    case "Voicemail": return "Voicemail(" . $data . "@6,su)";
    case "Queue": return "Goto(queues," . $data . ",1)";
    case "Hangup": return "Hangup";
    case "Reception": return "Goto(autoattendant,oper,1)";
    case "Background": return "Goto(autoattendant,prompt,play)";
  }
}

if (isset($_POST['saveivr'])) {

  $opt[10]="d";
  $opt[11]="t";
  $opt[12]="i";

  $hours=($_POST['officehours'] == "on") ? "t" : "f";

  print "DELETE FROM ivrconf WHERE ivr='" . $_POST['ivr'] . "' AND officehours='" . $hours . "'<BR>"; 
  for ($optcnt=0;$optcnt < $maxopt;$optcnt++) {
    $dest="destination" . $optcnt;
    if ($_POST[$dest] == "") {
      continue;
    }
    $sel="selection" . $optcnt;
    if ($opt[$optcnt] != "") {
      $option=$opt[$optcnt];
    } else {
      $option=$optcnt;
    }
    print "INSERT INTO ivrconf VALUES ('" . $_POST['ivr'] . "','" . $hours . "','" . $option . "','" .
           $_POST[$sel] . "','" . $_POST[$dest] . "','" . getdialplan($_POST[$sel], $_POST[$dest]) . "');<br>";
  }

  if ((is_array($_FILES)) && (count($_FILES) > 0) && (is_array($_FILES['greet']))) {
    if (! isset($agi)) {
      $agi=new AGI_AsteriskManager();
    }

    $agi->connect("127.0.0.1","admin","admin");
    $fileinf=$_FILES['greet'];
    $filebase = split("\.",basename($fileinf['name']));
    $type=strtolower($filebase[count($filebase)-1]);

    $convert=array("wav" => "/usr/bin/sox -t .wav " . $fileinf['tmp_name'] . " -c 1 -s -w -r 8000 ",
                   "mp3" => "/usr/bin/mpg123 -s -r 8000 -m " . $fileinf['tmp_name'] . "  |/usr/bin/sox -c 1 -t raw -s -w -r 8000 - ");

    if ($convert[$type] != "") {
      $filename="/tmp/" . uniqid("soxivr_") . ".wav"; 
      $wav=popen($convert[$type] . $filename,"r");
      pclose($wav);

      for ($ccnt=0;$ccnt < count($codecs);$ccnt++) {
        $conv[$codecs[$ccnt]]=$agi->command("file convert " . $filename . " custom/" . $ifile . "." . $codecs[$ccnt]);
      }
      @unlink($filename);
    }
    @unlink($fileinf['tmp_name']);
    $agi->disconnect();
  }
}
%>
<CENTER>
<FORM METHOD=POST NAME=ivrconf enctype="multipart/form-data" onsubmit=sendform.submit(this)>
<INPUT TYPE=hidden NAME=ivr VALUE=<%print $_POST['ivr'];%>>
<INPUT TYPE=hidden NAME=officehours VALUE=<%print $_POST['officehours'];%>>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
  <TH class=heading-body COLSPAN=3>IVR DDI Configuration for <% echo $myddi %></TH>
</TR>
<TR CLASS=list-color1>
  <TH CLASS=heading-body2 WIDTH=10%>Option</TH>
  <TH CLASS=heading-body2 WIDTH=45%>Selection</TH>
  <TH CLASS=heading-body2 WIDTH=45%>Destination</TH>
</TR>
<%
for($selxml=0;$selxml < 10;$selxml++) {%>
<TR align=center CLASS=list-color<%print ((($selxml+1) % 2) + 1);%>>
  <Td><%print $selxml;%></Td>
  <Td>
    <select name=selection<%print $selxml;%> onchange=ivropt[<%print $selxml;%>].submit() style="width:80%">
      <option value=""></option>
      <option value="Extension">Extension</option>
      <option value="Queue">Queue</option>
      <option value="Reception">Reception</option>
      <option value="Speeddial">External(Speed Dial)</option>
      <option value="Voicemail">Voicemail</option>
      <option value="Hangup">Hangup</option>
    </select>
  </Td>
  <Td>
    <select name="destination<%print $selxml;%>" style="width:80%">
    </select>
  </Td> 
</TR>
<%
}
%>
<TR align=center CLASS=list-color2>
  <Td>Default</Td>
  <Td>
    <select name=selection10 onchange=ivropt[10].submit() style="width:80%">
      <option value="Reception">Reception</option>
      <option value="Hangup">Hangup</option>
    </select>
  </Td>
  <Td>
    <select name=destination10 style="width:80%">                                                                 
    </select>
  </Td>
</TR>
<TR align=center CLASS=list-color1>
  <Td>Time Out</Td>
  <Td>
    <select name=selection11 onchange=ivropt[11].submit() style="width:80%">
      <option value="Reception">Reception</option>
      <option value="Hangup">Hangup</option>
    </select>
  </Td>
  <Td>
    <select name=destination11 style="width:80%">
      <option value="oper">Operator</option>
    </select>
  </Td>
</TR>
<TR align=center CLASS=list-color2>
  <Td>Invalid</Td>
  <Td>
    <select name=selection12 style="width:80%">
      <option value="Background">Play message again</option>
    </select>
  </Td>
  <Td>
    <select name=destination12 style="width:80%">
      <option value="ivr-<% echo $myddi %>">Back to IVR</option>
    </select>
  </Td>
</TR>
<TR CLASS=list-color1><%
if (file_exists("/var/lib/asterisk/sounds/custom/" . $ifile . ".WAV")) {
  print "<TH CLASS=heading-body2 WIDTH=100 COLSPAN=3>Greeting File</TH></TR>";
  print "<TR align=center CLASS=list-color1><TD COLSPAN=3 ALIGN=MIDDLE>";
  print "<embed src=\"playfile.php?file=" . $ifile . ".WAV\" autostart=false loop=false height=62 width=450><P>";
  print "Upload: <input name=greet size=27 type=file>";
} else {
  print "<TH CLASS=heading-body2 WIDTH=100 COLSPAN=3>Upload Greeting File</TH></TR>";
  print "<TR align=center CLASS=list-color1><TD COLSPAN=3 ALIGN=MIDDLE>";
  print "<input name=greet size=27 type=file>";
}
%>
  </TD>
</TR>
<TR align=center CLASS=list-color2>
  <TD COLSPAN=3 ALIGN=MIDDLE>
    <input type="submit" name="saveivr" value="Submit">
  </TD>
</TR>
</TABLE>
</FORM>
<script>
<%
for($selxml=0;$selxml < $maxopt;$selxml++) {%>
  ivropt[<%print $selxml;%>] = new XMLSelect(document.ivrconf.selection<%print $selxml;%>,"/cdr/ivrxml.php",document.ivrconf.destination<%print $selxml;%>);
<%
}
%>
</script>
