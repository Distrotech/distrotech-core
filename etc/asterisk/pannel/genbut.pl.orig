#!/usr/bin/perl

open(BF,">/tmp/op_buttons.cfg.new");
open(SF,">/tmp/op_style.cfg.new");

use DBI;

$user="asterisk";
$password="asterisk";
$driver = "ODBC";
$database="Asterisk";

$dsn = "DBI:$driver:$database";
$dbh = DBI->connect($dsn, $user, $password);

$contexts=$dbh->prepare("SELECT distinct context from console");
$contexts->execute;

if ($contexts->rows == 0) {
  $contexts=$dbh->prepare("INSERT INTO console (context) VALUES ('default')");
  $contexts->execute;
  $contexts=$dbh->prepare("SELECT distinct context from console");
  $contexts->execute;
  $delblank=1;
}

while(@crow=$contexts->fetchrow_array) {
  if (@crow[0] eq "default") {
    print  SF "[general]\n";
    $getuser=$dbh->prepare("SELECT DISTINCT users.mailbox,fullname FROM users
           LEFT OUTER JOIN console USING (mailbox)
           LEFT OUTER JOIN console_include ON (console.context=console_include.include)
             WHERE length(users.mailbox) = 4 AND (
               console.context='" . @crow[0] . "' OR console.context IS NULL OR (console_include.context='" . @crow[0] . "' AND console.context=console_include.include))
             ORDER BY fullname LIMIT 96");
  } else {
    if ( ! -d "/var/spool/apache/htdocs/reception/" . @crow[0] ) {
      print "Create State Directory " . @crow[0] . " !!!\n\n";
    }
    $getuser=$dbh->prepare("SELECT DISTINCT users.mailbox,fullname FROM users
           LEFT OUTER JOIN console USING (mailbox)
           LEFT OUTER JOIN console_include ON (console.context=console_include.include)
             WHERE length(users.mailbox) = 4 AND (
               console.context='" . @crow[0] . "' OR (console_include.context='" . @crow[0] . "' AND console.context=console_include.include))
             ORDER BY fullname LIMIT 96");
    print  SF "[" . @crow[0] . "]\n";
  }
 
  $getuser->execute;
  $userrc=$getuser->rows;
  $pos=0;

  while(@row=$getuser->fetchrow_array) {
    $pos++;
    $getlines=$dbh->prepare("select count,context from console where mailbox='@row[0]'");
    $getlines->execute;

    if (! $getlines->rows) {
      $addcons=$dbh->prepare("INSERT INTO console (mailbox,position) VALUES ('@row[0]',$pos)");
      $addcons->execute;
      push(@cons,"1");
      push(@cons,"default");
    } else {
      @cons=$getlines->fetchrow_array;
      if (@cons[1] eq @crow[0]) {
        $upcons=$dbh->prepare("UPDATE console set position='$pos' where mailbox='@row[0]'");
        $upcons->execute;
      }
    }
    print BF "[SIP/" . @row[0] . "]\n";
    print BF "Position=$pos";
    if (@cons[0] > 1) {
      for($ccnt=$pos+1;$ccnt < $pos + @cons[0];$ccnt++) {
        print BF ",$ccnt";
      }
      $pos=$ccnt-1;
    }
    print BF "\n";
    print BF <<__EOB__;
Label="@row[1] (@row[0])"
Extension=@row[0]
Context=intext
Icon=1
VoiceMailExt=100*@row[0]@private
Voicemail_Context=6
Panel_context=@crow[0]

__EOB__
  }

  if ($userrc > 48) {
    $rowcnt=16;
    $lpos=97;
    $mscale=3;
    $lmargint=12;
    $lmarginl=15;
    $lfont=8;
    $clifont=8;
    $climarginl=25;
    $climargint=0;
    $timerfont=8;
    $timerml=5;
    $timermt=22;
    $bwidth=141;
    $bheight=34;
    $mailmleft=-23;
    $mailmtop=13;
    $ledmtop=17;
    $ledmleft=10;
    $ledscale=30;
    $btnbdr=8;
    $arrowscale=50;
    $arrowmleft=10;
    $arrowmtop=5;
    $iconscale=6;
    $iconmleft[1]=-17;
    $iconmleft[2]=-14;
    $iconmleft[3]=-18;
    $iconmleft[4]=-17;
    $iconmleft[5]=-16;
    $iconmleft[6]=-16;

    $iconmtop[1]=21;
    $iconmtop[2]=23;
    $iconmtop[3]=17;
    $iconmtop[4]=16;
    $iconmtop[5]=16;
    $iconmtop[6]=16;
  } elsif ($userrc > 24) {
    $lpos=49;
    $rowcnt=12;
    $mscale=6;
    $lmargint=16;
    $lmarginl=20;
    $lfont=10;
    $clifont=10;
    $climarginl=20;
    $climargint=2;
    $timerfont=10;
    $timerml=20;
    $timermt=30;
    $bwidth=196;
    $bheight=46;
    $mailmleft=-23;
    $mailmtop=20;
    $ledmtop=23;
    $ledmleft=12;
    $ledscale=50;
    $btnbdr=8;

    $arrowscale=50;
    $arrowmleft=10;
    $arrowmtop=8;
    $iconscale=10;

    $iconmleft[1]=-22;
    $iconmleft[2]=-22;
    $iconmleft[3]=-22;
    $iconmleft[4]=-22;
    $iconmleft[5]=-22;
    $iconmleft[6]=-22;

    $iconmtop[1]=24;
    $iconmtop[2]=26;
    $iconmtop[3]=20;
    $iconmtop[4]=20;
    $iconmtop[5]=20;
    $iconmtop[6]=20;
  } elsif ($userrc > 12) {
    $lpos=25;
    $rowcnt=8;
    $mscale=8;

    $lmargint=26;
    $climargint=3;
    $timermt=45;

    $lmarginl=30;
    $climarginl=30;
    $timerml=30;

    $mailmleft=-27;
    $mailmtop=32;

    $lfont=12;
    $clifont=15;
    $timerfont=15;

    $bwidth=245;
    $bheight=69;

    $ledmtop=35;
    $ledmleft=18;
    $ledscale=80;
    $btnbdr=8;

    $arrowscale=80;
    $arrowmleft=15;
    $arrowmtop=12;
    $iconscale=13;

    $iconmleft[1]=-27;
    $iconmleft[2]=-27;
    $iconmleft[3]=-27;
    $iconmleft[4]=-27;
    $iconmleft[5]=-27;
    $iconmleft[6]=-27;

    $iconmtop[1]=36;
    $iconmtop[2]=38;
    $iconmtop[3]=32;
    $iconmtop[4]=32;
    $iconmtop[5]=32;
    $iconmtop[6]=32;
  } else {
    $lpos=13;
    $rowcnt=6;
    $mscale=12;

    $lmargint=36;
    $climargint=6;
    $timermt=60;

    $lmarginl=40;
    $climarginl=40;
    $timerml=40;

    $mailmleft=-45;
    $mailmtop=47;

    $lfont=14;
    $clifont=18;
    $timerfont=18;

    $bwidth=330;
    $bheight=92;

    $ledmtop=45;
    $ledmleft=22;
    $ledscale=100;
    $btnbdr=8;

    $arrowscale=100;
    $arrowmleft=20;
    $arrowmtop=16;

    $iconscale=22;

    $iconmleft[1]=-45;
    $iconmleft[2]=-45;
    $iconmleft[3]=-45;
    $iconmleft[4]=-45;
    $iconmleft[5]=-45;
    $iconmleft[6]=-45;

    $iconmtop[1]=51;
    $iconmtop[2]=53;
    $iconmtop[3]=47;
    $iconmtop[4]=47;
    $iconmtop[5]=47;
    $iconmtop[6]=47;
  }
  print BF <<__EOB__;
[799]
Position=$lpos
Label="Reception Queue"
Extension=-1
Context=private
Icon=5
Panel_context=@crow[0]

__EOB__

  for ($ccnt=1;$ccnt <= $rowcnt-1;$ccnt++) {
    $pos=$ccnt+$lpos;
    $room=$ccnt+899;
    print BF <<__EOB__;
[$room]
Position=$pos
Label="Conference $room"
Extension=$room
Context=conferences
Panel_context=@crow[0]
Icon=6

__EOB__
  }

  print SF<<__EOF__;
enable_crypto=0            ; set to 1 for encrypting server to client traffic
enable_animation=1
use_embed_fonts=1
ledcolor_ready=0x00A000
ledcolor_busy=0xA01020
ledcolor_agent=0xD0d020
label_font_size=$lfont
label_font_color = 000000
label_font_family=Verdana  ; only valid when use_embed_fonts is disabled
label_margin_top=$lmargint
label_margin_left=$lmarginl
label_shadow=0
label_shadow_color = FFFFFF
clid_font_size=$clifont
clid_font_family=Verdana   ; only valid when use_embed_fonts is disabled
clid_font_color = 00DD00
clid_margin_top=$climargint
clid_margin_left=$climarginl
timer_font_size=$timerfont
timer_font_family=Verdana  ; only valid when use_embed_fonts is disabled
timer_font_color = 200070
timer_margin_top=$timermt
timer_margin_left=$timerml
dimm_noregister_by=20
dimm_lagged_by=60

;Make longer
;btn_width=110
btn_width=$bwidth

btn_height=$bheight
btn_padding=1
btn_line_width=1
btn_line_color=0x000000
btn_fadecolor_1=ccccff
btn_fadecolor_2=ffffff
btn_round_border=$btnbdr
btn_highlight_color=ff0000
led_scale=$ledscale
led_margin_top=$ledmtop
led_margin_left=$ledmleft
arrow_scale=$arrowscale
arrow_margin_top=$arrowmtop
arrow_margin_left=$arrowmleft
icon1_margin_top=$iconmtop[1]
icon1_margin_left=$iconmleft[1]
icon1_scale=$iconscale
icon2_margin_top=$iconmtop[2]
icon2_margin_left=$iconmleft[2]
icon2_scale=$iconscale
icon3_margin_top=$iconmtop[3]
icon3_margin_left=$iconmleft[3]
icon3_scale=$iconscale
icon4_margin_top=$iconmtop[4]
icon4_margin_left=$iconmleft[4]
icon4_scale=$iconscale
icon5_margin_top=$iconmtop[5]
icon5_margin_left=$iconmleft[5]
icon5_scale=$iconscale
icon6_margin_top=$iconmtop[6]
icon6_margin_left=$iconmleft[6]
icon6_scale=$iconscale
mail_margin_left=$mailmleft
mail_margin_top=$mailmtop
mail_scale=$mscale

show_security_code=0
show_clid_info=4
show_btn_help=3
show_btn_debug=0
show_btn_reload=2
show_status=5

__EOF__

print BF <<__EOB__;
[rectangle]
x=0
y=35
width=990
height=560
line_width=3
line_color=0xffff10
fade_color1=0xffff10
fade_color2=0xffff3F
rnd_border=2
alpha=20       
layer=bottom
Panel_context=@crow[0]

__EOB__
}

if ($delblank) {
  $contexts=$dbh->prepare("DELETE FROM console WHERE mailbox=''");
  $contexts->execute;
}
