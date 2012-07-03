<?php
/*
#    Copyright (C) 2002  <Gregory Hinton Nietsky>
#    Copyright (C) 2005  <ZA Telecomunications>
#    Copyright (C) 2008  <DnS Telecom>
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

/*Read Global Variables Set From AGI URL*/
$GLOBALS['destination']=makenumint($destination);
$GLOBALS['username']=$username;
$GLOBALS['h323gwid']=$h323gwid;
$GLOBALS['h323prefix']=$h323prefix;

if ($agi->request['agi_extension'] != "h") {
  $callstat=place_call();
  if ($callstat < 0) {
    $agi->exec("Congestion","5");
  }
} else {
  account_call($agi->request['agi_uniqueid']);
}

/*
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
*/

/*

Determine the username and destination

*/

function place_call() {
  global $destination,$credit,$dialstr,$cbackuse,$username,$agi,$timeout,$h323gwid,$h323neighbor,$tariff,$noivr;

  getuserdest();

  //authenticate non neighbor calls
  if ($h323neighbor != "t") {
    $reseller=authenticate_call($reseller);
    if ($noivr == "0") {
      //Answer SIP calls to enable the IVR
      $agi->answer();
    }
    if (!is_array($reseller)) {
      if ($reseller == -1){
        if ($noivr == "0") {
          $status=$agi->stream_file("your-account");
          $status=$agi->stream_file("has-been-disconnected");
        } else {
          $agi->exec("Congestion","5");
        }
        return -1;
      } else if ($reseller < 0){
        return -1;
      }
    }
  }

  //internationalise the destination and fail if it is not set set the userfield otherwise
  if ($destination <= 0) {
    if ($noivr == "0") {
      $agi->stream_file("prepaid/prepaid-invalid-digits");
    }
    return -1;
  } else {
    $agi->set_variable("CDR(userfield)",$destination);
    $agi->set_variable("CDR(accountcode)",$username);
  }

  //authorise the call
  $callauth = authorize_call($tariff, $destination,$username,$reseller);

  if (is_array($callauth)) {
    //Set up the dial string
    if (create_call($callauth,$reseller) != 0) {
      return -1;
    }
  } else {
    return -1;
  }

  //if there is a credit value allocate it as in use
  odbcquery("INSERT INTO inuse (userid,uniqueid,callocated,setuptime) VALUES ('" . $username . "','" . $agi->request['agi_uniqueid'] . "'," . $credit . ",now()+interval '1 second' * ($timeout+120))");

  //Dial The Call
  if ($callauth["dialproto"] == "Peer") {
    $callauth["dialproto"]="SIP/" . $callauth["providerip"];
    $agi->exec("ResetCDR","v");
    $agi->exec_dial($callauth["dialproto"],$dialstr);
  } else if ($callauth["dialproto"] != "GSM") {
    $agi->exec("ResetCDR","v");
    $agi->exec_dial($callauth["dialproto"],$dialstr);
  } else {
    gsm_call($h323gwid,$dialstr,$timeout);
  }

  //if it is congested or unavailable play congestion otherwise play noanswer or busy
  $dialstatus=getagivar("HANGUPCAUSE","CANCEL");
  if (($dialstatus != "HANGUP") && ($dialstatus != "") && ($dialstatus != "16")) {
    //verbose(print_r($dialstatus,TRUE));
    $agi->exec("ResetCDR","vw");
  }

  //hangup the call processing of account will take place in h extension
  $agi->hangup();
  return 0;
}

