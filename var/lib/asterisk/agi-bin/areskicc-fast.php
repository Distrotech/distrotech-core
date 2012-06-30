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

if (! isset($agi)) {
  require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi.php");
  $agi=new AGI();
}

include "/var/lib/asterisk/agi-bin/functions.inc";

$stat_channel = $agi->channel_status($channel);

/*
Declare all global variables.
*/
$GLOBALS['active']='';
$GLOBALS['agi']=$agi;
$GLOBALS['cbackuse']=0;
$GLOBALS['channel']=$agi->request['agi_channel'];
$GLOBALS['chanstat']=$stat_channel['result'];
$GLOBALS['countrycode']='';
$GLOBALS['credit']=0;
$GLOBALS['destination']=$destination;
$GLOBALS['dialcmd']='';
$GLOBALS['dialproto']='';
$GLOBALS['dialstr']='';
$GLOBALS['freeans']='';
$GLOBALS['freebill']='';
$GLOBALS['freemin']='';
$GLOBALS['freepid']='';
$GLOBALS['h323gwid']=$h323gwid;
$GLOBALS['h323prefix']=$h323prefix;
$GLOBALS['h323neighbor']='';
$GLOBALS['ivroff']='';
$GLOBALS['linelim']='';
$GLOBALS['newdestination']='';
$GLOBALS['noivr']=0;
$GLOBALS['oh323callid']='';
$GLOBALS['provider']='';
$GLOBALS['psqlacct_stop']='';
$GLOBALS['rate']='';
$GLOBALS['removeprefix']='';
$GLOBALS['resellerbuyminperiod']='';
$GLOBALS['resellerbuyperiod']='';
$GLOBALS['resellercredit']='';
$GLOBALS['resellerexrate']='';
$GLOBALS['resellerid']='';
$GLOBALS['resellerminperiod']='';
$GLOBALS['resellerrate']='';
$GLOBALS['resellersellperiod']='';
$GLOBALS['resellertariff']='';
$GLOBALS['rlevel']='';
$GLOBALS['rowner']='';
$GLOBALS['rtaxrate']='';
$GLOBALS['simuse']='';
$GLOBALS['subcode']='';
$GLOBALS['tariff']='';
$GLOBALS['taxrate']='';
$GLOBALS['timeout']='';
$GLOBALS['uniqueid']=$agi->request['agi_uniqueid'];
$GLOBALS['uniquecdrid']=$GLOBALS['uniqueid'];
$GLOBALS['username']=$username;
$GLOBALS['usertype']='';

