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
  $htaccess=file("/var/spool/apache/htdocs/ns/config/netsentry.conf");
  while(list($lnum,$ldata)=each($htaccess)) {
    if (preg_match("/^Serial/",$ldata)) {
      $mustchange=true;
      $pwlnum=$lnum;
    }
    $newdata[$lnum]=$ldata;
  }
  if ($mustchange) {
    $newdata[$pwlnum]="Serial $snum\r\n";
  } else {
    array_push($newdata,"Serial $snum\r\n");
  }
  $fname="/var/spool/apache/htdocs/ns/config/netsentry.conf";
  $cfile=fopen($fname,w);
  chmod($fname,0660);
  $datain=implode($newdata);
  fwrite($cfile,$datain);
  fclose($cfile);
?>
