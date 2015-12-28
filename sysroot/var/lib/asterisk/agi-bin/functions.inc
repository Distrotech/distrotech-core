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

include "/var/lib/asterisk/agi-bin/auth.inc";

$GLOBALS['db']=$db;

function newpin($exten) {
  global $db;

  $pincnt=1;
  $pintry=1;

  while (($pintry <= 10) && ($pincnt > 0)) {
    $randpin=rand(0,9999);
    $randpin=str_pad($randpin,4,"0",STR_PAD_LEFT);
    $pincntq=pg_query($db,"SELECT count(id) FROM features  WHERE roampass='" . $randpin . "'");
    list($pincnt)=pg_fetch_array($pincntq,0);
    $pintry++;
  }
  if ($pincnt == 0) {
    pg_query($db,"UPDATE features SET roampass='" . $randpin . "' WHERE exten='" . $exten . "'");
  }
  return $randpin;
}

function odbcquery($sqlquery) {
  global $db;
  verbose("QUERY: " .  $sqlquery);
  $odbcexec=pg_query($db,$sqlquery);
  if (pg_num_rows($odbcexec) > 0) {
    return pg_fetch_row($odbcexec,0);
  } else {
    return -1;
  }
}

function getagivar($variable,$default) {
  global $agi;
  $vresult=$agi->get_variable($variable);
  if ($vresult['result'] == 1) {
    $retval=$vresult['data'];
  } else if ($vresult['result'] == "") {
    $retval="HANGUP";
  } else if ($default != "") {
    $retval=$default;
  } else {
    $retval='';
  }
  return $retval;
}


function verbose($outmsg) {
  global $agi;
  if (isset($agi)) {
    $agi->verbose($outmsg,3);
  }
}

function getivrdest($username) {
  global $agi;

  $agi->answer();
  play_credit($username);

  if (is_file("/var/lib/asterisk/sounds/prepaid/" . $agi->request['agi_language'] . "/prepaid-pls-enter-dest.gsm")) {
    $destnum=$agi->get_data("prepaid/prepaid-pls-enter-dest",3500);
  } else {
    $agi->exec("PlayTones","dial");
    $agi->exec("WAIT","1.75");
    $agi->exec("StopPlayTones");
    $agi->exec("WAIT","0.25");
    $destnum=$agi->get_data("beep",3500);
  }
  return substr(strrchr("*" . $destnum['result'],"*"),1);
}

function sayminutes($valuein) {
  global $agi;

  $minutes = intval($valuein / 60);
  $seconds = $valuein % 60;
  $agi->stream_file("prepaid/prepaid-you-have");
  if ($minutes>0){
    $agi->say_number($minutes);
    if ($minutes==1){
      $agi->stream_file("prepaid/prepaid-minute");
    } else {
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
    } else {
      $agi->stream_file("prepaid/prepaid-seconds");
    }
  }
}

function sayrand($valuein) {
  global $agi;

  $rand = intval($valuein / 100);
  $cents = $valuein % 100;
  $agi->stream_file("prepaid/prepaid-you-have");
  if ($rand > 0){
    $agi->say_number($rand);
    $agi->stream_file("prepaid/prepaid-rand");
  }
  if ($cents>0){
    if ($rand>0) {
      $agi->stream_file("prepaid/prepaid-and");
    }
    $agi->say_number($cents);
    if ($cents==1){
      $agi->stream_file("prepaid/prepaid-cent");
    } else {
      $agi->stream_file("prepaid/prepaid-cents");
    }
  }
}

function play_credit($usertoget) {
  global $agi;
  global $noivr;

  $isciduser=odbcquery("SELECT username from callerid where cid='" . $agi->request['agi_callerid'] . "'");
  if (($isciduser[0] != $usertoget) && (! $noivr)) {
    $usercredit=odbcquery("SELECT ((users.credit/simuse)*reseller.exchangerate)/100 from users left outer join reseller on (agentid=reseller.id) where name='" . $usertoget . "'");
    sayrand($usercredit[0]);
  }
}

