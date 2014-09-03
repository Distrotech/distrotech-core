<%
  if ($type != "") {
    $type="-" . $type;
  }
  if ($time == "") {
    $time="86400";
  }
  $opts=array("-a","PNG","--start","-".$time);

  if ($time2) {
    array_push($opts,"--end","-".($time-$time2));
  }

  if ($max != "") {
    array_push($opts,"-r");
  }

  array_push($opts,"-v","Calls",
              "DEF:cps=/var/spool/apache/htdocs/mrtg/voip" . $type . ".rrd:Calls:AVERAGE",
              "DEF:concur=/var/spool/apache/htdocs/mrtg/voip" . $type . ".rrd:CCalls:LAST",
              "CDEF:cpm=cps,60,*",
              "LINE1:concur#0000FF:Concurrent",
              "LINE1:cpm#008000:Calls/Min");

  $ret=rrd_graph("/var/spool/apache/htdocs/mrtg/voip" . $type . ".png", $opts);
  header("Content-type: image/png");

  $imin=imagecreatefrompng("voip" . $type . ".png");
  imagepng($imin);
%>
