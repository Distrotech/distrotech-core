<?php
/*
#    Copyright (C) 2002  <Gregory Hinton Nietsky>
#    Copyright (C) 2006  <Superset>
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

include_once "auth.inc";
include_once "menu_items.inc";

function openpage($menu,$page) {
  return "javascript:openpage(\'" . $page . "\',\'" . $menu . "\')";
}

function openapage($page) {
  return "javascript:openapage(\'" . $page . "\',\'" . $_SESSION['classi'] . "\')";
}

$menu=array("asetup");

for($vdcnt=0;$vdcnt < $vdoms["count"];$vdcnt++) {
  $virtdom[$vdoms[$vdcnt][_("cn")][0]]="javascript:openvirtrealm(\'" . $vdoms[$vdcnt]['cn'][0] . "\')";
  if ($vdoms[$vdcnt][_("cn")][0] == $_SESSION['utype']) {
    $virtuser="javascript:openvirtrealm(\'" . $vdoms[$vdcnt][_("cn")][0] . "\')";
  }
}

if ($_SESSION['utype'] == "system") {
  $asetup[_(".. System Accounts")]="javascript:usersetup(\'system\')";
  $asetup[_(".. Add New Account")]=openapage("ldap/adduser.php");
} else if ($_SESSION['utype'] == "pdc") {
  $asetup[_(".. PDC Accounts")]="javascript:usersetup(\'pdc\')";
} else if ($virtuser != "") {
  $asetup[".. Virtual Realms"]=$virtuser;
}

if (($_SESSION['utype'] == "system") || ($_SESSION['utype'] == "pdc") || ($virtuser != "")) {
  $asetup[_("Personal Details")]=openapage("ldap/userinfo.php");
  $asetup[_("Email Aliases")]="javascript:openaidata(\'email\',\'" . urlencode($_SESSION['classi']) . "\')";
  $asetup[_("Extra Mail Boxes")]=openapage("auth/mailbox.php");
  $asetup[_("Mail Delivery")]=openapage("auth/maildel.php");
  $asetup[_("Hosted Web Sites")]="javascript:openaidata(\'www\',\'" . urlencode($_SESSION['classi']) . "\')";
  $asetup[_("Auth. Profiles")]=openapage("auth/radius.php");
  $asetup[_("Disk Quotas")]=openapage("auth/quota.php");
  $asetup[_("Access Control")]=openapage("auth/access.php");
  $asetup[_("Edit Photo Album")]=openapage("auth/photo.php");
  $asetup[_("Photo Album")]="javascript:openualbum(\'" . urlencode($_SESSION['classi']) . "\')";
}

if ($_SESSION['utype'] != "pdc") {
  $asetup[_("Password Expiry")]=openapage("auth/pwexp.php");
  $asetup[_("User Zone Transfer")]=openapage("auth/migratevz.php");
} else if ($_SESSION['utype'] == "pdc") {
  $asetup[_("Migrate To System")]=openapage("auth/importpdc.php");
}

$asetup[_("View Certificate")]=openapage("auth/sslcert.php");
$asetup[_("Certificate Store")]=openapage("auth/sslstore.php");
$asetup[_("Update Certificate")]=openapage("auth/sslcreate.php");

$iarr=array("userPKCS12","usercertificate;binary","userSMIMECertificate");
$cdescrip["userPKCS12"]="Private Key Chain";
$cdescrip["userSMIMECertificate"]="Public Key Chain";
$cdescrip["userCertificate;binary"]="Public Certificate";

$sr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=" . $_SESSION['classi'] . "))",$iarr);
$centry = ldap_first_entry($ds,$sr);
$attrs=ldap_get_attributes($ds,$centry);
if ($attrs["count"] > 0) {
  asort($attrs);
  reset($attrs);
  $cgeturl[_("userPKCS12")]="/cert/" . $_SESSION['classi'] . ".p12";
  $cgeturl[_("userCertificate;binary")]="/cert/" . $_SESSION['classi'] . ".crt";
  $cgeturl[_("userSMIMECertificate")]="/cert/" . $_SESSION['classi'] . ".p7b";

  for ($acnt=0;$acnt < $attrs["count"];$acnt++) {
    $asetup[$cdescrip[$attrs[$acnt]]]=$cgeturl[$attrs[$acnt]];
    if ($attrs[$acnt] == "userCertificate;binary") {
      $asetup[_("Public Key File")]="/cert/" . $_SESSION['classi'] . ".pub";
      $asetup[_("Public SSH Key")]="/cert/" . $_SESSION['classi'] . ".ssh";
    }
    if ($attrs[$acnt] == "userPKCS12") {
      $asetup[_("Private Key File")]="javascript:getrsakey(\'" . $_SESSION['classi'] . "\')";
      $asetup[_("Open VPN Config")]="javascript:getovpnconf(\'" . $_SESSION['classi'] . "\')";
    }
  }
}

for($mcnt=0;$mcnt < count($menu);$mcnt++) {
  $subout[$menu[$mcnt]]="";
  while(list($item,$action)=each($$menu[$mcnt])) {
    if (substr($action,0,7) == "include") {
      $include=substr($action,8);
      if ($include == "login") {
        $include2="apps";
      } else {
        $include2=$include;
      }
      $subout[$menu[$mcnt]].="['" . $item . "', 'javascript:openpage(\'\',\'" . $include2 . "\')', null,\n\t" . $subout[$include] . "\n\t],\n\t";
    } else {
        $subout[$menu[$mcnt]].="\t['" . $item . "', '" . $action . "'],\n\t";
    }
  }
  $subout[$menu[$mcnt]]=substr($subout[$menu[$mcnt]],0,-3);
}

for($mcnt=0;$mcnt < count($menu);$mcnt++) {
  print $menu[$mcnt] . "_items_list=[\n" . $subout[$menu[$mcnt]] . "];\n\n";
}
?>

if (((activemenu != 'asetup') || (activeuser != '<?php print $_SESSION['classi'];?>')) && (asetup_items_list != null)) {
  if (menu_list['asetup_menu'] == null ) {
    menu_list['asetup_menu']=new menu(asetup_items_list,menu_vert);
  } else {
    menu_list['asetup_menu'].configmenu(asetup_items_list);
  }
  if ((menu_list['asetup_menu'] != null) && (menu_list['main_menu'])) {
    document.getElementById('menu-bar').innerHTML=menu_list['main_menu'].mframe.contentDocument.body.innerHTML+menu_list['asetup_menu'].mframe.contentDocument.body.innerHTML;
    menu_list['main_menu'].show();
    menu_list['asetup_menu'].show();
    activemenu='asetup';
    activeuser='<?php print $_SESSION['classi'];?>';
  }
}
