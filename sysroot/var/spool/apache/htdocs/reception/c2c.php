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
/*
    if ((strpos($numtocall,"@") === false) && 
        ((strlen($numtocall) == 7) || (strlen($numtoc all) == 10) || 
         (substr($numtocall,0,2) == "09") || (substr($numtocall,0,2) == "00"))) {
      $numtocall="$numtocall@parent";
    }
*/
    $timeout=120;
    $socket = fsockopen("127.0.0.1","5038", $errno, $errstr, $timeout); 
    fputs($socket,"Action: Login\r\n"); 
    fputs($socket,"UserName: admin\r\n"); 
    fputs($socket,"Secret: admin\r\n"); 
    fputs($socket,"Events: off\r\n"); 
    fputs($socket,"\r\n");

    fputs($socket,"Action: Originate\r\n");

    if ((isset($callback)) && ($callback != "")) {
      fputs($socket,"Channel: LOCAL/*" . $PHP_AUTH_USER . "*" . $callback . "@callback/n\r\n");
    } else {
      fputs($socket,"Channel: SIP/" . $PHP_AUTH_USER . "\r\n");
    }
    fputs($socket,"Callerid: $msqldat[2] <$PHP_AUTH_USER>\r\n");
    fputs($socket,"Context: 6\r\n");

    if (strpos($numtocall,"@") === false) {
      fputs($socket,"Exten: $numtocall\r\n");
      fputs($socket,"Priority: 1\r\n");
    } else {
      list($number,$domain)=split("@",$numtocall);
      fputs($socket,"Application: Macro\r\n");
      fputs($socket,"Data: callout|" . $number . "|SIP/" . $number . "@" . $domain . "|" . $PHP_AUTH_USER . "|0|10|6)\r\n");
    }

    fputs($socket,"\r\n");

    fputs($socket,"Action: Logoff\r\n"); 
    fputs($socket,"\r\n");
    while (!feof($socket)) { 
      $wrets .= fread($socket, 8192); 
    }
 //   print $wrets; 

    fclose($socket);
?>
    <SCRIPT>
      window.close();
    </SCRIPT>
<?php
  } else {?>
    <link rel="stylesheet" type="text/css" href="/style.php?style=<?php print $style;?>">
    <script language="JavaScript" src="/hints.js" type="text/javascript"></script>
    <script language="JavaScript" src="/hints_cfg.php?disppage=reception%2Fc2c.php" type="text/javascript"></script>
    <CENTER>
    <FORM METHOD=POST>
    <TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=90%>
      <TR CLASS=list-color2>
        <TH COLSPAN=2 ALIGN=CENTER CLASS=heading-body><?php print _("Web Quick Dial");?></TH>
      </TR><TR  CLASS=list-color1>
        <TD ALIGN=LEFT onmouseover="myHint.show('QC0')" onmouseout="myHint.hide()"><?php print _("Number/Telemail To Dial");?></TD>
        <TD><INPUT TYPE=TEXT NAME=numtocall></TD>
      </TR><TR  CLASS=list-color2>
        <TD ALIGN=LEFT onmouseover="myHint.show('QC1')" onmouseout="myHint.hide()"><?php print _("Call Me Back On (optional)");?></TD>
        <TD><INPUT TYPE=TEXT NAME=callback></TD>
      </TR><TR CLASS=list-color1>
        <TD COLSPAN=2 ALIGN=CENTER><INPUT TYPE=SUBMIT></TD>
      </TR>
    </TABLE>
    </FORM>
    <?php
  }
?>
