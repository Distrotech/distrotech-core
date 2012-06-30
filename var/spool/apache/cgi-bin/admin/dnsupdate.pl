#!/usr/bin/perl

use CGI;
use URI::Escape;
use MIME::Base64;

$query=new CGI;

print $query->header;

CGI::ReadParse(*form_data);

open(CF,"/var/spool/apache/htdocs/ns/config/netsentry.conf");
while(<CF>) {
  chop $_;
  if ((index($_,"DNS Hosted $form_data{'otherdns'}") ne -1) && ($form_data{'otherdns'} ne "")) {
    @words=split(/ /,$_);
    $cfsecret=@words[3];
  }
  if (index($_,"DNS Domain") ne -1) {
    @words=split(/ /,$_);
    $internal=@words[2];
  }
  if (index($_,"DNS DynKey") ne -1) {
    @words=split(/ /,$_);
    $internalkey=@words[2];
  }
  if (index($_,"DNS SmartKey") ne -1) {
    @words=split(/ /,$_);
    $smartkey=@words[2];
  }

  if (index($_,"DNS DynZone") ne -1) {
    @words=split(/ /,$_);
    $smartzone=@words[2];
  }

  if (index($_,"DNS DynServ") ne -1) {
    @words=split(/ /,$_);
    $smartserver=@words[2];
  }

  if (index($_,"DNS DefaultTTL") ne -1) {
    @words=split(/ /,$_);
    $defttl=@words[2];
  }
}

if (($cfsecret ne $form_data{'secret'} )  && (lc($query->auth_type()) ne "basic")){
   print "<CENTER><H1>Access Denied" . $query->auth_type() . "</H1></CENTER>\n";
}

if ($defttl eq "") {
  $defttl="3600";
}

if ($form_data{'domain'} eq "internal") {
  $server="127.0.0.1";
  $cfsecret=$internalkey;
  if ($form_data{'otherdns'} ne "") {
    $domain=$form_data{'otherdns'};
  } else {
    $domain=$internal;
  }
  $domkey=$internal;
} elsif ($form_data{'domain'} eq "external") {
  $server="127.0.0.2"; 
  $domain=$internal;
  $cfsecret=$internalkey;
  $domkey=$domain;
} elsif ($form_data{'domain'} eq "smart") {
  $cfsecret=$smartkey;
  $domain=$smartzone;
  $server="127.0.0.1";
  $domkey=$domain;
} elsif ($form_data{'domain'} eq "reverse") {
  $server="127.0.0.1";
  $cfsecret=$internalkey;
  if (index(lc($form_data{'otherdns'}),"in-addr.arpa") eq -1) {
    @revq=split(/\./,$form_data{'otherdns'});
    $qcnt=0;
    $rdom="";
    while(@revq[$qcnt] ne "") {
      $rdom="@revq[$qcnt].$rdom";
      $qcnt++;
    }
    chop $rdom;
    $form_data{'otherdns'}="$rdom.in-addr.arpa";
  }

} else {
  $domain=$form_data{'otherdns'};
  $server="127.0.0.2"; 
  $domkey=$domain;
}

$dnssec=encode_base64($cfsecret);


if ($form_data{'save'} eq "Update SOA") {
  open(NSU,"|/usr/bin/nsupdate > /dev/null");
  print NSU "server $server\n";
  print NSU "local $server\n";
  if ($form_data{'domain'} eq "reverse") {
    print NSU "zone $form_data{'otherdns'}\n"; 
    print NSU "key $internal $dnssec\n";
    $domain=$form_data{'otherdns'};
  } else {
    print NSU "zone $domain\n"; 
    print NSU "key $domain $dnssec\n";
  }
  print NSU "update add $domain $form_data{'STTL'} SOA $domain $form_data{'SMAIL'}. $form_data{'SSER'} ";
  print NSU "$form_data{'SFRESH'} $form_data{'SRETRY'} $form_data{'SEXP'} $form_data{'SMIN'}\n";
  print NSU "send\n";
  close(NSU);
}

