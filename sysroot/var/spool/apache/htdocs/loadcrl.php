<?php
  Header ("Content-type: application/octet-stream");
  $fname=file("/etc/ipsec.d/crls/crl.pem");
  while(list($lnum,$linedata)=each($fname)) {
    $linedata=rtrim($linedata);
    print "$linedata\r\n";
  }
?>
