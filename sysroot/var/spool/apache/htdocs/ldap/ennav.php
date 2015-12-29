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
/*
  if ($ds) {
    $sr=ldap_search($ds,"cn=Addressbooks","objectClass=groupofnames");
    $sinfo = ldap_get_entries($ds, $sr);
    for($mcnt=0;$mcnt<=$sinfo[0]["member"]["count"] -1;$mcnt++) {
      $dns=$sinfo[0]["member"][$mcnt];
      $dns=preg_split("/=/",$dns);
      $abookdn=$dns[1];
      if ($abookdn != "admin" ) {
        $dnact[$abookdn]=true;
        print "<OPTION VALUE=" . $abookdn . ">" . $abookdn . "</OPTION>\n";
      }
    }
  }
}*/

$usert['system']=_("System");
$usert['pdc']=_("PDC");
$usert['trust']=_("Trust Account");
$usert['server']=_("Server Account");
$usert['mserver']=_("Mail Server Account");
$usert['snom']=_("Snom Phonebook Entry");

if (($_SESSION['utype'] != "system") && ($_SESSION['utype'] != "pdc") && ($_SESSION['utype'] != "trust") && ($_SESSION['utype'] != "snom") && ($_SESSION['utype'] != "mserver")) {
  $_SESSION['classi']="system";
}

?>

