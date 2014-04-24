<%
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
%>
<link rel="stylesheet" href="/style.php">
<CENTER>
<FORM METHOD=POST NAME=hpcats>
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
<TR CLASS=list-color2>
  <TH CLASS=heading-body COLSPAN=2>Category Updates</TH>
</TR>
<%

$now=time();
$dnow=new DateTime("@$now");
$crow=array("list-color1","list-color2");

print "<INPUT TYPE=HIDDEN NAME=cat>\n";
print "<TR CLASS=list-color1>\n";
print "<TH WIDTH=30% CLASS=heading-body2>Question</TH><TH CLASS=heading-body2>Answer</TH>\n</TR>\n";

/*FAKE*/
$quest=array("q1" => "a1","q2" => "a2","q3"=>"a3","q4"=>"a4");
$cnt=1;
while($data = each($quest)) {
  $rcnt=$cnt % 2;

  print "<TR CLASS=" . $crow[$rcnt] . "><TD WIDTH=50%>" . $data[0] . "</TD><TD>";
  print "<INPUT TYPE=TEXT NAME=\"ques_" . $data[0] . "\" VALUE=\"" . $data[1] . "\">";
  print "</TD></TR>\n";
  $cnt++;
}
$rcnt=$cnt % 2;
%>
<TR CLASS=<%print $crow[$rcnt];%>><TH COLSPAN=2><INPUT TYPE=BUTTON ONCLICK=popdown() VALUE=Update></TH></TR>
</FORM>
</TABLE>
