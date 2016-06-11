<?php 
if (! $db) {
  include "/var/spool/apache/htdocs/cshop/auth.inc";
}

if (isset($_POST['addcompany'])) {
  pg_query($db,"INSERT INTO virtualcompany (resellerid,description,contact,email,altnumber) VALUES (" . $_SESSION['resellerid'] . ",'" .
        $_POST['description'] . "','" . $_POST['contact'] . "','" . $_POST['email'] . "','" . $_POST['altnumber'] . "')");
  $virtq=pg_query($db,"SELECT companyid FROM virtualcompany WHERE description='" . $_POST['description'] . "'");
  list($_POST['number']) = pg_fetch_array($virtq,0);
  if (!isset($_POST['number'])) {
    print "<B>Add Virtual Company Failed</B>";
  }
} else if (isset($_POST['savevirt'])) {
  unset($_POST['savevirt']);
  unset($_POST['deluser']);
  unset($_POST['disppage']);
  unset($_POST['showmenu']);
  unset($_POST['classi']);
  unset($_POST['style']);
  $custid=$_POST['number'];

  while(list($item,$value) = each($_POST)) {
    if ($item != "number") {
      $upquery .=$item . "='" . $value . "',";
    }
  }
  $upquery=substr($upquery,0,-1);
  pg_query($db, "UPDATE virtualcompany SET " . $upquery . " WHERE companyid=" . $_POST['number']);
} else if ($_POST['deluser'] == "1") {
  pg_query($db, "DELETE FROM virtualcompany WHERE companyid=" . $_POST['number']);
  pg_query($db, "DELETE FROM companysites WHERE companyid=" . $_POST['number']);
  pg_query($db, "DELETE FROM intersite WHERE companyid=" . $_POST['number']);
  pg_query($db, "DELETE FROM creditpool WHERE companyid=" . $_POST['number']);
  include "getvirt.php";
  return;
}


$vcinfq=pg_query($db,"SELECT description,contact,email,altnumber FROM virtualcompany " .
			"WHERE companyid=" . $_POST['number'] . " LIMIT 1");

$vcinf=pg_fetch_row($vcinfq,0);
?>
<CENTER> 
<FORM NAME=editvirt METHOD=POST onsubmit="ajaxsubmit(this.name);return false">
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%> 
<TR CLASS=list-color2>
<TH COLSPAN=2 CLASS=heading-body>Editing <?php print $vcinf[0];?></TH></TR>
<TR CLASS=list-color1>
<TD WIDTH=50%>Company Name</TD><TD><INPUT TYPE=TEXT NAME=description VALUE="<?php print $vcinf[0];?>"></TD></TR>
<TR CLASS=list-color2>
<TD>Contact Person</TD><TD><INPUT TYPE=TEXT NAME=contact VALUE="<?php print $vcinf[1];?>"></TD></TR>
<TR CLASS=list-color1>
<TD>Email</TD><TD><INPUT TYPE=TEXT NAME=email VALUE="<?php print $vcinf[2];?>"></TD></TR>
<TR CLASS=list-color2>
<TD>Contact Number</TD><TD><INPUT TYPE=TEXT NAME=altnumber VALUE="<?php print $vcinf[3];?>"></TD></TR>
<TR CLASS=list-color1>
<TD ALIGN=MIDDLE COLSPAN=2>
<INPUT TYPE=SUBMIT onclick=this.name='savevirt' VALUE="Save Changes">
<INPUT TYPE=BUTTON ONCLICK="deleteconf('This Company',document.editvirt,document.editvirt.deluser)" VALUE="Delete">
<INPUT TYPE=HIDDEN NAME=deluser>
<INPUT TYPE=HIDDEN NAME=number VALUE="<?php print $_POST['number'];?>">
</TD></TR>
</TABLE>
</FORM>
