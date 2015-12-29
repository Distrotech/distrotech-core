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

function apiopen() {
  $timeout=120;
  $socket = @fsockopen("127.0.0.1","5038", $errno, $errstr, $timeout); 

  if (! $socket) {
    return $socket;
  }

  fputs($socket,"Action: Login\r\n"); 
  fputs($socket,"UserName: admin\r\n"); 
  fputs($socket,"Secret: admin\r\n"); 
  fputs($socket,"Events: off\r\n"); 
  fputs($socket,"\r\n");

  $wrets=rtrim(fgets($socket,8192));
  while($wrets != "") {
    $wrets=rtrim(fgets($socket,8192));
  }
  return $socket;
}

function apiclose($socket) {
  fputs($socket,"Action: Logoff\r\n"); 
  fputs($socket,"\r\n");
  fclose($socket);
}

function apiquery($command) {
  if (($command == "SIPPeers") || ($command == "IAXPeers")) {
    $complete="PeerlistComplete";
  } else {
    $complete=$command . "Complete";
  }

  $socket=apiopen();
  if (! $socket) {
    return $socket;
  }

  fputs($socket,"Action: ". $command . "\r\n");
  fputs($socket,"\r\n");

  $pcnt=0;
  $apiinf=array();
  for (;;) {
    $wrets=rtrim(fgets($socket,8192));
    if ($wrets != "") {
      list($key,$val)=split(": ",$wrets);
      if (($key == "Response") && ($val == "Error")) {
        break;
      } else {
        if ($val == "") {
          $key=substr($key,0,-1);
        }
        $apiinf[$pcnt][$key]=$val;
      }
    } else {
      if ($apiinf[$pcnt]['Event'] == $complete) {
        break;
      }
      $pcnt++;
    }
  }
  
  apiclose($socket);

  array_shift($apiinf);
  array_pop($apiinf);
  return $apiinf;
}

function agentquery($agent) {
  $socket=apiopen();
  if (! $socket) {
    return $socket;
  }

  $agentdat=getagentstatus($agent,$socket);
  apiclose($socket); 
  return $agentdat;
}

function getagentstatus($agent,$socket) {
  $astatus['AGENT_UNKNOWN']=-2;
  $astatus['AGENT_LOGGEDOFF']=-1;
  $astatus['AGENT_IDLE']=0;
  $astatus['AGENT_ONCALL']=1;

  fputs($socket,"Action: Agents\r\n");
  fputs($socket,"\r\n");

  $pcnt=0;
  $apiinf=array();
  for (;;) {
    $wrets=rtrim(fgets($socket,8192));
    if ($wrets != "") {
      list($key,$val)=split(": ",$wrets);
      if (($key == "Response") && ($val == "Error")) {
        break;
      } else {
        if ($val == "") {
          $key=substr($key,0,-1);
        }
        $apiinf[$key]=$val;
      }
    } else {
      if ($apiinf['Event'] == "AgentsComplete") {
        $apiinf=array();
        break;
      } else if ($apiinf['Agent'] == $agent) {
        break;
      }
      $apiinf=array();
      $pcnt++;
    }
  }

  if (count($apiinf) > 0) {
    $apiinf['Status']=$astatus[$apiinf['Status']];
    unset($apiinf['Event']);
    return $apiinf;
  } else {
    return null;
  }
}

function chanstatus($channel) {
  $okkeys=array("Link","Channel","Uniqueid");
  for($okc=0;$okc<count($okkeys);$okc++) {
    $isokkey[$okkeys[$okc]]=true;
  }
  $socket=apiopen();
  if (! $socket) {
    return $soocket;
  }
  $chaninf=getchanstatus($channel,$socket);
  apiclose($socket);
  return $chaninf;
}

function getchanstatus($channel,$socket) {
  fputs($socket,"Action: Status\r\n");
  if (strpos($channel,"/") > 0) {
    fputs($socket,"Channel: " . $channel . "\r\n");
  } else {
    $chanlist=array();
  }
  fputs($socket,"\r\n");

  $chaninf=array();
  $pcnt=0;
  for(;;) {
    $wrets=rtrim(fgets($socket,8192));
    if ($wrets != "") {
      list($key,$val)=split(": ",$wrets);
      if (($key != "Response") && ($pcnt > 0)) {
        $chaninf[$key]=$val;
      } else if ($val == "Error") {
        break;
      }
    } else if (count($chaninf) == 1) {
      $chaninf=array();
      break;
    } else {
      if (preg_match("/^([^/]+)/([0-9]{4})/",$chaninf['Channel'],$datainf)) {
        if (($datainf[2] == $channel) || ($chaninf['Channel'] == $channel)) {
          break;
        }
      }
      if (($channel == "") && ($chaninf['Uniqueid'] != "")) {
        $chanlist[$chaninf['Uniqueid']]=$chaninf;
      }
      $chaninf=array();
      $pcnt++;
    }
    if ($chaninf['Event'] == "StatusComplete") {
      $chaninf=array();
      break;
    }
  }

  if (count($chaninf) > 0) {
    return $chaninf;
  } else if (count($chanlist) > 0) {
    return $chanlist;
  } else {
    return null;
  }
}

function apihangupchan($channel,$socket) {
  fputs($socket,"Action: Hangup\r\n");
  fputs($socket,"Channel: " . $channel . "\r\n");
  fputs($socket,"\r\n");  
}
?>
