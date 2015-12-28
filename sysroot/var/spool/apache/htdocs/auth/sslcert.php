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

if ((!isset($cuser)) && ($_SESSION['classi'] == "")) {
  $cuser=$PHP_AUTH_USER;
} else if ((!isset($cuser)) && ($_SESSION['classi'] != "")) {
  $cuser=$_SESSION['classi'];
}

?>
<CENTER>
<FORM METHOD=POST>
<INPUT TYPE=HIDDEN NAME=classi VALUE="<?php print $cuser;?>">
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<?php
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }


  $iarr=array("usercertificate;binary","certificateRequest","cn","uid");
  $bdnsr=ldap_search($ds,"","(&(|(objectClass=inetorgperson)(objectClass=usercertstore))(uid=$cuser))",array("cn"));
  $bdnei=ldap_first_entry($ds, $bdnsr);
  $bdn=ldap_get_dn($ds,$bdnei);

  if (isset($shcert)) {
    $sr=ldap_search($ds,$bdn,"(&(|(objectClass=inetorgperson)(objectclass=usercertstore))(usercertificate;binary=*)(uid=" . $cuser . "))",$iarr);
  } else {
    $sr=ldap_search($ds,$bdn,"(&(|(objectClass=inetorgperson)(objectclass=usercertstore))(usercertificate;binary=*))",$iarr);
  }
  $certcnt=ldap_count_entries($ds,$sr);

  if ($certcnt > 1) {
    $iinfo=ldap_get_entries($ds,$sr);?>
<TR CLASS=list-color2><TH CLASS=heading-body>
<?php print _("Select Certificate To View");?></TH></TR>
<TR CLASS=list-color1><TD ALIGN=MIDDLE>
<SELECT NAME=cuser>
<?php

    for($cnt=0;$cnt<$iinfo['count'];$cnt++) {
      print "<OPTION VALUE=\"" . $iinfo[$cnt]["uid"][0] . "\">";
      if ($iinfo[$cnt]["certificateRequest"][0] != "") {
        print _("Certificate Request");
      } else {
        print _("Trusted Certificate");
      }
      print " " . $iinfo[$cnt]["cn"][0] . " (" . $iinfo[$cnt]["uid"][0] . ")</OPTION>\n";
    }

?>
</SELECT>
</TD></TR>
<TR CLASS=list-color2><TD ALIGN=MIDDLE>
<INPUT TYPE=SUBMIT NAME=shcert VALUE="View Certificate">
</TD>
<?php
  } else {
    $ei=ldap_first_entry($ds, $sr);
    $cinfo["uid"]=ldap_get_values($ds,$ei,"uid");
    $cinfo["cn"]=ldap_get_values($ds,$ei,"cn");
    if (isset($signcert)) {
     $minfo["certificatesign"]=yes;
     ldap_modify($ds,ldap_get_dn($ds,$ei),$minfo);
?>
        <SCRIPT>
          alert("Certificate Changes Made Here Are Not Real Time\nChanges Will Become Effective On The Next System Resync\nApprox Within 10 Minutes");
        </SCRIPT>
<?php
    }

    $cinf = ldap_get_values_len($ds, $ei,"usercertificate;binary");
    $rinf = ldap_get_values_len($ds, $ei,"certificateRequest");
    $tmpssl=tempnam("/tmp","sslcert");
    $certfile=fopen($tmpssl,"w");
    if ($rinf[0] != "") {
      fputs($certfile,$rinf[0]);
      $certtype=_("Certificate Request");
    } else {
      fputs($certfile,$cinf[0]);
      $certtype=_("Trusted Certificate");
    }
    fclose($certfile);
?>
<TR CLASS=list-color2><TH CLASS=heading-body>
<?php print _("SSL Certificate") . " (" . $certtype . ")";?>
</TH></TR>
<TR CLASS=list-color1><TH CLASS=heading-body2>
<?php print $cinfo["uid"][0] . " - " . $cinfo["cn"][0];?>
</TH></TR>
<TR CLASS=list-color2>
<TD><FONT SIZE=2><PRE><?php
    if ($rinf[0] != "") {
      system("openssl req -text -noout -inform der -in \"$tmpssl\"",$retstat);
    } else {
      system("openssl x509 -text -noout -nameopt multiline -inform der -in \"$tmpssl\"",$retstat);
    }
    unlink($tmpssl);
?>
</PRE></TD></TR>
<?php
  if (($ADMIN_USER == "admin") && (! isset($signcert)) && ($rinf[0] != "")){
?>
<TR CLASS=list-color1><TD ALIGN=MIDDLE>
  <INPUT TYPE=SUBMIT NAME=signcert VALUE="Sign Request" onmouseover="myHint.show('Sign')" onmouseout=myHint.hide()>
</TD></TR>
<?php
  }
}
?>
</FORM>
</TABLE>
