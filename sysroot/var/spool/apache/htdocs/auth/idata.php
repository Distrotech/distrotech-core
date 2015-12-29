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
  if (isset($_POST['classi'])) {
    $euser=$_POST['classi'];
  } else {
    $euser=$PHP_AUTH_USER;
  }

  if (isset($_POST['mmap'])) {
    $info=$_POST['mmap'];
  }

  $discrip["mailLocalAddress"]="Email Aliases";
  $discrip["hostedSite"]="Website's (FTP/PHP/CGI Enabled)";
  $discrip["hostedFPSite"]="Website's (FrontPage Enabled)";

?>
<CENTER>
<FORM METHOD=POST NAME=idatafrm onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<?php print $showpage;?>">
<INPUT TYPE=HIDDEN NAME=nomenu VALUE="<?php if ($_POST['nomnenu'] < 2) {print $_POST['nomenu'];}?>">
<INPUT TYPE=HIDDEN NAME=classi VALUE="<?php print $euser;?>">
<INPUT TYPE=HIDDEN NAME=mmap VALUE="<?php print $info;?>">
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body><?php print _("Editing");?> <?php print  $discrip[$info];?></TH></TR>
<?php
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

  $iarr=array("mailLocalAddress","hostedSite","hostedFPSite","maxAliases","maxWebAliases");

  $dnarr=array("dn");
  $info=strtolower($info);

  $sr=ldap_search($ds,"","(|(&(objectClass=inetLocalMailRecipient)(uid=$euser))(&(objectClass=nisMailAlias)(cn=$euser)))",$dnarr);

  $iinfo = ldap_get_entries($ds, $sr);

  $dn=$iinfo[0]["dn"];
  if (isset($modrec)) {
    $dcnt=0;
    for ($i=0; $i<$ecount; $i++) {
      $ent="del" . $i;
      $entv="delh" . $i;
      if (${$ent}) {
        $dinfo[$info][$dcnt]=${$entv};
        $dcnt++;
      }
    }
    if (count($dinfo[$info]) > 0) {
      ldap_mod_del($ds,$dn,$dinfo);
    }
    if (($newent != "") || (($dcnt == 0) && ($mldomain != ""))) {
      $uniqsu=ldap_search($ds,"ou=Users","$info=$newent");
      $uniqsw=ldap_search($ds,"ou=Idmap","$info=$newent");
      if ($euser != $newent) {
        $notuidu=ldap_search($ds,"ou=Users","uid=$newent");
        $notuidw=ldap_search($ds,"ou=Idmap","uid=$newent");
      }
      if ((ldap_count_entries($ds,$uniqsu) == 0) && (ldap_count_entries($ds,$notuidu) == 0) &&
          (ldap_count_entries($ds,$uniqsw) == 0) && (ldap_count_entries($ds,$notuidw) == 0)) {
        if ($info == "maillocaladdress") {
          $dcheck=ldap_search($ds,"ou=Email","(&(|(sendmailMTAClassName=LDAPRoute)(sendmailMTAClassName=R))(sendmailMTAClassValue=$mldomain))");
          if (ldap_count_entries($ds,$dcheck)) {
            $binfo[$info]=$newent . "@" . $mldomain;
            ldap_mod_add($ds,$dn,$binfo);
          } else { 
            $adderr="ERROR:Invalid Domain In New Entry";
          }
        } else {
          $binfo[$info]=$newent;
          ldap_mod_add($ds,$dn,$binfo);
        }
      } else {
        $adderr="ERROR:Entry Already Exists";
      }
    }
    if ($info == "maillocaladdress") {
?>
      <SCRIPT>
        alert("Changes Made To Email Aliases Are Real Time\nAll Changes Made Are Now Active<?php if ($adderr != "") { print "\\n" . $adderr;}?>");
     </SCRIPT>
<?php
    } else {
?>
      <SCRIPT>
        alert("Changes Made To Websites Only Take Effect On The Hour<?php if ($adderr != "") { print "\\n" . $adderr;}?>");
     </SCRIPT>
<?php
    }
  }

  $sr=ldap_search($ds,$dn,"(|(&(objectClass=inetLocalMailRecipient)(uid=$euser))(&(objectClass=nisMailAlias)(cn=$euser)))",$iarr);
  $iinfo = ldap_get_entries($ds, $sr);
  $dnarr2=ldap_explode_dn($dn,0);

  if (eregi("^o=(.*)",$dnarr2[1],$vuser)) {
    $_SESSION['utype']=$vuser[1];
  } else if (eregi("^o=(.*)",$dnarr2[2],$vuser)) {
    $_SESSION['utype']=$vuser[1];
  } else if (eregi("^(uid|sambasid)=(.*)",$dnarr2[1],$mbowner)) {
    $_SESSION['utype']="system";
    $dnex=ldap_explode_dn($dn,0);
    $mbdn="";
    for($dncnt=1;$dncnt<count($dnex)-2;$dncnt++) {
      $mbdn.=$dnex[$dncnt] . ","	;
    }
    $mbdn.=$dnex[$dncnt];

    $mbsr=ldap_search($ds,$mbdn,"(&(objectClass=inetLocalMailRecipient)(" . $mbowner[1] . "=" . $mbowner[2] . "))",array("maxWebAliases","maxAliases"));
    $mbiinfo = ldap_get_entries($ds, $mbsr);
    $iinfo[0]["maxwebaliases"][0]=$mbiinfo[0]["maxwebaliases"][0];
    $iinfo[0]["maxaliases"][0]=$mbiinfo[0]["maxaliases"][0];
  } else {
    $_SESSION['utype']="system";
  }

  if (($_SESSION['utype'] != "system") && ($_SESSION['utype'] != "pdc") && ($_SESSION['utype'] != "")) {
    $vrsr=ldap_search($ds,"cn=" . $_SESSION['utype'] . ",ou=vadmin","(&(objectClass=virtZoneSettings)(cn=" . $_SESSION['utype'] . "))",array("maxWebAliases","maxAliases"));
    $vriinfo = ldap_get_entries($ds, $vrsr);
    $iinfo[0]["maxwebaliases"][0]=$vriinfo[0]["maxwebaliases"][0];
    $iinfo[0]["maxaliases"][0]=$vriinfo[0]["maxaliases"][0];
  }
  
  $sitecnt=$iinfo[0]["maxwebaliases"][0]-($iinfo[0]["hostedsite"]["count"]+$iinfo[0]["hostedfpsite"]["count"]);

  $dninf=ldap_explode_dn($dn,0);

  list($isuid,$ownerid)=explode("=",$dninf[1]);
  if (($isuid == "uid") && (($_SESSION['utype'] == "system") || ($_SESSION['utype'] == "pdc"))) {
    $sr=ldap_search($ds,"","(&(objectClass=posixAccount)(uid=$ownerid))",$iarr);
    $iinfo2 = ldap_get_entries($ds, $sr);
    $macnt=$iinfo2[0]["maxaliases"][0]-$iinfo[0]["maillocaladdress"]["count"];
  } else if (isset($iinfo[0]["maxaliases"][0])) {
    $macnt=$iinfo[0]["maxaliases"][0]-$iinfo[0]["maillocaladdress"]["count"];
  }

  for ($i=0; $i<$iinfo[0][$info]["count"]; $i++) {
    $rem=$i % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color1";
    }
