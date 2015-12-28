<?php
include "../cdr/auth.inc";
$path="/var/spool/apache/htdocs/";
$font=$path . "cdr/butfont.ttf";

$size=8;
$esize=8.5;
$offset=2;
$cellh=23;
$cellw=72;

if ($type != "kp") {
  $xmax=6*$cellw+1;
  $ymax=23*$cellh+1;
} else {
  $xmax=3*$cellw+1;
  $ymax=14*$cellh+1;
}

header ("Content-type: image/png");
$im = @imagecreate($xmax,$ymax);

$black=ImageColorAllocate($im,0,0,0);
$white=ImageColorAllocate($im,255,255,255);
$grey=ImageColorAllocate($im,192,192,192);


imagefill($im,0,0,$white);


if ($type == "xp") {
  $addp=12;
} else if ($type == "xp2") {
  $addp=54;
} else if ($type == "xp3") {
  $addp=96;
} else {
  $addp=0;
}

if ($type != "kp") {
  for($ccnt=1;$ccnt<=6;$ccnt++) {
    imagerectangle($im,$cellw*($ccnt-1),0,$cellw*$ccnt,$ymax-1,$grey);
 }
} else {
  for($ccnt=1;$ccnt<=3;$ccnt++) {
    imagerectangle($im,$cellw*($ccnt-1),0,$cellw*$ccnt,$ymax-1,$grey);
 }
}

for($hcnt=1;$hcnt*$cellh <= $ymax-2*$cellh;$hcnt++) {
  imagerectangle($im,0,$hcnt*$cellh,$xmax,$hcnt*$cellh+$cellh,$grey);
  if ($hcnt % 2) {
    imagefill($im,$cellw+1,$hcnt*$cellh+1,$grey);
    if ($type != "kp") {
      imagefill($im,$cellw*4+1,$hcnt*$cellh+1,$grey);    
    }
  }
}



if ($type != "kp") {
  for($hcnt=1+$addp;$hcnt <= 11+$addp;$hcnt++) {
    $tdata=imagettfbbox(12,0,$font,$hcnt);
    $xnpos=($cellw-$tdata[4])/2;
    ImageTTFText($im,12,0,$xnpos,($hcnt-1-$addp)*2*$cellh+17+$cellh,$black,$font,$hcnt);

    $hcnt2=$hcnt+21;
    $tdata=imagettfbbox(12,0,$font,$hcnt2);
    $xnpos=219+($cellw-$tdata[4])/2;
    ImageTTFText($im,12,0,$xnpos,($hcnt-1-$addp)*2*$cellh+17+$cellh,$black,$font,$hcnt2);
  }

  for($hcnt=12+$addp;$hcnt <= 21+$addp;$hcnt++) {
    $tdata=imagettfbbox(12,0,$font,$hcnt);
    $xnpos=$cellw*2+($cellw-$tdata[4])/2;
    ImageTTFText($im,12,0,$xnpos,($hcnt-11-$addp)*2*$cellh+17,$black,$font,$hcnt);

    $hcnt2=$hcnt+21;
    $tdata=imagettfbbox(12,0,$font,$hcnt2);
    $xnpos=$cellw*5+($cellw-$tdata[4])/2;
    ImageTTFText($im,12,0,$xnpos,($hcnt-11-$addp)*2*$cellh+17,$black,$font,$hcnt2);
  }
} else {
  for($hcnt=1;$hcnt <= 6;$hcnt++) {
    $tdata=imagettfbbox(12,0,$font,$hcnt);
    $xnpos=($cellw-$tdata[4])/2;
    ImageTTFText($im,12,0,$xnpos,($hcnt-1)*2*$cellh+17+2*$cellh,$black,$font,$hcnt);
  }
  for($hcnt=7;$hcnt <= 12;$hcnt++) {
    $tdata=imagettfbbox(12,0,$font,$hcnt);
    $xnpos=$cellw*2+($cellw-$tdata[4])/2;
    ImageTTFText($im,12,0,$xnpos,($hcnt-7)*2*$cellh+17+$cellh,$black,$font,$hcnt);
  }
}

$y=$cellh;

