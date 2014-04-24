<%
include "/var/spool/apache/htdocs/auth/auth.inc";

include "csvfunc.inc";

$defcodec[0][0]="g729";
$defcodec[0][1]="gsm";
$defcodec[0][2]="g726";
$defcodec[1][0]="h263p";
$defcodec[1][1]="h263";
$defcodec[1][2]="h261";

$level['Internal']=0;
$level['Local']=1;
$level['Long Distance']=2;
$level['Cellular']=3;
$level['Premium']=4;
$level['International']=5;

$level[0]="Internal";
$level[1]="Local";
$level[2]="Long Distance";
$level[3]="Cellular";
$level[4]="Premium";
$level[5]="International";

$leveld[0]="Internal And Interbranch Calls (No Access To PSTN)";
$leveld[1]="Calls Within Defined Area Code(s)";
$leveld[2]="Calls Outside Of Local Area Code(s) To Fixed Non Premium Lines";
$leveld[3]="Calls To Non Premium Cellular Phones";
$leveld[4]="Calls To Premium Numbers";
$leveld[5]="Calls To International Numbers";

$invert[0]=1;
$invert[1]=0;

$astdbk=array("ALTC","CLI","CFIM","CFBU","CFNA","CFFAX","NOVMAIL","RECORD","TOUT","CDND","Locked","WAIT","FAXMAIL","EFAXD","ACCESS","AUTHACCESS");

$astdbm=array();
$astdbm['ALTC']=3;
$astdbm['CLI']=4;
$astdbm['CFIM']=7;
$astdbm['CFBU']=8;
$astdbm['CFNA']=9;
$astdbm['CFFAX']=10;
$astdbm['NOVMAIL']=11;
$astdbm['RECORD']=12;
$astdbm['TOUT']=13;
$astdbm['CDND']=14;
$astdbm['Locked']=15;
$astdbm['WAIT']=16;
$astdbm['FAXMAIL']=17;
$astdbm['EFAXD']=18;
$astdbm['ACCESS']=21;
$astdbm['AUTHACCESS']=22;

$outstr[1]=1;
$outstr[2]=1;
$outstr[5]=1;
$outstr[6]=1;
$outstr[19]=1;
$outstr[20]=1;

$telout[3]=1;
$telout[4]=1;
$telout[7]=1;
$telout[8]=1;
$telout[9]=1;
$telout[10]=1;

$noyes[11]=1;

$yesno[12]=1;
$yesno[14]=1;
$yesno[15]=1;
$yesno[16]=1;
$yesno[17]=1;
$yesno[18]=1;

$outlevel[21]=1;
$outlevel[22]=1;

$pyesno[4]=1;
$pyesno[5]=1;
$pyesno[6]=1;
$pyesno[11]=1;
$pyesno[13]=1;

$poutstr[1]=1;
$poutstr[2]=1;
$poutstr[3]=1;

$poutstr[7]=1;
$poutstr[8]=1;
$poutstr[9]=1;

$poutcodec[10]=1;

$pnozero[12]=1;
$pnozero[14]=1;
$pnozero[15]=1;

$astpdbk=array("IAXLine","ZAPLine","NOVOIP","ZAPRXGain","ZAPTXGain");
$astpdbm=array();
$astpdbm['IAXLine']=11;
$astpdbm['ZAPLine']=12;
$astpdbm['NOVOIP']=13;
$astpdbm['ZAPRXGain']=14;
$astpdbm['ZAPTXGain']=15;

$usertx=array(0,1,2,5,6,19,20);
$prottx=array(0,1,2,3,4,5,6,7,8,9,10);

$acdbool[11]=1;
$acdbool[14]=1;
$acdstrout[1]=1;
$acdstrout[16]=1;


if (isset($getcsv)) {
   header( "Location: /csv/" . $filetype . ".csv");
}

