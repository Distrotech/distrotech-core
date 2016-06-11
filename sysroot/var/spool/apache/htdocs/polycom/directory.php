<?php
include "getphone.inc";
?>
<<?php print "?";?>xml version="1.0" encoding="UTF-8" standalone="yes"?>
<!-- $Revision: 1.2 $  $Date: 2004/12/21 18:28:05 $ -->
<directory>
  <item_list>
<?php
if ($mac != "") {
  $dirq="SELECT fullname,name,CASE WHEN (name != exten AND callgroup = '" . $callgroup . "') THEN 1 ELSE 0 END 
           FROM users 
             LEFT OUTER JOIN features ON (name=exten)
             LEFT OUTER JOIN astdb AS local ON (substr(name,1,2)=local.key AND local.family='LocalPrefix') 
           WHERE local.value='1' AND (ipaddr IS NULL OR ipaddr='' OR regseconds >= extract(epoch from now()) - 86400*2) AND 
                 (dgroup = '" . $dirgroup . "' OR dgroup IS NULL OR dgroup='' OR '" . $dirgroup . "' = '')
           ORDER BY fullname";
//  print $dirq . "\n";

  if ($ptype == "IP_601") {
    $maxbuddy=42;
  } else {
    $maxbuddy=8;
  }
  $userdir=pg_query($db,$dirq);
  for($r=0;$r < pg_num_rows($userdir);$r++) {
    list($uname,$cname,$buddy)=pg_fetch_array($userdir,$r);
    if ($cname == $exten ) {
      continue;
    }

    if (($ptype == "IP_601") && ($callgroup == "1")){
      $buddy=1;
    } if (($ptype != "IP_600") && ($ptype != "IP_601")) {
      $buddy=0;
    }

    $lnpos=strrpos($uname," ");
    print "    <item>\n";
    if (($lnpos == "") && ($uname != "")) {
      $lname="";
      $fname=$uname;
    } elseif ($uname != "") {
      $fname=substr($uname,0,strpos($uname," "));
      $lname=substr($uname,strpos($uname," ")+1);
      print "        <ln>" . $lname . "</ln>\n";
    } else {
      $fname=$cname;
    }  
    print "        <fn>" . $fname . "</fn>\n";
    print "        <ct>" . $cname . "</ct>\n";
    if (($buddy) && ($famn != $cname) && ($buddycnt < $maxbuddy)) {
      print "        <sd>" . ($r + 2). "</sd>\n";
      $buddycnt++;
      print "        <bw>1</bw>\n";
    } else {
      print "        <bw>0</bw>\n";
    }
//    print "        <rt>3</rt>\n";
    print "        <dc/>\n";
    print "        <ad>0</ad>\n";
    print "        <ar>0</ar>\n";
    print "        <bb>0</bb>\n";
    print "    </item>\n";
  }
}
?>
  </item_list>
</directory>
