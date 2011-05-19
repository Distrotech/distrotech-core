<%
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

  array_push($opts,"-v","KB/s","-t","Bandwidth Usage For $gname",
              "DEF:highpool=/var/spool/apache/htdocs/mrtg/bw-$gname.rrd:high:AVERAGE",
              "DEF:medpool=/var/spool/apache/htdocs/mrtg/bw-$gname.rrd:med:AVERAGE",
              "DEF:lowpool=/var/spool/apache/htdocs/mrtg/bw-$gname.rrd:low:AVERAGE",
              "CDEF:highpoolr=" . $max . ",highpool,1024,/,GT,highpool,1024,/,0,IF",
              "CDEF:medpoolr=" . $max . ",medpool,1024,/,GT,medpool,1024,/,0,IF",
              "CDEF:lowpoolr=" . $max . ",lowpool,1024,/,GT,lowpool,1024,/,0,IF");
  if ($total != "") {
    array_push($opts,"CDEF:total=highpool,medpool,lowpool,+,+,1024,/",
               "LINE1:total#000000:Total");
  }
  
  array_push($opts,"AREA:lowpoolr#FF0000:Low Priority",
              "STACK:medpoolr#0000FF:Med Priority",
              "STACK:highpoolr#FF00FF:High Priority");

  $ret=rrd_graph("bw-".$gname.".png", $opts, count($opts));
  header("Content-type: image/png");

  $imin=imagecreatefrompng("bw-".$gname.".png");
  imagepng($imin);
%>
