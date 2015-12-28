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

if ($relaunch == "1") {
?>
  <SCRIPT>
    window.open("/end.php?cno=<?php print $_POST['cno'];?>&endses=1&resellerownid=<?php print $_SESSION['resellerownid'];?>","sessiondeath","menubar=no,toolbar=yes,scrolling=yes,scrollbars=yes,resizable=yes,top=0,left=0,width="+screen.width+",height="+screen.height);
  </SCRIPT>
<?php
  return;
}

function gtime($secin) {
  $secin=abs($secin);
  $rem=$secin % 3600;
  $hours=sprintf("%02d",($secin-$rem)/3600);
  $rem2=$rem % 60;
  $mins=sprintf("%02d",($rem-$rem2)/60);
  $secs=sprintf("%02d",$rem2);
  $timeout="$hours:$mins:$secs";
  return $timeout;
}

if ($_POST['cno'] != "") {
  if (! $db) {
    include "/var/spool/apache/htdocs/cdr/auth.inc";
    print "<link rel=stylesheet type=text/css href=/style.php>";
  }
  $credit="";
  $cardid=pg_query($db,"SELECT users.uniqueid,exchangerate,agentid FROM users LEFT OUTER join reseller ON (agentid=reseller.id) WHERE name = '" . $_POST['cno'] . "'");
  $r=pg_fetch_array($cardid,0);
  $cnoi=$r[0];


  if ($sesname != "" ) {
    if ($stime != "") {
      $sesidq="SELECT username,saletime,credit from sale WHERE saletype = 'Session Start' AND
                           cardid = '" . $cnoi . "' AND username = '" . $sesname . "' AND 
                           saletime = '" . $stime . "'
                         ORDER BY saletime DESC LIMIT 1";
    } else {
      $sesidq="SELECT username,saletime,credit from sale WHERE saletype = 'Session Start' AND
                             cardid = '" . $cnoi . "' AND username = '" . $sesname . "'
                           ORDER BY saletime DESC LIMIT 1";
    }
    $sesid=pg_query($sesidq);
    $sesinfo=pg_fetch_row($sesid,0);
  } else {
    $sesidq="SELECT username,saletime,credit FROM sale WHERE saletype = 'Session Start' AND
                         cardid = '" . $cnoi . "' ORDER BY saletime DESC LIMIT 1";
    $sesid=pg_query($sesidq);
    $sesinfo=pg_fetch_row($sesid,0);

    $sesopen=pg_query($db,"SELECT saletime FROM sale WHERE saletype = 'Session End' AND
                             cardid = '" . $cnoi . "' AND username = '" . $sesinfo[0] . "' AND
                             saletime > '" . $sesinfo[1] . "' ORDER BY saletime DESC LIMIT 1");


    if ((pg_num_rows($sesopen) == 0) || ($sesinfo[2] != "0")){
      $creditqq="SELECT credit,fullname FROM users WHERE activated='t'
                            AND usertype=2 AND name = '" . $_POST['cno'] . "' LIMIT 1";

      $creditq=pg_query($db,$creditqq);
      $crow=pg_fetch_row($creditq,0);
      $credit=$crow[0];
    }
  }


  if ($credit != "") {
    if ($credit > 0) {
      $sesendq="INSERT INTO sale (cardid,username,saletime,saletype,credit,discount)
                         VALUES ('" . $cnoi . "','" . $sesinfo[0] . "',localtimestamp,'Session End',-" . ($r[1]*$credit)/100 . ",'0')";
      pg_query($db,"UPDATE reseller SET rcallocated=rcallocated-" . $credit . " WHERE id='" . $r[2] . "'");
    } else if ($credit < 0) {
      $sesendq="INSERT INTO sale (cardid,username,saletime,saletype,credit,discount)
                         VALUES ('" . $cnoi . "','" . $sesinfo[0] . "',localtimestamp,'Session End'," . abs(($r[1]*$credit)/100) . ",'0')";
    
      pg_query($db,"UPDATE reseller SET rcallocated=rcallocated+" . abs($credit) . " WHERE id='" . $r[2] . "'");
    }
    pg_query($db,$sesendq);
    pg_query($db,"UPDATE users SET  activated='f',credit = 0,callocated=0,inuse=0 WHERE name ='" . $_POST['cno'] . "'");
  }

  $sesqq="SELECT to_char(age(saletime,'" . $sesinfo[1] . "'),'HH24:MI:SS'),saletime,credit
                        FROM sale WHERE 
                          username='" . $sesinfo[0] . "' and cardid='" . $cnoi . "' and 
                          saletype = 'Session End' AND saletime > '$sesinfo[1]' 
                        ORDER BY saletime LIMIT 1";
  $sesq=pg_query($db,$sesqq);
  $sestime=pg_fetch_row($sesq,0);
  if ($sestime[1] == "") {
    $qstime="localtimestamp";
    $sestime[0]="Active";
  } else {
    $qstime="'" . $sestime[1] . "'";
  }

  $getsesq="SELECT to_char(call.starttime,'YY/MM/DD HH24:MI:SS'),country.countryname,
                            call.calledstation,call.totaltime,call.ringtime,call.sessiontime,call.calledrate,call.sessionbill / 100,
                            calledsub 
                    FROM call LEFT OUTER JOIN country ON (call.calledcountry = country.countrycode) 
                    WHERE call.username='" . $_POST['cno'] . "' AND call.sessionbill > '0' AND
                          call.starttime >= '" . $sesinfo[1] . "' AND 
                          call.starttime < " . $qstime . "
                    ORDER BY starttime";  
/*
  print $getsesq . "<BR>\n";
*/
  $cdr=pg_query($db,$getsesq);
?>
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=8 class="heading-body">Session Closed</TH>
</TR>
<TR CLASS=list-color1>
<TH COLSPAN=5 ALIGN=LEFT CLASS=heading-body2>Session Name</TH>
<TD COLSPAN=3 ALIGN=LEFT><?php print $sesinfo[0];?></TD>
<TR CLASS=list-color2>
<TH COLSPAN=5 ALIGN=LEFT CLASS=heading-body2>Number</TH>
<TD COLSPAN=3 ALIGN=LEFT><?php print $_POST['cno'];?></TD>
<TR CLASS=list-color1>
<TH COLSPAN=5 ALIGN=LEFT CLASS=heading-body2>Payment Recived</TH>
<TD COLSPAN=3 ALIGN=LEFT><?php printf("R%0.2f",$sesinfo[2]/100);?></TD>
<TR CLASS=list-color2>
<TH COLSPAN=5 ALIGN=LEFT CLASS=heading-body2>Calls Made</TH>
<TD COLSPAN=3 ALIGN=LEFT><?php printf("R%0.2f",($sesinfo[2]+$sestime[2])/100);?></TD>
<TR CLASS=list-color1>
<TH COLSPAN=5 ALIGN=LEFT CLASS=heading-body2>Refund</TH>
<TD COLSPAN=3 ALIGN=LEFT><?php printf("R%0.2f",0-$sestime[2]/100);?></TD>
<TR CLASS=list-color2>
<TH COLSPAN=5 ALIGN=LEFT CLASS=heading-body2>Session Time</TH>
<TD COLSPAN=3 ALIGN=LEFT><?php print $sestime[0];?></TD>
</TR>
<TR CLASS=list-color1><TD COLSPAN=8>&nbsp;</TD></TR>
<?php
$bcolor[0]="list-color1";
$bcolor[1]="list-color2";
?>
<TR CLASS=list-color2>
<TH ALIGN=LEFT><FONT SIZE=1>Start Time</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Country</TD><TH ALIGN=LEFT><FONT SIZE=1>Number</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Total Time</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Ring Time</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Bill. Time</TH><TH ALIGN=LEFT><FONT SIZE=1>Rate</TH>
<TH ALIGN=LEFT><FONT SIZE=1>Cost</TH></TR>
<?php
$num=pg_num_rows($cdr);
$total="0";
for ($i=0; $i < $num; $i++) {
  $r = pg_fetch_row($cdr,$i);
  $rem=$i % 2; 
  print "<TR CLASS=" . $bcolor[$rem] . ">";
  $total=$total+$r[7];
  for ($j=0;$j < count($r)-1;$j++) {
    if (($j == "3") || ($j == "4") || ($j == "5")){
      $r[$j]=gtime($r[$j]);
    } else if ($j == "1") {
      $r[$j]=$r[$j] . " (" . $r[8] . ")";
    } else if ($j > "5") {
      $r[$j]=sprintf("R%0.2f",$r[$j]);
    }
    print  "<TD><FONT SIZE=1>" . $r[$j] . "</TD>";
  }
  print "</TR>\n";
}
$rem=$i % 2; 
print "<TR CLASS=" . $bcolor[$rem] . ">";
?>
<TD COLSPAN=7><FONT SIZE=1>&nbsp;</TD><TD ALIGN=LEFT><FONT SIZE=1><?php printf("R%0.2f",$total);?></TD></TR>
</TABLE>
<?php
} else {
?>
<SCRIPT>
  alert("Ensure The Handset Is Down Before Closing A Session!");
</SCRIPT>
<FORM METHOD=POST>
<INPUT TYPE=HIDDEN NAME=relaunch VALUE="1">
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<TH COLSPAN=2 class="heading-body">End A Session</TH>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT WIDTH=50%>Active Booth</TD>
  <TD WIDTH=50% ALIGN=LEFT>
<?php
/*  print "SELECT users.name,fullname,users.credit
                          FROM users
                          LEFT OUTER JOIN reseller ON (reseller.id = " . $_SESSION['resellerid'] . " OR owner = " . $_SESSION['resellerid'] . ")
                          WHERE ((agentid=reseller.id AND admin = 't' AND reseller.id=" . $_SESSION['resellerid'] . ") OR
                                 (agentid=owner AND admin = 'f' AND reseller.id=" . $_SESSION['resellerid'] . "))
                                 AND activated='t' AND usertype=2 AND inuse <= 0
                            ORDER BY fullname";
*/
?>
  <SELECT NAME=cno><?php
  $sesbq="SELECT users.name,fullname,users.credit
                          FROM users
                          LEFT OUTER JOIN reseller ON (reseller.id = " . $_SESSION['resellerid'] . " OR owner = " . $_SESSION['resellerid'] . ")
                          WHERE ((agentid=reseller.id AND admin = 't' AND reseller.id=" . $_SESSION['resellerid'] . ") OR
                                 (agentid=owner AND admin = 'f' AND reseller.id=" . $_SESSION['resellerid'] . "))
                                 AND (activated='t' AND usertype=2 AND inuse <= 0)
                            ORDER BY fullname";

  $sesbooth=pg_query($db,$sesbq);

  $num=pg_num_rows($sesbooth);
  for ($i=0; $i < $num; $i++) {
    $r = pg_fetch_array($sesbooth,$i,PGSQL_NUM);
    $r[2]=sprintf("%0.2f",($r[2]*$_SESSION['rexrate'])/10000);
    print "<OPTION VALUE=\"" . $r[0] . "\">Booth " . $r[1] . " (" . $r[0] . " Credit:" . $r[2]. ")</OPTION>\n";
  }?>
</SELECT></TD></TR>
<TR CLASS=list-color2>
<TD ALIGN=MIDDLE COLSPAN=2>
<INPUT TYPE=SUBMIT NAME=endses VALUE="End Session">
</TABLE>
</FORM>
<?php }?>
