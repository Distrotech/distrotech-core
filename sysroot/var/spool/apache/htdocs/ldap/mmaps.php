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
<FORM METHOD=POST NAME=mmapform onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=mmap VALUE="<?php print $_POST['mmap'];?>">
<table border="0" width="90%" cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH CLASS=heading-body ALIGN=MIDDLE COLSPAN=2>
<?php
$abdn="ou=Email";

$mdisc["virtuser"]=_("Virtual Users (Non System Users)");
$mdisc["access"]=_("Access Control (Deny Servers Access)");
$mdisc["mailer"]=_("Mailertable (SMTP Redirects)");
$mdisc["domain"]=_("Domaintable (Domain Rewriting)");
$mdisc["horde"]=_("Horde Domain Map (Webmail Settings)");
$mdisc["authinfo"]=_("SMTP Authentication (Outgoing Mail)");


if ($mdisc[$_POST['mmap']] != "") {
  if ($mapmod == _("Delete")) {
    $ddn="sendmailMTAKey=" . $_POST[$_POST['mmap']] . ",sendmailMTAMapName=" . $_POST['mmap'] . ",ou=Email";
    ldap_delete($ds,$ddn);
  } else if (($key != "") && (($value != "") || ($action != "") || (($pass1 == $pass2) && ($user != "")))) {
   $addent=array();
   $addent["objectclass"][0]="sendmailMTA";
   $addent["objectclass"][1]="sendmailMTAMap";
   $addent["objectclass"][2]="sendmailMTAMapObject";
   $addent["sendmailmtamapname"][0]=$_POST['mmap'];
   if ($_POST['mmap'] != "horde") {
     $addent["sendmailmtacluster"][0]="AllServers";
   }  
   if ($_POST['mmap'] == "authinfo") {
     $key="AuthInfo:" . $key;
     $pass1=base64_encode($pass1);
     $value="\"U:" . $user . "\" \"P=" . $pass1 . "\""; 
   }
   $addent["sendmailmtakey"][0]=$key;
   if ($_POST['mmap'] == "virtuser") {
     if ($value == "") {
       $addent["sendmailmtamapvalue"][0]="error:5.7.0:550 $action";
     } else {
       $addent["sendmailmtamapvalue"][0]=$value;
     }
   } else if ($_POST['mmap'] == "mailer") {
     $addent["sendmailmtamapvalue"][0]="smtp:" . "[" . $value . "]";
   } else {
     $addent["sendmailmtamapvalue"][0]=$value;
   }
   ldap_add($ds,"sendmailMTAKey=$key,sendmailMTAMapName=" . $_POST['mmap'] . ",ou=Email",$addent);
  }

  $sobj="(&(objectClass=sendmailMTAMapObject)(sendmailMTAMapName=" . $_POST['mmap'] . "))";

  $sr=ldap_search($ds,"$abdn",$sobj);
  $info = ldap_get_entries($ds, $sr);

  print _("Adding Entries To") . " " . $mdisc[$_POST['mmap']];
  print "</TD></TR><TR CLASS=list-color1><TD  onmouseover=\"myHint.show('exist')\" onmouseout=myHint.hide() WIDTH=50%>" . _("Existing Entries") . "</TD><TD><SELECT NAME=\"" . $_POST['mmap'] . "\">\n";

  for($dcnt=0;$dcnt < $info["count"];$dcnt++) {
    $dn=$info[$dcnt]["dn"];
    if ($_POST['mmap'] == "access") {
      print "<OPTION VALUE=\"" . $info[$dcnt]["sendmailmtakey"][0] . "\">" . $info[$dcnt]["sendmailmtakey"][0] . "\n";
    } else {
      print "<OPTION VALUE=\"" . $info[$dcnt]["sendmailmtakey"][0] . "\">" . $info[$dcnt]["sendmailmtakey"][0] . " --> " . $info[$dcnt]["sendmailmtamapvalue"][0] . "\n";
    }
  }
  print "</SELECT>\n";

  print "<INPUT TYPE=HIDDEN NAME=mmap VALUE=" . $_POST['mmap'] . ">\n";

  if ($_POST['mmap'] == "virtuser") {
    print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "key')\" onmouseout=myHint.hide() WIDTH=50%>" . _("Local Address") . "</TD><TD><INPUT TYPE=TEXT NAME=key></TD></TR>\n";
    print "<TR CLASS=list-color1><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "value')\" onmouseout=myHint.hide()>" . _("Delivery Address") . "</TD><TD><INPUT TYPE=TEXT NAME=value></TD></TR>\n";
    print "<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body2>Or</TD></TR>\n";
    print "<TR CLASS=list-color1><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "action')\" onmouseout=myHint.hide()>" . _("Error Message") . "</TD><TD><INPUT TYPE=TEXT NAME=action></TD></TR>\n";    
    print "<TR CLASS=list-color2><TD COLSPAN=2 VALIGN=MIDDLE ALIGN=CENTER>";
  } else if ($_POST['mmap'] == "authinfo") {
    print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "key')\" onmouseout=myHint.hide() WIDTH=50%>" . _("Mail Server") . "</TD><TD><INPUT TYPE=TEXT NAME=key></TD></TR>\n";
    print "<TR CLASS=list-color1><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "user')\" onmouseout=myHint.hide()>" . _("Username") . "</TD><TD><INPUT TYPE=TEXT NAME=user></TD></TR>\n";
    print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "pass')\" onmouseout=myHint.hide()>" . _("Password") . "</TD><TD><INPUT TYPE=PASSWORD NAME=pass1></TD></TR>\n";    
    print "<TR CLASS=list-color1><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "pass')\" onmouseout=myHint.hide()>" . _("Confirm Password") . "</TD><TD><INPUT TYPE=PASSWORD NAME=pass2></TD></TR>\n";    
    print "<TR CLASS=list-color2><TD COLSPAN=2 VALIGN=MIDDLE ALIGN=CENTER>";
  } else if ($_POST['mmap'] == "access") {
    print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "key')\" onmouseout=myHint.hide() WIDTH=50%>" . _("Address/Server/IP") . "</TD><TD><INPUT TYPE=TEXT NAME=key></TD></TR>";
    print "<INPUT TYPE=HIDDEN  NAME=value VALUE=REJECT>";
    print "<TR CLASS=list-color1><TD COLSPAN=2 VALIGN=MIDDLE ALIGN=CENTER>";
  } else if ($_POST['mmap'] == "mailer") {
    print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "key')\" onmouseout=myHint.hide() WIDTH=50%>" . _("Domain/Server") . "</TD><TD><INPUT TYPE=TEXT NAME=key></TD></TR>";
    print "<TR CLASS=list-color1><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "value')\" onmouseout=myHint.hide()>" . _("SMTP Server") . "</TD><TD><INPUT TYPE=TEXT NAME=value></TD></TR>";
    print "<TR CLASS=list-color2><TD COLSPAN=2 VALIGN=MIDDLE ALIGN=CENTER>";
  } else if ($_POST['mmap'] == "domain") {
    print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "key')\" onmouseout=myHint.hide() WIDTH=50%>" . _("Original Domain") . "</TD><TD><INPUT TYPE=TEXT NAME=key></TD></TR>";
    print "<TR CLASS=list-color1><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "value')\" onmouseout=myHint.hide()>" . _("Rewrite To ..") . "</TD><TD><INPUT TYPE=TEXT NAME=value></TD></TR>";
    print "<TR CLASS=list-color2><TD COLSPAN=2 VALIGN=MIDDLE ALIGN=CENTER>";
  } else if ($_POST['mmap'] == "horde") {
    print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "key')\" onmouseout=myHint.hide() WIDTH=50%>" . _("Email Domain") . "</TD><TD><INPUT TYPE=TEXT NAME=key></TD></TR>";
    print "<TR CLASS=list-color1><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "value')\" onmouseout=myHint.hide()>" . _("Web Site (SERVER_NAME)") . "</TD><TD><INPUT TYPE=TEXT NAME=value></TD></TR>";
    print "<TR CLASS=list-color2><TD COLSPAN=2 VALIGN=MIDDLE ALIGN=CENTER>";
  } else {
    print "<TR CLASS=list-color2><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "key')\" onmouseout=myHint.hide() WIDTH=50%>" . _("Key") . "</TD><TD><INPUT TYPE=TEXT NAME=key></TD></TR>";
    print "<TR CLASS=list-color1><TD onmouseover=\"myHint.show('" . $_POST['mmap'] . "value')\" onmouseout=myHint.hide()>" . _("Value") . "</TD><TD><INPUT TYPE=TEXT NAME=value></TD></TR>";
    print "<TR CLASS=list-color2><TD COLSPAN=2 VALIGN=MIDDLE ALIGN=CENTER>";
  }

  print "<INPUT TYPE=SUBMIT onclick=this.name='aliasmod' VALUE=\"" . _("Add") . "\">\n";
  print "<INPUT TYPE=SUBMIT onclick=this.name='mapmod' VALUE=\"" . _("Delete") . "\">\n";
  print "</TD></TR>";
} else {
?>
Select Map To Edit<P><SELECT NAME=mmap>
<OPTION VALUE="virtuser"><?php print _("Virtual Users (Non System Users)");?>
<OPTION VALUE="access"><?php print _("Access Control (Deny Servers Access)");?>
<OPTION VALUE="mailer"><?php print _("Mailertable (SMTP Redirects)");?>
<OPTION VALUE="domain"><?php print _("Domaintable (Domain Rewriting)");?>
<OPTION VALUE="horde"><?php print _("Horde Domain Map (Webmail Settings)");?>
</SELECT><P>
<INPUT TYPE=SUBMIT onclick=this.name='mapedit' VALUE="Modify">
<?php
}
?>
</FORM>
  </TD></TR>
</table>
