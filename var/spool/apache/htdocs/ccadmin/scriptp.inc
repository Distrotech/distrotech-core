<%
$varchart="varchar(64)";
$dbrows=array();
$dbrows['id']="bigint";
$dbrows['leadid']="bigint";
$dbrows['contid']="bigint";
function parseinput($matches) {
  global $dbrows,$varchart;
  switch ($matches[1] ) {
    case "C":$ret="<INPUT TYPE=CHECKBOX NAME=SCRIPT_" . $matches[2]; 
             if ($matches[3] == "t") {
               $ret.=" CHECKED";
             }
             $ret.=">";
             $dbrows[$matches[2]]="boolean";
             break;
    case "I":$ret="<INPUT TYPE=INPUT NAME=SCRIPT_" . $matches[2] . " VALUE=\"" . $matches[3] . "\">";
             $dbrows[$matches[2]]=$varchart;
             break;
    case "N":$ret="<INPUT TYPE=INPUT NAME=SCRIPT_" . $matches[2] . " VALUE=\"" . $matches[3] . "\">";
             $dbrows[$matches[2]]="numeric";
             break;
    case "D":$ret="<INPUT TYPE=INPUT NAME=SCRIPT_" . $matches[2] . " VALUE=\"" . $matches[3] . "\">";
             $dbrows[$matches[2]]="timestamp with time zone";
             break;
    case "T":$ret="<TEXTAREA NAME=SCRIPT_" . $matches[2] . " COLS=" . $matches[3] . " ROWS=" . $matches[4] . "></TEXTAREA>";
             $dbrows[$matches[2]]="text";
             break;
    case "R":$sopts=explode(":",$matches[3]);
             $ret="";
             for($selcnt=0;$selcnt<count($sopts);$selcnt++) {
               $ret.="<INPUT TYPE=RADIO NAME=SCRIPT_" . $matches[2] . " VALUE=\"" . $sopts[$selcnt] . "\">" . $sopts[$selcnt] . "</OPTION>";
             }
             $dbrows[$matches[2]]=$varchart;
             break;
    case "S":$ret="<SELECT NAME=SCRIPT_" . $matches[2] . ">";
             $sopts=explode(":",$matches[3]);
             for($selcnt=0;$selcnt<count($sopts);$selcnt++) {
               $ret.="<OPTION VALUE=\"" . $sopts[$selcnt] . "\">" . $sopts[$selcnt] . "</OPTION>";
             }
             $ret.="</SELECT>";
             $dbrows[$matches[2]]=$varchart;
             break;
    case "\n\n":$ret="<P>";
             break;
  }
  return $ret;
}
function getscripthtml($text,$htmls) {
  if ($htmls == "t") {
    return preg_replace_callback(array("|@([CI])([a-zA-Z0-9]+)%(.*)@|","|@([N])([a-zA-Z0-9]+)%([0-9\.]+)@|","|@([T])([a-zA-Z0-9]+)%([0-9]+):([0-9]+)@|","|@([SR])([a-zA-Z0-9]+)%([a-zA-Z0-9\: ]+)@|","|@(D)([a-zA-Z0-9]+)%(.*)@|"),"parseinput",$text);
  } else {
    return nl2br(preg_replace_callback(array("|@([CI])([a-zA-Z0-9]+)%(.*)@|","|@([N])([a-zA-Z0-9]+)%([0-9\.]+)@|","|@([T])([a-zA-Z0-9]+)%([0-9]+):([0-9]+)@|","|(\n\n)|","|@([SR])([a-zA-Z0-9]+)%([a-zA-Z0-9\: ]+)@|","|@(D)([a-zA-Z0-9]+)%(.*)@|"),"parseinput",htmlspecialchars($text)));
  }
}
%>
