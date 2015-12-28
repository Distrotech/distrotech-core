<?php
include "/var/spool/apache/htdocs/ccadmin/auth.inc";
header("Content-type: text/xml");
//$_POST['xmlscriptid']=1;

$pgq=pg_query($db, "SELECT information FROM list WHERE id=" . $_POST['xmlscriptid']);

$r=pg_fetch_array($pgq,0);

preg_match_all('/([\x09\x0a\x0d\x20-\x7e]'. // ASCII characters
 '|[\xc2-\xdf][\x80-\xbf]'. // 2-byte (except overly longs)
 '|\xe0[\xa0-\xbf][\x80-\xbf]'. // 3 byte (except overly longs)
 '|[\xe1-\xec\xee\xef][\x80-\xbf]{2}'. // 3 byte (except overly longs)
 '|\xed[\x80-\x9f][\x80-\xbf])+/', // 3 byte (except UTF-16 surrogates)
 pg_unescape_bytea($r[0]) , $clean_pieces );

$xmlstr = join('?', $clean_pieces[0] );

$xml = simplexml_load_string($xmlstr);
if ($xml) {
  print "$xmlstr";
} else {
  print "<?xml version=\"1.0\" encoding=\"UTF-8\"?><script></script>";
}
?>
