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
include "auth.inc";
?>

<html>

<head>

<base target="_self">
<link rel=stylesheet type=text/css href=/style.php>

</head>
<body>
<CENTER>
<table border="0" width="80%" cellspacing="0" cellpadding="0">
<TR><TD ALIGN=MIDDLE>
<FORM METHOD=POST>
<?php

$abdn="ou=Email";

if (isset($subscribe)) {
    mail("$alias-request@$LOCAL_DOMAIN", "Subscribe Request",
         "subscribe " . $alias . " " . $newmember,
         "From: $newmember\r\n"
        ."X-Mailer: PHP/" . phpversion());
}

    $sobj="(&(sendmailMTAKey=*-list)(objectClass=sendmailMTAAlias))";
    $sr=ldap_search($ds,$abdn,$sobj);
    $info = ldap_get_entries($ds, $sr);

    for ($i=0; $i<$info["count"]; $i++) {
      $srsort[$i]=$info[$i]["sendmailmtakey"][0];
    }
    asort($srsort);
    reset ($srsort);
?>
   Select Mailing List To Join
   <P><SELECT NAME=alias>
<?php
    while (list($i,$val) = each($srsort)) {
      $dn=$info[$i]["dn"];
      $cname=$info[$i]["sendmailmtakey"][0];
      if (! ereg("^owner",$cname)) {
        print "<OPTION VALUE=\"" . $cname . "\">" . $cname . "\n";
      }
    }

    print "</SELECT><P>\n";
    print "Email Address<BR><INPUT TYPE=TEXT NAME=newmember><P>";
    print "<INPUT TYPE=SUBMIT NAME=subscribe VALUE=\"Subscribe\"><P>\n";

?>
</FORM>
  </TD></TR>
</table>
</body>