$keys=pg_query($db,"SELECT substr(key,5),fullname,value from astdb left outer join users on (value=name) where key ~ '^fkey[0-9]+$' AND family='" . $exten . "'");
for($row=0;$row < pg_num_rows($keys);$row++) {
  $kdat=pg_fetch_array($keys,$row);

  if ($kdat[2] == "1") {
    $lno=$kdat[0]+1;
    $kdat[1]="Line " . $lno;
  } else if (($kdat[2] >= 900) && ($kdat[2] <=999)) {
    $kdat[1]="Conference Room " . $kdat[2];
  } else if (($kdat[2] > 700) && ($kdat[2] < 750)) {
    $kdat[1]="Call Pickup " . substr($kdat[2],2) . " " . $kdat[2];
  } else if ($kdat[2] == 700) {
    $kdat[1]="Call Park 700";
  }

  $lbreak=strrpos($kdat[1]," ");
  if ($lbreak === false) {
    $first=$kdat[1];
    $last=$kdat[2];
  } else {
    $first=substr($kdat[1],0,$lbreak);
    if (($kdat[2] == "1") || (($kdat[2] >= 900) && ($kdat[2] <= 999)) || (($kdat[2] >= 700)  && ($kdat[2] < 750))) {
      $last=substr($kdat[1],$lbreak+1);  
    } else {
      $last=substr($kdat[1],$lbreak+1) . " (" . $kdat[2] . ")";  
    }
  }

  if ($type == "kp") {
    $kdat[0]=$kdat[0]+1;
    $cmax=7;
  } else if ($type == "xp") {
    $cmax=11;
    $kdat[0]=$kdat[0]-$cmax;
  } else if ($type == "xp2") {
    $cmax=11;
    $kdat[0]=$kdat[0]-$cmax-42;
  } else if ($type == "xp3") {
    $cmax=11;
    $kdat[0]=$kdat[0]-$cmax-84;
  }

  if (($kdat[0] > 21) && ($type != "kp")) {
    $kdat[0]=$kdat[0]-21;
    $x=$cellw*4;
  } else {
    $x=$cellw;
  }

  if ($type != "kp") {
    if ($kdat[0] > $cmax) {
      $y=2*$cellh+2*($kdat[0]-$cmax-1)*$cellh;
      $tdata=imagettfbbox($size,0,$font,$first);
      $x1=$x+$cellw-$tdata[4]-2*$offset;
      $tdata=imagettfbbox($size,0,$font,$last);
      $x2=$x+$cellw-$tdata[4]-2*$offset;
    } else {
      $y=$cellh+2*($kdat[0]-1)*$cellh;
      $x2=$x;
      $x1=$x;
    }
  } else {
    if ($kdat[0] < $cmax) {
      $y=2*$cellh+2*($kdat[0]-1)*$cellh;
      $x2=$x;
      $x1=$x;
    } else {
      $y=$cellh+2*($kdat[0]-$cmax)*$cellh;
      $tdata=imagettfbbox($size,0,$font,$first);
      $x1=$x+$cellw-$tdata[4]-2*$offset;
      $tdata=imagettfbbox($size,0,$font,$last);
      $x2=$x+$cellw-$tdata[4]-2*$offset;
    }
  }

  if (($type == "kp") && ($kdat[0] < 9)) {
    $kpdone[$kdat[0]-1]=1;
  }

  if ((($type == "kp") && ($kdat[0] <= 12)) || 
      (($type != "kp") && ($kdat[0] <= 21) && ($kdat[0] > 0))) {
    ImageTTFText($im,$size, 0,$x1+$offset,$y+$offset+$size,$black,$font,$first);
    ImageTTFText($im,$size, 0,$x2+$offset,$y+2*($offset+$size),$black,$font,$last);
  }
}

if ($type == "kp") {
  $kpkey[0]="Line 1";
  $kpkey[1]="Line 2";
  $kpkey[2]="Reception 9";
  $kpkey[3]="Call Park 700";
  $kpkey[4]="Call Pickup 1 701";
  $kpkey[5]="Call Pickup 2 702";
  $kpkey[6]="Conference Room 900";
  $kpkey[7]="Conference Room 901";

  $x=$cellw;

  for($kpcnt=0;$kpcnt<8;$kpcnt++) {
    if (! $kpdone[$kpcnt]) {
      $lbreak=strrpos($kpkey[$kpcnt]," ");
      if ($lbreak === false) {
        $first=$kpkey[$kpcnt];
        $last="";
      } else {
        $first=substr($kpkey[$kpcnt],0,$lbreak);
        $last=substr($kpkey[$kpcnt],$lbreak+1);  
      }

      if ($kpcnt < 6) {
        $y=2*$cellh+2*($kpcnt)*$cellh;
        $x2=$x;
        $x1=$x;
      } else {
        $y=$cellh+2*($kpcnt-6)*$cellh;
        $tdata=imagettfbbox($size,0,$font,$first);
        $x1=$x+$cellw-$tdata[4]-2*$offset;
        $tdata=imagettfbbox($size,0,$font,$last);
        $x2=$x+$cellw-$tdata[4]-2*$offset;
      }
      ImageTTFText($im,$size, 0,$x1+$offset,$y+$offset+$size,$black,$font,$first);
      ImageTTFText($im,$size, 0,$x2+$offset,$y+2*($offset+$size),$black,$font,$last);
    }
  }
}
ImagePNG($im);
ImageDestroy($im);
?>
