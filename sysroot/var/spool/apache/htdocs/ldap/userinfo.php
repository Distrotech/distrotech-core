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

  if ($rdn == "") {
    include "auth.inc";
  }

  include "../cdr/auth.inc";

$descrip["sambaSID"]="SMB Domain (SID To Set On Repair)";
array_push($atrib,"sambaSID");

  if ($_SESSION['style'] == "") {
    include "../style.css";
  } else {
    include "../" . $_SESSION['style'] . "/style.css";
  }

  if ((!isset($_POST['classi'])) || ($_POST['classi'] == "")) {
    $euser=$PHP_AUTH_USER;
  } else {
    $euser=$_POST['classi'];
  }

  if (isset($_POST['utype'])) {
    $ldtype=$_POST['utype'];
  }
  if ($deltex == "delete") {
    $uidarr=array("uidnumber","dn");
    $uidsr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=$euser))",$uidarr);
    $uidinfo = ldap_get_entries($ds, $uidsr);
    $uidnum=$uidinfo[0]['uidnumber'][0];

    $delsr=ldap_search($ds,$uidinfo[0]['dn'],"objectclass=*",array("dn"));
    $diinfo = ldap_get_entries($ds, $delsr);
    for($ccnt=0;$ccnt < $diinfo["count"];$ccnt++) {
      if ($dn != $diinfo[$ccnt]["dn"]) {
        ldap_delete($ds,$diinfo[$ccnt]["dn"]);
      }
    }

    $ld=ldap_delete($ds,$dn);

