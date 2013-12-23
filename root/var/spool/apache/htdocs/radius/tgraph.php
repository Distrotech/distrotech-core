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
  header ("Content-type: image/png");
  $gra =ImageCreate(720,600);
  $background_color = ImageColorAllocate ($gra,255,255,255);
  $text_color = ImageColorAllocate ($gra,0,0,0);
  $plot_color = ImageColorAllocate ($gra,0,0,255);
  $plotin_color = ImageColorAllocate ($gra,0,255,0);
  $plotout_color = ImageColorAllocate ($gra,255,0,0);
  include "opendb.inc";
  include "../ldap/auth.inc";

/*
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=$ldn)(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) != 1) && ($PHP_AUTH_USER != "admin")) {
    $username="$PHP_AUTH_USER";
  }
*/

function ghours($seconds)
  {
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


#  $conns=array();

  $tquery="SELECT date_part('day',AcctStopTime) AS DOM,
                             SUM(AcctInputOctets),
                             AVG(AcctInputOctets),
                             SUM(AcctOutputOctets),
                             AVG(AcctOutputOctets)
                             FROM radacct 
                             WHERE UserName='$username' AND 
                                   date_part('month',AcctStopTime) = $month AND
                                   date_part('Year',AcctStopTime) = $year
                             GROUP BY DOM 
                             ORDER BY DOM";

  $query=pg_query($tquery);

  while(list($dom,$bytein,$byteinav,$byteout,$byteoav)=pg_fetch_row($query))
    {
      $connbi[$dom]=$bytein;
      if ($bytein > $conbimax)
        {
          $conbimax=$bytein;
        }

      $connbo[$dom]=$byteout;
      if ($byteout > $conbomax)
        {
          $conbomax=$byteout;
        }

      $connbia[$dom]=$byteinav;
      if ($byteinav > $conbiamax)
        {
          $conbiamax=$byteinav;
        }

      $connboa[$dom]=$byteoav;
      if ($byteoav > $conboamax)
        {
          $conboamax=$byteoav;
        }

    }


  $ytop=0;
  $xtop=0;
  $title="Bytes In/Out Per Day";
  $maxi=$conbimax;
  $maxo=$conbomax;
  $disti=$maxi/20;
  $disto=$maxo/20;

  for($cnt=1;$cnt < 31;$cnt++)
    {
      $ioffset=(($maxi-$connbi[$cnt])/$maxi)*200;
      $ooffset=(($maxo-$connbo[$cnt])/$maxo)*200;
      imagefilledrectangle($gra,$xtop+50+($cnt-1)*20,$ytop+50+$ioffset,$xtop+50+$cnt*20-10,$ytop+250,$plotin_color);
      imagefilledrectangle($gra,$xtop+50+($cnt-1)*20+10,$ytop+50+$ooffset,$xtop+50+$cnt*20,$ytop+250,$plotout_color);
    }


  $txstart=((620-(imagefontwidth($font)*strlen($title)))/2)+$xtop+50;
  $tystart=(50-imagefontheight($font))/2;
  ImageString($gra,$font,$txstart,$ytop+$tystart,$title, $text_color);
  imagerectangle($gra,$xtop+50,$ytop+50,$xtop+670,$ytop+250,$text_color);

  imagestring($gra,$font,$xtop+imagefontwidth($font),$ytop+50,gbytes(round($maxi)),$text_color);
  for($cnt=10;$cnt < 200;$cnt=$cnt+10)
    {
      imageline($gra,$xtop+50,$ytop+50+$cnt,$xtop+670,$ytop+$cnt+50,$text_color);
      imagestring($gra,$font,$xtop+imagefontwidth($font),$ytop+50+$cnt,gbytes(round($maxi-($disti*($cnt/10)))),$text_color);
    }

  imagestring($gra,$font,$xtop+670+imagefontwidth($font),$ytop+50,gbytes(round($maxo)),$text_color);
  for($cnt=10;$cnt < 200;$cnt=$cnt+10)
    {
      imageline($gra,$xtop+50,$ytop+50+$cnt,$xtop+670,$ytop+$cnt+50,$text_color);
      imagestring($gra,$font,$xtop+imagefontwidth($font)+670,$ytop+50+$cnt,gbytes(round($maxo-($disto*($cnt/10)))),$text_color);
    }

  for($cnt=20;$cnt < 620;$cnt=$cnt+20)
    {
      imageline($gra,$xtop+50+$cnt,$ytop+50,$xtop+50+$cnt,$ytop+250,$text_color);
      imagestring($gra,$font,$xtop+50+$cnt-20,$ytop+250+10,$cnt/20,$text_color);
    }
  imagestring($gra,$font,$xtop+50+600,$ytop+260,"31",$text_color);

  $ytop=300;
  $xtop=0;
  $title="Average Bytes In/Out Per Connection Per Day";
  $maxi=$conbiamax;
  $maxo=$conboamax;
  $disti=$maxi/20;
  $disto=$maxo/20;

  for($cnt=1;$cnt < 31;$cnt++)
    {
      $ioffset=(($maxi-$connbia[$cnt])/$maxi)*200;
      $ooffset=(($maxo-$connboa[$cnt])/$maxo)*200;
      imagefilledrectangle($gra,$xtop+50+($cnt-1)*20,$ytop+50+$ioffset,$xtop+50+$cnt*20-10,$ytop+250,$plotin_color);
      imagefilledrectangle($gra,$xtop+50+($cnt-1)*20+10,$ytop+50+$ooffset,$xtop+50+$cnt*20,$ytop+250,$plotout_color);
    }


  $txstart=((620-(imagefontwidth($font)*strlen($title)))/2)+$xtop+50;
  $tystart=(50-imagefontheight($font))/2;
  ImageString($gra,$font,$txstart,$ytop+$tystart,$title, $text_color);
  imagerectangle($gra,$xtop+50,$ytop+50,$xtop+670,$ytop+250,$text_color);

  imagestring($gra,$font,$xtop+imagefontwidth($font),$ytop+50,gbytes(round($maxi)),$text_color);
  for($cnt=10;$cnt < 200;$cnt=$cnt+10)
    {
      imageline($gra,$xtop+50,$ytop+50+$cnt,$xtop+670,$ytop+$cnt+50,$text_color);
      imagestring($gra,$font,$xtop+imagefontwidth($font),$ytop+50+$cnt,gbytes(round($maxi-($disti*($cnt/10)))),$text_color);
    }

  imagestring($gra,$font,$xtop+670+imagefontwidth($font),$ytop+50,gbytes(round($maxo)),$text_color);
  for($cnt=10;$cnt < 200;$cnt=$cnt+10)
    {
      imageline($gra,$xtop+50,$ytop+50+$cnt,$xtop+670,$ytop+$cnt+50,$text_color);
      imagestring($gra,$font,$xtop+imagefontwidth($font)+670,$ytop+50+$cnt,gbytes(round($maxo-($disto*($cnt/10)))),$text_color);
    }

  for($cnt=20;$cnt < 620;$cnt=$cnt+20)
    {
      imageline($gra,$xtop+50+$cnt,$ytop+50,$xtop+50+$cnt,$ytop+250,$text_color);
      imagestring($gra,$font,$xtop+50+$cnt-20,$ytop+250+10,$cnt/20,$text_color);
    }
  imagestring($gra,$font,$xtop+50+600,$ytop+260,"31",$text_color);

  ImagePng($gra);
%>