function getuserdest() {
  global $agi,$channel,$chanstat,$h323gwid,$h323prefix,$uniqueid,$noivr,$cbackuse,$destination,$username;

  $channel=$agi->request['agi_channel'];
  $uniqueid=$agi->request['agi_uniqueid'];
  $stat_channel = $agi->channel_status($channel);
  $chanstat=$stat_channel['result'];
  $noivr=0;
  $cbackuse=0;

  if (substr($channel,0,4) == "SIP/") {
    if ($username == "") {
      $username=substr($channel,4,8);
    }
  } else if (substr($channel,0,5) == "IAX2/") {
    //Dont Answer IAX calls and disable the IVR it is a Trunk ??
    $noivr=1;
    if ($username == "") {
      $username=substr($channel,5,8);
    }
  } else if ((substr($channel,0,6) == "OH323/") && ($username == "")) {
    //OH323 Calls are trunks so no IVR the username/destination is in the h323cdr
    $noivr=1;
    list($proto,$epid,$ipinf)=preg_split("/[\/@]/",$channel);
    list($ipaddr)=split("-",$ipinf);
    

    $oh323callid=getagivar("OH323_CALLID");
    $h323user=odbcquery("SELECT name,substr('" . $destination . "',length(h323prefix)+1),h323neighbor from users where (name = '" . $epid . "' OR h323gkid='" . $epid . "') AND ".
                                                         "(h323permit = '" . $ipaddr . "' OR h323permit = 'allow' OR h323permit ='') AND ".
                                                         "substr('" . $destination . "',1,length(h323prefix)) = h323prefix ORDER BY length(h323prefix) DESC LIMIT 1");
    if (!is_array($h323user)) {
      $h323user=odbcquery("SELECT name,substr('" . $destination . "',length(h323prefix)+1),h323neighbor from users ".
                     "left outer join h323cdr on (callid=replace('" . $oh323callid . "','-','')) ".
                   "where (name =callingstationid OR h323gkid=callingstationid OR h323cdr.username = h323gkid OR name = '" . $epid . "' OR h323gkid = '" . $epid . "' )  AND ".
                         "(h323permit = callerip OR h323permit = 'allow' OR h323permit ='') AND ".
                         "substr('" . $destination . "',1,length(h323prefix)) = h323prefix ".
                       "ORDER BY length(h323prefix) DESC LIMIT 1");
    }
    if (is_array($h323user)) {
      $username=$h323user[0];
      $destination=$h323user[1];
      $h323neighbor=$h323user[2];
    }
  } else if (substr($channel,0,7) == "Local/*") {
    //callback calls are connected via a local channel are we a slave or master ??
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
  if ($destination == '') {
    $destination=$agi->request['agi_extension'];
  }
  if ($destination == "s") {
    $destination=getivrdest($username);
  }
}

function authenticate_call(){
  global $username,$credit,$tariff,$active,$agi,$usertype,$cbackuse,$noivr;

  $res=0;

  if ( !isset($username) || strlen($username) == 0) {
    return -1;
  }

  //is this call from a card phone requireing a ivr user/pin
  $cpresult = odbcquery("SELECT value from astdb where family='CardPhone' and key='" . $username . "'");
  if(is_array($cpresult)) {
    if ($cpresult[0] == 1) { 
      $fcardno=$agi->get_data("prepaid/prepaid-pls-enter-card-no",6000,12);
      $fpinno=$fcardno['result'];
      if (!isset($username) || strlen($username) == 0) {
        return -1;
      }
      $username=substr($fpinno,0,8);
      $password=substr($fpinno,8,4);

      $cpresult = odbcquery("SELECT uniqueid from users where password='" . $password . "' and name='" . $username . "'");
      if(!is_array($cpresult)) {
        return -1;
      }
    }
  }

  //if this is a callback call allow master call in addition to sim use
  if ($cbackuse == 1) {
    $usecnt=1;
  } else {
    $usecnt=0;
  }

  //get the available credit on the users account less allocated credit
  $qresult=odbcquery(
  "SELECT users.credit-(sum(CASE WHEN (NOT cleared AND setuptime > now()) THEN inuse.callocated ELSE 0 END)*100), tariff, activated, count(CASE WHEN (NOT cleared AND setuptime > now()) THEN 1 END), creditcap, simuse+" . $usecnt . "," .
      "reseller.credit,agentid,buyrate,buyperiod,sellperiod,exchangerate,minperiod ,buyminperiod,usertype,rlevel,rcallocated,owner," .
      "(ivrwarn < users.credit) OR (ivrwarn = 0)  " .
    "FROM users " .
      "LEFT OUTER JOIN reseller ON (reseller.id=users.agentid) " .
      "LEFT OUTER JOIN inuse ON (name=userid) " .
    "WHERE users.name='$username' AND activated='t' AND reseller.credit > 0 " .
    "GROUP by users.credit,users.tariff,users.activated,users.creditcap,users.simuse,reseller.credit,users.agentid,reseller.buyrate," .
             "reseller.buyperiod,reseller.sellperiod,reseller.exchangerate,reseller.minperiod,reseller.buyminperiod,users.usertype,reseller.rlevel," .
             "reseller.rcallocated,reseller.owner,users.ivrwarn");

  if( !is_array($qresult)) {
    return -1;
  }

  if ($qresult[18] == 't') {
    $noivr=1;
  }

  $isused = $qresult[3];
  $simuse=$qresult[5];

  if ($isused >= $simuse) {
    return -1;
  }

  //get the currently allocated credit for the reseller
  $usercount = odbcquery("SELECT sum(CASE WHEN (NOT cleared AND setuptime > now()) THEN inuse.callocated ELSE 0 END) FROM users " .
           "LEFT OUTER JOIN reseller ON (reseller.id=users.agentid) LEFT OUTER JOIN inuse ON (name=userid)" . 
           "WHERE users.agentid = " . $qresult[7] . " GROUP BY users.agentid");

  if( !is_array($usercount)) {
    return -1;
  }

  //get the credit available in the pool
  $creditpool = odbcquery("SELECT sum(users.credit), " .
                    "sum((SELECT sum(callocated*100) FROM inuse WHERE userid=users.name AND NOT cleared AND setup < now())),sum(simuse) " .
                            "FROM companysites " .
                            "LEFT OUTER JOIN companysites AS mysite USING (companyid,creditpool) " .
                            "LEFT OUTER JOIN users ON (companysites.source=name AND users.activated) WHERE mysite.source='" . $username . "'");

  $resellerr=array();
  $resellerr["aloccred"]=$usercount[1];
  $resellerr["tariff"]=$qresult[8];
  $resellerr["id"]=$qresult[7];
  $resellerr["buyperiod"]=$qresult[9];
  $resellerr["sellperiod"]=$qresult[10];
  $resellerr["exrate"]=$qresult[11];
  $resellerr["minperiod"]=$qresult[12];
  $resellerr["buyminperiod"]=$qresult[13];
  $resellerr["level"]=$qresult[15];
  $resellerr["credalloc"]=$qresult[16]/100;
  $resellerr["owner"]=$qresult[17];
  $resellerr["credit"]=$qresult[6]/100;

  $usertype = $qresult[14];
  $tariff = $qresult[1];
  $active = $qresult[2];
  $linelim = $qresult[4];
/*
  //if allocated credit is more than the resellers credit determine the ratio 
  if ($resellerr["credalloc"] > $resellerr["credit"]) {
    $resellerr["cratio"]=$resellerr["credit"]/$resellerr["credalloc"];
  } else {
    $resellerr["cratio"]=1;
  }

  $resellerr["ownert"]=$resellerr["owner"];
  for ($reslev=$resellerr["level"]-1;$reslev >= 0;$reslev--) {
    $GETOCRED="SELECT credit,rcallocated,owner FROM reseller WHERE rlevel=$reslev AND id=" . $resellerr["ownert"];
    $getcredratio = $instance_table -> SQLExec ($DBHandle, $GETOCRED);
    if ($getcredratio[0][0] < $getcredratio[0][1]) {
      $rcratio=$rcratio*($getcredratio[0][0]/$getcredratio[0][1]);
    }
    $resellerr["ownert"]=$getcredratio[0][2];
  }
  $resellerr["credit"]=($resellerr["credit"]*$rcratio)-$resellerr["aloccred"];
*/

  //Divide the credit pool amoungst the available channels
  if (is_array($creditpool) && ($creditpool[0] > 0)) {
    $credit=($creditpool[0]-$creditpool[1])/($creditpool[2]*100);
  } else {
    $credit = $qresult[0]/($simuse*100);
  }

  //if  this is a callback allow a additional call ??
  if ($cbackuse == 2) {
    $simuse++;
  }

  //apply the credit cap on the account
  $linelim = $linelim/$resellerr["exrate"];
  if (($linelim < $credit) && ($linelim > 0)) {
    $credit=$linelim;
  }

  //if the reseller is not the master account limit the credit to the resellers available credit
  if (($resellerr["credit"] < $credit) && ($resellerr["id"] != 0)) {
    $credit=$resellerr["credit"];
  }

  //check the credit and user account if it is active and available
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
    return $resellerr;
  } else if ($res == -2 ) {
    if ($noivr == "0") {
      $agi->stream_file("$prompt");
    }
    return $res;
  }else{
    $res = -1;
    return $res;
  }
}