<CENTER>
<FORM METHOD=POST NAME=ldapuform onsubmit="ajaxsubmit(this.name);return false">
<table border="0" width="90%" cellspacing="0" cellpadding="0">
  <INPUT TYPE=HIDDEN NAME=disppage VALUE="ldap/ldap.php">
  <INPUT TYPE=HIDDEN NAME=ldtype VALUE="abook">
  <INPUT TYPE=HIDDEN NAME=baseou VALUE="<?php print $_SESSION['classi'];?>">

  <tr CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body><?php print _("Search For") . " " . $usert[$_SESSION['classi']];?> User</TH></TR>
  <TR CLASS=list-color1><TD WIDTH=50% onmouseover="myHint.show('0')" onmouseout=myHint.hide()><?php print _("Search For") . " " . $usert[$baseou] . " " . _("Where");?>	...</TD><TD>
        <SELECT NAME=what><?php
          if ($_SESSION['classi'] == "mserver") {?>
            <OPTION VALUE=cn><?php print _("Host Name");?></OPTION><?php
          } else if ($_SESSION['classi'] == "trust") {?>
            <OPTION VALUE=cn><?php print _("IP Address");?></OPTION><?php
          } else {?>
            <OPTION VALUE=cn><?php print _("Common Name");?></OPTION><?php
          }
          if (($_SESSION['classi'] == "pdc") || ($_SESSION['classi'] == "system")) {?>
            <OPTION VALUE=uid<?php if ($what == "uid") {print " SELECTED";}?>><?php print _("Username");?></OPTION>
            <OPTION VALUE=givenname<?php if ($what == "givenname") {print " SELECTED";}?>><?php print _("First Name");?></OPTION>
            <OPTION VALUE=sn<?php if ($what == "sn") {print " SELECTED";}?>><?php print _("Last Name");?></OPTION>
            <OPTION VALUE=mail<?php if ($what == "mail") {print " SELECTED";}?>><?php print _("Email Address");?></OPTION>
            <OPTION VALUE=maillocaladdress<?php if ($what == "maillocaladdress") {print " SELECTED";}?>><?php print _("Email Alias")?></OPTION>
            <OPTION VALUE=o<?php if ($what == "o") {print " SELECTED";}?>><?php print _("Company");?></OPTION>
            <OPTION VALUE=ou<?php if ($what == "ou") {print " SELECTED";}?>><?php print _("Division");?></OPTION>
            <OPTION VALUE=st<?php if ($what == "st") {print " SELECTED";}?>><?php print _("State")?></OPTION>
            <OPTION VALUE=l<?php if ($what == "l") {print " SELECTED";}?>><?php print _("City");?></OPTION>
            <OPTION VALUE=postalcode<?php if ($what == "postalcode") {print " SELECTED";}?>><?php print _("Postal Code");?></OPTION>
            <OPTION VALUE=telephonenumber<?php if ($what == "telephonenumber") {print " SELECTED";}?>><?php print _("Work Phone Number");?></OPTION>
            <OPTION VALUE=homephone<?php if ($what == "homephone") {print " SELECTED";}?>><?php print _("Home Phone Number");?></OPTION>
            <OPTION VALUE=mobile<?php if ($what == "mobile") {print " SELECTED";}?>><?php print _("Cell Phone");?></OPTION>
            <OPTION VALUE=hostedfpsite<?php if ($what == "hostedfpsite") {print " SELECTED";}?>><?php print _("Front Page Site");?></OPTION>
            <OPTION VALUE=hostedsite<?php if ($what == "hostedsite") {print " SELECTED";}?>><?php print _("Web Site");?></OPTION>
          } else if ($_SESSION['classi'] == "snom") {?>
            <OPTION VALUE=telephonenumber<?php if ($what == "telephonenumber") {print " SELECTED";}?>><?php print _("Phone Number");?></OPTION><?php
          } else if ($_SESSION['classi'] == "trust") {?>
            <OPTION VALUE=uid<?php if ($what == "uid") {print " SELECTED";}?>><?php print _("Host Name");?></OPTION><?php
          } else if ($_SESSION['classi'] == "mserver") {?>
            <OPTION VALUE=uid<?php if ($what == "uid") {print " SELECTED";}?>><?php print _("Login Name");?></OPTION>
            <OPTION VALUE=l<?php if ($what == "l") {print " SELECTED";}?>><?php print _("Location");?></OPTION>
            <OPTION VALUE=iphostnumber<?php if ($what == "iphostnumber") {print " SELECTED";}?>><?php print _("IP Address");p?></OPTION>
            <OPTION VALUE=description<?php if ($what == "description") {print " SELECTED";}?>><?php print _("Description");?></OPTION><?php
          }
          if ($_SESSION['classi'] == "system") {?>
            <OPTION VALUE=accountSuspended<?php if ($what == "accountSuspended") {print " SELECTED";}?>><?php print _("All Suspended Accounts");?></OPTION><?php
          }?>
        </SELECT></TD></TR>
  <TR CLASS=list-color2><TD onmouseover="myHint.show('1')" onmouseout=myHint.hide()>... </TD><TD>
        <SELECT NAME=type>
          <OPTION VALUE=in<?php if ($type == "in") {print " SELECTED";}?>><?php print _("Contains");?></OPTION>
          <OPTION VALUE=start<?php if ($type == "start") {print " SELECTED";}?>><?php print _("Begins With");?></OPTION>
          <OPTION VALUE=end<?php if ($type == "end") {print " SELECTED";}?>><?php print _("Ends With");?></OPTION>
          <OPTION VALUE="eq"<?php if ($type == "eq") {print " SELECTED";}?>><?php print _("Equals");?></OPTION>
        </SELECT></TD></TR>
  <TR CLASS=list-color1><TD onmouseover="myHint.show('2')" onmouseout=myHint.hide()>... </TD><TD>
        <INPUT TYPE=TEXT NAME=search VALUE="<?php print $search;?>" autocomplete="off" SIZE=50></TD></TR>
  <TR CLASS=list-color2><TD COLSPAN=2 ALIGN=CENTER>
        <INPUT TYPE=SUBMIT onclick="this.name='find'" VALUE="<?php print _("Search");?>">
        <INPUT TYPE=SUBMIT onclick=this.name='add' VALUE="<?php print _("Add");?>">
    </td>
  </tr>
</table>
</FORM>
<script>
var ldappop=new TextComplete(document.ldapuform.search,ldapautodata,'/auth/uidxml.php',setldappopurl,document.ldapuform,ldappop);
</script>
