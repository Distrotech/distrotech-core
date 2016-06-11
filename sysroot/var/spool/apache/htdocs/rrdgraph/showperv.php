<?php
function graph_do($name,$max,$period,$classi) {
  $valname[0]="Max";
  $valname[1]="Average";
  $valname[2]="Current";

  $pername[1]="day";
  $pername[7]="week";
  $pername[30]="month";
  $pername[365]="year";


  $ctime=time();
  $ctime=$ctime-($ctime % 5);

  $color=array("#008000","#0000FF","#FF00FF");
  $opts=array("-a","PNG","--start",$ctime - 86400*$period,"--end",$ctime,
              "-r","-v","Calls",
              "DEF:cps=/var/spool/apache/htdocs/mrtg/voip" . $classi . ".rrd:Calls:AVERAGE",
              "DEF:ccur=/var/spool/apache/htdocs/mrtg/voip" . $classi . ".rrd:CCalls:LAST",
              "CDEF:cpm=cps,60,*",
              "LINE1:ccur#0000FF:Concurrent",
              "LINE1:cpm#008000:Calls/Min",
              "PRINT:cpm:MAX:%.2lf",
              "PRINT:cpm:AVERAGE:%.2lf",
              "PRINT:cpm:LAST:%.2lf",
              "PRINT:ccur:MAX:%.2lf",
              "PRINT:ccur:AVERAGE:%.2lf",
              "PRINT:ccur:LAST:%.2lf");

  $ret=rrd_graph("/var/spool/apache/htdocs/mrtg/voip-" . $pername[$period] . ".png", $opts);

  print "<IMG SRC=\"/mrtg/voip-" . $pername[$period] . ".png\" ALT=\"Daily Graph\" VSPACE=10 ALIGN=TOP><BR>";
  print "<TABLE CELLPADDING=0 CELLSPACING=0>";
  for($i=0;$i<2;$i++) {
    print "<TR>";
    for ($ii=0;$ii<3;$ii++) {
      print "<TD ALIGN=RIGHT><SMALL><FONT COLOR=" . $color[$i] . ">" . $valname[$ii] . ":</FONT></SMALL></TD>";
      print "<TD ALIGN=RIGHT><SMALL>&nbsp;";
      if ($i == 1) {
        printf("%.2f Calls",$ret[calcpr][$i*3+$ii]);
        if ($max > 0) {
          printf(" (%.2f%%)",($ret[calcpr][$i*3+$ii]/($max))*100);
        }
      } else {
        printf("%.2f Calls/m",$ret[calcpr][$i*3+$ii]);
        if ($max > 0) {
          printf(" (%.2f%%)",($ret[calcpr][$i*3+$ii]/$max)*100);
        }
      }
      print "</SMALL></TD><TD WIDTH=5></TD>";
    }
    print "</TR>";
  }
  print "</TABLE>";
}

if ($classi != "") {
  if ($classi == "tdm") {
    $gtitle=" (TDM Calls)";
  } else if ($classi == "lcr") {
    $gtitle=" (LCR Calls)";
  }
  $classi="-" . $classi;
}
?>
<META HTTP-EQUIV="Refresh" CONTENT="300;url=/auth">
<META HTTP-EQUIV="Cache-Control" content="no-cache">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="<?php print gmdate("D, d M Y G:i:s T");?>">

<H1>Voip Status Graphs<?php print $gtitle;?></H1>
<HR>The statistics were last updated <B><?php
$last=rrd_last("/var/spool/apache/htdocs/mrtg/voip" . $classi . ".rrd");
print date("D M j G:i:s T Y",$last);
?></B>

<HR>
<B>`Daily' Graph (5 Minute Average)</B><BR>
<?php graph_do($name,$max,1,$classi);?>

<HR>
<B>`Weekly' Graph (30 Minute Average)</B><BR>
<?php graph_do($name,$max,7,$classi);?>

<HR>
<B>`Monthly' Graph (2 Hour Average)</B><BR>
<?php graph_do($name,$max,30,$classi);?>

<HR>
<B>`Yearly' Graph (1 Day Average)</B><BR>
<?php graph_do($name,$max,365,$classi);?>