function makenumint($phonenumber){
  if (substr($phonenumber,0,2) == "00") {
    $phonenumber=substr($phonenumber,2);
  } else if (substr($phonenumber,0,1) == "+") {
    $phonenumber=substr($phonenumber,1);
  } else if ((strlen($phonenumber) == "10") && (substr($phonenumber,0,2) != "00")){
    $phonenumber="27" . substr($phonenumber,1,9);
  } else if ((strlen($phonenumber) < 6) && (substr($phonenumber,0,2) != "00")){
    $phonenumber="27" . $phonenumber;
  }
  return $phonenumber;
}

function make_gsm_call($number,$chanid,$timeout) {
  global $agi;
  $agi->exec_dial("DAHDI",$chanid . "/wwww" . $number,60,"FgrL(" . $timeout . ")");
}

function get_stats() {
  $dsres=array();
  $dsres[0]=getagivar("DIALEDTIME","0");
  $dsres[1]=getagivar("ANSWEREDTIME","0");
  $dsres[2]=getagivar("DIALSTATUS","");
  $dsres[3]=getagivar("HANGUPCAUSE","");
  return $dsres;
}

function get_chan($crouter,$dialednumber) {
  $minctime="1200";
  $result = odbcquery("SELECT channel,CASE WHEN ".
   "(endtime > starttime AND localtime > starttime AND localtime < endtime AND extract('epoch' from endtime - localtime) < calltime - " . $minctime . ") THEN ".
     "extract('epoch' from endtime - localtime) ELSE CASE WHEN ".
   "(endtime < starttime AND localtime > endtime AND localtime > starttime AND extract('epoch' from localtime - endtime + interval '12 hours') < calltime - " . $minctime . ") THEN ".
     "extract('epoch' from localtime - endtime + interval '12 hours') ELSE CASE WHEN ".
   "(endtime < starttime AND localtime < endtime AND localtime < starttime AND extract('epoch' from endtime - localtime) < calltime - " . $minctime . ") THEN ".
     "extract('epoch' from endtime - localtime) ELSE calltime - " . $minctime . " END END END as calltime,expires,".
     "CASE WHEN (maxfaultcount >= faultcount) THEN maxfaultcount ELSE faultcount END ".
             "FROM gsmchannels WHERE ((endtime > starttime AND starttime <= localtime AND localtime < endtime) OR ".
                                     "(endtime < starttime AND starttime > localtime AND localtime < endtime) OR ".
                                     "(endtime < starttime AND starttime <= localtime AND localtime > endtime) OR ".
                                     "(starttime = endtime and starttime = '00:00:00')) AND ".
                                    "'" . $dialednumber . "' ~ regex AND expires > now() AND inuse = 'f' AND router = '" . $crouter . "' AND ".
                                    "outofservice < now() AND calltime >= " . $minctime . " ORDER BY expires,calltime DESC LIMIT 1");
  if( !is_array($result)) {
    return -1;
  }
  $result[1]=$result[1]*1000;
  return $result;  
}

function gsm_call($crouter,$dialednumber,$atimeout) {
  global $agi;

//  for($cnt=0;$cnt <= 4;$cnt++) {
    $gcresult=get_chan($crouter,$dialednumber);
    if ( !is_array($gcresult)) {
      break;
    }
    list($chanid,$timeout,$expires,$faultcount)=$gcresult;
    if (($timeout > $atimeout) && ($atimeout > 0)){
      $timeout=$atimeout;
    }
    $agi->set_variable("GSMCHAN",$chanid);
    $agi->set_variable("GSMROUTER",$crouter);
    odbcquery("UPDATE gsmchannels SET inuse='t' WHERE channel='" . $chanid . "' AND router='" . $crouter . "'");
    make_gsm_call($dialednumber,$chanid,$timeout);
/*
    $callstat=get_stats();
    //verbose(print_r($callstat,TRUE));
    if (($callstat[2] != "BUSY") && ($callstat[2] != "ANSWER") && ($callstat[2] != "CANCEL") && ($callstat[2] != "")) {
      odbcquery("UPDATE gsmchannels SET inuse='f',faultcount=faultcount+1,outofservice = now() + (interval '1 second' * 5 ^ faultcount)  WHERE channel='" . $chanid . "' AND router='" . $crouter . "'"); 
      $agi->set_variable("GSMCHAN","");
      sleep(1);
    } else if ($callstat[2] != "") {
      $agi->hangup();
      sleep(20);
      break;
    } else {
      if ($chanid != "") {
        odbcquery("UPDATE gsmchannels SET inuse='f',faultcount=faultcount+1,outofservice = now() + (interval '1 second' * 5 ^ faultcount)  WHERE channel='" . $chanid . "' AND router='" . $crouter . "'"); 
        $agi->set_variable("GSMCHAN","");
      }      
      break;
    }
*/
//  }
}

