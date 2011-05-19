<%
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
include "ldapcon.inc";
$r=ldap_bind($ds,$LDAP_ROOT_DN,$LDAP_ROOT_PW);

if ($type == "pdc" ) {
  $sr=ldap_search($ds,"ou=Idmap", "(&(objectClass=officePerson)(uid=$euser))", array("jpegPhoto"));
} else {
  $sr=ldap_search($ds,"", "(&(objectClass=officePerson)(uid=$euser)(jpegPhoto=*))", array("jpegPhoto"));
}

$ei=ldap_first_entry($ds, $sr);
$cinf = ldap_get_values_len($ds, $ei,"jpegPhoto");
  header("Content-type: image/jpeg");
if (($imlim != "") && ($imlim != "0")){
  $tmpnme=tempnam("/tmp","jpeg");
  $cfile=fopen($tmpnme,w);
  fwrite($cfile,$cinf[$pcount]);
  fclose($cfile);
  $imin=imagecreatefromjpeg($tmpnme);
  $imx=imagesx($imin);
  $imy=imagesy($imin);
 
  if (($imx > $imlim) || ($imy > $imlim)) {
    if ($imx <= $imy) {
      $newy=$imlim;
      $newx=($imlim*$imx)/$imy;
    } else {
      $newx=$imlim;
      $newy=($imlim*$imy)/$imx;
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
%>
