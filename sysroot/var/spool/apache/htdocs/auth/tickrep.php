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
if (!isset($_SESSION['auth'])) {
  exit;
}

if ($rdn == "") {
  include "../ldap/auth.inc";
}

if ($_SESSION['classi'] == "") {
  $_SESSION['classi']=1;
}

$sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
  $ADMIN_USER="admin";
} else {
  return;
}

$colspan=9;
?>
<CENTER>
<FORM NAME=pform METHOD=POST>
<INPUT TYPE=HIDDEN NAME=print>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<?php print $_SESSION['disppage'];?>">
<INPUT TYPE=HIDDEN NAME=mmap VALUE="<?php print $_POST['mmap'];?>">
</FORM>
<FORM METHOD=POST NAME=tickman>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="auth/ticket.php">
<INPUT TYPE=HIDDEN NAME=nomenu VALUE=1>
<INPUT TYPE=HIDDEN NAME=ticket>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<?php
mysql_connect('localhost', 'admin', 'admin');
mysql_select_db('osticket');
$query="select ost_ticket.ticket_id,ost_ticket.created,subject,name,ost_ticket.email,concat(ost_staff.firstname,' ',ost_staff.lastname),
              case when (ost_ticket_response.created is not NULL) then timediff(now(),max(ost_ticket_response.created)) else timediff(now(),ost_ticket.created) end as stale,
              count(ost_ticket_response.response_id) as replies,ost_ticket.staff_id from ost_ticket
            left join ost_ticket_response on ost_ticket.ticket_id = ost_ticket_response.ticket_id 
            left join ost_staff on ost_ticket.staff_id = ost_staff.staff_id 
              where status = 'open' and ost_ticket.dept_id=" . $_SESSION['classi'] . " group by ost_ticket.ticket_id order by max(ost_ticket_response.created),ost_ticket.created";

//print "<PRE>" . $query . "</PRE><P>";
$result = mysql_query($query);

$align=array("LEFT","LEFT","LEFT","LEFT","LEFT","RIGHT","RIGHT");
$chead=array("Created","Subject","From","Email","Assigned To","Age","Replies");

print "<TR CLASS=list-color2><TH COLSPAN=" . ($colspan-2) . " CLASS=heading-body>";
print _("Ticket Status Report") . " ["  .$_POST['mmap'] . "]";
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
    } else if (($arcnt == 4) && ($_POST['print'] != 1)) {
      print "<TD><A HREF=mailto:$line[$arcnt]";
      print ">" . $line[$arcnt] . "</A></TD>";
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
      preg_match("/([0-9]+):([0-9]+):([0-9]+)/",$line[$arcnt],$agep);
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
print "<TR CLASS=list-color" . (($col % 2) +1) . "><TH ALIGN=LEFT COLSPAN=" . ($colspan-2) . ">Stats</TH></TR>\n";
$col++;
print "<TR CLASS=list-color" . (($col % 2) +1) . "><TD COLSPAN=" . ($colspan-3) . ">Total Open Tickets</TD><TD ALIGN=RIGHT>" . $total . "</TD></TR>\n";
$col++;
print "<TR CLASS=list-color" . (($col % 2) +1) . "><TD COLSPAN=" . ($colspan-4) . ">Unassigned Tickets</TD><TD ALIGN=RIGHT>" . $tstat[0] . "</TD><TD ALIGN=RIGHT>";
printf("%0.2f",$tstat[0]/$total*100);
print "%</TD></TR>\n";
$col++;
print "<TR CLASS=list-color" . (($col % 2) +1) . "><TD COLSPAN=" . ($colspan-4) . ">Unreplied Tickets</TD><TD ALIGN=RIGHT>" . $tstat[1] . "</TD><TD ALIGN=RIGHT>";
printf("%0.2f",$tstat[1]/$total*100);
print "%</TD></TR>\n";
$col++;
print "<TR CLASS=list-color" . (($col % 2) +1) . "><TD COLSPAN=" . ($colspan-4) . ">Unreplied Or Unassigned Tickets</TD><TD ALIGN=RIGHT>" . $tstat[2] . "</TD><TD ALIGN=RIGHT>";
printf("%0.2f",$tstat[2]/$total*100);
print "%</TD></TR>\n";
$col++;

$tage2=$tage;
while(list($key)=each($tage2)) {
  print "<TR CLASS=list-color" . (($col % 2) +1) . "><TD COLSPAN=" . ($colspan-4) . ">Tickets Older Than " . $key . " Hours</TD><TD ALIGN=RIGHT>" . $tage[$key] . "</TD><TD ALIGN=RIGHT>";
  printf("%0.2f",$tage[$key]/$total*100);
  print "%</TD></TR>\n";
  $col++;
}


$ctime=mysql_query("select avg(time_to_sec(timediff(closed,created))),count(ticket_id) from ost_ticket where status='closed' and ost_ticket.dept_id=" . $_SESSION['classi'] . " AND date_sub(now(),interval 1 month) < created");
$ctline=mysql_fetch_array($ctime, MYSQL_NUM);
print "<TR CLASS=list-color" . (($col % 2) +1) . "><TD COLSPAN=" . ($colspan-4) . ">Month Close Count/Average";
$ctmin=$ctline[0] % 3600;
$cthour=sprintf("%d",(($ctline[0] - $ctmin)/3600));
$ctsec=$ctmin % 60;
$ctmin=sprintf("%02d",(($ctmin - $ctsec)/60));
$ctsec=sprintf("%02d",$ctsec);
print "</TD><TD ALIGN=RIGHT>" . $ctline[1] . "</TD><TD ALIGN=RIGHT>" .  $cthour . ":" . $ctmin . ":" . $ctsec;
print "</TD></TR>\n";
$col++;

print "<TR CLASS=list-color" . (($col % 2) +1) . "><TH ALIGN=LEFT COLSPAN=" . ($colspan-2) . ">Staff Activity</TH></TR>\n";
$col++;

$stres=mysql_query("select concat(ost_staff.firstname,' ',ost_staff.lastname),count(ost_ticket.ticket_id) from ost_ticket left join ost_staff on ost_ticket.staff_id=ost_staff.staff_id where 
ost_ticket.status='open' and ost_ticket.dept_id=" . $_SESSION['classi'] . " and ost_ticket.staff_id > 0  group by ost_ticket.staff_id order by count(ost_ticket.ticket_id) DESC");

while(list($name,$otcnt)=mysql_fetch_array($stres, MYSQL_NUM)) {
  print "<TR CLASS=list-color" . (($col % 2) +1) . "><TD COLSPAN=" . ($colspan-4) . ">" . $name . "</TD><TD ALIGN=RIGHT>" . $otcnt . "</TD><TD ALIGN=RIGHT>";
  printf("%0.2f",$otcnt/$total*100);
  print "%</TD></TR>\n";
  $col++;
}

if ($_POST['print'] != "1") {
  print "<TR CLASS=list-color" . (($col % 2)+1) . "><TH COLSPAN=" . ($colspan-2) . " CLASS=heading-body><INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\"ONCLICK=\"printpage(document.pform)\"></TH></TR>";
}
?>
</TH></TR>
</FORM>
</TABLE>
