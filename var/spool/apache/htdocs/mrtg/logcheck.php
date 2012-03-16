<%
  include "../logs/uloginc.php";
  if ($time == "") {
    $time="86400";
  }
  $opts=array();

  array_push($opts,"-a","PNG","--start","-".$time);

  if ($time2) {
  array_push($opts,"--end","-".($time-$time2));
  }

  if ($max != "") {
    array_push($opts,"-u","$max","-r");
  }

  $color[0]="#000000";
  $color[1]="#0000A0";
  $color[2]="#0000FF";
  $color[3]="#00A000";
  $color[6]="#00A0A0";
  $color[12]="#00A0FF";
  $color[17]="#00FF00";
  $color[22]="#00FFA0";
  $color[47]="#00FFFF";
  $color[50]="#A00000";
  $color[51]="#A000A0";
  $color[89]="#A000FF";
  $color[255]="#A0A000";

  array_push($opts,"-v","Violations/min","-t","Firewall Violations");
  $parr=$proto;
  while(list($idx,$val)=each($parr)) {
    array_push($opts,"DEF:" . $val . "=/var/spool/apache/htdocs/mrtg/violations.rrd:" . $val . ":AVERAGE",
               "CDEF:r" . $val . "=" . $val . ",60,*",
               "LINE1:r" . $val . $color[$idx] . ":" . $val);
  }

  $ret=rrd_graph("violations.png",$opts);
  header("Content-type: image/png");

  $imin=imagecreatefrompng("violations.png");
  imagepng($imin);
%>
