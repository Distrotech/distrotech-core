<?php
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

include "/var/spool/apache/htdocs/ccadmin/auth.inc";

function openpage($menu,$page) {
  return "javascript:openpage(\'" . $page . "\',\'" . $menu . "\')";
}

/*
 * Admin Menu
 */
if ($_SESSION['auser'] > 1) {
  $admin[_("Campaign Admin")]=openpage("admin","ccadmin/cadmin.php");
}
$admin[_("Allocate Admin")]=openpage("admin","ccadmin/cauth.php");

/*
 * List Menu
 */
if ($_SESSION['auser'] >= 1) {
  $clist[_("Script")]=openpage("clist","ccadmin/ladmin.php");  
  $clist[_("Input Data Format")]=openpage("clist","ccadmin/linput.php");  
  $clist[_("Field Names")]=openpage("clist","ccadmin/colname.php");  
  $clist[_("Status Options")]=openpage("clist","ccadmin/status.php");  
  $clist[_("Default Fields")]=openpage("clist","ccadmin/ldefault.php");  
  $clist[_("Add Entries (CSV)")]=openpage("clist","ccadmin/leads.php");  
}

/*
 * Agent Menu
 */
if ($_SESSION['auser'] >= 1) {
  $agent[_("Agent Administration")]=openpage("agent","ccadmin/aadmin.php");  
//  $agent[_("Edit Agents")]=openpage("agent","");  
  $agent[_("Test Report")]=openpage("agent","ccadmin/report.php");  
}


$main[_("Campaign Admin")]="include:admin";
$main[_("List Admin")]="include:clist";
$main[_("Agent Admin")]="include:agent";

$main[_("Logoff")]="javascript:vboxlogoff()";

$menu=array("admin","clist","agent","main");

for($mcnt=0;$mcnt < count($menu);$mcnt++) {
  $subout[$menu[$mcnt]]="";
  while(list($item,$action)=each(${$menu[$mcnt]})) {
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
?>
