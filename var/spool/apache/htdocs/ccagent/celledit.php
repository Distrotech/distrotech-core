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

include "/var/spool/apache/htdocs/reception/auth.inc";

include_once "/var/spool/apache/htdocs/session.inc";
$sessid=session_id();
if ($sessid == "") {
  ob_start('ob_gzhandler');
  session_name("agent_" . $_SERVER['PHP_AUTH_USER']);
  session_set_cookie_params(28800);
  session_start();
}

$upquery="UPDATE inputdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid'] . " SET " . $_POST['cellname'] . "='" . $_POST['newvalue'] . 	"' FROM contact where contact.id='" . $_SESSION['lastcon'] . "' and leadid=contact.lead";
pg_query($db,$upquery);

$getval=pg_query($db,"SELECT " . $_POST['cellname'] . " FROM inputdata_" . $_SESSION['campid'] . "_" . $_SESSION['listid'] . " LEFT OUTER JOIN contact ON (leadid=contact.lead) WHERE contact.id='" . $_SESSION['lastcon'] . "'");
list($outval)=pg_fetch_array($getval,0,PGSQL_NUM);
print $outval;
%>