?>
    <TR<?php print $bcolor;?>><TD WIDTH=25?><INPUT TYPE=CHECKBOX NAME="del<?php print $i;?>">
      <INPUT TYPE=HIDDEN NAME=delh<?php print $i;?> VALUE="<?php print $iinfo[0][$info][$i];?>">
    </TD><TD><?php print $iinfo[0][$info][$i];?></TD></TR>
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
  if ((($info == "maillocaladdress") && (($macnt > 0) || (! isset($macnt)))) || 
      (($info == "hostedsite") && ($sitecnt > 0))) {
/*
 || ($ADMIN_USER == "admin")){ 
*/
?>
    <TR<?php print $bcol[2];?>><TD WIDTH=25% onmouseover="myHint.show('<?php print $_POST['mmap'];?>')" onmouseout="myHint.hide()"><?php print _("Add");?></TD><TD>
      <INPUT TYPE=TEXT NAME=newent>
<?php
      if ($_POST['mmap'] == "mailLocalAddress") {
        $darr=array();
        $doms=ldap_search($ds,"ou=Email","(|(sendmailMTAClassName=LDAPRoute)(sendmailMTAClassName=R))",array("sendmailMTAClassValue"));
        $dinfo=ldap_get_entries($ds,$doms);
        for($i=0;$i<$dinfo['count'];$i++) {
          for($j=0;$j<$dinfo[$i]['sendmailmtaclassvalue']['count'];$j++) {
            if (! ereg("^[0-9]+\.[0-9]+\.[0-9]+",$dinfo[$i]['sendmailmtaclassvalue'][$j])) {
              array_push($darr,$dinfo[$i]['sendmailmtaclassvalue'][$j]);
            }
          }
        }
        sort($darr);
        reset($darr);
        print "@<SELECT NAME=mldomain>\n";
        while(list(,$domain)=each($darr)) {
          if (!$dseen[$domain]) {
            print "<OPTION VALUE=\"" . $domain . "\">" . $domain . "</OPTION>\n";
            $dseen[$domain]=true;
          }
        }
        print "</SELECT>\n";
      }
?>
      </TD></TR>
    <TR><TH COLSPAN=2 <?php print $bcol[1];?>>
<?php
  } else {
?>
  <TR><TH COLSPAN=2 <?php print $bcol[2];?>>
<?php
  }
?>
  <INPUT TYPE=HIDDEN NAME=ecount VALUE=<?php print $iinfo[0][$info]["count"];?>>
<?php
  if ($info == "hostedfpsite" ) {
?>
  All Websites Are Frontpage Enabled You Can Only Delete Existing Front Page Sites
<?php
  }
?>
  <INPUT TYPE=SUBMIT VALUE="Modify" NAME=modrec></TH></TR>
</TABLE></FORM>
