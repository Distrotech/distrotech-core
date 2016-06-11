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
  if (isset($numtocall)) {
    print "<PRE>";
    $socket = fsockopen("127.0.0.1","5038", $errno, $errstr, $timeout); 
    fputs($socket,"Action: Login\r\n"); 
    fputs($socket,"UserName: admin\r\n"); 
    fputs($socket,"Secret: admin\r\n"); 
    fputs($socket,"Events: off\r\n"); 
    fputs($socket,"\r\n");

    fputs($socket,"Action: Originate\r\n");
    fputs($socket,"Channel: LOCAL/*" . $PHP_AUTH_USER . "0927" . substr($mynum,1) . "@callback/n\r\n");
#    fputs($socket,"Channel: OH323/0927" . substr($mynum,1) . "\r\n");
    fputs($socket,"Application: Agi\r\n");
    fputs($socket,"Data: areskicc.php|$PHP_AUTH_USER|$numtocall\r\n");
    fputs($socket,"Callerid: $PHP_AUTH_USER\r\n");
    fputs($socket,"\r\n");

    fputs($socket,"Action: Logoff\r\n"); 
    fputs($socket,"\r\n");
    while (!feof($socket)) { 
      $wrets.=fread($socket, 8192); 
    }
    fclose($socket);
    print $wrets; 
    ?>
    </PRE>
    <SCRIPT>
      window.close();
    </SCRIPT><?php
  } else {?>
    <link rel="stylesheet" type="text/css" href="/style.php">
    <CENTER>
    <FORM METHOD=POST>
    <TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
      <TR CLASS=list-color2>
        <TH COLSPAN=2 ALIGN=CENTER>Web Quick Dial</TH>
      </TR><TR  CLASS=list-color1>
        <TH ALIGN=LEFT>My Number</TH>
        <TD><INPUT TYPE=TEXT NAME=mynum></TD>
      </TR><TR  CLASS=list-color1>
        <TH ALIGN=LEFT>Number To Dial</TH>
        <TD><INPUT TYPE=TEXT NAME=numtocall></TD>
      </TR><TR CLASS=list-color2>
        <TD COLSPAN=2 ALIGN=CENTER><INPUT TYPE=SUBMIT></TD>
      </TR>
    </TABLE>
    </FORM>
    <?php
  }
?>
