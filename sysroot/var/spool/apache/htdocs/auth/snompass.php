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
if (!isset($_SESSION['auth'])) {
  exit;
}
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }
?>
<CENTER>
<FORM METHOD=POST NAME=snompassform onsubmit="ajaxsubmit(this.name);return false;">
<TABLE WIDTH=90% CELLSPACING=0 CELLPADDING=0>
<?php
  if (($pass1 == $pass2) && (isset($uppass)) && ($ADMIN_USER == "admin")) {
    $info["userpassword"]=$pass1;

    if (ldap_modify($ds,"cn=snom,ou=snom",$info)) {
      print "<TR CLASS=list-color2><TH CLASS=heading-body>" . _("Password Changed") . "</TH></TR></TABLE>";
      return;
    } else {
      if (ldap_errno($ds) == "32") {
        $dn="cn=Snom,ou=Snom";
        $info["objectclass"][0]="person";
        $info["cn"]="snom";
        $info["sn"]="Snom Global Phone Book";
        $info["userpassword"]=$pass1;
        ldap_add($ds,$dn,$info);

        $info2["objectclass"][0]="organizationalUnit";
        $info2["ou"]="snom";
        $dn2="ou=snom";
        ldap_add($ds,$dn2,$info2);
        ldap_add($ds,$dn,$info);

        print "<TR CLASS=list-color2><TH CLASS=heading-body>" . _("Password Changed (Phone Book Created)") . "</TH></TR></TABLE>";
        return;
      } else {
        $err=ldap_error($ds);
        print "<TR CLASS=list-color2><TH CLASS=heading-body>" . _("Password Change Failed") . " [" . $err . "]</TH></TR></TABLE>";
        return;
      }
    }
  } else if (isset($uppass)) {
    print "<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>" . _("Password Mismatch") . "</TH></TR>";
    return;
  } else if ($ADMIN_USER == "admin") {
?>
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body><?php print _("Changing Phone Book Password");?></TH></TR>
<TR CLASS=list-color1><TD WIDTH=50% onmouseover="myHint.show('SP0')" onmouseout="myHint.hide()"><?php print _("New Password");?></TD><TD><INPUT TYPE=PASSWORD NAME=pass1></TD></TR>
<TR CLASS=list-color2><TD onmouseover="myHint.show('SP1')" onmouseout="myHint.hide()"><?php print _("Confirm");?></TD><TD><INPUT TYPE=PASSWORD NAME=pass2></TD></TR>
<TR CLASS=list-color1><TD COLSPAN=2 ALIGN=MIDDLE><INPUT TYPE=SUBMIT NAME=uppass VALUE="<?php print _("Update Password");?>"></TD></TR>
<?php
  } else {
    print "<TR><TH>" . _("Administrive Access Is Required") . "</TH></TR>";
  }
?>
</TABLE>
</FORM>