if ($form_data{'save'} eq "Update Zone") {
  open(NSU,"|/usr/bin/nsupdate > /dev/null");

  print NSU "server $server\n";
  print NSU "local $server\n";
  if ($form_data{'domain'} eq "reverse") {
    print NSU "zone $form_data{'otherdns'}\n";
    print NSU "key $internal $dnssec\n";
    $domain=$form_data{'otherdns'};
  } else {
    print NSU "zone $domain\n";
    print NSU "key $domkey $dnssec\n";
  }
  for($cnt=1;$cnt<= $form_data{'rowcount'};$cnt++) {
    $val="delr$cnt";
    if ($form_data{$val} ne "") {
      print NSU uri_unescape($form_data{$val}) . "\n";
    }
  }
  if ($form_data{'value'} ne "") {
    if (($form_data{'type'} eq "sip") || ($form_data{'type'} eq "iax") ||
        ($form_data{'type'} eq "h323") || ($form_data{'type'} eq "tel") ||
        ($form_data{'type'} eq "mailto")) {
      @naptrdat=split(/ /,$form_data{'value'});
      if (@naptrdat == 1) {
        @naptrdat[2]=@naptrdat[0];
        @naptrdat[0]=0;
        @naptrdat[1]=0;
      }

      if (index($form_data{'record'},"09") == 0){
        $form_data{'record'}=substr($form_data{'record'},2);
      } elsif (index($form_data{'record'},"0") == 0){
        $form_data{'record'}="27" . substr($form_data{'record'},1);
      }
      $form_data{'value'}=@naptrdat[0] . " " . @naptrdat[1] . " u " . "E2U+" . $form_data{'type'} . " \"!^.*\$!" . $form_data{'type'} .":" . @naptrdat[2] . "!\" .";
      $newrec="";
      $oldrec=$form_data{'record'};
      print $oldrec[3] . "<BR>\n";
      for($cnt=length($oldrec);$cnt >0;$cnt--) { 
        $newrec.=chop($oldrec) . ".";
      }
      chop($newrec);
      $form_data{'record'}=$newrec;
      $form_data{'type'}="NAPTR";
    } elsif (($form_data{'type'} ne "A") && ($form_data{'type'} ne "AAAA")) {
      $form_data{'value'}="$form_data{'value'}.";
    }
    if ($form_data{'record'} eq "") {
      $form_data{'record'}="$domain.";
    } elsif ($form_data{'record'} eq $domain) {
      $form_data{'record'}="$domain.";
    } else {
      $form_data{'record'} .=".$domain.";
    }
    print NSU "update add $form_data{'record'} $form_data{'ttl'} $form_data{'type'} $form_data{'value'}\n";
  }
  print NSU "send\n";
  close(NSU);
}

if ($form_data{'domain'} eq "reverse") {
  $domain=$form_data{'otherdns'};
}


$data=readpipe("/usr/bin/host -lvt ANY $domain $server");

@dnsdatain=split(/\n/,$data);
foreach $record (@dnsdatain) {
  $record=join("\t",split(/ /,$record));
  @fields=split(/\t/,$record);
  @rdat=();
  foreach $field (@fields) {
    if ($field ne "") {
      push(@rdat,$field);
    }
  }
  if (@rdat[3] eq "NAPTR") {
    @number=split(/\./,substr(@rdat[0],0,rindex(@rdat[0],$domain)-1));
    $sort="";
    for($cnt=@number-1;$cnt >= 0;$cnt--) {
          $sort.=@number[$cnt];
    }
    if (index($sort,"27") == 0){
      $sort="0" . substr($sort,2);
    } else {
      $sort="09". $sort;
    }
  } else {
    $sort=@rdat[0];
  }
  if ((@rdat[2] eq "IN") && (@rdat[3] ne "SOA")) {
    push(@dnsdata,$sort . "\|" . @rdat[0] . "\|" . @rdat[1] . "\|" . @rdat[3] . "\|" . @rdat[4] . "\|" . @rdat[5] . "\|" . @rdat[6] . "\|" . @rdat[7] . "\|" . @rdat[8]);
  } elsif ((@rdat[2] eq "IN") && (@rdat[3] eq "SOA")) {
    $soa=@rdat[1] . "\|" . @rdat[5] . "\|" . @rdat[6] . "\|" . @rdat[7] . "\|" . @rdat[8] . "\|" . @rdat[9] . "\|" . @rdat[10]; 
  }
}

@dnsdata=sort(@dnsdata);

($soattl,$soamail,$soaser,$soafresh,$soaretry,$soaexpire,$soamin)=split(/\|/,$soa);
chop $soamail;
$soaser++;

if (lc($query->auth_type()) ne "basic") {
  $actionurl="/cgi-perl/dnsupdate.pl";
} else {
  $actionurl="/cgi-perl/admin/dnsupdate.pl";
}

