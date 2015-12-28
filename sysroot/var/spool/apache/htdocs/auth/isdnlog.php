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
<FORM METHOD=POST>
<INPUT TYPE=HIDDEN NAME=classi VALUE="<?php print $euser;?>">
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=3 CLASS=heading-body><?php print _("ISDN BRI Error Log");?></TH></TR>
<?php
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(|(cn=Admin Access)(cn=Voip Admin)))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }
  if ($ADMIN_USER == "admin") {
    $col=0;
    $bcol[0]=" CLASS=list-color2";
    $bcol[1]=" CLASS=list-color1";
?>
    <TR CLASS=list-color1>
       <TH CLASS=heading-body2 ALIGN=LEFT>Date</TH>
       <TH CLASS=heading-body2 ALIGN=LEFT>Port</TH>
       <TH CLASS=heading-body2>Error</TH>
    </TR>
<?php
    $fp=popen("grep -vEf /etc/misdnlfilt  /var/log/asterisk/misdn.log |tail -250","r");
    $outa=array();

    $chunk=8192;
    while (!feof($fp)) {
      $output=fgets($fp, $chunk);
      ereg("^[A-Za-z]+ ([A-Za-z]+ [ 0-9][0-9] [0-9:]+) [0-9]{4}: P\[([ 0-9]+)\](.*)",$output,$data);
      if ($output != "") {
        array_push($outa,$data[1] . "</TD><TD>" . $data[2] . "</TD><TD>" . $data[3]);
      }
    }
    fclose($fp);

    for($cnt=0;$cnt < count($outa);) {
      $outp=array_pop($outa);
      print "<TR" . $bcol[$col % 2] . "><TD ALIGN=LEFT>" . $outp . "</TD></TR>";
      $col++;
    }

    print "<TR" . $bcol[$col % 2] . "><TH COLSPAN=3><INPUT TYPE=SUBMIT VALUE=Refresh></TH></TR>";

  } else {
    print "<TR CLASS=list-color1><TH CLASS=heading-body2>Administrator Access Required</TH></TR>";
  }
?>
</FORM>
</TABLE>
