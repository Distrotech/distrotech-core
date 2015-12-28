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

$sessid=session_id();
if ($sessid == "") {
  if (!isset($_GET['sesname'])) {
    $_GET['sesname']="server_admin";
  }
  ob_start('ob_gzhandler');
  include "/var/spool/apache/htdocs/session.inc";
  session_name($_GET['sesname']);
  session_set_cookie_params(28800);
  session_start();
}

if (file_exists("/var/spool/apache/htdocs/images/" . $SERVER_NAME . "/style.css")) {
  include "/var/spool/apache/htdocs/images/" . $SERVER_NAME . "/style.css";
  exit;
}
if (file_exists("/var/spool/apache/htdocs/images/" . $owner . "/style.css")) {
  include "/var/spool/apache/htdocs/images/" . $owner . "/style.css";
  exit;
}

if ((file_exists("/var/spool/apache/htdocs/" . $_SESSION['style'] . "/style.css")) && ($_SESSION['style'] != "")) {
  include $_SESSION['style'] . "/style.css";
} else {
  include "style.css";
}
if ($_SESSION['print'] == "1") {
  $fontcolor="black";
  $rowcol1="white";
  $rowcol2="white";
  $bgimg="";
  $bgcolor="white";
  $borderw="1";
} else {
  $borderw="0";
}
?>
/* background color or image*/
body.popup {
	margin: 0px 0px 0px 0px;
<?php if ($_SESSION['print'] != "1") {?>
	overflow: hidden;
<?php }?>
}
body {
	margin: 0px 0px 0px 0px;
<?php if ($_SESSION['print'] != "1") {?>
	overflow: hidden;
<?php
}
if ($bgimg != "") {?>
	background-image:  url("<?php print $bgimg;?>");
	background-attachment:fixed;
<?php } else {?>
	background-color: <?php print $bgcolor;?>;
<?php }?>
	color: #000000;
}
/* color and appearance of normal cells */
td {
	color: <?php print $fontcolor;?>;
	font-weight:normal;
	font-size:12;
	font-family: Arial, Helvetica, sans-serif;
	text-decoration: none;
	border: solid black <?php print $borderw;?>px;
}
/* color and appearance of normal headings cells */
th {
	color: <?php print $fontcolor;?>;
	font-weight:bold;
	font-size:14;
	font-family: Arial, Helvetica, sans-serif;
	text-decoration: none;
	vertical-align: middle;
	border: solid black <?php print $borderw;?>px;
}
.table-cell1 {
	font-size: 12;
	font-weight: normal;
}
/* light color for rows */
.list-color1 {
	background-color: <?php print $rowcol1;?>;
}
/* dark color for rows */
.list-color2 {
	background-color: <?php print $rowcol2;?>;
}
.option-green {
	border: solid black 1px;
	color: #FFFFFF;
	background-color: green;
}
.option-red {
	border: solid black 1px;
	color: #FFFFFF;
	background-color: #d33831;
}
.option-orange {
	border: solid black 1px;
	color: #FFFFFF;
	background-color: #ffff00;
}
/* menu non highlighted options */
.menu-color1 {
	color: <?php print $menufg1;?>;
	background-color: <?php print $menubg1;?>;
	font-weight:normal;
	font-size:10;
	font-family:arial;
	text-decoration: none;
	padding-left:5;
	padding-right:5;
	vertical-align:middle;
	border: solid black 0px;
}
/* menu highlighted options */
.menu-color2 {
	color: <?php print $menufg2;?>;
	background-color: <?php print $menubg2;?>;
	font-weight:normal;
	font-family:arial;
	font-size: 10;
	text-decoration:none;
	padding-left:5;
	padding-right:5;
	vertical-align:middle;
	border: solid black 0px;
}
.hintsClass {
	text-align: center;
	font-family: Verdana, Arial, Helvetica;
}
/* popup help window settings */
.hint {
	border-style: solid;
	border-color: black;
	background: white;
	background-color: #FFFF99;
	width:350px;
	margin:0px;
	padding:0px;
	border-width: 2px;
}
/* main headings on table */
th.heading-body {
	font-size: 16;
	font-style:italic;
}
/* sub headings/bold cells on table */
th.heading-body2 {
	font-size: 12;
	font-weight: bold;
}
td.heading-body2 {
	font-size: 12;
	font-weight: bold;
}
div.heading-body {
	font-size: 16;
	font-style:italic;
}
/* color for links on page */
a:link {
	color: <?php print $fontcolor;?>;
}
a:visited {
	color: <?php print $fontcolor;?>;
}
a:active {
	text-decoration: none;
	color: <?php print $fontcolor;?>;
}
a:hover {
	text-decoration: none;
	font-weight: bold;
	color: <?php print $fontcolor;?>;
}
a.red:link {
	color: #d33831;
}
a.red:visited {
	color: #d33831;
}
a.red:active {
	text-decoration: none;
	color: #d33831;
}
a:.redhover {
	text-decoration: none;
	font-weight: bold;
	color: #d33831;
}
.heading-body a:active {
	text-decoration: none;
}
.heading-body a:hover {
	text-decoration: none;
	font-weight: lighter;
}
.heading-body2 a:active {
	text-decoration: none;
}
.heading-body2 a:hover {
	text-decoration: none;
	font-weight: lighter;
}
.table-cell1 a:active {
	text-decoration: none;
}
.table-cell1 a:hover {
	text-decoration: none;
	font-weight: bold;
}
/* various layout cells */
td.heading1 {
	width:250px;
	height:150;
	vertical-align:middle;
}
td.heading2 {
	height:150;
	vertical-align:middle;
}
tr.heading {
	height:150;
	vertical-align:middle;
}
td.side-menu {
	border: solid black 0px;
	width: 165;
}
td.main-body {
	padding-top:10;
	vertical-align:middle;
	border: solid black 0px;
}
td.logo {
	padding-right:25px;
	width:100%;
	height:25px;
	vertical-align:bottom;
}
table.heading {
	padding:0;
}
table.userinfo {
	width:90%;
	padding-top:0;
}
h1 {
	color: $fontcolor;
	font-weight:bold;
	font-size: 32;
	font-family:arial;
	text-decoration: none;
	border: solid black 0px;
}
div.main-scroll {
	position: relative;
	left: 130px;
	top: 20px;
	width: 100%;
	height: 100%;
<?php if ($_SESSION['print'] != "1") {?>
	overflow: auto;
<?php }?>
	padding-top: 0px;
	padding-bottom: 0px;
	padding-left: 0px;
	padding-right: 0px;
	border: solid black 0px;<?php if ($mainbgcol != "") {print "\t\nbackground-color: " . $mainbgcol . ";\n";}?>
	text-align: left;
}
div.logo {
	position: relative;
	left: 130px;
	top: 30px;
	width: 100%;
	text-align: right;
	padding-right: 20px;
	border: solid black 0px;<?php if ($mainbgcol != "") {print "\t\nbackground-color: " . $mainbgcol . ";\n";}?>
}
div.logo-left {
	position: relative;
	left: 0px;
	top: 0px;
	width: 130px;
	height: 100%;
	text-align: right;
	padding-right: 0px;
	border: solid black 0px;<?php if ($smenubg != "") {print "\t\nbackground-color: " . $smenubg . ";\n";}?>
}
div.side-bar {
	position: absolute;
	top: 0px;
	left: 0px;
	width: 130px;
	height: 100%;
	padding-top: 0px;
	padding-bottom: 0px;
	padding-left: 0px;
	padding-right: 0px;
	<?php if ($smenubgimg != "") {print "background-image: url(" . $smenubgimg . ");\n";} else if ($smenubg != "") {print "background-color: " . $smenubg . ";\n";}?>
	border: solid black 0px;
}
div.menu-bar {
	position: relative;
	top: 0px;
	left: 0px;
        height: 20px;
	width: 100%;
	<?php if ($menubgimg != "") {print "background-image: url(" . $menubgimg . ");\n";} else if ($menubg1 != "") {print "background-color: " . $menubg1 . ";\n";}?>
	border: solid black 0px;
	z-index: 1;
}
div.head-logo {
	position: relative;
	top: 0px;
	left: 0px;
	background-color: rgb(51,59,59);
	border: solid black 0px;
	text-align: right;
}
div.formpart {
	position: absolute;
	visibility: hidden;
	border: solid black 0px;
	width: 90%;
}
div {
	color: <?php print $fontcolor;?>;
	font-weight:normal;
	font-size:12;
	font-family: Arial, Helvetica, sans-serif;
	text-decoration: none;
	border: solid black <?php print $borderw;?>px;
}
#popUpDiv {
        position:absolute;
        z-index: 9002;
	border: solid black 2px;<?php if ($mainbgcol != "") {print "\t\nbackground-color: " . $mainbgcol . ";\n";} else { print "\n";}?>
        visibility: hidden;
