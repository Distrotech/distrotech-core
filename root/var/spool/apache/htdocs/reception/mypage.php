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
%>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
</head>
<SCRIPT>
  function opencrm(url,type) {
    window.open("<%print$SERVER_URL;%>/crm/index.php?action=DetailView&module="+type+"&record="+url,"crmframe","menubar=no,toolbar=no,scrolling=yes,scrollbars=yes,resizable=yes,top=0,left=0,width="+screen.width+",height="+screen.height);
    window.close();
  }
</SCRIPT>
<body>
<%

$db=mysql_connect("localhost","SugarCRM","SugarCRM");
mysql_select_db("SugarCRM");

if(!isset($_GET['clid'])) {
%>
  <SCRIPT>
    alert("No caller ID provided);
    window.close();
  </SCRIPT>
<%
} else {
  $clida=explode("@",$clid);
  $clid=$clida[0];
  $curset=mysql_query("select id,first_name,last_name from contacts where phone_home='$clid' or
                              phone_mobile='$clid' or phone_work='$clid' or phone_other='$clid' or 
                              phone_fax='$clid'");

  if (mysql_num_rows($curset) == "0") {
    $curset=mysql_query("select id,name from accounts where phone_fax='$clid' or
                              phone_office='$clid' or phone_alternate='$clid'");
    $rectype="Accounts";
  } else {
    $rectype="Contacts";
  }

  if (mysql_num_rows($curset) == "1") {
    $msqldat=mysql_fetch_array($curset,MYSQL_NUM);
%>
    <SCRIPT>
      if (confirm("Open CRM Page For <%print $clid;%>")) {
        opencrm("<%print $msqldat[0];%>","<%print $rectype;%>");
      } else {
        window.close();
      }
    </SCRIPT>
<%
  } else {
    while($msqldat=mysql_fetch_array($curset,MYSQL_NUM)) {
      print "<a href=javascript:opencrm(\"" . $msqldat[0] . "\",\"" . $rectype . "\")>" . $msqldat[1];
      if ($msqldat[2] != "") {
        print " " . $msqldat[2];
      }
      print "</A><BR>";
    }
%>
    <SCRIPT>
      if (confirm("Open CRM Accounts Page")) {
        window.open("<%print$SERVER_URL;%>/crm/index.php?module=Accounts&action=index","crmframe","menubar=no,toolbar=no,scrolling=yes,scrollbars=yes,resizable=yes,top=0,left=0,width="+screen.width+",height="+screen.height);
      }
      window.close();
    </SCRIPT>
<%
  }
}
%>
</body>
</html>
