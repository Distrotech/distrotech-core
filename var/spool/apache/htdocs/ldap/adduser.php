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
$adescrip["givenName"]=_("First Name");
$adescrip["sn"]=_("Last Name");
$adescrip["uid"]=_("Username");
$adescrip["clearPassword"]=_("Password (Encrypted On The Server)");


$sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
  $ADMIN_USER="admin";
} else {
  $ADMIN_USER="pleb";
}


if ((! file_exists("/etc/.networksentry-lite")) && ($ADMIN_USER == "admin") && ($_SESSION['utype'] == "system")) {
  $adescrip["maxAliases"]=_("Max No. Of Aliases User Can Assign Per Mailbox");
  $adescrip["maxMailBoxes"]=_("Max No. Of Mailboxes User Can Assign");
  $adescrip["maxWebAliases"]=_("Max No. Of Web Sites User Can Assign");
  $adescrip["quotaHomeDir"]=_("Maximum Size Of Home Directory (Mb)");
  $adescrip["quotaFileServer"]=_("Maximum Size Allowed On File Server (Mb)");
  $adescrip["quotaMailSpool"]=_("Maximum Size Of Incoming Mail (Mb)");
}

$atrib=array("uid","clearPassword","homeDirectory","uidNumber","gidNumber","radiusAuthType","loginShell",
             "shadowMin","radiusPortType","radiusServiceType","radiusFramedProtocol","radiusFramedIPAddress",
             "radiusFramedIPNetmask","radiusFramedMTU","radiusFramedCompression","radiusSimultaneousUse",
             "quotaChanged","quotaHomeDir","quotaFileServer","quotaMailSpool","cn","displayName","givenName",
             "sn","shadowMax","shadowWarning","shadowInactive","shadowExpire",
             "dialupAccess","maxAliases","maxMailBoxes","maxWebAliases","accountSuspended","mailRoutingAddress","radiusRealm",
             "radiusSessionTimeout","radiusIdleTimeout","radiusProfileDN");

if ($ds) {
  if (isset($adduser)) {

    $uidNumber=$newuidnumber;
    $uid=strtolower($uid);
    $time=time();

    if ($_SESSION['utype'] == "system") {
      $dn="uid=$uid,ou=Users";
    } else {
      $dn="uid=$uid,o=" . $_SESSION['utype'] . ",ou=Users";
    }

    $info["objectClass"][0]="person";
    $info["objectClass"][1]="inetOrgPerson";
    $info["objectClass"][2]="officePerson";
    $info["objectClass"][3]="organizationalPerson";
    $info["objectClass"][4]="posixAccount";
    $info["objectClass"][5]="shadowAccount";
    $info["objectClass"][6]="inetLocalMailRecipient";
    $info["objectClass"][7]="radiusprofile";
    $info["objectClass"][8]="pkiUser";

    $gidNumber=100;

    if (isset($exuser)) {
      $exchangeServerAccess="yes";
    }


    $accountSuspended="unsuspended";
    $quotaChanged="yes";

    if ($virtzone == "") {
      $radiusServiceType="Framed-User";
      $radiusFramedProtocol="PPP";
      $radiusFramedIPAddress="255.255.255.254";
      $radiusFramedIPNetmask="255.255.255.255";
      $radiusAuthType="Pam";
      $radiusFramedMTU="1500";
      $radiusFramedCompression="Van-Jacobson-TCP-IP";
      $radiusSimultaneousUse="1";
      $radiusRealm="DEFAULT";
      $radiusPortType="xDSL";
      $radiusIdleTimeout="1800";
      $radiusSessionTimeout="86400";
      $dialupAccess="yes";
    } else {
      $radiusProfileDN="cn=" . $virtzone . ",ou=Vadmin";
    }

    $cn="$givenName $sn";
    $mailRoutingAddress=$uid;
    $displayName=$cn;
    if (file_exists("/etc/.networksentry-lite")) {
      unset($mail);
    }
    $homeDirectory="/var/home/" . $uid[0] . "/" . $uid[1] . "/" . $uid;

    $loginShell="/usr/sbin/smrsh";
    $shadowMin="0";
    $shadowMax="99999";
    $shadowWarning="0";
    $shadowInactive="0";
    $shadowExpire="65535";

    if ($virtzone != "") {
      $vzad=ldap_search($ds,"cn=$virtzone,ou=VAdmin","(&(cn=$virtzone)(objectclass=virtZoneSettings))");
      $vzent=ldap_first_entry($ds,$vzad);
      $vzmem=ldap_get_attributes($ds,$vzent);

      $maxAliases=$vzmem["maxAliases"][0];
      $maxMailBoxes=$vzmem["maxMailBoxes"][0];
      $maxWebAliases=$vzmem["maxWebAliases"][0];
      $quotaHomeDir=$vzmem["quotaHomeDir"][0];
      $quotaFileServer=$vzmem["quotaFileServer"][0];
      $quotaMailSpool=$vzmem["quotaMailSpool"][0];

      $dialupAccess=$vzmem["dialupAccess"][0];
    }     

    if (($pass1 == $pass2) && ($pass1 != "")) {
      $clearPassword=$pass1;
      $natrib=$atrib;
      while(list($idx,$catt)=each($natrib)) {
        if ($$catt != "") {
          $info[$catt]=$$catt;
        }
      }
      if (!ldap_add($ds,$dn,$info)) {
        $err=ldap_error($ds);
%>

<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<%
        print "<TR><TD COLSPAN=2><FONT COLOR=RED>Not Added<P>$err $ldn $dn</TD></TR>";
      } else {
        if (isset($exuser)) {
          include "pgauth.inc";

          $sqlfile="user.sql";
          $sfd=fopen($sqlfile,"r");
          $usql=fread($sfd,filesize($sqlfile));
          fclose ($sfd);
          $upat=array("'\\$\{user\}'i","'xnow\(\)'i");
          $rpat=array($uid,time());
          $usql=preg_replace($upat,$rpat,$usql);
          $tbl_cre=pg_query($db,$usql);
        }
        $euser=$uid;
        $_SESSION['classi']=$uid;
        $_SESSION['disppage']="ldap/userinfo.php";
        $newacc="yes";
        include "userinfo.php";
        return;
      }
    } else {
%>
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<%
        print "<TR><TD COLSPAN=2 ALIGN=CENTER><FONT COLOR=RED>Not Added Password Mismatch</TD></TR>";
    }
  } else {
    $maxAliases=1;
    $maxMailBoxes=1;
    $maxWebAliases=1;
    $quotaHomeDir=10;
    $quotaFileServer=20;
    $quotaMailSpool=5;
%>
<CENTER>
<FORM METHOD=POST NAME=useradd onsubmit="ajaxsubmit(this.name);return false">
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=2><%print _("Adding New System User");%></TH></TR>
<%
  }
}

