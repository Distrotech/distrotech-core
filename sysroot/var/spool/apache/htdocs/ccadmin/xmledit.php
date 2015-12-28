<link rel="stylesheet" href="/style.php">
<script language="JavaScript" src="/java_popups.php" type="text/javascript"></script>
<script language="JavaScript" src="/xmlscript.js" type="text/javascript"></script>
<body class=popup>
<DIV ID=blanket></DIV>
<div id="popUpDiv">
  <div>
    <div id=popUpDivTitle border=0 height=20 width=100% align=RIGHT>
      <a href=javascript:popdown()><IMG SRC=/images/exit.png HEIGHT=20></A>
    </div>
    <div id=popUpDivContent width=100% height=100?></div>
  </div>
</div>
<DIV ID=main-body CLASS=popup>
<CENTER>
<FORM NAME=scriptform>
<TABLE border=0 width=90% cellspacing=0 cellpadding=0>
  <TR CLASS=list-color2>
    <TH COLSPAN=2 CLASS=heading-body>
      Testing Script
    </TH>
  </TR>
  <TR CLASS=list-color1>
    <TD COLSPAN=2 WIDTH=100%>
      <div id="script" class=ccscript style="white-space: normal;"></div>
    </TD>
  </TR>
  <TR CLASS=list-color2>
    <TH CLASS=heading-body2 COLSPAN=2>Add A New Paragraph To Script</TH>
  </TR>
  <TR CLASS=list-color2>
    <TD WIDTH=25%>Script Text</TD><TD WIDTH=75%><TEXTAREA NAME=newtext ROWS=10 COLS=70></TEXTAREA></TD>
  </TR>
  <TR CLASS=list-color1>
    <TD>Type Of Input</TD><TD><SELECT NAME=inputtype>
      <OPTION VALUE=checkbox>Yes/No Checkbox</OPTION>
      <OPTION VALUE=select>Drop Down List</OPTION>
      <OPTION VALUE=radio>Multi Button Chooser</OPTION>
      <OPTION VALUE=input>Text Input</OPTION>
      <OPTION VALUE=textarea>Multi Line Text</OPTION>
    </SELECT></TD>
  </TR>
  <TR CLASS=list-color1>
    <TD>Name Of Input</TD><TD WIDTH=75%><INPUT TYPE=text NAME=inputdesc></TD>
  </TR>
  <TR CLASS=list-color2>
    <TD COLSPAN=2 ALIGN=MIDDLE>
      <INPUT TYPE=BUTTON ONCLICK=addpara(xmlDoc) VALUE="Add Paragraph">
      <INPUT TYPE=button ONCLICK=savescript(xmlDoc,'<?php print $_POST['mmap'];?>') VALUE="Save XML">
    </TD>
  </TR>
  <TR>
</TABLE>
</FORM>
</div>

<script>
var xmlDoc = loadXMLDoc('<?php print $_POST['mmap'];?>');
loadhtml("script", xmlDoc, true);
</script>
