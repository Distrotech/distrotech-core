<?php
include_once "auth.inc";

$bcolor[0]=" CLASS=list-color2";
$bcolor[1]=" CLASS=list-color1";

$ftype['exten']=_("Extension");
$ftype['elist']=_("Contact Details");
$ftype['protocol']=_("Extention Protocol Settings");
$ftype['snommac']=_("Phone Mac Addresses (Snom/Linksys)");
$ftype['snompbook']=_("Snom Phone Book");
$ftype['acd']=_("Automatic Call Distribution (Call Queue's)");
$ftype['agents']=_("Automatic Call Distribution Agents");
$ftype['ibroute']=_("Inter Branch Routing");
$ftype['console']=_("Flash Operator Pannel");
$ftype['setup']=_("Genral Configuration");
$ftype['gsmchan']=_("GSM Channels");
$ftype['zapsetup']=_("DAHDI Setup");
$ftype['telcosts']=_("Tel. Costs");
$ftype['speeddial']=_("Speed Dials");

$dater['telcosts']=1;

$level['Internal']=0;
$level['Local']=1;
$level['Long Distance']=2;
$level['Cellular']=3;
$level['Premium']=4;
$level['International']=5;

$level[0]=0;
$level[1]=1;
$level[2]=2;
$level[3]=3;
$level[4]=4;
$level[5]=5;

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

$astpdbk=array("IAXLine","ZAPLine","NOVOIP","ZAPRXGain","ZAPTXGain");
$astpdbm=array();
$astpdbm['IAXLine']=16;
$astpdbm['ZAPLine']=17;
$astpdbm['NOVOIP']=18;
$astpdbm['ZAPRXGain']=19;
$astpdbm['ZAPTXGain']=20;

$setup['AANext']=_("Auto Attendant Mailbox/Forward On No Agent/Timeout");
$setup['AATimeout']=_("Reception Queue Timeout Checked Every 18s");
$setup['AreaCode']=_("Local Area Code");
$setup['ExCode']=_("Local Exchange Prefix");
$setup['Trunk']=_("PSTN Trunk");
$setup['Trunk2']=_("PSTN Second Trunk");
$setup['Trunk3']=_("PSTN Third Trunk");
$setup['Trunk4']=_("PSTN Fourth Trunk");
$setup['VoipFallover']=_("Allow VOIP Fallover When Trunk Is Unavailable");
$setup['NoEnum']=_("Use ENUM Lookups On Outgoing Calls");
$setup['GSMRoute']=_("Use Configured GSM Routers");
$setup['GSMTrunk']=_("Allow Trunk Failover When Using Configured GSM Routers");
$setup['Attendant']=_("Default Attendant");
$setup['FAXT']=_("Default Fax Terminal");
$setup['Context']=_("Default Extension Permision");
$setup['FAXBOX']=_("Default FAX Handler");
$setup['Timeout']=_("Default Ring Timeout");
$setup['IPContext']=_("Level To Start Routing To Master Server");
$setup['TrunkPre']=_("Prefix Trunk Calls With");
$setup['TrunkStrip']=_("Number Of Digits To Strip On Trunk");
$setup['DefaultPrefix']=_("Default Extension Prefix (2 Digit Dialing)");
$setup['DefCLI']=_("Default CLI (Number Displayed To Called Party)");
$setup['QTimeout']=_("Default ACD Queue Timeout");
$setup['QATimeout']=_("Default ACD Queue Agent Timeout");
$setup['QAPenalty']=_("Default ACD Queue Agent Penalty Factor");
$setup['AdminPass']=_("Admin Password");
$localt['LocalArea']=_("Local Area Code");
$localt['LocalPrefix']=_("Local Prefix");

function macload($macaddr) {
  $macaddr=strtoupper(preg_replace("/:/","",$macaddr));
  return $macaddr;
}

function telnumload($tnumber) {
  $tnumber=preg_replace("/\(/","",$tnumber);
  $tnumber=preg_replace("/)/","",$tnumber);
  $tnumber=preg_replace("/-/","",$tnumber);
  $tnumber=preg_replace("/ /","",$tnumber);
  $tnumber=preg_replace("/\+/","00",$tnumber);
  return $tnumber;
}

function strin($strin) {
  if (substr($strin,0,1) == "'") {
    $strin=substr($strin,2);
  }
  return $strin;
}

function zpadd($numin) {
  for($cnt=strlen($numin);$cnt < 4;$cnt++) {
    $numin="0" . $numin;  
  }
  return $numin;
}

function zout($numin) {
  if ($numin == "") {
    $numin="0";
  }
  return $numin;
}

