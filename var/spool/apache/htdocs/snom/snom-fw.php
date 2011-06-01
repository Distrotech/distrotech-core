<%
$uadata=explode(";",$_SERVER['HTTP_USER_AGENT']);
//$uadata=explode(";","Mozilla/4.0 (compatible; snom300-SIP 7.3.27)");
$snomver=trim($uadata[1]);
$fsver=trim($uadata[2]);
$curver=trim($uadata[3]," )");
$pdata=explode(" ",trim($uadata[1]));
$sipver=explode(".",trim($pdata[1]));

if (($fsver != "") && ($fsver != "snom" . $phone . " jffs2 v3.37")){
  $nfsver="snom" . $phone . " jffs2 v3.36";
} else if ($fsver != "") {
  $nfsver="snom" . $phone . " jffs2 v3.37";
}

if (($curver != "") && ($curver != "snom" . $phone . " linux undef")) {
  $linver="snom" . $phone . " linux 3.38";
} else {
  $linver=$curver;
}

if (($sipver[0] < 5) && (is_file("snom" . $phone . "-5.5a-SIP-j.bin"))) {
  print "firmware: http://" . $SERVER_NAME . "/snom/snom" . $phone . "-5.5a-SIP-j.bin\n";
} else if (($nfsver != $fsver) && (is_file("snom" . $phone . "-ramdiskToJffs2-3.36-br.bin")) && ($sipver[0] > 4)) {
  print "firmware: http://" . $SERVER_NAME . "/snom/snom" . $phone . "-ramdiskToJffs2-3.36-br.bin\n";
} else if (($linver != $curver) && (is_file("snom" . $phone . "-3.38-l.bin")) && ($sipver[0] == "6")) {
  print "firmware: http://" . $SERVER_NAME . "/snom/snom" . $phone . "-3.38-l.bin\n";
} else if (($sipver[0] == 5) || (($sipver[0] == 6) && ($sipver[1] < 5)) || 
           (($sipver[0] == 6) && ($sipver[1] == 5) && ($sipver[2] < 20))) {
  print "firmware: http://" . $SERVER_NAME . "/snom/snom" . $phone . "-6.5.20-SIP-j.bin\n";
} else if ((($sipver[1] == 7) && ($sipver[1] < 3)) || (($sipver[1] == 7) && ($sipver[1] == 3) && ($sipver[2] < 33)) ||
           (($sipver[0] == 6) && ($sipver[1] == 5) && ($sipver[2] == 20))) {
  print "firmware: http://" . $SERVER_NAME . "/snom/snom" . $phone . "-7.3.30-SIP-bf.bin\n";
} else if ((($sipver[1] == 8) && ($sipver[1] < 4)) || (($sipver[1] == 8) && ($sipver[1] == 4) && ($sipver[2] < 31)) ||
  print "firmware: http://" . $SERVER_NAME . "/snom/snom" . $phone . "-8.4.31-SIP-f.bin\n";
} else if (is_file($firmware_file)) {
  print "firmware: http://" . $SERVER_NAME . "/snom/" . $firmware_file . "\n";
}
%>
