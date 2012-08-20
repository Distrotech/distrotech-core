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
  include_once "/var/spool/apache/htdocs/ldap/ldap.inc";
  include_once "/var/spool/apache/htdocs/ldap/ldapbind.inc";

  if (ereg("ou=Idmap",$ldn)) {
    $ainfo["userPassword"]="{CRYPT}" . crypt($_SERVER['PHP_AUTH_PW']);
    ldap_mod_replace($ds,$ldn,$ainfo);
  }

  if ($getuid) {
    $uidarr=array("uidnumber");
    $useduid=array();

    $uidsr=ldap_search($ds,"","objectClass=posixAccount",$uidarr);
    $uinfo = ldap_get_entries($ds, $uidsr);

    for ($i=0; $i<$uinfo["count"]; $i++) {
      $uidnum=$uinfo[$i]["uidnumber"][0];
      if ($uidnum >= 500) {
        $useduid[$uidnum]=$uidnum;
      }
    }

    $ucnt=500;
    while($useduid[$ucnt] != "") {
      $ucnt++;
    }

    $sr=ldap_search($ds,"","(&(objectClass=posixAccount)(uidnumber=$ucnt))",$uidarr);
    if (! ldap_count_entries($ds,$sr)) {
      $newuidnumber=$ucnt;
    }
  }

  if (isset($_SERVER['PHP_AUTH_PW'])) {
//  @ldap_unbind($ds);
    $r=@ldap_bind($ds,$ldn,$_SERVER['PHP_AUTH_PW']);
  }
/*
  if ((!isset($PHP_AUTH_USER)) || (! $r) || (!isset($PHP_AUTH_PW))) {
    authenticate();
  }*/
%>
