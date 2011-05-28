<%
$msubrel[7]="5";
$mpatch[7]="20";


$uadata=explode(";",$_SERVER['HTTP_USER_AGENT']);
//$uadata=explode(";","Mozilla/4.0 (compatible; snom300-SIP 7.3.27)");
$snomver=trim($uadata[1]);
$fsver=trim($uadata[2]);
$curver=trim($uadata[3]," )");
$pdata=explode(" ",trim($uadata[1]));
$sipver=explode(".",trim($pdata[1]));

/*
# snom3X0-ramdiskToJffs2-br.bin: New bootloader and rootfs with new image format each, which can be updated by 
application part release 5 or above only. This saves 2MB of RAM and brings a TFTP update application which is able to 
update application images with version 5 or above only. Nevertheless it will be possible to downgrade your phone, but 
this is a more complex procedure, see here. It is highly recommended to not use PoE (power over ethernet) for this 
specific update session! 
*/

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
} else if (($msubrel[7] <= $sipver[1]) && ($mpatch[7] <= $sipver[2]) &&
    ($linver == $curver) && ($nfsver == $fsver) && ($sipver[0] == "6")) {
  print "firmware: http://" . $SERVER_NAME . "/snom/snom" . $phone . "-7.3.30-SIP-bf.bin\n";
} else if ((($sipver[0] == "6") || ($sipver[0] == "5")) && (is_file("snom" . $phone . "-6.5.20-SIP-j.bin"))) {
  print "firmware: http://" . $SERVER_NAME . "/snom/snom" . $phone . "-6.5.20-SIP-j.bin\n";
} else if (($sipver[0] == "7") && ($newver == 8) && (is_file($firmware_file))) {
  print "firmware: http://" . $SERVER_NAME . "/snom/" . $firmware_file . "\n";
} else if (($newver == $sipver[0]) && ($newver > 6) && (is_file($firmware_file))) {
  print "firmware: http://" . $SERVER_NAME . "/snom/" . $firmware_file . "\n";
}
%>
