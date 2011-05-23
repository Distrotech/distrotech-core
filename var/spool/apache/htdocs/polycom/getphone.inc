<%
include "../cdr/auth.inc";
include "../cdr/autoadd.inc";

$mac=ereg_replace(":","",$mac);
$mac=strtoupper($mac);

$pwlen=8;
$getports="SELECT users.name,proxy.value,users.fullname,users.secret,users.nat,ptype.value,callgroup,
                  dgroup.value,lndir.value,(name=secret OR length(secret) != " . $pwlen . " OR secret='' OR 
                    secret IS NULL OR secret !~ '[0-9]' OR secret !~ '[a-z]' OR secret !~ '[A-Z]'),encryption
             FROM astdb AS mac LEFT OUTER JOIN astdb AS proxy ON (proxy.family=mac.family AND proxy.key='REGISTRAR') 
               LEFT OUTER JOIN astdb AS ptype ON (ptype.family=mac.family AND ptype.key='PTYPE')
               LEFT OUTER JOIN users ON (name=mac.family)
               LEFT OUTER JOIN astdb AS dgroup ON (name=dgroup.family AND dgroup.key='DGROUP')
               LEFT OUTER JOIN astdb AS lndir ON (name=lndir.family AND lndir.key='POLYDIRLN')
             WHERE mac.key='SNOMMAC' AND mac.value='" . $mac . "'";

$uports=pg_query($db,$getports);

if (pg_num_rows($uports) == 0) {
  $uadata=explode("-",$_SERVER['HTTP_USER_AGENT']);
  $pdata=explode("_",$uadata[1]);
  if ($pdata[1] == "4000") {
    $nptype="IP_4000";
  } else if ($pdata[1] == "600") {
    $nptype="IP_600";
  } else if (($pdata[1] == "601") || ($pdata[1] == "650")) {
    $nptype="IP_601";
  } else if (($pdata[1] == "500") || ($pdata[1] == "501") || ($pdata[1] == "550")) {
    $nptype="IP_500";
  } else {
    $nptype="POLYCOM";
  }
  if (createexten($mac,$nptype,"","","") > 0) {
    $uports=pg_query($db,$getports);
  }
}
list($exten,$proxy,$dname,$passwd,$pnat,$ptype,$callgroup,$dirgroup,$lnsort,$pwchange,$encryption)=pg_fetch_array($uports,$port);
if ($pwchange == "t") {
  if (! isset($agi)) {
    require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
    $agi=new AGI_AsteriskManager();
    $agi->connect("127.0.0.1","admin","admin");
  }
  $agi->command("sip prune realtime peer " . $exten);
  $agi->command("sip prune realtime user " . $exten);
  $passwd=randpwgen($pwlen);
  pg_query($db,"UPDATE users SET secret='" . $passwd . "' WHERE name='" . $exten . "'");
  $agi->disconnect();
}
%>
