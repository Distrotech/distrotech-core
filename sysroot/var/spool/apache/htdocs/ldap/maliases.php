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
  if (! $rdn) {
    include "auth.inc";
  }
?>
<CENTER>
<FORM METHOD=POST NAME=aliasesform onsubmit="ajaxsubmit(this.name);return false">
<table border="0" width="90%" cellspacing="0" cellpadding="0">
<?php
$abdn="ou=Email";

if (($acount == "1") && ($aliasedit == "Modify") && ($aliasmod == _("Delete")) && ($alias != "root")) {
  $aliasedit=_("Delete");
}

if (($aliasedit == _("Delete")) && ($alias != "")){
  $domlist=explode("-",$alias);

  if ($domlist[1] == "admin") {
    ldap_delete($ds,"sendmailMTAKey=$domlist[0]-list,ou=Email");
    ldap_delete($ds,"sendmailMTAKey=$domlist[0]-members,ou=Email");
    ldap_delete($ds,"sendmailMTAKey=owner-$domlist[0]-list,ou=Email");
    ldap_delete($ds,"sendmailMTAKey=$domlist[0]-list-approval,ou=Email");
    ldap_delete($ds,"sendmailMTAKey=$domlist[0]-list-request,ou=Email");
  } 
  if ($alias != "root") {
    ldap_delete($ds,"sendmailMTAKey=$alias,ou=Email");
  }
} else if ((isset($aliasedit)) && ($newalias != "") && ($firstentry != "")) {
  if ($atype == "pubbox") {
    $addent=array();
    $addent["objectclass"][0]="sendmailMTA";
    $addent["objectclass"][1]="sendmailMTAAlias";
    $addent["objectclass"][2]="sendmailMTAAliasObject";
    $addent["sendmailmtaaliasgrouping"][0]="aliases";
    $addent["sendmailmtacluster"][0]="AllServers";  
    $addent["sendmailmtakey"][0]=$newalias;
    $addent["sendmailmtaaliasvalue"][0]="pubbox";
    $addent["Description"]=$firstentry . ":users";
    ldap_add($ds,"sendmailMTAKey=$newalias,ou=Email",$addent);
    $alias=$newalias;
    $aliasedit="Modify";
  } else if ($atype == "domo") {
    $addent=array();
    $addent["objectclass"][0]="sendmailMTA";
    $addent["objectclass"][1]="sendmailMTAAlias";
    $addent["objectclass"][2]="sendmailMTAAliasObject";
    $addent["sendmailmtaaliasgrouping"][0]="aliases";
    $addent["sendmailmtacluster"][0]="AllServers";  
    $addent["sendmailmtakey"][0]="$newalias-list";
    $addent["sendmailmtaaliasvalue"][0]="\"|/opt/majordomo/wrapper resend -h $LOCAL_DOMAIN -l $newalias-list $newalias-members\"";
    ldap_add($ds,"sendmailMTAKey=$newalias-list,ou=Email",$addent);

    $addent=array();
    $addent["objectclass"][0]="sendmailMTA";
    $addent["objectclass"][1]="sendmailMTAAlias";
    $addent["objectclass"][2]="sendmailMTAAliasObject";
    $addent["sendmailmtaaliasgrouping"][0]="aliases";
    $addent["sendmailmtacluster"][0]="AllServers";  
    $addent["sendmailmtakey"][0]="$newalias-members";
    $addent["sendmailmtaaliasvalue"][0]=":include:/opt/majordomo/lists/$newalias-list";
    ldap_add($ds,"sendmailMTAKey=$newalias-members,ou=Email",$addent);

    $addent=array();
    $addent["objectclass"][0]="sendmailMTA";
    $addent["objectclass"][1]="sendmailMTAAlias";
    $addent["objectclass"][2]="sendmailMTAAliasObject";
    $addent["sendmailmtaaliasgrouping"][0]="aliases";
    $addent["sendmailmtacluster"][0]="AllServers";  
    $addent["sendmailmtakey"][0]="owner-$newalias-list";
    $addent["sendmailmtaaliasvalue"][0]="$newalias-admin";
    ldap_add($ds,"sendmailMTAKey=owner-$newalias-list,ou=Email",$addent);

    $addent=array();
    $addent["objectclass"][0]="sendmailMTA";
    $addent["objectclass"][1]="sendmailMTAAlias";
    $addent["objectclass"][2]="sendmailMTAAliasObject";
    $addent["sendmailmtaaliasgrouping"][0]="aliases";
    $addent["sendmailmtacluster"][0]="AllServers";  
    $addent["sendmailmtakey"][0]="$newalias-list-approval";
    $addent["sendmailmtaaliasvalue"][0]="$newalias-admin";
    ldap_add($ds,"sendmailMTAKey=$newalias-list-approval,ou=Email",$addent);

    $addent=array();
    $addent["objectclass"][0]="sendmailMTA";
    $addent["objectclass"][1]="sendmailMTAAlias";
    $addent["objectclass"][2]="sendmailMTAAliasObject";
    $addent["sendmailmtaaliasgrouping"][0]="aliases";
    $addent["sendmailmtacluster"][0]="AllServers";  
    $addent["sendmailmtakey"][0]="$newalias-list-request";
    $addent["sendmailmtaaliasvalue"][0]="\"|/opt/majordomo/wrapper majordomo -l $newalias-list\"";
    ldap_add($ds,"sendmailMTAKey=$newalias-list-request,ou=Email",$addent);

    $addent=array();
    $addent["objectclass"][0]="sendmailMTA";
    $addent["objectclass"][1]="sendmailMTAAlias";
    $addent["objectclass"][2]="sendmailMTAAliasObject";
    $addent["sendmailmtaaliasgrouping"][0]="aliases";
    $addent["sendmailmtacluster"][0]="AllServers";  
    $addent["sendmailmtakey"][0]="$newalias-admin";
    $addent["sendmailmtaaliasvalue"][0]=$firstentry;
    ldap_add($ds,"sendmailMTAKey=$newalias-admin,ou=Email",$addent);

?>
    <SCRIPT>
      alert("This Mailing List Needs To Be Configured\nSend A Message To majordomo with\nconfig <?php print $newalias . "-list " . $newalias . "-list.admin";?>\nIn The Message Body\nReturn The New Config In A Email Starting With\nnewconfig <?php print $newalias . "-list " . $newalias . "-list.admin";?>\nEnd The Config File With EOF On A Line By Itself");
    </SCRIPT>
<?php
    $alias="$newalias-admin";
    $aliasedit="Modify";
/*
    mail("majordomo", "Mailing List Configuration ($newalias-list)",
         "config " . $newalias . "-list " . $newalias . "-list.admin",
         "From: $firstentry\r\n"
        ."X-Mailer: PHP/" . phpversion());

    mail("$newalias-list-request", "Subscribe Request",
         "subscribe " . $newalias . "-list",
         "From: $firstentry\r\n"
        ."X-Mailer: PHP/" . phpversion());
*/
  } else {
    $addent=array();
    $addent["objectclass"][0]="sendmailMTA";
    $addent["objectclass"][1]="sendmailMTAAlias";
    $addent["objectclass"][2]="sendmailMTAAliasObject";
    $addent["sendmailmtaaliasgrouping"][0]="aliases";
    $addent["sendmailmtacluster"][0]="AllServers";  
    $addent["sendmailmtakey"][0]=$newalias;
    $addent["sendmailmtaaliasvalue"][0]=$firstentry;
    ldap_add($ds,"sendmailMTAKey=$newalias,ou=Email",$addent);
    $alias=$newalias;
    $aliasedit="Modify";
  }
} else if ($litealias == "Save") {
  ldap_delete($ds,"sendmailMTAKey=$alias,ou=Email");
  $addent=array();
  $addent["objectclass"][0]="sendmailMTA";
  $addent["objectclass"][1]="sendmailMTAAlias";
  $addent["objectclass"][2]="sendmailMTAAliasObject";
  $addent["sendmailmtaaliasgrouping"][0]="aliases";
  $addent["sendmailmtacluster"][0]="AllServers";  
  $addent["sendmailmtakey"][0]=$newalias;
  $addent["sendmailmtaaliasvalue"][0]=$firstentry;
  ldap_add($ds,"sendmailMTAKey=$newalias,ou=Email",$addent);
} else if (isset($aliasedit)) {
  $aliasedit="Modify";
}

