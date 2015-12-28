<?php
if (! isset($agi)) {
  require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi.php");
  $agi=new AGI();
}

include "/var/lib/asterisk/agi-bin/functions.inc";

verbose("HI");

$agi->hangup();
?>
