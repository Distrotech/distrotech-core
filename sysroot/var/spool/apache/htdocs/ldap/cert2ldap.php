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
include "ldapbind.inc";

$subj=`/usr/bin/openssl x509 -in /etc/openssl/newcerts/$cert.pem  -noout -subject`;
$subj=chop($subj);
$x509dn=preg_split("/\//",$subj);
array_shift($x509dn);

$search=join(")(",$x509dn);

$info=array("uid");


$sr=ldap_search($ds,"","(&(" . $search . "))",$info);
$ei=ldap_first_entry($ds, $sr);
$cinf = ldap_get_values($ds,$ei,"uid");

ldap_unbind($ds);

if ($cinf[0] != "") {
  header("Location: https://$SERVER_NAME:666/auth/index.php?disppage=ldap/userinfo.php&euser=$cinf[0]");
} else {
  header("Location: https://$SERVER_NAME:666/auth/index.php?navpage=ldap/ennav.php");
}
?>
