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
%>

<FORM METHOD=POST NAME=mbform onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=classi VALUE="<%print $euser;%>">
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>
<%
  if (($_SESSION['utype'] != "system") && ($_SESSION['utype'] != "pdc")) {
    $sr=ldap_search($ds,"cn=" . $_SESSION['utype'] . ",ou=Vadmin","(&(objectclass=virtZoneSettings)(member=" . $ldn . ")(cn=" . $_SESSION['utype'] . "))");
  } else {
    $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  }
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }
  $ADMIN_USER="admin";

  print _("Editing Additional Mailbox");
%>
</TH></TR>
<%
  $disc=array(_("Login"),_("Name"),_("Destination"),_("Mail Relay"),_("Aliases"));
  $iarr=array("uid","cn","mailroutingaddress","mailhost","maillocaladdress");
  $jshint=array("MB2","MB3","MB7","MB4","MB8");
  $tarr=array("cn","mailRoutingAddress","mailHost");

  $info=strtolower($info);

  if ($newambox != "") {
    $notuidu=ldap_search($ds,"ou=Users","uid=$newambox");
    $notuidw=ldap_search($ds,"ou=Idmap","uid=$newambox");
  }

  if (isset($modrec)) {
    $sr=ldap_search($ds,$ambox,"objectClass=inetlocalmailrecipient",$iarr);
    $iinfo = ldap_get_entries($ds, $sr);
  
    while(list($idx,$catt)=each($tarr)) {
     $var=strtolower($catt);
     if ($$var != "") {
       $minfo[$var]=$$var;
     } else {
       $dinfo[$catt]=$iinfo[0][$var][0];
     }
    }

    if (($newamboxpass1 == $newamboxpass2 ) && ($newamboxpass1 != "")) {
      $minfo["userPassword"]="{CRYPT}" . crypt($newamboxpass1);
    } else if ($newamboxpass1 != "") {%>
<SCRIPT>
  alert("Password Unchanged !\nPassword Mismatch.");
</SCRIPT><%
    }


    ldap_mod_del($ds,$ambox,$dinfo);
    ldap_modify($ds,$ambox,$minfo);
    $ldaperr=ldap_error($ds);
    $ambox="";
  } else if ((isset($amboxdel)) && ($ambox != "")) {
    if ($ambox != $basedn) {
      ldap_delete($ds,$ambox);
    }
    $ambox="";
  } else if (($ambox == "") && ($newambox != "") && (isset($amboxup)) &&
             (ldap_count_entries($ds,$notuidu) == 0) && (ldap_count_entries($ds,$notuidw) == 0)) {
    $newambox=strtolower($newambox);
    $ambox="uid=" . $newambox . "," . $basedn;

    $info["objectClass"][0]="person";
    $info["objectClass"][1]="posixAccount";
    $info["objectClass"][2]="inetLocalMailRecipient";
    $info["objectClass"][3]="shadowAccount";
    $info["uid"]=$newambox;
    $info["mailRoutingAddress"]=$newambox;
    $info["uidNumber"]=$newuidnumber;
    $info["shadowMin"]="0";
    $info["shadowMax"]="99999";
    $info["shadowExpire"]="65535";
    $info["shadowWarning"]="0";
    $info["shadowInactive"]="0";
    $info["homeDirectory"]="/dev/null";
    $info["loginShell"]="/usr/bin/true";
    $info["gidNumber"]="300";
    if ($newamboxcn != "") {
      $info["cn"]=$newamboxcn;
    } else {
      $info["cn"]=$newambox;
    }
    if ($newamboxmh != "") {
      $info["mailHost"]=$newamboxmh;
    }
    $time=time();
    $info["shadowLastChange"]=($time -($time % 86400))/86400;

    if (($newamboxpass1 == $newamboxpass2 ) && ($newamboxpass1 != "")) {
      $info["userPassword"]="{CRYPT}" . crypt($newamboxpass1);
    } else {
      $info["userPassword"]="{CRYPT}" . crypt($newambox);%>
<SCRIPT>
  alert("Password Set To User Name !\nNo Password Supplied Or Password Mismatch.");
</SCRIPT><%
    }
    ldap_add($ds,$ambox,$info);
    $newambox="";
    $ambox="";
  }

 if ($ambox == "") {
    $dnarr=array("dn","cn");

    $dnsr=ldap_search($ds,"","(&(objectclass=officePerson)(uid=$euser))",array("dn","maxAliases","maxMailBoxes","cn"));
    $fentry=ldap_first_entry($ds,$dnsr);
    $basedn=ldap_get_dn($ds,$fentry);
    $uinf=ldap_get_attributes($ds,$fentry);

    $dnarr2=ldap_explode_dn($basedn,0);
    if (eregi("^o=(.*)",$dnarr2[1],$mbowner)) {
      $mbdn="cn=" . $mbowner[1] . ",ou=Vadmin";
      $mbsr=ldap_search($ds,$mbdn,"(&(objectClass=virtZoneSettings)(cn=" . $mbowner[1] . "))",array("maxMailBoxes","maxAliases"));
      $mbiinfo = ldap_get_entries($ds, $mbsr);
      $uinf["maxMailBoxes"][0]=$mbiinfo[0]["maxmailboxes"][0];
      $uinf["maxAliases"][0]=$mbiinfo[0]["maxaliases"][0];
    }
  
    if ($maxmbox=$uinf["maxMailBoxes"][0] <= "0") {
      $maxmbox="0";
    } else {
      $maxmbox=$uinf["maxMailBoxes"][0];
    }

    $sr=ldap_search($ds,$basedn,"(&(objectclass=inetLocalMailRecipient)(gidnumber=300))",$dnarr);
    $iinfo = ldap_get_entries($ds, $sr);
%>
    <TR CLASS=list-color1>
    <%if ($maxmbox == "0") {%>
      <TH CLASS=heading-body2 COLSPAN=2>
      No Mailboxes Allowed</TH>
    <%} else if ($iinfo['count'] < $maxmbox) {%>
      <TD onmouseover="myHint.show('MB1')" onmouseout="myHint.hide()" WIDTH=75%>
      <%print _("Modify/Add Mailbox");%></TD>
    <%} else {%>
      <TD onmouseover="myHint.show('MB1')" onmouseout="myHint.hide()" WIDTH=75%>
      <%print _("Modify Mailbox");%></TD>
    <%}
    if ($maxmbox > 0) {
    %>
      <TD><SELECT NAME=ambox><%
      if ($iinfo['count'] < $maxmbox) {%>
        <OPTION VALUE=""><%print _("Add New Mailbox To User");%></OPTION><%
      }
      for($cnt=0;$cnt<$iinfo['count'];$cnt++) {
        $mbuid=ldap_explode_dn($iinfo[$cnt]["dn"],1);
        print "<OPTION VALUE=\"" . $iinfo[$cnt]["dn"] . "\">" .  $iinfo[$cnt]["cn"][0] . " (" . $mbuid[0] . ")</OPTION>\n";
      }%>
      </TD></TR><%
      if ($iinfo['count'] < $maxmbox) {%>
        <TR CLASS=list-color2><TD onmouseover="myHint.show('MB2')" onmouseout="myHint.hide()" WIDTH=75%><B><%print _("New Mailbox Login");%></TD>
        <TD WIDTH=75%><INPUT TYPE=TEXT NAME=newambox></TD></TR>
        <TR CLASS=list-color1><TD onmouseover="myHint.show('MB3')" onmouseout="myHint.hide()" WIDTH=75%><%print _("Mailbox Owners Name")%></TD>
        <TD WIDTH=75%><INPUT TYPE=TEXT NAME=newamboxcn></TD></TR>
        <TR CLASS=list-color2><TD onmouseover="myHint.show('MB4')" onmouseout="myHint.hide()" WIDTH=75%><%print _("Mail Host To Redirect To");%></TD>
        <TD WIDTH=75%><INPUT TYPE=TEXT NAME=newamboxmh></TD></TR>
        <TR CLASS=list-color1><TD onmouseover="myHint.show('MB5')" onmouseout="myHint.hide()" WIDTH=75%><%print _("New Mailbox Password");%></TD>
        <TD WIDTH=75%><INPUT TYPE=PASSWORD NAME=newamboxpass1></TD></TR>
        <TR CLASS=list-color2><TD onmouseover="myHint.show('MB6')" onmouseout="myHint.hide()" WIDTH=75%><%print _("Confirm Password")%></TD>
        <TD WIDTH=75%><INPUT TYPE=PASSWORD NAME=newamboxpass2></TD></TR>
        <TR CLASS=list-color1><%
      } else {%>
        <TR CLASS=list-color2><%
      }%>
      <TD COLSPAN=2 ALIGN=CENTER><INPUT TYPE=HIDDEN NAME=basedn VALUE="<%print $basedn%>"><INPUT TYPE=SUBMIT onclick=this.name='amboxdel' VALUE="Delete"><%
      if ($iinfo['count'] < $maxmbox) {%>
        <INPUT TYPE=SUBMIT onclick=this.name='amboxup' VALUE="<%print _("Update/Add");%>"><%
      } else {%>
        <INPUT TYPE=SUBMIT onclick=this.name='amboxup' VALUE="<%print _("Update");%>"><%
      }
    }%>
    </TD></TR></TABLE></FORM>
<%
    return;
  }

  $sr=ldap_search($ds,$ambox,"objectClass=inetlocalmailrecipient",$iarr);
  $iinfo = ldap_get_entries($ds, $sr);
  
  $uidinf=ldap_explode_dn($iinfo[0]["dn"],1);

  for ($i=0; $i < count($iarr); $i++) {
    $rem=$i % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color1";
    }
    $attr=strtolower($iarr[$i]);
