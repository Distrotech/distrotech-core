<%
include "/var/spool/apache/htdocs/ccadmin/auth.inc";
header("Content-type: text/xml");

$pgq=pg_query($db, "SELECT information FROM list WHERE id=" . $_POST['xmlscriptid']);

$r=pg_fetch_array($pgq,0);

$xmlstr = pg_unescape_bytea($r[0]);

$xml = simplexml_load_string($xmlstr);
if (!$xml) {
	print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><script></script>";
} else {
	print $xmlstr;
}
%>
