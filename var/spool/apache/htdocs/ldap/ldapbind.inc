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

if (! $ds) {
  include_once "/var/spool/apache/htdocs/ldap/ldapcon.inc";
}

if ($ds && ldap_bind($ds,$LDAP_LIMIT_DN,$LDAP_LIMIT_PW) && ($_SERVER['PHP_AUTH_USER'] != "")) {
  $auth_ussr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=" . $_SERVER['PHP_AUTH_USER'] . "))");
  $auth_ures=ldap_first_entry($ds,$auth_ussr);
  $ldn=ldap_get_dn($ds,$auth_ures);
  ldap_bind($ds,$ldn,$_SERVER['PHP_AUTH_PW']);

  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($_SERVER['PHP_AUTH_USER'] == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }
} else {
  $ldn=$LDAP_LIMIT_DN;
  $ADMIN_USER="pleb";
}
%>