%>
    <TR<%print $bcolor;%>>
      <TD onmouseover="myHint.show('<%print $jshint[$i];%>')" onmouseout="myHint.hide()" WIDTH=75%>
        <% print $disc[$i];%> 
      </TD>
      <TD>
<%
        if ($attr == "maillocaladdress") {
           %><INPUT TYPE=BUTTON VALUE="Modify Aliases" onclick=javascript:openaliasedit('<%print urlencode($iinfo[0]['uid'][0]);%>')><%
        } elseif ($attr == "uid") {
           print $uidinf[0];
        } else {%>
            <INPUT TYPE=TEXT NAME=<%print $iarr[$i];%> VALUE="<%print $iinfo[0][$attr][0];%>"><%
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
              <TR <%print $bcol[2];%>>
              <TD onmouseover="myHint.show('MB5')" onmouseout="myHint.hide()" WIDTH=75%>
                 New Mailbox Password
              </TD><TD WIDTH=75%>
                <INPUT TYPE=PASSWORD NAME=newamboxpass1>
              </TD></TR>

              <TR <%print $bcol[1];%>>
              <TD onmouseover="myHint.show('MB6')" onmouseout="myHint.hide()" WIDTH=75%>
                 Confirm Password
              </TD><TD WIDTH=75%>
                <INPUT TYPE=PASSWORD NAME=newamboxpass2>
              </TD></TR>

<TR <%print $bcol[2];%>><TH COLSPAN=2>  
  <INPUT TYPE=HIDDEN NAME=ambox VALUE="<%print $ambox%>">
  <INPUT TYPE=SUBMIT VALUE="Modify" onclick=this.name='modrec'>
  <%if ($ADMIN_USER == "admin") {%>
    <INPUT TYPE=SUBMIT onclick=this.name='amboxdel' VALUE="Delete"><%
  }%>
</TH></TR>
</TABLE></FORM>
