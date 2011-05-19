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
  $font=1;

  include "opendb.inc";
  include "../ldap/auth.inc";

/*
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=$ldn)(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) != 1) && ($PHP_AUTH_USER != "admin")) {
    $username="$PHP_AUTH_USER";
  }
*/
  function ghours($seconds) {
    $hours=($seconds - ($seconds % 3600))/3600;
    $minuts=(($seconds % 3600) - (($seconds % 3600) %60))/60;
    if ($minuts < 10)
      {
        $minuts="0$minuts";
      }
    $secs=($seconds % 3600) % 60;
    if ($secs < 10)
      {
        $secs="0$secs";
      }
    return "$hours:$minuts:$secs";
  }

function gbytes($bytes)
  {
    if ($bytes >= 1073741824)
      {
        $bout=$bytes/1073741824;
        $bout=round($bout,2);
        $bout="$bout GB";
      }
     elseif ($bytes >= 1048576)
      {
        $bout=$bytes/1048576;
        $bout=round($bout,2);
        $bout="$bout MB";
      }
     elseif ($bytes >= 1024)
      {
        $bout=$bytes/1024;
        $bout=round($bout,2);
        $bout="$bout KB";
      }
     else
      {
        $bout="$bytes B";
      }
 
    return $bout;
  }


  $conns=array();

  $querys="SELECT date_part('day',AcctStopTime) AS DOM,
                             COUNT(AcctStopTime),
                             SUM(AcctSessionTime),
                             AVG(AcctSessionTime)
                             FROM radacct 
                             WHERE UserName='$username' AND 
                                   date_part('month',AcctStopTime) = $month AND
                                   date_part('Year',AcctStopTime) = $year
                             GROUP BY DOM 
                             ORDER BY DOM";

