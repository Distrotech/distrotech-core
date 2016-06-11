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

$info=strtolower($_GET['info']);
if ($info == "usercertificate") {
  $info .=";binary";
}

include "/var/spool/apache/htdocs/ldap/ldapbind.inc";

$sr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=" . $_GET['euser'] . ")($info=*))", array($info));
$ei=ldap_first_entry($ds, $sr);

$cinf = ldap_get_values_len($ds, $ei,$info);

if (($info == "userpkcs12") && ($_GET['keyt'] == "12") && (($_GET['euser'] == $PHP_AUTH_USER) || ($ADMIN_USER == "admin"))){
 header("Content-type: application/x-pkcs12");
 print $cinf[0];
}else if (($info == "userpkcs12") && ($_GET['keyt'] == "1") && (($_GET['euser'] == $PHP_AUTH_USER) || ($ADMIN_USER == "admin"))){
 header("Content-type: application/x-rsa-key");
 $pk12=tempnam("/tmp","sslpk12");
 $pkcs12file=fopen($pk12,"w");
 fwrite($pkcs12file,$cinf[0]);
 fclose($pkcs12file);
 system("/usr/bin/openssl pkcs12 -in $pk12 -password pass:\"" . $_POST['classi'] . "\" -nocerts -nodes |/usr/bin/openssl rsa -outform PEM -passout pass:\"" . $_POST['classi'] . "\" -des3");
 unlink($pk12);
} else if ($info == "usersmimecertificate") {
 header("Content-type: application/x-pkcs7-certificates");
 print $cinf[0];
} else {
 header("Content-type: application/x-x509-user-cert");
 print $cinf[0];
}
ldap_unbind($ds);
?>
