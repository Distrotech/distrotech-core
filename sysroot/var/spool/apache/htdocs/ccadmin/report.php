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

if ((isset($_POST['id'])) && (!isset($_POST['listid']))) {
  $getid=pg_query($db,"SELECT id,description||' ('||name||')' FROM campaign LEFT OUTER JOIN camp_admin ON (campaign.id=camp_admin.campaign AND camp_admin.userid='" . $_SERVER['PHP_AUTH_USER'] . "') WHERE id=" .  $_POST['id']);
  list($_SESSION['campid'],$_SESSION['campname'])=pg_fetch_array($getid,0);
} else if ((isset($_SESSION['campid'])) && (isset($_POST['listid']))) {
  $datain_tb=strtolower("inputdata_" . $_SESSION['campid'] . "_" . $_POST['listid']);
  $_SESSION['listid']=$_POST['listid'];
}

//print "<PRE>" . print_r($_POST,TRUE) . "</PRE>";
//print "<PRE>" . print_r($_SESSION,TRUE) . "</PRE>";

?>
<FORM NAME=ladmin METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<CENTER>
<TABLE border=0 width=90% cellspacing=0 cellpadding=0>
<?php

if ((!isset($_POST['id'])) && (!isset($_POST['listid'])) && (!isset($_POST['period']))) {
  unset($_SESSION['campid']);
  unset($_SESSION['listid']);
  $getcamp=pg_query($db,"SELECT id,description||' ('||name||')' FROM campaign LEFT OUTER JOIN camp_admin ON (campaign.id=camp_admin.campaign AND camp_admin.userid='" . $_SERVER['PHP_AUTH_USER'] . "')" . $_SESSION['limitadmin'] . " ORDER by description,name");?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TH COLSPAN=2 CLASS=heading-body>
      <?php print _("Select A Campaign To Report On");?>
    </TH>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD WIDTH=50%>
      <?php print _("Select Campaign To Configure Lists");?>
    </TD>
    <TD WIDTH=50%>
      <SELECT NAME=id onchange=ajaxsubmit(this.form.name)>
        <OPTION VALUE=""></OPTION><?php
        for($ccnt=0;$ccnt<pg_num_rows($getcamp);$ccnt++) {
          list($cid,$cname)=pg_fetch_array($getcamp,$ccnt);?>
          <OPTION VALUE="<?php print $cid;?>"><?php print $cname?></OPTION><?php
        }?>
    </TD>
  </TR><?php
} else if ((isset($_SESSION['campid'])) && (!isset($_SESSION['listid']))) {
  $getlist=pg_query($db,"SELECT id,description FROM list WHERE campaign=" . $_SESSION['campid'] . "ORDER by description");?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TH COLSPAN=2 CLASS=heading-body>
      <?php print _("Select List To Report On From Campaign") . " " . $_SESSION['campname'];?>
    </TH>
  </TR>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD WIDTH=50%>
      <?php print _("Select List");?>
    </TD>
    <TD WIDTH=50%>
      <SELECT NAME=listid onchange=ajaxsubmit(this.form.name)>
        <OPTION VALUE=""></OPTION><?php
        for($ccnt=0;$ccnt<pg_num_rows($getlist);$ccnt++) {
          list($cid,$cname)=pg_fetch_array($getlist,$ccnt);?>
          <OPTION VALUE="<?php print $cid;?>"><?php print $cname?></OPTION><?php
        }?>
    </TD>
  </TR><?php
} else if ((isset($_SESSION['campid'])) && (isset($_SESSION['listid'])) && (isset($_POST['period']))) {
  include_once "/var/spool/apache/htdocs/cdr/func.inc";

  $headinf=array("Date/Time","Duration","Dialed Num","Disposition","Status","F. Up","Notes","Title","First Name","Last Name","Number");


  $mfcnt=count($headinf);
  $datain_tb=strtolower("inputdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid']);
  $dataout_tb=strtolower("contactdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid']);
  $getcolname="SELECT data_type,column_name,CASE WHEN (table_name = '" . $datain_tb . "') THEN 'idata' ELSE 'cdata' END,CASE WHEN (field_names.fname IS NOT NULL) THEN  field_names.fname ELSE column_name END from information_schema.columns left outer join field_names on (tablename=table_name AND field=column_name) where table_catalog='asterisk' and (table_name='" . $datain_tb . "' OR table_name='" . $dataout_tb . "') AND (column_name != 'contid' AND column_name != 'custid' AND column_name != 'leadid' AND column_name != 'id' AND column_name != 'osticket') order by table_name DESC,information_schema.columns.column_name";
  $testdbtbl=pg_query($db,$getcolname);
  $ifields="";
  for($idtrcnt=0;$idtrcnt < pg_num_rows($testdbtbl);$idtrcnt++) {
    list($trowt,$trowcn,$trowtn,$trdescrip)=pg_fetch_array($testdbtbl,$idtrcnt);
    $toshow="show_" . $trowcn;
    if ($_POST[$toshow] == "on") {
      $xfields.="," . $trowtn . "." . $trowcn;
      array_push($headinf,$trdescrip);
      if ($trowt == "boolean") {
        $isyesno[$mfcnt]=true;
      }
      $mfcnt++;
    }
  }

  $filter="";
  if ($_POST['status'] != "") {
    $filter="contact.status = '" . $_POST['status'] . "'";
  }

  if ($_POST['followup'] != "") {
    if ($filter != "" ) {
      $filter.=" AND ";
    }
    $filter.="contact.followup = '" . $_POST['followup'] . "'";
  }

  if ($_POST['validcdr'] != "") {
    if ($filter != "" ) {
      $filter.=" AND ";
    }
    $filter.="cdr.uniqueid IS NOT NULL";
  }

  if ($_POST['disposition'] != "") {
    if ($filter != "" ) {
      $filter.=" AND ";
    }
    $filter.="cdr.disposition = '" . $_POST['disposition'] . "'";
  }

  if ($filter != "") {
    $filter=" AND (" . $filter . ")";
  }

  $time="(contact.datetime > '" . $month[1] . "-" . $month[0] . "-" . $month[2] . "' AND contact.datetime < '" . $month2[1] . "-" . $month2[0] . "-" . $month2[2] . "')";
  $rquery="SELECT date_trunc('seconds',contact.datetime),cdr.billsec,cdr.src,cdr.disposition,contact.status,contact.followup,contact.feedback,lead.title,lead.fname,lead.sname,lead.number" . $xfields . " from lead left outer join contact on (contact.lead = lead.id) left outer join cdr ON (contact.uniqueid=cdr.uniqueid) left outer join " . $dataout_tb . " as cdata on (cdata.contid = contact.id) left outer join " . $datain_tb . " as idata on (idata.leadid=lead.id) WHERE " . $time . $filter . "  ORDER BY contact.datetime";
  $repq=pg_query($db,$rquery);
//  print $rquery;
  $dcspan=$odtrcnt+$idtrcnt;
  print "<TR" . $bcolor[$rcnt % 2] . ">";
  $rcnt++;
  print "<TH COLSPAN=" . count($headinf) . " CLASS=heading-body>" . _("Call Centre Report") . "</TH></TR>";
  print "<TR" . $bcolor[$rcnt % 2] . ">";
  $rcnt++;
    
  for($hicnt=0;$hicnt < count($headinf);$hicnt++) {
    print "<TH ALIGN=LEFT CLASS=heading-body2>" . $headinf[$hicnt] . "</TH>";
  }

  for($repr=0;$repr < pg_num_rows($repq);$repr++) {
    print "<TR" . $bcolor[$rcnt % 2] . ">";
    $rcnt++;
    $rdata=pg_fetch_array($repq,$repr,PGSQL_NUM);
    for ($fcnt=0;$fcnt < count($rdata);$fcnt++) {
      if (($fcnt == 5) || ($isyesno[$fcnt])) {
        $rdata[$fcnt]=($rdata[$fcnt] == 't')?"Yes":"No";
      }
      print "<TD>" . $rdata[$fcnt] . "</TD>";
    }
    print "</TR>";
  }
} else if ((isset($_SESSION['campid'])) && (isset($_SESSION['listid'])) && (!isset($_POST['period']))) {?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TH COLSPAN=2 CLASS=heading-body>
      <?php print _("Select Period For Report");?>
    </TH></TR><?php

  include_once "/var/spool/apache/htdocs/cdr/func.inc";
  $month=preg_split("/\//",$date);
  $getcdr=pg_query($db,"SELECT date_part('month',calldate) AS month,
                               date_part('year',calldate) AS year
                             from cdr where 
                               userfield != '' AND dstchannel != '' AND disposition='ANSWERED' AND dst != 's' AND
                               (length(accountcode) = 4 OR accountcode = '')
                             group by year,month
                             order by year,month");

  $amon=array();
  for($i=0;$i<pg_num_rows($getcdr);$i++) {
    $r = pg_fetch_row($getcdr, $i);
    if (($r[0] != "") && ($r[1] != "")) {
      array_push($amon,array('year'=>$r[1],'mon'=>$r[0]));
    }
  }
  $curdate=getdate();
  array_push($amon,array('year'=>$curdate['year'],'mon'=>$curdate['mon']+1));
?>
<TR CLASS=list-color1><TD WIDTH=50%>
  From Month
</TD><TD WIDTH=50%>
<SELECT NAME=dom>
<?php
  for($dcnt=1;$dcnt <= 31;$dcnt++) {
    print "<OPTION VALUE=" . $dcnt . ">" . $dcnt . "\n";
  }
?>
</SELECT>
<SELECT NAME=date><?php
  for($dcnt=0;$dcnt < count($amon);$dcnt++) {
    print "<OPTION VALUE=\"" . $amon[$dcnt]['mon'] . "/" . $amon[$dcnt]['year'] . "\"";
    if ((($dtime['year'] == $amon[$dcnt]['year']) && ($dtime['mon'] == $amon[$dcnt]['mon']) && (! isset($date))) ||
        (($month[0] == $amon[$dcnt]['year']) && ($amon[$dcnt]['mon'] == $r[1]) && (isset($date)))) {
      print " SELECTED";
      $mqset="1";
    } else if (($dtime['year'] == $amon[$dcnt]['year']) && ($dtime['mon'] == $amon[$dcnt]['mon']) && ($mqset != "1")) {
      print " SELECTED";
    }
    print ">" . $amon[$dcnt]['mon'] . "/" . $amon[$dcnt]['year'] . "\n"; 
  }
?>
</SELECT></TD></TR>
<TR CLASS=list-color2><TD WIDTH=50%>
  To Month
</TD><TD WIDTH=50%>
<SELECT NAME=dom2>
<?php
  for($dcnt=31;$dcnt > 0;$dcnt--) {
    print "<OPTION VALUE=" . $dcnt . ">" . $dcnt . "\n";
  }
?>
</SELECT>
<SELECT NAME=date2><?php
  for($dcnt=0;$dcnt < count($amon);$dcnt++) {
    print "<OPTION VALUE=\"" . $amon[$dcnt]['mon'] . "/" . $amon[$dcnt]['year'] . "\"";
    if ((($dtime['year'] == $amon[$dcnt]['year']) && ($dtime['mon'] == $amon[$dcnt]['mon']) && (! isset($date))) ||
        (($month[0] == $amon[$dcnt]['year']) && ($amon[$dcnt]['mon'] == $r[1]) && (isset($date)))) {
      print " SELECTED";
      $mqset="1";
    } else if (($dtime['year'] == $amon[$dcnt]['year']) && ($dtime['mon'] == $amon[$dcnt]['mon']) && ($mqset != "1")) {
      print " SELECTED";
    }
    print ">" . $amon[$dcnt]['mon'] . "/" . $amon[$dcnt]['year'] . "\n"; 
  }
?>
</SELECT></TD></TR><?php


  $squery="SELECT option FROM status WHERE campid='" . $_SESSION['campid'] . "' AND listid='" . $_SESSION['listid'] . "'";
  $querylst=pg_query($db,$squery);?>
  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>><TD>Match Status</TD><TD><SELECT NAME=status><OPTION VALUE="">Any</OPTION><?php

  for ($scnt=0;$scnt < pg_num_rows($querylst);$scnt++) {
    $sdata=pg_fetch_array($querylst,$scnt,PGSQL_NUM);
    print "<OPTION NAME=\"" . $sdata[0] . "\">" . $sdata[0] . "</OPTION>"; 
  }?>
  </SELECT>
  </TD></TR>

  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>><TD>Follow Up Status To Show</TD><TD><SELECT NAME=followup>
    <OPTION VALUE="">Any</OPTION>
    <OPTION VALUE="t">Yes</OPTION>
    <OPTION VALUE="f">No</OPTION>
  </SELECT>
  </TD></TR>

  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>><TD>Call Disposition</TD><TD><SELECT NAME=disposition>
    <OPTION VALUE="">Any</OPTION>
    <OPTION VALUE="ANSWERED">Answered</OPTION>
    <OPTION VALUE="NO ANSWER">No Answer</OPTION>
    <OPTION VALUE="BUSY">Busy</OPTION>
    <OPTION VALUE="FAILED">Failed</OPTION>
  </SELECT>
  </TD></TR>

  <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>><TD>Valid Calls Only</TD><TD>
  <INPUT TYPE=CHECKBOX NAME=validcdr>
  </TD></TR>
<?php

  $datain_tb=strtolower("inputdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid']);
  $dataout_tb=strtolower("contactdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid']);
  $getcolname="SELECT data_type,column_name,CASE WHEN (table_name = '" . $datain_tb . "') THEN 'idata' ELSE 'cdata' END,CASE WHEN (field_names.fname IS NOT NULL) THEN  field_names.fname ELSE column_name END from information_schema.columns left outer join field_names on (tablename=table_name AND field=column_name) where table_catalog='asterisk' and (table_name='" . $datain_tb . "' OR table_name='" . $dataout_tb . "') AND (column_name != 'contid' AND column_name != 'custid' AND column_name != 'leadid' AND column_name != 'id' AND column_name != 'osticket') order by table_name DESC,information_schema.columns.column_name";
  $testdbtbl=pg_query($db,$getcolname);
  $tfcnt=pg_num_rows($testdbtbl);

  if ($tfcnt > 0) {?>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>><TH COLSPAN=2 CLASS=heading-body2>Select Fields For Inclusion</TD></TR><?php
  }
  for($idtrcnt=0;$idtrcnt < $tfcnt;$idtrcnt++) {
    list($trowt,$trowcn,$trowtn,$trdescrip)=pg_fetch_array($testdbtbl,$idtrcnt);?>
    <TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>>
    <TD><INPUT TYPE=CHECKBOX NAME=show_<?php print $trowcn;?>></TD><TD><?php print $trdescrip;?></TD>
    </TR>
<?php
  }
?>
<TR<?php print $bcolor[$rcnt % 2];$rcnt++;?>><TD COLSPAN=2 ALIGN=MIDDLE>
<INPUT TYPE=SUBMIT NAME=period ="See Report">
</TD></TR><?php
}
?>
</TABLE>
</FORM>
