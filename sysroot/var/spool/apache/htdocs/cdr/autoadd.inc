<?php

function randpwgen($len) {
  do {
    $newcode="";
    for($cnt=0;$cnt < $len;$cnt++) {
      $newcode .= chr(!rand(0, 2) ? rand(48, 57) : (!rand(0, 1) ? rand(65, 90) : rand(97, 122)));
    }
  } while (!preg_match("/[a-z]/",$newcode) || !preg_match("/[A-Z]/",$newcode) || !preg_match("/[0-9]/",$newcode));
  return $newcode;
}

function newpin($exten) {
  global $db;

  $pincnt=1;
  $pintry=1;

  while (($pintry <= 10) && ($pincnt > 0)) {
    $randpin=rand(0,9999);
    $randpin=str_pad($randpin,4,"0",STR_PAD_LEFT);
    $pincntq=pg_query($db,"SELECT count(id) FROM features WHERE roampass='" . $randpin . "'");
    list($pincnt)=pg_fetch_array($pincntq,0);
    $pintry++;
  }
  if ($pincnt == 0) {
    $ud=pg_query($db,"UPDATE features SET roampass='" . $randpin . "' WHERE exten=" . $exten);
  }
  return $randpin;
}

function getdefvars() {
  global $db,$autovars;
  $qgetdata=pg_query($db,"SELECT key,value FROM astdb WHERE family='Setup' AND (key = 'DefaultPrefix' OR key ~ '^Auto')");
  $dnum=pg_num_rows($qgetdata);
  for($i=0;$i<$dnum;$i++){
    $getdata=pg_fetch_array($qgetdata,$i);
    $autovars[$getdata[0]]=$getdata[1];
  }
}


function setdefaults($newexten) {
  global $db;

  $defaults=pg_query($db,"SELECT key,value FROM astdb where family='Setup' and 
                  (key='DEFNOVMAIL' OR key='Timeout' OR key='Context' OR key='AuthContext' OR key='DEFALOCK' OR key='DEFRECORD' OR key='DEFIPNET' OR key='DEFAUTHREG')");

  $setdef['Timeout']="TOUT";
  $setdef['DEFNOVMAIL']="NOVMAIL";
  $setdef['Context']="ACCESS";
  $setdef['AuthContext']="AUTHACCESS";
  $setdef['DEFALOCK']="ALOCK";
  $setdef['DEFRECORD']="RECORD";
  $setdef['DEFIPNET']="ipnet";
  $setdef['DEFAUTHREG']="authreg";

  for($def=0;$def < pg_num_rows($defaults);$def++) {
    $r=pg_fetch_array($defaults,$def,PGSQL_NUM);
    pg_query($db,"UPDATE features SET " . $setdef[$r[0]] . "='" . $r[1] . "' where exten=" . $newexten);
  }
}

function createexten($mac,$phonetype,$prefix,$start,$tdmport) {
  global $db,$autovars;
  $ret="";

  if (!is_array($autovars)) {
    getdefvars();
  }
  
  if ($prefix != "") {
    $autovars['DefaultPrefix']=$prefix;
  }


  $autovars['AutoEnd']=str_pad($autovars['AutoEnd'],2,"0",STR_PAD_LEFT);
  if ($start != "") {
    $autovars['AutoStart']=str_pad($start,2,"0",STR_PAD_LEFT);
    $autovars['AutoEnd']=95;    
  } else {
    $autovars['AutoStart']=str_pad($autovars['AutoStart'],2,"0",STR_PAD_LEFT);
  }


  if ($autovars['AutoStart'] < $autovars['AutoEnd']) {
    $gettexten=pg_query($db,"SELECT substr(name,3,2) from users where name >= '" . $autovars['DefaultPrefix'] . $autovars['AutoStart'] . "' and name <= '" . $autovars['DefaultPrefix'] . $autovars['AutoEnd'] . "' order by name");
    $enum=pg_num_rows($gettexten);
    $cno=$autovars['AutoStart'];
    for($i=0;$i<$enum;$i++){
      $getdata=pg_fetch_array($gettexten,$i);
      if ($getdata[0] > $cno) {
        break;
      } else {
        $cno=$getdata[0]+1;
      }
    }
    if ($cno <= $autovars['AutoEnd']) {
      $extennum=sprintf("%02u%02u",$autovars['DefaultPrefix'],$cno);
      $newexten="'" . $autovars['DefaultPrefix'] . "'||lpad('" . $cno . "',2,'0')";
      $newepass=randpwgen(8);

      if (($phonetype == "SNOM") || ($phonetype == "YEALINK") || ($phonetype == "POLYCOM") || (substr($phonetype,0,3) == "IP_")) {
        $encrypt="yes";
      } else {
        $encrypt="no";
      }

      if (($phonetype == "SNOM") || ($phonetype == "YEALINK")) {
        $transport="tls";
      } else {
        $transport="udp";
      }

      $exaddq="INSERT INTO users (context,name,defaultuser,mailbox,secret,usertype,
                                       fullname,callgroup,pickupgroup,qualify,encryption,transport) VALUES (
                                       '6'," . $newexten . "," . $newexten . "," . $newexten . "||'@6','" . $newepass .
                                       "','0','Exten '||" . $newexten . ",'1','1','yes','" . $encrypt . "','" . $transport . "')";
      pg_query($db,$exaddq);
      pg_query($db,"INSERT INTO voicemail (mailbox,context,email,fullname,password) SELECT users.name,context,'',fullname,name FROM users WHERE users.name = " . $newexten);
      pg_query($db,"DELETE FROM astdb where family=" . $newexten);
      pg_query($db,"DELETE FROM features where exten=" . $newexten);
      pg_query($db,"INSERT INTO features (exten) VALUES (" . $newexten . ")");

      setdefaults($newexten);

      if ($autovars['AutoVLAN'] > 1) {
        pg_query("UPDATE features SET vlan='" . $autovars['AutoVLAN'] . "' where exten=" . $newexten);
      }
      pg_query("UPDATE features SET snommac='" . $autovars['AutoLock'] . "' where exten=" . $newexten);
      if ($phonetype != "") {
        pg_query("UPDATE features SET ptype='" . $phonetype . "' where exten=" . $newexten);
      }
      if (($tdmport > 0) && ($tdmport != "")) {
        pg_query("UPDATE features SET zapline='" . $tdmport . "' where exten=" . $newexten);
      } else {
        pg_query("UPDATE features SET autoauth='" . $autovars['AutoAuth'] . "' where exten=" . $newexten);
      }
      if ($mac != "") {
        pg_query("UPDATE features SET snommac='" . $mac . "' where exten=" . $newexten);
      }
      $ret=$extennum;
      newpin($newexten);
    }
  }
  return $ret;
}
?>