/*
    pg_close($db);
    include "pgauth.inc";
    $utbl=pg_query($db,"SELECT c.relname as \"Table Name\"
                    FROM  pg_catalog.pg_class c
                    LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
                    WHERE (c.relkind='r' AND n.nspname NOT IN ('pg_catalog','pg_toast'))
                          AND (c.relname = '" . $euser . "_obj' OR c.relname = '" . $euser . "_props');");
    $tblnum=pg_num_rows($utbl);

    if ($tblnum > 0) {
      for($ir=0;$ir < $tblnum;$ir++) {
        $row=pg_fetch_row($utbl,$ir);
        for ($if=0;$if < count($row);$if++) {
          pg_query($db,"DROP TABLE " . $row[$if] . ";");
        }
      }
    }
    pg_close($db);
*/
    include "../cdr/auth.inc";
    pg_query("DELETE FROM users WHERE unix_id=$uidnum");
    $_POST['classi']=$ldtype;
    $baseou=$baseou;
    include "ennav.php";
    return;
  } else if ($fixtex == "repair") {
    $sidarr=array("sambaSID","sambaPrimaryGroupSID","uidnumber");
    if ($ldtype == "pdc") {
      $usidsr=ldap_search($ds,"ou=Idmap","(&(objectClass=officePerson)(uid=$euser))",$sidarr);
    } else {
      $usidsr=ldap_search($ds,"ou=Users","(&(objectClass=officePerson)(uid=$euser))",$sidarr);
    }
    $usidinf = ldap_get_entries($ds, $usidsr);
    $usid=$usidinf[0]['sambasid'][0];
    $ugsid=$usidinf[0]['sambaprimarygroupsid'][0];
    $userid=$usidinf[0]['uidnumber'][0]*2+1000;

    $sid = $domain;

    if (($usid != $userid) || ($ugsid != $sid . "-513")) {
      if ($usid != $sid . "-" . $userid) {
        $sidinfo["sambaSID"]=$sid . "-" . $userid;
      }
      if ($ugsid != $sid . "-513") {
        $sidinfo["sambaPrimaryGroupSID"]=$sid . "-513";
      }
      ldap_modify($ds,$usidinf[0]['dn'],$sidinfo);
    }
  }

  if (($_POST['utype'] != "system") && ($_POST['utype'] != "pdc")) {
    $sr=ldap_search($ds,"cn=" . $_POST['utype'] . ",ou=VAdmin","(&(objectclass=virtZoneSettings)(member=" . $ldn . "))");
  } else {
    $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  }
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

  if (($newuid != "") && ($newuid != $euser) && ($ADMIN_USER == "admin") && ($ldtype != "pdc")){
    $ouid=ldap_search($ds,$dn,"(&(uid=$euser)(olduid=*))");
    $entry=ldap_first_entry($ds,$ouid);
    $oudat=ldap_get_values($ds,$entry,"olduid");
 

    if (ldap_rename($ds,"uid=$euser,ou=users","uid=$newuid","ou=users",true)) {
      include "pgauth.inc";

      $utbl=pg_query($db,"SELECT c.relname as \"Table Name\"
                      FROM  pg_catalog.pg_class c
                      LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
                      WHERE (c.relkind='r' AND n.nspname NOT IN ('pg_catalog','pg_toast'))
                            AND (c.relname = '" . $euser . "_obj' OR c.relname = '" . $euser . "_props');");
      $tblnum=pg_num_rows($utbl);

      if ($tblnum > 0) {
        for($ir=0;$ir < $tblnum;$ir++) {
          $row=pg_fetch_row($utbl,$ir);
          for ($if=0;$if < count($row);$if++) {
            $tdat=preg_split("/_/",$row[$if]);
            pg_query($db,"ALTER TABLE " . $row[$if] . " RENAME TO " . $newuid . "_" . $tdat[1] . ";");
          }
        }
      }
      $rdninfo["homeDirectory"]="/var/home/" . substr($newuid,0,1) . "/" . substr($newuid,1,1) . "/" . $newuid;
      if ($oudat[0] == "") {
        $rdninfo["olduid"]=$euser;
      }
/*
      if ($mailRoutingAddress == $euser) {
        $mailRoutingAddress=$newuid;
      }
*/
      $r=ldap_modify($ds,"uid=$newuid,ou=users",$rdninfo);
      $euser=$newuid;
      $dn="uid=$euser,ou=users";
    }
  }


?>
<base target="_self">

<FORM NAME=uinfo METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=classi VALUE="<?php print $euser;?>">
<INPUT TYPE=HIDDEN NAME=curdiv VALUE=genral>
<?php
/*
  $descrip["jpegPhoto"]="Users Photograph (JPEG)";
  array_push($atrib,"jpegPhoto");
  if ( ! file_exists("/etc/.networksentry-lite")) {
    $descrip["c"]="ISO Country Code (IE ZA)";
    array_push($atrib,"c");
  }

  if (($ADMIN_USER == "admin") && ( ! file_exists("/etc/.networksentry-lite"))) {
    $descrip["squidProxyAccess"]="Allow Access To Proxy Server";
    array_push($atrib,"squidProxyAccess");
    $descrip["smbServerAccess"]="Allow Access To File Server";
    array_push($atrib,"smbServerAccess");
    $descrip["dialupAccess"]="Allow Radius Access (Global Setting)";
    array_push($atrib,"dialupAccess");
    $descrip["maxAliases"]="Maximum Email Aliases";
    $descrip["maxWebAliases"]="Maximum Web Aliases";
    array_push($atrib,"maxAliases");
    array_push($atrib,"maxWebAliases");
    if ( ! file_exists("/etc/.networksentry-lite")) {
      $descrip["accountSuspended"]="Suspend User";
      array_push($atrib,"accountSuspended");
    }
  }
*/

  $sr=ldap_search($ds,"","(&(objectclass=officePerson)(uid=$euser))");
  $entry=ldap_first_entry($ds,$sr);
  $dn=ldap_get_dn($ds,$entry);
  $dnarr=ldap_explode_dn($dn,1);
  if (strtolower($dnarr[1]) == "idmap") {
    $ldtype="pdc";
  }
  if ($newacc == "yes") {
?>
    <SCRIPT>
      alert("This New Account Will Only Be Activated Fully When\nLoged Into For The First Time.\n\nThe Password Will Be Activated On The Next System Sync.\nApprox. 10 Minutes.");
    </SCRIPT>
<?php
  }

/*
  if (($PHP_AUTH_USER == $euser) || ($ADMIN_USER != "pleb")) {
    if ( ! file_exists("/etc/.networksentry-lite")) {
      $descrip["certificateGenerate"]="Certificate Pass Phrase (For Updates Only)";
      array_push($atrib,"certificateGenerate");
    }
    $descrip["mailLocalAddress"]="Local Delivery Addresses";
    array_push($atrib,"mailLocalAddress");
    if ( ! file_exists("/etc/.networksentry-lite")) {
      $descrip["hostedSite"]="Hosted Web Sites";
      array_push($atrib,"hostedSite");
      $descrip["hostedFPSite"]="Front Page Web Sites";
      array_push($atrib,"hostedFPSite");
      array_push($atrib,"mailRoutingAddress");
      array_push($atrib,"mailHost");
    }
  }
*/

  $hidea=array();

  if ((!isset($update)) && ($csave != "click") && ($ds)) {
    $aine = ldap_get_attributes($ds,$entry);

    $aliascnt=$aine["maxAliases"][0];
    $webcnt=$aine["maxWebAliases"][0];

/*
    if ((!isset($aliascnt)) || ($aliascnt == "0")) {
      $hidea["mailLocalAddress"]=true;
      $hidea["mailHost"]=true;
      $hidea["mailRoutingAddress"]=true;
    }
*/

    if ((!isset($webcnt)) || ($webcnt == "0")) {
      $hidea["hostedSite"]=true;
      $hidea["hostedFPSite"]=true;
    }

    for($cnt=0;$cnt < $aine["count"];$cnt++) {
      $attr=$aine[$cnt];
//       print "$attr " . $adata["count"] . " " . $adata[0] . "<BR>";
      if ($cert[$attr] ) {
        $adata=ldap_get_values_len($ds,$entry,strtolower($attr));
        ${$attr}=$adata[0];
      } elseif ($bfile[$attr] ) {
        $adata=ldap_get_values_len($ds,$entry,strtolower($attr));
        $pcount=$adata["count"];
        ${$attr}=$adata[0];        
      } else {
        $adata=ldap_get_values($ds,$entry,$attr);
        if (($adata["count"] > 1) && ($descrip[$attr] != "")){
          $val="";
          for($acnt=0;$acnt<$adata["count"];$acnt++) {
            $info[$attr][$acnt]=$adata[$acnt];
            if ($val != "") {
              $val="$val\r\n$adata[$acnt]";
            } else {
              $val=$adata[$acnt];
            }
          }
          ${$attr}=$val;
        } elseif ($descrip[$attr] != "") {
          $adata=ldap_get_values($ds,$entry,$attr);
          ${$attr}=$adata[0];
        }
      }
    }
  }

  $rejar=array();
  $rok=true;


  while(list($idx,$attr)=each($reqat[$ldtype])) {
    if (${$attr} == "") {
      $rok=false;
      $rejar[$attr]=true;
    }
  }

  $attrhide2=$attrhide[$ldtype];
  while(list($idx,$attr)=each($attrhide[$ldtype])) {
    $hidea[$attr]=true;
  }

  $dnaa=array();
  while(list($idx,$attr)=each($dnattr[$ldtype])) {
    $dnaa[$attr]=true;
  }

?>
<DIV CLASS=content><DIV CLASS=list-color2 ID=headcol>
<DIV CLASS=heading-body><?php print _("Editing User");?> <?php print $cn . " (" . $euser;?>)</DIV></DIV>
<DIV CLASS=list-color1><DIV CLASS=formrow>
<DIV CLASS=formselect ID=genral_but onclick=showdiv('genral',document.uinfo) onmouseover=showdiv('genral',document.uinfo)><?php print _("Genral Settings");?></DIV>
<?php
while(list($divkey,$divval)=each($label)) {
  print "<DIV CLASS=formselect ID=" . $divkey . "_but onclick=showdiv('" . $divkey . "',document.uinfo) onmouseover=showdiv('" . $divkey . "',document.uinfo)>" . $divval . "</DIV>\n";
}
?>
<DIV CLASS=formselect ID=save_but onclick="document.uinfo.csave.value='click';ajaxsubmit('uinfo')" onmouseover=showdiv('save',document.uinfo)><?php print _("Save");?></DIV>
</DIV></DIV>
<DIV id=genral CLASS=formpart>
<TABLE CLASS=formtable>
<?php
  if (((isset($update)) || ($csave == "click")) && ($ds)) {
    $iinfo = ldap_get_entries($ds, $sr);

    if (($ppass1 != $ppass2) && ($ppass1 != "")) {
      print "<TR><TH COLSPAN=2 CLASS=heading-body2><FONT COLOR=RED>Pass Phrase Mismatch</FONT></TH></TR>";
      $certificateGenerate="";
    } else if ((($ppass1 != "") || (($ppassold != "") && ($certupdate != "NEWKEY:"))) && 
        ($c != "") && ($st != "") && ($l != "") && ($o != "") && ($ou != "") && 
        ($cn != "") && ($mail != "")) {
      ?>
      <SCRIPT>
        alert("Certificates Generated Will Need\nTo Be Verifyed By A System Administrator\nThe Certificate Will Not Be Trusted\nUntil Signed.\nOnce Signed You Will Need To\nUpdate Your Certificate With The\nPassphrase.\nUpdating The PKCS#7 File\nDoes Not Require The\nPassphrase.\n");
      </SCRIPT>
<?php
      if ($ppassold != "") {
        if ($ppass1 != "") {
          $newpass=$ppass1;
        } else {
          $newpass=$ppassold;
        }
        $ppass1=$ppassold;
      } else {
        $newpass=$ppass1;
      }
      $certificateGenerate="";
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
          system("/usr/bin/openssl pkcs12 -in $pk12 -password pass:\"$ppass1\" -passout pass:\"$ppass1\" -nocerts -descert -out $keyfile");
          unlink($pk12);
        } else {
          system("/usr/bin/openssl genrsa -des3 -passout pass:\"$ppass1\" -out $keyfile 2048");
        }
        $newreq=`/usr/bin/openssl req -outform der -new -days 365 -passin pass:"$ppass1" -key $keyfile -config $certconf 2>&1`;
        system("/usr/bin/openssl req -x509 -set_serial 0 -new -days 365 -passin pass:\"$ppass1\" -out $x509file -key $keyfile -extensions usr_cert -config $certconf",$retval);
        
        unlink($certconf);
        if ($retval == "0") {
          $userPKCS12=`/usr/bin/openssl pkcs12 -export -descert -inkey $keyfile -in $x509file -password pass:"$newpass" -passin pass:"$ppass1"`;
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
        system("/usr/bin/openssl pkcs12 -in $pk12 -password pass:\"$ppass1\" -passout pass:\"$ppass1\" -nocerts -descert -out $keyfile",$retval);
        unlink($pk12);
        if ($retval == "0") {
          $userPKCS12=`/usr/bin/openssl pkcs12 -export -descert -inkey $keyfile -in $x509file -password pass:"$newpass" -passin pass:"$ppass1"`;
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
      $certificateGenerate="";

      if ($ppassold != "") {
        $pkcs12=ldap_get_values_len($ds,$certs,"userPKCS12");
        $pk12=tempnam("/tmp","sslpk12");
        $pkcs12file=fopen($pk12,"w");
        fwrite($pkcs12file,$pkcs12[0]);
        fclose($pkcs12file);
        $keyfile=tempnam("/tmp","sslkey");
        system("/usr/bin/openssl pkcs12 -in $pk12 -password pass:\"$ppassold\" -passout pass:\"$ppassold\" -nocerts -descert -out $keyfile",$retval);
        unlink($pk12);
        if ($retval == "0") {
          $userPKCS12=`/usr/bin/openssl pkcs12 -export -descert -inkey $keyfile -in $x509file -password pass:"$ppassold" -passin pass:"$ppassold"`;
          if ($userPKCS12 != "") {
            $certsave["userPKCS12"]=$userPKCS12;
          }
          ldap_modify($ds,$dn,$certsave);
        }
        unlink($keyfile);
      }
      unlink($x509file);
    }


    if (($pass1 != $pass2) && ($pass1 != "")) {
      print "<TR><TH COLSPAN=2 CLASS=heading-body2><FONT COLOR=RED>Password Mismatch</FONT></TH></TR>";
      $userPassword="";
    } else if ($pass1 != "") {
      ?>
      <SCRIPT>
        alert("Password Changes Made Here Are Not Real Time\nChanges Will Become Effective On The Next System Resync\nApprox Within 10 Minutes");
      </SCRIPT>
<?php
      $userPassword=$pass1;
    } else {
      $userPassword="";
    }

    if (($iinfo[0]["accountsuspended"][0] == "suspended") && ($accountSuspended ==  "")) { 
?>
      <SCRIPT>
        alert("Account Unsuspentions Are Not Real Time\nChanges Will Become Effective On The Next System Resync\nApprox Within 10 Minutes");
      </SCRIPT>
<?php
    } else if ((($iinfo[0]["accountsuspended"][0] == "unsuspended") || ($iinfo[0]["accountsuspended"][0] == "")) &&
               ($accountSuspended ==  "on")) {
?>
      <SCRIPT>
        alert("Account Suspentions Are Not Real Time\nChanges Will Become Effective On The Next System Resync\nApprox Within 10 Minutes");
      </SCRIPT>
<?php
    } else if (($iinfo[0]["accountsuspended"][0] == "yes") && ($accountSuspended ==  "on")) {
?>
      <SCRIPT>
        alert("Account Suspention Is Pending\nChanges Will Become Effective On The Next System Resync\nApprox Within 10 Minutes");
      </SCRIPT>
<?php
    } else if (($iinfo[0]["accountsuspended"][0] == "no") && ($accountSuspended ==  "")) {
?>
      <SCRIPT>
        alert("Account Unsuspention Is Pending\nChanges Will Become Effective On The Next System Resync\nApprox Within 10 Minutes");
      </SCRIPT>
<?php
    } else if (($iinfo[0]["accountsuspended"][0] == "yes") && ($accountSuspended ==  "")) {
?>
      <SCRIPT>
        alert("Account Suspention Was Pending\nSuspention Has Been Canceled");
      </SCRIPT>
<?php
      $accountSuspended="unsuspended";
    } else if (($iinfo[0]["accountsuspended"][0] == "no") && ($accountSuspended ==  "on")) {
?>
      <SCRIPT>
        alert("Account Unsuspention Was Pending\nSuspention Will Continue");
      </SCRIPT>
<?php
      $accountSuspended="suspended";
    } else if ($iinfo[0]["accountsuspended"][0] != "") {
      $accountSuspended=$iinfo[0]["accountsuspended"][0];
    } else {
      $accountSuspended="unsuspended";
    }

    $aliascnt=$iinfo[0]["maxaliases"][0];
    $webcnt=$iinfo[0]["maxwebaliases"][0];

    $hidea=array();
/*
    if ((!isset($aliascnt)) || ($aliascnt == "0")) {
      $hidea["mailLocalAddress"]=true;
      $hidea["mailHost"]=true;
      $hidea["mailRoutingAddress"]=true;
      $mailHost=$iinfo[0]["mailhost"][0];
      $mailRoutingAddress=$iinfo[0]["mailroutingaddress"][0];
    }
*/
    if ((!isset($webcnt)) || ($webcnt == "0")) {
      $hidea["hostedSite"]=true;
      $hidea["hostedFPSite"]=true;
    }

    $natrib=$atrib;
/*
    if (($mailHost != "") && ($mailRoutingAddress == $euser)) {
      $mailRoutingAddress=$euser . "@" . $mailHost;
    }
*/
    while(list($idx,$catt)=each($natrib)) {
      $ei=ldap_first_entry($ds, $sr);
      if ($catt == "userPassword") {
         if ($userPassword != "") {
           $info["clearPassword"]=$userPassword;
	   if ($dn == $ldn) {
             $info[$catt]="{CRYPT}" . crypt($userPassword);
             $time=time();
             $info["shadowLastChange"]=($time -($time % 86400))/86400;
           }
         } else {
           unset($userPassword);
         }
      } else if ($catt == "certificateGenerate") {
        if (${$catt} != "") {
          $info[$catt]=${$catt};
        } else {
          unset(${$catt});
        }
      } else if ($catt == "cn") {
        $info[$catt]=$cn;
        $info["displayName"]=$cn;
      } else if (($catt == "hostedSite") || ($catt == "hostedFPSite") || ($catt == "mailLocalAddress")) {
        unset(${$catt});
        unset($catt);
      } else if ($b64[$catt]) {
        $data=${$catt};
        if ($data != "") {
          $info[$catt]=$data;
        }
      } elseif ($bfile[$catt]) {
        $adata=ldap_get_values_len($ds,$entry,strtolower($catt));
        $pcount=$adata["count"];
        if ($_FILES[$catt]['name'] != "") {
          $tmpnme=tempnam("/tmp","jpeg");
          if (move_uploaded_file($_FILES[$catt]['tmp_name'],$tmpnme)) {
            $imin=imagecreatefromjpeg($tmpnme);
            if ($imin) {
              $imx=imagesx($imin);
              $imy=imagesy($imin);
              if ( (($imx/$imy) > 0.5) && (($imx/$imy) < 2)) {
                $imlim=800;
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
                  imagejpeg($imout,$tmpnme);
                }
                $cfile=fopen($tmpnme,r);
                $dataout=fread($cfile,filesize($tmpnme));
                fclose($cfile);
                if ($inpindex == "") {
                  for ($pcnt=0;$pcnt < $pcount;$pcnt++) {
                    $info[$catt][$pcnt]=$adata[$pcnt];
                  }
                } else {
                  $pindex=$inpindex;
                  for ($pcnt=0;$pcnt < $pindex;$pcnt++) {
                    $info[$catt][$pcnt]=$adata[$pcnt];
                  }
                  for ($pcnt=$pindex;$pcnt < $pcount;$pcnt++) {
                    $pcinto=$pcnt+1;
                    $info[$catt][$pcinto]=$adata[$pcnt];
                  }
                } 
                if ($pindex == "") {
                  if ($pcount != "") {
                    $info[$catt][$pcount]=$dataout;
                  } else {
                    $info[$catt][0]=$dataout;
                  }
                } else {
                  $info[$catt][$pindex]=$dataout;                  
                }
                $pcount++;
              }
            }
          }
          unlink($tmpnme);
        } elseif ($dpindex != "") {
          for ($savcnt=0;$savcnt < $dpindex;$savcnt++) {
            $info[$catt][$savcnt]=$adata[$savcnt];
          }
          for ($savcnt=$dpindex+1;$savcnt < $pcount;$savcnt++) {
            $info[$catt][$savcnt-1]=$adata[$savcnt];
          }
          $pcount--;
        }
      } elseif ($cbox[$catt]) {
        if (${$catt} == "on") {
          $info[$catt]="yes";
          ${$catt}="yes";
        } else if (${$catt} == "") {
          if ($catt != "pkcs7update") {
            $info[$catt]="no";
            ${$catt}="no";
          }
        } else if (${$catt} != "unset") {
          $info[$catt]=${$catt};
        }
      } elseif ($cert[$catt] ) {
        $cinf = ldap_get_values_len($ds, $ei,strtolower($catt));
        ${$catt}=$cinf[0];
      } else {
        $data=preg_split("/\r\n/",${$catt});
        if (count($data) > 1) {
          $acnt=0;
          for ($cnt=0;$cnt < count($data);$cnt++) {
            if ($data[$cnt] != "") {
              $info[$catt][$acnt]=$data[$cnt];
              $acnt++;
            }
          }
        } elseif ($data[0] != "") {
          $info[$catt]=$data[0];
        }
      }
    }

    while(list($idx,$attr)=each($attrhide2)) {
      $hidea[$attr]=true;
    }

    $dinfo=array();
    $natrib=$atrib;
    $dinfocnt=0;
    while(list($idx,$catt)=each($natrib)) {
      $aname=strtolower($catt);
      if ((count($info[$catt]) == 0) && (count($iinfo[0][$aname]) > 0) && ($hidea[$catt] != "true")){
        if ((! $mline[$catt]) && (!$b64[$catt]) && (!$sline[$catt])) {
          if (($catt != "userPassword") && ($catt != "certificateGenerate" ) && 
              ($catt != "userSMIMECertificate") && ($catt != "userPKCS12" ) && 
              ($catt != "userCertificate;binary") && ($catt != "jpegPhoto" ) &&
              ($catt != "sambaSID")) {
            $dinfocnt++;
            $dinfo[$catt]=$iinfo[0][$aname][0];
            ${$catt}="";
          }
        } elseif ((!$cert[$catt]) || ($sline[$catt])) {
          $info[$catt]="Not Supplied";
          ${$catt}=$info[$catt];
        }
      }
    }

    if ($dinfocnt) {
      $rdel=ldap_mod_del($ds,$dn,$dinfo);
      if (!$rdel) {
        print ldap_error($ds);
        print "<TR><TH COLSPAN=2 CLASS=heading-body2><FONT COLOR=RED>Not Modifyied</FONT></TH></TR>";
      }
    }

    $r=ldap_modify($ds,$dn,$info);
    if (!$r ) {
      print ldap_error($ds);
      print "<TR><TH COLSPAN=2 CLASS=heading-body2><FONT COLOR=RED>Not Modifyied</FONT></TH></TR>";
    }

    $uidarr=array("uidnumber");
    if ($ldtype == "pdc") {
      $uidsr=ldap_search($ds,"ou=Idmap","(&(objectClass=officePerson)(uid=$euser))",$uidarr);
    } else {
      $uidsr=ldap_search($ds,"ou=Users","(&(objectClass=officePerson)(uid=$euser))",$uidarr);
    }
    $uidinfo = ldap_get_entries($ds, $uidsr);
    $uidnum=$uidinfo[0]['uidnumber'][0];

    $fnames=explode(" ",$cn);
    if (count($fnames) <= 1) {
      $fullname=$cn . " ZZZ";
    } else {
      $fullname=$cn;
    }

    pg_query("UPDATE users set fullname='$fullname',email='$mail' WHERE unix_id='$uidnum'");
  } elseif (! $ds) {
    print "<TR><TH COLSPAN=2 CLASS=heading-body2><FONT COLOR=RED>Not Connected To LDAP Server</FONT></TH></TR>";
  }

  if (($ADMIN_USER == "admin") && ($ldtype != "pdc")) {
    print "<TR CLASS=list-color2><TD WIDTH=50% onmouseover=\"myHint.show('uid')\" onmouseout=\"myHint.hide()\">" . _("Username (uid)") . "</TD>";
    print "<TD WIDTH=50%><INPUT TYPE=TEXT SIZE=40 NAME=newuid VALUE=\"" . $euser . "\"></TD></TR>\n\r";
  } else {
    print "<TR CLASS=list-color2><TD WIDTH=50%>" . _("Username (uid)") . "</TD>";
    print "<TD WIDTH=50% CLASS=list-info1>$euser</TD></TR>\n\r";
  }

  print "<INPUT TYPE=HIDDEN NAME=dn VALUE=\"$dn\">";
  print "<INPUT TYPE=HIDDEN NAME=euser VALUE=\"$euser\">";
  
  print "<INPUT TYPE=HIDDEN NAME=owner VALUE=\"$owner\">";
  if ($baseou == "") {
    $baseou="system";
  }
  print "<INPUT TYPE=HIDDEN NAME=baseou VALUE=\"$baseou\">";
  print "<INPUT TYPE=HIDDEN NAME=ldtype VALUE=\"$ldtype\">\n\r";
  
  $cnt=0;

  while(list($attr,$aname)=each($descrip)) {
    if ($attr == "userPassword") {
      $userPassword="";
    }

    if (($label[$attr] != "") && ($hidea[$attr] == ""))  {
      print "</TABLE></DIV><DIV id=" . $attr . " CLASS=formpart><TABLE CLASS=formtable>";
      $cnt=1;
    }

    $rem=$cnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
      $bcolor2=" CLASS=list-color1";
    } else {
      $bcolor=" CLASS=list-color1";
      $bcolor2=" CLASS=list-color2";
    }

    if ((${$attr} == "") && ($attr == "hostedFPSite")) {
      $hidea[$attr]=true;
    }
    if (! $hidea[$attr]) {
        if (($PHP_AUTH_USER == $euser) || (${$attr} != "") || ($ADMIN_USER != "pleb")) {
?>
          <TR <?php print "$bcolor"?>><TD WIDTH=50% onmouseover="myHint.show('<?php print $attr;?>')" onmouseout="myHint.hide()">
<?php
          if (($adduser == "yes") && (($rejar[$attr]) || ($certreqat[$attr]))) {
             print "<FONT COLOR=RED>*</FONT>";
          } else if (($ADMIN_USER != "pleb") && (($rejar[$attr]) || ($certreqat[$attr]))) {
            print "<B>";        
          }
          print $descrip[$attr];
?>
        </TD>
        <TD WIDTH=50% 
<?php
        }
        if (($dnaa[$attr]) || (($PHP_AUTH_USER != $euser) && ($ADMIN_USER == "pleb"))) {
          if (($bfile[$attr] ) && (${$attr} != "")) {
?>
            VALIGN=MIDDLE>
<?php
            if ($pcount > 0) {
?>
            <CENTER>
            <A HREF=/photo/<?php print urlencode($euser);?>.jpg TARGET=_BLANK><IMG SRC=/photo/<?php print urlencode($euser);?>.jpg&imlim=400&type=<?php print $ldtype;?> BORDER=0></A><BR>
            </CENTER>
<?php
            }
          } else if ($cert[$attr]) {
            if (${$attr} != "") {
?>
              ><A HREF=<?php print "/cert/" . $euser . $certext[$attr] . ">/cert/" . $euser .$certext[$attr];?></A><BR>
<?php
            }
          } else if (${$attr} != "") {
            print "$bcolor";
?>
            ><INPUT TYPE=HIDDEN NAME=<?php print $attr;?> VALUE="<?php print ${$attr};?>">
<?php
            if ($attr == "mail") {
              print "<A HREF=\"mailto:" . ${$attr} . "\">" . ${$attr} . "</A>";
            } elseif (strtolower($attr) == "url") {
              print "<A HREF=\"" . ${$attr} . "\" TARGET=_BLANK>" . ${$attr} . "</A>";
            } else {
              print ${$attr};
            }
          }
        } else {
          if (($mline[$attr]) || ($b64[$attr])){
?>
            ><TEXTAREA NAME=<?php print $attr;?> COLS=40 ROWS=5><?php print ${$attr};?></TEXTAREA>
<?php
          } elseif ($bfile[$attr]) {
?>
           VALIGN=MIDDLE>
<?php
           if ($pcount > 0) {
?>
           <CENTER>
           <A HREF=/photo/<?php print urlencode($euser);?>.jpg TARGET=_BLANK><IMG SRC=/photo/<?php print urlencode($euser);?>.jpg&imlim=400&type=<?php print $ldtype;?> BORDER=0></A><BR>
           </CENTER>
<?php
           }
?>
           Select JPEG Image To Add/Insert Into Album It Must Be A Unique Image Duplicates Are Rejected<BR>
           <INPUT TYPE=FILE  NAME=<?php print $attr;?> COLS=40 ROWS=5><BR>
           Chose One Option Bellow<P>
           Insert New Photo <SELECT NAME=inpindex>
             <OPTION VALUE="">
             <OPTION VALUE="">At The End
<?php
           for ($pcnt=0;$pcnt<$pcount;$pcnt++) {
             print "<OPTION VALUE=" . $pcnt . ">At Position " . $pcnt;
           }
           print "</SELECT><BR>";
?>
           Replace Photo<SELECT NAME=pindex>
             <OPTION VALUE="">
<?php
           for ($pcnt=0;$pcnt<$pcount;$pcnt++) {
             print "<OPTION VALUE=" . $pcnt . ">At Position " . $pcnt;
           }
           print "</SELECT><BR>";
?>
           Delete Photo At Position <SELECT NAME=dpindex>
             <OPTION VALUE="">
<?php
           for ($pcnt=0;$pcnt<$pcount;$pcnt++) {
             print "<OPTION VALUE=" . $pcnt . ">" . $pcnt;
           }
           print "</SELECT><BR>";
           print "At Least One Photo Must Be In The Data Base<BR>";
           print "<CENTER><A HREF=/ldap/album.php?euser=" . urlencode($euser) . " TARGET=_blank>My Album</A></CENTER>";
          } elseif ($cert[$attr]) {
            if (${$attr} != "") {
?>
              ><A HREF=<?php print "/cert/" . $euser . $certext[$attr] . ">/cert/" . $euser .$certext[$attr];?></A><BR>
<?php
            } else {
?>
              >Either The Certificate Does Not Exist Or You Dont Own The Certificate
<?php
            }
          } elseif ($cbox[$attr]) {
?>
            ><INPUT TYPE=CHECKBOX  NAME=<?php print $attr;?> <?php if ((${$attr} != "no") && (${$attr} != "") && (${$attr} !="unsuspended") && (${$attr} !="unset")) {print "CHECKED";};?>>
<?php
          } else {
            if ($attr == "userPassword") {
?>
              ><INPUT TYPE=PASSWORD SIZE=40 NAME=pass1 VALUE="">
              <TR <?php print "$bcolor2"?>><TD WIDTH=50% onmouseover="myHint.show('userPassword2')" onmouseout="myHint.hide()">Confirm Password</TD>
              <TD WIDTH=50%>
              <INPUT TYPE=PASSWORD SIZE=40 NAME=pass2 VALUE="">
<?php
              $cnt ++;
            } elseif ($attr == "certificateGenerate") {
?>
              ><INPUT TYPE=PASSWORD SIZE=40 NAME=ppassold VALUE="">
              <TR <?php print "$bcolor2"?>><TD WIDTH=50%>New Pass Phrase (For New Private Key)</TD>
              <TD WIDTH=50%>
              <INPUT TYPE=PASSWORD SIZE=40 NAME=ppass1 VALUE=""></TD></TR>
              <TR <?php print "$bcolor"?>><TD WIDTH=50%>Confirm New Pass Phrase (For New Private Key)</TD>
              <TD WIDTH=50%>
              <INPUT TYPE=PASSWORD SIZE=40 NAME=ppass2 VALUE="">
<?php
              $cnt ++;
              $cnt ++;
?>
              </TD></TR><TR <?php print "$bcolor2";?>><TD WIDTH=50%>Type Of Certificate Update<FONT SIZE=1><BR>Required Items For The Certificate Are Shown In Bold<BR>This Certificate Must Be Signed By A Admin User To Be Trusted</FONT></TD><TD>
              <SELECT NAME=certupdate>
                <OPTION VALUE="">
                <OPTION VALUE="UPDATE:">Update Certificate
                <OPTION VALUE="NEWREQ:">Create A New Public Certificate
                <OPTION VALUE="NEWKEY:">Create A New Private And Public Certificate
              </SELECT>
<?php
              $cnt ++;
	            } else {
?>
              ><INPUT TYPE=TEXT SIZE=40 NAME=<?php print $attr;?> VALUE="<?php print ${$attr};?>">
<?php
            }
          }
        }
?>
      </TD>
    </TR>
<?php	
      if (($PHP_AUTH_USER == $euser) || (${$attr} != "") || ($ADMIN_USER != "pleb")) {
        $cnt ++;
      }
    }
  }
  if (($PHP_AUTH_USER == $euser) || ($ADMIN_USER != "pleb")) {
    print "</TABLE></DIV><DIV id=save CLASS=formpart><TABLE CLASS=formtable>";
    if ($ADMIN_USER == "admin") {
      if (($sambaSID == "") && ($domain != "")) {
        $sambaSID=$domain;
        $data[1]=$domain;
      } else {
        preg_match("/^(S-1-5-21-[0-9]+-[0-9]+-[0-9]+)-[0-9]+/",$sambaSID,$data);
      }
      print "<TR CLASS=list-color2><TD>SMB Domain (SID To Set On Repair)";
      $ssr=ldap_search($ds,"","(&(sambadomainname=*)(sambasid=*))",array("sambaSID","sambaDomainName"));
      $info = ldap_get_entries($ds, $ssr);

      for ($i=0; $i<$info["count"]; $i++) {
        $windom[$i]=$info[$i]["sambadomainname"][0];
      }
      asort($windom);
      reset($windom);

      print "</TD><TD><SELECT NAME=domain>";
      while (list($i,$val) = each($windom)) {
        $disc=$info[$i]["sambadomainname"][0];
        $cname=$info[$i]["sambasid"][0];
        if (!$siddone[$cname]) {
          print "<OPTION VALUE=\"" . $cname . "\"";
          if ($data[1] == $cname) {
            print " SELECTED";
          }
          print ">" . $disc . "\n";
          $siddone[$cname]=true;
        }
      }
      print "</SELECT></TD></TR>";

      print "<TR CLASS=list-color1><TD COLSPAN=2 ALIGN=CENTER>";
      print "<INPUT TYPE=SUBMIT VALUE=\"" . _("Modify") . "\" onclick=this.name='update'>";
      if (( ! file_exists("/etc/.networksentry-lite")) && ($ldtype != "pdc")) {
        print "<INPUT TYPE=BUTTON VALUE=Repair onclick=fix_user()><INPUT TYPE=HIDDEN NAME=fixtex>";
      }
      print "<INPUT TYPE=BUTTON VALUE=Delete onclick=del_user()><INPUT TYPE=HIDDEN NAME=deltex>";
    } else {
      print "<TR CLASS=list-color2><TD COLSPAN=2 ALIGN=CENTER>";
      print "<INPUT TYPE=HIDDEN NAME=csave>";
      print "<INPUT TYPE=SUBMIT VALUE=Modify onclick=this.name='update'>";
    }
    print "<INPUT TYPE=RESET VALUE=Reset></TD></TR>\n";
  }
?>
</TABLE>
</DIV>

</DIV>
</FORM>

<SCRIPT>
document.getElementById(document.uinfo.curdiv.value).style.visibility='visible';
document.getElementById(document.uinfo.curdiv.value+'_but').style.backgroundColor='<?php print $menubg2;?>';
document.getElementById(document.uinfo.curdiv.value+'_but').style.color='<?php print $menufg2;?>';
</SCRIPT>