<?php
if ($bgimg != "") {?>
	background-image:  url("<?php print $bgimg;?>");
	background-attachment:fixed;
<?php } else {?>
	background-color: <?php print $bgcolor;?>;
<?php }?>
}
#blanket {
        background-color: #111;
        opacity: 0.65;
        position: absolute;
        z-index: 9001;
        top: 0px;
        left: 0px;
        width: 100%;
        height: 100%;
	visibility: hidden;
}
div.popup {
	position: relative;
	width: 100%;
	height: 100%;
<?php if ($_SESSION['print'] != "1") {?>
	overflow: auto;
<?php }?>
	padding-top: 0px;
	padding-bottom: 0px;
	padding-left: 0px;
	padding-right: 0px;
	border: solid black 0px;<?php if ($mainbgcol != "") {print "\t\nbackground-color: " . $mainbgcol . ";\n";}?>
	text-align: left;
	color: <?php print $fontcolor;?>;
}
div.content {
	margin: 0 auto;
	color: <?php print $fontcolor;?>;
	font-weight:normal;
	font-size:12;
	font-family: Arial, Helvetica, sans-serif;
	text-decoration: none;
	border: solid black <?php print $borderw;?>px;
	width: 90%;
}
div.cell {
	color: <?php print $fontcolor;?>;
	font-weight:normal;
	font-size:12;
	font-family: Arial, Helvetica, sans-serif;
	text-decoration: none;
	border: solid black <?php print $borderw;?>px;
}
div.heading-body {
	color: <?php print $fontcolor;?>;
	font-size: 16;
	font-style:italic;
	font-weight: bold;
	font-family: Arial, Helvetica, sans-serif;
	text-decoration: none;
	vertical-align: middle;
	border: solid black <?php print $borderw;?>px;
	margin: 0 auto;
	text-align: center;
}
div.heading-body2 {
	color: <?php print $fontcolor;?>;
	font-size: 12;
	font-style:bold;
	font-family: Arial, Helvetica, sans-serif;
	text-decoration: none;
	vertical-align: middle;
	border: solid black <?php print $borderw;?>px;
}
.formselect {
	position: static;
	font-size: 12;
	float: left;
	text-decoration: none;
	font-weight: bold;
	color: <?php print $menufg1;?>;
	border: solid black 0px;
	padding-right: 5px;
	padding-left: 5px;
	border: solid black 0px;
	height: 16px;
	background-color: <?php print $menubg1;?>;
	cursor: pointer;
}
.formrow {
	height: 16px;
	vertical-align: bottom;
	background-color: <?php print $menubg1;?>;
}
.formtable {
	border-collapse: collapse;
	width: 100%;
}
<?php
if ($_SESSION['print'] == "1") {
  $_SESSION['print']=0;
}
?>
