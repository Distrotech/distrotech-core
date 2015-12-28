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
} else if (isset($_POST['active'])) {
  $_POST['active']='f';
}

if ((isset($_POST['id'])) && (!isset($_POST['listid'])) && (!isset($_POST['updb']))) {
  $getid=pg_query($db,"SELECT id,description||' ('||name||')' FROM campaign LEFT OUTER JOIN camp_admin ON (campaign.id=camp_admin.campaign AND camp_admin.userid='" . $_SERVER['PHP_AUTH_USER'] . "') WHERE id=" .  $_POST['id']);
  list($_SESSION['campid'],$_SESSION['campname'])=pg_fetch_array($getid,0);
} else if (($_SESSION['campid'] != "") && ($_POST['listid'] != "") && (!isset($_POST['updb']))) {
  $_SESSION['listid']=$_POST['listid'];
} else if (($_SESSION['campid'] != "") && ($_SESSION['listid'] != "") && (isset($_POST['updb']))) {
  if ($_POST['addagent'] != "") {
    $isagent=pg_query("SELECT id FROM agent WHERE exten='"  . $_POST['addagent'] . "'");
    if (pg_num_rows($isagent) <= 0) {
      pg_query($db,"INSERT INTO agent (exten) VALUES ('" . $_POST['addagent'] . "')");
      $isagent=pg_query("SELECT id FROM agent WHERE exten='"  . $_POST['addagent'] . "'");
    }
    if (pg_num_rows($isagent) >= 0) {
      list($agentid)=pg_fetch_array($isagent,0,PGSQL_NUM);
      pg_query($db,"INSERT INTO agentlist (listid,agentid) VALUES (" . $_SESSION['listid'] . "," . $agentid . ")");
    }
  } else if ($_POST['agentlogon'] != "") {
    pg_query($db,"UPDATE agentlist SET active='t' FROM agent WHERE agent.exten='" . $_POST['agentlogon'] . "' AND agent.id=agentlist.agentid AND agentlist.listid=" . $_SESSION['listid']);
  } else if ($_POST['agentlogoff'] != "") {
    pg_query($db,"UPDATE agentlist SET active='f' FROM agent WHERE agent.exten='" . $_POST['agentlogoff'] . "' AND agent.id=agentlist.agentid AND agentlist.listid=" . $_SESSION['listid']);
  }
}

?>
<FORM NAME=agentadmin METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=agentlogon VALUE="">
<INPUT TYPE=HIDDEN NAME=agentlogoff VALUE="">
<CENTER>
<TABLE border=0 width=90% cellspacing=0 cellpadding=0>
<?php

