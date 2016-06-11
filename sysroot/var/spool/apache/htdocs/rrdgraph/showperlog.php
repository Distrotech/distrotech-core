<?php
function graph_do($period) {
  include "/var/spool/apache/htdocs/logs/uloginc.php";
  $valname[0]="Max";
  $valname[1]="Average";
  $valname[2]="Current";

  $pername[1]="day";
  $pername[7]="week";
  $pername[30]="month";
  $pername[365]="year";


  $colors[0]="#000000";
  $colors[1]="#0000A0";
  $colors[2]="#0000FF";
  $colors[3]="#00A000";
  $colors[6]="#00A0A0";
  $colors[12]="#00A0FF";
  $colors[17]="#00FF00";
  $colors[22]="#00FFA0";
  $colors[47]="#00FFFF";
  $colors[50]="#A00000";
  $colors[51]="#A000A0";
  $colors[89]="#A000FF";
  $colors[255]="#A0A000";

  $color=array();
  while(list($key,$val)=each($colors)) {
    array_push($color,$val);
  }
  
  $opts=array("-a","PNG","--start","-" . 86400*$period,"-r","-v","Violations/min","-t",
              "Firewall Violations");
  $parr=$proto;
  while(list($idx,$val)=each($parr)) {
    array_push($opts,"DEF:" . $val . "=/var/spool/apache/htdocs/mrtg/violations.rrd:" . $val . ":AVERAGE",
               "CDEF:r" . $val . "=" . $val . ",60,*",
               "LINE1:r" . $val . $colors[$idx] . ":" . $val,
               "PRINT:r" . $val . ":MAX:%.2lf",
               "PRINT:r" . $val . ":AVERAGE:%.2lf",
               "PRINT:r" . $val . ":LAST:%.2lf");
  }

  $ret=rrd_graph("/var/spool/apache/htdocs/mrtg/violations-" . $pername[$period] . ".png", $opts);
  print rrd_error();

  print "<IMG SRC=\"/mrtg/violations-" . $pername[$period] . ".png\" ALT=\"Daily Graph\" VSPACE=10 ALIGN=TOP><BR>";
  print "<TABLE CELLPADDING=0 CELLSPACING=0>";
  for($i=0;$i<13;$i++) {
    print "<TR>";
    for ($ii=0;$ii<3;$ii++) {
      print "<TD ALIGN=RIGHT><SMALL><FONT COLOR=" . $color[$i] . ">" . $valname[$ii] . ":</FONT></SMALL></TD>";
      print "<TD ALIGN=RIGHT><SMALL>&nbsp;" . $ret[calcpr][$i*3+$ii] . " V/s</SMALL></TD><TD WIDTH=5></TD>";
    }
    print "</TR>";
  }
  print "</TABLE>";
}
?>
<META HTTP-EQUIV="Refresh" CONTENT="300;url=/auth">
<META HTTP-EQUIV="Cache-Control" content="no-cache">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="<?php print gmdate("D, d M Y G:i:s T");?>">

<H1>Firewall Violations</H1> 
<HR>The statistics were last updated <B><?php
$last=rrd_last("/var/spool/apache/htdocs/mrtg/violations.rrd");
print date("D M j G:i:s T Y",$last);
?></B>

<HR>
<B>`Daily' Graph (5 Minute Average)</B><BR>
<?phpgraph_do(1);?>

<HR>
<B>`Weekly' Graph (30 Minute Average)</B><BR>
<?phpgraph_do(7);?>

<HR>
<B>`Monthly' Graph (2 Hour Average)</B><BR>
<?phpgraph_do(30);?>

<HR>
<B>`Yearly' Graph (1 Day Average)</B><BR>
<?phpgraph_do(365);?>

