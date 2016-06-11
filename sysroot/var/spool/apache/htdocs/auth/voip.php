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
  include "../ldap/auth.inc";
  include "../cdr/auth.inc";
  if (!isset($euser)) {
    $euser=$PHP_AUTH_USER;
  }
?>
<html>
<head>
<title>Voice Over IP Configuration</title>
<base target="_self">
<link rel="stylesheet" type="text/css" href="/style.php">
</head>
<body>

<FORM METHOD=POST>
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2>
<?php
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }
  if ($ADMIN_USER == "admin") {
    print "Editing ";
  } else {
    print "Viewing ";
  } 
?>
Voice Over IP Settings</TH></TR>
<?php

  $iarr=array("cn","mail","uidnumber","dn");
  $info=strtolower($info);

  if ($type == "pdc") {
    $sr=ldap_search($ds,"ou=Idmap","(&(objectClass=officePerson)(uid=$euser))",$iarr);
  } else {
    $sr=ldap_search($ds,"ou=Users","(&(objectClass=posixAccount)(uid=$euser))",$iarr);
  }
 
  $iinfo = ldap_get_entries($ds, $sr);

  $dn=$iinfo[0]["dn"];
  $uidnum=$iinfo[0]['uidnumber'][0];

  if (isset($modrec)) {
     if (($pass1 == $pass2 ) && ($name != "") && ($pass1 != "")) {
       $passw=$pass1;

       $curset=pg_query("SELECT DISTINCT unix_id FROM users WHERE mailbox='" . $name . "'");
       $pgsqldat=pg_fetch_array($curset,0);

       $isinuse=$pgsqldat[0];

       if ($isinuse != "") {
         $sr=ldap_search($ds,"","(&(objectclass=officeperson)(uidNumber=$isinuse))");
         if (ldap_count_entries($ds,$sr) <= 0) {
           $isinuse="";
         }
       }
       if (($isinuse == "") || ($isinuse == $uidnum)) {
         $fnames=explode(" ",$fullname);
         if (count($fnames) <= 1) {
           $fullname .=" ZZZ";
         }
         pg_query("DELETE FROM users WHERE name = '$name'");
         pg_query("INSERT INTO users (name,secret,username,fromuser,mailbox,callgroup,pickupgroup,password,fullname,email,unix_id,h323permit) VALUES ('$name','$passw','$name','$name','$name','$cgroup','$pgroup','$passw','$fullname','$email','$uidnum','$sigip')");
       } else {
         ?>
         <SCRIPT>
           alert("Not Added/Modified As The Extension Is Already Assigned!");
         </SCRIPT>
         <?php
         $passw="";
         $name="";
       }
     } else {
       ?>
       <SCRIPT>
         alert("Not Added/Modified As There Was Data Not Supplied!");
       </SCRIPT>
       <?php
       $passw="";
     }
  } else if (isset($modpw)) {
     if (($pass1 == $pass2 ) && ($pass1 != "")) {
       $passw=$pass1;
       $curset=pg_query("SELECT DISTINCT mailbox,callgroup,pickupgroup FROM users WHERE unix_id=" . $iinfo[0]['uidnumber'][0]);
       $pgsqldat=pg_fetch_array($curset,0);
       $name=$pgsqldat[0];
       $cgroup=$pgsqldat[2];
       $pgroup=$pgsqldat[2];
       pg_query("UPDATE users set secret='$passw',password='$passw',h323permit='$sigip' WHERE name = '$name'");
     } else {
       ?>
       <SCRIPT>
         alert("Not Added/Modified As There Was Data Not Supplied!");
       </SCRIPT>
       <?php
       $passw="";
     }
   } else if (isset($delrec)){
     pg_query("DELETE FROM users WHERE name = '$name'");
     $passw="";
     $name="";
   } else {
     $curset=pg_query("SELECT DISTINCT mailbox,password,callgroup,pickupgroup,h323permit FROM users WHERE unix_id=" . $iinfo[0]['uidnumber'][0]);
     $pgsqldat=pg_fetch_array($curset,0);
     $name=$pgsqldat[0];
     $passw=$pgsqldat[1];
     $cgroup=$pgsqldat[2];
     $pgroup=$pgsqldat[3];
     $sigip=$pgsqldat[4];
   }
  
  $bcolor[1]=" CLASS=list-color2";
  $bcolor[0]=" CLASS=list-color1";

  if ($ADMIN_USER == "admin") {
?>
    <INPUT TYPE=HIDDEN NAME=fullname VALUE="<?php print $iinfo[0]['cn'][0];?>">
    <INPUT TYPE=HIDDEN NAME=email VALUE="<?php print $iinfo[0]['mail'][0];?>">
    <TR <?php print $bcolor[0];?>><TD>Extension Number</TD>
      <TD><INPUT TYPE=TEXT NAME=name VALUE="<?php print $name;?>"></TD></TR>
    <TR <?php print $bcolor[1];?>><TD>SIP Caller Group</TD>
      <TD><INPUT TYPE=TEXT NAME=cgroup VALUE="<?php print $cgroup;?>"></TD></TR>
    <TR <?php print $bcolor[0];?>><TD>SIP Pickup Group</TD>
      <TD><INPUT TYPE=TEXT NAME=pgroup VALUE="<?php print $pgroup;?>"></TD></TR>
    <TR <?php print $bcolor[1];?>><TD>Password</TD>
      <TD><INPUT TYPE=PASSWORD NAME=pass1 VALUE="<?php print $passw;?>"></TD></TR>
    <TR <?php print $bcolor[0];?>><TD>Password</TD>
      <TD><INPUT TYPE=PASSWORD NAME=pass2 VALUE="<?php print $passw;?>"></TD></TR>
    <TR <?php print $bcolor[1];?>><TD>IP Address (For H323 Only)</TD>
      <TD><INPUT TYPE=TEXT NAME=sigip VALUE="<?php print $sigip;?>"></TD></TR>
    <TR <?php print $bcolor[0];?>><TH COLSPAN=2>  
      <INPUT TYPE=SUBMIT VALUE="Modify" NAME=modrec>
      <INPUT TYPE=SUBMIT VALUE="Delete" NAME=delrec></TH></TR>
    <TR <?php print $bcolor[1];?>><TH COLSPAN=2><H4><FONT SIZE=2>The Password Should Be Reset By Dialing *99/*99*&lt;EXTEN&gt; 
                                            Or From Inside The Voice Mail System As Changing It Here 
                                            Will Not Alter The Line Access Password<BR>The User Will Be 
                                            Prompted For A New Password When The Line Access Password Is 
                                            Requested The First Time.</FONT></H4></TH></TR>
<?php
  } else {
?>
    <TR <?php print $bcolor[0];?>><TD>Extension Number</TD>
      <TD><?php print $name;?></TD></TR>
    <TR <?php print $bcolor[1];?>><TD>SIP Caller Group</TD>
      <TD><?php print $cgroup;?></TD></TR>
    <TR <?php print $bcolor[0];?>><TD>SIP Pickup Group</TD>
      <TD><?php print $pgroup;?></TD></TR>
<?php
  }
?>
</TABLE></FORM>
