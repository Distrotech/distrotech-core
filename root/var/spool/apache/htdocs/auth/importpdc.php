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
if (!isset($_SESSION['auth'])) {
  exit;
}
  if ((!isset($_SESSION['classi'])) || ($_SESSION['classi'] == "")) {
    $euser=$PHP_AUTH_USER;
  } else {
    $euser=$_SESSION['classi'];
  }

  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

$disc=array(_("File Server Quota"),_("Home Directory Quota"),_("Mail Box Quota"),_("Allow Access To Proxy"));

%>
<FORM METHOD=POST>
<INPUT TYPE=HIDDEN NAME=classi VALUE="<%print $euser;%>">
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body><%print _("Import User To System From PDC");%></TH></TR>
<%
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

  $iarr=array("quotafileserver","quotahomedir","quotamailspool","squidProxyAccess");
  $dnarr=array("dn");
  $info=strtolower($info);

  $sr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=$euser))",$dnarr);
 
  $iinfo = ldap_get_entries($ds, $sr);
  $dn=$iinfo[0]["dn"];

  if (isset($modrec)) {
     %>
     <SCRIPT>
       alert("<%print _("Quota Changes Are Updated Every 4 Hours");%>\n(00:00 04:00 08:00 12:00 16:00 20:00)");
     </SCRIPT>
     <%

    $ocadd["objectClass"][0]="posixAccount";
    $ocadd["objectClass"][1]="shadowAccount";
    $ocadd["homeDirectory"]="/var/home/" . substr($euser,0,1) . "/" . substr($euser,1,1) . "/"  . $euser;
    $ocadd["gidNumber"]="100";
    ldap_mod_add($ds,$dn,$ocadd);

    $minfo["quotafileserver"]=$quotafileserver;
    $minfo["quotamailspool"]=$quotamailspool;
    $minfo["quotahomedir"]=$quotahomedir;
    $minfo["quotachanged"]="yes";
    $minfo["loginShell"]="/usr/sbin/smrsh";
    $minfo["shadowMin"]="0";
    $minfo["shadowMax"]="99999";
    $minfo["shadowWarning"]="0";
    $minfo["shadowInactive"]="0";
    $minfo["shadowExpire"]="65535";
    $minfo["clearPassword"]=$userpass1;

    if ($squidProxyAccess == "on") {
      $minfo["squidproxyaccess"]="yes";
    } else {
      $minfo["squidproxyaccess"]="no";
    }
    ldap_modify($ds,$dn,$minfo);

    $sr=ldap_search($ds,$dn,"objectclass=*",array("dn"));
    $iinfo = ldap_get_entries($ds, $sr);
    $childdn=array();
    for($ccnt=0;$ccnt < $iinfo["count"];$ccnt++) {
      $dninf=ldap_explode_dn($iinfo[$ccnt]["dn"],0);
      if ($dn != $iinfo[$ccnt]["dn"]) {
        ldap_rename($ds,$iinfo[$ccnt]["dn"],$dninf[0],"uid=admin,ou=Users",false);
        array_push($childdn,$dninf[0]);
      }
    }

    ldap_rename($ds,$dn,"uid=" . $euser,"ou=Users",false);
 
    for($ccnt=0;$ccnt < count($childdn);$ccnt++) {
      ldap_rename($ds,$childdn[$ccnt] . ",uid=admin,ou=users",$childdn[$ccnt],"uid=" . $euser . ",ou=Users",false);
    }

    $sr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=$euser))",array("dn","sambaSID"));
    $iinfo = ldap_get_entries($ds, $sr);
    $dn=$iinfo[0]["dn"];
    $delsmb["objectClass"][0]="simpleSecurityObject";
    $delsmb["objectClass"][1]="sambaIdmapEntry";
    ldap_mod_del($ds,$dn,$delsmb);
  }

  $sr=ldap_search($ds,$dn,"(&(objectClass=officePerson)(uid=$euser))",$iarr);
 
  $iinfo = ldap_get_entries($ds, $sr);
  
  for ($i=0; $i < count($iarr); $i++) {
    $rem=$i % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color1";
    }
%>
    <TR<%print $bcolor;%>>
      <TD onmouseover="myHint.show('<%print strtolower($iarr[$i]);%>')" onmouseout="myHint.hide()" WIDTH=75%>
        <% print $disc[$i];
        if ($i <= "2") {
          print " (Mb)";
        }%>
      </TD>
      <TD>
<%
        $attr=strtolower($iarr[$i]);
        if ($iinfo[0][$attr][0] == "") {
          $iinfo[0][$attr][0]="0";
        }
        if ($ADMIN_USER == "admin") {
          if ($i <= "2") {
%>
            <INPUT TYPE=TEXT NAME=<%print $iarr[$i];%> VALUE="<%print $iinfo[0][$attr][0];%>">
<%
          } else {
%>
            <INPUT TYPE=CHECKBOX NAME="<%print $iarr[$i];%>"<%if ($iinfo[0][$attr][0] == "yes") {print " CHECKED";}%>>
<%            
          }
        } else {
          print $iinfo[0][$attr][0];
        }
%>
      </TD></TR>
<%
  }
  $rem=$i % 2;
  if ($rem == 1) {
    $bcol[1]=" CLASS=list-color1";
    $bcol[2]=" CLASS=list-color2";
  } else {
    $bcol[2]=" CLASS=list-color1";
    $bcol[1]=" CLASS=list-color2";
  }
%>
<TR <%print $bcol[2];%>><TD onmouseover="myHint.show('PW1')" onmouseout="myHint.hide()"><%print _("New Password For Account");%></TD><TD><INPUT TYPE=PASSWORD NAME=userpass1></TD></TR>
<TR <%print $bcol[1];%>><TD onmouseover="myHint.show('PW2')" onmouseout="myHint.hide()"><%print _("Confirm Password");%></TD><TD><INPUT TYPE=PASSWORD NAME=userpass2></TD></TR>
<%
  if ($ADMIN_USER == "admin") {
%>
<TR <%print $bcol[2];%>><TH COLSPAN=2>  
  <INPUT TYPE=SUBMIT VALUE="<%print _("Modify");%>" NAME=modrec></TH></TR>
<%
  }
%>
</TABLE></FORM>
