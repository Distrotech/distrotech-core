<%
if (!isset($_SESSION['auth'])) {
  exit;
}

if ($rdn == "") {
  include "../ldap/auth.inc";
}

header("Content-type: application/ms-excel");

if ($filetype == "users") {
  $atrib=array_merge(array("uid"),$atrib);

  $descrip["uid"]="Username";
  $descrip["c"]="Country";

  array_push($atrib,"c");
  
  for($i=0;$i < count($atrib);$i++) {
    print "\"" . $descrip[$atrib[$i]] . "\",";
  }
  print "\n";

  for($i=0;$i < count($atrib);$i++) {
    print "\"" . $atrib[$i] . "\",";
  }
  print "\n";

  $sr=ldap_search($ds,"ou=Users","(&(uid=*)(objectclass=officeperson))",$atrib);
  $info=ldap_get_entries($ds,$sr);

  for ($i=0; $i<$info["count"]; $i++) {
    for($a=0;$a < count($atrib);$a++) {
      print "\"";
      $attr=strtolower($atrib[$a]); 
      for($b=0;$b < $info[$i][$attr]["count"];$b++) {
       print $info[$i][$attr][$b];
        if ($b < $info[$i][$atrib[$a]]["count"]-1) {
          print ", ";
        }
      }
      if ($a < count($atrib)-1) {
        print "\",";
      } else {
        print "\"";
      }
    }
    print "\n";
  }
}
%>
