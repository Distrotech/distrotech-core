<?php
  if ($time == "") {
    $time="86400";
  }
  $opts=array("-a","PNG","--start","end-" . $time . "s");

  if ($time2) {
    array_push($opts,"--end","-".($time-$time2));
  }

  if ($max != "") {
    array_push($opts,"-r");
  }

  if ($_GET['log'] != "") {
    $type=$_GET['log'];
  } else if ($_GET['type'] == "") {
    $type=$argv[1];
  } else {
    $type=$_GET['type'];
  }

  $type=strtolower($type);

  if ($max != "") {
    array_push($opts,"-r");
  }

  $rrd_path="/var/spool/apache/htdocs/mrtg/" . $type;

  array_push($opts,"-v","Bytes per second","-x","MINUTE:30:HOUR:1:HOUR:2:0:%H",
              "DEF:Input=" . $rrd_path . ".rrd:ds0:AVERAGE",
              "DEF:Output=" . $rrd_path . ".rrd:ds1:AVERAGE",
              "LINE1:Input#0000FF:In ",
              "GPRINT:Input:LAST:Cur\:%8.2lf %s",
              "GPRINT:Input:AVERAGE:Avg\:%8.2lf %s",
              "GPRINT:Input:MAX:Max\:%8.2lf %s\\r",
              "AREA:Output#00FF00:Out",
              "GPRINT:Output:LAST:Cur\:%8.2lf %s",
              "GPRINT:Output:AVERAGE:Avg\:%8.2lf %s",
              "GPRINT:Output:MAX:Max\:%8.2lf %s\\r");

  if (rrd_graph($rrd_path . ".png", $opts)) {
//    $rr_arr = rrd_info($rrd_path . ".rrd");
//    print_r($rr_arr);;
  }

  if ($argv[1] == "") {
    header("Content-type: image/png");
    $imin=imagecreatefrompng($type . ".png");
    imagepng($imin);
  }
?>
