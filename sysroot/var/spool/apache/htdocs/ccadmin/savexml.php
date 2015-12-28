<?php
include "/var/spool/apache/htdocs/ccadmin/auth.inc";

$xmlenc=pg_escape_bytea($db,stripslashes($_POST['xmlscript']));
pg_query($db,"UPDATE list SET information='" . $xmlenc . "' WHERE id=" . $_POST['xmlscriptid']);
?>
