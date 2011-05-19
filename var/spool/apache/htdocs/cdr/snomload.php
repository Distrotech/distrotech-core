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
include "ldap/auth.inc";


function writeconf($phone,$file) {
      $newver=explode("-",$file);
      $newverr=explode(".",$newver[1]);
      $fwareld=fopen("/var/spool/apache/htdocs/snom/snom" . $phone . "-fw.php","w");
      fputs($fwareld,"<%
\$phone=\"" . $phone . "\";
\$newver=\"" . $newverr[0] . "\";
\$firmware_file=\"" . $file . "\";
include \"snom-fw.php\";
%>\n");
      fclose($fwareld);
}

if (isset($addreq)) {%>
  <CENTER>
  <TABLE WIDTH=90% cellspacing="0" cellpadding="0"><%
  print "<TR CLASS=list-color2><TH CLASS=heading-body2>";
  if ($_FILES['snom300']['name'] != "") {
    if (move_uploaded_file($_FILES['snom300']['tmp_name'],"/var/spool/apache/htdocs/snom/" . $_FILES['snom300']['name'])) {
      Print _("Successfully Loaded Snom 300 Firmware File");
      writeconf("300",$_FILES['snom300']['name']);
    } else {
      Print _("Failed Loading Snom 300 Firmware File");
    }
  } else {
    Print _("Failed Loading Snom 300 Invalid File");
  }
  print "</TH></TR>\n";

  print "<TR CLASS=list-color1><TH CLASS=heading-body2>";
  if ($_FILES['snom320']['name'] != "") {
    if (move_uploaded_file($_FILES['snom320']['tmp_name'],"/var/spool/apache/htdocs/snom/" . $_FILES['snom320']['name'])) {
      Print _("Successfully Loaded Snom 320 Firmware File");
      writeconf("320",$_FILES['snom320']['name']);
    } else {
      Print _("Failed Loading Snom 320 Firmware File");
    }
  } else {
    Print _("Failed Loading Snom 320 Invalid File");
  }
  print "</TH></TR>\n";

  print "<TR CLASS=list-color2><TH CLASS=heading-body2>";
  if ($_FILES['snom360']['name'] != "") {
    if (move_uploaded_file($_FILES['snom360']['tmp_name'],"/var/spool/apache/htdocs/snom/" . $_FILES['snom360']['name'])) {
      Print _("Successfully Loaded Snom 360 Firmware File");
      writeconf("360",$_FILES['snom360']['name']);
    } else {
      Print _("Failed Loading Snom 360 Firmware File");
    }
  } else {
    Print _("Failed Loading Snom 360 Invalid File");
  }
  print "</TH></TR>\n";

  print "<TR CLASS=list-color1><TH CLASS=heading-body2>";
  if ($_FILES['snom370']['name'] != "") {
    if (move_uploaded_file($_FILES['snom370']['tmp_name'],"/var/spool/apache/htdocs/snom/" . $_FILES['snom370']['name'])) {
      Print _("Successfully Loaded Snom 370 Firmware File");
      writeconf("370",$_FILES['snom370']['name']);
    } else {
      Print _("Failed Loading Snom 370 Firmware File");
    }
  } else {
    Print _("Failed Loading Snom 370 Invalid File");
  }
  print "</TH></TR>\n";

  print "<TR CLASS=list-color1><TH CLASS=heading-body2>";
  if ($_FILES['snom820']['name'] != "") {
    if (move_uploaded_file($_FILES['snom820']['tmp_name'],"/var/spool/apache/htdocs/snom/" . $_FILES['snom820']['name'])) {
      Print _("Successfully Loaded Snom 820 Firmware File");
      writeconf("820",$_FILES['snom820']['name']);
    } else {
      Print _("Failed Loading Snom 820 Firmware File");
    }
  } else {
    Print _("Failed Loading Snom 820 Invalid File");
  }
  print "</TH></TR>\n";

  print "<TR CLASS=list-color1><TH CLASS=heading-body1>";
  if ($_FILES['snom870']['name'] != "") {
    if (move_uploaded_file($_FILES['snom870']['tmp_name'],"/var/spool/apache/htdocs/snom/" . $_FILES['snom870']['name'])) {
      Print _("Successfully Loaded Snom 870 Firmware File");
      writeconf("870",$_FILES['snom870']['name']);
    } else {
      Print _("Failed Loading Snom 870 Firmware File");
    }
  } else {
    Print _("Failed Loading Snom 870 Invalid File");
  }
  print "</TH></TR>\n";

  print "<TR CLASS=list-color1><TH CLASS=heading-body2>";
  if ($_FILES['snomMP']['name'] != "") {
    if (move_uploaded_file($_FILES['snomMP']['tmp_name'],"/var/spool/apache/htdocs/snom/" . $_FILES['snomMP']['name'])) {
      Print _("Successfully Loaded Snom MP Firmware File");
      writeconf("MP",$_FILES['snomMP']['name']);
    } else {
      Print _("Failed Loading Snom MP Firmware File");
    }
  } else {
    Print _("Failed Loading Snom MP Invalid File");
  }
  print "</TH></TR>\n";


  print "</TABLE>";
}else {
%>
<CENTER>
  <FORM enctype="multipart/form-data" METHOD=POST>
  <TABLE WIDTH=90% cellspacing="0" cellpadding="0">
    <TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body><%print _("Please Upload The Firmware Files For The Following Snom Phones");%></TH></TR>
    <TR CLASS=list-color1>
      <TD onmouseover="myHint.show('SL0')" onmouseout="myHint.hide()" WIDTH=50%>
        Snom 300
      </TD>
      <TD WIDTH=50%>
        <INPUT TYPE=FILE NAME=snom300>
      </TD>
    </TR>
    <TR CLASS=list-color2>
      <TD onmouseover="myHint.show('SL1')" onmouseout="myHint.hide()" WIDTH=50%>
        Snom 320
      </TD>
      <TD WIDTH=50%>
        <INPUT TYPE=FILE NAME=snom320>
      </TD>
    </TR>
    <TR CLASS=list-color1>
      <TD WIDTH=50% onmouseover="myHint.show('SL2')" onmouseout="myHint.hide()">
        Snom 360
      </TD>
      <TD WIDTH=50%>
        <INPUT TYPE=FILE NAME=snom360>
      </TD>
    </TR>
    <TR CLASS=list-color2>
      <TD WIDTH=50% onmouseover="myHint.show('SL2')" onmouseout="myHint.hide()">
        Snom 370
      </TD>
      <TD WIDTH=50%>
        <INPUT TYPE=FILE NAME=snom370>
      </TD>
    </TR>
    <TR CLASS=list-color1>
      <TD WIDTH=50% onmouseover="myHint.show('SL2')" onmouseout="myHint.hide()">
        Snom 820
      </TD>
      <TD WIDTH=50%>
        <INPUT TYPE=FILE NAME=snom820>
      </TD>
    </TR>
    <TR CLASS=list-color2>
      <TD WIDTH=50% onmouseover="myHint.show('SL2')" onmouseout="myHint.hide()">
        Snom 870
      </TD>
      <TD WIDTH=50%>
        <INPUT TYPE=FILE NAME=snom870>
      </TD>
    </TR>
    <TR CLASS=list-color1>
      <TD WIDTH=50% onmouseover="myHint.show('SL2')" onmouseout="myHint.hide()">
        Snom MP
      </TD>
      <TD WIDTH=50%>
        <INPUT TYPE=FILE NAME=snomMP>
      </TD>
    </TR>
    <TR CLASS=list-color2>
      <TD COLSPAN=2 ALIGN=MIDDLE>
        <INPUT TYPE=SUBMIT NAME=addreq VALUE="<%print _("Submit Request");%>">
      </TD>
    <TR>
  </TABLE>
  </FORM>
<%
}
%>
