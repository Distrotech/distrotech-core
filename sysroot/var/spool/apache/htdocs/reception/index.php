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
include "auth.inc";
?>
<title>Voice Over IP Server/Firewall Operator Panel (<?php print $msqldat[2] . " [" . $msqldat[3] . "]";?>)</title>
<style>
<!--
html,body {
	margin: 0;
	padding: 0;
	height: 100%;
	width: 100%;
}

-->
</style>
</head>

<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="100%" height="100%" id="operator_panel" align="left">
<param name="allowScriptAccess" value="sameDomain" />
<param name="movie" value="operator_panel.swf?mybutton=<?php print $msqldat[1]?>&<?php if ($msqldat[4] == "1") { print "url=mypage.php&target=crmpopup&"; }?>context=<?php print $msqldat[3]?>" />
<param name="quality" value="high" />
<param name="bgcolor" value="#ffffff" />
<param name="scale" value="exactfit" />
<embed src="operator_panel.swf?mybutton=<?php print $msqldat[1]?>&<?php if ($msqldat[4] == "1") { print "url=mypage.php&target=crmpopup&"; }?>context=<?php print $msqldat[3]?>" quality="high" scale="exactfit" bgcolor="#ffffff" width="100%" height="100%" name="operator_panel" align="left" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>
</body>
</html>
