<CENTER>
<FORM NAME=ppage METHOD=POST>
<INPUT TYPE=HIDDEN NAME=print>
</FORM>
<FORM NAME=officehours METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<INPUT TYPE=HIDDEN NAME=index VALUE="">
<INPUT TYPE=HIDDEN NAME=timerange VALUE="">
<%

include_once "cdr/auth.inc";
include_once "ldap/auth.inc";
if ($ldn == "") {
  include_once "reception/auth.inc";
  $getgrp=pg_query($db,"SELECT value FROM astdb WHERE key='BGRP' AND family='" . $_SERVER['PHP_AUTH_USER'] . "'");
  if (pg_num_rows($getgrp) > 0) {
    list($bgroup)=pg_fetch_array($getgrp,0,PGSQL_NUM);
  }
}

$sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
  $ADMIN_USER="admin";
} else {
  $ADMIN_USER="pleb";
}

$rcol=1;
if (!isset($bgroup)) {
  print "<TR CLASS=list-color" . (($rcol % 2 )+1) . "><TH COLSPAN=2 CLASS=heading-body>" . _("Select Group") . "</TH></TR>\n";
  pg_query($db,"insert INTO officehours (dayrange,monthday,month,year,pubhol,description,starttime,stoptime,bgroup) SELECT DISTINCT dayrange,monthday,month,year,pubhol,description,starttime,stoptime,value from officehours left outer join astdb on (key='BGRP') where (bgroup is null OR bgroup='') AND (SELECT count(*) = 0 from officehours WHERE bgroup=value) AND value is not null and value !=''");
  $rcol++;
  print "<TR CLASS=list-color" . (($rcol % 2 )+1) . "><TD WIDTH=50%>" . _("Group") . "</TD>\n";
  $rcol++;
  $grpsql=pg_query($db,"SELECT DISTINCT value FROM astdb WHERE key='BGRP' AND value IS NOT NULL AND value != ''");
  print "<TD>";
  print "<SELECT NAME=bgroup>\n";
  print "<OPTION VALUE=\"\">Default</OPTION>\n";
  for($gcnt=0;$gcnt < pg_num_rows($grpsql);$gcnt++) {
    $r=pg_fetch_array($grpsql,$gcnt);
    print "<OPTION VALUE=\"" . $r[0] . "\">" . $r[0] . "</OPTION>\n";
  }
  print "</SELECT></TD></TR>\n";
  print "<TR CLASS=list-color" . (($rcol % 2 )+1) . "><TD COLSPAN=2 ALIGN=MIDDLE>";
  $rcol++;
  print "<INPUT TYPE=SUBMIT VALUE=\"" . _("Update") . "\"></TD></TR>\n";
  print "</FORM></TABLE>";
  exit;
}

if (($_POST['print'] != "1") && ($ADMIN_USER != "pleb")) {
  $cspan="5";
} else {
  $cspan="4";
}

print "<TR CLASS=list-color" . (($rcol % 2 )+1) . "><TH COLSPAN=" . $cspan . ">" . _("Normal Office Hours") . "</TH></TR>\n";
print "<INPUT TYPE=HIDDEN NAME=bgroup VALUE=\"" . $bgroup . "\">";
$rcol++;
print "<TR CLASS=list-color" . (($rcol % 2 )+1) . ">";
if (($_POST['print'] != "1") && ($ADMIN_USER != "pleb")) {
  print "<TH>" . _("Delete") . "</TH>";
}
print "<TH COLSPAN=3>" . _("Office Hours") . "</TH><TH>" . _("Days Affected") . "</TH></TR>\n";
$rcol++;

