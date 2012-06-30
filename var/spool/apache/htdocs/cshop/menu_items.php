<%
/*
#    Copyright (C) 2002  <Gregory Hinton Nietsky>
#    Copyright (C) 2006  <Superset>
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

include "/var/spool/apache/htdocs/cshop/auth.inc";

function openpage($menu,$page) {
  return "javascript:openpage(\'cshop/" . $page . "\',\'" . $menu . "\')";
}

if ($_SESSION['auser'] != "1") {
  $main['Sessions']="include:apps";
}
$main['Tariffs']="include:tariffs";

if ($_SESSION['auser'] == "1") {
  $main['Credit']="include:credit";
}

$menu=array("apps","tariffs");

$apps["Start Session"]=openpage("apps","start.php");
$apps["End Session"]=openpage("apps","end.php");
$apps["View Session"]=openpage("apps","getses.php");

$tariffs["List Tariffs"]=openpage("tariffs","gettariff.php");
/*
$tariffs["Tariffs For Top 50"]=openpage("tariffs","top50.php");
*/

$credit["Account Topup"]=openpage("credit","topup.php");
$credit["Monthly Credit Topup"]=openpage("credit","credittop.php");

if ($_SESSION['auser'] == 1) {
  $main['Users/Accounts']="include:users";
  $tariffs['Edit Tariffs']=openpage("tariffs","gettplan.php");
  $tariffs['CSV Tariff Upload']=openpage("tariffs","csvrateup.php");
  $credit["Credit Transfer"]=openpage("credit","credit.php");

  if ($_SESSION['usrcnt'] > 0) {
    $users["Create Booth"]=openpage("users","mkbooth.php");
    $users["Edit Booth"]="javascript:voipedit(\'2\')";
  }

  $users["Create Account"]=openpage("users","mkuser.php");
  $users["Edit Account"]="javascript:voipedit(\'1\')";
  $users["DDI Assign"]=openpage("users","assignddi.php");
  $users["DDI Map"]=openpage("users","getcli.php");

  $users["Add Reseller"]="javascript:addagent(\'t\')";
  $users["Add Operator"]="javascript:addagent(\'f\')";
  $users["Edit Reseller/Operator"]=openpage("users","editagent.php");

  $virtual["Create Company"]=openpage("virtual","mkvirt.php");
  $virtual["Edit Company"]=openpage("virtual","getvirt.php");
  $virtual["Credit Pools"]=openpage("virtual","editpool.php");
  $virtual["Manage Nodes"]=openpage("virtual","editsite.php");
  $main['Virtual PBX']="include:virtual";
 
  $reports["Reseller Report"]=openpage("reports","mendrep.php");
//  $reports["Detailed Report"]=openpage("reports","getbill.php");
//  $reports["Users Report"]=openpage("reports","getusrreport.php");
  $reports["Daily Report"]=openpage("reports","getdlyrep.php");
  $reports["Account Registrations"]="javascript:opencsexten(\'SIP\')";

  $main["Reports"]="include:reports";


  array_push($menu,"users");
  array_push($menu,"virtual");
  array_push($menu,"credit");
  array_push($menu,"reports");
}

if ($_SESSION['resellerid'] == "0") {
  $credit["Add Credit"]=openpage("credit","acredit.php");
  $users["Add Realm"]=openpage("users","addrealm.php");
}

$main[_("Logoff")]="javascript:vboxlogoff()";
array_push($menu,"main");

for($mcnt=0;$mcnt < count($menu);$mcnt++) {
  $subout[$menu[$mcnt]]="";
  while(list($item,$action)=each($$menu[$mcnt])) {
    if (substr($action,0,7) == "include") {
      $include=substr($action,8);
      if ($include == "login") {
        $include2="apps";
      } else {
	$include2=$include;
      }
      $subout[$menu[$mcnt]].="['" . $item . "', 'javascript:openpage(\'\',\'" . $include2 . "\')', null,\n\t" . $subout[$include] . "\n\t],\n\t";
    } else {
      $subout[$menu[$mcnt]].="\t['" . $item . "', '" . $action . "'],\n\t"; 
    }
  }
  $subout[$menu[$mcnt]]=substr($subout[$menu[$mcnt]],0,-3);
}

print "var menu_items_list = new Array();\n";
print "var menu_list=new Array();\n";   
print "var activemenu=''\n";
print "var activeuser=''\n\n";
for($mcnt=0;$mcnt < count($menu);$mcnt++) {
  print "menu_items_list['" . $menu[$mcnt] . "_menu']=[\n" . $subout[$menu[$mcnt]] . "];\n\n";
  if ($menu[$mcnt] == "main") {
    print "menu_list['main_menu']=new menu (menu_items_list['main_menu'],menu_horiz);\n\n";
  }
}
%>
