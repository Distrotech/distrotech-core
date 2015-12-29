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
if ($pcount == "") {
  $pcount=0;
}
include "ldapbind.inc";

if ($type == "pdc" ) {
  $sr=ldap_search($ds,"ou=Idmap", "(&(objectClass=officePerson)(uid=" . $_GET['euser'] . "))", array("jpegPhoto"));
} else {
  $sr=ldap_search($ds,"", "(&(objectClass=officePerson)(uid=" . $_GET['euser'] . ")(jpegPhoto=*))", array("jpegPhoto"));
}

$ei=ldap_first_entry($ds, $sr);
$cinf = ldap_get_values_len($ds, $ei,"jpegPhoto");
  header("Content-type: image/jpeg");
if (($_GET['imlim'] != "") && ($_GET['imlim'] != "0")){
  $tmpnme=tempnam("/tmp","jpeg");
  $cfile=fopen($tmpnme,w);
  fwrite($cfile,$cinf[$pcount]);
  fclose($cfile);
  $imin=imagecreatefromjpeg($tmpnme);
  $imx=imagesx($imin);
  $imy=imagesy($imin);
 
  if (($imx > $_GET['imlim']) || ($imy > $_GET['imlim'])) {
    if ($imx <= $imy) {
      $newy=$_GET['imlim'];
      $newx=($_GET['imlim']*$imx)/$imy;
    } else {
      $newx=$_GET['imlim'];
      $newy=($_GET['imlim']*$imy)/$imx;
    }
    $imout=imagecreatetruecolor($newx,$newy);
    imagecopyresampled($imout,$imin,0,0,0,0,$newx,$newy,$imx,$imy);
    imagejpeg($imout);
  } else {
    imagejpeg($imin);
  }
  unlink($tmpnme);
}else{
  print $cinf[$pcount];
}
ldap_unbind($ds);
?>
