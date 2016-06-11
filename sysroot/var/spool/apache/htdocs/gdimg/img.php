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
  Header ("Content-type: image/png");

  $path="/var/spool/apache/htdocs/gdimg/";
  $font=$path . "butfont.ttf";
  $size=10;
  if (file_exists("/var/spool/apache/htdocs/images/" . $SERVER_NAME . "/but.png")) {
    $imgfile="/var/spool/apache/htdocs/images/" . $SERVER_NAME . "/but.png";
  } else {
    $imgfile=$path . "but.png";
  }
  $imout=imagecreatefrompng($imgfile);
  $black=ImageColorAllocate($imout,0,0,0);
  $bgcol=imagecolorat($imout,0,0);
  imagecolortransparent($imout,$bgcol);
  $x=imagesx($imout);
  $y=imagesy($imout);

  $tdata=imagettfbbox($size,0,$font,$text);
  ImageTTFText ($imout,$size, 0,($x-$tdata[4])/2,($y-$size)/2+$size,$black,$font,$text);

  ImagePng($imout);
  ImageDestroy($imout);
?>