//  print "<PRE>" . $querys . "</PRE>";
  $query=pg_query($querys);
  while(list($dom,$conn,$time,$avtime)=pg_fetch_row($query))
    {
      $conns[$dom]=$conn;
      if ($conn > $conmax)
        {
          $conmax=$conn;
        }
      $connt[$dom]=$time;
      if ($time > $contmax)
        {
          $contmax=$time;
        }
      $connta[$dom]=$avtime;
      if ($avtime > $contamax)
        {
          $contamax=$avtime;
        }
    }


  $ytop=0;
  $xtop=0;
  $title="Connections Per Day";
  $max=$conmax;


  header ("Content-type: image/png");
  $gra =ImageCreate(720,900);
  $background_color = ImageColorAllocate ($gra,255,255,255);
  $text_color = ImageColorAllocate ($gra,0,0,0);
  $plot_color = ImageColorAllocate ($gra,0,0,255);
  $plot_border = ImageColorAllocate ($gra,0,255,0);

  for($cnt=1;$cnt < 31;$cnt++)
    {
      $offset=(($max-$conns[$cnt])/$max)*200;
      imagefilledrectangle($gra,$xtop+50+($cnt-1)*20,$ytop+50+$offset,$xtop+50+$cnt*20,$ytop+250,$plot_color);
    }



  $txstart=((620-(imagefontwidth($font)*strlen($title)))/2)+$xtop+50;
  $tystart=(50-imagefontheight($font))/2;
  ImageString($gra,$font,$txstart,$ytop+$tystart,$title, $text_color);
  imagerectangle($gra,$xtop+50,$ytop+50,$xtop+670,$ytop+250,$text_color);
  $dist=$max/20;
  imagestring($gra,$font,$xtop+imagefontwidth($font),$ytop+50,$max,$text_color);
  for($cnt=10;$cnt < 200;$cnt=$cnt+10)
    {
      imageline($gra,$xtop+50,$ytop+50+$cnt,$xtop+670,$ytop+$cnt+50,$text_color);
      imagestring($gra,$font,$xtop+imagefontwidth($font),$ytop+50+$cnt,round($max-($dist*($cnt/10))),$text_color);
    }
  for($cnt=20;$cnt < 620;$cnt=$cnt+20)
    {
      imageline($gra,$xtop+50+$cnt,$ytop+50,$xtop+50+$cnt,$ytop+250,$text_color);
      imagestring($gra,$font,$xtop+50+$cnt-20,$ytop+250+10,$cnt/20,$text_color);
    }
  imagestring($gra,$font,$xtop+50+600,$ytop+260,"31",$text_color);


  $ytop=300;
  $xtop=0;
  $title="Time Online Per Day";
  $max=$contmax;

  for($cnt=1;$cnt < 31;$cnt++)
    {
      $offset=(($max-$connt[$cnt])/$max)*200;
      imagefilledrectangle($gra,$xtop+50+($cnt-1)*20,$ytop+50+$offset,$xtop+50+$cnt*20,$ytop+250,$plot_color);
    }


  $txstart=((620-(imagefontwidth($font)*strlen($title)))/2)+$xtop+50;
  $tystart=(50-imagefontheight($font))/2;
  ImageString($gra,$font,$txstart,$ytop+$tystart,$title, $text_color);
  imagerectangle($gra,$xtop+50,$ytop+50,$xtop+670,$ytop+250,$text_color);
  $dist=$max/20;

  imagestring($gra,$font,$xtop+imagefontwidth($font),$ytop+50,ghours($max),$text_color);
  for($cnt=10;$cnt < 200;$cnt=$cnt+10)
    {
      imageline($gra,$xtop+50,$ytop+50+$cnt,$xtop+670,$ytop+$cnt+50,$text_color);
      imagestring($gra,$font,$xtop+imagefontwidth($font),$ytop+50+$cnt,ghours(round($max-($dist*($cnt/10)))),$text_color);
    }

  for($cnt=20;$cnt < 620;$cnt=$cnt+20)
    {
      imageline($gra,$xtop+50+$cnt,$ytop+50,$xtop+50+$cnt,$ytop+250,$text_color);
      imagestring($gra,$font,$xtop+50+$cnt-20,$ytop+250+10,$cnt/20,$text_color);
    }
  imagestring($gra,$font,$xtop+50+600,$ytop+260,"31",$text_color);




  $ytop=600;
  $xtop=0;
  $title="Time Per Connection Per Day";
  $max=$contamax;

  for($cnt=1;$cnt < 31;$cnt++)
    {
      $offset=(($max-$connta[$cnt])/$max)*200;
      imagefilledrectangle($gra,$xtop+50+($cnt-1)*20,$ytop+50+$offset,$xtop+50+$cnt*20,$ytop+250,$plot_color);
    }


  $txstart=((620-(imagefontwidth($font)*strlen($title)))/2)+$xtop+50;
  $tystart=(50-imagefontheight($font))/2;
  ImageString($gra,$font,$txstart,$ytop+$tystart,$title, $text_color);
  imagerectangle($gra,$xtop+50,$ytop+50,$xtop+670,$ytop+250,$text_color);
  $dist=$max/20;

  imagestring($gra,$font,$xtop+imagefontwidth($font),$ytop+50,ghours(round($max)),$text_color);
  for($cnt=10;$cnt < 200;$cnt=$cnt+10)
    {
      imageline($gra,$xtop+50,$ytop+50+$cnt,$xtop+670,$ytop+$cnt+50,$text_color);
      imagestring($gra,$font,$xtop+imagefontwidth($font),$ytop+50+$cnt,ghours(round($max-($dist*($cnt/10)))),$text_color);
    }

  for($cnt=20;$cnt < 620;$cnt=$cnt+20)
    {
      imageline($gra,$xtop+50+$cnt,$ytop+50,$xtop+50+$cnt,$ytop+250,$text_color);
      imagestring($gra,$font,$xtop+50+$cnt-20,$ytop+250+10,$cnt/20,$text_color);
    }
  imagestring($gra,$font,$xtop+50+600,$ytop+260,"31",$text_color);

  ImagePng($gra);
%>
