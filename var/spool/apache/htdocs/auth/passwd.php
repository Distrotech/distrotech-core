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

  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }
%>
<CENTER>
<FORM METHOD=POST NAME=apwform onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% CELLSPACING=0 CELLPADDING=0>
<%
  if (($pass1 == $pass2) && (isset($uppass)) && ($ADMIN_USER == "admin")) {
    $htaccess=file("/var/spool/apache/htdocs/ns/config/netsentry.conf");
    while(list($lnum,$ldata)=each($htaccess)) {
      if (preg_match("/^(IP LDAP Login )(.*)/i",$ldata, $matches)) {
        $ldapuser=$matches[2];
      } elseif (preg_match("/^IP LDAP Password (.*)/",$ldata,$pwdat)) {
        $pwlnum=$lnum;
	$origpw=$pwdat[1];
      } elseif (preg_match("/^IP LDAP OPassword (.*)/",$ldata,$opwdat)) {
        $opwlnum=$lnum;
      }
      $newdata[$lnum]=$ldata;
    }

    if ($ldapuser != "") {
      $newdata[$pwlnum]="IP LDAP Password $pass1\r\n";
      if ($opwlnum != "") {
        $newdata[$opwlnum]="IP LDAP OPassword $origpw\r\n";
      } else {
        array_push($newdata,"IP LDAP OPassword $origpw\r\n");
      }
      $datain=implode($newdata);
      $fname="/var/spool/apache/htdocs/ns/config/netsentry.conf";
      $cfile=fopen($fname,w);
      chmod($fname,0660);
      fwrite($cfile,$datain);
      fclose($cfile);
      system("/usr/sbin/genconf > /dev/null 2>&1");       
      print "<TR CLASS=list-color2><TH>" . _("Password Changed") . "</TH></TR></TABLE>";
      return;
    } else {
      print "<TR CLASS=list-color2><TH COLSPAN=2>" . _("Change Failed") . "</TH></TR>";
      return;
    }
  } else if (isset($uppass)) {
    print "<TR CLASS=list-color2><TH COLSPAN=2>" . _("Password Mismatch") . "</TH></TR>";
    return;
  } else {
    if ($ADMIN_USER == "admin") {
%>
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>Changing Admin Password</TH></TR>
<TR CLASS=list-color1><TD onmouseover="myHint.show('AP0')" onmouseout="myHint.hide()"WIDTH=50%><%print _("New Password");%></TD><TD><INPUT TYPE=PASSWORD NAME=pass1></TD></TR>
<TR CLASS=list-color2><TD onmouseover="myHint.show('AP1')" onmouseout="myHint.hide()"><%print _("Confirm");%></TD><TD><INPUT TYPE=PASSWORD NAME=pass2></TD></TR>
<TR CLASS=list-color1><TD COLSPAN=2 ALIGN=MIDDLE><INPUT TYPE=SUBMIT onclick=this.name='uppass' VALUE="<%print _("Update Password");%>"></TD></TR>
<%
    } else {
      print "<TR><TH>" . _("Administrive Access Is Required") . "</TH></TR>";
    }
  }
%>
</FORM>
</TABLE>

