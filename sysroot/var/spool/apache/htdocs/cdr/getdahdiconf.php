<?php

include_once "auth.inc";

if (! isset($agi)) {
  require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
  $agi=new AGI_AsteriskManager();
  $agi->connect("127.0.0.1","admin","admin");
}

$chans=$agi->command("dahdi show channels");

$dir = "/proc/dahdi/";
$files=array();

$bcol=0;
if (is_dir($dir)) {
  if ($dh = opendir($dir)) {
    while (($file = readdir($dh)) !== false) {
      if ($file > 0) {
        array_push($files,$file);
      }
    }
    closedir($dh);
  }
}

sort($files);
reset($files);

print "<CENTER><TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>";
print "<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=2>" . _("DAHDI Configuration") . "</TH></TR>\n";
$bcol=0;
$chan=1;
foreach ($files as $file) {
  $zfile=file($dir . $file);
  while(list($lnum,$ldata)=each($zfile)) {
    $ldata=rtrim($ldata);
    if (preg_match("/^Span ([0-9]+: [WBT].*)\$/i",$ldata)) {
      print "<TR CLASS=list-color" . (($bcol %2) + 1). "><TH COLSPAN=2 CLASS=heading-body2>" . $ldata . "</TH></TR>\n";
      $bcol++;
    } else if (preg_match("/^.*([0-9]+) ([BXTW].*)\$/i",$ldata,$chaninf)) {
      print "<TR CLASS=list-color" . (($bcol %2) + 1). "><TD>" . $chan . "</TD><TD>" . $chaninf[2] . "</TD></TR>\n";
      $bcol++;
      $chan++;
    } else if ((! preg_match("/^Span ([0-9]+: [Z].*)\$/i",$ldata)) && ($ldata != "")) {
      print "<TR CLASS=list-color" . (($bcol %2) + 1). "><TD COLSPAN=2>"  . $ldata . "</TD></TR>\n";
      $bcol++;
    }
  }
}

if ($bcol == "0") {
  print "<TR CLASS=list-color" . (($bcol %2) + 1). "><TD>This System Has No DAHDI Interfaces</TD></TR>";
  $bcol++;
}

print "<TR CLASS=list-color" . (($bcol %2) + 1). ">";
?>
  
    <TH COLSPAN=2 CLASS=heading-body><?php print _("DAHDI Channels");?></TH>
  </TR>

<?php

$bcol++;
print "<TR CLASS=list-color" . (($bcol % 2) + 1). "><TD COLSPAN=2 ALIGN=MIDDLE>";
foreach(explode("\n",$chans['data']) as $line) {
  if (! preg_match("/(^Privilege: Command)|(^[ ]+Chan)|(^$)|(^[ ]+pseudo)/",$line)) {
    preg_match("/^[ ]+([0-9]+)[ ]+([0-9d]+)/",$line,$data);
    if (($data[2] == "dd") || ($data[2] == "6")) {
      $data[2]="";
    }
    print "<TR CLASS=list-color" . (($bcol %2) + 1). "><TD>" . $data[1] . "</TD><TD>" . $data[2] . "</TD></TR>\n";
    $bcol++;
  }
}
print "</TD></TR>\n";
$agi->disconnect();
?>
</TABLE>
</FORM>
