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

if (! $db) {
  include "auth.inc";
}
?>
<FORM METHOD=POST NAME=csprov onsubmit="ajaxsubmit(this.name);return false">
<CENTER>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
<TR CLASS=list-color2>
<?php
if ((isset($_POST['provupdate']) || isset($_POST['provsave'])) && (($_POST['trunkprefix'] != "") || (($_POST['trunkprefix'] == "") && ($_POST['newprov'] != "")))) {
  if (($_POST['trunkprefix'] == "") && ($_POST['newprov'] != "")) {
    pg_query($db,"INSERT INTO provider (name) VALUES ('" . pg_escape_string($db, $_POST['newprov']) . "')");
    $provq=pg_query($db,"SELECT trunkprefix,name,removeprefix,nationalprefix,internationalprefix,nationallen FROM provider " .
			"WHERE name = '" . pg_escape_string($db, $_POST['newprov']) . "' LIMIT 1");
  } else {
    if (isset($_POST['provsave'])) {
      pg_query($db, "UPDATE provider SET name='" . pg_escape_string($db, $_POST['pname']) . "',removeprefix='" . $_POST['removeprefix'] . "'," .
		"nationalprefix='" . $_POST['pnatpre'] . "',internationalprefix='" . $_POST['pinatpre'] . "'," . 
		"nationallen='" . $_POST['pnatlen'] . "' WHERE trunkprefix = " . $_POST['trunkprefix']);
    }
    $provq=pg_query($db,"SELECT trunkprefix,name,removeprefix,nationalprefix,internationalprefix,nationallen FROM provider " .
			"WHERE trunkprefix = " . $_POST['trunkprefix'] . " LIMIT 1");
  }
  $prov=pg_fetch_row($provq,0);
?>
<TH COLSPAN=2 CLASS=heading-body>Editing Provider <?php print $prov[1] . " (" . $prov[0] . ")";?></TH></TR>
<TR CLASS=list-color1>
<TD WIDTH=50%>Provider Name</TD><TD><INPUT TYPE=TEXT NAME=pname VALUE="<?php print $prov[1];?>"></TD></TR>
<TR CLASS=list-color2>
<TD>Remove Prefix (Local Country Code)</TD><TD><INPUT TYPE=TEXT NAME=removeprefix VALUE="<?php print $prov[2];?>"></TD></TR>
<TR CLASS=list-color1>
<TD>National Prefix To Add</TD><TD><INPUT TYPE=TEXT NAME=pnatpre VALUE="<?php print $prov[3];?>"></TD></TR>
<TR CLASS=list-color2>
<TD>Min National Length</TD><TD><INPUT TYPE=TEXT NAME=pnatlen VALUE="<?php print $prov[5];?>"></TD></TR>
<TR CLASS=list-color1>
<TD>International Prefix</TD><TD><INPUT TYPE=TEXT NAME=pinatpre VALUE="<?php print $prov[4];?>"></TD></TR>
<TR CLASS=list-color2>
<TD ALIGN=MIDDLE COLSPAN=2>
<INPUT TYPE=SUBMIT onclick=this.name='provsave' VALUE="Save Changes">
<INPUT TYPE=SUBMIT onclick=this.name='provdelete' VALUE="Delete">
<INPUT TYPE=BUTTON ONCLICK="deleteconf('This Provvider',document.csprov,document.csprov.provdelete)" VALUE="Delete">
<INPUT TYPE=HIDDEN NAME=provdelete>
<INPUT TYPE=HIDDEN NAME=trunkprefix VALUE="<?php print $prov['0'];?>">
</TD></TR>
</TABLE>
</FORM>
<?php
} else {
  if ((isset($_POST['provdelete'])) && ($_POST['trunkprefix'] != "") && ($_POST['provdelete'] = "1"))  {
    pg_query($db,"DELETE FROM provider WHERE trunkprefix='" . $_POST['trunkprefix'] . "'");
    pg_query($db,"DELETE FROM trunk WHERE trunkprefix='" . $_POST['trunkprefix'] . "'");
    pg_query($db,"DELETE FROM tariffrate WHERE trunkprefix='" . $_POST['trunkprefix'] . "'");
  }
?>
  <TH CLASS=heading-body COLSPAN=2><?php print _("Select Provider To Modify");?></TH>
</TR>
<TR CLASS=list-color1>
  <TD ALIGN=LEFT WIDTH=50% onmouseover="myHint.show('Z1')" onmouseout="myHint.hide()"><?php print _("Provider To Configure");?></TH>
  <TD WIDTH=50% ALIGN=LEFT>
<?php
  $provq=pg_query($db,"SELECT trunkprefix,name FROM provider ORDER BY name");
  print "  <SELECT NAME=trunkprefix onchange=this.form.subme.click()>\n    <OPTION VALUE=\"\">" . _("Add New Provider Bellow") . "</OPTION>\n";
  for($i=0;$i < pg_num_rows($provq);$i++) {
    $r=pg_fetch_array($provq,$i);
    print "    <OPTION VALUE=\"" .  $r[0] . "\">" . $r[1] . "</OPTION>\n";
  }
?>
  </SELECT>
  </TD></TR>
<TR CLASS=list-color2>
  <TD ALIGN=LEFT WIDTH=50% onmouseover="myHint.show('Z2')" onmouseout="myHint.hide()"><?php print _("Provider To Add");?></TH>
  <TD WIDTH=50% ALIGN=LEFT>
    <INPUT NAME=newprov VALUE="">
  </TD></TR>
<TR CLASS=list-color1>
  <TH COLSPAN=2>
    <INPUT TYPE=SUBMIT NAME=subme onclick=this.name='provupdate' VALUE="<?php print _("Add/Modify Provider");?>">
</TH>
</TR>
  </TABLE>
  </FORM>
<?php
}
?>
