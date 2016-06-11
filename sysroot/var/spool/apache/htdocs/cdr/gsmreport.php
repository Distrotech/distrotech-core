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

include_once "auth.inc";

if (($router != "") && ($channel != "") && ($dbrow != "")) {
  if ($dbrow == "outofservice") {
    if ($dbval == "") {
      $dbval=0;
    }
    pg_query($db,"UPDATE gsmchannels SET " . $dbrow . "=now() + interval '" . $dbval . "',faultcount=0 WHERE router='" . $router . "' AND channel='" . $channel . "'");
  } else if ($dbrow == "calltime") {
    $ctdata=preg_split("/:/",$dbval);
    if (count($ctdata) == 2) {
      if ($ctdata > 0) {
        $dbval=$ctdata[0]*60+$ctdata[1];
      } else {
        $dbval=$ctdata[0]*60-$ctdata[1];
      }
    } else {
      $dbval=$ctdata[0];
    }
    if ($dbval > 0) {
      $dbval="+" . $dbval;
    }
    pg_query("UPDATE gsmchannels SET " . $dbrow . "=" . $dbrow . $dbval . " WHERE router='" . $router . "' AND channel='" . $channel . "'");
  } else {
    pg_query($db,"UPDATE gsmchannels SET " . $dbrow . "='" . $dbval . "' WHERE router='" . $router . "' AND channel='" . $channel . "'"); 
  }
}

$getchanq=pg_query($db,"SELECT router,channel,calltime,inuse,starttime,endtime,CASE WHEN (expires > now()) THEN date_trunc('seconds',expires-now()) else date_trunc('seconds',now()-expires) END,CASE WHEN (now() > outofservice) THEN date_trunc('seconds',now()-outofservice) ELSE date_trunc('seconds',outofservice-now()) END,regex,faultcount,expires>now(),outofservice>now() from gsmchannels ORDER BY router,channel");

?>

<CENTER>
<FORM METHOD=POST NAME=gsmchan>
<INPUT TYPE=HIDDEN NAME=router>
<INPUT TYPE=HIDDEN NAME=channel>
<INPUT TYPE=HIDDEN NAME=dbval>
<INPUT TYPE=HIDDEN NAME=dbrow>
<INPUT TYPE=HIDDEN NAME=print>

<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
  <TR CLASS=list-color2>
    <TH CLASS=heading-body COLSPAN=9><?php print _("Asterisk GSM Channel Status");?></TH>
  </TR>

<TR CLASS=list-color1>
<TH CLASS=heading-body2><?php print _("Channel");?></TH>
<TH CLASS=heading-body2><?php print _("Calltime");?></TH>
<TH CLASS=heading-body2><?php print _("Inuse");?></TH>
<TH CLASS=heading-body2><?php print _("Start time");?></TH>
<TH CLASS=heading-body2><?php print _("End Time");?></TH>
<TH CLASS=heading-body2><?php print _("Expire(d/s)");?></TH>
<TH CLASS=heading-body2><?php print _("In/Out Of Service");?></TH>
<TH CLASS=heading-body2><?php print _("Number Match");?></TH>
<TH CLASS=heading-body2><?php print _("Faultcount");?></TH>
</TR>

<?php
$bcolor[0]="CLASS=list-color2";
$bcolor[1]="CLASS=list-color1";