if ((!isset($_POST['id'])) && (!isset($_POST['listid'])) && (!isset($_POST['updb']))) {
  unset($_SESSION['campid']);
  unset($_SESSION['listid']);
  $getcamp=pg_query($db,"SELECT id,description||' ('||name||')' FROM campaign LEFT OUTER JOIN camp_admin ON (campaign.id=camp_admin.campaign AND camp_admin.userid='" . $_SERVER['PHP_AUTH_USER'] . "')" . $_SESSION['limitadmin'] . " ORDER by description,name");?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TH COLSPAN=2 CLASS=heading-body>
      <?php print _("Select A Campaign To Assign To");?>
    </TH>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD WIDTH=50%>
      <?php print _("Select Campaign To Configure Agents");?>
    </TD>
    <TD>
      <SELECT NAME=id onchange=ajaxsubmit(this.form.name)>
        <OPTION VALUE=""></OPTION><?php
        for($ccnt=0;$ccnt<pg_num_rows($getcamp);$ccnt++) {
          list($cid,$cname)=pg_fetch_array($getcamp,$ccnt);?>
          <OPTION VALUE="<?php print $cid;?>"><?php print $cname?></OPTION><?php
        }?>
    </TD>
  </TR><?php
} else {
  if ((isset($_SESSION['campid'])) && (!isset($_SESSION['listid']))) {
    $getlist=pg_query($db,"SELECT id,description FROM list WHERE campaign=" . $_SESSION['campid'] . "ORDER by description");?>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TH COLSPAN=2 CLASS=heading-body>
        <?php print _("Select List To Assign Agents To") . " " . $_SESSION['campname'];?>
      </TH>
    </TR>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TD WIDTH=50%>
        <?php print _("Select List");?>
      </TD>
      <TD>
        <SELECT NAME=listid onchange=ajaxsubmit(this.form.name)>
          <OPTION VALUE=""></OPTION><?php
          for($ccnt=0;$ccnt<pg_num_rows($getlist);$ccnt++) {
            list($cid,$cname)=pg_fetch_array($getlist,$ccnt);?>
            <OPTION VALUE="<?php print $cid;?>"><?php print $cname?></OPTION><?php
          }?>
      </TD>
    </TR><?php
  } else {?>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
      <TH COLSPAN=2 CLASS=heading-body>
         <?php print _("Assigning Agents To ") . " " . $_SESSION['listname'] . " " . _("List For Campaign") . " " . $_SESSION['campname'];?>
      </TH>
    </TR><?php
    $cagentqq="SELECT agentlist.active,fullname||' ('||name||')',name,agentlist.listid FROM agent LEFT OUTER JOIN agentlist ON (agentlist.agentid=agent.id) LEFT OUTER JOIN users ON (exten=name) WHERE users.uniqueid IS NOT NULL AND agentlist.listid=" . $_SESSION['listid'] . " ORDER BY fullname";
    $cagentq=pg_query($db,$cagentqq);
    if (pg_num_rows($cagentq) > 0) {?>
      <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
        <TH CLASS=heading-body2 ALIGN=LEFT>Active</TH>
        <TH CLASS=heading-body2 ALIGN=LEFT>Agent</TH>
      </TR><?php
    }
    for($acnt=0;$acnt<pg_num_rows($cagentq);$acnt++) {
      $agdat=pg_fetch_array($cagentq,$acnt,PGSQL_NUM);?>
        <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
          <TD>
            <INPUT TYPE=BUTTON VALUE="" CLASS="option-<?php print ($agdat[0] == "t")?"green":"red";?>" onclick=ccagentonoff('<?php print $agdat[2] . "','" . $agdat[0];?>')>
          </TD><TD>
            <?php print $agdat[1];?>
          </TD>
          </TR><?php
    }
    $allagq=pg_query($db,"SELECT name,fullname||' ('||name||')' FROM users LEFT OUTER JOIN agent ON (exten=name) LEFT OUTER JOIN agentlist ON (agentlist.agentid=agent.id AND agentlist.listid=" . $_SESSION['listid'] . ") WHERE name ~ '^[0-9]{4}$' AND agentlist.listid IS NULL ORDER BY fullname");
    if (pg_num_rows($allagq) > 0) {?>
      <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
        <TH CLASS=heading-body2 COLSPAN=2>Add Agent To Campaign</TH>
      </TR>
      <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
        <TD WIDTH=15%>Select Agent</TD>
        <TD>
          <SELECT NAME=addagent>
            <OPTION VALUE=""></OPTION><?php
            for($acnt=0;$acnt<pg_num_rows($allagq);$acnt++) {
              $agdat=pg_fetch_array($allagq,$acnt,PGSQL_NUM);
              print "<OPTION VALUE=\"" . $agdat[0] . "\">" . $agdat[1] . "</OPTION>\n";
            }?>
          </SELECT>
      </TR>
      <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
        <TD COLSPAN=2 ALIGN=MIDDLE>
          <INPUT TYPE=SUBMIT NAME=updb VALUE="<?php print _("Update");?>">
        </TD>
      </TR>
  <?php } else {
      print "<INPUT TYPE=HIDDEN NAME=updb VALUE=1>\n";
    }
  }
}
?>
</TABLE>
</FORM>

