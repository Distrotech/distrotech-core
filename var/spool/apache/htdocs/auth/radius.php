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

if (!isset($_SESSION['auth'])) {
  exit;
}
*/

  $listitem['radiuscheckitem']=1;
  $listitem['radiusreplyitem']=1;
  $cntr['radiuscheckitem']="checkcount";
  $cntr['radiusreplyitem']="repcount";
  $disc=array(_("Realm"),_("Active"),_("Port Type"),_("IP Address"),_("MTU"),_("Compression"),_("Sim. Use"),
              _("Session Timeout"),_("Idle Timeout"),_("Interim Accounting Interval"),_("Reply Items"),_("Check Items"));
  $iarr=array("radiusrealm","dialupaccess","radiusporttype","radiusframedipaddress","radiusframedmtu",
              "radiusframedcompression","radiussimultaneoususe","radiussessiontimeout","radiusidletimeout",
              "radiusacctinteriminterval","radiusreplyitem","radiuscheckitem","radiusProfileDn");
  $jshint=array("RP2","RP3","RP4","RP5","RP6","RP7","RP8","RP9","RP10","RP11","RP12","RP13");
  $dnarr=array("dn","radiusrealm","dialupaccess","radiusprofiledn");

  $info=strtolower($info);

  if (($_SESSION['classi'] == "") && ($_SESSION['utype'] == "")) {
    $_SESSION['classi']=$PHP_AUTH_USER;
  }
