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
if ($ds) {
  $auth_uss=ldap_bind($ds,$LDAP_ROOT_DN,$LDAP_ROOT_PW);
  if ($PHP_AUTH_USER != "admin") {
    $auth_ussr=ldap_search($ds,"","(&(objectClass=officePerson)(uid=$PHP_AUTH_USER))");
    $auth_ures=ldap_first_entry($ds,$auth_ussr);
    $ldn=ldap_get_dn($ds,$auth_ures);
    $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
    $vadmin=ldap_search($ds,"ou=Vadmin","(&(objectclass=virtZoneSettings)(member=" . $ldn . "))",array("dn","cn"));
    $vdoms=ldap_get_entries($ds,$vadmin);
    ldap_bind($ds,$ldn,$PHP_AUTH_PW);
    $voipa=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Voip Admin))");
    $voipt=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=TMS Access))");
    $voipl=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Call Logging))");

    if (ldap_count_entries($ds,$sr) == 1) {
      $authtype=1;
    } else if ($ldn) {
      $authtype=0;
    } else {
      $authtype=-1;
    }
    if (ldap_count_entries($ds,$voipa) == 1) {
      $voipauth=1;
    } else {
      $voipauth=-1;
    }
    if (ldap_count_entries($ds,$voipt) == 1) {
      $tmsauth=1;
    } else {
      $tmsauth=-1;
    }
    if (ldap_count_entries($ds,$voipl) == 1) {
      $clogauth=1;
    } else {
      $clogauth=-1;
    }
  } else {
    ldap_bind($ds,$LDAP_ROOT_DN,$LDAP_ROOT_PW);
    $vadmin=ldap_search($ds,"ou=Vadmin","objectclass=virtZoneSettings",array("dn","cn"));
    $vdoms=ldap_get_entries($ds,$vadmin);
    $authtype=2;
    $ldn=$LDAP_ROOT_DN;
  }
}
%>
