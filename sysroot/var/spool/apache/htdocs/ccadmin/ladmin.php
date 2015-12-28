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

$prios=array("Low","Medium","High","Urgent");

if ($_POST['active'] == "on") {
  $_POST['active']='t';
} else {
  $_POST['active']='f';
}

if ($_POST['osticket'] == "on") {
  $_POST['osticket']='t';
} else {
  $_POST['osticket']='f';
}

if ($_POST['directdial'] == "on") {
  $_POST['directdial']='t';
} else {
  $_POST['directdial']='f';
}

if ($_POST['transfer'] == "on") {
  $_POST['transfer']='t';
} else {
  $_POST['transfer']='f';
}

if (ereg("(2[01][0-9]{2})-([0-9]{2})-([0-9]{2})$",$_POST['callbefore'],$dateinf)) {
  $curdate=getdate();
  if ((!checkdate($dateinf[2],$dateinf[3],$dateinf[1])) || ($dateinf[1] < $curdate['year']) || 
      (($curdate['year'] == $dateinf[1]) && ($dateinf[2] < $curdate['mon'])) ||
      (($curdate['year'] == $dateinf[1]) && ($dateinf[2] == $curdate['mon']) && ($dateinf[3] < $curdate['mday']))) {
    $_POST['callbefore']="";
  }
} else {
  $_POST['callbefore']="";
}

if ((isset($_POST['id'])) && ($_POST['id'] != "")) {
  $getid=pg_query($db,"SELECT id,description||' ('||name||')' FROM campaign LEFT OUTER JOIN camp_admin ON (campaign.id=camp_admin.campaign AND camp_admin.userid='" . $_SERVER['PHP_AUTH_USER'] . "') WHERE id=" .  $_POST['id']);
  list($_SESSION['campid'],$_SESSION['campname'])=pg_fetch_array($getid,0);
} else if (($_SESSION['campid'] != "") && (($_POST['description'] ==  "") || ($_POST['callbefore'] == "")) && (((isset($_POST['editlist'])) && ($_POST['listid'] == "")) || (isset($_POST['uplist'])))) {?>
<SCRIPT>
  alert("Insufficient Information Supplied");
</SCRIPT><?php
} else if ((isset($_POST['editlist'])) && ($_SESSION['campid'] != "") && ($_POST['listid'] == "")) {
  pg_query($db,"INSERT INTO list (description,callbefore,priority,active,acdmatch,campaign,owner,osticket,directdial,transfer,dialretry) VALUES ('" . $_POST['description'] . "','" . $_POST['callbefore'] . "','" . $_POST['priority'] . "','" . $_POST['active'] . "','" . $_POST['acdmatch'] . "','" . $_SESSION['campid'] . "','" . $_SESSION['uid'] . "','" . $_POST['osticket'] . "','" . $_POST['directdial'] . "','" . $_POST['transfer'] . "','" . $_POST['dialretry'] . "')");
//'" . pg_escape_bytea($db,$_POST['information']) . "'
  $getid=pg_query($db,"SELECT id FROM list WHERE campaign=" . $_SESSION['campid'] . " AND description='" . $_POST['description'] . "'");
  list($_SESSION['listid'])=pg_fetch_array($getid,0);
} else if ((isset($_POST['editlist'])) && ($_SESSION['campid'] != "") && ($_POST['listid'] != "")) {
  $_SESSION['listid']=$_POST['listid'];
  $getlist=pg_query($db,"SELECT id,description,date(callbefore),information,priority,active,acdmatch,osticket,directdial,transfer,dialretry FROM list WHERE campaign=" .  $_SESSION['campid'] . " AND id=" .  $_SESSION['listid']);
  list($_POST['uniqueid'],$_POST['description'],$_POST['callbefore'],$_POST['information'],$_POST['priority'],$_POST['active'],$_POST['acdmatch'],$_POST['osticket'],$_POST['directdial'],$_POST['transfer'],$_POST['dialretry'])=pg_fetch_array($getlist,0);
} else if ((isset($_POST['uplist'])) && ($_SESSION['campid'] != "") && ($_SESSION['listid'] != "")) {
  pg_query($db,"UPDATE list SET description='" . $_POST['description'] . "',callbefore='" . $_POST['callbefore'] . "',active='"  . $_POST['active'] . "',acdmatch='"  . $_POST['acdmatch'] . "',osticket='" . $_POST['osticket'] . "',directdial='" . $_POST['directdial'] . "',transfer='" . $_POST['transfer'] . "',dialretry='" . $_POST['dialretry'] . "' WHERE campaign=" .  $_SESSION['campid'] . " AND id=" .  $_SESSION['listid']);
//,information='" . pg_escape_bytea($db,$_POST['information']) . "'
}