function boolyn($quin) {
  $quin=strtolower($quin);
  if (($quin == "yes") || ($quin == "1") || ($quin == "y") || ($quin == "t") || ($quin == "true")) {
    $quin=1;
  } else {
    $quin=0;
  }
  return $quin;
}

function uboolyn($quin) {
  $quin=strtolower($quin);
  if (($quin == "yes") || ($quin == "1") || ($quin == "y") || ($quin == "t") || ($quin == "true")) {
    $quin="yes";
  } else {
    $quin="no";
  }
  return $quin;
}

function rboolyn($quin) {
  $quin=strtolower($quin);
  if (($quin == "yes") || ($quin == "1") || ($quin == "y") || ($quin == "t") || ($quin == "true")) {
    $quin=0;
  } else {
    $quin=1;
  }
  return $quin;
}


if (isset($csvup)) {
  $rcnt=0;
  print "<CENTER>";
  print "<TABLE WIDTH=90% cellspacing=0 cellpadding=0><TR CLASS=list-color2>\n";

  $tmpcsv=tempnam("/tmp","extin");
  if (move_uploaded_file($_FILES['topdf']['tmp_name'],$tmpcsv)) {
    print "<TH CLASS=heading-body>" . $ftype[$filetype] . "</TH></TR>\n";
    if ($filetype == "snompbook") {
      include "../ldap/auth.inc";
    } else if ($filetype == "agents") {
      if (! isset($agi)) {
        require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
        $agi=new AGI_AsteriskManager();
        $agi->connect("127.0.0.1","admin","admin");
      }    
    }
    $ratefd=file($tmpcsv);
    while(list($lnum,$ldata)=each($ratefd)) {

      $text=array();
      $out=array();
      $ldata=rtrim($ldata);
      $text=explode(",",$ldata);
      if (($text[0] <= 0) && ($filetype != "snompbook") && ($filetype != "telcosts") && (($filetype != "setup") || ($lnum < 1)) && ((($filetype != "gsmchan") && ($filetype != "speeddial")) || ($lnum < 1))) {
        continue;
      }
 
      for($item=0;$item<count($text);$item++) {
        if (($text[$item][0] == "\"") && (substr($text[$item],-1) != "\"")) {
          $tmp=substr($text[$item],1) . ",";
          $item++;
          while((substr($text[$item],-2) == "\"\"") || (substr($text[$item],-1) != "\"")) {
            $tmp.=$text[$item] . ",";
            $item++;
          }
          $tmp.=substr($text[$item],0,-1);
          $tmp=preg_replace("/\"\"/","\"",$tmp);
        } else if (($text[$item][0] == "\"") && (substr($text[$item],-1) == "\"")) {
          $tmp=substr($text[$item],1,-1);
        } else {
          $tmp=$text[$item];
        }
        if ($filetype != "setup") {
          $tmp=preg_replace("/'/","''",$tmp);
        }
        array_push($out,$tmp);
      }

      if ($filetype == "exten") {
        $rcnt++;
        $out[0]=zpadd($out[0]);
        $exten=$out[0];
        $eprefix=substr($exten,0,2);
        if (! $preok[$eprefix]) {
          $preokq=pg_query("SELECT value FROM astdb WHERE family='LocalPrefix' AND key='" . $eprefix . "'");
          if (pg_num_rows($preokq) > 0) {
            $chpre=pg_fetch_array($preokq,0);
            if ($chpre[0] != 1) {
              pg_query("UPDATE astdb SET value='1' WHERE family='LocalPrefix' AND key='" . $eprefix . "'");
            }
          } else {
            pg_query("INSERT INTO astdb (family,key,value) VALUES ('LocalPrefix','" . $eprefix . "','1')");
          }
          $preok[$eprefix]=1;
        }

	$out[1]=strin($out[1]);
	$out[2]=strin($out[2]);

        $out[3]=telnumload($out[3]);
        $out[3]=zout($out[3]);

        $out[4]=telnumload($out[4]);
        $out[4]=zout($out[4]);

        $out[5]=strin(zpadd($out[5]));
        $out[6]=strin(zpadd($out[6]));
 
        $out[7]=telnumload($out[7]);
        $out[7]=zout($out[7]);
        $out[8]=telnumload($out[8]);
        $out[8]=zout($out[8]);
        $out[9]=telnumload($out[9]);
        $out[9]=zout($out[9]);
        $out[10]=telnumload($out[10]);
        $out[10]=zout($out[10]);

        $out[11]=rboolyn($out[11]);
        $out[12]=boolyn($out[12]);

        if ($out[13] < 10) {
          $out[13]=10;
        }

        $out[14]=boolyn($out[14]);
        $out[15]=boolyn($out[15]);
        $out[16]=boolyn($out[16]);
        $out[17]=boolyn($out[17]);
        $out[18]=boolyn($out[18]);

	$out[19]=strin($out[19]);
	$out[20]=strin($out[20]);

        $out[21]=$level[$out[21]];
        $out[22]=$level[$out[22]];

        $existq=pg_query("SELECT uniqueid FROM users WHERE name='" . $exten . "'");
        if (pg_num_rows($existq) > 0) {
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Updating " . $out[0] . " [" . $out[1];
          if ($out[2] != "") {
            print " " . $out[2];
          }
          print "]</TD></TR>\n";

          pg_query("UPDATE users SET secret='" .  $out[5] . "',password='" . $out[6] . "',fullname='" . trim($out[1]) . "',email='" . trim($out[2]) . "'," .
                                    "callgroup='" . $out[19] . "',pickupgroup='" . $out[20] . "' WHERE name='" . $exten . "'");
        } else {
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Inserting " . $out[0] . " [" . $out[1];
          if ($out[2] != "") {
            print " " . $out[2];
          }
          print "]</TD></TR>\n";
          pg_query("INSERT INTO users (context,name,defaultuser,mailbox,secret,password,usertype,fullname,email,callgroup,pickupgroup)
                               VALUES ('6','" . $exten . "','" . $exten . "','" . $exten . "','" . $out[5] . "','" . $out[6] . "','0','" .
                                       trim($out[1]) . "','" . trim($out[2]) . "','" . $out[19] . "','" . $out[20] . "')");
          pg_query("INSERT INTO features (exten) VALUES ('" . $exten . "')");

        }
        for($dbq=0;$dbq < count($astdbk);$dbq++) {
          pg_query("UPDATE features SET $astdbk[$dbq]='" . $out[$astdbm[$astdbk[$dbq]]] . "' WHERE exten='" . $exten . "'");
        }
      } else if ($filetype == "elist") {
        $rcnt++;
        $out[0]=zpadd($out[0]);
        $exten=$out[0];

        $out[1]=strin($out[1]);
        $out[2]=strin($out[2]);

        $out[3]=telnumload($out[3]);
        $out[3]=zout($out[3]);
        $out[4]=strin($out[4]);

        $existq=pg_query("SELECT uniqueid FROM users WHERE name='" . $exten . "'");
        if (pg_num_rows($existq) > 0) {
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Updating " . $out[0] . " [" . $out[1];
          if ($out[2] != "") {
            print " " . $out[2];
          }
          print "]</TD></TR>\n";

          pg_query("UPDATE users SET fullname='" . $out[1] . "',email='" . $out[2] . "' WHERE name='" . $exten . "'");
          pg_query("UPDATE features SET altc='" . $out[3] . "',office='" . $out[4] . "' WHERE exten='" . $exten . "'");
        }
      } else if ($filetype == "protocol") {
        $rcnt++;
        $out[0]=zpadd($out[0]);
        $exten=$out[0];


        $out[4]=uboolyn($out[4]);
        $out[5]=uboolyn($out[5]);
        $out[6]=uboolyn($out[6]);
        $out[8]=zpadd($out[8]);

        $out[16]=boolyn($out[16]);
        $out[17]=zout($out[17]);
        $out[18]=boolyn($out[18]);
        $out[19]=zout($out[19]);
        $out[20]=zout($out[20]);
        $codecs="";
        for($ccnt=10;$ccnt<16;$ccnt++) {
          $codecs.=$out[$ccnt] . ";";
        }
        $codecs=substr($codecs,0,strlen($codecs)-1);

        $existq=pg_query("SELECT fullname,email FROM users WHERE name='" . $exten . "'");
        if (pg_num_rows($existq) > 0) {
          $name=pg_fetch_array($existq,0);
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Updateing: " . $out[0] . " (" . $name[0];
          if ($name[1] != "") {
            print  " - " . $name[1];
          }
          print ")</TD></TR>\n";
          pg_query($db,"UPDATE users SET nat='" .  $out[1] . "',dtmfmode='" . $out[2] . "',insecure='" . $out[3] . "',canreinvite='" . $out[4] . "'," .
                                    "cancallforward='" . $out[5] . "',qualify='" . $out[6] . "',h323permit='" . $out[7] . "',h323gkid='" . $out[8] .
                                    "',h323prefix='" . $out[9] . "',allow='" . $codecs . "' WHERE name='" . $exten . "'");
          for($dbq=0;$dbq < count($astpdbk);$dbq++) {
            pg_query("UPDATE features SET $astdbk[$dbq]='" . $out[$astdbm[$astdbk[$dbq]]] . "' WHERE exten='" . $exten . "'");
          }
        } else {
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Can't Update " . $out[0] . " (Extention Does Not Exist)</TD></TR>\n";
        }
      } else if ($filetype == "snommac") {
        $out[0]=zpadd($out[0]);
        $out[1]=macload($out[1]);
        $defkey[2]="9";
        $defkey[3]="700";
        $defkey[4]="701";
        $defkey[5]="702";
        $defkey[6]="900";
        $defkey[7]="901";
     
        $nameq=pg_query($db,"SELECT fullname FROM users WHERE name = '" . $out[0] . "'");
        $name=pg_fetch_array($nameq,0);
        
        if ($out[1] != "") {
          $rcnt++;
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Adding " . $out[0] . " (" . $name[0] . " - " . $out[1] . ")</TD></TR>\n";
          pg_query("UPDATE features SET snommac='" . $out[1] . "',snomlock='" . boolyn($out[2]) . "',registrar='" . $out[3] . "',vlan='" . $out[4] . "',ptype='" . $out[5] . "' WHERE exten='" . $out[0] . "'");
        }
        if (($out[6] == "") && ($out[7] == "") && ($out[8] == "") && ($out[1] != "")) {
          pg_query($db,"DELETE FROM astdb WHERE family='" . $out[0] . "' AND key ~ '^fkey[0-9]+'");
          for($kcnt=9;$kcnt < count($out);$kcnt++) {
            $key=$kcnt-9;
            if (($out[$kcnt] != "") && ($out[$kcnt] != $defkey[$key])) {
              $out[$kcnt]=telnumload($out[$kcnt]);
              pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $out[0] . "','fkey" . $key . "','" . $out[$kcnt] . "')");
            } else if (($out[$kcnt] == "") && ($defkey[$key] != "")) {
              pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $out[0] . "','fkey" . $key . "','1')");
            }
          }
        } else if ($out[1] != "") {
          pg_query($db,"UPDATE users SET dtmfmode='info' WHERE name='" . $out[0] . "'");
          if ((! $lsysdone[$out[1]]) && (count($out) > 6) && ($out[1] != "")) {
            pg_query($db,"DELETE FROM astdb WHERE family='" . $out[1] . "' AND (key='LINKSYS' OR key='VLAN' OR key='PROFILE' OR key='STUNSRV')");
            pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $out[1] . "','LINKSYS','" . $out[6] . "')");
            pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $out[1] . "','PROFILE','" . $out[7] . "')");
            pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $out[1] . "','STUNSRV','" . $out[8] . "')");
            if ($out[4] == "") {
              $out[4]="1";
            }
            pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $out[1] . "','VLAN','" . $out[4] . "')");
            $lsysdone[$out[1]]=1;
          }	
        }
      } else if ($filetype == "snompbook") {
        $getent=ldap_search($ds,"ou=snom","cn=" . $out[0],array(telephonenumber,cn));
        $info=ldap_get_entries($ds,$getent);
        if ($out[1] != "") {
          $out[1]=telnumload($out[1]);
          if (($info[0]["telephonenumber"][0] != $out[1]) && ($info["count"] > 0)){
            $rcnt++;
            print "<TR" . $bcolor[$rcnt % 2] . "><TD>Updateing " . $out[0] . " [" . $out[1] . "]</TD></TR>\n";
            ldap_modify($ds,$info[0]["dn"],array("telephonenumber"=>$out[1]));
          } else if ($info["count"] == 0) {
            $rcnt++;
            $add=array("objectclass"=>array("snomcontact"),"cn"=>$out[0],"telephonenumber"=>$out[1]);
            $dn="cn=" . $out[0] . ",ou=snom";
            if (!ldap_add($ds,$dn,$add)) {
              if (ldap_errno($ds) == "32") {
                 $add2=array("objectclass"=>"organizationalUnit","ou"=>"snom");
                 ldap_add($ds,"ou=snom",$add2);
              }
              ldap_add($ds,$dn,$add);
            }
            print "<TR" . $bcolor[$rcnt % 2] . "><TD>Adding " . $out[0] . " [" . $out[1] . "]</TD></TR>\n";
          }
        } else if ($info["count"] > 0) {
            $rcnt++;
            print "<TR" . $bcolor[$rcnt % 2] . "><TD>Deleting " . $out[0] . " [" . $info[0]["telephonenumber"][0] . "]</TD></TR>\n";
            ldap_delete($ds,$info[0]["dn"]);
        }
      } else if ($filetype == "acd") {
        if ($out[1] == "") {
          continue;
        }
        $out[11]=uboolyn($out[11]);
        $out[14]=uboolyn($out[14]);
        $existq=pg_query("SELECT name FROM queue_table WHERE name='" . $out[0] . "'");
        if (pg_num_rows($existq) > 0) {
          $rcnt++;
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Updateing: " . $out[0] . " (" . $out[1];
          if ($out[16] != "") {
            print  " - " . $out[16];
          }
          print ")</TD></TR>\n";

          pg_query($db,"UPDATE queue_table SET description='" . $out[1] . "',strategy='" . $out[2] . "',timeout='" . $out[3] . "'," .
                                        "wrapuptime='" . $out[4] . "',memberdelay='" . $out[5] . "',servicelevel='" . $out[6] . "',weight='" . $out[7] . "'," .
                                        "maxlen='" . $out[8] . "',retry='" . $out[9] . "',announce_frequency='" . $out[10] . "',announce_holdtime='" . $out[11] . "'," .
                                        "announce_round_seconds='" . $out[12] . "',autopausedelay='" . $out[13] . "',playmusiconhold='" . $out[14] . "' " .
                                      "WHERE name='" . $out[0] . "'");
          pg_query($db,"UPDATE users SET defaultuser='" . $out[0] . "',context='6',password='" . $out[15] . "',email='" . $out[16] . "',fullname='" . $out[1] . "',mailbox='" . $out[0] . "' WHERE name='" . $out[0] . "'");
        } else {
          $rcnt++;
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Adding: " . $out[0] . " (" . $out[1];
          if ($out[16] != "") {
            print  " - " . $out[16];
          }
          print ")</TD></TR>\n";
          pg_query($db,"INSERT INTO queue_table (name,description,strategy,timeout,wrapuptime,memberdelay,servicelevel,weight,maxlen,retry,announce_frequency,
                                          announce_holdtime,announce_round_seconds,autopausedelay,playmusiconhold) VALUES ('" . $out[0] . "','" . $out[1] .
                                          "','" . $out[2] . "','" . $out[3] . "','" . $out[4] . "','" . $out[5] . "','" . $out[6] . "','" . $out[7] .
                                          "','" . $out[8] . "','" . $out[9] . "','" . $out[10] . "','" . $out[11] . "','" . $out[12] . "','" .  $out[13] .
                                          "','" . $out[14] . "');");
          pg_query($db,"DELETE FROM users WHERE name='" . $out[0] . "'");
          pg_query($db,"INSERT INTO users (defaultuser,name,mailbox,password,fullname,email,context) VALUES ('" . $out[0] . "','" . $out[0] . "','" . $out[0] . "','" . $out[15] . "','" . $out[1] . "','" . $out[16] . "','6')");
        }
        pg_query($db,"DELETE FROM astdb WHERE family = 'Q" . $out[0] . "' AND (key='QTIMEOUT' OR key='QAPENALTY' OR key='QRDELAY')");
        pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('Q" . $out[0] . "','QTIMEOUT','" . $out[17] . "')");
        pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('Q" . $out[0] . "','QAPENALTY','" . $out[18] . "')");
        pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('Q" . $out[0] . "','QRDELAY','" . $out[19] . "')");
      } else if ($filetype == "ibroute") {
        if ($out[1] == "") {
          continue;
        }
        $rcnt++;

        if (strlen($out[0]) == 1) {
          $out[0]="0" . $out[0];
        }

        if (strlen($out[3]) == 1) {
          $out[3]="0" . $out[3];
        }

        print "<TR" . $bcolor[$rcnt % 2] . "><TD>Adding/Updating: " . $out[0] . " -> " . $out[1] . " -> " . $out[3] . " (" . $out[2] . ")</TD></TR>";

        pg_query($db,"DELETE FROM astdb WHERE key='" . $out[0] . "' AND (family='LocalRoute' OR family='LocalRouteProto' OR family='LocalRewrite')");
        pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('LocalRoute','" . $out[0] . "','" . $out[1] . "')");
        pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('LocalRouteProto','" . $out[0] . "','" . $out[2] . "')");
        pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('LocalRewrite','" . $out[0] . "','" . $out[3] . "')");
      } else if ($filetype == "agents") {
        $existq=pg_query("SELECT name FROM queue_table WHERE name='" . $out[0] . "'");
        if ((pg_num_rows($existq) > 0) || ($out[0] == "799")){
          $rcnt++;
          $out[1]=telnumload($out[1]);
          $linetype=pg_query($db,"SELECT CASE WHEN (zapline > 0) THEN 'DAHDI/'||zapline ELSE
                                           CASE WHEN (iaxline = '1') THEN 'IAX2/'||name ELSE 'SIP/'||name
                                           END
                                         END
                                    FROM users LEFT OUTER JOIN features ON (exten = name)
                                    WHERE name='" . $out[1] . "'");
          if (pg_num_rows($linetype) > 0) {
            $ltype=pg_fetch_row($linetype,0);
            $out[1]=$ltype[0];
          } else {
            $out[1]="Local/" . $out[1] . "@6/n";
          }
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Updateing/Adding: " . $out[0] . " (" . $out[1] . " - " . $out[2] . " - ";
          $out[3]=boolyn($out[3]);
          $agi->QueueRemove($out[0],$out[1]);
          if ($out[3]) {
            $out[3]=$out[2];
            print "Active";
            $agi->QueueAdd($out[0],$out[1],$out[2]);
          } else {
            $out[3]="-1";
            print "Inactive";
          }
          print ")</TD></TR>";

          pg_query($db,"DELETE FROM astdb WHERE family ~ '(^Q" . $out[0] . "\$)|(^" . $out[0] . "\$)' AND key = '" . $out[1] . "'");
          pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('Q" . $out[0] . "','" . $out[1] . "','" . $out[2] . "')");
          pg_query($db,"INSERT INTO astdb (family,key,value) VALUES ('" . $out[0] . "','" . $out[1] . "','" . $out[3] . "')");


        }
      } else if ($filetype == "console") {
        $out[0]=zpadd($out[0]);
        $exten=$out[0];
        $existq=pg_query("SELECT uniqueid FROM users WHERE name='" . $exten . "'");
        if (pg_num_rows($existq) > 0) {
          $econ=pg_query("SELECT position FROM console WHERE mailbox='" . $exten . "' AND context='" . $out[1] . "'");
          $rcnt++;
          if (pg_num_rows($econ) > 0) {
            print "<TR" . $bcolor[$rcnt % 2] . "><TD>Updateing: " . $out[0] . " (" . $out[1] . " - " . $out[2] . ")</TD></TR>";
            pg_query($db,"UPDATE console SET count='" . $out[2] . "' WHERE mailbox='" . $exten . "' AND context='" . $out[1] . "'");
          } else {
            print "<TR" . $bcolor[$rcnt % 2] . "><TD>Adding: " . $out[0] . " (" . $out[1] . " - " . $out[2] . ")</TD></TR>";

            pg_query($db,"DELETE FROM console WHERE mailbox='" . $exten . "'");
            $econ2=pg_query("SELECT DISTINCT context FROM console WHERE context='" . $out[1] . "'");
            if (pg_num_rows($econ2) > 0) {
              pg_query($db,"INSERT INTO console SELECT position+1,'" . $exten . "',context,'" . $out[2] . "'  from console where context='" . $out[1] . "' order by  position desc limit 1");
            } else {
              pg_query($db,"INSERT INTO console VALUES ('0','" . $exten . "','" . $out[1] . "','" . $out[2] . "')");
            }
          }
        }
      } else if ($filetype == "setup") {
        $rcnt++;
        $supq=pg_query($db,"UPDATE astdb SET value=" . $out[2] . " WHERE family=" . $out[0] . " AND key=" . $out[1]);
        if(!pg_affected_rows($supq)) {
          pg_query($db,"INSERT INTO astdb (value,family,key) VALUES (" . $out[2] . "," . $out[0] . "," . $out[1] . ")");
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Adding: ";
        } else {
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Updateing :";
        }
        $out[0]=preg_replace("/'/","",$out[0]);
        $out[1]=preg_replace("/'/","",$out[1]);
        $out[2]=preg_replace("/'/","",$out[2]);

        
        if (($out[0] == "Setup") && ($setup[$out[1]] != "")) {
          $out[0]=$setup[$out[1]];
        } else if ($out[0] == "Setup") {
          $out[0]=$out[1];
        } else if ($out[0] == "Q799") {
          $out[0]=_("Default Agent Penalty (Reception Queue)");
        } else if ($out[0] == "DDI") {
          $out[0]="DDI Route";
          $out[2]=$out[1] . " -> " . $out[2];
        } elseif (($out[0] == "LocalPrefix") || ($out[0] == "LocalArea")) {
          $out[0]=$localt[$out[0]];
          $out[2]=$out[1];
        }
        print $out[0] . " (" . $out[2] . ")</TD></TR>";
      } else if ($filetype == "gsmchan") {
        $rcnt++;
        $gsmupq=pg_query($db,"UPDATE gsmchannels SET calltime='" . $out[2] . "',starttime='" . $out[3] . "',endtime='" . $out[4] . "',regex='" . $out[5] .
                                        "',expires='" . $out[6] . "' WHERE router='" . $out[0] . "' AND channel='" . $out[1] . "'");
        if(!pg_affected_rows($gsmupq)) {
          pg_query($db,"INSERT INTO gsmchannels  (router,channel,calltime,starttime,endtime,regex,expires) VALUES ('" . $out[0] . "','" . $out[1] . "','" . $out[2] . "','" . $out[3] . "','" . $out[4] . "','" . $out[5] . "','" . $out[6] . "')");
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Adding: ";
        } else {
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Updateing :";
        }
        $secs=$out[2] % 60;
        $min=($out[2]-$secs)/60;
        print $out[0] . " (" . $out[1] . ") [" . $min . "m " . $secs . "s (" . $out[2] . "s)";
        if ($out[3] != $out[4]) {
          print " From " . $out[3] . " To " . $out[4];
        }
        if ($out[5] != "") {
          print " Match (" . $out[5] . ")";
        }
        print " Expires - " . $out[6];
        print "</TD></TR>";
      } else if ($filetype == "zapsetup") {
        $rcnt++;
        if ($out[28] != "") {
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Configuring Group " . $out[0] . "</TD></TR>\n";
          pg_query("DELETE FROM zapgroup WHERE zaptrunk='" . $out[0] . "'");
          pg_query("INSERT INTO zapgroup VALUES ('" . $out[0] . "','" . $out[1] . "','" . $out[2] . "','" . $out[3]  . "','" . $out[4] . "',
                                                '" . $out[5] . "','" . $out[6] . "','" . $out[7] . "','" . $out[8]  . "','" . $out[9] . "',
                                                '" . $out[10] . "','" . $out[11] . "','" . $out[12] . "','" . $out[13]  . "','" . $out[14] . "',
                                                '" . $out[15] . "','" . $out[16] . "','" . $out[17] . "','" . $out[18]  . "','" . $out[19] . "',
                                                '" . $out[20] . "','" . $out[21] . "','" . $out[22] . "','" . $out[23]  . "','" . $out[24] . "',
                                                '" . $out[25] . "','" . $out[26] . "','" . $out[27] . "','" . $out[28]  . "')");
        } else if ($out[6] != "") {
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Configuring Span " . $out[0] . "</TD></TR>\n";
          pg_query("DELETE FROM zapspan WHERE spannum='" . $out[0] . "'");
          if ($out[7] == "") {
            $out[7]="NULL";
          }
          pg_query("INSERT INTO zapspan VALUES ('" . $out[0] . "','" . $out[1] . "','" . $out[2] . "','" . $out[3]  . "','" . $out[4] . "',
                                                '" . $out[5] . "','" . $out[6] . "'," . $out[7]  . ")");
        } else {
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>Adding Channel " . $out[1] . " To Group " . $out[0] . " Rx/Tx (" . $out[2] . "/" . $out[3] . ")</TD></TR>\n";
          pg_query("DELETE FROM zapchan WHERE zaptrunk='" . $out[0] . "' AND channel='" . $out[1] . "'");
          pg_query("INSERT INTO zapchan VALUES ('" . $out[0] . "','" . $out[1] . "','" . $out[2] . "','" . $out[3]  . "')");
        }
      } else if ($filetype == "telcosts") {
 	if ($out[0] != "ID") {
          $rcnt++;
          print "<TR" . $bcolor[$rcnt % 2] . "><TD>";
          if ($out[2] == "") {
            $out[2]="-1";
          }
          if ($out[9] == "") {
            $out[9]=$out[5];
            $out[10]=$out[6];
            $out[11]=$out[7];
            $out[12]=$out[8];
          }
          $out[6]=sprintf("%0.0f",$out[6]*100000);
          $out[7]=sprintf("%0.0f",$out[7]*100000);
          $out[10]=sprintf("%0.0f",$out[10]*100000);
          $out[11]=sprintf("%0.0f",$out[11]*100000);

          if ($out[0] != "") {
            print "Updating: " . $out[1];
            pg_query($db,"UPDATE localrates set validto='" . $validfrom . "' WHERE index='" . $out[0] . "'");
          } else {
            print "New: " . $out[1];
          }
          print "</TD></TR>\n";
          array_shift($out);
          pg_query($db,"INSERT INTO localrates  (description,distance,match,peakdays,peakstart,peakmin,peaksec,peakperiod,peakend,offpeakmin,offpeaksec,offpeakperiod,validfrom) values ('" . implode("','",$out) . "','" . $validfrom . "')");
        } else {
          pg_query($db,"DELETE from trunkcost where cast(substr(uniqueid,1,10) as int) > extract(epoch from timestamp '" . $validfrom . "') AND timestamp '" . $validfrom . "' < now()");
        }
      } else if ($filetype == "speeddial") {
 	if ($out[1] != "") {
          $rcnt++;
          print "<TR" . $bcolor[$rcnt % 2] . ">";

          print "<TD>" . $out[0] . " - " . $out[1] . " - " . $out[2] . "</TD>";

	  pg_query($db,"DELETE FROM speed_dial WHERE number='" . rtrim($out[0]) . "'");
          pg_query($db,"INSERT INTO speed_dial (number,dest,discrip) VALUES ('" . rtrim($out[0]) . "','" . rtrim($out[1]) . "','" . $out[2] . "')");

          print "</TR>\n";
        } else {
	  pg_query($db,"DELETE FROM speed_dial WHERE number='" . $out[0] . "'");
        }
      }
    }
  }
  if (! isset($agi)) {
    require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
    $agi=new AGI_AsteriskManager();
  }
  $agi->connect("127.0.0.1","admin","admin");
  $curext=pg_query("SELECT name,ipaddr,ptype from users left outer join astdb as lkey on (substr(name,0,3)=lkey.key) LEFT OUTER JOIN features ON (name=exten) WHERE (ptype='SNOM' OR ptype ~ '^IP_' OR ptype='POLYCOM') AND length(name) = 4 AND lkey.family = 'LocalPrefix' AND lkey.value=1");
  for($i=0;$i < pg_num_rows($curext);$i++) {
    $r = pg_fetch_array($curext,$i,PGSQL_NUM);
    if ($r[2] == "SNOM") {
      $agi->command("sip notify reconfig-snom " . $r[0]);
    } else {
      $agi->command("sip notify reboot-polycom " . $r[0]);
    }
  }
  $agi->command("sip prune realtime peer " . $r[0]);
  $agi->command("sip prune realtime user " . $r[0]);
  $agi->disconnect();

  if ($filetype == "telcosts") {
    pg_query($db,"UPDATE localrates set distance=null where distance = -1");
    $getbill=on;
    $ddat=getdate();
    $date=$ddat['mon'] . "/" . $ddat['mday'];
    $lgetbdate[0]=$validfrom;
    include "/var/spool/apache/htdocs/cdr/func.inc";
  }
} else {
?>
  <CENTER>
  <FORM enctype="multipart/form-data" METHOD=POST>
  <TABLE WIDTH=90% cellspacing=0 cellpadding=0><TR CLASS=list-color2>
  <INPUT TYPE=HIDDEN NAME=disppage VALUE=cdr/csvupload.php>
<?php
    if ($_POST['mmap'] == "") {
?>
      <TD ALIGN=LEFT onmouseover="myHint.show('CU1')" onmouseout="myHint.hide()">Select Type Of File To Load</TD>
      <TD ALIGN=LEFT VALIGN=MIDDLE>
        <SELECT NAME=filetype>
          <OPTION VALUE=exten>Extensions</OPTION>
          <OPTION VALUE=setup>Setup</OPTION>
        </SELECT>
      </TD>
    </TR>
<?php
    } else {
?>
      <INPUT TYPE=HIDDEN NAME=filetype VALUE="<?php print $_POST['mmap'];?>">
      <TH COLSPAN=2 CLASS=heading-body>CSV <?php print $ftype[$_POST['mmap']];?> Upload</TH></TR>
<?php
    }
?>
    <TR CLASS=list-color1>
      <TD WIDTH=50% onmouseover="myHint.show('CU0')" onmouseout="myHint.hide()">
        <?php print _("CSV File To Be Loaded");?>
      </TD>
      <TD WIDTH=50%>
        <INPUT TYPE=FILE NAME=topdf>
      </TD>
    </TR>
<?php
    if ($dater[$_POST['mmap']]) {
?>
    <TR CLASS=list-color2>
      <TD WIDTH=50% onmouseover="myHint.show('CU0')" onmouseout="myHint.hide()">
        <?php print _("Date From When To Be Activated");?>
      </TD>
      <TD WIDTH=50%>
        <INPUT TYPE=TEXT NAME=validfrom VALUE="<?php print date('Y-m-d H:i:s');?>">
      </TD>
    <TR CLASS=list-color1>
<?php
    } else {
?>
    <TR CLASS=list-color2>
<?php  }?>
      <TD COLSPAN=2 ALIGN=MIDDLE>
        <INPUT TYPE=SUBMIT NAME=csvup VALUE="<?php print _("Submit Request");?>">
      </TD>
    <TR>
 </FORM>
<?php
}
?>
</TABLE>
