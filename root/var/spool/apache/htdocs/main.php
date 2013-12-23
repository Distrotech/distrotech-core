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
  include "/var/spool/apache/htdocs/ldap/ldapcon.inc";
%>
<CENTER>
<TABLE WIDTH=90%>
<TR><TD WIDTH=40%>
<P><BLOCKQUOTE><STRONG>System License</STRONG><BR>
<PRE>
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.If not, see <A HREF=http://www.gnu.org/licenses target=_blank>&lt;http://www.gnu.org/licenses/&gt;</A></PRE></TD></TR>
<%
  if (@ldap_bind($ds,"uid=admin,ou=users","admin")) {
%>
    <TR><TD>
    <P><BLOCKQUOTE><STRONG>ATTENTION !!!</STRONG><BR><PRE>
    This server is insecure as the default password admin for user admin has not ben changed
    The password should be changed only once the system has been configured with server 
    accounts and at least one user is allocated to the admin access group.
    Failure to do so could cause the system to stop functioning.This should only be for 3 
    minutes if after 3 minutes you cant access the management console recovery procedures 
    need to be followed.
    <A HREF=javascript:openpage('auth/passwd.php','inet')>Click Here To Change</A>
    </BLOCKQUOTE></PRE><P></TD></TR>
<%
  }
%>
</TABLE>
</CENTER>

