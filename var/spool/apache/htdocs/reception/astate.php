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

include "auth.inc";
%>
<link rel="stylesheet" href="/style.php?style=<%print $style;%>">
<meta http-equiv="refresh" content="15">
<CENTER>
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH COLSPAN=4 CLASS=heading-body><%print _("User Available State")%></TH>
</TR><TR CLASS=list-color1>
<%
$ustateq="SELECT '    <TD CLASS=option-'||CASE WHEN (cdnd = '1') THEN 'red' ELSE 'green' END ||'>'||name||' ('||fullname||')</TD>\n' from 
users 
  LEFT OUTER JOIN astdb AS lpre ON (substr(name,1,2) = lpre.key AND lpre.family='LocalPrefix')  
  LEFT OUTER JOIN features ON (exten=users.name)  
  WHERE lpre.value='1' AND 
    (dgroup = '" . $msqldat[5] . "' OR dgroup IS NULL) order by name";
$ustates=pg_query($db,$ustateq);
//print $ustateq . "<P>";
$rcnt=1;
for($qcnt=0;$qcnt<pg_num_rows($ustates);$qcnt++) {
  $ustate=pg_fetch_array($ustates);
  print $ustate[0];
  if ((($qcnt % 4) == 3) && ($qcnt != pg_num_rows($ustates)-1)) {
    print "</TR>\n<TR CLASS=list-color" . (($rcnt %2) + 1) . ">\n";
    $rcnt++;
  }
}
if (($qcnt % 4) > 0) {
  for($blcnt=$qcnt%4;$blcnt < 4;$blcnt++) {
    print "    <TD>&nbsp;</TD>\n";
  }
}
%>
</TR>
</TABLE>
