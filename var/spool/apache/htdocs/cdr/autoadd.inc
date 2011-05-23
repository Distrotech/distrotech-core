<%

function randpwgen($len) {
  do {
    $newcode="";
    for($cnt=0;$cnt < $len;$cnt++) {
      $newcode .= chr(!rand(0, 2) ? rand(48, 57) : (!rand(0, 1) ? rand(65, 90) : rand(97, 122)));
    }
  } while (!ereg("[a-z]",$newcode) || !ereg("[A-Z]",$newcode) || !ereg("[0-9]",$newcode));
  return $newcode;
}

function newpin($exten) {
  global $db;

  $pincnt=1;
  $pintry=1;

  while (($pintry <= 10) && ($pincnt > 0)) {
    $randpin=rand(0,9999);
    $randpin=str_pad($randpin,4,"0",STR_PAD_LEFT);
    $pincntq=pg_query($db,"SELECT count(id) FROM astdb WHERE key='RoamPass' AND value='" . $randpin . "'");
    list($pincnt)=pg_fetch_array($pincntq,0);
    $pintry++;
  }
  if ($pincnt == 0) {
    $ud=pg_query($db,"UPDATE astdb SET value='" . $randpin . "' WHERE key='RoamPass' AND family='" . $exten . "'");
    if (pg_affected_rows($ud) <= 0) {
      pg_query("INSERT INTO astdb (family,key,value) VALUES ('" . $exten . "','RoamPass','" . $randpin . "')");
    }
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
      $newexten="'" . $autovars['DefaultPrefix'] . "'||lpad('" . $cno . "',2,0)";
      $newepass=randpwgen(8);

      if ($phonetype == "SNOM") {
        $encrypt="try";
      } else {
        $encrypt="no";
      }

      $exaddq="INSERT INTO users (context,name,defaultuser,mailbox,secret,password,usertype,
                                       fullname,email,callgroup,pickupgroup,qualify,encryption) VALUES (
                                       '6'," . $newexten . "," . $newexten . "," . $newexten . ",'" . $newepass . "'," . $newexten . "
                                       ,'0','Exten '||" . $newexten . ",'','1','1','yes','" . $encrypt . "')";
      pg_query($db,$exaddq);
      pg_query($db,"DELETE FROM astdb where family=" . $newexten);
      pg_query($db,"INSERT INTO astdb (family,key,value) SELECT " . $newexten . ",'TOUT',value FROM astdb WHERE family='Setup' AND key='Timeout'");
      pg_query($db,"INSERT INTO astdb (family,key,value) SELECT " . $newexten . ",'NOVMAIL',value FROM astdb WHERE family='Setup' AND key='DEFNOVMAIL'");

      pg_query($db,"INSERT INTO astdb (family,key,value) SELECT " . $newexten . ",'ACCESS',value FROM astdb WHERE family='Setup' AND key='Context'");
      pg_query($db,"INSERT INTO astdb (family,key,value) SELECT " . $newexten . ",'AUTHACCESS',value FROM astdb WHERE family='Setup' AND key='AuthContext'");
      pg_query($db,"INSERT INTO astdb (family,key,value) SELECT " . $newexten . ",'ALOCK',value FROM astdb WHERE family='Setup' AND key='DEFALOCK'");
      pg_query($db,"INSERT INTO astdb (family,key,value) SELECT " . $newexten . ",'RECORD',value FROM astdb WHERE family='Setup' AND key='DEFRECORD'");

      pg_query("INSERT INTO astdb (family,key,value) VALUES (" . $newexten . ",'FWDU','0')");
      pg_query("INSERT INTO astdb (family,key,value) VALUES (" . $newexten . ",'WAIT','1')");
      pg_query("INSERT INTO astdb (family,key,value) VALUES (" . $newexten . ",'CFIM','0')");
      pg_query("INSERT INTO astdb (family,key,value) VALUES (" . $newexten . ",'CFNA','0')");
      pg_query("INSERT INTO astdb (family,key,value) VALUES (" . $newexten . ",'CFBU','0')");
      pg_query("INSERT INTO astdb (family,key,value) VALUES (" . $newexten . ",'FWDU','0')");

      if ($autovars['AutoVLAN'] > 1) {
        pg_query("INSERT INTO astdb (family,key,value) VALUES (" . $newexten . ",'VLAN','" . $autovars['AutoVLAN'] . "')");
      } else {
        pg_query("INSERT INTO astdb (family,key,value) VALUES (" . $newexten . ",'VLAN','')");
      }

      pg_query("INSERT INTO astdb (family,key,value) VALUES (" . $newexten . ",'SNOMLOCK','" . $autovars['AutoLock'] . "')");
      if ($phonetype != "") {
        pg_query("INSERT INTO astdb (family,key,value) VALUES (" . $newexten . ",'PTYPE','" . $phonetype . "')");
      }
      if (($tdmport > 0) && ($tdmport != "")) {
        pg_query("INSERT INTO astdb (family,key,value) VALUES (" . $newexten . ",'ZAPLine','" . $tdmport . "')");
      } else {
        pg_query("INSERT INTO astdb (family,key,value) VALUES (" . $newexten . ",'AUTOAUTH','" . $autovars['AutoAuth'] . "')");
      }
      if ($mac != "") {
        pg_query("INSERT INTO astdb (family,key,value) VALUES (" . $newexten . ",'SNOMMAC','" . $mac . "')");
      }
      $ret=$extennum;
      newpin($newexten);
    }
  }
  return $ret;
}
%>
