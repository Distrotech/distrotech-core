<?php
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

$colspan=4;
?>
<META HTTP-EQUIV="Refresh" CONTENT="5; URL=<?php print "/ticket/scp/tickets.php?id=" . $ticket;?>">


<CENTER>
<CENTER><TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<?php
$col=1;
print "<TR CLASS=list-color" . (($col % 2) +1) . "><TH CLASS=heading-body COLSPAN=" . ($colspan) . ">";
print "Hi There Im Ticket No " . $ticket;
print "</TH></TR>\n";
$col++;
print "<TR CLASS=list-color" . (($col % 2) +1) . "><TH CLASS=heading-body COLSPAN=" . ($colspan) . "><A HREF=/ticket/scp/tickets.php?id=" . $ticket . ">";
print "Im Not Ready For You Yet Taking You To Old System ...</A></TH></TR>";
?>
</TABLE>
