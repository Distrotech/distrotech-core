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

  if ((isset($add)) && ($baseou == "system")) {
    include "adduser.php";
    return 0;
  } else if ((isset($add)) && ($baseou == "trust")) {
    include "addtrust.php";
    return 0;
  } else if ((isset($add)) && ($baseou == "mserver")) {
    include "addmserv.php";
    return 0;
  } else if ((isset($add)) && ($baseou == "snom")) {
    include "addsnom.php";
    return 0;
  } else if ((isset($add)) && ($baseou == "server")) {
    include "/var/spool/apache/htdocs/ldap/addserv.php";
    return 0;
  } else if ((isset($add)) && ($baseou == "mserver")) {
    include "/var/spool/apache/htdocs/ldap/addmserv.php";
    return 0;
  } else if ((isset($add)) && ($baseou == "pdc")) {
?>
<html>
<head>
</head>
<body>
<CENTER>
<H1>PDC Users Cannot Be Added</H1>
</body>
</html>
<?php
    return 0;
  }


  if (($ds) && (isset($find))) { 
    $search=rtrim($search);
    if ($type == "in") {
      $search="*$search*";
    } elseif ($type == "end") {
      $search="*$search";
    } elseif ($type == "start") {
      $search="$search*";
    }     

    
    if ($baseou == "trust") {
      $obcl="objectClass=posixAccount";
    } elseif (($baseou == "server") || ($baseou == "mserver")){
      $obcl="objectClass=ipHost";
    } elseif ($baseou == "snom"){
      $obcl="objectClass=snomcontact";
    } else {
      $obcl="objectClass=officePerson)(cn=*";
    }

    if ($what == "accountSuspended") {
      $search="*";
    }

    if (($search == "") || ($search == "**")){
      $sr=ldap_search($ds,$abdn,"(&($obcl))");  
    } else {
      $sr=ldap_search($ds,$abdn,"(&($obcl)($what=$search))");  
    }
    $info = ldap_get_entries($ds, $sr);

    print "\n<CENTER>\n";
    print "$LDAP_OPT_PROTOCOL_VERSION";
    $baseou=urlencode($baseou);
    $ldtype=urlencode($ldtype);
    print "\n<TABLE cellspacing=0 cellpadding=0 WIDTH=90%>\n";

    for ($i=0; $i<$info["count"]; $i++) {
      $srsort[$i]=$info[$i]["cn"][0];
    }
    asort($srsort);
    reset ($srsort);


    while (list($i,$val) = each($srsort)) {
      $dn=$info[$i]["dn"];
      if (($baseou == "pdc") && (strrpos($info[$i]["uid"][0],"\$") == strlen($info[$i]["uid"][0]) -1)) {
        continue;
      } else if (($baseou == "system") && (eregi("uid=.*,o=.*,ou=users",$dn)) && ($_SESSION['utype'] == $baseou)) {
        continue;
      }
      $edit=urlencode($dn);
      $cname=$info[$i]["cn"][0];
      $cnhtml="<TR>\n  <TH COLSPAN=4 CLASS=heading-body>";
      if ($baseou == "system") {
        $cnhtml="<TR>\n  <TH COLSPAN=5 CLASS=heading-body>";
        $suarr=ldap_explode_dn($dn,1);
        $cnhtml="$cnhtml<A HREF=javascript:edituser('$suarr[0]','" . $_SESSION['utype'] . "') CLASS=heading-body>$cname";
        if (($info[$i]["accountsuspended"][0] == "suspended") || ($info[$i]["accountsuspended"][0] == "yes")){
          $cnhtml=$cnhtml . " (Suspended)</A>";
        } else {
          $cnhtml=$cnhtml . "</A>";
        }
      } else if ($baseou == "pdc") {
        $cnhtml="<TR>\n  <TH COLSPAN=5 CLASS=heading-body>";
        $suarr=array($info[$i]["uid"][0]);
        $cnhtml="$cnhtml<A HREF=javascript:edituser('$suarr[0]','" . $baseou . "') CLASS=heading-body>$cname ($suarr[0])</A>";
      } else if ($baseou == "trust") {
        $suarr=ldap_explode_dn($dn,1);
        $cnhtml="$cnhtml<A HREF=javascript:edituser('$suarr[0]','" . $baseou . "') CLASS=heading-body>$cname ($suarr[0])</A>";
      } else if ($baseou == "snom") {
        $suarr=ldap_explode_dn($dn,1);
        $cnhtml="$cnhtml<A HREF=\"javascript:edituser('$suarr[0]','" . $baseou . "')\" CLASS=heading-body>$cname (" . $info[$i]["telephonenumber"][0] . ")</A>";
      } else if ($baseou == "server") {
        $suarr=ldap_explode_dn($dn,1);
        $cnhtml="$cnhtml<A HREF=/auth/index.php?disppage=ldap/serverinfo.php&euser=$suarr[0] CLASS=heading-body>$cname</A>";
      } else if ($baseou == "mserver") {
        $suarr=ldap_explode_dn($dn,1);
        $cnhtml="$cnhtml<A HREF=javascript:edituser('$suarr[0]','" . $baseou . "') CLASS=heading-body>$cname ($suarr[0])</A>";
      } else {
        $cnhtml="$cnhtml<A HREF=/auth/index.php?disppage=ldap/ldap.php&dn=$edit&baseou=$baseou&ldtype=$ldtype CLASS=heading-body>$cname</A>";
      }
      $cnhtml="$cnhtml</TH>\n</TR>\n";
      print "$cnhtml";

      if (($baseou == "system") || ($baseou == "pdc")) {
        print "<TD COLSPAN=4><TABLE cellspacing=0 border=0 cellpadding=0 WIDTH=100%><TR CLASS=list-color1>";
      } else {
        print "<TR CLASS=list-color1>";
      }
      $info[$i]["cn"][0]=$cnhtml;

      $natrib=$atrib;
      $rcnt=0;
      while(list($idx,$catt)=each($natrib)) {
        $aname=$catt;
        $catt=strtolower($catt);
        if (($catt != "cn") && (! $cert[$aname]) && (! $bfile[$aname]) &&
            ($catt != "userpassword") && ($catt != "comment")){
          $ccnt=$rcnt % 4;
          if (($ccnt == 0) && ($rcnt > 0)){
            print "\n</TR>\n<TR CLASS=list-color1>";
          }
          $rcnt++;
          print "\n  <TD WIDTH=25% VALIGN=TOP>\n    <TABLE WIDTH=100% cellspacing=0 cellpadding=0>\n      ";
          print "<TR CLASS=list-color2><TD VALIGN=TOP><font SIZE=1>$descrip[$aname]</font></TD></TR>\n      ";
          print "<TR><TD><FONT SIZE=2>";
          if (count($info[$i][$catt]) <= 0) {
            print "&nbsp;";
          }
          if ($b64[$aname]) {
            $val=$info[$i][$catt][0];
            $data=split("\r\n",$val);       
            if (count($data) > 1) {
              $acnt=0;
              for ($cnt=0;$cnt < count($data);$cnt++) {
                if ($data[$cnt] != "") {
                  print "$data[$acnt]<BR>";
                  $acnt++;
                }
              }
            } elseif ($data[0] != "") {
              print "$val<BR>";
            }
          } else {
            for ($cnt=0;$cnt<count($info[$i][$catt]);$cnt++) {
              $val=$info[$i][$catt][$cnt];
              if ($catt == "mail") {
                print "<A HREF=\"mailto:" . $val . "\">" . $val . "</A>";
              } elseif ($catt == "url") {
                print "<A HREF=\"" . $val . "\" TARGET=_BLANK>" . $val . "</A>";
              } else {
                print "$val";
              }
              if ($info[$i][$catt][$cnt+1] != "") {
                print "<BR>";
              }
            }
          }
          print "</FONT></TD></TR>\n    </TABLE>\n  </TD>";
        }
      }
     $ccnt=$rcnt % 4;
     while ($ccnt != 0) {
       print "\n  <TD WIDTH=25%>\n    <TABLE WIDTH=100% cellspacing=0 cellpadding=0>\n      ";
       print "<TR CLASS=list-color2><TD VALIGN=TOP><font size=1>&nbsp;</font></TD></TR>\n";
       print "<TR><TD><font size=2>&nbsp;</font></TD></TR>\n    </TABLE>\n  </TD>";
       $rcnt++;
       $ccnt=$rcnt % 4;
     }
     
     if ($info[$i]["comment"][0] != "") {
?>
       </TR><TR CLASS=list-color2><TD COLSPAN=4>
       <TABLE WIDTH=100% cellspacing=0 cellpadding=0>
       <TR CLASS=list-color2><TD VALIGN=TOP>
         <font SIZE=1>Notes</font></TD></TR>
       <TR CLASS=list-color1 COLSPAN=4><TD VALIGN=TOP>
         <font SIZE=2>
<?php
       print "<PRE>" . $info[$i]["comment"][0] . "</PRE></font></TD></TABLE></TD>";
/*
       Notes</font></TD>
       </TABLE></TD>
*/
     }
    
     if (($baseou == "system") || ($baseou == "pdc")){
       if ($info[$i]["jpegphoto"][0] != "" ) {
         print "</TR></TABLE></TD><TD CLASS=list-color2 VALIGN=MIDDLE ALIGN=CENTER><A HREF=/photo/" . $suarr[0] .".jpg target=_blank><IMG SRC=/ldap/photo.php?euser=" . $suarr[0] . "&imlim=150 BORDER=0></A></TD></TR>";
       } else {
         print "</TR></TABLE></TD><TD CLASS=list-color2 ALIGN=CENTER>" . _("User Image") . "<BR>" . _("Not Found") . "</TD></TR>";
       }
     }
    }
    print "</TABLE>\n";
    print "</body></html>";
  } else {
?>

<FORM METHOD=POST>
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">

<?php
  $modify=false;
  if (isset($delete)) {
    $ld=ldap_delete($ds,$dn);
  }
  if ($ld) {
    $modify=false;
    $dn="";
    $natrib=$atrib;
    while(list($idx,$catt)=each($natrib)) {
      ${$catt}="";
    }
    $c="";
  }

  if (($dn != "") && ($ds) && (! ((isset($submited)) || (isset($update)))))  {
    $sr=ldap_search($ds,$dn,"cn=*");

    $modify=true;
    $entry=ldap_first_entry($ds,$sr);
    $aine = ldap_get_attributes($ds,$entry);
    for($cnt=0;$cnt < $aine["count"];$cnt++) {
      $attr=$aine[$cnt];
      $adata=ldap_get_values($ds,$entry,$attr);
      if (($cert[$attr] ) || ($bfile[$attr])) {
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
        ${$attr}=$val;
      } elseif ($descrip[$attr] != "") {
        ${$attr}=$adata[0];
      }
    }
 }

  $rejar=array();
  $rok=true;
  while(list($idx,$attr)=each($reqat[$ldtype])) {
    if (${$attr} == "") {
      $rok=false;
      $rejar[$attr]=true;
    }
  }

  $dnaa=array();
  while(list($idx,$attr)=each($dnattr[$ldtype])) {
    $dnaa[$attr]=true;
  }

  $hidea=array();
  while(list($idx,$attr)=each($attrhide[$ldtype])) {
    $hidea[$attr]=true;
  }

  if (((isset($submited)) || (isset($update))) && ($ds) && ($rok)) {
    $info["objectclass"][0]="person";
    $info["objectclass"][1]="inetOrgPerson";
    $info["objectclass"][2]="officePerson";
    $info["objectclass"][3]="organizationalPerson";

    $sr=ldap_search($ds,$dn,"givenname=*");
    $iinfo = ldap_get_entries($ds, $sr);

    $natrib=$atrib;
    while(list($idx,$catt)=each($natrib)) {
      if ($b64[$catt]) {
        $data=${$catt};
        if ($data != "") {
          $info[$catt]=$data;
        }
      } elseif ($bfile[$catt]) {
        $fname=${$catt};
        if ($fname != "") {
          $cfile=fopen($fname,r);
          $dataout=fread($cfile,filesize($fname));
          fclose($cfile);
          $info[$catt]=";binary $dataout";
        }
      } else {
        $data=split("\r\n",${$catt});       
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

    $ls=ldap_search($ds,"cn=$baseou","(objectclass=device)");
    if (! $ls) {
      $raent["objectClass"]="top";
      $raent["objectClass"]="device";
      $raent["cn"]="$baseou";
      $r=ldap_add($ds,"cn=$baseou",$raent);
    }

    $rou["objectClass"]="organizationalunit";
    $ls=ldap_search($ds,"ou=Entries,cn=$baseou","(objectclass=device)");

    if ($dn == "") {
      $dn="cn=$cn,$abdn";
    } else {
      $ndn="cn=$cn,$abdn";
      if ($dn != $ndn) {
        ldap_rename($ds,$dn,"cn=$cn",$abdn,true);
        $dn=$ndn;
      }
    }

    if ( ! $ls ) {
      $rou["ou"]="Entries";
      ldap_add($ds,"ou=Entries,cn=$baseou",$rou);
    }
    
    $ls=ldap_search($ds,$dn,"cn=*");
    if (!$ls) {
      $r=ldap_add($ds,$dn,$info);
    } elseif (isset($update)) {
      $dinfo=array();
      $natrib=$atrib;
      while(list($idx,$catt)=each($natrib)) {
        $aname=strtolower($catt);
        if ((count($info[$catt]) == 0) && (count($iinfo[0][$aname]) > 0)){
          if (! $mline[$catt]) {
            $dinfo[$catt]=$iinfo[0][$aname][0];
            ${$catt}="";
          } else {
            $info[$catt]="Not Supplied";
            ${$catt}=$info[$catt];
          }
        }
      }
      ldap_mod_del($ds,$dn,$dinfo);
      $r=ldap_modify($ds,$dn,$info);
    } else {
      print "<TR><TH COLSPAN=2 CLASS=heading-body2><FONT COLOR=RED>Record Exists Not Changed Click Modify Below</FONT></TH></TR>";
    }
    if ((!$r) && (! $modify)) {
      print "<TR><TH COLSPAN=2 CLASS=heading-body2><FONT COLOR=RED>Not Added</FONT></TH></TR>";
    } elseif ((!$r ) && ($modify)) {
      print "<TR><TH COLSPAN=2 CLASS=heading-body2><FONT COLOR=RED>Not Modifyied</FONT></TH></TR>";
      $modify=true;
    } else {
      $modify=true;
    }     
    ldap_close($ds);
  } elseif ((isset($submited)) && ($ds)) {
    print "<TR><TH COLSPAN=2 CLASS=heading-body2><FONT COLOR=RED>Not All Required Data Suppplied</FONT></TH></TR>";
  } elseif (! $ds) {
    print "<TR><TH COLSPAN=2 CLASS=heading-body2><FONT COLOR=RED>Not Connected To LDAP Server</FONT></TH></TR>";
  }

  print "<INPUT TYPE=HIDDEN NAME=dn VALUE=\"$dn\">";
  
  print "<INPUT TYPE=HIDDEN NAME=owner VALUE=\"$owner\">";
  print "<INPUT TYPE=HIDDEN NAME=baseou VALUE=\"$baseou\">";
  print "<INPUT TYPE=HIDDEN NAME=ldtype VALUE=\"$ldtype\">";
  
  $cnt=0;
  while(list($attr,$aname)=each($descrip)) {
    $rem=$cnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color1";
    } else {
      $bcolor=" CLASS=list-color2";
    }
    
    if (! $hidea[$attr]) {
?>
      <TR <?php print $bcolor;?>><TD WIDTH=50%>
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
        if (($modify) && ($dnaa[$attr])) {
?>
          ><INPUT TYPE=HIDDEN NAME=<?php print $attr;?> VALUE="<?php print ${$attr};?>"><?php print ${$attr};?>
<?php
        } else {
          if ($mline[$attr]) {
?>
            ><TEXTAREA NAME=<?php print $attr;?> COLS=40 ROWS=5><?php print ${$attr};?></TEXTAREA>
<?php
          } elseif ($b64[$attr]) {
            $tmpval=${$attr};
?>
            ><TEXTAREA NAME=<?php print $attr;?> COLS=40 ROWS=5><?php print $tmpval;?></TEXTAREA>
<?php
          } elseif ($bfile[$attr]) {
?>
            ><INPUT TYPE=FILE  NAME=<?php print $attr;?> COLS=40 ROWS=5>
<?php
          } elseif ($cert[$attr]) {
?>
            ><INPUT TYPE=FILE  NAME=<?php print $attr;?> COLS=40 ROWS=5>
<?php
          } else {
?>
            ><INPUT TYPE=TEXT SIZE=40 NAME=<?php print $attr;?> VALUE="<?php print ${$attr};?>">
<?php
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
      $bcolor="";
    } else {
      $bcolor=" class=list-color2";
    }
?>
  <TR<?php print "$bcolor"?>>
    <TD COLSPAN=2 ALIGN=MIDDLE>
<?php
      if ($modify) {
        print "<INPUT TYPE=SUBMIT VALUE=Modify NAME=update>";
        print "<INPUT TYPE=SUBMIT VALUE=Delete NAME=delete>";
        print "<INPUT TYPE=RESET VALUE=Reset>";
      } else {
        print "<INPUT TYPE=SUBMIT VALUE=Add NAME=submited>";
      }
?>
    </TD>
  </TR>
</TABLE>
</FORM>
<?php
  }
?>