if (($index != "") && ($timerange != "")) {
  list($startt,$stopt)=explode("-",$timerange);
  list($starth,$startm)=explode(":",$startt);
  list($stoph,$stopm)=explode(":",$stopt);
  $startt=60*$starth+$startm;
  $stopt=60*$stoph+$stopm;
  pg_query("UPDATE officehours SET starttime=" . $startt . ",stoptime=" . $stopt . " WHERE index=" . $index . " AND bgroup='" . $bgroup . "'");
} else if (($newtimehf != "") && ($newtimeht != "") && ($holday != "") && ($holmon != "") && ($newdisc != "")) {
  list($startt,$stopt)=explode("-",$newtimehf . "-" . $newtimeht);
  list($starth,$startm)=explode(":",$startt);
  list($stoph,$stopm)=explode(":",$stopt);
  $startt=60*$starth+$startm;
  $stopt=60*$stoph+$stopm;
  pg_query("INSERT INTO officehours (dayrange,pubhol,starttime,stoptime,monthday,month,description,bgroup) VALUES ('*','t'," . $startt . "," . $stopt . ",'" . $holday . "','" . $holmon . "','" . $newdisc . "','" . $bgroup . "')");
} else if (($newtimef != "") && ($newtimet != "") && ($newfromday != "") && ($newtoday != "")) {
  list($startt,$stopt)=explode("-",$newtimef . "-" . $newtimet);
  list($starth,$startm)=explode(":",$startt);
  list($stoph,$stopm)=explode(":",$stopt);
  $startt=60*$starth+$startm;
  $stopt=60*$stoph+$stopm;
  if ($newtoday < $newfromday) {
    $tmpday=$newfromday;
    $newfromday=$newtoday;
    $newtoday=$tmpday;
  }
  if ($newtoday == "7") {
    if ($newfromday == "7") {
      $ndayrange="0";
    } else if ($newfromday == "6") {
      $ndayrange="06";
    } else {
      $ndayrange="0" . $newfromday . "-6";
    }
  } else if ($newfromday != $newtoday) {
    $ndayrange=$newfromday . "-" . $newtoday;
  } else {
    $ndayrange=$newfromday;
  }
  pg_query($db,"INSERT INTO officehours (starttime,stoptime,dayrange,bgroup) VALUES (" . $startt . "," . $stopt . ",'[" . $ndayrange . "]','" . $bgroup . "')");
}

$days[0]=_("Sunday");
$days[1]=_("Monday");
$days[2]=_("Tuesday");
$days[3]=_("Wednesday");
$days[4]=_("Thursday");
$days[5]=_("Friday");
$days[6]=_("Saturday");
$days[7]=_("Sunday");

$mon[1]=_("January");
$mon[2]=_("Febuary");
$mon[3]=_("March");
$mon[4]=_("April");
$mon[5]=_("May");
$mon[6]=_("June");
$mon[7]=_("July");
$mon[8]=_("August");
$mon[9]=_("September");
$mon[10]=_("October");
$mon[11]=_("November");
$mon[12]=_("December");

$tariffq1=pg_query($db,"SELECT starttime,stoptime,dayrange,index FROM officehours WHERE NOT pubhol AND bgroup='" . $bgroup . "'");

