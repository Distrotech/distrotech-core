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
$info=strtolower($info);
if ($info == "usercertificate") {
  $info .=";binary";
}

include "ldapbind.inc";

$sr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=$euser)($info=*))", array($info));
$ei=ldap_first_entry($ds, $sr);
$cinf = ldap_get_values_len($ds, $ei,$info);
ldap_unbind($ds);

if ($info == "usersmimecertificate") {
  header("Content-type: application/x-pkcs7-certificates");
  $certtype="pkcs7";
} else {
  header("Content-type: octet/stream");
  $certtype="x509";
}

$certfile=tempnam("/tmp","sslcert");
$derfile=fopen($certfile,"w");
fwrite($derfile,$cinf[0]);
fclose($derfile);

if ($pkey != "") {
  if ($pkey == "pub") {
    system("/usr/bin/openssl x509 -in " . $certfile . " -inform der  -outform pem -pubkey -noout");
  } else if ($pkey == "ssh") {
    system("/usr/bin/openssl x509 -in " . $certfile . " -inform der  -pubkey -noout |/usr/bin/pubkey2ssh - " . $euser);
  }
} else {
  system("/usr/bin/openssl " . $certtype . " -in " . $certfile . " -inform der  -outform pem");
}
unlink($certfile);
%>
