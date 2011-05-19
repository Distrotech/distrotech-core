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

  $color=array("#FF0000","#0000FF","#FF00FF");
  $opts=array("-a","PNG","--start",$ctime - 86400*$period,"--end",$ctime,
              "-r","-v","KB/s","-t",
              "Bandwidth Usage For $name",
              "DEF:highpool=/var/spool/apache/htdocs/mrtg/bw-$name.rrd:high:AVERAGE",
              "DEF:medpool=/var/spool/apache/htdocs/mrtg/bw-$name.rrd:med:AVERAGE",
              "DEF:lowpool=/var/spool/apache/htdocs/mrtg/bw-$name.rrd:low:AVERAGE",
              "CDEF:highpoolr=" . $max . ",highpool,1024,/,GT,highpool,1024,/,0,IF",
              "CDEF:medpoolr=" . $max . ",medpool,1024,/,GT,medpool,1024,/,0,IF",
              "CDEF:lowpoolr=" . $max . ",lowpool,1024,/,GT,lowpool,1024,/,0,IF",
              "AREA:lowpoolr". $color[0] .":Low Priority",
              "STACK:medpoolr". $color[1] .":Med Priority",
              "STACK:highpoolr". $color[2] .":High Priority",
              "PRINT:lowpoolr:MAX:%.2lf",
              "PRINT:lowpoolr:AVERAGE:%.2lf",
              "PRINT:lowpool:LAST:%.2lf",
              "PRINT:medpoolr:MAX:%.2lf",
              "PRINT:medpoolr:AVERAGE:%.2lf",
              "PRINT:medpool:LAST:%.2lf",
              "PRINT:highpoolr:MAX:%.2lf",
              "PRINT:highpoolr:AVERAGE:%.2lf",
              "PRINT:highpool:LAST:%.2lf");

  $ret=rrd_graph("../mrtg/bw-" . $name . "-" . $pername[$period] . ".png", $opts, count($opts));
  print "<IMG SRC=\"/mrtg/bw-" . $name . "-" . $pername[$period] . ".png\" ALT=\"Daily Graph\" VSPACE=10 ALIGN=TOP><BR>";
  print "<TABLE CELLPADDING=0 CELLSPACING=0>";
  for($i=0;$i<3;$i++) {
    print "<TR>";
    for ($ii=0;$ii<3;$ii++) {
      print "<TD ALIGN=RIGHT><SMALL><FONT COLOR=" . $color[$i] . ">" . $valname[$ii] . ":</FONT></SMALL></TD>";
      print "<TD ALIGN=RIGHT><SMALL>&nbsp;";
      if ($ii == 2) {
        printf("%.2f KB/s (%.2f",$ret[calcpr][$i*3+$ii]/1024,($ret[calcpr][$i*3+$ii]/($max*1024))*100);
      } else {
        printf("%.2f KB/s (%.2f",$ret[calcpr][$i*3+$ii],($ret[calcpr][$i*3+$ii]/$max)*100);
      }
      print "%)</SMALL></TD><TD WIDTH=5></TD>";
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

<H1>Traffic Data For <%print $name;%></H1> 
<TABLE> 
 <TR><TD>Description:</TD><TD><%print $name;%></TD></TR> 
 <TR><TD>Max Speed:</TD><TD><%print $max;%></TD></TR>
</TABLE>
<HR>The statistics were last updated <B><%
$last=rrd_last("/var/spool/apache/htdocs/mrtg/bw-$name.rrd");
print date("D M j G:i:s T Y",$last);
%></B>

<HR>
<B>`Daily' Graph (5 Minute Average)</B><BR>
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