for($tcnt=0;$tcnt<pg_num_rows($tariffq1);$tcnt++) {
  $r=pg_fetch_array($tariffq1,$tcnt);
  $todel="del" . $r[3];
  if ($$todel == "on") {
    pg_query("DELETE FROM officehours WHERE index=" . $r[3]);
    continue;
  }
  $starth=str_pad(($r[0]-($r[0] % 60))/60,2,"0",STR_PAD_LEFT);
  $startm=str_pad($r[0] % 60,2,"0");
  $stoph=str_pad(($r[1]-($r[1] % 60))/60,2,"0",STR_PAD_LEFT);
  $stopm=str_pad($r[1] % 60,2,"0");

  if (($r[0] == "0") && ($r[1] == "1440")) {
    $time=_("Open");
  } else {
    $time=_("Open") . " (" . $starth . ":" . $startm . "-" . $stoph . ":" . $stopm . ")";
  }

  $r[2]=substr($r[2],1,-1);
  $ohdays=explode("-",$r[2]);
  if ($ohdays[1] == "") {
    if (($ohdays[0][0] == "0") && ($ohdays[0][1] != "")) {
      $ohdays[1]="7";
      $ohdays[0]=$ohdays[0][1];
    } else {
      $ohdays[1]=$ohdays[0];
    }
  } else if (($ohdays[0][0] == "0") && (strlen($ohdays[0]) > 1)){
    $ohdays[0]=$ohdays[0][1];
    $ohdays[1]=7;
  } else if ($ohdays[0][0] == "0") {
    $ohdays[0]++;
    $ohdays[1]++;
  }

  if (($_POST['print'] != "1") && ($ADMIN_USER == "admin")) {
    $time="<A HREF=javascript:editivrtime('" . $r[3] . "','" . $starth . ":" . $startm . "-" . $stoph . ":" . $stopm . "')>" . $time . "</A>"; 
  }

  print "<TR CLASS=list-color" . (($rcol % 2 )+1) . ">";
  if (($_POST['print'] != "1") && ($ADMIN_USER != "pleb")) {
    print "<TD><INPUT TYPE=CHECKBOX NAME=del" . $r[3] . "></TD>";
  }
  print "<TD COLSPAN=3>" . $time . "</TD><TD>" . $days[$ohdays[0]];
  if ($ohdays[0] != $ohdays[1]) {
    print "-" . $days[$ohdays[1]];
  }
  print "</TD></TR>\n";
  $rcol++;
}

if (($_POST['print'] != "1") && ($ADMIN_USER == "admin")) {
  print "<TR CLASS=list-color" . (($rcol % 2 )+1) . "><TD>&nbsp;</TD><TD>" . _("Time Range") . " HH:MM-HH:MM<BR>" . _("From") . " 00:00-24:00</TD><TD>";
  print "<INPUT TYPE=TEXT NAME=newtimef MAXLEN=5 SIZE=5 VALUE=>-";
  print "<INPUT TYPE=TEXT NAME=newtimet MAXLEN=5 SIZE=5 VALUE=>";
  print "</TD>";
  print "<TD>" . _("Day Range") . "</TD><TD><SELECT NAME=newfromday>";

  for($dnum=1;$dnum <= 7;$dnum++) {
    print "<OPTION VALUE=" . $dnum . ">" . $days[$dnum] . "</OPTION>\n";
  }

  print "</SELECT>-<SELECT NAME=newtoday>";

  for($dnum=1;$dnum <= 6;$dnum++) {
    print "<OPTION VALUE=" . $dnum . ">" . $days[$dnum] . "</OPTION>\n";
  }
  print "<OPTION VALUE=" . $dnum . " SELECTED>" . $days[$dnum] . "</OPTION>\n";
  print "</SELECT></TD></TR>\n";
  $rcol++;
}
/*
SELECT * from officehours where NOT pubhol AND  date_part('dow',now()) ~ dayrange AND date_part('hour',now())*60+date_part('min',now()) > starttime AND  
date_part('hour',now()-interval '10 hours')*60+date_part('min',now()) < stoptime-1;
*/

print "<TR CLASS=list-color" . (($rcol % 2 )+1) . "><TH COLSPAN=" . $cspan . ">" . _("Public Holidays") . 
"</TH></TR>\n";
$rcol++;
print "<TR CLASS=list-color" . (($rcol % 2 )+1) . ">";
if (($_POST['print'] != "1") && ($ADMIN_USER != "pleb")) {
  print "<TH>" . _("Delete") . "</TH>";
}
print "<TH>" . _("Open/Closed (Office Hours)") . "</TH><TH>" . _("Day") . "</TH><TH>" . _("Month")  . "</TH><TH>" . _("Description") . "</TH></TR>\n";
$rcol++;

$tariffq1=pg_query($db,"SELECT starttime,stoptime,lpad(monthday,2,'0'),lpad(month,2,'0'),description,date_part('year',now()),index FROM officehours where (year='' OR date_part('year',now()) = year)  AND pubhol AND bgroup='" . $bgroup . "' ORDER BY lpad(month,2,0),lpad(monthday,2,0)");

