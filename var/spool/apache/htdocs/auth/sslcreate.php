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


%>
<FORM METHOD=POST NAME=usercert onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=classi VALUE="<%print $euser;%>">
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body><%print _("Updating/Creating SSL Certificate");%></TH></TR>
<TR CLASS=list-color1><TH COLSPAN=2 CLASS=heading-body2><%print _("Compulsory Information");%></TH></TR>
<%
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

  $descrip["ipHostNumber"]=_("IP Address (Optional)");
  $descrip["description"]=_("Domain Name (Optional)");
  if ( ! file_exists("/etc/.networksentry-lite")) {
      $descrip["certificateGenerate"]=_("Certificate Pass Phrase");
  }

  $iarr=array("cn","mail","l","st","c","o","ou","description","ipHostNumber","certificateGenerate");

  $dnarr=array("dn");
  $info=strtolower($info);

  $sr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=$euser))",$dnarr);
  $iinfo = ldap_get_entries($ds, $sr);
  $dn=$iinfo[0]["dn"];

  $sr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=$euser))",$iarr);
  $iinfo = ldap_get_entries($ds, $sr);

  if (isset($modrec)) {
    for ($i=0; $i < count($iarr); $i++) {
      $attr=$iarr[$i];
      $lattr=strtolower($attr);
      if (($attr != "certificateGenerate") && ($$attr != "")) {
        $minfo[$attr]=$$attr;
      } else if ($attr != "certificateGenerate") {
        $dinfo[$lattr]=$iinfo[0][$lattr][0];
      }
    }

    ldap_modify($ds,$dn,$minfo);
    if (count($dinfo) > 0) {
      ldap_mod_del($ds,$dn,$dinfo);
    }

    $sr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=$euser))",$iarr);
    $iinfo = ldap_get_entries($ds, $sr);

    if ((((($ppass1 != "") && ($pass2 == $ppass1)) && ($certupdate == "NEWKEY:")) || (($ppass1 != "") && ($certupdat="NEWREQ"))) && 
        (($c != "") && ($st != "") && ($l != "") && ($o != "") && ($ou != "") && ($cn != "") && ($mail != ""))) {
      %>
      <SCRIPT>
        alert("Certificates Generated Will Need\nTo Be Verifyed By A System Administrator\nThe Certificate Will Not Be Trusted\nUntil Signed.\nOnce Signed You Will Need To\nUpdate Your Certificate With The\nPassphrase.\nUpdating The PKCS#7 File\nDoes Not Require The\nPassphrase.\n");
      </SCRIPT>
<%
      $newpass=$ppass1;
      $x509file=tempnam("/tmp","sslreq");

      $certsr=ldap_search($ds,$dn,"(&(objectClass=officePerson)(uid=$euser))", 
                          array("userPKCS12","usercertificate;binary"));
      $certs=ldap_first_entry($ds, $certsr);
      $pkcs12=ldap_get_values_len($ds,$certs,"userPKCS12");
      $x509cert=ldap_get_values_len($ds,$certs,"usercertificate;binary");

      if (($certupdate == "NEWKEY:") || ($certupdate == "NEWREQ:") || ($pkcs12 == "")) {
        if ($description != "") {
          $certsubname="DNS:" . $description . ",";
        }
        $certsubname .="email:" . $mail;
        if ($ipHostNumber != "") {
          $certsubname .=",IP:" . $ipHostNumber;
        }
        $certconf=tempnam("/tmp","sslconf");
        $ccfile=fopen($certconf,"w");
        fwrite($ccfile,"HOME                   = .
RANDFILE               = \$ENV::HOME/.rnd

[ req ]
default_bits           = 2048
distinguished_name     = req_distinguished_name
attributes             = req_attributes
prompt                 = no
encrypt_key            = no
req_extensions         = usr_cert
default_md             = sha1

[ req_distinguished_name ]
C                      = $c
ST                     = $st
L                      = $l
O                      = $o
OU                     = $ou
CN                     = $cn

[ req_attributes ]

[usr_cert]
subjectKeyIdentifier   = hash
basicConstraints       = CA:FALSE
nsComment              = Generated On Network Sentinel Solutions Firewall
nsCertType             = client, email, server
keyUsage               = nonRepudiation, digitalSignature, keyEncipherment
subjectAltName         = $certsubname
issuerAltName          = $certsubname\n");

        if ($description != "") {
          fwrite($ccfile,"nsSslServerName        = $description\n");
        }
        fclose($ccfile);
        $keyfile=tempnam("/tmp","sslkey");
        if (($pkcs12 != "") && ($certupdate == "NEWREQ:")) {
          $pk12=tempnam("/tmp","sslpk12");
          $pkcs12file=fopen($pk12,"w");
          fwrite($pkcs12file,$pkcs12[0]);
          fclose($pkcs12file);
          system("/usr/bin/openssl pkcs12 -in $pk12 -password pass:\"$ppass1\" -passout pass:\"$ppass1\" -des3 -nocerts -descert -out $keyfile");
          unlink($pk12);
        } else {
          system("/usr/bin/openssl genrsa -des3 -passout pass:\"$ppass1\" -out $keyfile 2048");
          if ($ADMIN_USER == "admin") {
            touch("/var/spool/apache/htdocs/ns/config/gensshauth");
          }
        }
        $newreq=`/usr/bin/openssl req -outform der -new -days 365 -passin pass:"$ppass1" -key $keyfile -config $certconf 2>&1`;
        system("/usr/bin/openssl req -x509 -set_serial 0 -new -days 365 -passin pass:\"$ppass1\" -out $x509file -key $keyfile -extensions usr_cert -config $certconf",$retval);

        unlink($certconf);
        if ($retval == "0") {
          $userPKCS12=`/usr/bin/openssl pkcs12 -export -descert -inkey $keyfile -in $x509file -des3 -password pass:"$newpass" -passin pass:"$ppass1"`;
          unlink($keyfile);
          if ($x509cert[0] == "") {
            $newcert=`/usr/bin/openssl x509 -in $x509file -outform der`;
            $certsave["userCertificate;binary"]=$newcert;
          } else {
            $x509fd=fopen($x509file,"w");
            fwrite($x509fd,"-----BEGIN CERTIFICATE-----\n");
            fwrite($x509fd,chunk_split(base64_encode($x509cert[0]),64));
            fwrite($x509fd,"-----END CERTIFICATE-----\n");
            fclose($x509fd);
          }
          if ($userPKCS12 != "") {
            $certsave["userPKCS12"]=$userPKCS12;
          }
          $certsave["certificateRequest"]=$newreq;
          ldap_modify($ds,$dn,$certsave);
        } else {
          unlink($keyfile);
        }
      } else if (($x509cert[0] != "") && ($pkcs12[0] != "")) {
        $pk12=tempnam("/tmp","sslpk12");
        $keyfile=tempnam("/tmp","sslkey");
        $x509file=tempnam("/tmp","sslcert");
        $x509fd=fopen($x509file,"w");
        fwrite($x509fd,"-----BEGIN CERTIFICATE-----\n");
        fwrite($x509fd,chunk_split(base64_encode($x509cert[0]),64));
        fwrite($x509fd,"-----END CERTIFICATE-----\n");
        fclose($x509fd);
        $pkcs12file=fopen($pk12,"w");
        fwrite($pkcs12file,$pkcs12[0]);
        fclose($pkcs12file);
        system("/usr/bin/openssl pkcs12 -in $pk12 -des3 -password pass:\"$ppass1\" -passout pass:\"$ppass1\" -nocerts -descert -out $keyfile",$retval);
        unlink($pk12);
        if ($retval == "0") {
          $userPKCS12=`/usr/bin/openssl pkcs12 -export -des3 -descert -CApath /etc/ipsec.d/certs -chain -inkey $keyfile -in $x509file -caname "CA Certificate - $cn" -name "$cn" -password pass:"$newpass" -passin pass:"$ppass1"`;
          unlink($keyfile);
          if ($userPKCS12 != "") {
            $certsave["userPKCS12"]=$userPKCS12;
          }
          ldap_modify($ds,$dn,$certsave);
        } else {
          unlink($keyfile);
        }
      } else {
        $x509fd=fopen($x509file,"w");
        fwrite($x509fd,"-----BEGIN CERTIFICATE-----\n");
        fwrite($x509fd,chunk_split(base64_encode($x509cert[0]),64));
        fwrite($x509fd,"-----END CERTIFICATE-----\n");
        fclose($x509fd);
      }
      $newpkcs7=`/usr/bin/openssl crl2pkcs7 -in /etc/ipsec.d/crls/crl.pem -certfile /etc/ipsec.d/cacerts/cacert.pem -certfile $x509file -outform der`;
      unlink($x509file);
      $certsave["userSMIMECertificate"]=$newpkcs7;
      ldap_modify($ds,$dn,$certsave);
    } else if ($certupdate == "UPDATE:") {
      $x509file=tempnam("/tmp","sslreq");
      $certsr=ldap_search($ds,$dn,"(&(objectClass=officePerson)(uid=$euser))", 
                          array("usercertificate;binary"));
      $certs=ldap_first_entry($ds, $certsr);
      $x509cert=ldap_get_values_len($ds,$certs,"usercertificate;binary");
      $certfd=fopen("$x509file","w");
      fwrite($certfd,"-----BEGIN CERTIFICATE-----\n");
      fwrite($certfd,chunk_split(base64_encode($x509cert[0]),64));
      fwrite($certfd,"-----END CERTIFICATE-----\n");
      fclose($certfd);
      $newpkcs7=`/usr/bin/openssl crl2pkcs7 -in /etc/ipsec.d/crls/crl.pem -certfile /etc/ipsec.d/cacerts/cacert.pem -certfile $x509file -outform der`;
      $certsave["userSMIMECertificate"]=$newpkcs7;
      ldap_modify($ds,$dn,$certsave);

      if ($ppass1 != "") {
        $pkcs12=ldap_get_values_len($ds,$certs,"userPKCS12");
        $pk12=tempnam("/tmp","sslpk12");
        $pkcs12file=fopen($pk12,"w");
        fwrite($pkcs12file,$pkcs12[0]);
        fclose($pkcs12file);
        $keyfile=tempnam("/tmp","sslkey");
        system("/usr/bin/openssl pkcs12 -in $pk12 -des3 -password pass:\"$ppass1\" -passout pass:\"$ppass1\" -nocerts -descert -out $keyfile",$retval);
        unlink($pk12);
        if ($retval == "0") {
          $userPKCS12=`/usr/bin/openssl pkcs12 -export -des3 -descert -inkey $keyfile -in $x509file -password pass:"$ppass1" -passin pass:"$ppass1"`;
          if ($userPKCS12 != "") {
            $certsave["userPKCS12"]=$userPKCS12;
          }
          ldap_modify($ds,$dn,$certsave);
        }
        unlink($keyfile);
      }
      unlink($x509file);
    }
  }
  
  for ($i=0; $i < count($iarr); $i++) {
    $rem=$cnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color1";
      $bcolor1=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color2";
      $bcolor1=" CLASS=list-color1";
    }
    $attr=$iarr[$i];
    $lattr=strtolower($attr);
