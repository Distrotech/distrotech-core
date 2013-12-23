#!/usr/bin/php -q
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

$localgkid="ZATCGK1";

$odbc_handle=odbc_connect("Master","asterisk","zatelepass");
if ($odbc_handle === 0) {
  exit();
}

if (!isset($agi)) {
  require "phpagi/phpagi.php";
  $agi = new AGI();
}

$stat_channel = $agi->channel_status($channel);
$chanstat=$stat_channel['result'];
$channel=$agi->request['agi_channel'];
$uniqueid=$agi->request['agi_uniqueid'];
$credit='';
$tariff='';
$active='';
$timeout='';
$newdestination='';
$dialcmd='';
$dialstr='';
$prefix='';
$removeprefix='';
$countrycode='';
$subcode='';
$rate='';
$resellerid='';
$rowner='';
$rlevel='';
$resellerbuyperiod='';
$resellersellperiod='';
$resellerrate='';
$resellertariff='';
$resellercredit='';
$resellerexrate='';
$resellerminperiod='';
$resellerbuyminperiod='';
$simuse='';
$linelimit='';
$taxrate='';
$rtaxrate='';
$freemin='';
$freeans='';
$freebill='';
$freepid='';
$usertype='';
$chanid='';
$cbackuse=0;
$h323prefix='';

if (isset($_SERVER['argv'][1])) {
  $username=$_SERVER['argv'][1];
} else {
  $username='';
}

if ((isset($_SERVER['argv'][3])) && ($_SERVER['argv'][3] != "")) {
  $h323prefix=$_SERVER['argv'][3];
  $noivr=1;
} else {
  $h323prefix='';
  $noivr=0;
}

if (substr($channel,0,4) == "SIP/") {
  $agi->answer();
  if ($username == "") {
    $username=substr($channel,4,8);
  }
} else if (substr($channel,0,5) == "IAX2/") {
  $noivr=1;
  if ($username == "") {
    $username=substr($channel,5,8);
  }
} else if (substr($channel,0,6) == "OH323/") {
  $noivr=1;
} else if (substr($channel,0,7) == "Local/*") {
  if ($chanstat == 6) {
    $cbackuse=2;
    $noivr=0;
    $subcall=0;
    $uniqueid=$uniqueid*10000;
  } else {
    $noivr=1;
    $cbackuse=1;
  }
  if ($username == "") {
    $username=substr($channel,7,8);
  }
}

if (isset($_SERVER['argv'][2])) {
  $destination=$_SERVER['argv'][2];
} else {
  $destination=$agi->request['agi_extension'];
}

if ($destination == "s") {
  $destination=getivrdest();
}
  
verbose("DEST : " .$destination);

place_call();

if ($cbackuse == 2) {
  $stat_channel = $agi->channel_status($channel);
  $chanstat=$stat_channel['result'];
  while(($chanstat == 6) && ($subcall < 10)){
    $destination=getivrdest();
    $subcall++;
    $uniqueid++;
    place_call();
    $stat_channel = $agi->channel_status($channel);
    $chanstat=$stat_channel['result'];
  }
}

exit;

function getivrdest() {
  global $agi;

  $agi->answer();
  $agi->exec("WAIT","0,25");
  $destnum=$agi->get_data("beep",3500);
  return substr(strrchr("*" . $destnum['result'],"*"),1);
}

function verbose($outmsg) {
  global $agi;
  $agi->verbose($outmsg,3);
}

function odbcquery($sqlquery) {
  global $odbc_handle;

  $odbcexec=odbc_exec($odbc_handle,$sqlquery);
  if ($odbcexec === 0) {
    return -1;
  }
  $odbc_data=odbc_fetch_into($odbcexec,$odbc_array);
  if ($odbc_data === 0) {
    return -1;
  } else {
    return $odbc_array;
  }
}

