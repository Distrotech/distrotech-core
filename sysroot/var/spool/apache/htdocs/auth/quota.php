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
  if ((!isset($_SESSION['classi'])) || ($_SESSION['classi'] == "")) {
    $euser=$PHP_AUTH_USER;
  } else {
    $euser=$_SESSION['classi'];
  }

  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

$disc=array(_("File Server Quota"),_("Home Directory Quota"),_("Mail Box Quota"));

?>
<FORM METHOD=POST NAME=dquotform onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=classi VALUE="<?php print $euser;?>">
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>
<?php
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

  $iarr=array("quotafileserver","quotahomedir","quotamailspool","quotachanged");
  $dnarr=array("dn");
  $info=strtolower($info);

  if (($_SESSION['utype'] != "pdc") && ($_SESSION['utype'] != "system") && ($_SESSION['utype'] != "")) {
    $sr=ldap_search($ds,"cn=" . $_SESSION['utype'] . ",ou=vadmin","(&(objectClass=virtZoneSettings)(cn=" . $_SESSION['utype'] . "))",$dnarr);
    $ADMIN_USER="pleb";
  } else {
    $sr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=$euser))",$dnarr);
  }

  $iinfo = ldap_get_entries($ds, $sr);
  $dn=$iinfo[0]["dn"];

  if (isset($modrec)) {
     ?>
     <SCRIPT>
       alert("Quota Changes Are Updated Every 4 Hours\n(00:00 04:00 08:00 12:00 16:00 20:00)");
     </SCRIPT>
     <?php

    $minfo["quotafileserver"]=$quotafileserver;
    $minfo["quotamailspool"]=$quotamailspool;
    $minfo["quotahomedir"]=$quotahomedir;
    $minfo["quotachanged"]="yes";
    ldap_modify($ds,$dn,$minfo);
  }


  if (($_SESSION['utype'] != "pdc") && ($_SESSION['utype'] != "system") && ($_SESSION['utype'] != "")) {
    $sr=ldap_search($ds,"cn=" . $_SESSION['utype'] . ",ou=vadmin","(&(objectClass=virtZoneSettings)(cn=" . $_SESSION['utype'] . "))",$iarr);
  } else {
    $dninf=ldap_explode_dn($dn,0);
    if (eregi("^o=(.*)",$dninf[1],$vzinf)) {
      $sr=ldap_search($ds,"cn=" . $vzinf[1] . ",ou=vadmin","(&(objectClass=virtZoneSettings)(cn=" . $vzinf[1] . "))",$iarr);
      $ADMIN_USER="pleb";
    } else {
      $sr=ldap_search($ds,$dn,"(&(objectClass=officePerson)(uid=$euser))",$iarr);
    }
  }

  if ($ADMIN_USER == "admin") {
    print _("Editing Quota[s]");
  } else {
    print _("Viewing Quota[s]");
  } 
  print "</TH></TR>";


  $iinfo = ldap_get_entries($ds, $sr);
  
  for ($i=0; $i <= 2; $i++) {
    $rem=$i % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color1";
    }
?>
    <TR<?php print $bcolor;?>>
      <TD onmouseover="myHint.show('<?php print strtolower($iarr[$i]);?>')" onmouseout="myHint.hide()" WIDTH=75%>
        <?php print $disc[$i];?> (Mb)
      </TD>
      <TD>
<?php
        $attr=$iarr[$i];
        if ($iinfo[0][$attr][0] == "") {
          $iinfo[0][$attr][0]="0";
        }
        if ($ADMIN_USER == "admin") {
?>
          <INPUT TYPE=TEXT NAME=<?php print $iarr[$i];?> VALUE="<?php print $iinfo[0][$attr][0];?>">
<?php
        } else {
          if ($iinfo[0][$attr][0] > 0) {
            print $iinfo[0][$attr][0];
          } else {
            print "None";
          }
        }
?>
      </TD></TR>
<?php
  }
  $rem=$i % 2;
  if ($rem == 1) {
    $bcol[1]=" CLASS=list-color1";
    $bcol[2]=" CLASS=list-color2";
  } else {
    $bcol[2]=" CLASS=list-color1";
    $bcol[1]=" CLASS=list-color2";
  }
  if ($ADMIN_USER == "admin") {
?>
<TR <?php print $bcol[2];?>><TH COLSPAN=2>  
  <INPUT TYPE=SUBMIT VALUE="<?php print _("Modify");?>" NAME=modrec></TH></TR>
<?php
  }
?>
</TABLE></FORM>
