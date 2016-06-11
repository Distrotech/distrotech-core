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

?>
<FORM METHOD=POST NAME=cadminf onsubmit="ajaxsubmit(this.name);return false">
<CENTER>
<TABLE border=0 width=90% cellspacing=0 cellpadding=0>

<?php

if ($_POST['active'] == "on") {
  $_POST['active']='t';
} else if (isset($_POST['active'])) {
  $_POST['active']='f';
}

if ((isset($_POST['editcamp'])) && ($_POST['id'] == "")) {
  pg_query($db,"INSERT INTO campaign (description,name,priority,active) VALUES ('" . $_POST['description'] . "','" . $_POST['name'] . "','" . $_POST['priority'] . "','"  . $_POST['active'] . "')");
  $getid=pg_query($db,"SELECT id FROM campaign WHERE description='" . $_POST['description'] . "' AND name='" . $_POST['name'] . "'");
  list($_SESSION['campid'])=pg_fetch_array($getid,0);
} else if ((isset($_POST['editcamp'])) && ($_POST['id'] != "")) {
  $_SESSION['campid']=$_POST['id'];
  $getcamp=pg_query($db,"SELECT description,name,priority,active FROM campaign WHERE id=" .  $_SESSION['campid']);
  list($_POST['description'],$_POST['name'],$_POST['priority'],$_POST['active'])=pg_fetch_array($getcamp,0);
} else if ((isset($_POST['delcamp'])) && ($_POST['id'] != "")) {
  pg_query($db,"DELETE FROM campaign WHERE id=" .  $_SESSION['campid']);
  pg_query($db,"DELETE FROM camp_admin WHERE campaign=" .  $_SESSION['campid']);
} else if ((isset($_POST['upcamp'])) && ($_SESSION['campid'] != "")) {
  pg_query($db,"UPDATE campaign SET description='" . $_POST['description'] . "',name='" . $_POST['name'] . "',priority='" . $_POST['priority'] . "',active='"  . $_POST['active'] . "' WHERE id=" . $_SESSION['campid']);
}

if ((!isset($_POST['editcamp'])) && (!isset($_POST['upcamp']))) {
  $getcamp=pg_query($db,"SELECT id,description||' ('||name||')' FROM campaign ORDER by description,name");
?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TH COLSPAN=2 CLASS=heading-body>
      <?php print _("Select A Campaign To Edit/Add Or Delete");?>
    </TH>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD WIDTH=50%>
      <?php print _("Select Campaign");?>
    </TD>
    <TD>
      <SELECT NAME=id>
        <OPTION VALUE="">Add New Campaign (Fill In Bellow)</OPTION><?php
        for($ccnt=0;$ccnt<pg_num_rows($getcamp);$ccnt++) {
          list($cid,$cname)=pg_fetch_array($getcamp,$ccnt);?>
          <OPTION VALUE="<?php print $cid;?>"><?php print $cname?></OPTION><?php
        }?>
    </TD>
  </TR><?php
} else {?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TH COLSPAN=2 CLASS=heading-body>
      <?php print _("Editing Campaign") . " (" . $_POST['description'] . " - " . $_POST['name'] . ")";?>
    </TH>
  </TR><?php
}?>
<TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
  <TD WIDTH=50%>
    <?php print _("Priority Of Calls In This Campaign Compared To Other Campaigns");?>
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
    <?php print _("Description (Long Name)");?>
  </TD>
  <TD>
    <INPUT NAME=description VALUE="<?php print $_POST['description'];?>">
  </TD>
</TR>
<TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
  <TD>
    <?php print _("Name (Short Description)");?>
  </TD>
  <TD>
    <INPUT NAME=name VALUE="<?php print $_POST['name'];?>">
  </TD>
</TR>
<TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
  <TD>
    <?php print _("Campaign Is Active");?>
  </TD>
  <TD>
    <INPUT TYPE=CHECKBOX NAME=active<?php if ((!isset($_POST['active'])) || ($_POST['active'] == 't')) {print " CHECKED";}?>>
  </TD>
</TR>
<TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
  <TD COLSPAN=2 ALIGN=MIDDLE><?php
    if ((isset($_POST['upcamp'])) || (isset($_POST['editcamp']))) {?>
      <INPUT TYPE=SUBMIT onclick=this.name='upcamp' VALUE="<?php print _("Update");?>"><?php
    } else {?>
      <INPUT TYPE=SUBMIT onclick=this.name='delcamp' VALUE="<?php print _("Delete")?>">
      <INPUT TYPE=SUBMIT onclick=this.name='editcamp' VALUE="<?php print _("Edit/Add")?>"><?php
    }?>
  </TD>
</TR>
</TABLE>
</TABLE>
</FORM>
