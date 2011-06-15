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

function create_exten_x509($euser) {
  global $ds;

  $iarr=array("userPKCS12","usercertificate;binary");

  $dn="uid=" . $euser . ",ou=snom";
  $sr=@ldap_search($ds, $dn,"(&(objectClass=userCertStore)(uid=$euser))",$iarr);

  if (($sr === FALSE)  || (ldap_count_entries($ds, $sr) == 0)) {
    $certsave["objectClass"][0]="userCertStore";
    $certsave["uid"]=$euser;
    $certsave["cn"]=$euser;
    $pkcs12 = "";
    $x509cert = "";
  } else {
    $ent = ldap_first_entry($ds, $sr);
    $pkcs12 =ldap_get_values_len($ds, $ent, "userPKCS12");
    $x509cert = ldap_get_values_len($ds, $ent, "usercertificate;binary");
  }


  if (!is_array($pkcs12)) {
    $x509file=tempnam("/tmp","sslreq");
    $keyfile=tempnam("/tmp","sslkey");
    $certconf=tempnam("/tmp","sslconf");
    $ccfile=fopen($certconf,"w");
    fwrite($ccfile,"HOME                   = .
RANDFILE               = \$ENV::HOME/.rnd

[ req ]
default_bits           = 512
distinguished_name     = req_distinguished_name
attributes             = req_attributes
prompt                 = no
encrypt_key            = no
req_extensions         = usr_cert
default_md             = sha1

[ req_distinguished_name ]
CN                     = $euser

[ req_attributes ]

[usr_cert]
subjectKeyIdentifier   = hash
basicConstraints       = CA:FALSE
nsComment              = Generated On Network Sentinel Solutions Firewall
nsCertType             = client, server
keyUsage               = nonRepudiation, digitalSignature, keyEncipherment\n");

    fclose($ccfile);

    if (!is_array($pkcs12)) {
      system("/usr/bin/openssl genrsa 512 > $keyfile 2>&1");
    } else {
      $pk12=tempnam("/tmp","sslpk12");
      $pkcs12file=fopen($pk12,"w");
      fwrite($pkcs12file,$pkcs12[0]);
      fclose($pkcs12file);
      system("/usr/bin/openssl pkcs12 -in $pk12  -password pass:\"\" -nodes -nocerts -descert -out $keyfile");
      unlink($pk12);
    }

    $sslreq=tempnam("/tmp","sslreq");
    system("/usr/bin/openssl req -out $sslreq -new -days 365 -nodes -key $keyfile -config $certconf >/dev/null 2>&1");
    system("/usr/bin/openssl ca -in $sslreq -out $x509file -config /etc/openssl/voipca/ca.conf -batch > /dev/null 2>&1");
    unlink($sslreq);

    $certsave["userPKCS12"]=`/usr/bin/openssl pkcs12 -password pass:"" -export -descert -CApath /etc/openssl/voipca/cacerts -chain -inkey $keyfile -in $x509file -caname "CA Certificate - $euser" -name "$euser" -nodes`;
    $certsave["userCertificate;binary"] = `/usr/bin/openssl x509 -in $x509file -outform der`;
    if ($sr === FALSE) {
      ldap_add($ds,$dn,$certsave);
    } else {
      ldap_modify($ds,$dn,$certsave);
    }
    unlink($certconf);
    unlink($x509file);
    unlink($keyfile);
  }
}

function delete_exten_x509($euser) {
  global $ds;

  $iarr=array("usercertificate;binary");

  $dn="uid=" . $euser . ",ou=snom";
  $sr=@ldap_search($ds, $dn,"(&(objectClass=userCertStore)(uid=$euser))",$iarr);

  if (!($sr === FALSE)  && (ldap_count_entries($ds, $sr) > 0)) {
    $ent = ldap_first_entry($ds, $sr);
    $x509cert = ldap_get_values_len($ds, $ent, "usercertificate;binary");

    $x509file=tempnam("/tmp","sslreq");
    $certfd=fopen("$x509file","w");  
    fwrite($certfd,"-----BEGIN CERTIFICATE-----\n");
    fwrite($certfd,chunk_split(base64_encode($x509cert[0]),64));
    fwrite($certfd,"-----END CERTIFICATE-----\n");
    fclose($certfd);
    system("/usr/bin/openssl ca -batch -config /etc/openssl/voipca/ca.conf -revoke $x509file > /dev/null 2>&1");
    unlink($x509file);
    ldap_delete($ds,$dn);
  }
}

function delete_allexten_x509() {
  global $ds;

  do {
    $sr = ldap_search($ds, "ou=snom", "(&(objectClass=userCertStore)(uid=*))", array("uid"));
    $iinfo = ldap_get_entries($ds, $sr);
    for($x=0;$x < $iinfo['count'];$x++) {
      delete_exten_x509($iinfo[$x]['uid'][0]);
    }
  } while($iinfo['count'] > 0);
}

function create_phone_x509($ptype) {
  global $db;

  $users=pg_query($db, "SELECT name FROM users LEFT OUTER JOIN astdb ON (family = name AND key = 'PTYPE') WHERE value='" . $ptype . "'");
  for($x=0;$x < pg_num_rows($users);$x++) {
    $r=pg_fetch_array($users,$x);
    create_exten_x509($r[0]);
  }
}

function dump_phone_x509($euser) {
  global $ds;

  $iarr=array("userPKCS12","usercertificate;binary");


  $dn="uid=" . $euser . ",ou=snom";
  $sr=ldap_search($ds, $dn,"(&(objectClass=userCertStore)(uid=$euser))",$iarr);

  if (($sr === FALSE)  || (ldap_count_entries($ds, $sr) == 0)) {
    return;
  } else {
    $ent = ldap_first_entry($ds, $sr);
    $pkcs12 =ldap_get_values_len($ds, $ent, "userPKCS12");
    $x509cert = ldap_get_values_len($ds, $ent, "usercertificate;binary");
  }

  $x509file=tempnam("/tmp","sslreq");
  $keyfile=tempnam("/tmp","sslkey");

  $pk12=tempnam("/tmp","sslpk12");
  $pkcs12file=fopen($pk12,"w");
  fwrite($pkcs12file,$pkcs12[0]);
  fclose($pkcs12file);
  system("/usr/bin/openssl pkcs12 -in $pk12  -password pass:\"\" -nodes -nocerts -descert -out $keyfile >/dev/null 2>&1");
  unlink($pk12);

  $x509file=tempnam("/tmp","sslreq");
  $certfd=fopen("$x509file","w");  
  fwrite($certfd,"-----BEGIN CERTIFICATE-----\n");
  fwrite($certfd,chunk_split(base64_encode($x509cert[0]),64));
  fwrite($certfd,"-----END CERTIFICATE-----\n");
  fclose($certfd);


  $key=`/usr/bin/openssl rsa -in $keyfile 2>/dev/null`;
  $cert=`/usr/bin/openssl x509 -in $x509file 2>/dev/null`;

  unlink($x509file);

  return $cert . $key;
}
%>
