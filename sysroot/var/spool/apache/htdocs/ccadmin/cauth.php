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
<FORM METHOD=POST NAME=cauthf onsubmit="ajaxsubmit(this.name);return false">
<CENTER>
<TABLE border=0 width=90% cellspacing=0 cellpadding=0>
<?php

if ($_POST['active'] == "on") {
  $_POST['active']='t';
} else if (isset($_POST['active'])) {
  $_POST['active']='f';
}

if ((isset($_POST['id'])) && ($_POST['id'] != "")) {
  $_SESSION['campid']=$_POST['id'];
  $getcamp=pg_query($db,"SELECT description||' ('||name||')' FROM campaign LEFT OUTER JOIN camp_admin ON (campaign.id=camp_admin.campaign AND camp_admin.userid='" . $_SERVER['PHP_AUTH_USER'] . "') WHERE id=" .  $_SESSION['campid']);
  list($_SESSION['campname'])=pg_fetch_array($getcamp,0);
} else if ((isset($_POST['upcamp'])) && ($_SESSION['campid'] != "")) {
  $getadmin=pg_query($db,"SELECT userid FROM camp_admin WHERE campaign=" . $_SESSION['campid']);
  for($dcnt=0;$dcnt< pg_num_rows($getadmin);$dcnt++) {
    list($euid)=pg_fetch_array($getadmin,$dcnt);
    $todel="del" . $euid;
    if (isset($_POST[$todel])) {
      pg_query($db,"DELETE FROM camp_admin WHERE campaign=" . $_SESSION['campid'] . " AND userid='" . $euid . "'");
    }
  }
  if ((isset($_POST['newadmin'])) && ($_POST['newadmin'] != "")) {
    pg_query($db,"INSERT INTO camp_admin VALUES (" . $_SESSION['campid'] . ",'" . $_POST['newadmin'] . "')");
  }
}

if ((!isset($_POST['upcamp'])) && (!isset($_POST['id']))) {
  $getcamp=pg_query($db,"SELECT id,description||' ('||name||')' FROM campaign LEFT OUTER JOIN camp_admin ON (campaign.id=camp_admin.campaign AND camp_admin.userid='" . $_SERVER['PHP_AUTH_USER'] . "')" . $_SESSION['limitadmin'] . " ORDER by description,name");?>
<TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
  <TH COLSPAN=2 CLASS=heading-body>
    <?php print _("Select A Campaign To Modify");?>
  </TH>
</TR>
<TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
  <TD WIDTH=50%>
    <?php print _("Select Campaign");?>
  </TD>
  <TD>
    <SELECT NAME=id onchange=ajaxsubmit(this.form.name)>
      <OPTION VALUE=""></OPTION>
      <?php
      for($ccnt=0;$ccnt<pg_num_rows($getcamp);$ccnt++) {
        list($cid,$cname)=pg_fetch_array($getcamp,$ccnt);?>
        <OPTION VALUE="<?php print $cid;?>"><?php print $cname?></OPTION><?php
      }?>
  </TD>
</TR>
<?php
} else {
  $getadmin=pg_query($db,"SELECT userid FROM camp_admin WHERE campaign=" . $_SESSION['campid'] . " ORDER by userid");?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TH COLSPAN=2 CLASS=heading-body>
      <?php print _("Campaign Administrators") . " " . $_SESSION['campname'];?>
    </TH>
  </TR><?php
  $admincnt=pg_num_rows($getadmin);
  if ($admincnt > 0) {?>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TH ALIGN=LEFT WIDTH=25% CLASS=heading-body2>
        Del.
      </TH>
      <TH ALIGN=LEFT CLASS=heading-body2>
        Username
      </TH>
    </TR><?php
    for($ucnt=0;$ucnt<$admincnt;$ucnt++) {
      list($auser)=pg_fetch_array($getadmin);?>
      <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
        <TD>
          <INPUT TYPE=CHECKBOX NAME="del<?php print $auser;?>">
        </TD>
        <TD>
          <?php print $auser;?>
        </TD>
      </TR><?php
    }
  }?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD WIDTH=50%>
      <?php print _("Add Admin");?>
    </TD>
    <TD>
      <INPUT TYPE=TEXT NAME=newadmin autocomplete="off" SIZE=50>
    </TD>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD COLSPAN=2 ALIGN=MIDDLE>
      <INPUT TYPE=SUBMIT NAME=upcamp VALUE="<?php print _("Modify");?>">
    </TD>
  </TR>
  <INPUT TYPE=HIDDEN NAME=type VALUE=in>
  <INPUT TYPE=HIDDEN NAME=what VALUE=uid>
  <INPUT TYPE=HIDDEN NAME=baseou VALUE=system>
  <INPUT TYPE=HIDDEN NAME=utype VALUE=system>
<?php
}
?>
</TABLE>
</TABLE>
</FORM>
<script>
var ldappop=new TextComplete(document.cauthf.newadmin,ldapautodata,'/auth/uidxml.php',setldappopurl,document.cauthf,ldappop);
</script>
