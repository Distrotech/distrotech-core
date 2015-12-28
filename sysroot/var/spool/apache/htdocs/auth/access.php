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

  if (($_SESSION['utype'] != "pdc") && ($_SESSION['utype'] != "system") && ($_SESSION['utype'] != "")) {
    $sr=ldap_search($ds,"cn=" .  $_SESSION['utype'] . ",ou=VAdmin","(&(objectclass=virtZoneSettings)(member=" . $ldn . "))");
    $vadmin=1;
  } else {
    $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
    $vadmin=0;
  }
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

$disc=array(_("Allow Radius Access (Global Setting)"),
            _("Suspended"),_("Maximum Email Aliases"),_("Maximum Mail Boxes"),_("Maximum Web Aliases"));


$iarr2=array("dialupAccess","accountSuspended");
for($lacnt=0;$lacnt<count($iarr2);$lacnt++) {
  $iarr2[$lacnt]=strtolower($iarr2[$lacnt]);
  $vadminattr[$iarr2[$lacnt]]=1;
}

?>
<FORM METHOD=POST NAME=laccform onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=classi VALUE="<?php print $euser;?>">
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>
<?php
  if ($ADMIN_USER == "admin") {
    print _("Editing Access Control");
  } else {
    print _("Viewing Access Control");
  } 
?>
</TH></TR>
<?php
  $iarr=array("dialupAccess","accountSuspended","maxAliases","maxMailBoxes","maxWebAliases");
  $dnarr=array("dn","accountSuspended","dialupAccess");
  $info=strtolower($info);

  $sr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=$euser))",$dnarr);
 
  $dinfo = ldap_get_entries($ds, $sr);
  $dn=$dinfo[0]["dn"];

  
  if (isset($modrec)) {
    $susstatus=$dinfo[0]["accountsuspended"][0];

    if (($susstatus == "suspended") && ($accountSuspended ==  "")) {
?>
      <SCRIPT>
        alert("Account Unsuspentions Are Not Real Time\nChanges Will Become Effective On The Next System Resync\nApprox Within 10 Minutes");
      </SCRIPT>
<?php
      $accountSuspended="no";
    } else if ((($susstatus == "unsuspended") || ($susstatus == "")) && ($accountSuspended ==  "on")) {
?>
      <SCRIPT>
        alert("Account Suspentions Are Not Real Time\nChanges Will Become Effective On The Next System Resync\nApprox Within 10 Minutes");
      </SCRIPT>
<?php
      $accountSuspended="yes";
    } else if (($susstatus == "yes") && ($accountSuspended ==  "on")) {
?>
      <SCRIPT>
        alert("Account Suspention Is Pending\nChanges Will Become Effective On The Next System Resync\nApprox Within 10 Minutes");
      </SCRIPT>
<?php
      $accountSuspended="yes";
    } else if (($susstatus == "no") && ($accountSuspended ==  "")) {
?>
      <SCRIPT>
        alert("Account Unsuspention Is Pending\nChanges Will Become Effective On The Next System Resync\nApprox Within 10 Minutes");
      </SCRIPT>
<?php
      $accountSuspended="no";
    } else if (($susstatus == "yes") && ($accountSuspended ==  "")) {
?>
      <SCRIPT>
        alert("Account Suspention Was Pending\nSuspention Has Been Canceled");
      </SCRIPT>
<?php
      $accountSuspended="unsuspended";
    } else if (($susstatus == "no") && ($accountSuspended ==  "on")) {
?>
      <SCRIPT>
        alert("Account Unsuspention Was Pending\nSuspention Will Continue");
      </SCRIPT>
<?php
      $accountSuspended="suspended";
    } else if ($susstatus == "on") {
      $accountSuspended="yes";
    } else if ($susstatus != "") {
      $accountSuspended=$susstatus;
    } else {
      $accountSuspended="unsuspended";
    }

    for ($i=0; $i < count($iarr); $i++) {
      if (($cbox[$iarr[$i]]) && ($iarr[$i] != "accountSuspended")) {
        if ($$iarr[$i] == "on") {
          $$iarr[$i]="yes";
        } else {
          $$iarr[$i]="no";
        }
      } else if (($iarr[$i] != "accountSuspended") && ($$iarr[$i] == "")) {
        $$iarr[$i]="0";
      }
      if ($$iarr[$i] != "") {
        if (($vadmin == "0") || ($vadminattr[strtolower($iarr[$i])])) {
          $minfo[$iarr[$i]]=$$iarr[$i];
          
        }
      }
    }
    ldap_modify($ds,$dn,$minfo);
  }

  if (($_SESSION['utype'] != "pdc") && ($_SESSION['utype'] != "system") && ($_SESSION['utype'] != "")) {
    $sr=ldap_search($ds,"cn=" . $_SESSION['utype'] . ",ou=vadmin","(&(objectClass=virtZoneSettings)(cn=" . $_SESSION['utype'] . "))",$iarr);
    $iinfo = ldap_get_entries($ds, $sr);
    $sr2=ldap_search($ds,$dn,"(&(objectClass=officePerson)(uid=$euser))",$iarr2);
    $iinfo2 = ldap_get_entries($ds, $sr2);
    for($lacnt=0;$lacnt<count($iarr2);$lacnt++) {
      $iinfo[0][$iarr2[$lacnt]][0]=$iinfo2[0][$iarr2[$lacnt]][0];
    }
  } else {
    $dninf=ldap_explode_dn($dn,0);
    if (eregi("^o=(.*)",$dninf[1],$vzinf)) {
      $sr=ldap_search($ds,"cn=" .  $vzinf[1] . ",ou=vadmin","(&(objectClass=virtZoneSettings)(cn=" .  $vzinf[1] . "))",$iarr);
      $iinfo = ldap_get_entries($ds, $sr);
      $sr2=ldap_search($ds,$dn,"(&(objectClass=officePerson)(uid=$euser))",$iarr2);
      $iinfo2 = ldap_get_entries($ds, $sr2);
      for($lacnt=0;$lacnt<count($iarr2);$lacnt++) {
        $iinfo[0][$iarr2[$lacnt]][0]=$iinfo2[0][$iarr2[$lacnt]][0];
      }
      $vadmin=1;
    } else {
      $sr=ldap_search($ds,$dn,"(&(objectClass=officePerson)(uid=$euser))",$iarr);
      $iinfo = ldap_get_entries($ds, $sr);
    }
  }
  
  for ($i=0; $i < count($iarr); $i++) {
    $rem=$i % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color1";
    }
