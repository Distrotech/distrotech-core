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


include_once "/var/spool/apache/htdocs/cdr/uauth.inc";

function printcsv($inarr) {
  $dataout="\"" . str_replace(array("\"","--!@#%^&--"),array("\"\"","\",\""),implode("--!@#%^&--",$inarr)). "\"\n";
  return $dataout;
}

function gtime($secin) {
  $secin=abs($secin);
  $rem=$secin % 3600;
  $hours=sprintf("%02d",($secin-$rem)/3600);
  $rem2=$rem % 60;
  $mins=sprintf("%02d",($rem-$rem2)/60);
  $secs=sprintf("%02d",$rem2);
  $timeout="$hours:$mins:$secs";
  return $timeout;
}
if ($exep >= 5) {
  $exep=1+$exep/100;
}
$trunkd=array("TDM Circut","VOIP Provider");
$tchans=array("(^Local/[0-9]+@trunkproxy-[0-9]+)|(^mISDN/[0-9]+-u[0-9]+$)|(^mISDN/[0-9]+-[0-9]+$)|(^DAHDI/i[0-9]+/[0-9]+-[a-fA-F0-9]+$)|(^DAHDI/[0-9]+-[0-9]+$)|(^Zap/[0-9]+-[0-9]+$)|(^H323/)|(^WOOMERA/)","(^SIP/parent)|(^IAX2/parent)");
$trunkchan=implode("|",$tchans);
$exposq=pg_query($db,"SELECT sitecode,position FROM exchangeinfo LEFT OUTER JOIN exchanges USING (sitecode) left OUTER JOIN astdb  as acode ON (acode.family='Setup' AND acode.key='AreaCode') LEFT OUTER JOIN astdb  as expre ON (expre.family='Setup' AND expre.key='ExCode') WHERE acode.value=areacode and expre.value=prefix");
$expos=@pg_fetch_array($exposq,0);

