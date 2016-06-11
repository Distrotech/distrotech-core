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
$pg_handle=pg_connect("host=localhost dbname=asterisk user=asterisk password=zatelepass");

function odbcquery($sqlquery) {
  global $pg_handle;

  $odbcexec=pg_query($pg_handle,$sqlquery);
  if (pg_num_rows($odbcexec) > 0) {
    return pg_fetch_row($odbcexec,0);
  } else {
    return -1;
  }
}

    $minutes = intval($timeout / 60);
    $seconds = $timeout % 60;
    $agi->stream_file("prepaid/prepaid-you-have");
    if ($minutes>0){
      $agi->say_number($minutes);
      if ($minutes==1){
        $agi->stream_file("prepaid/prepaid-minute");
      }else{
        $agi->stream_file("prepaid/prepaid-minutes");
      }
    }
    if ($seconds>0){
      if ($minutes>0) {
        $agi->stream_file("prepaid/prepaid-and");
      }
      $agi->say_number($seconds);
      if ($seconds==1){
        $agi->stream_file("prepaid/prepaid-second");
      }else{
        $agi->stream_file("prepaid/prepaid-seconds");
      }
    }
  }
?>
