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
  include_once "/var/spool/apache/htdocs/ldap/ldapcon.inc";
  $rdn="cn=root";

  if ($ldtype == "user") {
    $abdn="ou=Users,cn=$baseou";
  } else if ($ldtype == "abook") {
    $abdn="ou=Entries,cn=$baseou";
  }

  if ($baseou == "system") {
    $ldtype="user";
    if ($_SESSION['utype'] != "system" ) {
      $abdn="o=" . $_SESSION['utype'] . ",ou=Users";
    } else {
      $abdn="ou=Users";
    }
  }

  if ($baseou == "server") {
    $ldtype="user";
    $abdn="ou=Servers";
  }

  if ($baseou == "mserver") {
    $ldtype="user";
    $abdn="ou=Email";
  }

  if ($baseou == "trust") {
    $ldtype="user";
    $abdn="ou=Trusts";
  }

  if ($baseou == "snom") {
    $ldtype="snom";
    $abdn="ou=Snom";
  }

  if ($baseou == "pdc") {
    $ldtype="pdc";
    $abdn="ou=Idmap";
  }

  if ($baseou != "snom") {
    $descrip["userPassword"]=_("Password");
  }

  if ($baseou == "mserver") {
    $descrip["cn"]=_("Hostname Of Server");
  } else {
    $descrip["cn"]=_("Common Name (Unique Name For Contact)");
  }
  if (($baseou != "snom") && ($baseou != "trust") && ($baseou != "mserver")) {
    $descrip["title"]=_("Title");
    $descrip["givenName"]=_("Firstname");
    $descrip["sn"]=_("Surname");
//    unset($descrip["userPassword"]);
  }
  if (! file_exists("/etc/.networksentry-lite")) {
/*
    $descrip["jpegPhoto"]="Users Photograph (JPEG)";
*/
  
    if (($baseou != "snom") && ($baseou != "trust") && ($baseou != "mserver")) {
      $descrip["mail"]=_("Email address");
      $descrip["initials"]=_("Initials");
      $descrip["preferredLanguage"]=_("Prefered Language");
      $label["telephoneNumber"]=_("Contact Details");
      $descrip["telephoneNumber"]=_("B Tel");
      $descrip["facsimileTelephoneNumber"]=_("B Fax");
      $descrip["homePhone"]=_("H Tel");
      $descrip["otherFacsimiletelephoneNumber"]=_("H Fax");
      $descrip["mobile"]=_("Cell");
      $descrip["pager"]=_("Pager");
      $descrip["IPPhone"]=_("IP Phone");
      $descrip["conferenceInformation"]=_("Netmeeting Info");
      $label["comment"]=_("Additional Information");
      $descrip["comment"]=_("Notes");
      $label["postalAddress"]=_("Address Information");
      $descrip["postalAddress"]=_("B Postal Address");
      $descrip["postalCode"]=_("B Postal Code");
      $descrip["l"]=_("City");
      $descrip["st"]=_("Region (Province/State)");
      if ( ! file_exists("/etc/.networksentry-lite")) {
        $descrip["c"]=_("ISO Country Code (IE ZA)");
      }
      $descrip["homePostalAddress"]=_("Home Address");
//      $descrip["sambaSID"]=_("SID");
    } else if (($baseou != "trust") && ($baseou != "mserver")){
      $descrip["telephoneNumber"]=_("Telephone Number");
    } else if ($baseou == "trust") {
      $descrip["uid"]=_("UID");
      $descrip["sambasid"]=_("SID");
    } else if ($baseou == "mserver") {
      $descrip["cn"]=_("Hostname");
      $descrip["uid"]=_("Login");
      $descrip["ipHostNumber"]=_("IP Address");
      $descrip["l"]=_("Location");
      $descrip["description"]=_("Description");
    }
    if (($baseou != "snom") && ($baseou != "trust") && ($baseou != "mserver")) {
      $label["o"]=_("Work Information");
      $descrip["o"]=_("Organization");
      $descrip["ou"]=_("Division");
      $descrip["physicalDeliveryOfficeName"]=_("Office Name/Number");
      $descrip["URL"]=_("Bussiness Web Site");
    }
  }

  $atrib=array();
  $darr=$descrip;

  $reqat["abook"]=array("cn","sn");
  $reqat["snom"]=array("cn");

  if ($baseou != "") {
    $reqat["user"]=array("sn","cn","givenName","mail");
  } else {
    $reqat["user"]=array("uid");
  }

  $dnattr["abook"]=array();
  if ($baseou != "") {
    $dnattr["user"]=array("cn");
  } else {
    $dnattr["user"]=array("uid");
  }
  $b64["postalAddress"]=true;
  $b64["homePostalAddress"]=true;
  $b64["comment"]=true;


  $iline["mailLocalAddress"]=true;
  $iline["hostedSite"]=true;
  $iline["hostedFPSite"]=true;
  $mline["outOfOfficeMSG"]=true;

  $sline["facsimileTelephoneNumber"]=true;

  $cert["userCertificate;binary"]=true;
  $cert["userPKCS12"]=true;
  $cert["userSMIMECertificate"]=true;

  $certext["userCertificate;binary"]=".crt";
  $certext["userPKCS12"]=".p12";
  $certext["userSMIMECertificate"]=".p7b";


  $pass["certificateGenerate"]=true;

  $bfile["jpegPhoto"]=true;

  $cbox["squidProxyAccess"]=true;
  $cbox["pkcs7update"]=true;
  $cbox["smbServerAccess"]=true;
  $cbox["dialupAccess"]=true;
  $cbox["outOfOfficeActive"]=true;
  $cbox["exchangeServerAccess"]=true;
  $cbox["accountSuspended"]=true;
//  $cbox["certificateGenerate"]=true;


  $certreqat["c"]=true;
  $certreqat["st"]=true;
  $certreqat["l"]=true;
  $certreqat["o"]=true;
  $certreqat["ou"]=true;
  $certreqat["cn"]=true;
  $certreqat["mail"]=true;
  $certreqat["ipHostNumber"]=true;
  $certreqat["description"]=true;

  $attrhide["abook"]=array("userPassword","description","ipHostNumber","jpegPhoto",
                           "userPKCS12","userCertificate;binary","userSMIMECertificate");

  $attrhide["pdc"]=array("userPassword","uid","accountSuspended");

  if ($baseou != "") {
    $attrhide["user"]=array("c");
  } else {
    $attrhide["user"]=array("userPassword","uid","pwdLastSet","logonTime","logoffTime","kickoffTime",
                            "pwdCanChange","pwdMustChange","lmPassword","ntPassword","acctFlags","primaryGroupID","rid",
                            "gidNumber","uidNumber","homeDirectory","sambaSID");
    $attrhide["system"]=array("uid","pwdLastSet","logonTime","logoffTime","kickoffTime",
                            "pwdCanChange","pwdMustChange","lmPassword","ntPassword","acctFlags","primaryGroupID","rid",
                            "gidNumber","uidNumber","homeDirectory","sambaSID");
  }
  $atrib=array();
  while(list($idx)=each($darr)) {
    array_push($atrib,$idx);
  }
?>