function account_call($uniqueid) {
/*still to do
freemin
SET uniquecdrid credit and starttime
*/

  odbcquery("UPDATE inuse SET cleared='t' WHERE uniqueid='" . $uniqueid . "'");
  $QUERY="SELECT channel,billsec,duration,calldate+interval '1 second' * (duration-billsec),calldate+interval '1 second' * duration,disposition,case when (userfield ~ '^0') then '27'||substr(userfield,2) else userfield end,accountcode FROM cdr WHERE uniqueid='$uniqueid'";
  sleep(2);
  $cdrrec=odbcquery($QUERY);

  if (!is_array($cdrrec)) {
    return -1;
  }

  list($channel,$cdrbillsec,$cdrduration,$cdrstart,$cdrend,$dialstatus,$destination,$username)=$cdrrec;

  if ($dialstatus == "ANSWERED") {
    $dialstatus="ANSWER";
  } else {
    $dialstatus=($dialstatus == "")?"CANCEL":$dialstatus;
  }

  //Get the reseller/dest/freemin information as we may be called from the h extension
  $rquery=odbcquery("SELECT agentid,buyperiod,sellperiod,exchangerate,minperiod,buyminperiod,rlevel,owner,tariff," .
                           "tax,rate,countrycode,subcode,ansperiod,billperiod,package.id,provider.name " .
                         "FROM users " .
                           "LEFT OUTER JOIN reseller ON (reseller.id=agentid) " .
                           "LEFT OUTER JOIN tariff ON (buyrate=tariffcode) " .
                           "LEFT OUTER JOIN tariffrate ON (tariffrate.tariffcode=buyrate) " .
                           "LEFT OUTER JOIN countryprefix USING (countrycode,subcode) " .
                           "LEFT OUTER JOIN package ON (userid=users.uniqueid) " .
                           "LEFT JOIN provider ON (tariffrate.trunkprefix=provider.trunkprefix) " .
                         "WHERE users.name='" . $username . "' AND prefix=SUBSTRING('" . $destination . "',1,length(prefix)) " .
                           "ORDER BY LENGTH(prefix) DESC LIMIT 1");
  $resell["id"]=$rquery[0];
  $resell["buyperiod"]=$rquery[1];
  $resell["sellperiod"]=$rquery[2];
  $resell["exrate"]=$rquery[3];
  $resell["minperiod"]=$rquery[4];
  $resell["buyminperiod"]=$rquery[5];
  $resell["level"]=$rquery[6];
  $resell["owner"]=$rquery[7];
  $tariffcode=$rquery[8];
  $provider=$rquery[16];
  $callinf["countrycode"]=$rquery[11];
  $callinf["subcode"]=$rquery[12];

  //Get The Tarriff info as we arw been called from h exten
  $tarifq=odbcquery("SELECT rate,tax FROM tariffrate LEFT OUTER JOIN tariff USING (tariffcode) " .
                      "WHERE countrycode='" . $callinf["countrycode"] . "' AND " .
                            "subcode='" . $callinf["subcode"] . "' AND " .
                            "tariffcode='" . $tariffcode . "'");

  $callinf["rate"]=$tarifq[0];
  $callinf["taxrate"]=$tarifq[1];
  $callinf["rtaxrate"]=$rquery[9];
  $callinf["resellerrate"]=$rquery[10];
  $callinf["freeans"]=$rquery[13];
  $callinf["freebill"]=$rquery[14];

  if (($dialstatus == "ANSWER") && ($answeredtime == 0)) {
    $answeredtime=1;
  }

  //need to fetch freemins again and allocate freemins as we do allocated credit ????
  //work out actual billed time and free allocated free minutes (perhaps split free mins over simuse)
  //need to normalise to free time period/anstime
  $callinf["freemin"]=($callinf["freemin"] == "")?0:$callinf["freemin"];
  if ($callinf["freemin"] >= $cdrbillsec) {
    $freetime=$cdrbillsec;
    $billtime=0;
  } else {
    $freetime=$callinf["freemin"];
    $billtime=$cdrbillsec-$callinf["freemin"];
  }

  //Zero billedtime on not answered calls
  if ($dialstatus != "ANSWER") {
    $billtime=0;
  }

  $anstime=$billtime;

  //set freetime according to package parameters
  if (($freetime > 0) && ($freetime <= $callinf["freeans"])) {
    $freetime=$callinf["freeans"];
  } else if ((($freetime - $callinf["freeans"]) % $callinf["freebill"]) > 0) {
    $freetime=$freetime - (($freetime - $callinf["freeans"]) % $callinf["freebill"]) + $callinf["freebill"];
  }

  //set billing time according to package paramaters
  if (($billtime > 0) && ($billtime <= $resell["minperiod"])) {
    $billtime=$resell["minperiod"];
  } else if (((($billtime - $resell["minperiod"]) % $resell["sellperiod"]) > 0) && ($billtime > 0)){
    $billtime=$billtime - (($billtime - $resell["minperiod"]) % $resell["sellperiod"]) + $resell["sellperiod"];
  }

  //there is no tax on this rate ??? possibly wrong
  $rateapply=($callinf["rate"] * $resell["exrate"])/10000;

  //set the resellers time according to there package
  if (($anstime > 0) && ($anstime <= $resell["buyminperiod"])) {
    $anstime=$resell["buyminperiod"];
  } else if (((($anstime - $resell["buyminperiod"]) % $resell["buyperiod"]) > 0) && ($anstime > 0)){
    $anstime=$anstime - (($anstime - $resell["buyminperiod"]) % $resell["buyperiod"]) + $resell["buyperiod"];
  }
 
  //users credit used
  $racredit_used=ceil(($callinf["rate"]*$billtime*(1+$callinf["taxrate"]/100))/60);

  //debit user and reclaim the allocated credit
  if ($racredit_used > 0) {
    $pooluser="SELECT name FROM companysites LEFT OUTER JOIN companysites AS mysite USING (companyid,creditpool) " .
		 "LEFT OUTER JOIN users ON (companysites.source=name AND users.activated) WHERE mysite.source='" . $username . "'" .
                 "ORDER BY users.credit DESC LIMIT 1";
    list($newuser) = odbcquery($pooluser);
    if ($newuser != "") {
      $username = $newuser;
    }
    $QUERY = "UPDATE users SET credit=(credit - $racredit_used) WHERE name='$username'";
    odbcquery($QUERY);
  }

  if ($callinf["freemin"] > 0) {
    odbcquery("UPDATE package SET freemin=freemin-$freetime WHERE id=" . $callinf["freepid"]);
  }

  //resellers credit used
  $credit_used=($racredit_used*$resell["exrate"])/100;

  if ($dialstatus == "ANSWER") {
    //answered non H323 Call
    $QUERY="INSERT INTO call (uniqueid,sessionid,username,calledstation,".
                             "calledcountry,calledsub,starttime,stoptime,".
                             "totaltime,ringtime,callduration,oh323callid,".
                             "terminatecause,sessiontime,calledrate,".
                             "sessionbill,usertariff,calledprovider,stopdelay) ".
                 "VALUES ('$uniqueid','" . $channel . "','$username','$destination',".
                        "'" . $callinf["countrycode"] . "','" . $callinf["subcode"] . "','$cdrstart','$cdrend',".
                        "$cdrduration,$cdrduration-$cdrbillsec,$cdrduration,'$uniqueid','$dialstatus',$freetime+$billtime,'$rateapply',".
                   "'$credit_used','" . $tariffcode . "','" . $provider . "',$billtime - $anstime )";
  } else {
    //non answered Call
    $QUERY="INSERT INTO call (uniqueid,sessionid,username,calledstation,".
                             "calledcountry,calledsub,starttime,stoptime,".
                             "totaltime,ringtime,callduration,oh323callid,".
                             "terminatecause,sessiontime,calledrate,".
                             "sessionbill,usertariff,calledprovider,stopdelay) ".
                 "VALUES ('$uniqueid','" . $channel . "','$username','$destination',".
                   "'" . $callinf["countrycode"] . "','" . $callinf["subcode"] . "','" . $cdrstart . "',localtimestamp,".
                   "extract(epoch from localtimestamp-'" . $cdrstart . "'),".
                   "extract(epoch from localtimestamp-'" . $cdrstart . "')-$anstime,".
                   "$cdrduration,'$uniqueid','$dialstatus',$freetime+$billtime,'".
                   "$rateapply','$credit_used','" . $tariffcode . "',".
                   "'" . $provider . "',$billtime - $anstime)";
  }
  odbcquery($QUERY);

  if (($anstime > 0) && ($anstime <= $resell["buyminperiod"])) {
    $ranstime=$resell["buyminperiod"];
  } else if ((($anstime - $resell["buyminperiod"]) % $resell["buyperiod"]) > 0) {
    $ranstime=$anstime - (($anstime - $resell["buyminperiod"]) % $resell["buyperiod"]) + $resell["buyperiod"];
  } else {
    $ranstime=$anstime;
  }

  $rcredit_used = ceil(($callinf["resellerrate"] * $ranstime * (1+$callinf["rtaxrate"]/100))/60);

  if ($rcredit_used > 0) {
    odbcquery("UPDATE reseller SET credit=(credit - $rcredit_used) WHERE id='" . $resell["id"] . "'");
    odbcquery("UPDATE reseller SET rcallocated=(rcallocated - $racredit_used) WHERE id='" . $resell["id"] . "'");
  }

  $outputtax=ceil(($credit_used - $credit_used/(1+$callinf["taxrate"]/100))*100);
  $inputtax=ceil($rcredit_used*$resell["exrate"] - ($rcredit_used*$resell["exrate"])/(1+$callinf["rtaxrate"]/100));


  odbcquery("INSERT INTO resellercall VALUES ('$uniqueid','" . $resell["id"] . "','" . $resell["exrate"] . "','$rcredit_used','" . $callinf["resellerrate"] . "','$inputtax','$outputtax','$credit_used','" . $callinf["rate"] . "','$racredit_used')");

  $resell["ownert"]=$resell["owner"];
  $oldrate=$callinf["rate"];
  $cred_last=$rcredit_used*$resell["exrate"]/100;
  for ($reslev=$resell["level"];$reslev > 1;$reslev--) {
    $getrlevelcall=odbcquery("SELECT buyrate,buyperiod,exchangerate,minperiod,owner FROM reseller WHERE id=" . $resell["ownert"]);
    $rownertnew=$getrlevelcall[4];
    $rbuymin=$getrlevelcall[3];
    $rexrate=$getrlevelcall[2];
    $rbuy=$getrlevelcall[1];
    $rtariff=$getrlevelcall[0];

    $rtarresult=odbcquery("SELECT rate,tax,minrate FROM tariffrate LEFT OUTER JOIN tariff USING (tariffcode) WHERE countrycode='" . $callinf["countrycode"] . "' AND subcode='" . $callinf["subcode"] . "' AND tariffcode='$rtariff'");

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
    odbcquery("UPDATE reseller SET credit=(credit - $rcused) WHERE id='" . $resell["ownert"] . "'");
    odbcquery("UPDATE reseller SET rcallocated=(rcallocated - $rcredit_used) WHERE id='" . $resell["ownert"] . "'");

    $outputtax=ceil(($cred_last - $cred_last/(1+$callinf["taxrate"]/100))*100);
    $inputtax=ceil($rcused*$rexrate - ($rcused*$rexrate)/(1+$rtax/100));
    odbcquery("INSERT INTO resellercall VALUES ('$uniqueid','" . $resell["ownert"] . "','$rexrate','$rcused','$rrate','$inputtax','$outputtax','$cred_last','$oldrate','$rcredit_used')");

    $cred_last=$rcused*$rexrate/100;
    $rcredit_used=$rcused;
    $callinf["taxrate"]=$rtax;
    $resell["ownert"]=$rownertnew;
    $oldrate=$rrate;
  }
}

?>
