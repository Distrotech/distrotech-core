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

if ((isset($_POST['id'])) && ($_POST['id'] != "")) {
  $getid=pg_query($db,"SELECT id,description||' ('||name||')' FROM campaign LEFT OUTER JOIN camp_admin ON (campaign.id=camp_admin.campaign AND camp_admin.userid='" . $_SERVER['PHP_AUTH_USER'] . "') WHERE id=" .  $_POST['id']);
  list($_SESSION['campid'],$_SESSION['campname'])=pg_fetch_array($getid,0);
  print "<FORM NAME=ccleadf METHOD=POST onsubmit=\"ajaxsubmit(this.name);return false\">\n";
} else if ((isset($_POST['listid'])) && ($_SESSION['campid'] != "") && ($_POST['listid'] != "")) {
  $getid=pg_query($db,"SELECT id,description FROM list WHERE campaign=" .  $_SESSION['campid'] . " AND  id=" .  $_POST['listid']);
  list($_SESSION['listid'],$_SESSION['listname'])=pg_fetch_array($getid,0);
  print "<FORM ENCTYPE=\"multipart/form-data\" METHOD=POST>\n";
} else {
  print "<FORM NAME=ccleadf METHOD=POST onsubmit=\"ajaxsubmit(this.name);return false\">\n";
}

include "/var/spool/apache/htdocs/ccadmin/csvimport.inc";

?>
<CENTER>
<TABLE border=0 width=90% cellspacing=0 cellpadding=0>
<?php

