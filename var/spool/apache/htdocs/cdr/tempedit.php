<%
include "../cdr/auth.inc";

$cellw=72;
$cellh=23;

if (($key != "") && ($key != "")) {
 if ($type == "xp") {
   $key=$key+11;
 } else if ($type == "xp2") {
   $key=$key+53;
 } else if ($type == "xp3") {
   $key=$key+95;
 } else {
   $key=$key-1;
 }
 if ($dest == "0") {
   pg_query($db,"DELETE FROM astdb WHERE family='" . $exten . "' AND key='fkey" . $key . "'");
 } else {
   $getkey=pg_query($db,"SELECT id,value FROM astdb WHERE  family='" . $exten . "' AND key='fkey" . $key . "'");
   if (pg_num_rows($getkey) > 0) {
     $odat=pg_fetch_array($getkey,0);
     if ($odat[1] != $dest) {
       pg_query($db,"UPDATE astdb SET value='" . $dest . "' WHERE id='" . $odat[0] . "'");
     }
   } else {
     pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','fkey" . $key . "','" . $dest . "')");
   }
 }
 if (! isset($agi)) {
   require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
   $agi=new AGI_AsteriskManager();
   $agi->connect("127.0.0.1","admin","admin");
 }
 $agi->command("sip notify reconfig-snom " . $exten);
 $agi->disconnect();
}

%>
<SCRIPT>
function editkey(keyno) {
  document.fkeyedit.dest.value=prompt('Please Enter The Extension Number To Assign To Key'+keyno+'\nEnter 0 To Delete The Maping');
  if (document.fkeyedit.dest.value) {
    document.fkeyedit.key.value=keyno;
    document.fkeyedit.submit();
  }
}
</SCRIPT>

<MAP NAME=EXPANEL ID=EXPANEL>
<%

if ($type == "kp" ) {
  $amax=12;
} else {
  $amax=42;
}

for($key=1;$key <= $amax;$key++) {

  if ($key > 21) {
    $row=$key-21;
    $x=4*$cellw;
  } else {
    $row=$key;
    $x=$cellw;
  }

  if ($type == "kp" ) {
    $max=7;
  } else {
    $max=11;
  }

  if ($type != "kp") {
    if ($row > $max) {
      $y=2*$cellh+2*($row-$max-1)*$cellh;
    } else {
      $y=$cellh+2*($row-1)*$cellh;
    }
  } else {
    if ($row < $max) {
      $y=2*$cellh+2*($row-1)*$cellh;
    } else {
      $y=$cellh+2*($row-$max)*$cellh;
    }
  }

  $x2=$x+$cellw;
  $y2=$y+$cellh;
  print "  <AREA SHAPE=RECT href=javascript:editkey('" . $key . "') coords=" . $x . "," . $y . "," . $x2 . "," . $y2 . ">\n";
}

print "</MAP>\n";

%>
<IMG SRC=/images/snom-<%print $exten . "-" . $type;%>.png USEMAP=#EXPANEL BORDER=0>

<FORM NAME=fkeyedit METHOD=POST>
  <INPUT TYPE=HIDDEN NAME=key>
  <INPUT TYPE=HIDDEN NAME=dest>
</FORM>
