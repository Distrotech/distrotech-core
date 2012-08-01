<%
/*
#    Copyright (C) 2002  <Gregory Hinton Nietsky>
#    Copyright (C) 2005  <ZA Telecomunications>
#
#    This program is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program; if not, write to the Free Software
#    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
  include "ulogauth.php";
  include "uloginc.php";

  if ($slog == "time") {
    $mintime=mktime($time_hour,$time_min,$time_sec,$time_month,$time_day,$time_year);
    $maxtime=mktime($mtime_hour,$mtime_min,$mtime_sec,$mtime_month,$mtime_day,$mtime_year);
  }

  if ($mintime != "") {
    $search=" AND local_time >= $mintime";
  }

  if ($maxtime != "") {
    $search="$search AND local_time <= $maxtime";
  }

  if ($direction == "in") {
    $search="$search AND oob_in != \"\" AND oob_out = \"\"";
  }
  if ($direction == "out") {
    $search="$search AND oob_out != \"\" AND oob_in = \"\"";
  }

  if ($direction == "fwd") {
    $search="$search AND oob_out != \"\" AND oob_in != \"\"";
  }

  if ($sproto != "") {
    $search="$search AND ip_protocol = $sproto";
  }

  if ($saddr != "") {
//    settype($saddr, "double");
//    $saddr=sprintf("%u",ip2long($saddr));
    $search="$search AND ip_saddr = $saddr";
  }

  if ($daddr != "") {
//    settype($daddr, "double");
//    $daddr=sprintf("%u",ip2long($daddr));
    $search="$search AND ip_daddr = $daddr";
  }
 
//  print "$search<BR>";

%>

<link rel=stylesheet type=text/css href=/style.php>
<CENTER>
<%
if (($type == "") || ($type == "rep")) {
  print "<FORM NAME=ipdata METHOD=POST onsubmit=\"ajaxsubmit(this.name);return false;\">\n";
  print "<INPUT TYPE=HIDDEN NAME=disppage VALUE=\"logs/ulog.php\">\n";
  print "<INPUT TYPE=HIDDEN NAME=mintime VALUE=\"$mintime\">\n";
  print "<INPUT TYPE=HIDDEN NAME=maxtime VALUE=\"$maxtime\">\n";
  print "<INPUT TYPE=HIDDEN NAME=direction VALUE=\"$direction\">\n";
  print "<INPUT TYPE=HIDDEN NAME=sproto VALUE=\"$sproto\">\n"; 
  print "<INPUT TYPE=HIDDEN NAME=saddr VALUE=\"\">\n"; 
  print "<INPUT TYPE=HIDDEN NAME=daddr VALUE=\"\">\n"; 
  print "<INPUT TYPE=HIDDEN NAME=type VALUE=\"ip\">\n"; 
  if (($idtype == 0 ) || ($idtype == "")) {
    $query[0]="SELECT ip_saddr,ip_protocol,SUM(ip_totlen),count(id) AS traf_count FROM packet_filter 
                      WHERE oob_prefix=\"\"$search
                      GROUP BY ip_saddr,ip_protocol
                      ORDER BY traf_count DESC,ip_protocol";
  }
  if (($idtype == 1 ) || ($idtype == "")) {
    $query[1]="SELECT ip_daddr,ip_protocol,SUM(ip_totlen),count(id) AS traf_count FROM packet_filter 
                 WHERE oob_prefix=\"\"$search
                 GROUP BY ip_daddr,ip_protocol
                 ORDER BY traf_count DESC,ip_protocol";
  }
  $rname[0]="Source";
  $rname[1]="Destination";
  $cnt=0;
  while(list($idx)=each($query)) {
    if (($cnt %2) == 1) {
      $basecol[1]=" CLASS=list-color2";
      $basecol[0]=" CLASS=list-color1";
    } else {
      $basecol[0]=" CLASS=list-color2";
      $basecol[1]=" CLASS=list-color1";
    }
    print "<table WIDTH=90% CELLPADDING=0 CELLSPACING=0>";
    print "<TR $basecol[0]><TH COLSPAN=4>Summary By $rname[$idx] Addrress</TH></TR>";
    print "<TR $basecol[1]><TH WIDTH=25%>$rname[$idx] Address</TH><TH WIDTH=25%>Protocol</TH><TH WIDTH=25%>Size</TH><TH WIDTH=25%>Violation Count</TH></TR>";
//    print "\n\n$query[$idx]<BR>$cnt\n\n";
    $result=mysql_query($query[$idx]);
    $total=0;
    $total_cnt=0;
    while($line = mysql_fetch_row($result)) {
      $rem=$cnt % 2;
      if ($rem == 1) {
        $bcolor=" CLASS=list-color1";
      } else {
        $bcolor=" CLASS=list-color2";
      }
      print "\t<tr $bcolor>\n";
      $cnt++;
      $colid=0;
      $pdir="";
      $align="";
      $icmp_msg="";
      while(list($col_name,$col_value) = each($line)) {
        if ($colid == "0") {
          settype($col_value, "double");
          $real_ip=$col_value;
          $ipaddr=long2ip($col_value);
          $col_value="";
        } else if ($colid == "1") {
          $pid=$col_value;
          if ($proto[$col_value] != "") {
            $col_value=$proto[$col_value];
          }
          if ($idx == "0") {
            print "<td><A HREF=javascript:ShowIpData('$real_ip','','$pid')>$ipaddr</A></TD><TD ALIGN=MIDDLE>$col_value</TD>";
          } else {
            print "<td><A HREF=javascript:ShowIpData('','$real_ip','$pid')>$ipaddr</A></TD><TD ALIGN=MIDDLE>$col_value</TD>";
          }
          $col_value="";
        } else if ($colid == "2") {
          $total=$total+$col_value;
          $align=" ALIGN=RIGHT";
        } else if ($colid == "3") {
          $total_cnt=$total_cnt+$col_value;
          $align=" ALIGN=RIGHT";
        }
        if ($col_value != "") {
          print "\t\t<td$align>$col_value</td>\n";
        }
        $colid++;
      }
      print "\t</tr>\n";
    }  
    $rem=$cnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color1";
    } else {
      $bcolor=" CLASS=list-color2";
    }
    print "<TR WIDTH=100% $bcolor><TD COLSPAN=2><BR></TD><TD ALIGN=RIGHT>$total</TD><TD ALIGN=RIGHT>$total_cnt</TD></TR>";
    print "</TABLE>";
    $cnt++;
  }
  print "</FORM>";
}

if (($type == "") || ($type == "ip")) {
  print "<table WIDTH=90% CELLPADDING=0 CELLSPACING=0>";
  print "<TR CLASS=list-color2><TH COLSPAN=8>List Of All Matching Packets</TH></TR>";
  print "<TR CLASS=list-color1><TH>ID</TH><TH>Time</TH><TH>Source Addr</TH><TH>Dest. Address</TH><TH>Direction</TH><TH>Protocol</TH>";
  print "<TH>Size</TH><TH>TTL</TH></TR>\n";

  $resultq="SELECT tcp_sport,tcp_dport,udp_sport,udp_dport,ip_saddr,ip_daddr,ip_protocol,oob_in,oob_out,
                              icmp_type,icmp_code,icmp_echoid,icmp_echoseq,icmp_gateway,icmp_fragmtu,ip_csum,
                              ip_tos,ip_id,ip_fragoff,ip_ihl,udp_len,raw_mac,tcp_ack,tcp_psh,tcp_rst,tcp_syn,tcp_fin,
                              tcp_urg,tcp_seq,tcp_ackseq,tcp_window,tcp_urgp,
                              id,FROM_UNIXTIME(local_time),ip_totlen,ip_ttl FROM packet_filter
                              WHERE oob_prefix=\"\"$search ORDER BY local_time";

//  print $resultq . "<P>";
  $result=mysql_query($resultq);

  $idval=32;
  $cnt=0;
  while($line = mysql_fetch_row($result)) {
    $rem=$cnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color1";
    } else {
      $bcolor=" CLASS=list-color2";
    }
    print "\t<tr $bcolor>\n";
    $cnt++;
    $colid=0;
    $pdir="";
    $icmp_msg="";
    while(list($col_name,$col_value) = each($line)) {
      if ($colid == "0") {
        $srcprt["6"]="$col_value";
        $col_value="";
      } else if ($colid == "1") {
        $dstprt["6"]="$col_value";
        $col_value="";
      } else if ($colid == "2") {
        $srcprt["17"]="$col_value";
        $col_value="";
      } else if ($colid == "3") {
        $dstprt["17"]="$col_value";
        $col_value="";
      } else if (($colid == "4") || ($colid == "5")) {
        settype($col_value, "double");
        $col_value=long2ip($col_value);
        if ($colid == "4") {
          $srcip=$col_value;
        } else {
          $dstip=$col_value;
        }
        $col_value="";
      } else if ($colid == "6") {
        $pid=$col_value;
        if ($proto[$col_value] != "") {
          $pname=$proto[$col_value];
        }
        $col_value="";
     } else if ($colid == "7") {
        if ($col_value != "") {
          $pdir="IN ($col_value)";
        }
        $col_value="";
     } else if ($colid == "8") {
        if ($col_value != "") {
          if ($pdir != "") {
            $pdir="$pdir</BR>OUT ($col_value)";
          } else {
            $pdir="OUT ($col_value)";
          }
        }
        $col_value="";
     } else if ($colid == "9") {
        $icmp_type=$col_value;
        $col_value="";
     } else if ($colid == "10") {
        $icmp_code=$col_value;
        $col_value="";
     } else if ($colid == "11") {
        $icmp_echoid=$col_value;
        $col_value="";
     } else if ($colid == "12") {
        $icmp_seq=$col_value;
        $col_value="";
     } else if ($colid == "13") {
        $icmp_gateway=$col_value;
        $col_value="";
     } else if ($colid == "14") {
        $icmp_fragmtu=$col_value;
        $col_value="";
     } else if ($colid == "15") {
        $ip_cs=$col_value;
        $col_value="";
     } else if ($colid == "16") {
        $ip_tos=$col_value;
        $col_value="";
     } else if ($colid == "17") {
        $ip_id=$col_value;
        $col_value="";
     } else if ($colid == "18") {
        $ip_frag=$col_value;
        $col_value="";
     } else if ($colid == "19") {
        $ip_hl=$col_value;
        $col_value="";
     } else if ($colid == "20") {
        $udp_len=$col_value;
        $col_value="";
     } else if ($colid == "21") {
        $macaddr=$col_value;
        $col_value="";
     } else if ($colid == "22") {
        $tcp_ack=$col_value;
        $col_value="";
     } else if ($colid == "23") {
        $tcp_psh=$col_value;
        $col_value="";
     } else if ($colid == "24") {
        $tcp_rst=$col_value;
        $col_value="";
     } else if ($colid == "25") {
        $tcp_syn=$col_value;
        $col_value="";
     } else if ($colid == "26") {
        $tcp_fin=$col_value;
        $col_value="";
     } else if ($colid == "27") {
        $tcp_urg=$col_value;
        $col_value="";
     } else if ($colid == "28") {
        $tcp_seq=$col_value;
        $col_value="";
     } else if ($colid == "29") {
        $tcp_ackseq=$col_value;
        $col_value="";
     } else if ($colid == "30") {
        $tcp_win=$col_value;
        $col_value="";
     } else if ($colid == "31") {
        $tcp_urgp=$col_value;
        $col_value="";
     } else if ($colid == $idval) {
        $js_al="<A HREF=\"javascript:alert('Packet: $col_value\\nProtocol: $pname\\nMAC Address: $macaddr\\nIP TOS: $ip_tos\\n
IP Checksum: $ip_cs\\nIP ID: $ip_id\\nIP Fragmentation Offset: $ip_frag\\nIP Header Length: $ip_hl";
        if ($pid == "1") {
          $icmp_msg="$icmp_type-$icmp_code";
          $icmp_msg=$imsg[$icmp_msg];
          $js_al="$js_al\\nICMP Type: $icmp_type\\nICMP Code: $icmp_code\\nMessage: $icmp_msg\\nEcho ID: $icmp_echoid\\n
Echo Seq.: $icmp_seq\\nICMP Gateway: $icmp_gateway\\nICMP Frag MTU: $icmp_fragmtu";
        } else if ($pid == "17"){
          $js_al="$js_al\\nUDP Length: $udp_len";
        } else if ($pid == "6"){
          $js_al="$js_al\\nTCP URGP: $tcp_urgp\\nTCP Seq.: $tcp_seq\\nTCP ACK Seq.: $tcp_ackseq\\nTCP Window: $tcp_win\\n
TCP URG Flag: $tcp_urg\\nTCP ACK Flag: $tcp_ack\\nTCP PSH Flag: $tcp_psh\\nTCP RST Flag: $tcp_rst\\n
TCP SYN Flag: $tcp_syn\\nTCP FIN Flag: $tcp_fin";
        }
        $js_al="$js_al')\">$col_value</A>";
        $col_value=$js_al;
     } else if ($colid == $idval+1) {
        if ($col_value == "1970-01-01 02:00:00") {
          $col_value="<BR>";
        }
        if (($pid == "6") || ($pid == "17")) {
          print "<TD>$col_value</TD><TD>$srcip:$srcprt[$pid]</TD><TD>$dstip:$dstprt[$pid]</TD><TD>$pdir</TD><TD>$pname</TD>";
        } else if ($pid == "1") {
          print "<TD>$col_value</TD><TD>$srcip</TD><TD>$dstip</TD><TD>$pdir</TD><TD>$pname ($icmp_type,$icmp_code)</TD>"; 
        } else {
          print "<TD>$col_value</TD><TD>$srcip</TD><TD>$dstip</TD><TD>$pdir</TD><TD>$pname</TD>";
        }
        $col_value="";
      } else {
        if ($col_value == "") {
            $col_value="<BR>";
        } else {
          $col_value=nl2br(htmlentities($col_value));
        }
      }
      if ($col_value != "") {
        print "\t\t<td>$col_value</td>\n";
      }
      $colid++;
    }
    print "\t</tr>\n";
  }
  print "</TABLE>";
}
%>
