<%
function graph_do($name,$max,$period) {
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
              "-r","-v","Channels",
              "DEF:iuse=/var/spool/apache/htdocs/mrtg/gsm.rrd:Inuse:LAST",
              "DEF:fault=/var/spool/apache/htdocs/mrtg/gsm.rrd:Faulty:LAST",
              "LINE1:iuse#0000FF:Inuse",
              "LINE1:fault#008000:Faulty",
              "PRINT:fault:MAX:%.1lf",
              "PRINT:fault:AVERAGE:%.1lf",
              "PRINT:fault:LAST:%.1lf",
              "PRINT:iuse:MAX:%.1lf",
              "PRINT:iuse:AVERAGE:%.1lf",
              "PRINT:iuse:LAST:%.1lf");

  $ret=rrd_graph("../mrtg/gsm-" . $pername[$period] . ".png", $opts, count($opts));
  print "<IMG SRC=\"/mrtg/gsm-" . $pername[$period] . ".png\" ALT=\"Daily Graph\" VSPACE=10 ALIGN=TOP><BR>";
  print "<TABLE CELLPADDING=0 CELLSPACING=0>";
  for($i=0;$i<2;$i++) {
    print "<TR>";
    for ($ii=0;$ii<3;$ii++) {
      print "<TD ALIGN=RIGHT><SMALL><FONT COLOR=" . $color[$i] . ">" . $valname[$ii] . ":</FONT></SMALL></TD>";
      print "<TD ALIGN=RIGHT><SMALL>&nbsp;";
      if ($i < 2) {
        printf("%.2f Channels",$ret[calcpr][$i*3+$ii]);
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
%>
<META HTTP-EQUIV="Refresh" CONTENT="300;url=/auth">
<META HTTP-EQUIV="Cache-Control" content="no-cache">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="<%print gmdate("D, d M Y G:i:s T");%>">

<H1>GSM Router Status</H1> 
<HR>The statistics were last updated <B><%
$last=rrd_last("/var/spool/apache/htdocs/mrtg/gsm.rrd");
print date("D M j G:i:s T Y",$last);
%></B>

<HR>
<B>`Daily' Graph (5 Minute Snapshot)</B><BR>
<%graph_do($name,$max,1);%>

<HR>
<B>`Weekly' Graph (30 Minute Average)</B><BR>
<%graph_do($name,$max,7);%>

<HR>
<B>`Monthly' Graph (2 Hour Average)</B><BR>
<%graph_do($name,$max,30);%>

<HR>
<B>`Yearly' Graph (1 Day Average)</B><BR>
<%graph_do($name,$max,365);%>

