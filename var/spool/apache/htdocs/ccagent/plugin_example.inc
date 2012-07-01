<%
function plug_div() {%>
  <TABLE WIDTH=100% CELLPADDING=0 CELLSPACING=0>
<%
  $now=time();
  $dnow=new DateTime("@$now");

  $crow=array("list-color1","list-color2");

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

    if (($cnt % 3) == 0) {
      if ($cnt > 0) {
        print "</TR>\n";
      }
      print "<TR>\n";
    }

    print "<TD WIDTH=33% CLASS=option-";
    if ($diffo > 90) {
      print $color[2];
    } else if ($diffo > 60) {
      print $color[1];
    } else {
      print $color[0];
    }
    print "><A HREF=javascript:subcat('" . $catid . "')>" . $catn . "</A></TD>\n";
  }
  for($rcnt=3 - ($cnt % 3);$rcnt > 0;$rcnt--) {
    print "<TD>&nbsp;</TD>\n";
  }
  print "</TR>\n";
%>
  </TABLE><%
}

function plug_js() {%>
function subcat(catid) {
  popup(null,450,450);
  document.hpcats.cat.value=catid;
  AJAX.senddata('popUpDivContent','hpcats','/ccadmin/poc.php', false);
}
<%
}

function plug_form() {%>
<FORM METHOD=POST NAME=hpcats>
<INPUT TYPE=HIDDEN NAME=cat>
</FORM>
<%
}

$plug_t="Customer Intelegence";
