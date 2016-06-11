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
  if (!isset($euser)) {
    $euser=$PHP_AUTH_USER;
  }
?>
<html>
<head>
<title>e4l Configuration</title>
<base target="_self">
<link rel="stylesheet" type="text/css" href="/style.php">
</head>
<body>
<?php
  include "../ldap/pgauth.inc";
  if (! $db) {
    print "A Database Error Has Occured ...";
    exit();
  }
  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=" . $ldn . ")(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

  $dnarr=array("dn");

  if ($type == "pdc") {
    $sr=ldap_search($ds,"ou=Idmap","(&(objectClass=officePerson)(uid=$euser))",$dnarr);
  } else {
    $sr=ldap_search($ds,"ou=Users","(&(objectClass=officePerson)(uid=$euser))",$dnarr);
  }

  $iinfo = ldap_get_entries($ds, $sr);
  $dn=$iinfo[0]["dn"];


  $utbl=pg_query($db,"SELECT c.relname as \"Table Name\" 
                  FROM  pg_catalog.pg_class c 
                  LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace 
                  WHERE (c.relkind='r' AND n.nspname NOT IN ('pg_catalog','pg_toast'))
                        AND (c.relname = '" . $euser . "_obj' OR c.relname = '" . $euser . "_props');");
  $tblnum=pg_num_rows($utbl);

  if ((isset($ce4l)) && ($ADMIN_USER == "admin")) {
    if ($tblnum > 0) {
      for($ir=0;$ir < $tblnum;$ir++) {
        $row=pg_fetch_row($utbl,$ir);
        for ($if=0;$if < count($row);$if++) {
          pg_query($db,"DROP TABLE " . $row[$if] . ";");
        }
      }
    }

    $sqlfile="../ldap/user.sql";
    $sfd=fopen($sqlfile,"r");
    $usql=fread($sfd,filesize($sqlfile));
    fclose ($sfd);
    $upat=array("'\\$\{user\}'i","'xnow\(\)'i");
    $rpat=array($euser,time());
    $usql=preg_replace($upat,$rpat,$usql);
    $tbl_cre=pg_query($db,$usql);

    if ($tbl_cre) {
      $ldapact["exchangeServerAccess"]="yes";
      $r=ldap_modify($ds,$dn,$ldapact);
    }

    $utbl=pg_query($db,"SELECT c.relname as \"Table Name\" 
                    FROM  pg_catalog.pg_class c 
                    LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace 
                    WHERE (c.relkind='r' AND n.nspname NOT IN ('pg_catalog','pg_toast'))
                          AND (c.relname = '" . $euser . "_obj' OR c.relname = '" . $euser . "_props');");
    $tblnum=pg_num_rows($utbl);
  } else if ((isset($dele4l)) && ($ADMIN_USER == "admin")) {
    if ($tblnum > 0) {
      for($ir=0;$ir < $tblnum;$ir++) {
        $row=pg_fetch_row($utbl,$ir);
        for ($if=0;$if < count($row);$if++) {
          pg_query($db,"DROP TABLE " . $row[$if] . ";");
        }
      }
    }


    $ldapact["exchangeServerAccess"]="active";
//    $ldapact["exchangeServerAccess"]="yes";
    $r=ldap_mod_del($ds,$dn,$ldapact);

    $utbl=pg_query($db,"SELECT c.relname as \"Table Name\" 
                    FROM  pg_catalog.pg_class c 
                    LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace 
                    WHERE (c.relkind='r' AND n.nspname NOT IN ('pg_catalog','pg_toast'))
                          AND (c.relname = '" . $euser . "_obj' OR c.relname = '" . $euser . "_props');");
    $tblnum=pg_num_rows($utbl);
  } else if ((isset($ree4l)) && ($ADMIN_USER == "admin")) {
    pg_query($db,"REINDEX TABLE " . $euser . "_Obj FORCE;");
    pg_query($db,"REINDEX TABLE " . $euser . "_Props FORCE;");
  }

?>

<FORM METHOD=POST>
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2>
<?php
  if ($ADMIN_USER == "admin") {
    print "Editing ";
  } else {
    print "Viewing ";
  }
?>
Exchange 4 Linux Profile</TH></TR>

<TR CLASS=list-color1>
<?php
  if ($ADMIN_USER == "admin") {
    if ($tblnum != 2) {?>
      <TD>Create e4l Profile</TD>
      <TD><INPUT TYPE=SUBMIT NAME=ce4l></TD>
<?php
    } else {
?>
      <TD>Recreate e4l Profile</TD>
      <TD><INPUT TYPE=SUBMIT NAME=ce4l></TD>
<?php
    }
?>
  <TR  CLASS=list-color2>
    <TD>Delete e4l Profile</TD>
    <TD><INPUT TYPE=SUBMIT NAME=dele4l></TD>
  </TR>  
  <TR  CLASS=list-color1>
    <TD>Reindex e4l Profile</TD>
    <TD><INPUT TYPE=SUBMIT NAME=ree4l></TD>
  
  </TR></TABLE></FORM>
<?php
  }
?>