if (isset($filetype)) {
  header("Content-type: application/ms-excel");
  if ($filetype == "exten") {
    print "Access Options\r\n";
    for($acnt=0;$acnt<=5;$acnt++) {
      print $level[$acnt] . "," . $leveld[$acnt] . "\r\n";
    }
    print "\r\n";
    print "\r\n";
    print "EXTEN,Name,Email,Contact,DDI,PWD,VM PWD,FWD Imm,FWD Busy,FWD No Ans,FWD FAX,V. MAIL,Record,R Time,DND,Lock,C. Wait,Fax2Mail,Fax Detect,Gr,PU Gr,Access,Auth Access\r\n";
    $users=pg_query("SELECT name,fullname,email,secret,password,callgroup,pickupgroup FROM users LEFT OUTER JOIN astdb ON (family='LocalPrefix' AND key=substring(name,1,2)) WHERE value=1 ORDER BY name");
    for($ucnt=0;$ucnt < pg_num_rows($users);$ucnt++) {
      $out=array();
      $tmpout="";
      $uent=pg_fetch_array($users,$ucnt);
      for ($outcnt=0;$outcnt < count($uent);$outcnt++) {
        $out[$usertx[$outcnt]]=$uent[$outcnt];
      }
      for($dbent=0;$dbent < count($astdbk);$dbent++) {
        $ugetastdb=pg_query("SELECT value FROM astdb WHERE key='" . $astdbk[$dbent] . "' AND family='" . $uent[0] . "' LIMIT 1");
        $uastdb=pg_fetch_array($ugetastdb,0);
        $out[$astdbm[$astdbk[$dbent]]]=$uastdb[0];
      }
      for ($ocnt=0;$ocnt < count($out)-1;$ocnt++) {
        if ($noyes[$ocnt]) {
          $out[$ocnt]=booltostr($invert[$out[$ocnt]]);
        } else if ($yesno[$ocnt]) {
          $out[$ocnt]=booltostr($out[$ocnt]);
        } else if ($nozero[$ocnt]) {
          $out[$ocnt]=nozeroout($out[$ocnt]);
        } else if ($outstr[$ocnt]) {
          $out[$ocnt]="\"'" . $out[$ocnt] . "\"";
        } else if ($outlevel[$ocnt]) {
          $out[$ocnt]=$level[$out[$ocnt]];
        } else if ($telout[$ocnt]) {
          $out[$ocnt]=telformat($out[$ocnt]);
          $out[$ocnt]=nozeroout($out[$ocnt]);      
        }
        $tmpout.=$out[$ocnt] . ",";
      }
      print substr($tmpout,0,-1) . "\r\n";
    }
  } else if ($filetype == "elist") {
    print "EXTEN,Name,Email,Contact,Office/Loc.\r\n";
    $users=pg_query("SELECT name,fullname,email,callgroup FROM users LEFT OUTER JOIN astdb ON (family='LocalPrefix' AND key=substring(name,1,2)) WHERE value=1 ORDER BY name");
 
    $eoutstr=array('0','1','1','0','1');
    $etelout=array('0','0','0','1','0');

    for($ucnt=0;$ucnt < pg_num_rows($users);$ucnt++) {
      $out=array();
      $tmpout="";
      $uent=pg_fetch_array($users,$ucnt);
      for ($outcnt=0;$outcnt < count($uent)/2;$outcnt++) {
        $out[$usertx[$outcnt]]=$uent[$outcnt];
      }
      $ugetastdb=pg_query("SELECT astdb.value,loc.value FROM astdb LEFT OUTER JOIN astdb AS loc ON (loc.family=astdb.family AND loc.key='OFFICE') WHERE astdb.key='ALTC' AND astdb.family='" . $uent[0] . "' LIMIT 1");
      $uastdb=pg_fetch_array($ugetastdb,0);
      $out[3]=$uastdb[0];
      $out[4]=$uastdb[1];

      for ($ocnt=0;$ocnt < count($out)-1;$ocnt++) {
        if ($eoutstr[$ocnt]) {
          $out[$ocnt]="\"'" . $out[$ocnt] . "\"";
        } else if ($outlevel[$ocnt]) {
          $out[$ocnt]=$level[$out[$ocnt]];
        } else if ($etelout[$ocnt]) {
          $out[$ocnt]=telformat($out[$ocnt]);
          $out[$ocnt]=nozeroout($out[$ocnt]);      
        }
        $tmpout.=$out[$ocnt] . ",";
      }
      print substr($tmpout,0,-1) . "\r\n";
    }
  } else if ($filetype == "snommac") {
    print "\"Exten\",\"Mac Address\",\"Locked\",\"Registrar\",\"VLAN\",\"Type\",\"Linksys Name\",\"Linksys Profile Server\",\"Linksys STUN Server\"";
    for($fkcnt=1;$fkcnt <= 54;$fkcnt++) {
      if ($fkcnt <= 12) {
        print ",\"F.Key" . $fkcnt . "\"";
      } else {
        $xkey=$fkcnt-12;
        print ",\"X.Key" . $xkey . "\"";
      }
    }
    print "\r\n";
    $maclistq="SELECT name,astdb.value,slock.value,sreg.value,vlan.value,ptype.value,lsysname.value,lsyspro.value,lsysstun.value,lsysvlan.value FROM users
                                LEFT OUTER JOIN astdb ON (astdb.family=name AND astdb.key='SNOMMAC')
                                LEFT OUTER JOIN astdb AS lclpre ON (lclpre.family='LocalPrefix' AND lclpre.key=substring(name,1,2))
                                LEFT OUTER JOIN astdb AS slock ON (slock.family=name AND slock.key = 'SNOMLOCK') 
                                LEFT OUTER JOIN astdb AS sreg ON (sreg.family=name AND sreg.key = 'REGISTRAR') 
                                LEFT OUTER JOIN astdb AS vlan ON (vlan.family=name AND vlan.key = 'VLAN') 
                                LEFT OUTER JOIN astdb AS ptype ON (ptype.family=name AND ptype.key = 'PTYPE') 
                                LEFT OUTER JOIN astdb AS lsysname ON (lsysname.family=astdb.value AND lsysname.key='LINKSYS') 
                                LEFT OUTER JOIN astdb AS lsyspro ON (lsyspro.family=astdb.value AND lsyspro.key='PROFILE') 
                                LEFT OUTER JOIN astdb AS lsysstun ON (lsysstun.family=astdb.value AND lsysstun.key='STUNSRV') 
                                LEFT OUTER JOIN astdb AS lsysvlan ON (lsysvlan.family=astdb.value AND lsysvlan.key='VLAN') 
                              WHERE lclpre.value='1' ORDER BY name";
    $maclist=pg_query($db,$maclistq);
    for($mcnt=0;$mcnt < pg_num_rows($maclist);$mcnt++) {
      $kpkey=array();
      $kpkey[0]="";
      $kpkey[1]="";
      $kpkey[2]="9";
      $kpkey[3]="700";
      $kpkey[4]="701";
      $kpkey[5]="702";
      $kpkey[6]="900";
      $kpkey[7]="901";
      $max=7;
      $macr=pg_fetch_array($maclist,$mcnt);
      if (($macr[1] != "") && (strlen($macr[1]) >= 12)) {
        $macr[1]=substr($macr[1],0,2) . ":" . substr($macr[1],2,2) . ":" . substr($macr[1],4,2) . ":" . substr($macr[1],6,2) . ":" . substr($macr[1],8,2) . ":" . substr($macr[1],10,2);
      } else {
        $macr[1]="";
      }
      if ((! $lsysdone[$macr[1]]) && ($macr[5] == "LINKSYS")) {
        $macr[4]=$macr[9];
        print $macr[0] . ",\"" . $macr[1] . "\",\"" . booltostr($macr[2]) . "\",\"" . $macr[3] . "\",\"" . $macr[4] . "\",\"" . $macr[5] . "\",\"" . $macr[6] . "\",\"" . $macr[7] . "\",\"" . $macr[8] . "\"";
      } else if ($macr[5] != "SNOM") {
        if ($macr[5] == "LINKSYS") {
          $macr[4]=$macr[9];
        }
        print $macr[0] . ",\"" . $macr[1] . "\",\"" . booltostr($macr[2]) . "\",\"" . $macr[3] . "\",\"" . $macr[4] . "\",\"" . $macr[5] . "\"";
      } else {
        print $macr[0] . ",\"" . $macr[1] . "\",\"" . booltostr($macr[2]) . "\",\"" . $macr[3] . "\",\"" . $macr[4] . "\",\"" . $macr[5] . "\",\"\",\"\",\"\"";
      }
      if (($macr[5] == "SNOM") && ($macr[1] != "")) {
        $fkeys=pg_query($db,"SELECT substr(key,5),value from astdb where key ~ '^fkey' and family='" . $macr[0] . "'");
        for($kcnt=0;$kcnt < pg_num_rows($fkeys);$kcnt++) {
          $fkey=pg_fetch_array($fkeys,$kcnt);
          if ($fkey[1] != "1") {
            $kpkey[$fkey[0]]=$fkey[1];
          } else {
            $kpkey[$fkey[0]]="";
          }
          if ($fkey[0] > $max) {
            $max=$fkey[0];
          }
        }
         for($krout=0;$krout <= $max;$krout++) {
          print ",\"" . telformat($kpkey[$krout]) . "\"";
        }
      } else {
        $lsysdone[$macr[1]]=1;
      }
      print "\r\n";
    }
  } else if ($filetype == "snompbook") {
    include "../ldap/auth.inc";
    $sr=ldap_search($ds,"ou=snom","(&(cn=*)(telephonenumber=*))",array("cn","telephonenumber"));
    $info = ldap_get_entries($ds, $sr);
    for($i=0;$i < $info["count"];$i++) {
      $info[$i]["telephonenumber"][0]=telformat($info[$i]["telephonenumber"][0]);
      print "\"" . $info[$i]["cn"][0] . "\",\"" . $info[$i]["telephonenumber"][0] . "\"\r\n";
    }
  } else if ($filetype == "protocol") {
    print "EXTEN,Nat,DTMF,Relaxed Auth.,Reinvite,Forwarding,Keepalive,GK IP Access,GK ID,Recived Prefix,Audio 1,Audio 2,Audio 3,Video 1,Video 2,Video 3,Use IAX,TDM Port,NO VOIP,TDM RX Gain,TDM TX Gain\r\n";
    $users=pg_query("SELECT name,nat,dtmfmode,insecure,canreinvite,cancallforward,qualify,h323permit,h323gkid,h323prefix,allow FROM users LEFT OUTER JOIN astdb ON (family='LocalPrefix' AND key=substring(name,1,2)) WHERE value=1 ORDER BY name");
    for($ucnt=0;$ucnt < pg_num_rows($users);$ucnt++) {
      $out=array();
      $tmpout="";
      $uent=pg_fetch_array($users,$ucnt);
      for ($outcnt=0;$outcnt < count($uent);$outcnt++) {
        $out[$prottx[$outcnt]]=$uent[$outcnt];
      }
      for($dbent=0;$dbent < count($astpdbk);$dbent++) {
        $ugetastdb=pg_query("SELECT value FROM astdb WHERE key='" . $astpdbk[$dbent] . "' AND family='" . $uent[0] . "' LIMIT 1");
        $uastdb=pg_fetch_array($ugetastdb,0);
        $out[$astpdbm[$astpdbk[$dbent]]]=$uastdb[0];
      }
      for ($ocnt=0;$ocnt < count($out)-1;$ocnt++) {
        if ($pnoyes[$ocnt]) {
          $out[$ocnt]=booltostr($invert[$out[$ocnt]]);
        } else if ($pyesno[$ocnt]) {
          $out[$ocnt]=booltostr($out[$ocnt]);
        } else if ($pnozero[$ocnt]) {
          $out[$ocnt]=nozeroout($out[$ocnt]);
        } else if ($poutstr[$ocnt]) {
          $out[$ocnt]="\"" . $out[$ocnt] . "\"";
        } else if ($poutcodec[$ocnt]) {
          $codecs=getcodecs($out[$ocnt]);
          $out[$ocnt]="";
          for($i=0;$i <= 1;$i++) {
            for($j=0;$j<3;$j++) {
              if ($codecs[$i][$j] == "") {
                 $codecs[$i][$j]=$defcodec[$i][$j];
              }
              $out[$ocnt].=nozeroout($codecs[$i][$j]) . ",";
            }
          }
          $out[$ocnt]=substr($out[$ocnt],0,strlen($out[$ocnt])-1);
        }
        $tmpout.=$out[$ocnt] . ",";
      }
      print substr($tmpout,0,-1) . "\r\n";
    }
  } else if ($filetype == "acd") {
    $acdq="SELECT queue_table.name,description,strategy,timeout,wrapuptime,memberdelay,servicelevel,weight,maxlen,retry,announce_frequency,announce_holdtime,
                              announce_round_seconds,autopausedelay,playmusiconhold,users.password,users.email,tout.value,penalty.value,delay.value
                          FROM queue_table 
                            LEFT OUTER JOIN users ON (mailbox=queue_table.name) 
                            LEFT OUTER JOIN astdb AS tout ON (tout.family='Q'||queue_table.name AND tout.key='QTIMEOUT') 
                            LEFT OUTER JOIN astdb AS penalty ON (penalty.family='Q'||queue_table.name AND penalty.key='QAPENALTY') 
                            LEFT OUTER JOIN astdb AS delay ON (delay.family='Q'||queue_table.name AND delay.key='QRDELAY') 
                              WHERE queue_table.name ~ '5[0-9]{2}'";
    $acdqq=pg_query($db,$acdq);
    print "Queue,Description,Scheme,Timeout,Wrapup Time,Agent Delay,Service Level,Weight,Max Hold Calls,Retry,Announce Freq,Announce Holdtime,";
    print "Anounce Round To,Autologout,MOH,VM Password,Email Address,Timeout,Penalty,Ring Delay\r\n";
    for($i=0;$i < pg_num_rows($acdqq);$i++) {
      $acd=pg_fetch_row($acdqq,$i);
      for($j=0 ;$j < count($acd);$j++) {
        if ($acdbool[$j]) {
          $acd[$j]=booltostr($acd[$j]);
        } else if ($acdstrout[$j]) {
          $acd[$j]="\"" . $acd[$j] . "\"";
        }
        print $acd[$j];
        if ($j < count($acd)-1) {
          print ",";
        }
      }
      print "\r\n";
    }
  } else if ($filetype == "ibroute") {
    $ibroute=pg_query($db,"SELECT route.key,route.value,proto.value,rewrite.value FROM astdb AS route 
                    LEFT OUTER JOIN astdb AS proto ON (proto.key=route.key AND proto.family='LocalRouteProto') 
                    LEFT OUTER JOIN astdb AS rewrite ON (rewrite.key=route.key AND rewrite.family='LocalRewrite') WHERE 
                  route.family='LocalRoute'");
    print "Dialed Prefix,Host,Protocol,Called Prefix\r\n";
    for($i=0;$i < pg_num_rows($ibroute);$i++) {
      $branch=pg_fetch_row($ibroute,$i);
      for($j=0 ;$j < count($branch);$j++) {
        if ($j == "1") {
          $branch[$j]="\"" . $branch[$j] . "\"";
        }
        print $branch[$j];
        if ($j < count($branch)-1) {
          print ",";
        }
      }
      print "\r\n";
    }
  } else if ($filetype == "agents") {
    print "Queue,Agent,Penalty,Loged In\r\n";
    $agents=pg_query($db,"SELECT queue.family,substring(queue.key from position('/' in queue.key)+1),agent.value,CASE WHEN (queue.value = '-1') THEN 'No' ELSE 'Yes' END FROM astdb AS queue 
                                  LEFT OUTER JOIN astdb AS agent ON (agent.family = 'Q'||queue.family AND agent.key=queue.key) 
                                WHERE queue.family ~ '(^5[0-9]{2})|(^799)' ORDER by queue.family");
    for($i=0;$i < pg_num_rows($agents);$i++) {
      $agent=pg_fetch_row($agents,$i);
      for($j=0 ;$j < count($agent);$j++) {
        if ($j == "1") {
          if (strpos($agent[$j],"@")) {
            $agent[$j]=substr($agent[$j],0,strpos($agent[$j],"@"));
          }
          $agent[$j]="\"" . telformat($agent[$j]) . "\"";
        }
        print $agent[$j];
        if ($j < count($agent)-1) {
          print ",";
        }
      }
      print "\r\n";
    }    
  } else if ($filetype == "setup") {
    $setupq=pg_query($db,"SELECT family,key,value from astdb WHERE family = 'Setup' OR family='LocalArea' OR family='DDI' 
                                 OR (family = 'Q799' AND key='QAPENALTY') OR family = 'LocalPrefix' ORDER by family DESC,key");
    print "Family,Key,Value\r\n";
    for($i=0;$i < pg_num_rows($setupq);$i++) {
      $setup=pg_fetch_row($setupq,$i);
      for($j=0 ;$j < count($setup);$j++) {
        $setup[$j]="\"'" . $setup[$j] . "'\"";
        print $setup[$j];
        if ($j < count($setup)-1) {
          print ",";
        }
      }
      print "\r\n";
    }    
  } else if ($filetype == "console") {
    $conq=pg_query($db,"SELECT name,console.context,console.count FROM users LEFT OUTER JOIN console ON (name=console.mailbox) 
                                     LEFT OUTER JOIN astdb on (astdb.key=substring(name,1,2) AND astdb.family='LocalPrefix') 
                                   WHERE astdb.value='1' order by name");

    print "Exten,Group,Count\r\n";
    for($i=0;$i < pg_num_rows($conq);$i++) {
      $cons=pg_fetch_row($conq,$i);
      for($j=0 ;$j < count($cons);$j++) {
        if ($j == "1") {
          $cons[$j]="\"" . $cons[$j] . "\"";
        }
        print $cons[$j];
        if ($j < count($cons)-1) {
          print ",";
        }
      }
      print "\r\n";
    }
  } else if ($filetype == "gsmchan") {
    $gsmq=pg_query($db,"SELECT router,channel,calltime,starttime,endtime,regex,expires FROM gsmchannels ORDER BY router,channel");

    print "Router,Channel,Call Time,Start Time,End Time,Match Pattern,Expires\r\n";
    for($i=0;$i < pg_num_rows($gsmq);$i++) {
      $gsmc=pg_fetch_row($gsmq,$i);
      for($j=0 ;$j < count($gsmc);$j++) {
        if ($j == "1") {
          $gsmc[$j]="\"" . $gsmc[$j] . "\"";
        }
        print $gsmc[$j];
        if ($j < count($gsmc)-1) {
          print ",";
        }
      }
      print "\r\n";
    }
  } else if ($filetype == "speeddial") {
    $sdialq=pg_query($db,"SELECT number,dest,discrip FROM speed_dial ORDER BY discrip");

    print "Number,Dest,Discription\r\n";
    for($i=0;$i < pg_num_rows($sdialq);$i++) {
      $sdial=pg_fetch_row($sdialq,$i);
      for($j=0 ;$j < count($sdial);$j++) {
        if ($j == "2") {
          $sdial[$j]="\"" . $sdial[$j] . "\"";
        }
        print $sdial[$j];
        if ($j < count($sdial)-1) {
          print ",";
        }
      }
      print "\r\n";
    }
  } else if ($filetype == "zapsetup") {
    $dbtab[0]="zapgroup";
    $dbtab[1]="zapspan";
    $dbtab[2]="zapchan";
    $order[0]=" ORDER BY zaptrunk";
    $order[1]=" ORDER BY spannum";
    $order[2]=" ORDER BY zaptrunk,channel";
    for($dbcnt=0;$dbcnt<count($dbtab);$dbcnt++) {
      $zaptq=pg_query($db,"SELECT * FROM " . $dbtab[$dbcnt] . $order[$dbcnt]);
      for($i=0;$i < pg_num_rows($zaptq);$i++) {
        $zgroup=pg_fetch_row($zaptq,$i);
        for($j=0 ;$j < count($zgroup);$j++) {
          if ($j != "0") {
            $zgroup[$j]="\"" . $zgroup[$j] . "\"";
          }
          print $zgroup[$j];
          if ($j < count($zgroup)-1) {
            print ",";
          }
        }
        print "\r\n";
      }
    }
  } 
} else {
%>


<CENTER>
  <FORM enctype="multipart/form-data" METHOD=POST ACTION=/cdr/csvdownload.php>
  <TABLE WIDTH=90% cellspacing="0" cellpadding="0">
    <TR CLASS=list-color2>
      <TD ALIGN=LEFT>Select Type Of File To Download</TD>
      <TD ALIGN=LEFT VALIGN=MIDDLE>
        <SELECT NAME=filetype>
          <OPTION VALUE=exten>Extensions</OPTION>
          <OPTION VALUE=snommac>Snom Phones Auto Config</OPTION>
        </SELECT>
      </TD>
    </TR>
    <TR CLASS=list-color1>
      <TD COLSPAN=2 ALIGN=MIDDLE>
        <INPUT TYPE=SUBMIT NAME=getcsv VALUE="Submit Request">
      </TD>
    <TR>
 </TABLE>
 </FORM>

<%
}
%>
