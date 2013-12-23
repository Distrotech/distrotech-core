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

include "/var/lib/asterisk/agi-bin/functions.inc";

if ($account == "") {
  list($userac)=odbcquery("SELECT username FROM callerid WHERE cid='" . $agi->request['agi_callerid'] . "'");
} else {
  $userac=$account;
}


if ($userac != "") {
//  $pid=pcntl_fork();
//  if ($pid == 0) {

    if ($delay != "") {
      sleep($delay);
    } else {
      sleep(5);
    }
    $socket = fsockopen("127.0.0.1","5038", $errno, $errstr, $timeout);
    fputs($socket,"Action: Login\r\n"); 
    fputs($socket,"UserName: admin\r\n"); 
    fputs($socket,"Secret: admin\r\n"); 
    fputs($socket,"Events: off\r\n"); 
    fputs($socket,"\r\n");

    $wrets=rtrim(fgets($socket,8192));
    verbose($wrets . "\n");
    while($wrets != "") {
      $wrets=rtrim(fgets($socket,8192));
      verbose($wrets . "\n");
    }

    fputs($socket,"Action: Originate\r\n");
    if ($callback != "") {
      fputs($socket,"Channel: Local/" . $callback . "@callback/n\r\n");
    } else {
      fputs($socket,"Channel: Local/" . $agi->request['agi_callerid'] . "@callback/n\r\n");
    }
    fputs($socket,"Callerid: " . $userac . "\r\n");

    if ($context == "") {
      fputs($socket,"Context: disa\r\n");
    } else {
      fputs($socket,"Context: " . $context  . "\r\n");    
    }
    fputs($socket,"Exten: s\r\n");
    fputs($socket,"Priority: 1\r\n");
    fputs($socket,"Variable: CBACK=" . $callback . "|UUID=" . uniqid() . "\r\n");
    fputs($socket,"\r\n");

    $wrets=rtrim(fgets($socket,8192));
    verbose($wrets . "\n");
    while($wrets != "") {
      $wrets=rtrim(fgets($socket,8192));
      verbose($wrets . "\n");
    }

    fputs($socket,"Action: Logoff\r\n"); 
    fputs($socket,"\r\n");

    $wrets=rtrim(fgets($socket,8192));
    verbose($wrets . "\n");
    while($wrets != "") {
      $wrets=rtrim(fgets($socket,8192));
      verbose($wrets . "\n");
    }

    fclose($socket);
//  }
}
?>
