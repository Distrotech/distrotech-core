#!/usr/bin/php -q
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

$odbc_handle=odbc_connect("Asterisk","asterisk","zatelepass");
if ($odbc_handle === 0) {
  exit();
}

function verbose($outmsg) {
  global $agi;
  $agi->verbose($outmsg,3);
}

function odbcquery($sqlquery) {
  global $odbc_handle;

  $odbcexec=odbc_exec($odbc_handle,$sqlquery);
  if ($odbcexec === 0) {
    return -1;
  }
  $odbc_data=odbc_fetch_into($odbcexec,$odbc_array);
  if ($odbc_data === 0) {
    return -1;
  } else {
    return $odbc_array;
  }
}

$rateq="SELECT username FROM callerid WHERE cid='" . $agi->request['agi_callerid'] . "'";
$ratequery=odbcquery($rateq);

$agi->set_variable("ACNUM",$ratequery[0]);
verbose("OK!!!!!!!");
?>
