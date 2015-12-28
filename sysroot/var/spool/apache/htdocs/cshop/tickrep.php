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

function show_tickets($emailaddie,$status) {
  $colspan=8;
  mysql_connect('localhost', 'admin', 'admin');
  mysql_select_db('osticket');
  $query="select ost_ticket.ticket_id,ost_ticket.created,subject,name,concat(ost_staff.firstname,' ',ost_staff.lastname),
                case when (ost_ticket_response.created is not NULL) then timediff(now(),max(ost_ticket_response.created)) else timediff(now(),ost_ticket.created) end as stale,
                count(ost_ticket_response.response_id) as replies,ost_ticket.staff_id from ost_ticket
              left join ost_ticket_response on ost_ticket.ticket_id = ost_ticket_response.ticket_id 
              left join ost_staff on ost_ticket.staff_id = ost_staff.staff_id 
                where status = '" . $status . "' and ost_ticket.email='" . $emailaddie . "' group by ost_ticket.ticket_id
              order by max(ost_ticket_response.created),ost_ticket.created";

  $result = mysql_query($query);
  $numrows=mysql_num_rows($result);

  if ($numrows > 0) {?>
    <CENTER>
    <FORM METHOD=POST NAME=tickman>
    <INPUT TYPE=HIDDEN NAME=disppage VALUE="auth/ticket.php">
    <INPUT TYPE=HIDDEN NAME=nomenu VALUE=1>
    <INPUT TYPE=HIDDEN NAME=ticket>
    <TABLE WIDTH=90% cellspacing=0 cellpadding=0><?php

    $align=array("LEFT","LEFT","LEFT","LEFT","RIGHT","RIGHT");
    $chead=array("Created","Subject","From","Assigned To","Age","Replies");

    print "<TR CLASS=list-color2><TH COLSPAN=" . ($colspan-2) . " CLASS=heading-body>";
    print _("Ticket Status Report") . " ["  . $emailaddie . "]";
    print "</TH></TR><TR CLASS=list-color1>";
    for($hcnt=0;$hcnt < count($chead);$hcnt++) {
      print "<TH CLASS=heading-body2";
      if ($align[$hcnt] != "") {
        print " ALIGN=" . $align[$hcnt];
      }
      print ">" . $chead[$hcnt] . "</TH>";
    }
    print "</TR>";
    $col=1;
    $total=0;
    $tstat=array(0,0,0);
    $tage=array(72=>0,48=>0,24=>0,12=>0,8=>0,4=>0,2=>0);

    while ($line = mysql_fetch_array($result, MYSQL_NUM)) {
      print "<TR CLASS=list-color" . (($col % 2) +1) . ">";
      $col++;
      for($arcnt=1;$arcnt < count($line);$arcnt++) {
        if (($arcnt == 2) && ($_POST['print'] != 1)) {
          print "<TD><A HREF=javascript:ticketwin('" . $line[0] . "')";
          if (($line[$colspan-2] == 0) || ($line[$colspan-1] == 0)){
            $tstat[2]++;
            print " CLASS=red";
          }
          print ">" . $line[$arcnt] . " (#" . $line[0] . ")</A></TD>";
        } else if ($arcnt < ($colspan -1)) {
          if ($line[$arcnt] == "") {
            $line[$arcnt]="&nbsp;";
          }
          print "<TD";
          if ($align[$arcnt-1] != "") {
            print " ALIGN=" . $align[$arcnt-1];
          }
          print ">" . $line[$arcnt] . "</TD>";
        }
        if (($arcnt > ($colspan - 3)) && ($line[$arcnt] == "0")){
          $oset=$colspan-$arcnt-1;
          $tstat[$oset]++;
        } else if ($arcnt == ($colspan -3)) {
          ereg("([0-9]+):([0-9]+):([0-9]+)",$line[$arcnt],$agep);
          $tage2=$tage;
          while(list($key)=each($tage2)) {
            if ($agep[1] > $key) {
              $tage[$key]++;
	      break;
            }
          }
        }
      }
      $total++;
      print "</TR>\n";
    }
    print "</FORM></TABLE>\n";
  }
}

show_tickets("johanv@blue.co.za","closed");
