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
  if (!$ds) {
    include "../ldap/auth.inc";
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

$disc=array("File Server Quota","Home Directory Quota","Mail Box Quota","Allow Access To Proxy");

?>
<FORM METHOD=POST>
<INPUT TYPE=HIDDEN NAME=classi VALUE="<?php print $euser;?>">
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>
<?php print _("Transfer User To Zone");?></TH></TR>
<?php
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

  $dnarr=array("dn");
  $sr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=$euser))",$dnarr);
  $iinfo = ldap_get_entries($ds, $sr);
  eregi("^(uid=.*,ou=Users),(dc=.*)",$iinfo[0]["dn"],$sdn);
  $dn=$sdn[1];
  $dninf=ldap_explode_dn($dn,0);

  if (isset($modrec)) {
    if (($vzone) && ($dninf[1] != "o=" . $vzone)) {
      $zarr=array("quotafileserver","quotamailspool","quotahomedir","smbserveraccess","squidproxyaccess","maxaliases","maxwebaliases","maxmailboxes");
      $zinfq=ldap_search($ds,"cn=" . $vzone . ",ou=vadmin","(&(objectClass=virtZoneSettings)(cn=" . $vzone . "))",$zarr);
      $zinf=ldap_get_entries($ds,$zinfq);
      for($zcnt=0;$zcnt<count($zarr);$zcnt++) {
        $minfo[$zarr[$zcnt]]=$zinf[0][$zarr[$zcnt]][0];
      }
      $minfo["quotachanged"]="yes";
      $minfo["radiusprofiledn"]="cn=" . $vzone . ",ou=vadmin";

      ldap_modify($ds,$dn,$minfo);
      $domove=1;

      $zrarr=array("radiusrealm","radiusporttype","radiusframedipaddress","radiusframedmtu","radiusframedcompression","radiussimultaneoususe","radiussessiontimeout","radiusidletimeout","radiusacctinteriminterval","radiusreplyitem","radiuscheckitem","radiusservicetype","radiusframedprotocol","radiusframedipnetmask","radiusauthtype");
      $zinfq=ldap_search($ds,$dn,"(&(objectClass=officeperson)(uid=" . $euser . "))",$zrarr);
      $zinf=ldap_get_entries($ds,$zinfq);
      for($zcnt=0;$zcnt<$zinf["count"];$zcnt++) {
        for($racnt=0;$racnt < count($zrarr);$racnt++) {
          if ($zinf[$zcnt][$zrarr[$racnt]][0] != "") {
            $dinfo[$zrarr[$racnt]][0]=$zinf[$zcnt][$zrarr[$racnt]][0];
          }
        }
      }
      if (count($dinfo) > 0) {
        ldap_mod_del($ds,$dn,$dinfo);
      }
    } else if (eregi("o=(.*)",$dninf[1],$czone)) {
      $zarr=array("quotafileserver","quotamailspool","quotahomedir","smbserveraccess","squidproxyaccess","maxaliases","maxwebaliases","maxmailboxes","radiusporttype","radiusframedipaddress","radiusframedmtu","radiusframedcompression","radiussimultaneoususe","radiussessiontimeout","radiusidletimeout","radiusacctinteriminterval","radiusreplyitem","radiuscheckitem","radiusrealm");
      $zinfq=ldap_search($ds,"cn=" . $czone[1] . ",ou=vadmin","(&(objectClass=virtZoneSettings)(cn=" . $czone[1] . "))",$zarr);
      $zinf=ldap_get_entries($ds,$zinfq);
 
     for($zcnt=0;$zcnt<count($zarr)-1;$zcnt++) {
        $racntm=0;
        for($racnt=0;$racnt<$zinf[0][$zarr[$zcnt]]["count"];$racnt++) {
          if  (($zinf[0][$zarr[$zcnt]][$racnt] != "") && (($zarr[$zcnt] != "radiuscheckitem") || ($zinf[0][$zarr[$zcnt]][$racnt] != "Realm = \"" . $zinf[0]["radiusrealm"][0] . "\""))) {
            $minfo[$zarr[$zcnt]][$racntm]=$zinf[0][$zarr[$zcnt]][$racnt];
            $racntm++;
          }
        }
      }
      $minfo["radiusServiceType"][0]="Framed-User";
      $minfo["radiusFramedProtocol"][0]="PPP";
      $minfo["radiusFramedIPNetmask"][0]="255.255.255.255";
      $minfo["radiusAuthType"][0]="Pam";
      $minfo["radiusRealm"][0]="DEFAULT";
      $minfo["quotachanged"][0]="yes";

      ldap_modify($ds,$dn,$minfo);
      $domove=1;

      $zrarr=array("radiusprofiledn");
      $zinfq=ldap_search($ds,$dn,"(&(objectClass=officeperson)(uid=" . $euser . "))",$zrarr);
      $zinf=ldap_get_entries($ds,$zinfq);
      for($zcnt=0;$zcnt<$zinf["count"];$zcnt++) {
        for($racnt=0;$racnt < count($zrarr);$racnt++) {
          if ($zinf[$zcnt][$zrarr[$racnt]][0] != "") {
            $dinfo[$zrarr[$racnt]][0]=$zinf[$zcnt][$zrarr[$racnt]][0];
          }
        }
      }
      if (count($dinfo) > 0) {
        ldap_mod_del($ds,$dn,$dinfo);
      }
    }

    if ($domove) {
      $sr=ldap_search($ds,$dn,"objectclass=*",array("dn"));
      $iinfo = ldap_get_entries($ds, $sr);
      $childdn=array();
      for($ccnt=0;$ccnt < $iinfo["count"];$ccnt++) {
        eregi("^(uid=.*,ou=Users),(dc=.*)",$iinfo[$ccnt]["dn"],$sdn);
        if ($dn != $sdn[1]) {
          $cdninf=ldap_explode_dn($sdn[1],0);
          if ($vzone != "") {
            ldap_rename($ds,$sdn[1],$cdninf[0],"cn=" . $vzone . ",ou=vadmin",false);
          } else if ($czone[1] != "") {
            ldap_rename($ds,$sdn[1],$cdninf[0],"cn=" . $czone[1] . ",ou=vadmin",false);
          }
          array_push($childdn,$cdninf[0]);
        }
      }
      if ($vzone != "") {
        ldap_rename($ds,$dn,"uid=" . $euser,"o=" . $vzone . ",ou=Users",false);
      } else {
        ldap_rename($ds,$dn,"uid=" . $euser,"ou=Users",false);
      }

      for($ccnt=0;$ccnt < count($childdn);$ccnt++) {
        if ($vzone != "") {
          ldap_rename($ds,$childdn[$ccnt] . ",cn=" . $vzone . ",ou=vadmin",$childdn[$ccnt],"uid=" . $euser . ",o=" . $vzone . ",ou=Users",false);
        } else if ($czone[1] != "") {
          ldap_rename($ds,$childdn[$ccnt] . ",cn=" . $czone[1] . ",ou=vadmin",$childdn[$ccnt],"uid=" . $euser . ",ou=Users",false);
        }
      }
      $sr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=$euser))",$dnarr);
      $iinfo = ldap_get_entries($ds, $sr);
      eregi("^(uid=.*,ou=Users),(dc=.*)",$iinfo[0]["dn"],$sdn);
      $dn=$sdn[1];
      $dninf=ldap_explode_dn($dn,0);
    }
  }

  $vzonesq=ldap_search($ds,"ou=vadmin","(objectClass=virtZoneSettings)",array("cn"));
  $vzones=ldap_get_entries($ds,$vzonesq);
  print "<TR CLASS=list-color1><TD WIDTH=50% onmouseover=\"myHint.show('0')\" onmouseout=\"myHint.hide()\">" . _("Zone To Add User To") . "</TD><TD>";
  print "<SELECT NAME=vzone>\n";
  if (($ADMIN_USER == "admin") && (strtolower($dninf[1]) != "ou=users")) { 
    print "<OPTION VALUE=\"\">System Users</OPTION>\n";
  }
  for($vzcnt=0;$vzcnt < $vzones["count"];$vzcnt++) {
    if ("o=" . $vzones[$vzcnt]["cn"][0] != $dninf[1]) {
      print "<OPTION VALUE=\"" . $vzones[$vzcnt]["cn"][0] . "\">" . $vzones[$vzcnt]["cn"][0] . "</OPTION>\n";
    }
  }
  print "</SELECT>\n</TD></TR>";
  if ($ADMIN_USER == "admin") {
?>
<TR CLASS=list-color2><TH COLSPAN=2>
  <INPUT TYPE=SUBMIT VALUE="Modify" NAME=modrec></TH></TR>
<?php
  }
?>
</TABLE></FORM>