/*

Determine a rate/route for the call

*/
function authorize_call($tariffcode, $phonenumber, $username, $reseller){
  global $h323prefix,$tariff,$agi,$h323gwid,$h323neighbor;

  //Neighbour call this needs plenty work ....
  if ($h323neighbor == "t") {
    $ccresult=odbcquery("SELECT countrycode, subcode FROM countryprefix WHERE prefix=SUBSTRING('" . $phonenumber . "',1,length(prefix)) ORDER BY LENGTH(prefix) DESC LIMIT 1");
    if (!is_array($ccresult)) {
      return -1;
    } else {
      $qresult=array();
      $qresult[1]=$ccresult[0];
      $qresult[2]=$ccresult[1];
      if ($qresult[1] == 'ZAF') {
        $localrate=odbcquery("SELECT CASE WHEN(to_char(now(), 'HH24:MI:SS') >= peakstart AND to_char(now(), 'HH24:MI:SS') <= peakend AND extract('dow' FROM now()) ~ peakdays ) THEN peakmin||':'||peakperiod||':'||peaksec ELSE offpeakmin||':'||offpeakperiod||':'||offpeaksec END,description,index FROM localrates where '0" . substr($phonenumber,2) . "' ~ match");
      } else {
        $localrate=odbcquery("SELECT CASE WHEN(to_char(now(), 'HH24:MI:SS') >= peakstart AND to_char(now(), 'HH24:MI:SS') <= peakend AND extract('dow' FROM now()) ~ peakdays ) THEN peakmin||':'||peakperiod||':'||peaksec ELSE offpeakmin||':'||offpeakperiod||':'||offpeaksec END,'Telkom',-1 FROM localrates where countrycode = '" . $qresult[1] . "' AND subcode = '" . $qresult[2] . "'");
      }
      list($locmin,$locperiod,$locrate)=split(":",$localrate[0]);
      $freemin[0]=0;
      $freemin[1]=ceil($locmin/$locrate);
      $freemin[2]=$locperiod;

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
    //Get the local gatekeeper ...
    $gkidq=odbcquery("SELECT h323gkid from users where name='" . $h323gwid . "'");
    $h323gkid=$gkidq[0];

    //Set the cli for the call
    $clid=$agi->parse_callerid();

    //find the shortest prefix in the tariff sheet
    $qresult=odbcquery("SELECT tariffrate.rate,tariffrate.countrycode,tariffrate.subcode,lpad(provider.trunkprefix,7,'0'),removeprefix,tariff.tax,dialcmd," .
         "CASE WHEN (provider.callerid != '') THEN provider.callerid ELSE " .
           "CASE WHEN (cc_climap.userid IS NOT NULL) THEN cc_climap.prefix||substr('" . $clid['username'] . "',4+strip) ELSE users.callerid END END," .
         "trunk.protocol,trunk.providerip,trunk.h323prefix,trunk.h323gkid,rtariffrate.rate,rtariff.tax,rtariff.minrate," .
         "nationalprefix,internationalprefix,nationallen " .
      "FROM countryprefix " .
         "LEFT JOIN tariffrate USING (countrycode,subcode,trunkprefix) " .
         "LEFT JOIN tariffrate AS rtariffrate USING (countrycode,subcode,trunkprefix) " .
         "LEFT OUTER JOIN tariff ON (tariff.tariffcode=tariffrate.tariffcode) " .
         "LEFT OUTER JOIN tariff AS rtariff ON (rtariff.tariffcode=rtariffrate.tariffcode) " .
         "LEFT JOIN provider ON (provider.trunkprefix=tariffrate.trunkprefix) " .
         "LEFT OUTER JOIN trunk ON (trunk.trunkprefix=provider.trunkprefix AND h323reggk = '$h323gkid') " .
         "LEFT OUTER JOIN users ON (users.name='" . $username . "') " .
         "LEFT OUTER JOIN cc_route ON (cc_route.userid = users.uniqueid AND tariffrate.countrycode = cc_route.countrycode AND tariffrate.subcode = cc_route.subcode AND tariffrate.trunkprefix = cc_route.trunkprefix) " .
         "LEFT OUTER JOIN cc_climap ON (cc_climap.userid=users.uniqueid AND '" . $clid['username'] . "' ~ match) " .
       "WHERE rtariffrate.rate IS NOT NULL AND tariffrate.rate >= rtariffrate.rate AND " .
         "tariffrate.tariffcode='" . $tariffcode . "' AND rtariff.tariffcode='" . $reseller["tariff"] . "' AND " .
         "countryprefix.prefix=SUBSTRING('" . $phonenumber . "',1,length(countryprefix.prefix)) ".
       "ORDER BY LENGTH(countryprefix.prefix) DESC,cc_route.trunkprefix=tariffrate.trunkprefix,tariffrate.rate LIMIT 1");

    //the reseller has no rate bomb the call
    if (!is_array($qresult)) {
      return -1;
    }								

    //check free minutes packages for user free minutes
    $freemin=odbcquery("SELECT freemin/simuse,ansperiod,billperiod FROM package " .
                    "LEFT OUTER JOIN tariffrate ON (freerate=tariffcode) " .
                    "LEFT OUTER JOIN users ON (userid=users.uniqueid) " .
                  "WHERE rate < (freethreshold - freesfee)  AND rate > 0 AND " .
                      "users.name='" . $username . "' AND countrycode ='" . $qresult[1] . "' AND " .
                      "subcode = '" . $qresult[2] . "' AND activedate+expiredays > localtimestamp AND " .
                      "freemin/simuse > ansperiod " .   
                      "ORDER BY activedate LIMIT 1");
    
    //if the resellers minimum rate for this call is more than the users rate make the user pay the min rate
    if ($qresult[14] > $qresult[0]) {
      $qresult[0]=$qresult[14];
    }
  }
  //pass back the callauth array
  $rateinfo=array();
  $rateinfo["rate"]= $qresult[0]; // RATE
  if ($h323prefix == "") {
    $rateinfo["prefix"]= $qresult[3]; // PREFIX
  } else {
    $rateinfo["prefix"]=str_pad($h323prefix,7,"0",STR_PAD_LEFT);
  }
  $rateinfo["newdestination"]= $phonenumber;
  $rateinfo["countrycode"]= $qresult[1]; // COUNTRYCODE
  $rateinfo["subcode"]= $qresult[2]; // SUBCODE
  $rateinfo["removeprefix"]= $qresult[4]; // REMOVEPREFIX
  $rateinfo["nationalprefix"] = $qresult[15]; // National Prefix
  $rateinfo["internationalprefix"] = $qresult[16]; // International Prefix
  $rateinfo["nationallen"] = $qresult[17]; // National Len
  $rateinfo["resellerrate"]= $qresult[12]; // RESELER RATE
  $rateinfo["taxrate"]= $qresult[5]; // TAX RATE
  $rateinfo["rtaxrate"] = $qresult[13]; // RESELER TAX RATE
  $rateinfo["freemin"] = $freemin[0]; //Minutes Available On Package
  $rateinfo["freeans"] = $freemin[1]; //Ans Charge For Package
  $rateinfo["freebill"] = $freemin[2]; //Bill Period For Package
  $rateinfo["dialcmd"] = $qresult[6]; // DIAL COMMAND
  if ($qresult[7] != "") {
    $agi->set_callerid("\"" . $qresult[7] . "\"<" . $qresult[7] . ">");
  }
  if (($qresult[11] == $h323gwid) && ($rateinfo[18] == "*")) {
    $rateinfo["dialproto"] = "GSM";
  } else if ($qresult[9] != "") {
    $rateinfo["dialproto"] = $qresult[8]; // PROTOCOL
  } else {
    $rateinfp["dialproto"] = "OH323";
  }
  $rateinfo["providerip"] = $qresult[9]; // PROTOCOL IP
  $rateinfo["providerpre"] = $qresult[10]; // PROVPRE
  return $rateinfo;
}

/*

Build the dial string and calculate the duration

*/
function create_call($callauth,$reseller){
  global $noivr,$agi,$cbackuse,$credit,$timeout,$dialstr,$h323neighbor;

  //if the callauth array does not exist the destination is unreachable ??
  if (!is_array($callauth)){
    if ($noivr == "0") {
      $agi->stream_file("prepaid/prepaid-dest-unreachable");
    }
    return -1;
  }

  //update the destination by removing the prefix
  if (strncmp($callauth["newdestination"], $callauth["removeprefix"], strlen($callauth["removeprefix"])) == 0) {
    /*if its bellow certain len do not append national code*/
    if (strlen($callauth["newdestination"]) < $callauth["nationallen"]) {
      $callauth["newdestination"]=substr($callauth["newdestination"], strlen($callauth["removeprefix"]));
    } else if ($callauth["nationalprefix"] != ""){
      $callauth["newdestination"]=$callauth["nationalprefix"] . substr($callauth["newdestination"], strlen($callauth["removeprefix"]));
    }
  } else if ($callauth["internationalprefix"] != "") {
    /*apppend international prefix*/
    $callauth["newdestination"]=$callauth["internationalprefix"]  . $callauth["newdestination"];
  }

  //if the rate is less than or equal to 0 and there no free minutes to chew the destination is unreachable
  if (($callauth["rate"] <= 0) && ($callauth["freemin"] <= 0)){
    if ($noivr == "0") {
      $agi->stream_file("prepaid/prepaid-dest-unreachable");
    }
    return -1;
  }

  //the rate to use is the rate in rands plus tax
  $rateapply=($callauth["rate"] * $reseller["exrate"] * (1+$callauth["taxrate"]/100))/10000;
  //the basic timeout is ammount of credit divided by the rate 
  $timeout=floor((3 * $credit * $reseller["exrate"])/(5 * $rateapply));
 
  //if the timeout is more than the min billing period (answer charge) adjust it to accommodate complete periods
  if ($timeout > $reseller["minperiod"]) {
    $timeout = $timeout - (($timeout - $reseller["minperiod"]) % $reseller["sellperiod"]);
  }

  //Workout the timeout of the resellers credit
  $rtimeout = floor(($reseller["credit"] * 6000) / ($callauth["resellerrate"] *(1+$callauth["rtaxrate"]/100)));

  //if the resellers timeout is more than the reseller buy min period adjust for complete period
  if ($rtimeout > $reseller["buyminperiod"]) {
    $rtimeout = $rtimeout - (($rtimeout - $reseller["buyminperiod"]) % $reseller["buyperiod"]);
  }

  //if the reseller timeout is less than the users timeout adjust down for a complete period
  if ($rtimeout < $timeout) {
    $timeout=$rtimeout;
    $timeout = $timeout - ($timeout % $reseller["sellperiod"]);
  }

  //double check and bomb if the timeout is not right
  if ((($timeout < $reseller["minperiod"] ) || ($rtimeout < $reseller["buyminperiod"])) && ($h323neighbor != "t")){
    if ($noivr == "0") {
      $agi->stream_file("prepaid/prepaid-no-enough-credit");
    }
    return -1;
  }
 
  //take freeminutes into account adjusted for the package awnswer charge and period
  if ($callauth["freemin"] > 0) {
    $callauth["freemin"] = $callauth["freemin"] - (($callauth["freemin"] - $callauth["freeans"]) % $callauth["freebill"]);
    $timeout=$callauth["freemin"]+$timeout;
  } else {
    $callauth["freemin"]=0;
  }

  //if it is a callback call allow * hangup
  if ($cbackuse == 2) {
    $callauth["dialcmd"] .="H";
  }

  //put a 2 second margin on all calls that they hang up 2 seconds early
  $timeout=$timeout-2;
  $rtimeou=$rtimeout-2;

  //dont allow calls less than 5 seconds
  if ((($timeout < 5 ) || ($rtimeout < 5)) && ($h323neighbor != "t")) {
    if ($noivr == "0") {
      $agi->stream_file("prepaid/prepaid-no-enough-credit");
    }
    return -1;
  }

  //Setup the dial command for different protocols (local uses internal PBX dialplan)
  $timeout=($timeout < 7200)?$timeout:"7200";
  if ($callauth["dialproto"] == "OH323") {
    $dialstr = $callauth["prefix"] . $callauth["newdestination"] . "|120|" . $callauth["dialcmd"] . "gD(:C)";
    if ($timeout > 0 ) {
      $dialstr = $dialstr  . "L("  . $timeout*1000;
    }
  } else if ($callauth["dialproto"] == "GSM") {
    $dialstr = "0" . substr($callauth["newdestination"],2);
  } else if ($callauth["dialproto"] == "Local") {
    $dialstr = "+" . $callauth["newdestination"] . "@" . $callauth["providerip"] . "|120|" . $callauth["dialcmd"] . "gD(:C)";
    if ($timeout > 0 ) {
      $dialstr = $dialstr  . "L("  . $timeout*1000;
    }
  } else if ($callauth["dialproto"] == "Peer") {
    $dialstr = $callauth["newdestination"] . "|120|" . $callauth["dialcmd"] . "gD(:C)";
    if ($timeout > 0 ) {
      $dialstr = $dialstr  . "L("  . $timeout*1000;
    }
  } else if (($callauth["dialproto"] == "LOCAL") && ($h323neighbor == "t")) {
    $callauth["dialproto"]=rtdbget("Setup","Trunk");
    $callauth["dialproto"]=substr($callauth["dialproto"],0,strlen($callauth["dialproto"])-1);
    $dialstr = "0" . substr($callauth["newdestination"],2) . "|120|wgtD(:C)";
    if ($timeout > 0 ) {
      $dialstr = $dialstr  . "L("  . $timeout*1000;
    }
  } else {
    $dialstr = $callauth["providerpre"] . $callauth["newdestination"] . "@" . $callauth["providerip"] . "|120|" . $callauth["dialcmd"] . "gD(:C)";
    if ($timeout > 0 ) {
      $dialstr = $dialstr  . "L("  . $timeout*1000;
    }
  }

  //setup the calltime warning on calls not GSM routed
  if (($callauth["dialproto"] != "GSM") && ($timeout > 0)) {
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
  //if the ivr is active report the available calltime

  if (! $noivr) {
    sayminutes($timeout);
  }
  return 0;
}
%>