if (substr($GLOBALS['channel'],0,4) == "SIP/") {
  $agi->answer();
  if ($GLOBALS['username'] == "") {
    $GLOBALS['username']=substr($GLOBALS['channel'],4,8);
  }
} else if (substr($GLOBALS['channel'],0,5) == "IAX2/") {
  $GLOBALS['noivr']=1;
  if ($GLOBALS['username'] == "") {
    $GLOBALS['username']=substr($GLOBALS['channel'],5,8);
  }
} else if ((substr($GLOBALS['channel'],0,6) == "OH323/") && ($GLOBALS['username'] == "")) {
  $GLOBALS['noivr']=1;
  list($proto,$epid,$ipinf)=preg_split("/[\/@]/",$GLOBALS['channel']);
  list($ipaddr)=split("-",$ipinf);
  $vresult=$agi->get_variable("OH323_CALLID");
  if ($vresult['result'] == 1) {
    $GLOBALS['oh323callid']=$vresult['data'];
  } else {
    $GLOBALS['oh323callid']='';
  }

  $authq="SELECT name,substr('" . $GLOBALS['destination'] . "',length(h323prefix)+1),h323neighbor from users where (name = '" . $epid . "' OR h323gkid='" . $epid . "') AND ".
                                                         "(h323permit = '" . $ipaddr . "' OR h323permit = 'allow' OR h323permit ='') AND ".
                                                         "substr('" . $GLOBALS['destination'] . "',1,length(h323prefix)) = h323prefix ORDER BY length(h323prefix) DESC LIMIT 1";
  $h323user=odbcquery($authq);
  if (!is_array($h323user)) {
     $authn="SELECT name,substr('" . $GLOBALS['destination'] . "',length(h323prefix)+1),h323neighbor from users ".
                     "left outer join h323cdr on (callid=replace('" . $GLOBALS['oh323callid'] . "','-','')) ".
                   "where (name =callingstationid OR h323gkid=callingstationid OR h323cdr.username = h323gkid OR name = '" . $epid . "' OR h323gkid = '" . $epid . "' )  AND ".
                         "(h323permit = callerip OR h323permit = 'allow' OR h323permit ='') AND ".
                         "substr('" . $GLOBALS['destination'] . "',1,length(h323prefix)) = h323prefix ".
                       "ORDER BY length(h323prefix) DESC LIMIT 1;";

    $h323user=odbcquery($authn);
  }
  if (is_array($h323user)) {
    $GLOBALS['username']=$h323user[0];
    $GLOBALS['destination']=$h323user[1];
    $GLOBALS['h323neighbor']=$h323user[2];
  }
} else if (substr($GLOBALS['channel'],0,7) == "Local/*") {
  if ($GLOBALS['chanstat'] == 6) {
    $GLOBALS['cbackuse']=2;
    $GLOBALS['noivr']=0;
    $GLOBALS['subcall']=0;
    $GLOBALS['uniqueid']=$GLOBALS['uniqueid']*10000;
  } else {
    $GLOBALS['noivr']=1;
    $GLOBALS['cbackuse']=1;
  }
  if ($GLOBALS['username'] == "") {
    $GLOBALS['username']=substr($GLOBALS['channel'],7,8);
  }
}

if ($GLOBALS['destination'] == '') {
  $GLOBALS['destination']=$agi->request['agi_extension'];
}
if ($GLOBALS['destination'] == "s") {
  $GLOBALS['destination']=getivrdest($GLOBALS['username']);
}

place_call();

$stat_channel = $agi->channel_status($GLOBALS['channel']);
$GLOBALS['chanstat']=$stat_channel['result'];
while(($GLOBALS['chanstat'] == 6) && ($GLOBALS['subcall'] < 10)){
  $GLOBALS['destination']=getivrdest($GLOBALS['username']);
  $GLOBALS['subcall']++;
  $GLOBALS['uniqueid']++;
  place_call();
  $stat_channel = $agi->channel_status($channel);
  $GLOBALS['chanstat']=$stat_channel['result'];
}