for($tcnt=0;$tcnt<pg_num_rows($tariffq1);$tcnt++) {
  $r=pg_fetch_array($tariffq1,$tcnt);
  $todel="del" . $r[6];
  if ($$todel == "on") {
    pg_query("DELETE FROM officehours WHERE index=" . $r[6]);
    continue;
  }
   
  $tstamp=mktime(0,0,0,$r[3],$r[2],$r[5],0);
  $dateinfo=getdate($tstamp);

  if ($dateinfo[wday] == 0) {
    $dateinfo=getdate($tstamp + 86400);
  }

  $r[2]=$dateinfo["mday"] . " (" . $dateinfo["weekday"] . ")";
  $r[3]=$dateinfo["mon"] . " (" . $dateinfo["month"] . ")";

  $starth=str_pad(($r[0]-($r[0] % 60))/60,2,"0",STR_PAD_LEFT);
  $startm=str_pad($r[0] % 60,2,"0");
  $stoph=str_pad(($r[1]-($r[1] % 60))/60,2,"0",STR_PAD_LEFT);
  $stopm=str_pad($r[1] % 60,2,"0");

  if (($r[0] == "0") && ($r[1] == "1440")) {
    $time=_("Closed");
  } else {
    $time=_("Open") . " (" . $starth . ":" . $startm . "-" . $stoph . ":" . $stopm . ")";
  }

  if (($_POST['print'] != "1") && ($ADMIN_USER == "admin")) {
    $time="<A HREF=javascript:editivrtime('" . $r[6] . "','" . $starth . ":" . $startm . "-" . $stoph . ":" . $stopm . "')>" . $time . "</A>"; 
  }

  print "<TR CLASS=list-color" . (($rcol % 2 )+1) . ">";
  if (($_POST['print'] != "1") && ($ADMIN_USER != "pleb")) {
    print "<TD><INPUT TYPE=CHECKBOX NAME=del" . $r[6] . ">";
  }
  print "<TD WIDTH=20%>" . $time . "</TD><TD WIDTH=20%>" . $r[2] . "</TD><TD ALIGN WIDTH=20%>" . $r[3];
  print "</TD><TD>" . $r[4] . "</TD></TR>\n";
  $rcol++;
}

if (($_POST['print'] != "1") && ($ADMIN_USER == "admin")) {
  print "<TR CLASS=list-color" . (($rcol % 2 )+1) . "><TD>&nbsp;</TD></TD><TD>";
  print "<INPUT TYPE=TEXT NAME=newtimehf MAXLEN=5 SIZE=5 VALUE=00:00>-";
  print "<INPUT TYPE=TEXT NAME=newtimeht MAXLEN=5 SIZE=5 VALUE=24:00>";
  print "</TD>";
  print "<TD><SELECT NAME=holday>\n";
  for($hday=1;$hday <= 31;$hday++) {
    print "<OPTION VALUE=" . $hday . ">" . $hday . "</OPTION>\n";
  }
  print "</SELECT></TD><TD><SELECT NAME=holmon>\n";
  for($hmon=1;$hmon <= 12;$hmon++) {
    print "<OPTION VALUE=" . $hmon . ">" . $mon[$hmon] . "</OPTION>\n";
  }

  print "</TD><TD><INPUT TYPE=TEXT NAME=newdisc></TD></TR>";
  $rcol++;
}

if ($_POST['print'] != "1") {
  print "<TR CLASS=list-color" . (($rcol % 2)+1) . "><TH COLSPAN=" . $cspan . " CLASS=heading-body>";
  if ($ADMIN_USER == "admin") {
    print "<INPUT TYPE=SUBMIT VALUE=\"" . _("Update") . "\">";
  }
  print "<INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.ppage)\"></TH></TR>\n";
}
%>
</FORM>
</TABLE>