%>
    <TR<%print $bcolor;%>>
      <TD WIDTH=75% onmouseover="myHint.show('<%print $attr;%>')" onmouseout="myHint.hide()">
        <% print $descrip[$attr];%>
      </TD>
<%
    if ($attr == "certificateGenerate") {
%>
      <TD><INPUT TYPE=PASSWORD SIZE=40 NAME=ppass1 VALUE=""></TD></TR>
      <TR <%print "$bcolor1";$cnt++%>><TD WIDTH=50% onmouseover="myHint.show('<%print $attr . "2";%>')" onmouseout="myHint.hide()">Confirm Pass Phrase (For New Private Key)</TD>
        <TD WIDTH=50%><INPUT TYPE=PASSWORD SIZE=40 NAME=ppass2 VALUE=""></TD></TR>
      <TR <%print "$bcolor";%>><TD WIDTH=50% onmouseover="myHint.show('<%print $attr . "3";%>')" onmouseout="myHint.hide()">Type Of Certificate Update<BR><FONT SIZE=1>This Certificate Must Be Signed By A Admin User To Be Trusted</FONT></TD>
        <TD><SELECT NAME=certupdate>
                <OPTION VALUE="UPDATE:">Update Certificate
                <OPTION VALUE="NEWREQ:">Create A New Public Certificate
                <OPTION VALUE="NEWKEY:">Create A New Private And Public Certificate
              </SELECT>
<%} else {%>
      <TD>
          <INPUT TYPE=TEXT SIZE=40 NAME=<%print $iarr[$i];%> VALUE="<%print $iinfo[0][$lattr][0];%>">
      </TD></TR>
<%
    }
    $cnt++;
  }
  $rem=$cnt % 2;
  if ($rem == 1) {
    $bcol[1]=" CLASS=list-color1";
    $bcol[2]=" CLASS=list-color2";
  } else {
    $bcol[2]=" CLASS=list-color1";
    $bcol[1]=" CLASS=list-color2";
  }
%>
<TR <%print $bcol[2];%>><TH COLSPAN=2>  
  <INPUT TYPE=SUBMIT VALUE="Modify" NAME=modrec></TH></TR>
</TABLE></FORM>
