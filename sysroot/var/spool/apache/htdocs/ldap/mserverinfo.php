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
  if (isset($delete)) {
    $ld=ldap_delete($ds,$dn);
    return;
  }


?>
<FORM METHOD=POST>
<CENTER>

<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>Mail Server Access Account</TH></TR>
<?php
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if (ldap_count_entries($ds,$sr) == 1) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

  $descrip=array();

  $dnaa['uid']=1;

  $descrip["uid"]="Login Name";
  $descrip["cn"]="Host Name";
  $descrip["userPassword"]="Password";
  $descrip["ipHostNumber"]="I.P. Address";
  $descrip["l"]="Location";
  $descrip["description"]="Discription";

  $atrib=array("uid","cn","ipHostNumber","userPassword","l","description");

  $euser=$_SESSION['classi'];

    if ($dn == "") {
      $dn="uid=$euser,ou=Email";
    } else {
      $ndn="uid=$uid,ou=Email";
      if ($dn != $ndn) {
        ldap_rename($ds,$dn,"uid=$uid","ou=Email",true);
        $dn=$ndn;
      }
    }

  if ((! isset($update)) && ($ds)) {
    $sr=ldap_search($ds,$dn,"uid=*");
    $dnarr=ldap_explode_dn($dn,1);
    $entry=ldap_first_entry($ds,$sr);
    $aine = ldap_get_attributes($ds,$entry);
    for($cnt=0;$cnt < $aine["count"];$cnt++) {
      $attr=$aine[$cnt];
      $adata=ldap_get_values($ds,$entry,$attr);
//       print "$attr " . $adata["count"] . " " . $adata[0] . "<BR>";
      if ($bfile[$attr] ) {
        $dataout=ldap_get_values_len($ds,$entry,$attr);
        $fname=tempnam(".","LDAP");
        $temp=fopen($fname,w);
        fwrite($temp,$dataout[0]);
        fclose($temp);
        print "<IMG SRC=$fname>";
      } elseif (($adata["count"] > 1) && ($descrip[$attr] != "")){
        $val="";
        for($acnt=0;$acnt<$adata["count"];$acnt++) {
          $info[$attr][$acnt]=$adata[$acnt];
          if ($val != "") {
            $val="$val\r\n$adata[$acnt]";
          } else {
            $val=$adata[$acnt];
          }
        }
        $$attr=$val;
      } elseif ($descrip[$attr] != "") {
        $$attr=$adata[0];
      }
    }
  }

  $rejar=array();
  $rok=true;
  while(list($idx,$attr)=each($reqat[$ldtype])) {
    if ($$attr == "") {
      $rok=false;
      $rejar[$attr]=true;
    }
  }

  $attrhide2=$attrhide[$ldtype];
  $hidea=array();
  while(list($idx,$attr)=each($attrhide[$ldtype])) {
    $hidea[$attr]=true;
  }


  if ((isset($update)) && ($ds)) {
    if (($pass1 != $pass2) && ($pass1 != "")) {
      print "<TR><TH COLSPAN=2><FONT COLOR=RED>Password Mismatch</FONT></TH></TR>";
    } else {
      $userPassword=$pass1;
    }

    $sr=ldap_search($ds,$dn,"uid=*");
    $iinfo = ldap_get_entries($ds, $sr);

    $natrib=$atrib;
    while(list($idx,$catt)=each($natrib)) {
      if ($catt == "userPassword") {
         if ($userPassword != "") {
           $info[$catt]="{CRYPT}" . crypt($userPassword);
           $time=time();
         } else {
           $uidarr=array("userpassword");
           $sr2=ldap_search($ds,$dn,"uid=$uid",$uidarr);
           $uinfo = ldap_get_entries($ds, $sr2);
           $info["userPassword"]=$uinfo[0]["userpassword"][0];
         }
      } elseif ($b64[$catt]) {
        $data=$$catt;
        if ($data != "") {
          $info[$catt]=$data;
        }
      } elseif ($bfile[$catt]) {
        $fname=$$catt;
        if ($fname != "") {
          $cfile=fopen($fname,r);
          $dataout=fread($cfile,filesize($fname));
          fclose($cfile);
          $info[$catt]=";binary $dataout";
        }
      } else {
        $data=split("\r\n",$$catt);
        if (count($data) > 1) {
          $acnt=0;
          for ($cnt=0;$cnt < count($data);$cnt++) {
            if ($data[$cnt] != "") {
              $info[$catt][$acnt]=$data[$cnt];
              $acnt++;
            }
          }
        } elseif ($data[0] != "") {
          $info[$catt]=$data[0];
        }
      }
    }

    $hideae=array();
    while(list($idx,$attr)=each($attrhide2)) {
      $hideae[$attr]=true;
    }

    $dinfo=array();
    $natrib=$atrib;
    while(list($idx,$catt)=each($natrib)) {
      $aname=strtolower($catt);
      if ((count($info[$catt]) == 0) && (count($iinfo[0][$aname]) > 0) && ($hideae[$catt] != "true")){
        if (! $mline[$catt]) {
          $dinfo[$catt]=$iinfo[0][$aname][0];
          $$catt="";
        } else {
          $info[$catt]="Not Supplied";
          $$catt=$info[$catt];
        }
      }
    }

    ldap_mod_del($ds,$dn,$dinfo);
    $r=ldap_modify($ds,$dn,$info);
    if (!$r ) {
      print "<TR><TH COLSPAN=2><FONT COLOR=RED>Not Modifyied</FONT></TH></TR>";
    }     
    ldap_close($ds);

  } elseif (! $ds) {
    print "<TR><TH COLSPAN=2><FONT COLOR=RED>Not Connected To LDAP Server</FONT></TH></TR>";
  }

  print "<INPUT TYPE=HIDDEN NAME=dn VALUE=\"$dn\">";
  
  print "<INPUT TYPE=HIDDEN NAME=owner VALUE=\"$owner\">";
  print "<INPUT TYPE=HIDDEN NAME=baseou VALUE=\"$baseou\">";
  print "<INPUT TYPE=HIDDEN NAME=ldtype VALUE=\"$ldtype\">";
  
  $cnt=0;

  $descrip["uid"]="UID";
  $descrip["cn"]="Host Information";

  while(list($attr,$aname)=each($descrip)) {
    if ($attr == "userPassword") {
      $userPassword="";
    }

    $rem=$cnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
      $bcolor2=" CLASS=list-color1";
    } else {
      $bcolor=" CLASS=list-color1";
      $bcolor2=" CLASS=list-color2";
    }
    
    if (! $hidea[$attr]) {
?>
      <TR<?php print "$bcolor"?>><TD WIDTH=50% onmouseover="myHint.show('<?php print $attr;?>')" onmouseout="myHint.hide()">
<?php
        if ((isset($submited)) && ($rejar[$attr])) {
           print "<FONT COLOR=RED>*</FONT>";
        } else if ($rejar[$attr]) {
          print "<B>";
        }
        print $descrip[$attr];
?>
      </TD>
      <TD WIDTH=50%
<?php
        if (($dnaa[$attr]) || ($PHP_AUTH_USER != $euser) && ($PHP_AUTH_USER != "admin") && ($ADMIN_USER != "admin")) {
?>
          ><INPUT TYPE=HIDDEN NAME=<?php print $attr;?> VALUE="<?php print $$attr;?>"><?php print $$attr;?>
<?php
        } else {
          if (($mline[$attr]) || ($b64[$attr])){
?>
            ><TEXTAREA NAME=<?php print $attr;?> COLS=40 ROWS=5><?php print $$attr;?></TEXTAREA>
<?php
          } elseif ($bfile[$attr]) {
?>
            ><INPUT TYPE=FILE  NAME=<?php print $attr;?> COLS=40 ROWS=5>
<?php
          } else {
            if ($attr != "userPassword") {
?>
              ><INPUT TYPE=TEXT SIZE=40 NAME=<?php print $attr;?> VALUE="<?php print $$attr;?>">
<?php
            } else {
?>
              ><INPUT TYPE=PASSWORD SIZE=40 NAME=pass1 VALUE="">
              <TR <?php print "$bcolor2"?>><TD WIDTH=50% onmouseover="myHint.show('userPassword2')" onmouseout="myHint.hide()">Confirm Password</TD>
              <TD WIDTH=50%1>
              <INPUT TYPE=PASSWORD SIZE=40 NAME=pass2 VALUE="">
<?php
              $cnt ++;
            }
          }
        }
?>
      </TD>
    </TR>
<?php
      $cnt ++;
    }
  }
    $rem=$cnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color1";
    }
    if (($PHP_AUTH_USER == $euser) || ($PHP_AUTH_USER == "admin") || ($ADMIN_USER="admin")) {
?>
      <TR<?php print "$bcolor"?>>
        <TD COLSPAN=2 ALIGN=MIDDLE>
<?php
         print "<INPUT TYPE=SUBMIT VALUE=Modify NAME=update>";
         if (($ADMIN_USER == "admin") || ($PHP_AUTH_USER == "admin")) {
           print "<INPUT TYPE=SUBMIT VALUE=Delete NAME=delete>";
         }
         print "<INPUT TYPE=RESET VALUE=Reset>";
?>
       </TD>
    </TR>
<?php
    }
?>
</TABLE>
</FORM>
