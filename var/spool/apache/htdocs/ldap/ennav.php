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
/*
  if ($ds) {
    $sr=ldap_search($ds,"cn=Addressbooks","objectClass=groupofnames");
    $sinfo = ldap_get_entries($ds, $sr);
    for($mcnt=0;$mcnt<=$sinfo[0]["member"]["count"] -1;$mcnt++) {
      $dns=$sinfo[0]["member"][$mcnt];
      $dns=split("=",$dns);
      $abookdn=$dns[1];
      if ($abookdn != "admin" ) {
        $dnact[$abookdn]=true;
        print "<OPTION VALUE=" . $abookdn . ">" . $abookdn . "</OPTION>\n";
      }
    }
  }
}*/

$usert['system']=_("System");
$usert['pdc']=_("PDC");
$usert['trust']=_("Trust Account");
$usert['server']=_("Server Account");
$usert['mserver']=_("Mail Server Account");
$usert['snom']=_("Snom Phonebook Entry");

if (($_SESSION['utype'] != "system") && ($_SESSION['utype'] != "pdc") && ($_SESSION['utype'] != "trust") && ($_SESSION['utype'] != "snom") && ($_SESSION['utype'] != "mserver")) {
  $_SESSION['classi']="system";
}

%>

<CENTER>
<FORM METHOD=POST NAME=ldapuform onsubmit="ajaxsubmit(this.name);return false">
<table border="0" width="90%" cellspacing="0" cellpadding="0">
  <INPUT TYPE=HIDDEN NAME=disppage VALUE="ldap/ldap.php">
  <INPUT TYPE=HIDDEN NAME=ldtype VALUE="abook">
  <INPUT TYPE=HIDDEN NAME=baseou VALUE="<%print $_SESSION['classi'];%>">

  <tr CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body><%print _("Search For") . " " . $usert[$_SESSION['classi']];%> User</TH></TR>
  <TR CLASS=list-color1><TD WIDTH=50% onmouseover="myHint.show('0')" onmouseout=myHint.hide()><%print _("Search For") . " " . $usert[$baseou] . " " . _("Where");%>	...</TD><TD>
        <SELECT NAME=what><%
          if ($_SESSION['classi'] == "mserver") {%>
            <OPTION VALUE=cn><%print _("Host Name");%></OPTION><%
          } else if ($_SESSION['classi'] == "trust") {%>
            <OPTION VALUE=cn><%print _("IP Address");%></OPTION><%
          } else {%>
            <OPTION VALUE=cn><%print _("Common Name");%></OPTION><%
          }
          if (($_SESSION['classi'] == "pdc") || ($_SESSION['classi'] == "system")) {%>
            <OPTION VALUE=uid<%if ($what == "uid") {print " SELECTED";}%>><%print _("Username");%></OPTION>
            <OPTION VALUE=givenname<%if ($what == "givenname") {print " SELECTED";}%>><%print _("First Name");%></OPTION>
            <OPTION VALUE=sn<%if ($what == "sn") {print " SELECTED";}%>><%print _("Last Name");%></OPTION>
            <OPTION VALUE=mail<%if ($what == "mail") {print " SELECTED";}%>><%print _("Email Address");%></OPTION>
            <OPTION VALUE=maillocaladdress<%if ($what == "maillocaladdress") {print " SELECTED";}%>><%print _("Email Alias")%></OPTION>
            <OPTION VALUE=o<%if ($what == "o") {print " SELECTED";}%>><%print _("Company");%></OPTION>
            <OPTION VALUE=ou<%if ($what == "ou") {print " SELECTED";}%>><%print _("Division");%></OPTION>
            <OPTION VALUE=st<%if ($what == "st") {print " SELECTED";}%>><%print _("State")%></OPTION>
            <OPTION VALUE=l<%if ($what == "l") {print " SELECTED";}%>><%print _("City");%></OPTION>
            <OPTION VALUE=postalcode<%if ($what == "postalcode") {print " SELECTED";}%>><%print _("Postal Code");%></OPTION>
            <OPTION VALUE=telephonenumber<%if ($what == "telephonenumber") {print " SELECTED";}%>><%print _("Work Phone Number");%></OPTION>
            <OPTION VALUE=homephone<%if ($what == "homephone") {print " SELECTED";}%>><%print _("Home Phone Number");%></OPTION>
            <OPTION VALUE=mobile<%if ($what == "mobile") {print " SELECTED";}%>><%print _("Cell Phone");%></OPTION>
            <OPTION VALUE=hostedfpsite<%if ($what == "hostedfpsite") {print " SELECTED";}%>><%print _("Front Page Site");%></OPTION>
            <OPTION VALUE=hostedsite<%if ($what == "hostedsite") {print " SELECTED";}%>><%print _("Web Site");%></OPTION>
          } else if ($_SESSION['classi'] == "snom") {%>
            <OPTION VALUE=telephonenumber<%if ($what == "telephonenumber") {print " SELECTED";}%>><%print _("Phone Number");%></OPTION><%
          } else if ($_SESSION['classi'] == "trust") {%>
            <OPTION VALUE=uid<%if ($what == "uid") {print " SELECTED";}%>><%print _("Host Name");%></OPTION><%
          } else if ($_SESSION['classi'] == "mserver") {%>
            <OPTION VALUE=uid<%if ($what == "uid") {print " SELECTED";}%>><%print _("Login Name");%></OPTION>
            <OPTION VALUE=l<%if ($what == "l") {print " SELECTED";}%>><%print _("Location");%></OPTION>
            <OPTION VALUE=iphostnumber<%if ($what == "iphostnumber") {print " SELECTED";}%>><%print _("IP Address");p%></OPTION>
            <OPTION VALUE=description<%if ($what == "description") {print " SELECTED";}%>><%print _("Description");%></OPTION><%
          }
          if ($_SESSION['classi'] == "system") {%>
            <OPTION VALUE=accountSuspended<%if ($what == "accountSuspended") {print " SELECTED";}%>><%print _("All Suspended Accounts");%></OPTION><%
          }%>
        </SELECT></TD></TR>
  <TR CLASS=list-color2><TD onmouseover="myHint.show('1')" onmouseout=myHint.hide()>... </TD><TD>
        <SELECT NAME=type>
          <OPTION VALUE=in<%if ($type == "in") {print " SELECTED";}%>><%print _("Contains");%></OPTION>
          <OPTION VALUE=start<%if ($type == "start") {print " SELECTED";}%>><%print _("Begins With");%></OPTION>
          <OPTION VALUE=end<%if ($type == "end") {print " SELECTED";}%>><%print _("Ends With");%></OPTION>
          <OPTION VALUE="eq"<%if ($type == "eq") {print " SELECTED";}%>><%print _("Equals");%></OPTION>
        </SELECT></TD></TR>
  <TR CLASS=list-color1><TD onmouseover="myHint.show('2')" onmouseout=myHint.hide()>... </TD><TD>
        <INPUT TYPE=TEXT NAME=search VALUE="<%print $search;%>" autocomplete="off" SIZE=50></TD></TR>
  <TR CLASS=list-color2><TD COLSPAN=2 ALIGN=CENTER>
        <INPUT TYPE=SUBMIT onclick="this.name='find'" VALUE="<%print _("Search");%>">
        <INPUT TYPE=SUBMIT onclick=this.name='add' VALUE="<%print _("Add");%>">
    </td>
  </tr>
</table>
</FORM>
<script>
var ldappop=new TextComplete(document.ldapuform.search,ldapautodata,'/auth/uidxml.php',setldappopurl,document.ldapuform,ldappop);
</script>