%>
<FORM METHOD=POST NAME=radmod onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=disppage VALUE="<%print $showpage;%>">
<INPUT TYPE=HIDDEN NAME=nomenu VALUE="<%if ($_POST['nomnenu'] < 2) {print $_POST['nomenu'];}%>">
<INPUT TYPE=HIDDEN NAME=utype VALUE="<%print $_POST['utype'];%>">
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>
<%

  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

  if (($_SESSION['utype'] != "system") && ($_SESSION['utype'] != "pdc") && ($_SESSION['utype'] != "") && ($_SESSION['classi'] == "")) {
    $sr=ldap_search($ds,"cn=" . $_SESSION['utype'] . ",ou=Vadmin","(&(objectclass=virtZoneSettings)(member=" . $ldn . ")(cn=" . $_SESSION['utype'] . "))");
    $virtzone=1;
  } else {
    $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
    $virtzone=0;
  }

  if ($virtzone) {
    $dnsr=ldap_search($ds,"cn=" . $_SESSION['utype'] . ",ou=Vadmin","(&(cn=" . $_SESSION['utype'] . "))",$dnarr);
  } else {
    $dnsr=ldap_search($ds,"","(&(uid=" . $_SESSION['classi'] . "))",$dnarr);
  }



  $fentry=ldap_first_entry($ds,$dnsr);
  $basedn=ldap_get_dn($ds,$fentry);

  if ($virtzone) {
    $fattrs=ldap_get_attributes($ds,$fentry);
    $radrealm=$basedn;
  } else {
    $dninf=ldap_explode_dn($basedn,0);
    if (eregi("^o=(.*)",$dninf[1],$vzinf)) {
      $fattrs=ldap_get_attributes($ds,$fentry);
      $radrealm=$fattrs['radiusProfileDn'][0];
      $lactive=$fattrs['dialupAccess'][0];
      $virtzone=1;
      $ADMIN_USER="pleb";
    }
  }

  if ($ADMIN_USER == "admin") {
    print _("Editing") . " ";
  } else {
    print _("Viewing") . " ";
  }
  print _("Radius Profile") . "</TH></TR>\n";


  $uparr=array("dialupaccess","radiusporttype","radiusframedipaddress","radiusframedmtu","radiusframedcompression",
               "radiussessiontimeout","radiusidletimeout","radiussimultaneoususe","radiusacctinteriminterval");

  if (isset($modrec)) {
    if ($dialupaccess == "on") {
      $dialupaccess="yes";
    } else {
      $dialupaccess="no";
    }
    $minfo["radiusservicetype"]="Framed-User";
    $minfo["radiusFramedProtocol"]="PPP";
    $minfo["radiusAuthType"]="Pam";
    $minfo["radiusframedipnetmask"]="255.255.255.255";

    $todel=array();
    for($ucnt=0;$ucnt<count($uparr);$ucnt++) {
      if ($$uparr[$ucnt] != "") {
        $minfo[$uparr[$ucnt]]=$$uparr[$ucnt];
      } else {
        array_push($todel,$uparr[$ucnt]);
      }
    }

    if (count($todel) > 0) {
      $delsr=ldap_search($ds,$radrealm,"objectClass=radiusprofile",$todel);
      $info = ldap_get_entries($ds, $delsr);
      for($cnt=0;$cnt < count($todel);$cnt++) {
        if ($info[0][strtolower($todel[$cnt])][0] != "") {
          $delinf[$todel[$cnt]]=$info[0][strtolower($todel[$cnt])][0];
        }
      }
      ldap_mod_del($ds,$radrealm,$delinf);
    }    

    ldap_modify($ds,$radrealm,$minfo);

    $dcnt=0;
    for ($i=0; $i<$checkcount; $i++) {
      $ent="delradiuscheckitem" . $i;
      $entv="delhradiuscheckitem" . $i;
      if ($$ent) {
        $dcinfo['radiusCheckItem'][$dcnt]=stripslashes($$entv);
        $dcnt++;
      }
    }
    if (count($dcinfo['radiusCheckItem']) > 0) {
      ldap_mod_del($ds,$radrealm,$dcinfo);
    }

    $dcnt=0;
    for ($i=0; $i<$repcount; $i++) {
      $ent="delradiusreplyitem" . $i;
      $entv="delhradiusreplyitem" . $i;
      if ($$ent) {
        $drinfo['radiusReplyItem'][$dcnt]=stripslashes($$entv);
        $dcnt++;
      }
    }
    if (count($drinfo['radiusReplyItem']) > 0) {
      ldap_mod_del($ds,$radrealm,$drinfo);
    }

    if (($newcheckval != "") && ($newcheck != "")) {
      $acinfo['radiusCheckItem'][0]=$newcheck . " = \"" . $newcheckval . "\"";
      ldap_mod_add($ds,$radrealm,$acinfo);
    }
    if (($newrepval != "") && ($newrep != "")) {
      $arinfo['radiusReplyItem'][0]=$newrep . " = \"" . $newrepval . "\"";
      ldap_mod_add($ds,$radrealm,$arinfo);
    }
  } else if ((isset($raddel)) && ($radrealm != "")) {
    if ($radrealm != $basedn) {
      ldap_delete($ds,$radrealm);
    }
  }


  if ((($radrealm == "") && ($newradrealm == "")) || (isset($raddel))) {
    $sr=ldap_search($ds,$basedn,"(&(radiusrealm=*))",$dnarr);
    $iinfo = ldap_get_entries($ds, $sr);
%>
      <TR onmouseover="myHint.show('RP1')" onmouseout="myHint.hide()" CLASS=list-color1>
        <TD WIDTH=50%>
          <%print _("Modify/Add Realm");%>
        </TD>
        <TD><SELECT NAME=radrealm>
<%
    if ($ADMIN_USER == "admin") {
      print "<OPTION VALUE=\"\">" . _("Add New Realm To User") . "</OPTION>";
    }
    print "<OPTION VALUE=\"" . $basedn . "\">Default Profile";
    for($cnt=0;$cnt<$iinfo['count'];$cnt++) {
      if ($iinfo[$cnt]["radiusrealm"][0] != "DEFAULT") {
        print "<OPTION VALUE=\"" . $iinfo[$cnt]["dn"] . "\">" .  $iinfo[$cnt]["radiusrealm"][0] . "</OPTION>\n";
      }
    }
%>
    </TD></TR>
<%
      if ($ADMIN_USER == "admin") {
%>
        <TR CLASS=list-color2>
          <TD onmouseover="myHint.show('RP1')" onmouseout="myHint.hide()" WIDTH=50%>
            New Realm
          </TD><TD WIDTH=50%>
            <INPUT TYPE=TEXT NAME=newradrealm>
      </TD></TR>
        <TR CLASS=list-color1>
<%
        } else {
%>
        <TR CLASS=list-color2>
<%
        }
%>
          <TD COLSPAN=2 ALIGN=CENTER>
	    <INPUT TYPE=HIDDEN NAME=basedn VALUE="<%print $basedn%>">
<%
          if ($ADMIN_USER == "admin") {
%>
             <INPUT TYPE=SUBMIT onclick=this.name='raddel' VALUE="<%print _("Delete");%>">
             <INPUT TYPE=SUBMIT onclick=this.name='radup' VALUE="<%print _("Update/Add");%>">
<%
          } else {
%>
             <INPUT TYPE=SUBMIT onclick=this.name='radup' VALUE="View">
<%
          }
%>
    </TD></TR></TABLE></FORM>
<%
    return;
  } else if (($radrealm == "") && ($newradrealm != "") && (isset($radup))) {
    $radrealm="radiusRealm=" . $newradrealm . "," . $basedn;

    $info["objectClass"][0]="radiusprofile";
    $info["dialupaccess"]="yes";
    $info["radiusRealm"]=$newradrealm;
    $info["radiuscheckitem"]="Realm = \"" . $newradrealm . "\"";
    $info["radiusservicetype"]="Framed-User";
    $info["radiusFramedProtocol"]="PPP";
    $info["radiusAuthType"]="Pam";
    $info["radiusframedipnetmask"]="255.255.255.255";
    $info["radiusporttype"]="Async";
    $info["radiusframedipaddress"]="255.255.255.254";
    $info["radiusframedmtu"]="1500";
    $info["radiusframedcompression"]="Van-Jacobson-TCP-IP";
    $info["radiussimultaneoususe"]="1";
    $info["radiussessiontimeout"]=86400;
    $info["radiusidletimeout"]=1800;
    ldap_add($ds,$radrealm,$info);
  }

  $sr=ldap_search($ds,$radrealm,"objectClass=radiusprofile",$iarr);
  $iinfo = ldap_get_entries($ds, $sr);
  
  if ($iinfo[0]["radiusprofiledn"][0] != "" ) {
    $sr=ldap_search($ds,$iinfo[0]["radiusprofiledn"][0],"objectClass=radiusprofile",$iarr);
    $iinfo = ldap_get_entries($ds, $sr);
  }
  $icol=0;
  for ($i=0; $i < count($iarr)-1; $i++) {
    $rem=$icol % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color1";
    }
    $attr=$iarr[$i];
    if (!$listitem[$attr]) {
%>
      <TR<%print $bcolor;%>><TD WIDTH=50% onmouseover="myHint.show('<%print $jshint[$i];%>')" onmouseout="myHint.hide()"><% print $disc[$i];%></TD><TD>
<%
    }
        if ($ADMIN_USER == "admin") {
          if ($attr == "radiusporttype") {
%>
            <SELECT NAME=<%print $iarr[$i];%>>
              <OPTION VALUE="Async" <%if ($iinfo[0][$attr][0] == "Async") {print "SELECTED";};%>>Modem
              <OPTION VALUE="ISDN" <%if ($iinfo[0][$attr][0] == "ISDN") {print "SELECTED";};%>>ISDN
              <OPTION VALUE="ISDN-V110" <%if ($iinfo[0][$attr][0] == "ISDN-V110") {print "SELECTED";};%>>ISDN V.110
              <OPTION VALUE="ISDN-V120" <%if ($iinfo[0][$attr][0] == "ISDN-V120") {print "SELECTED";};%>>ISDN V.120
              <OPTION VALUE="Virtual" <%if ($iinfo[0][$attr][0] == "Virtual") {print "SELECTED";};%>>Virtual
              <OPTION VALUE="xDSL" <%if ($iinfo[0][$attr][0] == "xDSL") {print "SELECTED";};%>>xDSL
              <OPTION VALUE="Wireless-802.11" <%if ($iinfo[0][$attr][0] == "Wireless-802.11") {print "SELECTED";};%>>Wireless-802.11
              <OPTION VALUE="" <%if ($iinfo[0][$attr][0] == "") {print "SELECTED";};%>>All

<%
          } elseif ($attr == "dialupaccess") {
            if (($iinfo[0]["radiusrealm"][0] == "DEFAULT") || (! $virtzone)) {
              print _("Alter In Users Profile") . "<INPUT TYPE=HIDDEN NAME=dialupaccess VALUE=";
              if ($iinfo[0][$attr][0] == "yes") {
                print "on> (" . _("Active") . ")";
              } else {
                print "> (" . _("Deactive") . ")";
              }
            } else {
              print "<INPUT TYPE=CHECKBOX NAME=dialupaccess";
              if ($iinfo[0][$attr][0] == "yes") {
                print " CHECKED";
              }
              print ">";
           }
          } elseif ($attr == "radiusframedcompression") {
%>
            <SELECT NAME=<%print $iarr[$i];%>>
              <OPTION VALUE="None" <%if ($iinfo[0][$attr][0] == "None") {print "SELECTED";};%>>No Compression
              <OPTION VALUE="Van-Jacobson-TCP-IP" <%if (($iinfo[0][$attr][0] == "Van-Jacobson-TCP-IP") | ($iinfo[0][$attr][0] == "")) {print "SELECTED";};%>>Van Jacobson

<%
          } elseif ($attr == "radiusrealm") {
             if ($iinfo[0]["radiusrealm"][0] == "DEFAULT") {
               print _("Default Profile");
             } else {
               print $iinfo[0][$attr][0];
             }
          } elseif ($listitem[$attr]) {
             if ($iinfo[0][$attr]['count'] > 0) {
               $icol++;
               $rem=$icol % 2;
               if ($rem == 1) {
                 $bcolor=" CLASS=list-color1";
               } else {
                 $bcolor=" CLASS=list-color2";
               }
               print "<TR " . $bcolor . "><TH COLSPAN=2 CLASS=heading-body2>" . $disc[$i] . "</TH></TR>";
               for ($licnt=0; $licnt < $iinfo[0][$attr]["count"]; $licnt++) {
                 $rem=$icol % 2;
                 if ($rem == 1) {
                   $bcolor=" CLASS=list-color2";
                 } else {
                   $bcolor=" CLASS=list-color1";
                 }
                 if (($attr == "radiusreplyitem") || (($attr == "radiuscheckitem") && ($iinfo[0][$attr][$licnt] != "Realm = \"" . $iinfo[0]["radiusrealm"][0] . "\""))) {
%>
                   <TR<%print $bcolor;%>><TD WIDTH=25%><INPUT TYPE=CHECKBOX NAME="del<%print $attr . $licnt;%>">
                     <INPUT TYPE=HIDDEN NAME=delh<%print $attr . $licnt;%> VALUE="<%print htmlentities($iinfo[0][$attr][$licnt]);%>">
                   </TD><TD><%print $iinfo[0][$attr][$licnt];%></TD></TR>
<%
                   $rem=$icol % 2;
                   if ($rem == 1) {
                     $bcolor=" CLASS=list-color1";
                   } else {
                     $bcolor=" CLASS=list-color2";
                   }
	           $icol++;
                 }
               }
	       print "<INPUT TYPE=HIDDEN NAME=" . $cntr[$attr] . " VALUE=" . $iinfo[0][$attr]["count"] . ">";
               $icol++;
             } else {
               $icol--;
             }
          } else {
%>
              <INPUT TYPE=TEXT NAME=<%print $iarr[$i];%> VALUE="<%print $iinfo[0][$attr][0];%>">
<%
          }
        } else {
          if (($ADMIN_USER != "admin") && ($listitem[$attr])) {
            print "<TR " . $bcolor . "><TD  onmouseover=\"myHint.show('" . $jshint[$i] . "')\" onmouseout=\"myHint.hide()\">" . $disc[$i] . "</TD><TD>";
            for($acnt=0;$acnt<$iinfo[0][$attr]["count"];$acnt++) {
              if (($attr == "radiusreplyitem") || (($attr == "radiuscheckitem") && ($iinfo[0][$attr][$acnt] != "Realm = \"" . $iinfo[0]["radiusrealm"][0] . "\""))) {
                print $iinfo[0][$attr][$acnt];
                if ($acnt < $iinfo[0][$attr]["count"]) {
                  print "<BR>";
                }
              }
            }
          } else {
            if ($attr == "dialupaccess") {
              if (($iinfo[0]["radiusrealm"][0] == "DEFAULT") || ($virtzone)) {
                print "Alter In Users Profile";
                if ($iinfo[0][$attr][0] == "yes") {
                  print " (Realm Active";
                } else {
                  print " (Realm Deactive";
                }
                if ($lactive === "yes") {
                  print "/User Active";
                } else if ($lactive == "no") {
                  print "/User Dective";
                }
                print ")";
              } else {
                if ($iinfo[0][$attr][0] == "yes") {
                  print "Active";
                } else {
                  print "Deactive";
                }
              }
            } else { 
              print $iinfo[0][$attr][0];
            }
          }
        }
