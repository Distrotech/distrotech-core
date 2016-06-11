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
?>
<html>

<head>

<base target="_self">

<CENTER>

<?php
if (! isset($addreq)) {
?>
  <FORM enctype="multipart/form-data" METHOD=POST>
  <TABLE WIDTH=90% cellspacing="0" cellpadding="0">
    <TR CLASS=list-color2>
      <TH COLSPAN=2 CLASS=heading-body><?php print _("Select A Request To Sign");?></TH>
    </TR>
    <TR CLASS=list-color1>
      <TD WIDTH=50% onmouseover="myHint.show('SS0')" onmouseout="myHint.hide()">
        <?php print _("Certificate Request To Be Signed");?>;
      </TD>
      <TD WIDTH=50%>
        <INPUT TYPE=FILE NAME=cert>
      </TD>
    </TR>
    <TR CLASS=list-color2>
      <TD COLSPAN=2 ALIGN=MIDDLE>
        <INPUT TYPE=SUBMIT NAME=addreq VALUE="<?php print _("Submit Request");?>">
      </TD>
    <TR>
  </TABLE>
  </FORM>
<?php
} else if ($addreq == _("Submit Request")) {
?>
  <FORM METHOD=POST>
  <TABLE WIDTH=90% cellspacing="0" cellpadding="0">
    <TR CLASS=list-color2>
      <TH COLSPAN=2 CLASS=heading-body>
<?php
  print "$reqfile<BR>";

  if ($_FILES['cert']['name'] != "") {
    $reqfile=
    $tmpssl=tempnam("/tmp","sslreq");
    move_uploaded_file($_FILES['cert']['tmp_name'],$tmpssl);
    print "Confirm Certificate</TH></TR>";
    print "<TR CLASS=list-color1>";
    print "<TD COLSPAN=2><FONT SIZE=1><PRE>";
    system("openssl req -nameopt multiline -text -noout -in \"$tmpssl\"",$retstat); 
    print "&nbsp;</PRE></TD></TR>";
    if ($retstat != "0") {
      print "<TR CLASS=list-color2><TH CLASS=heading-body2 COLSPAN=2>" .  _("The File Submited Is Not A Valid Certificate Request") . "</TH></TR>";
    } else {
?>
      <TR CLASS=list-color2>
        <TD WIDTH=50% onmouseover="myHint.show('SS1')" onmouseout="myHint.hide()"><?php print _("Email Address To Send Links For Download");?>
      </TD>
        <TD ALIGN=LEFT WIDTH=50%><INPUT TYPE=TEXT NAME=email></TD>
      </TR>
      <TR CLASS=list-color1>
        <TD WIDTH=50% onmouseover="myHint.show('SS2')" onmouseout="myHint.hide()"><?php print _("Is This Certificate To Be Signed As A CA Certificate");?>
      </TD>
        <TD ALIGN=LEFT WIDTH=50%><INPUT TYPE=CHECKBOX NAME=cert_ca></TD>
      </TR>
      <TR CLASS=list-color2>
        <TD COLSPAN=2 ALIGN=MIDDLE>
          <INPUT TYPE=HIDDEN NAME=reqfile VALUE="<?php print $_FILES['cert']['name'];?>">
          <INPUT TYPE=HIDDEN NAME=tmpfile VALUE="<?php print $tmpssl;?>">
          <INPUT TYPE=SUBMIT NAME=addreq VALUE="Sign Request">
        </TD>
      <TR>
<?php
    }
  } else {
    print _("There is a existing request for this certificate.") . "</TH></TR>";
  }
?>
  </TABLE>   
  </FORM>
<?php
} else if ($addreq == "Sign Request") {
?>
  <TABLE WIDTH=90% cellspacing="0" cellpadding="0">
    <TR CLASS=list-color2>
      <TD>
<?php
        $cert=file_get_contents("$tmpfile");
        $cname=bin2hex(mhash(MHASH_CRC32,$cert));
        if ($cert_ca == "on") {
          $urlloc="X509CA";
          $storedir="casign";
        } else {
          $urlloc="X509";
          $storedir="sign";
        }
        system("openssl req -out \"/var/spool/apache/htdocs/ssl/" . $storedir . "/" . $cname . ".pem\" -in \"$tmpfile\"",$signstat);
        if (($signstat == 0) && ( ! is_file("/var/spool/apache/htdocs/" . $urlloc . "/$cname.p7b"))) {
          print _("This Request Will Be Signed Shortly (With The Next System Update) Follow The Bellow Links To Retrive It") . "<BR>";
          print _("Each File Is Assigned A Unique File Name This Certificates ID IS $cname With A Filename Of") . " $cname.p7b " . _("Or") . " $cname.pem";
          print "<P>Please Note If The Certificate Already Exists Or If It Matches The Subjecct Of A Existing Certificate The File Will Not Be Signed</P>";
          print "<BR><A HREF=/" . $urlloc . "/" . urlencode($cname . ".p7b") . ">PKCS#7 Signed Request With CA And CRL Certificates</A>";
          print "<BR><A HREF=/" . $urlloc . "/" . urlencode($cname . ".pem") . ">Signed Request</A>";
          mail($email,"Certificate Signed","Please Obtain The Certificate From\n
http://" . $SERVER_NAME . "/" . $urlloc . "/" . urlencode($cname . ".p7b") . 
" (PKCS#7 DER Encoded File) \nOr
http://" . $SERVER_NAME . "/" . $urlloc . "/" . urlencode($cname . ".pem") . " (PEM Encoded X509 Certificate)

Should The File Not Be Found After 10 Minutes Please Contact The Administrator.

Duplicate Requests Will Not Be Signed."
,"From: Certificate Authority <root@" . $SERVER_NAME .">");
        } else if (($signstat == 0) && (is_file("/var/spool/apache/htdocs/" . $urlloc . "/$cname.p7b"))) {
          print "The Certificate Already Exists<P>";
          print "Each File Is Assigned A Unique File Name This Certificates ID IS $cname With A Filename Of $cname.p7b Or $cname.pem<P>";
          print "<BR><A HREF=/" . $urlloc . "/" . urlencode($cname . ".p7b") . ">PKCS#7 Signed Request With CA And CRL Certificates</A>";
          print "<BR><A HREF=/" . $urlloc . "/" . urlencode($cname . ".pem") . ">Signed Request</A>";
          unlink("/var/spool/apache/htdocs/ssl/" . $storedir . "/$cname.pem"); 
          mail($email,"Certificate Already Signed","Please Obtain The Certificate From\n
http://" . $SERVER_NAME . "/" . $urlloc . "/" . urlencode($cname . ".p7b") . 
" (PKCS#7 DER Encoded File) \nOr
http://" . $SERVER_NAME . "/" . $urlloc . "/" . urlencode($cname . ".pem") . " (PEM Encoded X509 Certificate)

Should The File Not Be Found After 10 Minutes Please Contact The Administrator.

Duplicate Requests Will Not Be Signed."
,"From: Certificate Authority <root@" . $SERVER_NAME .">");
       } else {
          print "A Unknown Error Occured The Request Will Not Be Signed";
          unlink("/var/spool/apache/htdocs/ssl/sign/$cname.pem"); 
          mail($email,"Certificate Error","A Unexpected Error Was Encountered Please Contact The System Administrator

The Certificate Was Not Signed
","From: Certificate Authority <root@" . $SERVER_NAME .">");
       }
      ?>
      </TD>
    </TD>
  </TABLE>   
<?php
} else {
  print "$addreq";
}
?>
