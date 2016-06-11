<?php
/*
 * Start the session if not started
 */
include "../cdr/auth.inc";
include_once "../session.inc";
$sessid=session_id();
if ($sessid == "") {
  ob_start('ob_gzhandler');
  session_name("server_admin");
  session_set_cookie_params(28800);
  session_start();
  if (!isset($_SESSION['auth'])) {
    $_SESSION['auth']=false;
  }
}
?>
<link rel="stylesheet" href="/style.php">
<CENTER>
<FORM METHOD=POST NAME=hpcats>
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH CLASS=heading-body COLSPAN=2>Category Updates</TH>
</TR>
<?php

$now=time();
$dnow=new DateTime("@$now");

$crow=array("list-color1","list-color2");


if (!isset($_POST['cat'])) {
  print "<INPUT TYPE=HIDDEN NAME=cat>\n";

  /*FAKE*/
  $cats=array("Storage","Industry Standard Servers","Bussiness Critical Servers","Networking","Technology Services");
  $color=array("green","orange","red");

  for($cnt=0;$cnt < count($cats);$cnt++) {
    /*FAKE*/
    $fake=$now-rand(1,4)*30*86400;
    $catid=$cnt;
    $catn=$cats[$catid];

    $date=$fake;
    $dcat=new DateTime("@$date");
    $diff=date_diff($dnow,$dcat);
    $diffo=$diff->format('%a');
    $last=gmdate("Y-m-d H:i:s", $date);

    $rcnt=$cnt % 2;

    print "<TR CLASS=" . $crow[$rcnt] . ">";
    print "<TD WIDTH=50% CLASS=option-";
    if ($diffo > 90) {
      print $color[2];
    } else if ($diffo > 60) {
      print $color[1];
    } else {
      print $color[0];
    }
    print "><A HREF=javascript:subcat('" . $catid . "')>" . $catn . "</A></TD></TR>\n";
  }
} else if (isset($_POST['cat'])) {
  print "<INPUT TYPE=HIDDEN NAME=cat>\n";
  print "<TR CLASS=list-color1>\n";
  print "<TH CLASS=heading-body2>Question</TH><TH CLASS=heading-body2>Answer</TH>\n</TR>\n";

  /*FAKE*/
  $quest=array("q1" => "a1","q2" => "a2","q3"=>"a3","q4"=>"a4");
  $cnt=1;
  while($data = each($quest)) {
    $rcnt=$cnt % 2;

    print "<TR CLASS=" . $crow[$rcnt] . "><TD WIDTH=50%>" . $data[0] . "</TD><TD>";
    print "<INPUT TYPE=TEXT SIZE=40 NAME=\"ques_" . $data[0] . "\" VALUE=\"" . $data[1] . "\">";
    print "</TD></TR>\n";
    $cnt++;    
  }
}
?>
</TABLE>

<SCRIPT>
function subcat(catid) {
  document.hpcats.cat.value=catid;
  document.hpcats.submit();
}
</SCRIPT>