%>
      </TD></TR>
<%
    $icol++;
  }
  $rem=$icol % 2;
  if ($rem == 1) {
    $bcol[1]=" CLASS=list-color2";
    $bcol[2]=" CLASS=list-color1";
  } else {
    $bcol[1]=" CLASS=list-color1";
    $bcol[2]=" CLASS=list-color2";
  }
  if ($ADMIN_USER == "admin") {
%>
<TR <%print $bcol[1];%>><TH COLSPAN=2 CLASS=heading-body2><%print _("Check And Reply Extensions");%></TH></TR>
<TR <%print $bcol[2];%>><TD onmouseover="myHint.show('RP12')" onmouseout="myHint.hide()"><%print _("Add Additional Reply Item");%></TD><TD><INPUT TYPE=TEXT NAME=newrep></TD></TR>  
<TR <%print $bcol[1];%>><TD onmouseover="myHint.show('RP13')" onmouseout="myHint.hide()"><print _("Add Additional Check Item");%></TD><TD><INPUT TYPE=TEXT NAME=newcheck></TD></TR>  
<TR <%print $bcol[2];%>><TH COLSPAN=2>  
  <INPUT TYPE=HIDDEN NAME=radrealm VALUE="<%print $radrealm%>">
  <INPUT TYPE=HIDDEN NAME=newcheckval>
  <INPUT TYPE=HIDDEN NAME=newrepval>
  <INPUT TYPE=HIDDEN NAME=modrec VALUE=1>
  <INPUT TYPE=BUTTON VALUE="<%print _("Modify");%>" ONCLICK=javascript:addnewradrealm()>
<%
if (($iinfo[0]["radiusrealm"][0] != "DEFAULT") && (!$virtzone)){
%>
  <INPUT TYPE=SUBMIT onclick=this.name='raddel' VALUE="<%print _("Delete");%>">
<%
}
%>
</TH></TR>
<%
  }
%>
</TABLE></FORM>
