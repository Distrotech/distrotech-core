<?php
  if ($time == "") {
    $time="86400";
  }
  $opts=array();

  array_push($opts,"-a","PNG","--start","-".$time);

  if ($time2) {
  array_push($opts,"--end","-".($time-$time2));
  }

  if ($max != "") {
    array_push($opts,"-r");
  }

  array_push($opts,"-v","Channels",
              "DEF:iuse=/var/spool/apache/htdocs/mrtg/gsm.rrd:Inuse:LAST",
              "DEF:fault=/var/spool/apache/htdocs/mrtg/gsm.rrd:Faulty:LAST",
              "LINE1:iuse#0000FF:Inuse",
              "LINE1:fault#008000:Faulty");

  $ret=rrd_graph("/var/spool/apache/htdocs/mrtg/vgsm.png", $opts);
  header("Content-type: image/png");

  $imin=imagecreatefrompng("vgsm.png");
  imagepng($imin);
?>
