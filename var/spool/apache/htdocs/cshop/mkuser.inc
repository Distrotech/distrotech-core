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
function cardnum() {
  $chrs="8";
  $pwd="";
  $getnext=pg_query("SELECT nextval('user_telno')");
  $r=pg_fetch_array($getnext,0);
  $uexists=pg_query("SELECT name FROM users WHERE name='" . $r[0] . "'");
  if (pg_num_rows($uexists)) {
    $getnext=pg_query("SELECT nextval('user_telno')");
    $r=pg_fetch_array($getnext,0);
  }
  for($i=strlen($r[0]);$i < 8;$i++) {
    $r[0]="0" . $r[0];
  }
  return $r[0];
};

function cardpin() {
  $chrs="4";
  $pwd="";
  mt_srand ((double) microtime() * 1000000);
  while (strlen($pwd)<$chrs) {
    $chr = chr(mt_rand (0,255));
    if (eregi("^[0-9]$", $chr))
      $pwd = $pwd.$chr;
  };
 return $pwd;
};

function randpwgen($len) {
  do {
    $newcode="";
    for($cnt=0;$cnt < $len;$cnt++) {
      $newcode .= chr(!rand(0, 2) ? rand(48, 57) : (!rand(0, 1) ? rand(65, 90) : rand(97, 122)));
    }
  } while (!ereg("[a-z]",$newcode) || !ereg("[A-Z]",$newcode) || !ereg("[0-9]",$newcode));
  return $newcode;
};
%>
