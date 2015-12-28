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
  if ((!isset($classi)) || ($classi == "")) {
    $euser=$PHP_AUTH_USER;
  } else {
    $euser=$classi;
  }

  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

$disc=array(_("Photo Album"));

?>
<FORM ENCTYPE="multipart/form-data" METHOD=POST>
<INPUT TYPE=HIDDEN NAME=classi VALUE="<?php print $euser;?>">
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body><?php print _("Editing Photo Album");?></TH></TR>
<TR CLASS=list-color1><TH COLSPAN=2 CLASS=heading-body2><?php print _("Default Image");?> (0)</TH></TR>
<?php

  $iarr=array("jpegPhoto");
  $dnarr=array("dn");
  $info=strtolower($info);

  $sr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=$euser))",$dnarr);
  $iinfo = ldap_get_entries($ds, $sr);
  $dn=$iinfo[0]["dn"];

  $sr=ldap_search($ds,$dn,"(&(objectClass=officePerson)(uid=$euser))",$iarr);
  $entry = ldap_first_entry($ds,$sr);

  if (isset($modrec)) {
    for ($i=0; $i < count($iarr); $i++) {
      $catt=$iarr[$i];
      if ($bfile[$catt]) {
        $adata=ldap_get_values_len($ds,$entry,strtolower($catt));
        $pcount=$adata["count"];
        if ($_FILES[$catt]['name'] != "") {
          $tmpnme=tempnam("/tmp","jpeg");
          if (move_uploaded_file($_FILES[$catt]['tmp_name'],$tmpnme)) {
            $imin=imagecreatefromjpeg($tmpnme);
            if ($imin) {
              $imx=imagesx($imin);
              $imy=imagesy($imin);
              if ( (($imx/$imy) > 0.5) && (($imx/$imy) < 2)) {
                $imlim=800;
                if (($imx > $imlim) || ($imy > $imlim)) {
                  if ($imx <= $imy) {
                    $newy=$imlim;
                    $newx=($imlim*$imx)/$imy;
                  } else {
                    $newx=$imlim;
                    $newy=($imlim*$imy)/$imx;
                  }
                  $imout=imagecreatetruecolor($newx,$newy);
                  imagecopyresampled($imout,$imin,0,0,0,0,$newx,$newy,$imx,$imy);
                  imagejpeg($imout,$tmpnme);
                }
                $cfile=fopen($tmpnme,r);
                $dataout=fread($cfile,filesize($tmpnme));
                fclose($cfile);
                if ($inpindex == "") {
                  for ($pcnt=0;$pcnt < $pcount;$pcnt++) {
                    $minfo[$catt][$pcnt]=$adata[$pcnt];
                  }
                } else {
                  $pindex=$inpindex;
                  for ($pcnt=0;$pcnt < $pindex;$pcnt++) {
                    $minfo[$catt][$pcnt]=$adata[$pcnt];
                  }
                  for ($pcnt=$pindex;$pcnt < $pcount;$pcnt++) {
                    $pcinto=$pcnt+1;
                    $minfo[$catt][$pcinto]=$adata[$pcnt];
                  }
                }
                if ($pindex == "") {
                  if ($pcount != "") {
                    $minfo[$catt][$pcount]=$dataout;
                  } else {
                    $minfo[$catt][0]=$dataout;
                  }
                } else {
                  $minfo[$catt][$pindex]=$dataout;
                }
                $pcount++;
              }
            }
          }
          unlink($tmpnme);
        } elseif ($dpindex != "") {
          for ($savcnt=0;$savcnt < $dpindex;$savcnt++) {
            $minfo[$catt][$savcnt]=$adata[$savcnt];
          }
          for ($savcnt=$dpindex+1;$savcnt < $pcount;$savcnt++) {
            $minfo[$catt][$savcnt-1]=$adata[$savcnt];
          }
          $pcount--;
        }
      }
    }
    ldap_modify($ds,$dn,$minfo);
    $sr=ldap_search($ds,$dn,"(&(objectClass=officePerson)(uid=$euser))",$iarr);
    $entry = ldap_first_entry($ds,$sr);
  }
  
  for ($i=0; $i < count($iarr); $i++) {
    $attr=$iarr[$i];
    $adata=ldap_get_values_len($ds,$entry,strtolower($attr));
    $pcount=$adata["count"];
    $$attr=$adata[0];

    print "<TR CLASS=list-color2><TD COLSPAN=2 VALIGN=MIDDLE ALIGN=CENTER>";
    if ($pcount > 0) {
?>
      <A HREF=/photo/<?php print urlencode($euser);?>.jpg TARGET=_BLANK><IMG SRC=/photo/<?php print urlencode($euser);?>.jpg&imlim=400 onmouseover="myHint.show('photo')" onmouseout="myHint.hide()" BORDER=0></A><BR>
<?php
    } else {
      print _("NO IMAGE");
    }
?>
    <TR CLASS=list-color1><TH COLSPAN=2 CLASS=heading-body2><?php print _("Photo Options");?></TH></TR>
    <TR CLASS=list-color2><TD COLSPAN onmouseover="myHint.show('0')" onmouseout=myHint.hide()><?php print _("Select JPEG Image To Add/Insert Into Album");?><BR><FONT SIZE=1><?php print _("It Must Be A Unique Image Duplicates Are Rejected");?></FONT></BR></TD<TD>
    <INPUT TYPE=FILE  NAME=<?php print $attr;?> COLS=40 ROWS=5></TD></TR>
    <TR CLASS=list-color1><TH COLSPAN=2 CLASS=heading-body2><?php print _("Chose One Option Bellow");?></TH></TR>
    <TR CLASS=list-color2><TD onmouseover="myHint.show('1')" onmouseout=myHint.hide()><?php print _("Insert New Photo");?></TD><TD>
        <SELECT NAME=inpindex>
          <OPTION VALUE="">
          <OPTION VALUE=""><?php print _("At The End");?>
<?php
          for ($pcnt=0;$pcnt<$pcount;$pcnt++) {
            print "<OPTION VALUE=" . $pcnt . ">" .  _("At Position") . " " . $pcnt;
          }
?>
        </SELECT></TD></TR>
      <TR CLASS=list-color1><TD onmouseover="myHint.show('2')" onmouseout=myHint.hide()>
      <?php print _("Replace Photo");?></TD><TD>
        <SELECT NAME=pindex>
          <OPTION VALUE="">
<?php
          for ($pcnt=0;$pcnt<$pcount;$pcnt++) {
            print "<OPTION VALUE=" . $pcnt . ">" . _("At Position") . " " . $pcnt;
          }
?>
        </SELECT></TD></TR>
      <TR CLASS=list-color2><TD onmouseover="myHint.show('3')" onmouseout=myHint.hide()>
      <?php print _("Delete Photo At Position");?><BR><FONT SIZE=1>
      <?php print _("At Least One Photo Must Be In The Data Base");?></FONT>
      </TD><TD>
        <SELECT NAME=dpindex>
          <OPTION VALUE="">
<?php
          for ($pcnt=0;$pcnt<$pcount;$pcnt++) {
            print "<OPTION VALUE=" . $pcnt . ">" . $pcnt;
          }
        print "</SELECT>";
      print "</TD></TR>\n\r";
  }
?>
<TR CLASS=list-color1><TH COLSPAN=2>  
  <INPUT TYPE=SUBMIT VALUE="<?php print _("Modify");?>" NAME=modrec></TH></TR>
</TABLE></FORM>
