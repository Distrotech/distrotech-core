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
  include "ulogauth.php";
?>
<link rel=stylesheet type=text/css href=/style.php>
<?php  
  $mintime="$time_year-$time_month-$time_day $time_hour:$time_min:$time_sec";
  $query="DELETE FROM mail_from WHERE time < \"$mintime\"";
  $dresult=mysql_query($query);
  $pcnt=mysql_affected_rows();
  $query="DELETE FROM mail_to WHERE time < \"$mintime\"";
  $dresult=mysql_query($query);
  $pcnt=$pcnt+mysql_affected_rows();
  print "Deleted $pcnt Entries";
?>