function apply_rules ($phonenumber){
  if ((strlen($phonenumber) == "10") && (substr($phonenumber,0,2) != "09")){
    $phonenumber="27" . substr($phonenumber,1,9); 
  } else if (substr($phonenumber,0,2) == "09") {
    $phonenumber=substr($phonenumber,2); 
  }
  return $phonenumber;
}

function CC_asterisk_authorize($tariffcode, $phonenumber){
  global $resellertariff;
  global $username;	
  global $h323prefix;
  global $provider;
  global $agi;
  global $localgkid;

  $rateinfo = array();

  $GKQUERY=("SELECT h323gkid from users where name='" $h323gwid '");
  $gkidq=odbcquery($GKQUERY);
  $h323gkid=$gkid[0];
  verbose("GKID: " . $h323gkid);

  $QUERY = "SELECT rate, countrycode, subcode,lpad(provider.trunkprefix,7,'0'),removeprefix,tax,dialcmd,callerid,provider.name,trunk.protocol,trunk.providerip,trunk.h323prefix FROM countryprefix LEFT JOIN tariffrate ".
	   "USING (countrycode,subcode) LEFT JOIN provider USING (trunkprefix) LEFT OUTER JOIN trunk ON (trunk.trunkprefix=provider.trunkprefix AND h323reggk = '$h323gkid') LEFT OUTER JOIN tariff ON (tariff.tariffcode=tariffrate.tariffcode) ".
           "WHERE ".
	   "tariffrate.tariffcode='$tariffcode' AND prefix=SUBSTRING('$phonenumber',1,length(prefix)) ".
	   "ORDER BY LENGTH(prefix) DESC LIMIT 1;";
  $result=odbcquery($QUERY);
  
  if (!is_array($result)) {
    return -1;
  }								

  $QUERY = "SELECT rate,tax,minrate,showtax  " .
           "FROM countryprefix LEFT JOIN tariffrate " .
           "USING (countrycode,subcode) ".
           "LEFT OUTER JOIN tariff ON (tariff.tariffcode=tariffrate.tariffcode) ".
           "WHERE ".
	   "tariffrate.tariffcode='$resellertariff' AND prefix=SUBSTRING('$phonenumber',1,length(prefix)) ".
	   "ORDER BY LENGTH(prefix) DESC LIMIT 1;";
  $rresult=odbcquery($QUERY);

  if (!is_array($rresult)) {
    return -1;
  }								

  $QUERY ="SELECT freemin/simuse,ansperiod,billperiod,package.id FROM  package " .
                  "LEFT OUTER JOIN tariffrate ON (freerate=tariffcode) " .
                  "LEFT OUTER JOIN users ON (userid=users.id) " .
                "WHERE rate < (freethreshold - freesfee)  AND rate > 0 AND " .
                  "users.username='$username' AND countrycode ='" . $result[1] . "' AND " .
                  "subcode = '" . $result[2] . "' AND activedate+expiredays > now() AND " .
                  "freemin/simuse > ansperiod " .   
                  "ORDER BY activedate LIMIT 1;";
  $freemin=odbcquery($QUERY);


  if ($rresult[2] > $result[0]) {
    $result[0]=$rresult[2];
  }

  $rateinfo[0][0]= $result[0]; // RATE
  if ($h323prefix == "") {
    $rateinfo[0][2]= $result[3]; // PREFIX
  } else {
    $rateinfo[0][2]=str_pad($h323prefix,7,"0",STR_PAD_LEFT);
  }
  $rateinfo[0][4]= $phonenumber;

  $rateinfo[0][5]= $result[1]; // COUNTRYCODE
  $rateinfo[0][6]= $result[2]; // SUBCODE
  $rateinfo[0][7]= $result[4]; // REMOVEPREFIX
  $rateinfo[0][8]= $rresult[0]; // RESELER RATE
  $rateinfo[0][9]= $result[5]; // TAX RATE

  $rateinfo[0][10]= $rresult[1]; // RESELER TAX RATE
  $rateinfo[0][11] = $freemin[0]; //Minutes Available On Package
  $rateinfo[0][12] = $freemin[1]; //Ans Charge For Package
  $rateinfo[0][13] = $freemin[2]; //Bill Period For Package
  $rateinfo[0][14] = $freemin[3]; //Packageid
  $rateinfo[0][15] = $result[6]; // DIAL COMMAND
  $rateinfo[0][16] = $result[9]; // PROTOCOL
  $rateinfo[0][17] = $result[10]; // PROTOCOL IP
  $rateinfo[0][18] = $result[11]; // PROVPRE
  
  if ($result[7] != "") {
    $agi->set_callerid("\"" . $result[7] . "\"<" . $result[7] . ">");
  }

  $provider=$result[8];

  return $rateinfo;
}

function callingcard_ivr_authorize(){
  global $noivr;
  global $agi;
  global $cbackuse;
  global $resellerbuyperiod;
  global $resellersellperiod;
  global $resellerbuyminperiod;
  global $credit;
  global $resellercredit;
  global $tariff;
  global $destination;
  global $resellerexrate;
  global $resellerminperiod;
  global $rate;
  global $prefix;
  global $newdestination;
  global $countrycode;
  global $subcode;
  global $removeprefix;
  global $resellerrate;
  global $taxrate;
  global $rtaxrate;
  global $freemin;
  global $freeans;
  global $freebill;
  global $freepid;
  global $dialcmd;
  global $timeout;
  global $dialstr;	

  $result = CC_asterisk_authorize($tariff, $destination);

  if (!is_array($result)){
    $agi->stream_file("prepaid/prepaid-dest-unreachable");
    return -1;
  }

  $rate=$result[0][0];
  $prefix = $result[0][2];
  $newdestination = $result[0][4];
  $countrycode = $result[0][5];
  $subcode = $result[0][6];
  $removeprefix = $result[0][7];
  $resellerrate = $result[0][8];
  $taxrate=$result[0][9];
  $rtaxrate=$result[0][10];
  $freemin=$result[0][11];
  $freeans=$result[0][12];
  $freebill=$result[0][13];
  $freepid=$result[0][14];
  $dialcmd = $result[0][15];
  if ($result[0][16] != "") {
    $dialstr = $result[0][16];
  } else {
    $dialstr = "OH323";
  }
  $providerip = $result[0][17];
  $providerpre = $result[0][18];

  if (strncmp($newdestination, $removeprefix, strlen($removeprefix)) == 0) {
    $newdestination=substr($newdestination, strlen($removeprefix));
  }

  if (($rate <= 0) && ($freemin <= 0)){
    $agi->stream_file("prepaid/prepaid-dest-unreachable");
    return -1;
  }

  $rateapply=($rate * $resellerexrate * (1+$taxrate/100))/10000;
  $timeout=floor((3 * $credit * $resellerexrate)/(5 * $rateapply));
 
  if ($timeout > $resellerminperiod) {
    $timeout = $timeout - (($timeout - $resellerminperiod) % $resellersellperiod);
  }

  $rtimeout = floor(($resellercredit * 6000) / ($resellerrate *(1+$rtaxrate/100)));

  if ($rtimeout > $resellerbuyminperiod) {
    $rtimeout = $rtimeout - (($rtimeout - $resellerbuyminperiod) % $resellerbuyperiod);
  }

  if ($rtimeout < $timeout) {
    $timeout=$rtimeout;
    $timeout = $timeout - ($timeout % $resellersellperiod);
  }

  if (($timeout < $resellerminperiod ) || ($rtimeout < $resellerbuyminperiod)) {							
    $agi->stream_file("prepaid/prepaid-no-enough-credit");
    return -1;
  }
 
  if ($freemin > 0) {
    $freemin = $freemin - (($freemin - $freeans) % $freebill);
    $timeout=$freemin+$timeout;
  } else {
    $freemin=0;
  }

  if ($cbackuse == 2) {
    $dialcmd .="CH";
  }


  $timeout=$timeout-2;
  $rtimeou=$rtimeout-2;

  if ($dialstr == "OH323") {
    $dialstr .= "/" . $prefix . $newdestination . "|120|" . $dialcmd . "gD(:C)L("  . $timeout*1000;
  } else {
    $dialstr .= "/" . $providerpre . $newdestination . "@" . $providerip . "|120|" . $dialcmd . "gD(:C)L("  . $timeout*1000;
  }

  if ($timeout >= 60) {
    $dialstr.=":60000:30000)";
  }else if (($timeout < 60) && ($timeout >= 30)) {
    $dialstr.=":30000:15000)";
  }else if (($timeout < 30) && ($timeout >= 15)) {
    $dialstr.=":15000)";
  }else if (($timeout < 15) && ($timeout >= 10)) {
    $dialstr.=":" . $timeout*1000 . ")";
  } else {
    $dialstr.=")";
  }


  if (! $noivr) {
    $minutes = intval($timeout / 60);
    $seconds = $timeout % 60;
    $agi->stream_file("prepaid/prepaid-you-have");
    if ($minutes>0){
      $agi->say_number($minutes);	
      if ($minutes==1){
        $agi->stream_file("prepaid/prepaid-minute");
      }else{
        $agi->stream_file("prepaid/prepaid-minutes");
      }
    }
    if ($seconds>0){
      if ($minutes>0) {
        $agi->stream_file("prepaid/prepaid-and");
      }
      $agi->say_number($seconds);	
      if ($seconds==1){
        $agi->stream_file("prepaid/prepaid-second");
      }else{
        $agi->stream_file("prepaid/prepaid-seconds");
      }
    }
  }
  return 0;
}

function place_call() {
  global $agi;
  global $destination;
  global $dialstr;
  global $cbackuse;
  global $subcode;
  global $countrycode;
  global $uniqueid;
  global $channel;
  global $username;


  $destination=apply_rules($destination);
  if ($destination <= 0){
    $agi->stream_file("prepaid/prepaid-invalid-digits");
    return -1;
  }

  if (callingcard_ivr_authenticate() != 0){
    $status=$agi->stream_file("prepaid/prepaid-auth-fail");
    return -1;
  }
  if (callingcard_ivr_authorize() != 0){
    return -1;
  }

  if ($cbackuse == 0) {
    $inusecnt=1;
  } elseif ($cbackuse == 2) {
    $inusecnt=2;
  } else {
    $inusecnt=0;    
  }

  $QUERY = "UPDATE users SET inuse=inuse+$inusecnt,callocated=callocated+$credit WHERE username='$username'";
  odbcquery($QUERY);

  $starttime=gmdate("Y-m-d H:i:s");

  $agi->exec_dial($dialstr);
  $QUERY = "UPDATE users SET inuse=inuse-$inusecnt,callocated=callocated-$credit WHERE username='$username'";
  odbcquery($QUERY);

  $result=$agi->get_variable("DIALEDTIME");
  if ($result['result'] == 1) {
    $dialedtime=$result['data'];
  } else {
    $dialedtime=0;
  }

  $result=$agi->get_variable("DIALSTATUS");
  if ($result['result'] == 1) {
    $dialstatus=$result['data'];
  } else {
    $dialstatus="CANCEL";
  }

  $result=$agi->get_variable("ANSWEREDTIME");
  if ($result['result'] == 1) {
    $answeredtime=$result['data'];
  } else {
    $answeredtime=0;
  }

  $result=$agi->get_variable("OH323_CALLID");
  if ($result['result'] == 1) {
    $oh323callid=$result['data'];
  } else {
    $oh323callid='';
  }

  verbose("TC: " . $dialstatus . " DT: " . $dialedtime);

  if (!($dialstatus  == "CHANUNAVAIL") && !($dialstatus  == "CONGESTION")) {
    if ($dialstatus  == "BUSY") {
      $agi->exec("Busy","5");
    } elseif ($dialstatus == "NOANSWER") {
      $agi->stream_file("prepaid/prepaid-noanswer");
    }
  } else {
    $agi->exec("Congestion","5");
  }

  callingcard_acct_stop($dialstatus,$dialedtime,$oh323callid,$starttime,$answeredtime);
}

function callingcard_ivr_authenticate( ){
  global $username;
  global $credit;
  global $tariff;
  global $active;		
  global $agi;
  global $resellerid;
  global $resellertariff;
  global $resellercredit;
  global $simuse;
  global $linelimit;
  global $resellersellperiod;
  global $resellerbuyperiod;
  global $resellerminperiod;
  global $resellerbuyminperiod;
  global $resellerexrate;
  global $usertype;
  global $cbackuse;
  global $ivroff;
  global $rowner;
  global $rlevel;

  $res=0;

  if ( !isset($username) || strlen($username) == 0) {
    return -1;
  }

  if ($cbackuse == 1) {
    $usecnt=1;
  } else {
    $usecnt=0;
  }

  $QUERY =  "SELECT users.credit, tariff, activated, inuse, creditcap, simuse," .
            "reseller.credit, agentid, buyrate, buyperiod, sellperiod, exchangerate," .
            "minperiod ,buyminperiod,usertype,rlevel,rcallocated,owner  FROM users " .
            "LEFT OUTER JOIN reseller ON (reseller.id=users.agentid) " .
            "WHERE users.username='$username' AND activated='t' AND reseller.credit > 0" .
            " AND users.inuse < users.simuse+" . $usecnt;

  $result = odbcquery($QUERY);

  if( !is_array($result)) {
    return -1;
  }

  $QUERY = "SELECT sum(callocated) FROM users " .
           "LEFT OUTER JOIN reseller ON (reseller.id=users.agentid) " . 
           "WHERE users.agentid = " . $result[7] . " GROUP BY users.agentid";
  $usercount = odbcquery($QUERY);
  if( !is_array($usercount)) {
    return -1;
  }

  $raloccred=$usercount[1];
  $resellertariff=$result[8];
  $resellerid=$result[7];
  $resellerbuyperiod=$result[9];
  $resellersellperiod=$result[10];
  $resellerexrate=$result[11];
  $resellerminperiod=$result[12];
  $resellerbuyminperiod=$result[13];
  $rlevel=$result[15];
  $rcredalloc=$result[16]/100;
  $rowner=$result[17];
  $resellercredit=$result[6]/100;

  if ($rcredalloc > $resellercredit) {
    $rcratio=$resellercredit/$rcredalloc;
  } else {
    $rcratio=1;
  }

  $rownert=$rowner;
/*
  for ($reslev=$rlevel-1;$reslev >= 0;$reslev--) {
    $GETOCRED="SELECT credit,rcallocated,owner FROM reseller WHERE rlevel=$reslev AND id=$rownert";
    $getcredratio = $instance_table -> SQLExec ($DBHandle, $GETOCRED);
    if ($getcredratio[0][0] < $getcredratio[0][1]) {
      $rcratio=$rcratio*($getcredratio[0][0]/$getcredratio[0][1]);
    }
    $rownert=$getcredratio[0][2];
  }
  $resellercredit=($resellercredit*$rcratio)-$raloccred;
*/

  $simuse=$result[5];
  if ($cbackuse == 1) {
    $credit = $result[0]/(($simuse+1)*100);
  } else {
    $credit = $result[0]/($simuse*100);
  }
  if ($cbackuse == 2) {
    $simuse++;
  }
  $linelimit = $result[4]/$resellerexrate;

  if (($linelimit < $credit) && (linelimit > 0)) {
    $credit=$linelimit;
  }

  if ($resellercredit < $credit) {
    $credit=$resellercredit;
  }

  $usertype = $result[14];
  $tariff = $result[1];
  $active = $result[2];
  $isused = $result[3];

  if( $credit <= 0 ) {
    $prompt = "prepaid/prepaid-zero-balance";
    $res = -2;
  }
  if(!$active) {
    $prompt = "prepaid/prepaid-card-expired";
    $res = -2;
  }
  if ($isused > $simuse){
    $prompt="prepaid/prepaid-card-in-use";
    $res = -2;
  }
  if ($res == 0) {
    $dollars = intval($credit / 100);
    $cents = $credit % 100;
  } else if ($res == -2 ) {
    $agi->stream_file("$prompt");
  }else{
   $res = -1;
  }
  return $res;
}

/*still to do*/

function callingcard_acct_stop($dialstatus,$dialedtime,$oh323callid,$starttime,$answeredtime){
  global $agi;
  global $username;
  global $channel;	
  global $uniqueid;
  global $destination;
  global $rate;
  global $countrycode;
  global $subcode;
  global $psqlacct_stop;
  global $resellerid;
  global $resellercredit;
  global $resellerrate;
  global $resellerbuyperiod;
  global $resellersellperiod;
  global $freemin;
  global $freeans;
  global $freebill;
  global $freepid;
  global $credit;
  global $resellerexrate;
  global $resellerminperiod;
  global $resellerbuyminperiod;
  global $taxrate;
  global $rtaxrate;
  global $usertype;
  global $rlevel;
  global $tariff;
  global $rowner;
  global $provider;

  if ($oh323callid != "") {
    $GETATIME="SELECT callduration from h323cdr where callid=replace('$oh323callid','-','') ORDER BY callduration DESC LIMIT 1";
    sleep(1);
    $atime=odbcquery($GETATIME);
    while(!is_array($atime)) {
      sleep(1);
      $atime=odbcquery($GETATIME);
    }
    $answeredtime=$atime[0];
  } else {
    $oh323callid=$agi->request['agi_uniqueid'];
  }

  if ($freemin >= $answeredtime) {
    $freetime=$answeredtime;
    $billtime=0;
  } else {
    $freetime=$freemin;
    $billtime=$answeredtime-$freemin;
  }

 if ($dialstatus != "ANSWER") {
   $answeredtime=0;
   $billtime=0;
 }

  $anstime=$billtime;

  if (($freetime > 0) && ($freetime <= $freeans)) {
    $freetime=$freeans;
  } else if ((($freetime - $freeans) % $freebill) > 0) {
    $freetime=$freetime - (($freetime - $freeans) % $freebill) + $freebill;
  }

  if (($billtime > 0) && ($billtime <= $resellerminperiod)) {
    $billtime=$resellerminperiod;
  } else if (((($billtime - $resellerminperiod) % $resellersellperiod) > 0) && ($billtime > 0)){
    $billtime=$billtime - (($billtime - $resellerminperiod) % $resellersellperiod) + $resellersellperiod;
  }

  $rateapply=($rate * $resellerexrate)/10000;

  if (($anstime > 0) && ($anstime <= $resellerbuyminperiod)) {
    $anstime=$resellerbuyminperiod;
  } else if (((($anstime - $resellerbuyminperiod) % $resellerbuyperiod) > 0) && ($anstime > 0)){
    $anstime=$anstime - (($anstime - $resellerbuyminperiod) % $resellerbuyperiod) + $resellerbuyperiod;
  }

  $racredit_used=ceil(($rate*$billtime*(1+$taxrate/100))/60);
  $credit_used=($racredit_used*$resellerexrate)/100;

  if (($oh323callid != "") && ($dialstatus == "ANSWER")){
    $QUERY="INSERT INTO call (uniqueid,sessionid,username,calledstation,".
                             "calledcountry,calledsub,starttime,stoptime,".
                             "totaltime,ringtime,callduration,oh323callid,".
                             "terminatecause,sessiontime,calledrate,".
                             "sessionbill,usertariff,calledprovider,stopdelay) ".
                 "SELECT '$uniqueid','$channel','$username','$destination',".
                        "'$countrycode','$subcode','" . $starttime . "',".
                        "disconnecttime,extract(EPOCH from disconnecttime-setuptime),".
                   "extract(EPOCH from connecttime-setuptime),callduration,".
                   "callid,'$dialstatus',$freetime+$billtime,'$rateapply',".
                   "'$credit_used','$tariff','" . $provider . "',$billtime - $anstime ".
              "from h323cdr where callid=replace('$oh323callid','-','') ORDER BY callduration DESC LIMIT 1";
  } else {
    $QUERY="INSERT INTO call (uniqueid,sessionid,username,calledstation,".
                             "calledcountry,calledsub,starttime,stoptime,".
                             "totaltime,ringtime,callduration,oh323callid,".
                             "terminatecause,sessiontime,calledrate,".
                             "sessionbill,usertariff,calledprovider,stopdelay) ".
                 "SELECT '$uniqueid','$channel','$username','$destination',".
                   "'$countrycode','$subcode','$starttime',now(),".
                   "extract(epoch from now()-'$starttime'),".
                   "extract(epoch from now()-'$starttime'),".
                   "$dialedtime,'','$dialstatus',$freetime+$billtime,'".
                   "$rateapply','$credit_used','$tariff',".
                   "'$provider',$billtime - $anstime";
  }
  odbcquery($QUERY);

  if ($racredit_used > 0) {
    $credit = $credit - $credit_used;
    $QUERY = "UPDATE users SET credit=(credit - $racredit_used) WHERE username='$username'";
    odbcquery($QUERY);
    verbose("CALL U QUERY: " .  $QUERY);
  }

  if ($freemin > 0) {
    $QUERY="UPDATE package SET freemin=freemin-$freetime WHERE id=$freepid";
    odbcquery($QUERY);
  }

  if (($anstime > 0) && ($anstime <= $resellerbuyminperiod)) {
    $ranstime=$resellerbuyminperiod;
  } else if ((($anstime - $resellerbuyminperiod) % $resellerbuyperiod) > 0) {
    $ranstime=$anstime - (($anstime - $resellerbuyminperiod) % $resellerbuyperiod) + $resellerbuyperiod;
  } else {
    $ranstime=$anstime;
  }
  
  $rcredit_used = ceil(($resellerrate * $ranstime * (1+$rtaxrate/100))/60);

  if ($rcredit_used > 0) {
    $QUERY = "UPDATE reseller SET credit=(credit - $rcredit_used) WHERE id='$resellerid'";
    odbcquery($QUERY);
    verbose("CALL R QUERY: " .  $QUERY);
    $QUERY = "UPDATE reseller SET rcallocated=(rcallocated - $racredit_used) WHERE id='$resellerid'";
    odbcquery($QUERY);
    verbose("CALL RA QUERY: " .  $QUERY);
  }

  $outputtax=ceil(($credit_used - $credit_used/(1+$taxrate/100))*100);
  $inputtax=ceil($rcredit_used*$resellerexrate - ($rcredit_used*$resellerexrate)/(1+$rtaxrate/100));


  $QUERY = "INSERT INTO resellercall VALUES ('$uniqueid','$resellerid','$resellerexrate','$rcredit_used','$resellerrate','$inputtax','$outputtax','$credit_used','$rate','$racredit_used')";
  odbcquery($QUERY);

  $rownert=$rowner;
  $oldrate=$rate;
  $cred_last=$rcredit_used*$resellerexrate/100;
  for ($reslev=$rlevel;$reslev > 0;$reslev--) {
    $GETOCRED="SELECT buyrate,buyperiod,exchangerate,minperiod,owner FROM reseller WHERE id=$rownert";
    $getrlevelcall=odbcquery($GETOCRED);
    $rownertnew=$getrlevelcall[4];
    $rbuymin=$getrlevelcall[3];
    $rexrate=$getrlevelcall[2];
    $rbuy=$getrlevelcall[1];
    $rtariff=$getrlevelcall[0];

    $GETRTAR="SELECT rate,tax,minrate FROM tariffrate LEFT OUTER JOIN tariff USING (tariffcode) WHERE countrycode='$countrycode' AND subcode='$subcode' AND tariffcode='$rtariff'";

    $rtarresult=odbcquery($GETRTAR);

    $rrate=$rtarresult[0];
    $rtax=$rtarresult[1];
    $rmin=$rtarresult[2];

    if ($rmin > $rrate) {
      $rrate=$rmin;
    }

    if (($anstime > 0) && ($anstime <= $rbuymin)) {
      $ranstime=$rbuymin;
    } else if ((($anstime - $rbuymin) % $rbuy) > 0) {
      $ranstime=$anstime - (($anstime - $rbuymin) % $rbuy) + $buy;
    } else {
      $ranstime=$anstime;
    }

    $rcused = ceil(($rrate * $ranstime * (1+$rtax/100)) / 60);
    $QUERY = "UPDATE reseller SET credit=(credit - $rcused) WHERE id='$rownert'";
    odbcquery($QUERY);

    $QUERY = "UPDATE reseller SET rcallocated=(rcallocated - $rcredit_used) WHERE id='$rownert'";
    odbcquery($QUERY);

    $outputtax=ceil(($cred_last - $cred_last/(1+$taxrate/100))*100);
    $inputtax=ceil($rcused*$rexrate - ($rcused*$rexrate)/(1+$rtax/100));

    $QUERY = "INSERT INTO resellercall VALUES ('$uniqueid','$rownert','$rexrate','$rcused','$rrate','$inputtax','$outputtax','$cred_last','$oldrate','$rcredit_used')";
    odbcquery($QUERY);

    $cred_last=$rcused*$rexrate/100;
    $rcredit_used=$rcused;
    $taxrate=$rtax;
    $rownert=$rownertnew;
    $oldrate=$rrate;
  }
}

/*
  if (($tech != "H323") && ($tech != "GSM")) {
    $dialstr = "$tech/$ipaddress/$prefix$newdestination".$dialparams;
  } elseif ($tech == "GSM") {
    $QUERY =  "SELECT DISTINCT id,'IAX2/'||interface||'/'||'*'||channel||'*',calltime,expires FROM gsmchannels WHERE NOT inuse AND NOT outofservice AND (calltime >= " . $timeout . "  OR (calltime > 1200 AND calltime < 15000)) AND calltime > 1200 AND expires > NOW() ORDER BY expires,calltime LIMIT 1";
    $result = $instance_table -> SQLExec ($DBHandle, $QUERY);
    if( !is_array($result)) {
      $QUERY =  "SELECT DISTINCT id,'IAX2/'||interface||'/'||'*'||channel||'*',calltime,expires FROM gsmchannels WHERE NOT inuse AND NOT outofservice AND calltime <= " . $timeout . " AND calltime > 1200 AND expires > NOW() ORDER BY expires,calltime DESC LIMIT 1";
      $result = $instance_table -> SQLExec ($DBHandle, $QUERY);
      if( !is_array($result)) {
        $agi->stream_file("prepaid/prepaid-dest-unreachable");
        return -1;
      }
    }
    $chanid=$result[0][0];

    $QUERY="UPDATE gsmchannels SET inuse='t' WHERE id=" . $chanid;
    $setinuse = $instance_table -> SQLExec ($DBHandle, $QUERY);

    if ($timeout > $result[0][2]-1020) {
      $dialparams = str_replace("%timeout%", ($result[0][2]-1020)*1000, DIALCOMMAND_PARAM);
    }
    $dialstr = $result[0][1] . $prefix . $newdestination . $dialparams;
  } else {
    $dialstr = "O$tech/$prefix$newdestination".$dialparams;
  }

---

    if ($tech == "GSM") {
     sleep(3); 
     if ($answeredtime != "") {
        $QUERY="UPDATE gsmchannels SET calltime=calltime-" . $answeredtime . ",inuse='f' WHERE id=" . $chanid;
      } else {
        $QUERY="UPDATE gsmchannels SET inuse='f' WHERE id=" . $chanid;
      }
      $setinuse = $instance_table -> SQLExec ($DBHandle, $QUERY);
    }
*/
?>
