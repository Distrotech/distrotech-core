<%
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
    $_SESSION['classi']="snom";
    $_SESSION['utype']="snom";
    include "ennav.php";
    return;
  }

%>
<FORM METHOD=POST NAME=snominfo onsubmit="ajaxsubmit(this.name);return false">

<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<%
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(|(cn=Admin Access)(cn=Voip Admin)))");
  if (ldap_count_entries($ds,$sr) == 1) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

  $descrip=array();

  $descrip["cn"]="Name";
  $descrip["telephoneNumber"]="Telephone Number";

  $atrib=array("cn","telephoneNumber");

  if ($dn == "") {
    $dn="cn=" . $_SESSION['classi'] . ",ou=Snom";
  } else {
    $ndn="cn=$cn,ou=Snom";
    if ($dn != $ndn) {
      ldap_rename($ds,$dn,"cn=$cn","ou=snom",true);
      $dn=$ndn;
    }
  }

  if ((! isset($update)) && ($ds)) {
    $sr=ldap_search($ds,$dn,"cn=*");
    $dnarr=ldap_explode_dn($dn,1);
    $entry=ldap_first_entry($ds,$sr);
    $aine = ldap_get_attributes($ds,$entry);
    for($cnt=0;$cnt < $aine["count"];$cnt++) {
      $attr=$aine[$cnt];
      $adata=ldap_get_values($ds,$entry,$attr);

//      print "$attr " . $adata["count"] . " " . $adata[0] . "<BR>";

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

  $dnaa=array();
  while(list($idx,$attr)=each($dnattr[$ldtype])) {
    $dnaa[$attr]=true;
  }

  if ((isset($update)) && ($ds)) {
    $sr=ldap_search($ds,$dn,"cn=*");
    $iinfo = ldap_get_entries($ds, $sr);

    $natrib=$atrib;
    while(list($idx,$catt)=each($natrib)) {
      $info[$catt]=$$catt;
//      print $catt . ": " . $$catt . "<BR>\n";
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

  print "<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>Edititg " . $cn . " (" . $telephoneNumber . ")</TH></TR>";
  while(list($attr,$aname)=each($descrip)) {
    $rem=$cnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
      $bcolor2=" CLASS=list-color1";
    } else {
      $bcolor=" CLASS=list-color1";
      $bcolor2=" CLASS=list-color2";
    }
    
    if (! $hidea[$attr]) {
%>
      <TR<% print "$bcolor"%>><TD WIDTH=50% onmouseover="myHint.show('<%print $attr;%>')" onmouseout="myHint.hide()">
<%
        if ((isset($submited)) && ($rejar[$attr])) {
           print "<FONT COLOR=RED>*</FONT>";
        } else if ($rejar[$attr]) {
          print "<B>";
        }
        print $descrip[$attr];
%>
      </TD>
      <TD WIDTH=50%
<%
        if (($dnaa[$attr]) || ($PHP_AUTH_USER != $_SESSION['classi']) && ($PHP_AUTH_USER != "admin") && ($ADMIN_USER != "admin")) {
%>
          ><INPUT TYPE=HIDDEN NAME=<%print $attr;%> VALUE="<%print $$attr;%>"><%print $$attr;%>
<%
        } else {
          if (($mline[$attr]) || ($b64[$attr])){
%>
            ><TEXTAREA NAME=<%print $attr;%> COLS=40 ROWS=5><%print $$attr;%></TEXTAREA>
<%
          } elseif ($bfile[$attr]) {
%>
            ><INPUT TYPE=FILE  NAME=<%print $attr;%> COLS=40 ROWS=5>
<%
          } else {
            if ($attr != "userPassword") {
%>
              ><INPUT TYPE=TEXT SIZE=40 NAME=<%print $attr;%> VALUE="<%print $$attr;%>">
<%
            }
          }
        }
%>
      </TD>
    </TR>
<%
      $cnt ++;
    }
  }
    $rem=$cnt % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color1";
    }
    if (($PHP_AUTH_USER == $_SESSION['classi']) || ($PHP_AUTH_USER == "admin") || ($ADMIN_USER="admin")) {
%>
      <TR<% print "$bcolor"%>>
        <TD COLSPAN=2 ALIGN=MIDDLE>
<%
         print "<INPUT TYPE=SUBMIT VALUE=Modify onclick=this.name='update'>";
         if (($ADMIN_USER == "admin") || ($PHP_AUTH_USER == "admin")) {
           print "<INPUT TYPE=SUBMIT VALUE=Delete onclick=this.name='delete'>";
         }
         print "<INPUT TYPE=RESET VALUE=Reset>";
%>
       </TD>
    </TR>
<%
    }
%>
</TABLE>
</FORM>
