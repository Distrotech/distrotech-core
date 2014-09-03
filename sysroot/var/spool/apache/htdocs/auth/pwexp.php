<%
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
  if ((!isset($_SESSION['classi'])) || ($_SESSION['classi'] == "")) {
    $euser=$PHP_AUTH_USER;
  } else {
    $euser=$_SESSION['classi'];
  }

  $disc=array("Password Can Change After","Password Last Set","Password Expires<BR><FONT SIZE=1>Date And Time When File Server Login Expires</FONT>");

  $sr=ldap_search($ds,"ou=Admin","(&(objectclass=groupofnames)(member=$ldn)(cn=Admin Access))");
  if ((ldap_count_entries($ds,$sr) == 1) || ($PHP_AUTH_USER == "admin")) {
    $ADMIN_USER="admin";
  } else {
    $ADMIN_USER="pleb";
  }

  $months=array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

  $iarr=array("sambapwdcanchange","sambapwdlastset","sambapwdmustchange");
  $info=strtolower($info);

  if (isset($modrec)) {
    $etime=mktime($pwhour,$pwmin,$pwsec,$pwmonth,$pwday,$pwyear);
    $minfo["sambapwdmustchange"]=$etime;
/*
    $etime=time();
    $minfo["sambapwdcanchange"]=$etime;
    $minfo["sambapwdlastset"]=$etime;
*/
    ldap_modify($ds,$dn,$minfo);
  }

  $sr=ldap_search($ds,"","(&(objectClass=posixAccount)(uid=$euser))",$iarr);
  $iinfo = ldap_get_entries($ds, $sr);
  $dn=$iinfo[0]['dn'];

%>

<FORM METHOD=POST NAME=pwexpform onsubmit="ajaxsubmit(this.name);return false">
<INPUT TYPE=HIDDEN NAME=classi VALUE="<%print $euser;%>">
<INPUT TYPE=HIDDEN NAME=dn VALUE="<%print $dn;%>">
<CENTER>
<TABLE WIDTH=90% cellspacing="0" cellpadding="0">
<TR CLASS=list-color2><TH COLSPAN=2 CLASS=heading-body>
<%
  if ($ADMIN_USER == "admin") {
    print "Editing ";
  } else {
    print "Viewing ";
  } 
%>
Pasword Validity</TH></TR>
<%
  for ($i=0; $i <= 2; $i++) {
    $rem=$i % 2;
    if ($rem == 1) {
      $bcolor=" CLASS=list-color2";
    } else {
      $bcolor=" CLASS=list-color1";
    }
    $attr=$iarr[$i];
    if ($iinfo[0][$attr][0] == "") {
      $iinfo[0][$attr][0]="0";
    }
%>
    <TR<%print $bcolor;%>>
      <TD onmouseover="myHint.show('<%print $iarr[$i];%>')" onmouseout="myHint.hide()" WIDTH=75%>
        <% print $disc[$i];%>
      </TD>
      <TD>
<%
        if (($ADMIN_USER == "admin") && ($i > 1)) {
          $cyear=date("Y",$iinfo[0][$attr][0]);
          $cmonth=date("m",$iinfo[0][$attr][0]);
          $cday=date("d",$iinfo[0][$attr][0]);

          $chour=date("H",$iinfo[0][$attr][0]);
          $cmin=date("i",$iinfo[0][$attr][0]);
          $csec=date("s",$iinfo[0][$attr][0]);
%>
          <TABLE WIDTH=90% cellspacing="0" cellpadding="0">
          <TR><TD><SELECT NAME=pwday>
<%
          for($day=1;$day <= 31;$day++) {
            print "<OPTION VALUE=" . $day;
            if ($day == $cday) {
              print " SELECTED";
            }
            print ">" . $day . "\n";
          }
%>
          </SELECT></TD><TD>
          <SELECT NAME=pwmonth>
<%
          for($mth=1;$mth <= 12;$mth++) {
            print "<OPTION VALUE=" . $mth;
            if ($mth == $cmonth) {
              print " SELECTED";
            }
            print ">" . $months[$mth-1] . "\n";
          }
%>
          </SELECT></TD><TD>
          <SELECT NAME=pwyear>
<%
          $maxyear=date("Y",time())+10;
          if ($cyear > $maxyear) {
            $maxyear=$cyear;
          }       
          for($year=date("Y",time());$year <= $maxyear;$year++) {
            print "<OPTION VALUE=" . $year;
            if ($year == $cyear) {
              print " SELECTED";
            }
            print ">" . $year . "\n";
          }
%>
          </SELECT></TD></TR>
          <TR><TD>
          <SELECT NAME=pwhour>
<%
          for($hour=0;$hour <= 23;$hour++) {
            print "<OPTION VALUE=" . $hour;
            if ($hour == $chour) {
              print " SELECTED";
            }
            print ">" . $hour . "\n";
          }
%>
          </SELECT></TD><TD>
          <SELECT NAME=pwmin>
<%
          for($min=0;$min < 60;$min++) {
            print "<OPTION VALUE=" . $min;
            if ($min == $cmin) {
              print " SELECTED";
            }
            print ">" . $min . "\n";
          }
%>
          </SELECT></TD><TD>
          <SELECT NAME=pwsec>
<%
          for($sec=0;$sec < 60;$sec++) {
            print "<OPTION VALUE=" . $sec;
            if ($sec == $csec) {
              print " SELECTED";
            }
            print ">" . $sec . "\n";
          }
%>
          </SELECT></TD>
          </TR></TABLE>
<%
        } else {
          print date("Y-m-d H:i:s",$iinfo[0][$attr][0]);
        }
%>
      </TD></TR>
<%
  }
  $rem=$i % 2;
  if ($rem == 1) {
    $bcol[1]=" CLASS=list-color1";
    $bcol[2]=" CLASS=list-color2";
  } else {
    $bcol[2]=" CLASS=list-color1";
    $bcol[1]=" CLASS=list-color2";
  }
  if ($ADMIN_USER == "admin") {
%>
<TR <%print $bcol[2];%>><TH COLSPAN=2>  
  <INPUT TYPE=SUBMIT VALUE="Modify" NAME=modrec></TH></TR>
<%
  }
%>
</TABLE></FORM>