function CC_asterisk_authorize($tariffcode, $phonenumber){
  global $resellertariff;
  global $username;	
  global $h323prefix;
  global $provider;
  global $tariff;
  global $agi;
  global $h323gwid;
  global $h323neighbor;

  $rateinfo = array();

  if ($h323neighbor == "t") {
    $CCQUERY="SELECT countrycode, subcode FROM countryprefix WHERE prefix=SUBSTRING('" . $phonenumber . "',1,length(prefix)) ORDER BY LENGTH(prefix) DESC LIMIT 1";
    $ccresult=odbcquery($CCQUERY);
    if (!is_array($ccresult)) {
      return -1;
    } else {
      $qresult=array();
      $qresult[1]=$ccresult[0];
      $qresult[2]=$ccresult[1];
      if ($qresult[1] == 'ZAF') {
        $LOCQUERY="SELECT CASE WHEN(to_char(now(), 'HH24:MI:SS') >= peakstart AND to_char(now(), 'HH24:MI:SS') <= peakend AND extract('dow' FROM now()) ~ peakdays ) THEN peakmin||':'||peakperiod||':'||peaksec ELSE offpeakmin||':'||offpeakperiod||':'||offpeaksec END,description,index FROM localrates where '0" . substr($phonenumber,2) . "' ~ match";
        $localrate=odbcquery($LOCQUERY);
      } else {
        $LOCQUERY="SELECT CASE WHEN(to_char(now(), 'HH24:MI:SS') >= peakstart AND to_char(now(), 'HH24:MI:SS') <= peakend AND extract('dow' FROM now()) ~ peakdays ) THEN peakmin||':'||peakperiod||':'||peaksec ELSE offpeakmin||':'||offpeakperiod||':'||offpeaksec END,'Telkom',-1 FROM localrates where countrycode = '" . $qresult[1] . "' AND subcode = '" . $qresult[2] . "'";
        $localrate=odbcquery($LOCQUERY);
      }
      list($locmin,$locperiod,$locrate)=split(":",$localrate[0]);
      $freemin[0]=0;
      $freemin[1]=ceil($locmin/$locrate);
      $freemin[2]=$locperiod;
      $freemin[3]='';

      $qresult[0]=$locrate;
      $tariff=$localrate[2];
      $qresult[3]='';
      $qresult[4]='';
      $qresult[5]=0;
      $qresult[8]=$localrate[1];
      $qresult[9]="LOCAL";
      $result[0]=0;
      $result[1]=0;
    }
  } else {
    $GKQUERY=("SELECT h323gkid from users where name='" . $h323gwid . "'");
    $gkidq=odbcquery($GKQUERY);
    $h323gkid=$gkidq[0];
  
    $QUERY = "SELECT rate,countrycode,subcode,lpad(provider.trunkprefix,7,'0'),removeprefix,tax,dialcmd,callerid,provider.name,trunk.protocol," .
                     "trunk.providerip,trunk.h323prefix,trunk.h323gkid FROM countryprefix LEFT JOIN tariffrate ".
  	   "USING (countrycode,subcode) LEFT JOIN provider USING (trunkprefix) LEFT OUTER JOIN trunk ON (trunk.trunkprefix=provider.trunkprefix AND h323reggk = '$h323gkid') LEFT OUTER JOIN tariff ON (tariff.tariffcode=tariffrate.tariffcode) ".
           "WHERE ".
	   "tariffrate.tariffcode='$tariffcode' AND prefix=SUBSTRING('$phonenumber',1,length(prefix)) ".
	   "ORDER BY LENGTH(prefix) DESC LIMIT 1;";
    $qresult=odbcquery($QUERY);

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
                      "users.username='$username' AND countrycode ='" . $qresult[1] . "' AND " .
                      "subcode = '" . $qresult[2] . "' AND activedate+expiredays > localtimestamp AND " .
                      "freemin/simuse > ansperiod " .   
                      "ORDER BY activedate LIMIT 1;";
      $freemin=odbcquery($QUERY);
    if ($rresult[2] > $qresult[0]) {
      $qresult[0]=$rresult[2];
    }
  }

  $rateinfo[0]= $qresult[0]; // RATE
  if ($h323prefix == "") {
    $rateinfo[2]= $qresult[3]; // PREFIX
  } else {
    $rateinfo[2]=str_pad($h323prefix,7,"0",STR_PAD_LEFT);
  }
  $rateinfo[4]= $phonenumber;
  $rateinfo[5]= $qresult[1]; // COUNTRYCODE
  $rateinfo[6]= $qresult[2]; // SUBCODE
  $rateinfo[7]= $qresult[4]; // REMOVEPREFIX
  $rateinfo[8]= $rresult[0]; // RESELER RATE
  $rateinfo[9]= $qresult[5]; // TAX RATE

  $rateinfo[10] = $rresult[1]; // RESELER TAX RATE
  $rateinfo[11] = $freemin[0]; //Minutes Available On Package
  $rateinfo[12] = $freemin[1]; //Ans Charge For Package
  $rateinfo[13] = $freemin[2]; //Bill Period For Package
  $rateinfo[14] = $freemin[3]; //Packageid

  $rateinfo[15] = $qresult[6]; // DIAL COMMAND

  if ($qresult[7] != "") {
    $agi->set_callerid("\"" . $qresult[7] . "\"<" . $qresult[7] . ">");
  }
  $provider=$qresult[8];

  if (($qresult[12] == $h323gwid) && ($rateinfo[18] == "*")) {
    $rateinfo[16] = "GSM";
  } else {
    $rateinfo[16] = $qresult[9]; // PROTOCOL
  }

  $rateinfo[17] = $qresult[10]; // PROTOCOL IP
  $rateinfo[18] = $qresult[11]; // PROVPRE

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
  global $resellerid;
  global $tariff;
  global $destination;
  global $resellerexrate;
  global $resellerminperiod;
  global $rate;
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
  global $dialproto;	
  global $h323neighbor;

  $aresult = CC_asterisk_authorize($tariff, $destination);

  if (!is_array($aresult)){
    $agi->stream_file("prepaid/prepaid-dest-unreachable");
    return -1;
  }

  $rate=$aresult[0];
  $prefix = $aresult[2];
  $newdestination = $aresult[4];
  $countrycode = $aresult[5];
  $subcode = $aresult[6];
  $removeprefix = $aresult[7];
  $resellerrate = $aresult[8];
  $taxrate=$aresult[9];
  $rtaxrate=$aresult[10];
  $freemin=$aresult[11];
  $freeans=$aresult[12];
  $freebill=$aresult[13];
  $freepid=$aresult[14];
  $dialcmd = $aresult[15];
  if ($aresult[16] != "") {
    $dialproto = $aresult[16];
  } else {
    $dialproto = "OH323";
  }
  $providerip = $aresult[17];
  $providerpre = $aresult[18];

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

  if ((($timeout < $resellerminperiod ) || ($rtimeout < $resellerbuyminperiod)) && ($h323neighbor != "t")){
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

  if ((($timeout < 5 ) || ($rtimeout < 5)) && ($h323neighbor != "t")) {
    $agi->stream_file("prepaid/prepaid-no-enough-credit");
    return -1;
  }

  if ($dialproto == "OH323") {
    $dialstr = $prefix . $newdestination . "|120|" . $dialcmd . "CrgD(:C)";
    if ($timeout > 0 ) {
      $dialstr = $dialstr  . "L("  . $timeout*1000;
    }
  } else if ($dialproto == "GSM") {
    $dialstr = "0" . substr($newdestination,2);
  } else if (($dialproto == "LOCAL") && ($h323neighbor == "t")) {
    $dialproto=rtdbget("Setup","Trunk");
    $dialproto=substr($dialproto,0,strlen($dialproto)-1);
    $dialstr = "0" . substr($newdestination,2) . "|120|CwgtD(:C)";
    if ($timeout > 0 ) {
      $dialstr = $dialstr  . "L("  . $timeout*1000;
    }
  } else {
    $dialstr = $providerpre . $newdestination . "@" . $providerip . "|120|" . $dialcmd . "rgCD(:C)L("  . $timeout*1000;
    if ($timeout > 0 ) {
      $dialstr = $dialstr  . "L("  . $timeout*1000;
    }
  }

  
  if (($dialproto != "GSM") && ($timeout > 0)) {
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
  }

  if (! $noivr) {
    sayminutes($timeout);
  }
  return 0;
}

function place_call() {
  global $destination,$dialproto,$credit,$dialstr,$cbackuse,$subcode,$countrycode,$uniqueid,$channel,$username,$agi,$timeout,$h323gwid;
  global $h323neighbor,$tariff;

  $destination=apply_rules($destination);

  if ($destination <= 0) {
    $agi->stream_file("prepaid/prepaid-invalid-digits");
    return -1;
  } else {
    $agi->set_variable("CDR(userfield)",$destination);
  }

  if ($h323neighbor != "t") {
    $authenticate=callingcard_ivr_authenticate();
    if ($authenticate == -1){
      $status=$agi->stream_file("prepaid/prepaid-auth-fail");
      return -1;
    } else if ($authenticate < 0){
      return -1;
    }
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

  if ($credit > 0) {
    $QUERY = "UPDATE users SET inuse=inuse+$inusecnt,callocated=callocated+$credit WHERE username='$username'";
    odbcquery($QUERY);
  }

  $starttime=date("Y-m-d H:i:s");
  verbose("Tarriff" . $tariff);

  if ($dialproto != "GSM") {
    $agi->exec_dial($dialproto,$dialstr);
    $agi->exec("ResetCDR","w");
  } else {
    gsm_call($h323gwid,$dialstr,$timeout);
  }

  if ($credit > 0) {
    $QUERY = "UPDATE users SET inuse=inuse-$inusecnt,callocated=callocated-$credit WHERE username='$username'";
    odbcquery($QUERY);
  }

  $vresult=$agi->get_variable("DIALEDTIME");
  if ($vresult['result'] == 1) {
    $dialedtime=$vresult['data'];
  } else {
    $dialedtime=0;
  }

  $vresult=$agi->get_variable("DIALSTATUS");
  if ($vresult['result'] == 1) {
    $dialstatus=$vresult['data'];
  } else {
    $dialstatus="CANCEL";
  }

  $vresult=$agi->get_variable("ANSWEREDTIME");
  if ($vresult['result'] == 1) {
    $answeredtime=$vresult['data'];
  } else {
    $answeredtime=0;
  }

  $vresult=$agi->get_variable("OH323_CALLID");
  if ($vresult['result'] == 1) {
    $oh323calluid=$vresult['data'];
  } else {
    $oh323calluid='';
  }

  if (!($dialstatus  == "CHANUNAVAIL") && !($dialstatus  == "CONGESTION")) {
    if ($dialstatus  == "BUSY") {
      $agi->exec("Busy","5");
    } elseif ($dialstatus == "NOANSWER") {
      $agi->stream_file("prepaid/prepaid-noanswer");
    }
  } else {
    $agi->exec("Congestion","5");
  }

  callingcard_acct_stop($dialstatus,$dialedtime,$oh323calluid,$starttime,$answeredtime);
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
  global $linelim;
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

  $CPHONE="SELECT value from astdb where family='CardPhone' and key='" . $username . "'";
  $cpresult = odbcquery($CPHONE);
  if(is_array($cpresult)) {
    if ($cpresult[0] == 1) { 
      $fcardno=$agi->get_data("prepaid/prepaid-pls-enter-card-no",6000,12);
      $fpinno=$fcardno['result'];
      if (!isset($username) || strlen($username) == 0) {
        return -1;
      }
      $username=substr($fpinno,0,8);
      $password=substr($fpinno,8,4);

      $CPHONE="SELECT id from users where secret='" . $password . "' and name='" . $username . "'";
      $cpresult = odbcquery($CPHONE);
      if(!is_array($cpresult)) {
        return -1;
      }
    }
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
            "WHERE users.username='$username' AND activated='t' AND reseller.credit > 0 " .
            "AND users.inuse < users.simuse+" . $usecnt . "";

  $qresult = odbcquery($QUERY);
  if( !is_array($qresult)) {
    return -1;
  }

  $QUERY = "SELECT sum(callocated) FROM users " .
           "LEFT OUTER JOIN reseller ON (reseller.id=users.agentid) " . 
           "WHERE users.agentid = " . $qresult[7] . " GROUP BY users.agentid";
  $usercount = odbcquery($QUERY);
  if( !is_array($usercount)) {
    return -1;
  }

  $raloccred=$usercount[1];
  $resellertariff=$qresult[8];
  $resellerid=$qresult[7];
  $resellerbuyperiod=$qresult[9];
  $resellersellperiod=$qresult[10];
  $resellerexrate=$qresult[11];
  $resellerminperiod=$qresult[12];
  $resellerbuyminperiod=$qresult[13];
  $rlevel=$qresult[15];
  $rcredalloc=$qresult[16]/100;
  $rowner=$qresult[17];
  $resellercredit=$qresult[6]/100;

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

  $simuse=$qresult[5];
  if ($cbackuse == 1) {
    $credit = $qresult[0]/(($simuse+1)*100);
  } else {
    $credit = $qresult[0]/($simuse*100);
  }
  if ($cbackuse == 2) {
    $simuse++;
  }
  $linelim = $qresult[4]/$resellerexrate;

  if (($linelim < $credit) && (linelim > 0)) {
    $credit=$linelim;
  }

  $usertype = $qresult[14];
  $tariff = $qresult[1];
  $active = $qresult[2];
  $isused = $qresult[3];

  if (($resellercredit < $credit) && ($resellerid != 0)) {
    $credit=$resellercredit;
  }

  if ( $credit <= 0 ) {
    $prompt = "prepaid/prepaid-zero-balance";
    $res = -2;
  } else if(!$active) {
    $prompt = "prepaid/prepaid-card-expired";
    $res = -2;
  } else if ($isused > $simuse){
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

function callingcard_acct_stop($dialstatus,$dialedtime,$oh323calluid,$starttime,$answeredtime){
  global $agi;
  global $username;
  global $channel;	
  global $uniqueid;
  global $uniquecdrid;
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
  global $h323neighbor;
  global $oh323callid;

  if ($oh323calluid != "") {
    $GETATIME="SELECT callduration from h323cdr where callid=replace('$oh323calluid','-','') ORDER BY callduration DESC LIMIT 1";
    sleep(2);
    $atime=odbcquery($GETATIME);
    if (!is_array($atime) || (($atime[0] == 0) && ($answeredtime != 0))) {
      for($cnt=0;$cnt < 10;$cnt++) {
        $atime=odbcquery($GETATIME);
        sleep(2);
        if (is_array($atime) && (($atime[0] > 0) || ($answeredtime == 0))) {
          break;
        }
      }
    }
    $answeredtime=$atime[0];
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


  if (($oh323calluid != "") && ($dialstatus == "ANSWER")){
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
              "from h323cdr where callid=replace('$oh323calluid','-','') ORDER BY callduration DESC LIMIT 1";
  } else if (($oh323calluid == "") && ($dialstatus == "ANSWER")){
    $QUERY="INSERT INTO call (uniqueid,sessionid,username,calledstation,".
                             "calledcountry,calledsub,starttime,stoptime,".
                             "totaltime,ringtime,callduration,oh323callid,".
                             "terminatecause,sessiontime,calledrate,".
                             "sessionbill,usertariff,calledprovider,stopdelay) ".
                 "SELECT '$uniqueid','$channel','$username','$destination',".
                        "'$countrycode','$subcode',calldate,calldate + interval '". $dialedtime . " seconds',".
                        "duration,duration-billsec,duration,uniqueid,'$dialstatus',$freetime+$billtime,'$rateapply',".
                   "'$credit_used','$tariff','" . $provider . "',$billtime - $anstime ".
              "from cdr where uniqueid='" . $uniquecdrid . "' ORDER BY calldate desc LIMIT 1";
  } else {
    $QUERY="INSERT INTO call (uniqueid,sessionid,username,calledstation,".
                             "calledcountry,calledsub,starttime,stoptime,".
                             "totaltime,ringtime,callduration,oh323callid,".
                             "terminatecause,sessiontime,calledrate,".
                             "sessionbill,usertariff,calledprovider,stopdelay) ".
                 "VALUES ('$uniqueid','$channel','$username','$destination',".
                   "'$countrycode','$subcode','$starttime',localtimestamp,".
                   "extract(epoch from localtimestamp-'$starttime'),".
                   "extract(epoch from localtimestamp-'$starttime')-$anstime,".
                   "$dialedtime,'$uniquecdrid','$dialstatus',$freetime+$billtime,'".
                   "$rateapply','$credit_used','$tariff',".
                   "'$provider',$billtime - $anstime)";
  }
  odbcquery($QUERY);

  if ($racredit_used > 0) {
    $credit = $credit - $credit_used;
    $QUERY = "UPDATE users SET credit=(credit - $racredit_used) WHERE username='$username'";
    odbcquery($QUERY);
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
    $QUERY = "UPDATE reseller SET rcallocated=(rcallocated - $racredit_used) WHERE id='$resellerid'";
    odbcquery($QUERY);
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
?>
