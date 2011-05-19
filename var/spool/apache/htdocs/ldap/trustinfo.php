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
    include "main.php";
    return;
  }
%>

<FORM METHOD=POST NAME=trustform onsubmit="ajaxsubmit(this.name);return false">
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>Modifying Trust Account</TH></TR>
<%
  if (isset($_SESSION['classi'])) {
    $euser=$_SESSION['classi'];
  }
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if (ldap_count_entries($ds,$sr) == 1) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }


  $descrip=array();
  $descrip["uid"]="UID";
  array_push($atrib,"uid");
  $descrip["cn"]="Host Information";
  array_push($atrib,"cn");

  if ($dn == "") {
    $dn="uid=$euser,ou=Trusts";
  } else {
    $ndn="uid=$uid\$,ou=Trusts";
    if ($dn != $ndn) {
      ldap_rename($ds,$dn,"uid=$uid\$","ou=Trusts",true);
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
          print $attr . ": " . $adata[$acnt];
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

     $uid=$uid . "\$";

    $sr=ldap_search($ds,$dn,"uid=*");
    $iinfo = ldap_get_entries($ds, $sr);

    $natrib=array("cn","uid");
    while(list($idx,$catt)=each($natrib)) {
      $info[$catt]=$$catt;
    }

    $hideae=array();
    while(list($idx,$attr)=each($attrhide2)) {
      $hideae[$attr]=true;
    }

    $r=ldap_modify($ds,$dn,$info);
    if (!$r ) {
      print "<TR><TH COLSPAN=2><FONT COLOR=RED>Not Modifyied - " . ldap_error($ds) . "</FONT></TH></TR>";
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
    } else {
      $bcolor=" CLASS=list-color1";
    }
    
    $uid=chop($uid,"\$");

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
        if ($attr == "uid") {
%>
          ><INPUT TYPE=HIDDEN NAME=<%print $attr;%> VALUE="<%print $$attr;%>"><%print $$attr;%>
<%
        } else {
%>
            ><INPUT TYPE=TEXT SIZE=40 NAME=<%print $attr;%> VALUE="<%print $$attr;%>">
<%
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
    if (($PHP_AUTH_USER == $euser) || ($PHP_AUTH_USER == "admin") || ($ADMIN_USER == "admin")) {
%>
      <TR<% print "$bcolor"%>>
        <TD COLSPAN=2 ALIGN=MIDDLE>
<%
         print "<INPUT TYPE=SUBMIT VALUE=Modify onclick=this.name='update'>";
         print "<INPUT TYPE=SUBMIT VALUE=Delete onclick=this.name='delete'>";
         print "<INPUT TYPE=RESET VALUE=Reset>";
%>
       </TD>
    </TR>
<%
    }
%>
</TABLE>
</FORM>