print <<__EOH__;
<FORM METHOD=POST NAME=dnsform onsubmit="AJAX.senddata('main-body',this.name,'$actionurl');return false">
<INPUT TYPE=HIDDEN NAME=otherdns VALUE="$form_data{'otherdns'}">
<INPUT TYPE=HIDDEN NAME=secret VALUE="$form_data{'secret'}">
<INPUT TYPE=HIDDEN NAME=domain VALUE="$form_data{'domain'}">
<INPUT TYPE=HIDDEN NAME=authtype VALUE="$form_data{'authtype'}">
<INPUT TYPE=HIDDEN NAME=style VALUE="$form_data{'style'}">
<INPUT TYPE=HIDDEN NAME=showmenu VALUE="$form_data{'showmenu'}">
<CENTER>
<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>
  <TR CLASS=list-color2>
    <TH COLSPAN=5 CLASS=heading-body>
      SOA Record
    </TH>
  </TR>
  <TR CLASS=list-color1>
    <TD COLSPAN=2 onmouseover=myHint.show('DNS3') ONMOUSEOUT=myHint.hide()>
      SOA TTL Value
    <TD COLSPAN=3>
      <INPUT TYPE=TEXT NAME=STTL VALUE="$soattl">
    </TD>
  </TR>
  <TR CLASS=list-color2>
    <TD COLSPAN=2 onmouseover=myHint.show('DNS4') ONMOUSEOUT=myHint.hide()>
      SOA Mail Record
    <TD COLSPAN=3>
      <INPUT TYPE=TEXT NAME=SMAIL VALUE="$soamail">
    </TD>
  </TR>
  <TR CLASS=list-color1>
    <TD COLSPAN=2 onmouseover=myHint.show('DNS5') ONMOUSEOUT=myHint.hide()>
      SOA Serial Number
    <TD COLSPAN=3>
      <INPUT TYPE=TEXT NAME=SSER VALUE="$soaser">
    </TD>
  </TR>
  <TR CLASS=list-color2>
    <TD COLSPAN=2 onmouseover=myHint.show('DNS6') ONMOUSEOUT=myHint.hide()>
      SOA Refresh Time
    <TD COLSPAN=3>
      <INPUT TYPE=TEXT NAME=SFRESH VALUE="$soafresh">
    </TD>
  </TR>
  <TR CLASS=list-color1>
    <TD COLSPAN=2 onmouseover=myHint.show('DNS7') ONMOUSEOUT=myHint.hide()>
      SOA Retry Time
    <TD COLSPAN=3>
      <INPUT TYPE=TEXT NAME=SRETRY VALUE="$soaretry">
    </TD>
  </TR>
  <TR CLASS=list-color2>
    <TD COLSPAN=2 onmouseover=myHint.show('DNS8') ONMOUSEOUT=myHint.hide()>
      SOA Expire Time
    <TD COLSPAN=3>
      <INPUT TYPE=TEXT NAME=SEXP VALUE="$soaexpire">
    </TD>
  </TR>
  <TR CLASS=list-color1>
    <TD COLSPAN=2 onmouseover=myHint.show('DNS9') ONMOUSEOUT=myHint.hide()>
      SOA Minimum
    <TD COLSPAN=3>
      <INPUT TYPE=TEXT NAME=SMIN VALUE="$soamin">
    </TD>
  </TR>
  <TR CLASS=list-color2>
    <TD COLSPAN=5 ALIGN=MIDDLE>
      <INPUT TYPE=SUBMIT onclick=this.name='save' VALUE="Update SOA">
    </TD>
  </TR>
  <TR CLASS=list-color1>
    <TH COLSPAN=5 CLASS=heading-body>
      Zone Records
    </TH>
  </TR>
  <TR CLASS=list-color2>
    <TH CLASS=heading-body2>
      Delete
    </TH>
    <TH CLASS=heading-body2>
      Record
    </TH>
    <TH CLASS=heading-body2>
      TTL
    </TH>
    <TH CLASS=heading-body2>
      Type
    </TH>
    <TH CLASS=heading-body2>
      Value
    </TH>
  </TR>
__EOH__
$rcnt=0;

