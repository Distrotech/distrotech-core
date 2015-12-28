<?php
include "/var/spool/apache/htdocs/cdr/auth.inc";

header("Content-type: application/ms-excel");

ob_start('ob_gzhandler');

$start=$_GET['start'];
$end=$_GET['end'];

$sdarr=getdate(strtotime($start));
$edarr=getdate(strtotime($end));

$start.=" 00:00:00";
$end.=" 24:00:00";

$csvout=fopen("php://output","w+");

$cdrr=pg_query($db,"SELECT * FROM cdr");
for($i=0;$i < pg_num_rows($cdrr);$i++) {
  $r=pg_fetch_array($cdrr,$i,PGSQL_NUM);
  fputcsv($csvout,$r);
}

fclose($csvout);
?>