if ((!isset($_POST['uplead'])) && (!isset($_POST['listid'])) && (!isset($_POST['id']))) {
  unset($_SESSION['campid']);
  unset($_SESSION['listid']);
  $getcamp=pg_query($db,"SELECT id,description||' ('||name||')' FROM campaign LEFT OUTER JOIN camp_admin ON (campaign.id=camp_admin.campaign AND camp_admin.userid='" . $_SERVER['PHP_AUTH_USER'] . "')" . $_SESSION['limitadmin'] . " ORDER by description,name");?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TH COLSPAN=2 CLASS=heading-body>
      <?php print _("Select A Campaign");?>
    </TH>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD WIDTH=50%>
      <?php print _("Select Campaign To Configure Lead Lists");?>
    </TD>
    <TD>
      <SELECT NAME=id onchange="ajaxsubmit(this.form.name)">
        <OPTION VALUE=""></OPTION><?php
        for($ccnt=0;$ccnt<pg_num_rows($getcamp);$ccnt++) {
          list($cid,$cname)=pg_fetch_array($getcamp,$ccnt);?>
          <OPTION VALUE="<?php print $cid;?>"><?php print $cname?></OPTION><?php
        }?>
    </TD><?php
} else if ((!isset($_POST['uplead'])) && (!isset($_POST['listid'])) && (isset($_SESSION['campid']))) {
  unset($_SESSION['listid']);
  $getlist=pg_query($db,"SELECT id,description FROM list WHERE campaign=" .  $_SESSION['campid']);?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TH COLSPAN=2 CLASS=heading-body>
      <?php print _("Select A List To Add Leads To");?>
    </TH>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD WIDTH=50%>
      <?php print _("Select List To Configure Leads");?>
    </TD>
    <TD>
      <SELECT NAME=listid onchange="ajaxsubmit(this.form.name)">
        <OPTION VALUE=""></OPTION><?php
        for($ccnt=0;$ccnt<pg_num_rows($getlist);$ccnt++) {
          list($cid,$cname)=pg_fetch_array($getlist,$ccnt);?>
          <OPTION VALUE="<?php print $cid;?>"><?php print $cname?></OPTION><?php
        }?>
    </TD>
  </TR><?php
} else if ((!isset($_POST['uplead'])) && (isset($_SESSION['campid'])) && (isset($_SESSION['listid']))) {?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TH COLSPAN=2 CLASS=heading-body>
      <?php print _("Adding Leads To") . " " . $_SESSION['listname'] . " " . _("List For Campaign") . " " . $_SESSION['campname'];?>
    </TH>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD>
      <?php print _("Call After (HH:MM:SS)");?>
    </TD>
    <TD>
      <INPUT NAME=availfrom VALUE="08:00:00">
    </TD>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD>
      <?php print _("Call Before (HH:MM:SS)");?>
    </TD>
    <TD>
      <INPUT NAME=availtill VALUE="17:00:00">
    </TD>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD>
      <?php print _("Aditional Numbers");?>
    </TD>
    <TD>
      <SELECT NAME=addnum>
        <OPTION VALUE=0>0</OPTION>
        <OPTION VALUE=1>1</OPTION>
        <OPTION VALUE=2>2</OPTION>
        <OPTION VALUE=3>3</OPTION>
        <OPTION VALUE=4>4</OPTION>
      </SELECT>
    </TD>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD>
      <?php print _("CSV File (Title,First Name,Surname,Number....)");?>
    </TD>
    <TD>
      <INPUT TYPE=FILE NAME=leads>
    </TD>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TH COLSPAN=2 ALIGN=MIDDLE CLASS=heading-body2>
      Additional Fields Defined
    </TH>
  </TR><?php
  $data_tb=strtolower("inputdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid']);
  $testdbtbl=pg_query($db,"SELECT CASE WHEN (fname IS NOT NULL) THEN fname ELSE column_name END,data_type,character_maximum_length from information_schema.columns LEFT OUTER JOIN field_names ON (field_names.field = information_schema.columns.column_name) where table_catalog='asterisk' and table_name='" .$data_tb . "' AND (column_name != 'leadid' AND column_name != 'id' AND column_name != 'osticket')");
  for($dtrcnt=0;$dtrcnt < pg_num_rows($testdbtbl);$dtrcnt++) {
    list($trown,$trowt,$trows)=pg_fetch_array($testdbtbl,$dtrcnt);
    if ($trowt == "character varying") {
        $trowt.="(" . $trows . ")";
      }?>
      <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
        <TD>
          <?php print $trown;?>
        </TD>   
        <TD> 
          <?php print $trowt;?>
        </TD>
      </TR><?php
  }?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD COLSPAN=2 ALIGN=MIDDLE>
      <INPUT TYPE=SUBMIT NAME=uplead VALUE="<?php print _("Load File");?>">
    </TD>
  </TR><?php
} else {
  $tmpcsv=tempnam("/tmp","lead");
  $data_tb=strtolower("inputdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid']);

  $numeric=array('smallint','integer','bigint','numeric','real','double precision');
  for($numcnt=0;$numcnt<count($numeric);$numcnt++) {
   $isnum[$numeric[$numcnt]]=true;
  }

  if (move_uploaded_file($_FILES['leads']['tmp_name'],$tmpcsv)) {?>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>><TH CLASS=heading-body><?php
    print _("Adding Leads To") . " " . _("Campaign") . " " . $_SESSION['campname'] . " [" . _("List") . " " . $_SESSION['listname'] . "]";
    print "</TH></TR>";

    valicsv($tmpcsv, $data_tb, 4);

    $testdbtbl=pg_query($db,"SELECT column_name,data_type,character_maximum_length from information_schema.columns where table_catalog='asterisk' and table_name='" . $data_tb . "' AND (column_name != 'leadid' AND column_name != 'osticket' AND column_name != 'id')");
    if (pg_num_rows($testdbtbl) > 0) {
      for($dtrcnt=0;$dtrcnt < pg_num_rows($testdbtbl);$dtrcnt++) {
        list($trown,$trowt,$trows)=pg_fetch_array($testdbtbl,$dtrcnt);
        $arrtype[$dtrcnt]=$trowt;
        $arrname[$dtrcnt]=$trown;
        $arrsize[$dtrcnt]=$trows;
      }
    }

    $csvfd = fopen($tmpcsv, "r");
    $head = fgetcsv($csvfd, 0, ",");

    while (($csvlead = fgetcsv($csvfd, 0, ",")) !== FALSE) {?>
      <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>><TD>Adding <?php
      $newlead=array(array_shift($csvlead),array_shift($csvlead),array_shift($csvlead),array_shift($csvlead));

      for($nlcnt=0;$nlcnt < count($newlead);$nlcnt++) {
        $newlead[$nlcnt]=pg_escape_string($db,$newlead[$nlcnt]);
      }

      $altnum="";
      $altnumv="";
      for($enumcnt=1;$enumcnt <= $_POST['addnum'];$enumcnt++) {
        $altnum.=",number" . $enumcnt;
        $tmpnum=pg_escape_string($db,array_shift($csvlead));
        $altnumv.=",'" . $tmpnum . "'";
        array_push($newlead,$tmpnum);
      }

      print $newlead[0] . " " . $newlead[1] . " " . $newlead[2] . " (" . $newlead[3] . ")";

      $qfields="";
      $qvals="";
      for($csva=0;$csva < count($csvlead);$csva++) {
        if ($isnum[$arrtype[$csva]]) {
          $qfields.="," . $arrname[$csva];
          if (($csvlead[$csva] > 0) || ($csvlead[$csva] < 0)) {
            $qvals.="," . $csvlead[$csva];
          } else {
            $qvals.=",0";
          }
        } else if ($arrtype[$csva] == "character varying") {
          $qvals.=",'" . pg_escape_string($db,substr($csvlead[$csva],0,$arrsize[$csva])) . "'";
          $qfields.="," . $arrname[$csva];
        } else if ($arrtype[$csva] == "text") {
          $qvals.=",'" . pg_escape_bytea($db,$csvlead[$csva]) . "'";
          $qfields.="," . $arrname[$csva];
        } else if ($arrtype[$csva] == "timestamp with time zone") {
          if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}:[0-9]{2}:[0-9]{2})([-\+0-9]+)$/", $csvlead[$csva], $dateinf)) {
            if (checkdate($dateinf[2],$dateinf[3],$dateinf[1])) {
              $qfields.="," . $arrname[$csva];
              $qvals.=",'" . $csvlead[$csva] . "'";
            }
          } else if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}:[0-9]{2}:[0-9]{2})$/", $csvlead[$csva], $dateinf)) {
            if (checkdate($dateinf[2],$dateinf[3],$dateinf[1])) {
              $qfields.="," . $arrname[$csva];
              $qvals.=",'" . $csvlead[$csva] . "'";
            }
          }
        } else if ($arrtype[$csva] == "timestamp without time zone") {
          if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}:[0-9]{2}:[0-9]{2})$/", $csvlead[$csva], $dateinf)) {
            if (checkdate($dateinf[2],$dateinf[3],$dateinf[1])) {
              $qfields.="," . $arrname[$csva];
              $qvals.=",'" . $csvlead[$csva] . "'";
            }
          }
        } else if ($arrtype[$csva] == "time with time zone") {
          if (preg_match("/^([0-9]{2}:[0-9]{2}:[0-9]{2})([-\+0-9]+)$/", $csvlead[$csva], $dateinf)) {
            $qfields.="," . $arrname[$csva];
            $qvals.=",'" . $csvlead[$csva] . "'";
          } else if (preg_match("/^([0-9]{2}:[0-9]{2}:[0-9]{2})$/", $csvlead[$csva], $dateinf)) {
            $qfields.="," . $arrname[$csva];
            $qvals.=",'" . $csvlead[$csva] . "'";
          }
        } else if ($arrtype[$csva] == "time without time zone") {
          if (preg_match("/^([0-9]{2}:[0-9]{2}:[0-9]{2})$/", $csvlead[$csva], $dateinf)) {
            $qfields.="," . $arrname[$csva];
            $qvals.=",'" . $csvlead[$csva] . "'";
          }
        } else if ($arrtype[$csva] == "boolean") {
          $csvlead[$csva]=strtolower($csvlead[$csva]);
          if (($csvlead[$csva] == "yes") || ($csvlead[$csva] == "y") || ($csvlead[$csva] == "1") || ($csvlead[$csva] == "t") || ($csvlead[$csva] == "on")) {
            $csvlead[$csva]='t';
          } else {
            $csvlead[$csva]='f';
          }
          $qfields.="," . $arrname[$csva];
          $qvals.=",'" . $csvlead[$csva] . "'";
        } else {
          $qfields.="," . $arrname[$csva];
          $qvals.=",'" . $csvlead[$csva] . "'";
        }
      }
      $qvals=substr($qvals,1);
      $qfields=substr($qfields,1);
      pg_query($db,"INSERT INTO lead (list,availfrom,availtill,title,fname,sname,number" . $altnum . ") VALUES (" . $_SESSION['listid'] . ",'" . $_POST['availfrom'] . "','" . $_POST['availtill'] . "','" . $newlead[0] . "','" . $newlead[1] . "','" . $newlead[2] . "','" . $newlead[3] . "'" . $altnumv . ")");
      $ldata=pg_query("SELECT id from lead WHERE list=" . $_SESSION['listid'] . " AND title='" . $newlead[0] . "' AND fname='" . $newlead[1] . "' AND sname='" . $newlead[2] . "' AND number='" . $newlead[3] . "'");
      list($leadid)=pg_fetch_array($ldata,0);
      if (($leadid > 0) && ($qvals != "")){
        pg_query("INSERT INTO " .  $data_tb . " (leadid," . $qfields . ") VALUES (" . $leadid . "," . $qvals . ")");
        print "INSERT INTO " .  $data_tb . " (leadid," . $qfields . ") VALUES (" . $leadid . "," . $qvals . ");<P>";
      }
?>
      </TD></TR><?php
    }
  }
  unlink($tmpcsv);
}

?>
</TABLE>
</TABLE>
</FORM>
