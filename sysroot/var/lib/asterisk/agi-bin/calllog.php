<?php

include "/var/lib/asterisk/agi-bin/functions.inc";

$socket="";

function asmconnect($username,$password,$timeout) {
  global $socket;
  if (!$socket) {
    $socket=fsockopen("127.0.0.1","5038", $errno, $errstr, $timeout);
  }

  fputs($socket,"Action: Login\r\n");
  fputs($socket,"UserName: " . $username . "\r\n");
  fputs($socket,"Secret: " . $password . "\r\n");
  fputs($socket,"Events: off\r\n");
  fputs($socket,"\r\n");
  return $socket;
}

function agisend($agicmd) {
  $pcnt=0;
  $agiinf=array();
  $chaninf=array();

  $socket=asmconnect("admin","admin",120);
  if (!$socket)
    return null;

  while(list($key,$value) = each($agicmd)) {
    fputs($socket,$key . ": " . $value . "\r\n");
  }
  fputs($socket,"\r\n");

  fputs($socket,"Action: Logoff\r\n");
  fputs($socket,"\r\n");

  while($wrets=fgets($socket,8192)) {
    $wrets=rtrim($wrets);
    if ($wrets != "") {
      list($key,$val)=preg_split("/: /",$wrets);
      if ($val == "") {
        $key=substr($key,0,-1);
      }
      if (($key == "Response") && ($val == "Error")) {
        break;
      } else {
        $chaninf[$key]=$val;
      }
    } else {
      $pcnt++;
      array_push($agiinf,$chaninf);
      $chaninf=array();
    }
  }
  if (count($agiinf >= 4)) {
    array_shift($agiinf);
    array_shift($agiinf);
    array_pop($agiinf);
    array_pop($agiinf);
    if (count($agiinf) > 1) {
      return $agiinf;
    } else {
      return $agiinf[0];
    }
  }
  return null;
}

$agicmd['Action']="Status";
$agicmd['Channel']=$remchan;

$agiinf=agisend($agicmd);
verbose($agiinf);

if ($dstchan != "") {
  $agicmd['Channel']=$dstchan;
  $agiinf=agisend($agicmd);
  verbose($agiinf);
}
?>
