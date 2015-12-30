<?php
  if ($_GET['type'] != "") {
    $_GET['type']="-" . $_GET['type'];
  }
  if ($_GET['time'] == "") {
    $_GET['time']="86400";
  }

  $opts=array("-a","PNG","--start","end-". $_GET['time'] . "s");

  if ($time2) {
    array_push($opts,"--end","-".($time-$time2));
  }

  if ($max != "") {
    array_push($opts,"-r");
  }

//"-x","MINUTE:30:HOUR:1:HOUR:2:0:%H",
  array_push($opts,"-v","Calls",
              "DEF:cps=/var/spool/apache/htdocs/mrtg/voip" . $_GET['type'] . ".rrd:Calls:AVERAGE",
              "DEF:concur=/var/spool/apache/htdocs/mrtg/voip" . $_GET['type'] . ".rrd:CCalls:LAST",
              "CDEF:cpm=cps,60,*",
              "LINE1:concur#0000FF:Concurrent",
              "LINE1:cpm#008000:Calls/Min");

  if (!rrd_graph("/var/spool/apache/htdocs/mrtg/voip" . $_GET['type'] . ".png", $opts)) {
    print_r($_GET);
  }

  header("Content-type: image/png");

  $imin=imagecreatefrompng("voip" . $_GET['type'] . ".png");
  imagepng($imin);
?>
