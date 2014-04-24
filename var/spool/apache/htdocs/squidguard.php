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
<HTML><HEAD>
<TITLE>ERROR: Access To The requested URL Was Denied</TITLE>
</HEAD><BODY>
<H2>Access To The requested URL Was Denied</H2>
<HR>
<P>
While trying to retrieve the URL:
<A HREF="<%print $url;%>"><%print $url;%></A>
<P>
The following error was encountered:
<BLOCKQUOTE>
Access Blocked By Group <%print $clientgroup;%> Rule <%print $destinationgroup;%> From User <%print $clientident;%> Address <%print "$clientaddr ($clientname)<BR>";%>
To URL <I><%print $url;%></I>
</BLOCKQUOTE>

<br clear="all">
<hr noshade size=1>
<%
$datetime=date("D, d M Y T");
$address = gethostbyaddr("$REMOTE_ADDR");
print "Generated $datetime by $address<BR>";
%>
</BODY></HTML>
