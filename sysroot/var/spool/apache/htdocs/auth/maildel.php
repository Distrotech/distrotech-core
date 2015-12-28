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
  if ((!isset($_SESSION['classi'])) || ($_SESSION['classi'] == "")) {
    $euser=$PHP_AUTH_USER;
  } else {
    $euser=$_SESSION['classi'];
  }

  $disc=array("Remote Delivery Address","Server To Redirect To [In Cluster]","Out Of Office Reply","Use Out Of Office Reply ?");

  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=$ldn)(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

  $months=array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

  $iarr=array("mailroutingaddress","mailhost","outofofficemsg","outofofficeactive");
  $oarr=array("oldmailroutingaddress","oldmailhost","oldoutofofficemsg","oldoutofofficeactive");
  $info=strtolower($info);

  if ($oldooactive == "unset") {
    $oldooactive="";
  }
  if (isset($modrec)) {
    if (($oldooactive == "active") && ($outofofficeactive ==  "")) {
?>
      <SCRIPT>
        alert("Out Of Office Responce Will Be Disabled\nChanges Will Become Effective On The Next System Resync\nApprox Within 10 Minutes");
      </SCRIPT>
<?php
     } else if (($oldooactive == "") && ($outofofficeactive ==  "on")) {
?>
      <SCRIPT>
        alert("Out Of Office Responce Will Be Activated\nChanges Will Become Effective On The Next System Resync\nApprox Within 10 Minutes\nPlease Note It Will Not Be Activated Unless The User Has Loged Into Mail");
      </SCRIPT>
<?php
     } else if (($oldooactive == "yes") && ($outofofficeactive ==  "on")) {
?>
      <SCRIPT>
        alert("Out Of Office Activation Pending\nChanges Will Become Effective On The Next System Resync\nApprox Within 10 Minutes\nPlease Note It Will Not Be Activated Unless The User Has Loged Into Mail");
      </SCRIPT>
<?php
     } else if (($oldooactive == "no") && ($outofofficeactive ==  "")) {
?>
      <SCRIPT>
        alert("Out Of Office Dectivation Pending\nChanges Will Become Effective On The Next System Resync\nApprox Within 10 Minutes");
      </SCRIPT>
<?php
     } else if (($oldooactive == "no") && ($outofofficeactive ==  "on")) {
?>
      <SCRIPT>
        alert("Out Of Office Deactivation Canceled");
      </SCRIPT>
<?php
      $outofofficeactive="active";
     } else if (($oldooactive == "yes") && ($outofofficeactive ==  "")) {
?>
      <SCRIPT>
        alert("Out Of Office Activation Canceled");
      </SCRIPT>
<?php
       $outofofficeactive="unset";
    } else if ($oldooactive == "active") {
      $outofofficeactive="active";
    } else if ($oldooactive == "") {
      $outofofficeactive="unset";
    }

    if ($outofofficeactive == "on") {
      $outofofficeactive="yes";
    } else if ($outofofficeactive == "") {
      $outofofficeactive="no";
    }

    for($i=0;$i<count($iarr);$i++) {
      if ($$iarr[$i] != "") {
        $minfo[$iarr[$i]]=$$iarr[$i];
      } else {
        if ($$oarr[$i] != "") {
          $delinfo[$iarr[$i]]=$$oarr[$i];
          $delcnt ++; 
        }
      }
    }
    if ($delcnt > 0) {
      ldap_mod_del($ds,$dn,$delinfo);
    }
    ldap_modify($ds,$dn,$minfo);
  }

  $sr=ldap_search($ds,"","(&(objectClass=officeperson)(uid=$euser))",$iarr);
  $iinfo = ldap_get_entries($ds, $sr);
  $dn=$iinfo[0]['dn'];

?>

<FORM METHOD=POST NAME=mdelform onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=classi VALUE="<?php print $euser;?>">
<INPUT TYPE=HIDDEN NAME=dn VALUE="<?php print $dn;?>">
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>Editing Mail Delivery</TH></TR>
<?php
  for ($i=0; $i < count($iarr); $i++) {
    $rem=$i % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color1";
    }
?>
    <TR<?php print $bcolor;?>>
      <TD WIDTH=75% onmouseover="myHint.show('<?php print $i;?>')" onmouseout="myHint.hide()">
        <?php print $disc[$i];?>
      </TD>
      <TD>
<?php
        $attr=$iarr[$i];
        print "<INPUT TYPE=HIDDEN NAME=old" . $attr . " VALUE=" . $iinfo[0][$attr][0] . ">";
        if ($i < 2) {
          print "<INPUT TYPE=TEXT NAME=" . $attr . " SIZE=40 VALUE=" . $iinfo[0][$attr][0] . ">";
        } else if ($i == "2"){ 
          print "<TEXTAREA NAME=" . $attr . " COLS=40 ROWS=5>" . $iinfo[0][$attr][0] . "</TEXTAREA>";
        } else {
          print "<INPUT TYPE=HIDDEN NAME=oldooactive VALUE=" . $iinfo[0][$attr][0] . ">";
          print "<INPUT TYPE=CHECKBOX NAME=" . $attr;
          if (($iinfo[0][$attr][0] != "") && ($iinfo[0][$attr][0] != "no") && ($iinfo[0][$attr][0] != "unset")) {
            print " CHECKED>";
          } else {
            print ">";
          }
        }
?>
      </TD></TR>
<?php
  }
  $rem=$i % 2;
  if ($rem == 1) {
    $bcol[1]=" CLASS=list-color1";
    $bcol[2]=" CLASS=list-color2";
  } else {
    $bcol[2]=" CLASS=list-color1";
    $bcol[1]=" CLASS=list-color2";
  }
?>
<TR <?php print $bcol[2];?>><TH COLSPAN=2>
  <INPUT TYPE=SUBMIT VALUE="Modify" NAME=modrec></TH></TR>
</TABLE></FORM>
