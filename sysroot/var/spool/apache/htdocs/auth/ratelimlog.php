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
if (!isset($_SESSION['auth'])) {
  exit;
}
?>
<CENTER>
<FORM METHOD=POST NAME=ratelim onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=classi VALUE="<?php print $euser;?>">
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=5><?php print _("Last 100 Rate Limiter Events");?></TH></TR>
<?php
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }
  if ($ADMIN_USER == "admin") {
    $col=0;
    $bcol[0]=" CLASS=list-color2";
    $bcol[1]=" CLASS=list-color1";
    $col++;
    $fp=popen("grep -E \"kernel: RATELIM\" /var/log/debug |tail -100","r");

    $outa=array();

    $chunk=8192;
    while (!feof($fp)) {
      $output=fgets($fp, $chunk);
      ereg("^([A-Za-z]+ [ 0-9][0-9] [0-9:]+).*kernel: RATELIM (.*)",$output,$data);
      if ($output != "") {
        $tmdat=split(" ",$data[2]);
        $outd=array();
        for($i=0;$i < count($tmdat);$i++) {
           list($key,$val)=split("=",$tmdat[$i]);
           if ($val != "") {
             $outd[$key]=$val;
           }
        }
        $dir=(($outd['IN'] != "") && ($outd['OUT'] != ""))?"FWD (" . $outd['IN'] . "->" . $outd['OUT'] . ")":
              ($outd['IN'] != "")?"IN (" . $outd['IN'] . ")":"OUT (" . $outd['OUT'] . ")";
//        print "<PRE>" . print_r($outd,TRUE) . "</PRE>";
        $outline=$dir . "</TD><TD>" . $outd['PROTO'] . "</TD><TD>" . $outd['SRC'] . ":" . $outd['SPT'] . "</TD><TD>" . $outd['DST'] . ":" . $outd['DPT'];
        array_push($outa,$data[1] . "</TD><TD>" . $outline);
      }
    }
    fclose($fp);

    for($cnt=0;$cnt < count($outa);) {
      $outp=array_pop($outa);    
      print "<TR" . $bcol[$col % 2] . "><TD ALIGN=LEFT>" . $outp . "</TD></TR>";
      $col++;
    }
   
    print "<TR" . $bcol[$col % 2] . "><TH COLSPAN=5><INPUT TYPE=SUBMIT VALUE=Refresh></TH></TR>";
  } else {
    print "<TR CLASS=list-color1><TH CLASS=heading-body2>Administrator Access Required</TH></TR>";
  }
?>
</FORM>
</TABLE>
