<%
include "/var/lib/asterisk/agi-bin/functions.inc";

gsm_call($router,$destination,$maxcall);
?>