$lastrtr="";
for($r=0;$r<pg_num_rows($getchanq);$r++) {
  print "<TR " . $bcolor[$r % 2] . ">";
  $gsm=pg_fetch_row($getchanq,$r);
  if ($lastrtr != $gsm[0]) {
    $lastrtr=$gsm[0];
    $tmpclr=$bcolor[0];
    $bcolor[0]=$bcolor[1];
    $bcolor[1]=$tmpclr;
    print "<TH COLSPAN=9 CLASS=heading-body2>" . $lastrtr . "</TH></TR>";
    print "<TR " . $bcolor[$r % 2] . ">";
  }
  for($col=1;$col < count($gsm)-2;$col++) {
    print "<TD>";
    if ($_POST['print'] != "1") {
      if (($col == "6" ) && ($gsm[10] == "f")) {
        $gsm[$col]="<A HREF=\"javascript:gsmchanup('" . $gsm[0] . "','" . $chan . "','" .  _("Enter date channel expires YYYY-MM-DD") . "','expires')\">" . $gsm[$col] . " " . _("Ago") . "</A>";
      } else if ($col == "6" ) {
        $gsm[$col]="<A HREF=\"javascript:gsmchanup('" . $gsm[0] . "','" . $chan . "','" .  _("Enter date channel expires YYYY-MM-DD") . "','expires')\">" . $gsm[$col] . "</A>";
      } else if (($col == "7" ) && ($gsm[11] == "t")) {
        $gsm[$col]="<A HREF=\"javascript:gsmchanup('" . $gsm[0] . "','" . $chan . "','" .  _("Enter period to set out of service HH:MM:SS") . "','outofservice')\">" . $gsm[$col] . " (" . _("Out") . ")</A>";
      } else if ($col == "7" ) {
        $gsm[$col]="<A HREF=\"javascript:gsmchanup('" . $gsm[0] . "','" . $chan . "','" .  _("Enter period to set out of service HH:MM:SS"). "','outofservice')\">" . $gsm[$col] . " (" . _("In") . ")</A>";
      } else if ($col == "3") {
        if ($gsm[$col] == "t") {
          $gsm[$col]=_("Yes");
          $dbval="1";
        } else {
          $gsm[$col]=_("No");
          $dbval="0";
        }
        $gsm[$col]="<A HREF=\"javascript:gsmchanup('" . $gsm[0] . "','" . $chan . "','" . $dbval . "','inuse')\">" . $gsm[$col] . "</A>";
      } else if ($col == "4") {
        $gsm[$col]="<A HREF=\"javascript:gsmchanup('" . $gsm[0] . "','" . $chan . "','" . _("Enter time channel becomes active HH:MM:SS") . "','starttime')\">" . $gsm[$col] . "</A>";
      } else if ($col == "5") {
        $gsm[$col]="<A HREF=\"javascript:gsmchanup('" . $gsm[0] . "','" . $chan . "','" . _("Enter time channel becomes inactive HH:MM:SS") . "','endtime')\">" . $gsm[$col] . "</A>";
      } else if ($col == "8") {
        if ($gsm[$col] == "") {
          $gsm[$col]="(^0[1-9]+)";
        }
        $gsm[$col]="<A HREF=\"javascript:gsmchanup('" . $gsm[0] . "','" . $chan . "','" . _("Enter pattern of numbers to match") . "','regex')\">" . $gsm[$col] . "</A>";
      } else if ($col == "2") {
        $secs=sprintf("%02d",$gsm[$col] % 60);
        $mins=($gsm[$col]-$secs)/60;
        $gsm[$col]=$mins . ":" . $secs;
        $gsm[$col]="<A HREF=\"javascript:gsmchanup('" . $gsm[0] . "','" . $chan . "','" . _("Enter calltime to add to channel in MM:SS or as SSSS") . "','calltime')\">" . $gsm[$col] . "</A>";
      } else if ($col == "1") {
        $chan=$gsm[$col];
        $gsm[$col]="<A HREF=\"javascript:opengsm('" . $gsm[0] . "','" . $gsm[1] . "')\">" . $gsm[1] . "</A>";
      }
    } else {
      if (($col == "8") && ($gsm[$col] == "")) {
          $gsm[$col]="(^0[1-9]+)";
      } else if ($col == "3") {
        if ($gsm[$col] == "t") {
          $gsm[$col]="Yes";
        } else {
          $gsm[$col]="No";
        }
      } else if (($col == "7" ) && ($gsm[11] == "t")) {
        $gsm[$col].=" (Out)";        
      } else if ($col == "7" ) {
        $gsm[$col].=" (In)";        
      } else if ($col == "2") {
        $secs=sprintf("%02d",$gsm[$col] % 60);
        $mins=($gsm[$col]-$secs)/60;
        $gsm[$col]=$mins . ":" . $secs;
      }
    }
    print $gsm[$col];
    print "</TD>";
  }
  print "</TR>";
}
if ($_POST['print'] != "1") {
  print "<TR " . $bcolor[$r % 2]  . "><TH COLSPAN=9 CLASS=heading-body><INPUT TYPE=BUTTON NAME=pbutton VALUE=\"" . _("Print") . "\" ONCLICK=\"printpage(document.gsmchan)\"></TH></TR>";
}

?>
</TABLE></FORM>
<FORM NAME=gsmform METHOD=POST>
  <INPUT TYPE=HIDDEN NAME=router>
  <INPUT TYPE=HIDDEN NAME=channel>
  <INPUT TYPE=HIDDEN NAME=modchan VALUE=1>
  <INPUT TYPE=HIDDEN NAME=nomenu VALUE=1>
  <INPUT TYPE=HIDDEN NAME=disppage VALUE=cdr/gsmroute.php>
</FORM>