foreach $record (@dnsdata) {
  @rdat=split(/\|/,$record);
  $sort=shift(@rdat);
  if (($rcnt % 2) eq "0") {
    $rowcol="CLASS=list-color1";
  } else {
    $rowcol="CLASS=list-color2";
  }
  $rcnt++;
  if (@rdat[0] ne "$domain.") {
    @rdat[0]=substr(@rdat[0],0,rindex(@rdat[0],$domain)-1);
    $deldom=@rdat[0]  . "." . $domain . ".";
  } else {
    $deldom=@rdat[0];
  }
  if (@rdat[2] eq "MX") {
    @rdat[3]="@rdat[3] @rdat[4]";
  }
  if (@rdat[2] eq "SRV") {
    @rdat[3]="@rdat[3] @rdat[4] @rdat[5] @rdat[6]";
  }
  if (@rdat[2] eq "NAPTR") {
    @rdat[0]=$sort;
    @rdat[3]="@rdat[3] @rdat[4] @rdat[5] @rdat[6] @rdat[7] .";
  }

  $delcom="<INPUT TYPE=CHECKBOX NAME=delr$rcnt VALUE=\"" . uri_escape("update delete $deldom @rdat[2] @rdat[3]") . "\">";
 
  if (@rdat[2] eq "NAPTR") {
    @naptrdat=split(/ /,@rdat[3]);
    @naptrudat=split(/\!/,@naptrdat[4]);
    @naptrurl=split(/:/,@naptrudat[2]);
    @rdat[2]=uc(@naptrurl[0]);
    @rdat[3]=@naptrdat[0] . " " . @naptrdat[1] . " " .@naptrurl[1];
  }
  print <<__EOH__;
  <TR $rowcol>
    <TD WIDTH=5%>
      $delcom
    </TD>
    <TD WIDTH=35%>
      @rdat[0]
    </TD>
      <TD WIDTH=10%>
        @rdat[1]
      </TD>
    <TD WIDTH=10%>
      $rdat[2]
    </TD>
    <TD WIDTH=40%>
      @rdat[3]
    </TD>
  </TR>
__EOH__
}

if (($rcnt % 2) ne "0") {
  $rowcol="CLASS=list-color2";
} else {
  $rowcol="CLASS=list-color1";
}

print <<__EOH__;
  <TR $rowcol>
    <TD>
      &nbsp;
    </TD>
    <TD onmouseover=myHint.show('DNS10') ONMOUSEOUT=myHint.hide()>
      <INPUT TYPE=TEXT NAME=record>
    </TD>
    <TD onmouseover=myHint.show('DNS11') ONMOUSEOUT=myHint.hide()>
      <INPUT TYPE=TEXT NAME=ttl VALUE=$defttl SIZE=5>
    </TD>
    <TD onmouseover=myHint.show('DNS12') ONMOUSEOUT=myHint.hide()>
      <select NAME=type>";
        <OPTION VALUE=A>A
        <OPTION VALUE=AAAA>AAAA
        <OPTION VALUE=CNAME>CNAME
        <OPTION VALUE=DNAME>DNAME
        <OPTION VALUE=MX>MX (Priority)
        <OPTION VALUE=SRV>SRV (Prio. Wei. Port)
        <OPTION VALUE=NS>NS
        <OPTION VALUE=PTR
__EOH__
  if (index($domain,"in-addr.arpa") ne -1) {
    print " SELECTED";
  }

print <<__EOH__;
>PTR
        <OPTION VALUE=sip>SIP (Prio. Wei.)
        <OPTION VALUE=iax>IAX (Prio. Wei.)
        <OPTION VALUE=h323>H323 (Prio. Wei.)
        <OPTION VALUE=tel>TEL (Prio. Wei.)
        <OPTION VALUE=mailto>MAILTO (Prio. Wei.)
      </SELECT>
    </TD>
    <TD onmouseover=myHint.show('DNS13') ONMOUSEOUT=myHint.hide()>
      <INPUT TYPE=TEXT NAME=value>
    </TD>
  </TR>
__EOH__

$rcnt++;
if (($rcnt % 2) ne "0") {
  $rowcol="CLASS=list-color2";
} else {
  $rowcol="CLASS=list-color1";
}

print <<__EOH__;
  <TR $rowcol>
    <TD COLSPAN=5 ALIGN=MIDDLE>
      <INPUT TYPE=SUBMIT onclick=this.name='save' VALUE="Update Zone">
    </TD>
  </TR>
</TABLE>
<INPUT TYPE=HIDDEN NAME=rowcount VALUE=$rcnt>
</FORM>
__EOH__
