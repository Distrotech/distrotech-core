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
if (!$rdn) {
  include "auth.inc";
}
%>

<FORM METHOD=POST NAME=mbform onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=classi VALUE="<%print $euser;%>">
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>
<%
  print _("Editing Mail Routing");
%>
</TH></TR>
<%
  $disc=array(_("Name"),_("Destination"),_("Mail Relay"),_("Aliases"));
  $iarr=array("cn","mailroutingaddress","mailhost","maillocaladdress");
  $jshint=array("MB2","MB3","MB7","MB4","MB8");
  $tarr=array("mailRoutingAddress","mailHost");

  $info=strtolower($info);

  if (isset($modrec)) {
    $sr=ldap_search($ds,$ambox,"objectClass=inetlocalmailrecipient",$iarr);
    $iinfo = ldap_get_entries($ds, $sr);
  
    while(list($idx,$catt)=each($tarr)) {
     $var=strtolower($catt);
     if ($$var != "") {
       $minfo[$var]=$$var;
     } else if (isset($iinfo[0][$var][0])) {
       $dinfo[$catt]=$iinfo[0][$var][0];
     }
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
  } else if (($ambox == "") && ($newambox != "") && (isset($amboxup))) {
    $newambox=strtolower($newambox);

    $ambox="cn=" . $newambox . ",ou=Email";
    $info["objectClass"][0]="nisMailAlias";
    $info["objectClass"][1]="inetLocalMailRecipient";
    $info["cn"]=$newambox;

    if ($newamboxmh != "") {
      $info["mailHost"]=$newamboxmh;
    }
    if ($newamboxmr != "") {
      $info["mailRoutingAddress"]=$newamboxmr;
    }

    ldap_add($ds,$ambox,$info);
    $newambox="";
//    $ambox="";
  }

 if ($ambox == "") {
    $dnarr=array("dn","cn");

    $sr=ldap_search($ds,"ou=email","(&(objectclass=nisMailAlias)(objectclass=inetLocalMailRecipient))",$dnarr);
    $iinfo = ldap_get_entries($ds, $sr);
%>
    <TR CLASS=list-color1>
      <TD onmouseover="myHint.show('MB1')" onmouseout="myHint.hide()" WIDTH=75%>
      <%print _("Modify/Add Mailbox");%></TD>
      <TD><SELECT NAME=ambox>
        <OPTION VALUE=""><%print _("Add New Mail Routing");%></OPTION><%
      for($cnt=0;$cnt<$iinfo['count'];$cnt++) {
        print "<OPTION VALUE=\"" . $iinfo[$cnt]["dn"] . "\">" .  $iinfo[$cnt]["cn"][0] . "</OPTION>\n";
      }%>
      </TD></TR>
        <TR CLASS=list-color2><TD onmouseover="myHint.show('MB3')" onmouseout="myHint.hide()" WIDTH=75%><%print _("Description")%></TD>
        <TD WIDTH=75%><INPUT TYPE=TEXT NAME=newambox><TD></TR>
        <TR CLASS=list-color1><TD onmouseover="myHint.show('MB4')" onmouseout="myHint.hide()" WIDTH=75%><%print _("Mail Host To Redirect To");%></TD>
        <TD WIDTH=75%><INPUT TYPE=TEXT NAME=newamboxmh></TD></TR>
        <TR CLASS=list-color2><TD onmouseover="myHint.show('MB4')" onmouseout="myHint.hide()" WIDTH=75%><%print _("Destination Routing Address");%></TD>
        <TD WIDTH=75%><INPUT TYPE=TEXT NAME=newamboxmr></TD></TR>
        <TR CLASS=list-color1>
      <TD COLSPAN=2 ALIGN=CENTER><INPUT TYPE=HIDDEN NAME=basedn VALUE="<%print $basedn%>"><INPUT TYPE=SUBMIT onclick=this.name='amboxdel' VALUE="Delete">
        <INPUT TYPE=SUBMIT onclick=this.name='amboxup' VALUE="<%print _("Update/Add");%>">
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
           %><INPUT TYPE=BUTTON VALUE="Modify Aliases" onclick=javascript:openaliasedit('<%print urlencode($iinfo[0]['cn'][0]);%>')><%
        } elseif ($attr == "cn") {
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

<TR <%print $bcol[2];%>><TH COLSPAN=2>  
  <INPUT TYPE=HIDDEN NAME=ambox VALUE="<%print $ambox%>">
  <INPUT TYPE=SUBMIT VALUE="Modify" onclick=this.name='modrec'>
  <%if ($ADMIN_USER == "admin") {%>
    <INPUT TYPE=SUBMIT onclick=this.name='amboxdel' VALUE="Delete"><%
  }%>
</TH></TR>
</TABLE></FORM>
