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
?>
<CENTER>
<FORM NAME=ppage METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=print>
</FORM>
<FORM METHOD=POST name=macform onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<INPUT TYPE=HIDDEN NAME=classi VALUE="<?php print $euser;?>">
<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=2><?php print _("Mac Scan (ARP List)");?></TH></TR>
<?php
  include "../cdr/auth.inc";
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
    $col++;
    $fp=popen("/usr/sbin/macscan","r");

    $chunk=8192;
    while (!feof($fp)) {
      $output=fgets($fp, $chunk);
      ereg("^\? \(([0-9\.]+)\) at ([0-9a-fA-F\:]+) \[ether\] on (.*)$",$output,$data);
      if ($output != "") {
        $ints[$data[3]][$data[2]]=$data[1];
      }
    }
    fclose($fp);

    asort($ints,SORT_STRING);

    while(list($key,$iface) = each($ints)) {
      print "<TR" . $bcol[$col % 2] . "><TH COLSPAN=2>Interface : " . $key . "</TD></TR>\n";
      $col++;
      asort($iface,SORT_STRING);
      while(list($mac,$ip) = each($iface)) {
        print "<TR" . $bcol[$col % 2] . "><TD>";
        if ($_POST['print'] != "1") {
          print "<A HREF=javascript:openphone('http://" . $ip . "')>" . $ip . "</A></TD><TD>";
          $macq=pg_query($db,"SELECT name,fullname FROM users LEFT OUTER JOIN features ON (exten=name) WHERE snommac = replace(upper('" . $mac . "'),':','')");
          if (pg_num_rows($macq) > 0) {
            $phonem=pg_fetch_array($macq,0);
            $mac="<A HREF=javascript:openextenedit('" . $phonem[0] . "')>" . $mac . " (" . $phonem[0] . " [" . $phonem[1] . "])</A>";
          }
        } else {
          print $ip . "</TD><TD>";
        }
        print $mac . "</TD></TR>\n"; 
        $col++;
      }
    }
    if ($_POST['print'] != "1") {
      print "<TR" . $bcol[$col % 2] . "><TH COLSPAN=2><INPUT TYPE=SUBMIT VALUE=Refresh>";?>
<INPUT TYPE=BUTTON NAME=pbutton VALUE="Print" ONCLICK="printpage(document.ppage)">
<?php
      print "</TH></TR>";
    }
  } else {
    print "<TR CLASS=list-color1><TH CLASS=heading-body2>Administrator Access Required</TH></TR>";
  }
?>
</FORM>
</TABLE>
