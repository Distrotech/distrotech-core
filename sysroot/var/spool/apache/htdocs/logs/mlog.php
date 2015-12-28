<?php
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

  if ($slog == "time") {
    $mintime="$time_year-$time_month-$time_day $time_hour:$time_min:$time_sec";
    $maxtime="$mtime_year-$mtime_month-$mtime_day $mtime_hour:$mtime_min:$mtime_sec";
  }

  if ($mintime != "") {
    $search="WHERE mail_to.time >= \"$mintime\" AND mail_from.time >= \"$mintime\"";
  }

  if ($maxtime != "") {
    $search="$search AND mail_to.time <= \"$maxtime\" AND mail_from.time <= \"$maxtime\"";
  }

  if ($emailaddr != "") {
    $search="$search AND (mail_to.addr LIKE '%$emailaddr%' OR mail_from.addr LIKE '%$emailaddr%')";
  }

  if ($msgid != "") {
    $search="$search AND mail_to.message_id LIKE '$msgid'";
  }

?>

<CENTER>
<?php
  print "<FORM NAME=msgdata METHOD=POST NAME=msgdata>\n";
  print "<INPUT TYPE=HIDDEN NAME=mintime VALUE=\"$mintime\">\n";
  print "<INPUT TYPE=HIDDEN NAME=maxtime VALUE=\"$maxtime\">\n";
  print "<INPUT TYPE=HIDDEN NAME=emailaddr VALUE=\"\">\n";
  print "<INPUT TYPE=HIDDEN NAME=msgid VALUE=\"$msgid\">\n";


  print "<table WIDTH=90% CELLPADDING=0 CELLSPACING=0>\n";
/*
  print "<TR><TH COLSPAN=3>List Of All Incomeing Mail</TH></TR>\n";
  print "<TR><TH>Time</TH><TH>Message ID</TH><TH>From</TH>";
  print "</TR>\n";

  $result=mysql_query("SELECT * FROM mail_from $search");

  $idval=32;
  $cnt=0;
  while($line = mysql_fetch_row($result)) {
    $rem=$cnt % 2;
    if ($rem == 1) {
      $bcolor="  CLASS=list-color2";
    } else {
      $bcolor="  CLASS=list-color1";
    }
    print "\t<tr $bcolor WIDTH=100%>\n\t\t";
    $cnt++;
    $colid=0;
    $pdir="";
    $icmp_msg="";
    while(list($col_name,$col_value) = each($line)) {
      if (($col_name == 1) || ($col_name == 2)){
        $col_value=htmlentities($col_value);
        if ($col_value == "") {
          $col_value="<BR>";
        }
        if ($col_name == 2) {
          print "<td><A HREF=javascript:ShowMsgData('$col_value')>$col_value</A></TD>";
        } else {
          print "<TD>$col_value</TD>";
        }
      } else if ($col_name == 3) {
        $addr=$col_value;
      } else if ($col_name == 4) {
        $msize=$col_value;
      } else if ($col_name == 5) {
        $rcnt=$col_value;
      } else if ($col_name == 6) {
        $mtag=$col_value;
      } else if ($col_name == 7) {
        $relay=$col_value;
      }

    }
    $js_al="<A HREF=\"javascript:alert('Size: $msize\\nNo. Off Recipients: $rcnt\\nMessage Tag: $mtag\\nRelay: $relay')\"";
    $addr=htmlentities($addr);

    print "<TD>$js_al>$addr</A></TD>";
    print "\n\t</tr>\n";
  }
*/
?>


<?php
  print "<TR  CLASS=list-color2><TH COLSPAN=4 CLASS=heading-body>List Of All Matching Mail</TH></TR>";
  print "<TR  CLASS=list-color1><TH CLASS=heading-body2>Time</TH><TH CLASS=heading-body2>Message ID</TH><TH CLASS=heading-body2>To</TH><TH CLASS=heading-body2>From</TH>";
  print "</TR>\n";

  $query="SELECT * FROM mail_to,mail_from $search AND mail_to.message_id = mail_from.message_id";
  $result=mysql_query($query);

//  print "$query<BR>";

  $idval=32;
  $cnt=0;
  while($line = mysql_fetch_row($result)) {
    $rem=$cnt % 2;
    if ($rem == 1) {
      $bcolor="  CLASS=list-color1";
    } else {
      $bcolor="  CLASS=list-color2";
    }
    print "\t<tr $bcolor>\n\t\t";
    $cnt++;
    $colid=0;
    $pdir="";
    $icmp_msg="";

    while(list($col_name,$col_value) = each($line)) {
      if (($col_name == 1) || ($col_name == 2)){
        $col_value=htmlentities($col_value);
        if ($col_value == "") {
          $col_value="<BR>";
        }
        if ($col_name == 2) {
          print "<td><A HREF=javascript:ShowMsgData('$col_value')>$col_value</A></TD>";
        } else {
          print "<TD>$col_value</TD>";
        }
      } else if ($col_name == 3) {
        $addr_to=$col_value;
      } else if ($col_name == 4) {
        $delay=$col_value;
      } else if ($col_name == 5) {
        $mailer=$col_value;
      } else if ($col_name == 6) {
        $mstat=$col_value;
      } else if ($col_name == 7) {
        $xdelay=$col_value;
      } else if ($col_name == 8) {
        $caddr=$col_value;
      } else if ($col_name == 9) {
        $relay_to=$col_value;
      } else if ($col_name == 11) {
        $from_time=$col_value;
      } else if ($col_name == 13) {
        $addr_from=$col_value;
      } else if ($col_name == 14) {
        $msize=$col_value;
      } else if ($col_name == 15) {
        $rcnt=$col_value;
      } else if ($col_name == 16) {
        $mtag=$col_value;
      } else if ($col_name == 17) {
        $relay_from=$col_value;
      }
    }
    $js_to="<A HREF=\"javascript:alert('Mailer: $mailer\\nDelay: $delay\\nXDelay: $xdelay\\nFrom Addr: $caddr\\nRelay: $relay_to\\nStatus: $mstat')\"";
    $addr_to=htmlentities($addr_to);

    $js_from="<A HREF=\"javascript:alert('Time Arrived: $from_time\\nSize: $msize\\nNo. Off Recipients: $rcnt\\nMessage Tag: $mtag\\nRelay: $relay_from')\"";
    $addr_from=htmlentities($addr_from);

    print "<TD>$js_to>$addr_to</A></TD>";
    print "<TD>$js_from>$addr_from</A></TD>";

    print "\n\t</tr>\n";
  }
  print "</TABLE>";
?>
</FORM>