if (($alias != "") && ($aliasedit == "Modify")){
  $sobj="(&(objectClass=sendmailMTAAlias)(sendmailMTAKey=$alias))";

  if ($aliasmod == _("Delete")) {
    $addent=array();
    $addent["sendmailmtaaliasvalue"]=${$alias};
    ldap_mod_del($ds,"sendmailMTAKey=$alias,ou=Email",$addent);
  } else if (($aliasmod == _("Add")) && ($add != "")) {
    $addent=array();
    $addent["sendmailmtaaliasvalue"]=$add;
    if (($alias == "root") && ($add == "pubbox")) {
      $addentm=array();
      $addentm["description"]="System Alerts/Admin Mail:smbadm";
      ldap_modify($ds,"sendmailMTAKey=$alias,ou=Email",$addentm);
    }
    ldap_mod_add($ds,"sendmailMTAKey=$alias,ou=Email",$addent);
    if (($alias == "root") && ($add != "root")) {
      $addentr=array();
      $addentr["sendmailmtaaliasvalue"]="root";
      ldap_mod_del($ds,"sendmailMTAKey=$alias,ou=Email",$addentr);
    }
  } else if (($aliasmod == "Save") && ($atype == "pubbox")) {
    $addent=array();
    $addent["description"]=$folder . ":" . $group;
    ldap_modify($ds,"sendmailMTAKey=$alias,ou=Email",$addent);
  }


  $sr=ldap_search($ds,"$abdn",$sobj);
  $info = ldap_get_entries($ds, $sr);
  $dn=$info[0]["dn"];  
  $allent=$info[0]["sendmailmtaaliasvalue"];
  print "<INPUT TYPE=HIDDEN NAME=acount VALUE=" . $allent["count"] . ">\n";
  unset($allent["count"]);

  if (($allent[0] != "pubbox") || ($alias == "root")) {
    sort($allent);
    reset($allent);
    print "<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>" . _("Adding Entries To") . " " . $alias . "</TH></TR>";
    print "<TR CLASS=list-color1><TD onmouseover=\"myHint.show('MA5')\" onmouseout=myHint.hide()>" . _("Select Entry To Delete") . "</TD>";
    print "<TD><SELECT NAME=\"" . $alias . "\">\n";

    for($dcnt=0;$dcnt < count($allent);$dcnt++) {
      print "<OPTION VALUE=\"" . $allent[$dcnt] . "\">" . $allent[$dcnt] . "\n";
    }

    print "</SELECT></TD></TR>\n";
    print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('MA6')\" onmouseout=myHint.hide()>Entry To Add</TD>";
    print "<TD><INPUT TYPE=TEXT NAME=add></TD></TR>";
    print "<TR CLASS=list-color1><TD COLSPAN=2 ALIGN=CENTER>\n";
    print "<INPUT TYPE=HIDDEN NAME=aliasedit VALUE=" . $aliasedit . ">\n";
    print "<INPUT TYPE=HIDDEN NAME=alias VALUE=\"" . $alias . "\">\n";
    print "<INPUT TYPE=SUBMIT onclick=this.name='aliasmod' VALUE=\"" . _("Add") . "\">\n";
    print "<INPUT TYPE=SUBMIT onclick=this.name='aliasmod' VALUE=\"" . _("Delete") . "\">\n";
  } else {
?>
<SCRIPT>
  alert("<?php print _("Changes To Public Mail Boxes Are Not Realtime \\nChanges Made May Only Reflect On The Hour");?>\n");
</SCRIPT>
<?php
    $mbinfo=explode(":",$info[0]["description"][0]);
    if ($mbinfo[1] == "") {
      $mbinfo[1]="users";
    }
    $srg=ldap_search($ds,"ou=Groups","(&(objectclass=posixgroup)(cn=*)(description=*))",array("cn","description"));
    $ginfo = ldap_get_entries($ds, $srg);
    $dn=$ginfo[0]["dn"];  

    $allgrp=array();
    for($gcnt=0;$gcnt < $ginfo["count"];$gcnt++) {
      $allgrp[$ginfo[$gcnt]["cn"][0]]=$ginfo[$gcnt]["description"][0];  
    }

    print "<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>" . _("Select The Group And Mail Box Name For") . " " . $alias . "</TH></TR>";
    print "<TR CLASS=list-color1><TD onmouseover=\"myHint.show('MA5')\" onmouseout=myHint.hide()>" . _("Select Access Group") . "</TD>";
    print "<TD><SELECT NAME=group>\n";

    asort($allgrp);
    while(list($gcn,$gname)=each($allgrp)) {
      print "<OPTION VALUE=\"" . $gcn . "\"";
      if ($gcn == $mbinfo[1]) {
        print " SELECTED";
      }
      print ">" . $gname . "\n";
    }

    print "</SELECT></TD></TR>\n";
    print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('MA6')\" onmouseout=myHint.hide()>" . _("Mail Box Name") . "</TD>";
    print "<TD><INPUT TYPE=TEXT NAME=folder VALUE=\"" . $mbinfo[0] . "\"></TD></TR>";
    print "<TR CLASS=list-color1><TD COLSPAN=2 ALIGN=CENTER>";
    print "<INPUT TYPE=HIDDEN NAME=alias VALUE=\"" . $alias . "\">\n";
    print "<INPUT TYPE=HIDDEN NAME=aliasedit VALUE=" . $aliasedit . ">\n";
    print "<INPUT TYPE=HIDDEN NAME=atype VALUE=pubbox>\n";
    print "<INPUT TYPE=SUBMIT onclick=this.name='aliasmod' VALUE=\"Save\">\n";
  }
} else {
  if (! file_exists("/etc/.networksentry-lite")) {
    $sobj="(objectClass=sendmailMTAAlias)";

    $sr=ldap_search($ds,"$abdn",$sobj);
    $info = ldap_get_entries($ds, $sr);

    for ($i=0; $i<$info["count"]; $i++) {
      $srsort[$i]=$info[$i]["sendmailmtakey"][0];
    }
    asort($srsort);
    reset ($srsort);
?>
   <TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body><?php print _("Email Aliases");?></TH></TR>
   <TR CLASS=list-color1><TD onmouseover="myHint.show('MA1')" onmouseout="myHint.hide()"><?php print _("Select Mailing List To Edit");?></TD><TD>
   <SELECT NAME=alias>
<?php
    while (list($i,$val) = each($srsort)) {
      $dn=$info[$i]["dn"];
      $cname=$info[$i]["sendmailmtakey"][0];
      $dscrip=explode(":",$info[$i]["description"][0]);
      if (!preg_match("/^owner-/",$cname) && !preg_match("/^owner-/",$cname) && 
          !preg_match("/-members\$/",$cname) && !preg_match("/-request\$/",$cname) && 
          !preg_match("/-list\$/",$cname) && !preg_match("/-approval\$/",$cname) &&
          ($cname != "majordomo") && ($cname != "FETCHMAIL-DAEMON") &&
          ($cname != "MAILER-DAEMON") && ($cname != "bin") && 
          ($cname != "daemon") && ($cname != "faxmaster") && 
          ($cname != "manager") && ($cname != "nobody") && 
          ($cname != "toor") && ($cname != "system") && ($cname != "pubbox") && 
          ($cname != "postmaster") && ($cname != "operator") && ($cname != "root")) {
        if ($dscrip[0] == "") {
          print "<OPTION VALUE=\"" . $cname . "\">" . $cname . "\n";
        } else {
          print "<OPTION VALUE=\"" . $cname . "\">" . $dscrip[0] . "\n";
        }
      } else if ($cname == "root") {
        print "<OPTION VALUE=\"" . $cname . "\">" . _("System Alerts/Admin Mail") . "\n";
      }
    }
    print "</SELECT></TD></TR>\n";
    print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('MA2')\" onmouseout=myHint.hide()>" . _("Name Of Mailing List") . "</TD><TD><INPUT TYPE=TEXT NAME=newalias></TD></TR>";
    print "<TR CLASS=list-color1><TD onmouseover=\"myHint.show('MA3')\" onmouseout=myHint.hide()>" . _("Initial Entry For Simple List") . " Or<BR>"  . _("Managers Address For Complex List") . " Or<BR>" . _("Mailbox Name For Public MailBox") . "</TD><TD>";
    print "<INPUT TYPE=TEXT NAME=firstentry></TD></TR>";
?>
    <TR CLASS=list-color2><TD onmouseover="myHint.show('MA4')" onmouseout=myHint.hide()><?php print _("List Type");?></TD><TD>
    <SELECT NAME=atype>
      <OPTION VALUE=""><?php print _("Simple List");?>
      <OPTION VALUE="pubbox"><?php print _("Public Mailbox");?>
      <OPTION VALUE="domo"><?php print _("Complex List");?>
    </SELECT></TD></TR>
    <TR CLASS=list-color1><TD COLSPAN=2 ALIGN=CENTER>
<?php
  } else {
    print "<INPUT TYPE=HIDDEN NAME=alias VALUE=root>";
    print "<INPUT TYPE=HIDDEN NAME=newalias VALUE=root>";
    print _("Email Address For") . "<BR>" . _("System Administration Mail");

    $sobj="(&(objectClass=sendmailMTAAlias)(sendmailMTAKey=root))";
    $sr=ldap_search($ds,"$abdn",$sobj);
    $info = ldap_get_entries($ds, $sr);
    $firstentry=$info[0]["sendmailmtaaliasvalue"][0];
    print "<BR><INPUT TYPE=TEXT NAME=firstentry VALUE=$firstentry>";
  }
 
  if (! file_exists("/etc/.networksentry-lite")) {
    print "<INPUT TYPE=SUBMIT onclick=this.name='aliasedit' VALUE=\"" . _("Modify/Add") . "\">\n";
  } else {
    print "<INPUT TYPE=SUBMIT onclick=this.name='litealias' VALUE=\"" . _("Save") . "\">\n";
  }
  print "<INPUT TYPE=SUBMIT onclick=this.name='aliasedit' VALUE=\"" . _("Delete") . "\">\n";
}
?>
  </TD></TR>
</table>
</FORM>

