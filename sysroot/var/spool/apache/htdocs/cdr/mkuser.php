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

include "autoadd.inc";

if ((isset($_POST['mmap'])) && (!isset($_POST['pbxupdate']))) {
  $_POST['exten']=$_POST['mmap'];
  $_POST['pbxupdate']=true;
}

?>
<FORM METHOD=POST NAME=extenform onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=delext>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<?php print $showpage;?>">
<INPUT TYPE=HIDDEN NAME=nomenu VALUE="<?php if ($_POST['nomnenu'] < 2) {print $_POST['nomenu'];}?>">
<?php

/* Add New Extension */


if ((isset($_POST['pbxupdate'])) && ($_POST['exten'] == "") && (($_POST['prefix'] != "") || ($npre != "")) && ($_POST['cno'] != "") && (strlen($_POST['cno']) == 2)) {
  $_POST['exten']=$_POST['prefix'] . $_POST['cno'];

  $isval=pg_query($db,"SELECT 1 FROM features WHERE exten='" . $_POST['exten'] . "'");
  if (pg_num_rows($isval) > 0) {
    include "vladmin.php";
  } else {
    if (isset($npre)) {
      pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('LocalPrefix','" . $npre . "','1')");
      $dpre=pg_query($db,"SELECT value FROM astdb WHERE family='Setup' AND key='DefaultPrefix'");
      if (pg_num_rows($dpre) > 0) {
        pg_query($db,"UPDATE astdb SET value='" . $npre . "' WHERE family='Setup' AND key='DefaultPrefix'");
      } else {
        pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('Setup','DefaultPrefix','" . $npre . "')");
      }
      $_POST['prefix']=$npre;
    } else {
      $ispre=pg_query($db,"SELECT value FROM astdb WHERE family = 'LocalPrefix' AND key = '" . $_POST['prefix'] . "'");
      if (pg_num_rows($ispre) <= 0) {
        pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('LocalPrefix','" . $_POST['prefix'] . "','1')");
      }
    }
    do {
      $rndpin=randpwgen(8);
    } while($rndpin == $_POST['exten']);
    $exaddq="INSERT INTO users (context,name,defaultuser,mailbox,secret,usertype,
                                     fullname,callgroup,pickupgroup,qualify) VALUES (
                                     '6','" . $_POST['prefix'] . $_POST['cno'] . "','" . $_POST['prefix'] . $_POST['cno'] . "','" . $_POST['prefix'] . $_POST['cno'] . "@6',
                                     '" . $rndpin . "','0','Exten " . $_POST['prefix'] . $_POST['cno'] . "','1','1','yes')";
    $exadd=pg_query($db,$exaddq);
    pg_query($db,"INSERT INTO voicemail (mailbox,context,email,fullname,password) SELECT users.name,context,'',fullname,name FROM users WHERE users.name = '" . $_POST['prefix'] . $_POST['cno'] . "'");
    pg_query($db,"INSERT INTO features (exten) VALUES ('" . $_POST['prefix'] . $_POST['cno'] . "')");
    setdefaults($_POST['prefix'] . $_POST['cno']);
    include "vladmin.php";
  }
} else if ((isset($_POST['pbxupdate'])) && ($_POST['exten'] != "")) {
  $exedit=pg_query($db,"SELECT exten FROM features WHERE exten='" . $_POST['exten'] . "'");
  if (pg_num_rows($exedit) > 0) {
    include "vladmin.php";
  }
} else if ((!isset($_POST['pbxupdate'])) || ($_POST['exten'] == "")){
  if ((isset($delext)) && ($_POST['exten'] != "")) {
    pg_query($db,"DELETE FROM voicemail WHERE mailbox='" . $_POST['exten'] . "'");
    pg_query($db,"DELETE FROM users WHERE name='" . $_POST['exten'] . "'");
    pg_query($db,"DELETE FROM astdb WHERE family='" . $_POST['exten'] . "'");
    pg_query($db,"DELETE FROM features WHERE exten='" . $_POST['exten'] . "'");
    pg_query($db,"DELETE FROM console WHERE mailbox='" . $_POST['exten'] . "'");
    $delpre=pg_query($db,"SELECT name from features where exten ~ '^" . substr($_POST['exten'],0,2) . "'");
    if (pg_num_rows($delpre) <= 0) {
      $delpre2=pg_query($db,"SELECT value from astdb where family = 'Setup' AND key = 'DefaultPrefix' AND value = '" . substr($_POST['exten'],0,2) . "'");
      if (pg_num_rows($delpre2) <= 0) {
        pg_query($db,"DELETE FROM astdb WHERE family = 'LocalPrefix' and key = '" . substr($_POST['exten'],0,2) . "'");
      }
    }
  }
?>
<CENTER>
<table border="0" width="90%" cellspacing="0" cellpadding="0">
<TR CLASS=list-color2>
  <TH COLSPAN=2 CLASS=heading-body><?php print _("Select Extension");?></TH>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT WIDTH=50% onmouseover="myHint.show('ESS0')" onmouseout="myHint.hide()"><?php print _("Extension To Configure");?></TH>
  <TD WIDTH=50% ALIGN=LEFT>
  <SELECT NAME=exten>
<?php
  if ($SUPER_USER == 1) {
?>
    <OPTION VALUE=""><?php print _("Select From List Or Enter Bellow");?></OPTION>
<?php
  }
  $curextq="SELECT name,fullname||'('||name||')' AS fname from users
                      left outer join astdb on (substr(name,0,3)=key)";
  if ($SUPER_USER != 1) {
    $curextq.=" LEFT OUTER JOIN astdb AS bgrp ON (name=bgrp.family AND bgrp.key='BGRP')";
  }
  $curextq.=" WHERE length(name) = 4 AND astdb.family = 'LocalPrefix' AND astdb.value='1'";
  if ($SUPER_USER != 1) {
    $curextq.=" AND " . $clogacl;
  }
  $curextq.=" ORDER BY fname";

  $curext=pg_query($db,$curextq);
  $num=pg_num_rows($curext);
  for($i=0;$i < $num;$i++) {
    $r = pg_fetch_array($curext,$i,PGSQL_NUM);
    print "    <OPTION VALUE=\"" .  $r[0] . "\">" . $r[1] . "</OPTION>\n";
  }
?>
  </SELECT>
  </TD></TR>
<?php
  if ($SUPER_USER == 1) {
?>
  <TR CLASS=list-color2>
  <TD ALIGN=LEFT WIDTH=50% onmouseover="myHint.show('ESS1')" onmouseout="myHint.hide()">Extension Prefix/Number</TH>
  <TD WIDTH=50% ALIGN=LEFT>
<?php
    if (! isset($_POST['prefix'])) {
      $defpre=pg_query($db,"SELECT value FROM astdb WHERE family='Setup' AND key='DefaultPrefix'");
      $def = pg_fetch_array($defpre,0,PGSQL_NUM);
      $dpre=$def[0];
    } else {
      $dpre=$_POST['prefix'];
    }
    $epres=pg_query($db,"SELECT key FROM astdb WHERE family='LocalPrefix' AND value='1' ORDER BY key");
    $num=pg_num_rows($epres);

    if (($num > 0) || (pg_num_rows($defpre) > 0)){
      print "  <SELECT NAME=prefix>\n";
      for ($i=0; $i < $num; $i++) {
        $r = pg_fetch_array($epres,$i,PGSQL_NUM);
        print "    <OPTION VALUE=\"" . $r[0] . "\"";
        if ($dpre == $r[0]) {
          $dsel=1;
          print " SELECTED";
        }
        print ">" . $r[0] . "</OPTION>\n";
      }
     if (($dsel != 1) && ($dpre != "")) {
        print "    <OPTION VALUE=\"" . $dpre . "\" SELECTED>" . $dpre . "</OPTION>\n";
     }
     print "  </SELECT>\n";
   } else {
     print "  <INPUT TYPE=TEXT NAME=npre MAXLENGTH=2 SIZE=2>";
   }
?>
    <INPUT TYPE=TEXT NAME=cno MAXLENGTH=2 SIZE=2 VALUE="<?php print $_POST['cno'];?>"></TD></TR>
  <TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
  <TR CLASS=list-color1>
<?php
  } else {
?>
    <TR CLASS=list-color2>
<?php
  }
?>
  <TH COLSPAN=2>
<?php
  if ($SUPER_USER == 1) {
?>
    <INPUT TYPE=SUBMIT onclick=this.name='pbxupdate' VALUE="<?php print _("Add/Edit");?>">
    <INPUT TYPE=BUTTON VALUE="<?php print _("Delete");?>" onclick=deleteexten()>
<?php
  } else {
?>
    <INPUT TYPE=SUBMIT onclick=this.name='pbxupdate' VALUE="<?php print _("Edit");?>">
<?php
  }
}
?>
</TABLE>
</FORM>

