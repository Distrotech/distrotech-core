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
include "ldap/auth.inc";
if (isset($addreq)) {
  if ($_FILES['topdf']['name'] != "") {
    $upat=array("'\..*'i");
    $rpat=array(".pdf");
    $fname1=preg_replace($upat,$rpat,$_FILES['topdf']['name']);
    $fname="/var/spool/apache/htdocs/pdf/" . $fname1;
    $tmppdf=tempnam("/tmp","pdfin");
    if (move_uploaded_file($_FILES['topdf']['tmp_name'],$tmppdf)) {
      exec("/usr/bin/gs -dNOPAUSE -q -sPAPERSIZE=a4 -dBATCH -sDEVICE=pdfwrite -sOutputFile=\"" . $fname . "\" " . $tmppdf . ";/usr/bin/uuenview -b -m " . $PHP_AUTH_USER . " \"" . $fname . "\"",$output,$retval);
      unlink($tmppdf);
      header( "Location: /pdf/" . $fname1);
    };
  };
}else {
%>
<CENTER>
  <FORM enctype="multipart/form-data" METHOD=POST TARGET=_BLANK ACTION=ps2pdf.php>

  <TABLE WIDTH=90% cellspacing="0" cellpadding="0">
    <TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=2><%print _("Select A Post Script File To Convert To PDF Format");%></TH></TR>
    <TR CLASS=list-color1>
      <TD  onmouseover="myHint.show('PDF')" onmouseout="myHint.hide()" WIDTH=50%>
        <%print _("File To Be Converted To PDF");%>
      </TD>
      <TD WIDTH=50%>
        <INPUT TYPE=FILE NAME=topdf>
      </TD>
    </TR>
    <TR CLASS=list-color2>
      <TD COLSPAN=2 ALIGN=MIDDLE>
        <INPUT TYPE=SUBMIT NAME=addreq VALUE="Submit Request">
      </TD>
    <TR>
  </TABLE>
  </FORM>
<%
}
%>