if (($_POST['date'] != "") || (($date != "") && (!isset($_POST['date'])))){
  $month=explode("/",$_POST['date']);
  if ($_POST['date2'] == "") {
    $_POST['date2']=$_POST['date'];
  }
  $month2=explode("/",$_POST['date2']);
  if ($_POST['dom'] != "") {
    $month[2]=$_POST['dom'];
  } else {
    $month[2]="1";
  }
  if ($_POST['date2'] == "") {
    $_POST['date2']=$_POST['date'];
  }

  if ($_POST['dom2'] != "") {
    $month2[2]=$_POST['dom2'];
    if ($month2[0] != 2) {
      if (checkdate($month2[0],31,$month2[1])) {
        $dom2m=31;
      } else {
        $dom2m=30;
      }
    } else {
      if (checkdate($month2[0],29,$month2[1])) {
        $dom2m=29;
      } else {
        $dom2m=28;
      }
    }
    if ($month2[2] > $dom2m) {
      $month2[2]=$dom2m;
    }
  } else {
    if ($month[0] != 2) {
      if (checkdate($month[0],31,$month[1])) {
        $_POST['dom2']=31;
      } else {
        $_POST['dom2']=30;
      }
    } else {
      if (checkdate($month[0],29,$month[1])) {
        $_POST['dom2']=29;
      } else {
        $_POST['dom2']=28;
      }
    }
    $month2[2]=$_POST['dom2'];
  }

  pg_query($db,"INSERT INTO exchangedist SELECT '" . $expos[0] . "',sitecode,ceil(length(lseg('" . $expos[1] . "',position))) FROM exchangeinfo
         LEFT OUTER JOIN exchangedist using(sitecode)
       WHERE
         distance IS NULL AND position IS NOT NULL");

  if ($getbill == "on") {
    if (! is_array($lgetbdate)) {
      $lastdate=pg_query($db,"SELECT calldate from cdr left outer join trunkcost using (uniqueid) where trunkcost.uniqueid is not  NULL ORDER BY calldate DESC LIMIT 1");
      $lgetbdate=pg_fetch_array($lastdate,0);
    }
    if ($lgetbdate[0] == "") {
      $lgetbdate[0]="1970-01-01 00:00:00";
    }
/*
SELECT starttime,uniqueid,sessiontime,case when (substr(calledstation,1,2)='27') then '0'||substr(calledstation,3) else case when (calledstation ~ '^[1-9]' ) then 
'00'||calledstation else calledstation end end from call where terminatecause='ANSWER';
*/
    $callquery="INSERT INTO trunkcost SELECT DISTINCT ON (call.uniqueid) call.uniqueid,
      fn_calldist('0'||substr(calledstation,3),'" . $expos[0] . "'),
      CASE WHEN(CAST(to_char(starttime, 'HH24:MI:SS') AS time) >= peakstart AND CAST(to_char(starttime, 'HH24:MI:SS') AS time) <= peakend AND CAST(extract('dow' FROM starttime) AS varchar) ~ peakdays ) THEN
             fn_natcallcost(peakmin,peaksec,peakperiod,sessiontime)
           ELSE
             fn_natcallcost(offpeakmin,offpeaksec,offpeakperiod,sessiontime)
           END,
         index
       FROM call
         LEFT OUTER JOIN trunkcost USING (uniqueid)
         LEFT OUTER JOIN localrates ON ('0'||substr(calledstation,3) ~ match)
       WHERE
         trunkcost.uniqueid IS NULL AND
         terminatecause = 'ANSWER' AND
         localrates.index IS NOT NULL AND
         calledstation ~ '^27' AND 
         ((localrates.distance <= fn_calldist('0'||substr(calledstation,3),'" . $expos[0] . "') AND localrates.distance >= 0) OR localrates.distance IS NULL) AND
         localrates.validfrom < starttime AND localrates.validto > starttime AND
         starttime >= '" . $lgetbdate[0] . "'
       ORDER BY call.uniqueid,localrates.distance DESC";
//    print $callquery . "\n";

    $callquery2="INSERT INTO trunkcost SELECT DISTINCT ON (call.uniqueid) call.uniqueid,-1,
           CASE WHEN(CAST(to_char(starttime, 'HH24:MI:SS') AS time) >= peakstart AND CAST(to_char(starttime, 'HH24:MI:SS') AS time) <= peakend AND
                     CAST(extract('dow' FROM starttime) AS varchar) ~ peakdays ) THEN
             fn_natcallcost(peakmin,peaksec,peakperiod,sessiontime)
           ELSE
             fn_natcallcost(offpeakmin,offpeaksec,offpeakperiod,sessiontime)
           END,-1
       FROM call
         LEFT OUTER JOIN trunkcost USING (uniqueid)
         LEFT OUTER JOIN countryprefix ON (countryprefix.prefix = substr(calledstation,1,length(countryprefix.prefix)))
         LEFT OUTER JOIN intrates USING (countrycode,subcode)
       WHERE
         trunkcost.uniqueid IS NULL AND calledstation !~ '^27' AND
         intrates.validfrom < now() AND validto > now() AND
         terminatecause = 'ANSWER' AND
         starttime  >= '" . $lgetbdate[0] . "' AND intrates.peaksec is not null AND intrates.offpeaksec is not null
       ORDER BY call.uniqueid,length(countryprefix.prefix) DESC";

//print $callquery2;

    $upquery="INSERT INTO trunkcost SELECT DISTINCT ON (cdr.uniqueid) cdr.uniqueid,fn_calldist(userfield,'" . $expos[0] . "'),
         CASE WHEN (cdr.dstchannel ~ '" . $trunkchan . "|(^Local/)') AND dst != 's' THEN
           CASE WHEN(CAST(to_char(calldate, 'HH24:MI:SS') AS time) >= peakstart AND CAST(to_char(calldate, 'HH24:MI:SS') AS time) <= peakend AND
                     CAST(extract('dow' FROM calldate) AS varchar) ~ peakdays ) THEN
             fn_natcallcost(peakmin,peaksec,peakperiod,billsec)
           ELSE
             fn_natcallcost(offpeakmin,offpeaksec,offpeakperiod,billsec)
           END
         ELSE
           -1
         END,index
       FROM cdr
         LEFT OUTER JOIN trunkcost USING (uniqueid)
         LEFT OUTER JOIN localrates ON (userfield ~ match)
       WHERE
         trunkcost.uniqueid IS NULL AND
         disposition = 'ANSWERED' AND
         cdr.dstchannel != '' AND
         localrates.index IS NOT NULL AND
         ((localrates.distance <= fn_calldist(userfield,'" . $expos[0] . "') AND localrates.distance >= 0) OR localrates.distance IS NULL) AND
         localrates.validfrom < calldate AND localrates.validto > calldate AND
         calldate >= '" . $lgetbdate[0] . "'
       ORDER BY cdr.uniqueid,localrates.distance DESC";

//	print $upquery . "\n";

    $upquery2="INSERT INTO trunkcost SELECT DISTINCT ON (cdr.uniqueid) cdr.uniqueid,-1,
         CASE WHEN (cdr.dstchannel ~ '" . $trunkchan . "|(^Local/)') AND dst != 's' THEN
           CASE WHEN(CAST(to_char(calldate, 'HH24:MI:SS') AS time) >= peakstart AND CAST(to_char(calldate, 'HH24:MI:SS') AS time) <= peakend AND
                     CAST(extract('dow' FROM calldate) AS varchar) ~ peakdays ) THEN
             fn_natcallcost(peakmin,peaksec,peakperiod,billsec)
           ELSE
             fn_natcallcost(offpeakmin,offpeaksec,offpeakperiod,billsec)
           END
         ELSE
           -1
         END,-1
       FROM cdr
         LEFT OUTER JOIN trunkcost USING (uniqueid)
         LEFT OUTER JOIN countryprefix ON (countryprefix.prefix = substr(userfield,3,length(countryprefix.prefix)))
         LEFT OUTER JOIN intrates USING (countrycode,subcode)
       WHERE
         intrates.validfrom < now() AND validto > now() AND
         trunkcost.uniqueid IS NULL AND
         disposition = 'ANSWERED' AND
         cdr.dstchannel != '' AND userfield ~  '^00' AND 
         calldate >= '" . $lgetbdate[0] . "' AND intrates.peaksec is not null AND intrates.offpeaksec is not null AND length(userfield) > 4
       ORDER BY cdr.uniqueid,length(countryprefix.prefix) DESC";
//    print $upquery . "<P>";
//    print $upquery2 . "<P>";
     pg_query($db,$callquery);
     pg_query($db,$callquery2);
     pg_query($db,$upquery);
     pg_query($db,$upquery2);
  }
  if ($type == "1") {
    $chan="(cdr.dstchannel ~ '^IAX2/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+:[0-9]+-[0-9]+' OR
            cdr.dstchannel ~ '^IAX2/parent-') AND (userfield ~ '^[0-9]{4}\$' OR userfield ~ '^[0-9]{8}\$')";
  } else if ($type == "2") {
    $chan="((cdr.dstchannel ~ '^mISDN/[0-9]+' OR cdr.dstchannel ~ 'Zap/[0-9]+' OR cdr.dstchannel ~ 'DAHDI/[0-9]+') AND 
            astdb.value is null AND 
            userfield ~ '^0[1-6][0-9]{8}$' AND
            cdr.accountcode ~ '^[0-9]{4}\$')";
  } else if ($type == "9") {
    $chan="((cdr.dstchannel ~ '^mISDN/[0-9]+' OR cdr.dstchannel ~ 'Zap/[0-9]+' OR cdr.dstchannel ~ 'DAHDI/[0-9]+') AND 
            userfield ~ '(^07[2-4689][0-9]{7}$)|(^08[2-47][0-9]{7}$)' AND
            cdr.accountcode ~ '^[0-9]{4}\$')";
  } else if ($type == "8") {
    $chan="(dst ~ '(^00...)|(^09...)')";
  } else if ($type == "3") {
    $chan="(cdr.accountcode ~ '^[0-9]{4}\$' AND
            cdr.dstchannel != '' AND 
            NOT cdr.dstchannel ~  '^IAX2/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+:[0-9]+-[0-9]+' AND 
            NOT cdr.dstchannel ~  '^IAX2/parent-' AND 
            NOT cdr.dstchannel ~ '^Zap/[0-9]+-' AND NOT cdr.dstchannel ~ '^DAHDI/[0-9]+-' AND
            NOT cdr.dstchannel ~ '^mISDN/[0-9]+-' AND userfield ~ '^[0-9]{4}\$')";
  } else if ($type == "7") {
     $chan="(channel ~ '^SIP/[0-9]{8}-' OR cdr.dstchannel ~ '^SIP/[0-9]{8}-')";
  } else if ($type == "4") {
    $chan="(cdr.dstchannel ~ '" . $trunkchan . "' AND 
            dst ~ '(^0[1-6][0-9]{8}$)|(^0881[0-9]{6}$)|(^080[0-9]{7}$)|(^086[0-1][0-9]{6}$)|(^0862000000$)|(^0862[129][0-9]{5}$)|(^0867[1-5][0-9]{5}$)|(^0866[0-9]{6}$)|(^088[0-9]{7}$)|(^1021[0-9]$)|(^1013[1-6]$)|(^10177$)|(^1011[16]$)|(^102[0-9]$)|(^0900$)')";
  } else if ($type == "0") {
    $chan="(cdr.dstchannel ~ '" . $trunkchan . "')";
  } else if ($type == "5") {
    $chan="((channel ~ '^Zap/[0-9]+-' OR channel ~ '^DAHDI/[0-9]+-' OR
            channel ~ '^mISDN/[0-9]+-' ) AND
            dst ~ '(^[0-9]{4}$)|(^799$)|(^5[0-9]{2}$)')"; 
  } else {
    $chan="cdr.dstchannel ~ '" . $trunkchan . "'";
  }

  $baseurl="&exep=" . $exep . "&xexep=" . $xexep . "&date=" . urlencode($_POST['date']);
  $baseurl.="&type=" . $type;
  $baseurl.="&mweight=" . $mweight . "&morder=" . urlencode($morder);
}

?>
