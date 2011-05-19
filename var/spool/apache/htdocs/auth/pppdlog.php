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
%>
<CENTER>
<FORM METHOD=POST NAME=ppplog onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH CLASS=heading-body><%print _("Current PPP Status");%></TH></TR>
<%
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }
  if ($ADMIN_USER == "admin") {
    $col=0;
    $bcol[0]=" CLASS=list-color2";
    $bcol[1]=" CLASS=list-color1";
    for ($pcnt=0;$pcnt<=9;$pcnt++) {
      if ((is_file("/var/log/pppd.log." . $pcnt)) && (is_file("/etc/ifconf/pppup.ppp" . $pcnt))) {
        $fp=fopen("/var/log/pppd.log." . $pcnt,"r");
        $col++;
        print "<TR" . $bcol[$col % 2] . "><TH CLASS=heading-body2>" . _("Interface") . " PPP" . $pcnt;
        $col++;

        $chunk=8192;
        while (!feof($fp)) {
          $output=fgets($fp, $chunk);
          if (!ereg("(^Couldn't increase M[TR]U to 1500)|(pppoe: Interface eth[0-9a-zA-Z] has MTU of 1450)",$output)) {
            print "</TH></TR><TR" . $bcol[$col % 2] . "><TD>" . $output . "</TD></TR>\n";
            $col++;
          }
        }
        fclose($fp);
        print "</PRE></TD></TR>\n";
      }
    }

    if ((is_file("/var/log/pppd.log.3g")) && (is_file("/etc/ifconf/pppup.ppp3g"))) {
      $fp=fopen("/var/log/pppd.log.3g","r");
      $col++;
      print "<TR" . $bcol[$col % 2] . "><TH CLASS=heading-body2>" . _("3G Interface") . " PPP10";
      $col++;

      $chunk=8192;
      while (!feof($fp)) {
        $output=fgets($fp, $chunk);
        if (!ereg("(^Couldn't increase M[TR]U to 1500)|(pppoe: Interface eth[0-9a-zA-Z] has MTU of 1450)",$output)) {
          print "</TH></TR><TR" . $bcol[$col % 2] . "><TD>" . $output . "</TD></TR>\n";
          $col++;
        }
      }
      fclose($fp);
      print "</PRE></TD></TR>\n";
    }

    $col++;
    print "<TR" . $bcol[$col % 2] . "><TH><INPUT TYPE=SUBMIT VALUE=Refresh></TH></TR>";
  } else {
    print "<TR CLASS=list-color1><TH CLASS=heading-body2>Administrator Access Required</TH></TR>";
  }
%>
</FORM>
</TABLE>
