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

include_once "auth.inc";
require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");

$maxopt=13;

if ($_POST['ivr'] == "") {
  $_POST['ivr'] = $_POST['newivr'];
}

$ifile=$_POST['ivr'] . $_POST['hours'];

$sper=array("1" => "Office Hours", "2" => "After Hours", "3" => "Public Holiday");

$codecw=array("sln16","g722");
$codech=array("wav","alaw","ulaw","sln");
$codecl=array("WAV","g723","g729","gsm","ilbc");
$codecs=array_merge($codecw,$codech,$codecl);

$snddir="/var/lib/asterisk/sounds/custom/";

if (!file_exists($snddir . $ifile . ".WAV")) {
  if (!isset($agi)) {
    $agi=new AGI_AsteriskManager();
  }
  $agi->connect("127.0.0.1","admin","admin");

  /*sln16 is the best i can do so do it*/
  if (file_exists($snddir . $ifile . ".sln16")) {
    $bestlaw="sln16";
    for ($ccnt=0;$ccnt < count($codecs);$ccnt++) {
      if (!file_exists($snddir . $ifile . "." . $codecs[$ccnt])) {
        $agi->command("file convert custom/" . $ifile . "." . $bestlaw . " custom/" . $ifile . "." . $codecs[$ccnt]);
      }
    }
  }

  /*if i have a .wav file striping the header will give me .sln if i dont have it*/
  if ((file_exists($snddir . $ifile . ".wav")) &&
      (!file_exists($snddir . $ifile . ".sln"))) {
    $agi->command("file convert custom/" . $ifile . "." . $bestlaw . " custom/" . $ifile . ".sln");
  }

  /*sln will be fine for below its the top of the 8k dogs*/
  if (file_exists($snddir . $ifile . ".sln")) {
    $bestlaw=($bestlaw != "") ? $bestlaw : "sln";
    for ($ccnt=0;$ccnt < count($codech);$ccnt++) {
      if (!file_exists($snddir . $ifile . "." . $codech[$ccnt])) {
        $agi->command("file convert custom/" . $ifile . "." . $bestlaw . " custom/" . $ifile . "." . $codech[$ccnt]);
      }
    }
  }

  /*i have no clean audio files let me try g711 and g722 bias to 8k [g711]*/
  if ($bestlaw == "") {
    if (file_exists($snddir . $ifile . ".alaw")) {
      $bestlaw=($bestlaw != "") ? $bestlaw : "alaw";
    } else if (file_exists($snddir . $ifile . ".ulaw")) {
      $bestlaw=($bestlaw != "") ? $bestlaw : "ulaw";
    } else if (file_exists($snddir . $ifile . ".g722")) {
      $bestlaw=($bestlaw != "") ? $bestlaw : "g722";
    }
  }

  /*grab my compresed 8k codecs while i can am i missing sln ?? from now on gsm will be best this is horid*/
  if ($bestlaw != "") {
    if (!file_exists($snddir . $ifile . ".alaw")) {
      $agi->command("file convert custom/" . $ifile . "." . $bestlaw ." custom/" . $ifile . ".alaw");
    }
    if (!file_exists($snddir . $ifile . ".ulaw")) {
      $agi->command("file convert custom/" . $ifile . "." . $bestlaw . " custom/" . $ifile . ".ulaw");
    }
  } else if (file_exists($snddir . $ifile . ".gsm")) {
    $bestlaw="gsm";
  }

  /*the lossy codecs are we converting from gsm that is not good*/
  if ($bestlaw != "") {
    for ($ccnt=0;$ccnt < count($codecl);$ccnt++) {
      if (!file_exist($snddir . $ifile . "." . $codecl[$ccnt])) {
        $conv[$codecl[$ccnt]]=$agi->command("file convert custom/" . $ifile . "." . $bestlaw . " custom/" . $ifile . "." . $codecl[$ccnt]);
      }
    }
  }
  $agi->disconnect();
}

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

  if (($_POST['hours'] >= 1) && ($_POST['hours'] < 3)) {
    $hours=($_POST['hours'] == "1") ? "t" : "f";

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
  }

  if ((is_array($_FILES)) && (count($_FILES) > 0) && (is_array($_FILES['greet']))) {
    if (! isset($agi)) {
      $agi=new AGI_AsteriskManager();
    }

    $agi->connect("127.0.0.1","admin","admin");
    $fileinf=$_FILES['greet'];
    $filebase = split("\.",basename($fileinf['name']));
    $type=strtolower($filebase[count($filebase)-1]);

    $convert=array("wav" => "/usr/bin/sox -t .wav " . $fileinf['tmp_name'] . " -t raw -c 1 -s -w -r 16000 ",
                   "mp3" => "/usr/bin/mpg123 -s -r 16000 -m " . $fileinf['tmp_name'] . "  |/usr/bin/sox -c 1 -t raw -s -w -r 16000 - -t raw -c 1 -s -w -r 16000 ");

    if ($convert[$type] != "") {
      $filename="/tmp/" . uniqid("soxivr_") . ".sln16"; 
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
?>
<CENTER>
<FORM METHOD=POST NAME=ivrconf enctype="multipart/form-data" onsubmit=sendform.submit(this)>
<INPUT TYPE=hidden NAME=ivr VALUE=<?php print $_POST['ivr'];?>>
<INPUT TYPE=hidden NAME=hours VALUE=<?php print $_POST['hours'];?>>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
  <TH class=heading-body COLSPAN=3>IVR DDI Configuration for <?php
print $_POST['ivr'];
if ($_POST['hours'] != "") {
  print " (" . $sper[$_POST['hours']] . ")";
}
?></TH>
</TR>
<?php
if (($_POST['hours'] >= 1) && ($_POST['hours'] < 3)) {
?>
  <TR CLASS=list-color1>
    <TH CLASS=heading-body2 WIDTH=10%>Option</TH>
    <TH CLASS=heading-body2 WIDTH=45%>Selection</TH>
    <TH CLASS=heading-body2 WIDTH=45%>Destination</TH>
  </TR>
<?php
  for($selxml=0;$selxml < 10;$selxml++) {?>
  <TR align=center CLASS=list-color<?php print ((($selxml+1) % 2) + 1);?>>
    <Td><?php print $selxml;?></Td>
    <Td>
      <select name=selection<?php print $selxml;?> onchange=ivropt[<?php print $selxml;?>].submit() style="width:80%">
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
      <select name="destination<?php print $selxml;?>" style="width:80%">
      </select>
    </Td> 
  </TR>
<?php
  }
?>
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
      </select>
    </Td>
  </TR>
<?php
}
?>
<TR CLASS=list-color1><?php
if (file_exists($snddir . $ifile . ".WAV")) {
  print "<TH CLASS=heading-body2 WIDTH=100 COLSPAN=3>Greeting File</TH></TR>";
  print "<TR align=center CLASS=list-color1><TD COLSPAN=3 ALIGN=MIDDLE>";
  print "<embed src=\"playfile.php?file=" . $ifile . ".WAV\" autostart=false loop=false height=62 width=450><P>";
  print "Upload: <input name=greet size=27 type=file>";
} else {
  print "<TH CLASS=heading-body2 WIDTH=100 COLSPAN=3>Upload Greeting File</TH></TR>";
  print "<TR align=center CLASS=list-color1><TD COLSPAN=3 ALIGN=MIDDLE>";
  print "<input name=greet size=27 type=file>";
}
?>
  </TD>
</TR>
<TR align=center CLASS=list-color2>
  <TD COLSPAN=3 ALIGN=MIDDLE>
    <input type="submit" name="saveivr" value="Submit">
  </TD>
</TR>
</TABLE>
</FORM>
<?php
if (($_POST['hours'] >= 1) && ($_POST['hours'] < 3)) {
  print "<script>\n";
  for($selxml=0;$selxml < $maxopt;$selxml++) {?>
  ivropt[<?php print $selxml;?>] = new XMLSelect(document.ivrconf.selection<?php print $selxml;?>,"/cdr/ivrxml.php",document.ivrconf.destination<?php print $selxml;?>);
<?php
  }
  print "</script>\n";
}
?>