/*  document.loadpage.submit();
*/
%>


<INPUT TYPE=HIDDEN NAME=adduser>
<INPUT TYPE=HIDDEN NAME=showmenu>
<INPUT TYPE=HIDDEN NAME=classi>
<INPUT TYPE=HIDDEN NAME=disppage VALUE="ldap/adduser.php">
<%
while(list($attr,$aname)=each($adescrip)) {
  $rem=$cnt % 2;
  if ($rem == 1) {
    $bcolor=" CLASS=list-color2";
    $bcolor2=" CLASS=list-color1";
  } else {
    $bcolor=" CLASS=list-color1";
    $bcolor2=" CLASS=list-color2";
  }

%>
  <TR<% print $bcolor%>><TD WIDTH=50% onmouseover="myHint.show('<%print strtolower($attr);%>')" onmouseout="myHint.hide()">
<%
      print $adescrip[$attr];
  if ($attr != "clearPassword") {
%>
    </TD><TD WIDTH=50%>
      <INPUT TYPE=TEXT SIZE=40 NAME=<%print $attr;%> VALUE="<%print $$attr;%>">
    </TD></TR>
<%
  } else {
%>
    </TD><TD WIDTH=50%>
      <INPUT TYPE=PASSWORD SIZE=40 NAME=pass1 VALUE="">
    </TD></TR>
  <TR<% print $bcolor2%>><TD WIDTH=50% onmouseover="myHint.show('PW2')" onmouseout=myHint.hide()><%print _("Confirm Password");%>
    </TD><TD WIDTH=50%>
      <INPUT TYPE=PASSWORD SIZE=40 NAME=pass2 VALUE="">
    </TD></TR>  
<%
    $cnt ++;
  }
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
  if (! file_exists("/etc/.networksentry-lite")) {
    if (($ADMIN_USER == "admin") && ($_SESSION['utype'] == "system")){
      $bgcoltmp=$bgcolor["0"];
      $bgcolor["1"]=$bgcolor["1"];
      $bgcolor["0"]=$bgcoltmp;
    } else {
      $bgcoltmp=$bgcolor["1"];
      $bgcolor["1"]=$bgcolor["0"];
      $bgcolor["0"]=$bgcoltmp;
    }

    $svz=ldap_search($ds,"cn=" . $_SESSION['utype'] . ",ou=Vadmin","(&(member=$ldn)(objectclass=virtZoneSettings))");
    $vzinfo = ldap_get_entries($ds, $svz);
    if (($ADMIN_USER == "admin") && ($_SESSION['utype'] == "system")){
%>
      <TR<% print $bgcolor["0"]%>><TD WIDTH=50%>
      <%print _("Add To Virtual Zone");%></TD>
      <TD WIDTH=50%><SELECT NAME=virtzone>
      <OPTION VALUE=""><%print _("None");%>
<% 
      for ($i=0; $i<$vzinfo["count"]; $i++) {
        $vzsort[$i]=$vzinfo[$i]["cn"][0];
      }
      asort($vzsort);
      reset ($vzsort);

      while (list($i,$val) = each($vzsort)) {
        $cname=$vzinfo[$i]["cn"][0];
        if ($cname != "Virtual Admin Access") {
          print "<OPTION VALUE=\"" . $cname . "\">" . $cname . "\n";
        }
      }
      print "</SELECT></TD></TR>\n";
      print "<TR " . $bgcolor["1"] . "><TD ALIGN=MIDDLE COLSPAN=2 WIDTH=50%>";
    } else {
      for ($i=0; $i<$vzinfo["count"]; $i++) {
        if ($vzinfo[$i]["cn"][0] != "Virtual Admin Access") {
          $vzone=$vzinfo[$i]["cn"][0];
        }
      }
      print "<INPUT TYPE=HIDDEN NAME=virtzone VALUE=\"$vzone\">";
      print "<TR " . $bgcolor["1"] . "><TD ALIGN=MIDDLE COLSPAN=2 WIDTH=50%>";
    }
  } else {
    print "<TR " . $bgcolor["1"] . "><TD ALIGN=MIDDLE COLSPAN=2 WIDTH=50%>";
  }
  if ($modify) {
    print "<INPUT TYPE=BUTTON VALUE=Modify onclick=this.name='update'>";
    print "<INPUT TYPE=SUBMIT VALUE=Delete onclick=this.name='delete'>";
    print "<INPUT TYPE=RESET VALUE=Reset>";
  } else {
    print "<INPUT TYPE=BUTTON VALUE=\"" . _("Add") . "\" NAME=submited ONCLICK=javascript:addnewuser()>";
  }
%>
    </TD></TR>
</TABLE>
</FORM>

