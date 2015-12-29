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
  if (! $rdn) {
    include "auth.inc";
  }
$adescrip["cn"]=_("Host Name");
$adescrip["uid"]=_("Login Name");
$adescrip["pass1"]=_("Password");
$adescrip["pass2"]=_("Confirm Password");
$adescrip["ipHostNumber"]=_("I.P. Address");
$adescrip["l"]=_("Location");
$adescrip["description"]=_("Discription");

$atrib=array("uid","cn","ipHostNumber","userPassword","l","description");

if ($ds) {
  if (isset($submited)) {
    $dn="uid=$uid,ou=Email";
    $info["objectClass"][0]="device";
    $info["objectClass"][1]="uidObject";
    $info["objectClass"][2]="simpleSecurityObject";
    $info["objectClass"][3]="ipHost";

    if (($pass1 == $pass2) && ($pass1 != "")) {
      $userPassword=crypt($pass1);
      $userPassword="{CRYPT}$userPassword";

      $natrib=$atrib;
      while(list($idx,$catt)=each($natrib)) {
        if (${$catt} != "") {
          $info[$catt]=${$catt};
        }
      }

      if (!ldap_add($ds,$dn,$info)) {
        print "<CENTER><B><H2><FONT COLOR=RED>Not Added - " . ldap_error($ds)  . "</FONT></B></CENTER>";
      } else {
        $euser=$uid;
        include "mserverinfo.php";
        return;
      }
    } else {
        print "<CENTER><B><H2><FONT COLOR=RED>Password Mismatch</FONT></B></CENTER>";
    }
  }
}
?>

<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body><?php print _("Add Mail Server Access Account");?></TH></TR>
<FORM METHOD=POST>
<?php

while(list($attr,$aname)=each($adescrip)) {
  $rem=$cnt % 2;
  if ($rem == 1) {
    $bcolor=" CLASS=list-color2";
  } else {
    $bcolor=" CLASS=list-color1";
  }

?>
  <TR <?php print "$bcolor"?>><TD WIDTH=50% onmouseover="myHint.show('<?php print $attr;?>')" onmouseout="myHint.hide()">
<?php
      print $adescrip[$attr];
?>
    </TD><TD WIDTH=50%>
<?php
    if (($attr == "pass1") || ($attr == "pass2")) {
?>
      <INPUT TYPE=PASSWORD SIZE=40 NAME=<?php print $attr;?> VALUE="">
<?php
    } else {
?>
      <INPUT TYPE=TEXT SIZE=40 NAME=<?php print $attr;?> VALUE="<?php print ${$attr};?>">
<?php
    }
?>
    </TD></TR>
<?php
  $cnt ++;
}
  $rem=$cnt % 2;
  if ($rem != 1) {
    $bgcolor["0"]=" CLASS=list-color1";
    $bgcolor["1"]=" CLASS=list-color2";
  } else {
    $bgcolor["1"]=" CLASS=list-color1";
    $bgcolor["0"]=" CLASS=list-color2";
  }
?>
    <TR <?php print $bgcolor["0"]?>><TD ALIGN=MIDDLE COLSPAN=2 WIDTH=50%>
<?php
  if ($modify) {
    print "<INPUT TYPE=SUBMIT VALUE=Modify NAME=update>";
    print "<INPUT TYPE=SUBMIT VALUE=Delete NAME=delete>";
    print "<INPUT TYPE=RESET VALUE=Reset>";
  } else {
    print "<INPUT TYPE=SUBMIT VALUE=\"" . _("Add") . "\" NAME=submited>";
  }
?>
    </TD></TR>
</TABLE>
</FORM>
