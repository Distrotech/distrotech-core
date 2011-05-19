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
include_once "auth.inc";

if (isset($openphone)) {
%>
<SCRIPT>
</SCRIPT>
<%
}

$qgetdataq.="SELECT 'http://'||name||':'||secret||'@'||ipaddr,fullname||' ('||name||')' from users ";
if ($SUPER_USER != 1) {
  $qgetdataq.=" LEFT OUTER JOIN astdb AS bgrp ON (bgrp.family=name AND bgrp.key='BGRP')";
}
$qgetdataq.=" where ipaddr != '' and extract(EPOCH from NOW())-cast(regseconds as double precision) < 86400";
if ($SUPER_USER != 1) {
  $qgetdataq.=" AND " . $clogacl;
}
$qgetdataq.=" ORDER BY fullname";

$qgetdata=pg_query($db,$qgetdataq);

%>

<CENTER>
<FORM METHOD=POST>
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH CLASS=heading-body COLSPAN=2><%print _("Open HTML Phone");%></TH>
</TR>
<TR CLASS=list-color1>
<TD WIDTH=50% onmouseover="myHint.show('WP0')" onmouseout="myHint.hide()"><%print _("Select Phone To Open");%></TD>
<TD><SELECT NAME=htmlphone onchange="if (this.value != '') {openphone(this.value);this.value='';}">
<OPTION VALUE=''></OPTION>
<%
$dnum=pg_num_rows($qgetdata);
for($i=0;$i<$dnum;$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  $iszap[$getdata[0]]=1;
  print "<OPTION VALUE=" . $getdata[0] . ">" . $getdata[1] . "</OPTION>"; 
}
%>
</SELECT>
</TR>
</TABLE>
</FORM>
