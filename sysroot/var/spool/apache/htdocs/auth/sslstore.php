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
if (!isset($_SESSION['auth'])) {
  exit;
}

if ($rdn == "") {
  include "../ldap/auth.inc";
}

if ($_SESSION['classi'] == "") {
  $cuser=$PHP_AUTH_USER;
} else if ($_SESSION['classi'] != "") {
  $cuser=$_SESSION['classi'];
}


if ($bdn == "") {
  $bdnsr=ldap_search($ds,"","(&(objectClass=posixaccount)(uid=$cuser))",array("cn"));
  $bdnei=ldap_first_entry($ds, $bdnsr);
  $bdn=ldap_get_dn($ds,$bdnei);
}

$sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
  $ADMIN_USER="admin";
} else {
  $ADMIN_USER="pleb";
}

if ($delcert != "") {
  ldap_delete($ds,$delcert);
  if ($ADMIN_USER == "admin") {
    touch("/var/spool/apache/htdocs/ns/config/gensshauth");
  }
}
?>
<CENTER>
<FORM enctype="multipart/form-data" METHOD=POST>
<INPUT TYPE=HIDDEN NAME=bdn VALUE="<?php print $bdn;?>">
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>
<?php

if ($_FILES['newcert']['name'] != "") {
  $tmpssl=tempnam("/tmp","sslstore");
  move_uploaded_file($_FILES['newcert']['tmp_name'],$tmpssl);

  print _("Certificate Upload") . "</TH></TR><TR CLASS=list-color1><TD COLSPAN=2><PRE>";
  system("openssl x509 -nameopt multiline -text -noout -in \"$tmpssl\"",$retstat);
  if ($retstat != 0) {
    print "Certificate Error";
  } else {
    $uid=`/usr/bin/openssl x509 -in $tmpssl -noout -hash`;
    $uid=trim($uid);
    $uid=$cuser . "_" . $uid . "#";
    $cn=`/usr/bin/openssl x509 -in $tmpssl -noout -subject -nameopt compat`;
    $cn=trim($cn);
    $dn="uid=" . $uid . "," . $bdn;
    $certsave["objectClass"][0]="top";
    $certsave["objectClass"][1]="userCertStore";
    $certsave["uid"]=$uid;
    $certsave["cn"]=substr($cn,9);
    $certsave["userCertificate;binary"]=`/usr/bin/openssl x509 -in $tmpssl -outform der`;          
    ldap_add($ds,$dn,$certsave);
    $err=ldap_error($dn);
    if ($ADMIN_USER == "admin") {
      touch("/var/spool/apache/htdocs/ns/config/gensshauth");
    }
  }
  print "</PRE></TD></TR><TR CLASS=list-color2><TD ALIGN=MIDDLE COLSPAN=2><INPUT TYPE=SUBMIT VALUE=Continue></TD></TR>";
  unlink($tmpssl);
} else {
  print _("Certificate Store Management") . "</TH></TR>";
  $iarr=array("usercertificate;binary","certificateRequest","cn","uid");
  $sr=ldap_search($ds,$bdn,"(&(objectclass=usercertstore)(usercertificate;binary=*))",$iarr);
  $certcnt=ldap_count_entries($ds,$sr);

  $iinfo=ldap_get_entries($ds,$sr);?>
<TR CLASS=list-color1>
<TD WIDTH=50%>Certificate</TD><TD>
<SELECT NAME=delcert>
<OPTION VALUE="">Select Certificate To Delete Or Add New Certificate Bellow</OPTION>
<?php
  for($cnt=0;$cnt<$iinfo['count'];$cnt++) {
    print "<OPTION VALUE=\"" . htmlspecialchars($iinfo[$cnt]["dn"]) . "\">";
    print " " . $iinfo[$cnt]["cn"][0] . "</OPTION>\n";
  }
?>
</SELECT>
<?php
$rcnt=1;
?>
<TR CLASS=list-color<?php print ($rcnt % 2) +1;$rcnt++?>><TD>
  New Certificate Or Request
</TD><TD>
  <INPUT TYPE=FILE NAME=newcert>
</TD></TR>
<TR CLASS=list-color<?php print ($rcnt % 2) +1;$rcnt++?>><TD ALIGN=MIDDLE COLSPAN=2>
<INPUT TYPE=SUBMIT VALUE="Add/Delete Certificate">
</TD></TR>
<?php
}
?>
</FORM>
</TABLE>