?>
    <TR<?php print $bcolor;?>>
      <TD WIDTH=75% onmouseover="myHint.show('<?php print strtolower($iarr[$i]);?>')" onmouseout="myHint.hide()">
        <?php print $disc[$i];?>
      </TD>
      <TD>
<?php
	$attr=strtolower($iarr[$i]);
        $$attr=$iinfo[0][$attr][0];
        if (($ADMIN_USER == "admin") && (($vadmin == "0") || ($vadminattr[$attr]))) {
          if ($cbox[$iarr[$i]]) {
            if ($attr == "accountsuspended") {
              print "<INPUT TYPE=HIDDEN NAME=susstatus VALUE=\"" . $iinfo[0][$attr][0] . "\">";
            }
?>
            <INPUT TYPE=CHECKBOX NAME=<?php print $iarr[$i];?><?php if (($$attr != "no") && ($$attr != "") && ($$attr !="unsuspended") && ($$attr !="unset"))  {print " CHECKED";};?>>
<?php
           } else {
?>
            <INPUT TYPE=TEXT NAME=<?php print $iarr[$i];?> VALUE="<?php print $iinfo[0][$attr][0];?>">
<?php
           }
        } else {
          if ($cbox[$iarr[$i]]) {
            if (($$attr != "no") && ($$attr != "") && ($$attr !="unsuspended") && ($$attr !="unset")) {
              print "Yes";
            } else {
              print "No";
            }
          } else {
            print $iinfo[0][$attr][0];
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
  if ($ADMIN_USER == "admin") {
?>
<TR <?php print $bcol[2];?>><TH COLSPAN=2>
  <INPUT TYPE=SUBMIT VALUE="Modify" NAME=modrec></TH></TR>
<?php
  }
?>
</TABLE></FORM>