/*
} else if ((isset($_POST['delcamp'])) && ($_POST['id'] != "")) {
  pg_query($db,"DELETE FROM campaign WHERE id=" .  $_SESSION['campid']);
  pg_query($db,"DELETE FROM camp_admin WHERE campaign=" .  $_SESSION['campid']);
*/

?>
<FORM NAME=ladmin METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<CENTER>
<TABLE border=0 width=90% cellspacing=0 cellpadding=0>
<?php

if ((!isset($_POST['editlist'])) && (!isset($_POST['uplist'])) && (!isset($_POST['id']))) {
  unset($_SESSION['campid']);
  unset($_SESSION['listid']);
  $getcamp=pg_query($db,"SELECT id,description||' ('||name||')' FROM campaign LEFT OUTER JOIN camp_admin ON (campaign.id=camp_admin.campaign AND camp_admin.userid='" . $_SERVER['PHP_AUTH_USER'] . "')" . $_SESSION['limitadmin'] . " ORDER by description,name");?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TH COLSPAN=2 CLASS=heading-body>
      <?php print _("Select A Campaign To Modify");?>
    </TH>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD WIDTH=50%>
      <?php print _("Select Campaign To Configure Lists");?>
    </TD>
    <TD>
      <SELECT NAME=id onchange="ajaxsubmit(this.form.name)">
        <OPTION VALUE=""></OPTION><?php
        for($ccnt=0;$ccnt<pg_num_rows($getcamp);$ccnt++) {
          list($cid,$cname)=pg_fetch_array($getcamp,$ccnt);?>
          <OPTION VALUE="<?php print $cid;?>"><?php print $cname?></OPTION><?php
        }?>
    </TD>
  </TR><?php
} else if (!isset($_POST['uplist'])) {
  if ((isset($_SESSION['campid'])) && (!isset($_SESSION['listid']))) {
    $getlist=pg_query($db,"SELECT id,description FROM list WHERE campaign=" . $_SESSION['campid'] . "ORDER by description");?>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TH COLSPAN=2 CLASS=heading-body>
        <?php print _("Select A List To Edit/Add Or Delete For Campaign") . " " . $_SESSION['campname'];?>
      </TH>
    </TR>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TD WIDTH=50%>
        <?php print _("Select List");?>
      </TD>
      <TD>
        <SELECT NAME=listid>
          <OPTION VALUE="">Add New Campaign List (Fill In Bellow)</OPTION><?php
          for($ccnt=0;$ccnt<pg_num_rows($getlist);$ccnt++) {
            list($cid,$cname)=pg_fetch_array($getlist,$ccnt);?>
            <OPTION VALUE="<?php print $cid;?>"><?php print $cname?></OPTION><?php
          }?>
      </TD>
    </TR><?php
  } else {?>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TH COLSPAN=2 CLASS=heading-body>
         <?php print _("Editing") . " " . $_SESSION['listname'] . " " . _("List For Campaign") . " " . $_SESSION['campname'];?>
      </TH>
    </TR><?php
  }?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD WIDTH=50%>
      <?php print _("Priority Of Calls In This List Compared To Other Lists");?>
    </TD>
    <TD>
      <SELECT NAME=priority><?php
      for($pcnt=1;$pcnt <= count($prios);$pcnt++) {
        print "<OPTION VALUE=" . $pcnt;
        if ($_POST['priority'] == $pcnt) {
          print " SELECTED";
        }
        print ">" . $prios[$pcnt-1] . "</OPTION>";
      }?>
      </SELECT>
    </TD>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD>
      <?php print _("Description");?>
    </TD>
    <TD>
      <INPUT NAME=description VALUE="<?php print $_POST['description'];?>">
    </TD>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD>
      <?php print _("Call Before (Date YYYY-MM-DD)");?>
    </TD>
    <TD>
      <INPUT NAME=callbefore VALUE="<?php print $_POST['callbefore'];?>">
    </TD>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD>
      <?php print _("Delay Before Calling Back (seconds)");?>
    </TD>
    <TD>
      <INPUT NAME=dialretry VALUE="<?php print (($_POST['dialretry'] == "")?"1800":$_POST['dialretry']);?>">
    </TD>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD>
      <?php print _("ACD Regex Pattern For This Data");?>
    </TD>
    <TD>
      <INPUT NAME=acdmatch VALUE="<?php print $_POST['acdmatch'];?>">
    </TD>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD>
      <?php print _("List Is Active");?>
    </TD>
    <TD>
      <INPUT TYPE=CHECKBOX NAME=active<?php if ((!isset($_POST['active'])) || ($_POST['active'] == 't')) {print " CHECKED";}?>>
    </TD>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD>
      <?php print _("OST Ticket Intergration");?>
    </TD>
    <TD>
      <INPUT TYPE=CHECKBOX NAME=osticket<?php if ((!isset($_POST['osticket'])) || ($_POST['osticket'] == 't')) {print " CHECKED";}?>>
    </TD>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD>
      <?php print _("Allow Transfer");?>
    </TD>
    <TD>
      <INPUT TYPE=CHECKBOX NAME=transfer<?php if ((!isset($_POST['transfer'])) || ($_POST['transfer'] == 't')) {print " CHECKED";}?>>
    </TD>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD>
      <?php print _("Allow Direct Dial");?>
    </TD>
    <TD>
      <INPUT TYPE=CHECKBOX NAME=directdial<?php if ((!isset($_POST['directdial'])) || ($_POST['directdial'] == 't')) {print " CHECKED";}?>>
    </TD>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD COLSPAN=2 ALIGN=MIDDLE><?php
      if ((isset($_SESSION['campid'])) && (!isset($_SESSION['listid']))){?>
        <INPUT TYPE=SUBMIT onclick="this.name='dellist'" VALUE="<?php print _("Delete")?>">
        <INPUT TYPE=SUBMIT onclick="this.name='editlist'" VALUE="<?php print _("Edit/Add")?>"><?php
      } else {?>
        <INPUT TYPE=BUTTON ONCLICK=testscript('<?php print $_POST['uniqueid'];?>') VALUE="<?php print _("Edit Script");?>">
        <INPUT TYPE=SUBMIT NAME=uplist VALUE="<?php print _("Update");?>"><?php
      }?>
    </TD>
  </TR><?php
} else {?>
  <TR CLASS=list-color2>
    <TH CLASS=heading-body>
      <?php print _("Script For") . " " . $_SESSION['campname'] . _("Campaign") . " [" . $_POST['description'] . " " . _("List") . "]";?>
    </TH>
  </TR>
  <TR CLASS=list-color1>
    <TD>
<?php
  $data_tb=strtolower("contactdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid']);
  $data_tb2=strtolower("inputdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid']);
?>
    </TD>
  </TR>
  <TR CLASS=list-color2>
    <TH CLASS=heading-body2>
      Database Table Setup <?php print $data_tb;?>
    </TD>
  </TR>
  <TR CLASS=list-color1>
    <TD><?php
      $testdb=pg_query($db,"SELECT * from information_schema.tables where table_catalog='asterisk' and table_name='" . $data_tb . "'");
      if (pg_num_rows($testdb) == 1) {
        print "Contact Database Table Exists<BR>";
      } else {
        print "Creating Contact Database Table<BR>";
        pg_query($db,"CREATE TABLE " . $data_tb . " (id bigserial,leadid bigint,contid bigint)");
      }
      $testdb=pg_query($db,"SELECT * from information_schema.tables where table_catalog='asterisk' and table_name='" . $data_tb2 . "'");
      if (pg_num_rows($testdb) == 0) {
        print "Creating Input Database Table<BR>";
        pg_query($db,"CREATE TABLE " . $data_tb2 . " (id bigserial,leadid bigint)");
        pg_query($db,"ALTER TABLE " . $data_tb2 . " ADD CONSTRAINT key_" . $data_tb2 . " PRIMARY KEY (id)");
        pg_query($db,"CREATE UNIQUE INDEX " . $data_tb2 . "_contact ON " . $data_tb2 . " USING btree (leadid)");
      }
      $testdbtbl=pg_query($db,"SELECT column_name,data_type,character_maximum_length from information_schema.columns where table_catalog='asterisk' and column_name='osticket' and table_name='" . $data_tb2 . "'");
      if (pg_num_rows($testdbtbl) == 0) {
        pg_query("ALTER TABLE " . $data_tb2 . " ADD osticket integer");
        print "Adding Input Database Field osticket<BR>";
      }

      $testdbtbl=pg_query($db,"SELECT column_name,data_type,character_maximum_length from information_schema.columns where table_catalog='asterisk' and table_name='" . $data_tb . "'");
      for($dtrcnt=0;$dtrcnt < pg_num_rows($testdbtbl);$dtrcnt++) {
        list($trown,$trowt,$trowd)=pg_fetch_array($testdbtbl,$dtrcnt);
        if ($trowt == "character varying") {
          $trowt=$varchart;
        }
        if ($dbrows[$trown] == $trowt) {
          unset($dbrows[$trown]);
          print "Database Field " . $trown . " Is Ok<BR>";
        } else {
          print "Database Field " . $trown . " Is Bad Type " . $trowt . "<BR>";
          pg_query("ALTER TABLE " . $data_tb . " DROP " . $trown);
        }
      }
      while(list($trow,$ttype) = each($dbrows)) {
        pg_query("ALTER TABLE " . $data_tb . " ADD " . $trow . " " . $ttype);
        print "Adding Database Field " . $trow . "<BR>";
      }?>
    </TD>
<?php
}
?>
</TABLE>
</FORM>
