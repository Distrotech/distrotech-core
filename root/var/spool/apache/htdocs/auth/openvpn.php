<%
header("Content-Type: application/zip");
include "/var/spool/apache/htdocs/ldap/ldapbind.inc";
  
$certs=array("usercertificate;binary","userpkcs12","usersmimecertificate");
$sr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=" . $_GET['euser'] . "))", $certs);
$ei=ldap_first_entry($ds, $sr);

$pubcert = ldap_get_values_len($ds, $ei, $certs[0]);
$pkcs12 = ldap_get_values_len($ds, $ei, $certs[1]);
$p7bfile = ldap_get_values_len($ds, $ei, $certs[2]);

$zip = new ZipArchive();
$filename=tempnam("/tmp","openvpn");

if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
    exit("cannot open <$filename>;\n");
}


$zip->addFromString($_SERVER['SERVER_NAME'] . "/openvpn.ovpn", "remote " . $_SERVER['SERVER_NAME'] . "\r\nproto tcp-client\r\nclient\r\ndev tun\r\nca ca.pem\r\ncert public.crt\r\nkey private.key\r\n");
$zip->addFromString($_SERVER['SERVER_NAME'] . "/public.crt", "-----BEGIN CERTIFICATE-----\r\n" . chunk_split(base64_encode($pubcert[0]),64) . "-----END CERTIFICATE-----\r\n");

$pk12=tempnam("/tmp","sslpk12");
$pkcs12file=fopen($pk12,"w");
fwrite($pkcs12file,$pkcs12[0]);
fclose($pkcs12file);
$privcert=popen("/usr/bin/openssl pkcs12 -in $pk12 -password pass:\"" . $_POST['classi'] . "\" -nocerts -nodes |/usr/bin/openssl rsa -passout pass:\"" . $_POST['classi'] . "\" -des3","r");
$zip->addFromString($_SERVER['SERVER_NAME'] . "/private.key", fread($privcert,8192));
unlink($pk12);
pclose($privcert);

$zip->addFromString($_SERVER['SERVER_NAME'] . "/public.p7b", $p7bfile[0]);
$zip->addFromString($_SERVER['SERVER_NAME'] . "/private.p12", $pkcs12[0]);
$zip->addFromString($_SERVER['SERVER_NAME'] . "/ca.pem", file_get_contents("/etc/ipsec.d/cacerts/cacert.pem"));

$zip->close();
readfile($filename);
unlink($filename);
ldap_unbind($ds);
%>
