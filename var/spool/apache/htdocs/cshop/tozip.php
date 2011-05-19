<%
header("Content-Type: application/zip");
include "/var/spool/apache/htdocs/cshop/auth.inc";
include_once "/var/spool/apache/htdocs/cdr/func.inc";

$start=$_GET['start'];
$end=$_GET['end'];

$sdarr=getdate(strtotime($start));
$edarr=getdate(strtotime($end));

$start.=" 00:00:00";
$end.=" 24:00:00";

$file=sprintf("%d%02d%02d_%d%02d%02d",$sdarr[year],$sdarr[mon],$sdarr[mday],$edarr[year],$edarr[mon],$edarr[mday]);

$zip = new ZipArchive();
$zfile="/tmp/" . $file . ".zip";
if ($zip->open($zfile, ZIPARCHIVE::CREATE)!==TRUE) {
    exit("cannot open <" . $zfile . ">\n");
}

$acquery="SELECT users.fullname,users.name,localrates.description,count(call.uniqueid),
       sum(call.sessiontime),sum(trunkcost.cost),sum(resellercall.sellcost-outtax) FROM call 
     LEFT OUTER JOIN trunkcost USING (uniqueid) 
     LEFT OUTER JOIN localrates ON (trunkcost.price=localrates.index) 
     LEFT OUTER JOIN users ON (name=call.username) 
     LEFT OUTER JOIN reseller ON (users.agentid=reseller.id)
     LEFT OUTER JOIN resellercall on (call.uniqueid=resellercall.calluid) 
   WHERE terminatecause='ANSWER' AND resellercall.resellerid = reseller.id AND reseller.id=0 AND
     (starttime >= '" . $start . "' AND starttime <= '" . $end . "')
 GROUP BY users.fullname,users.name,localrates.description,reseller.id 
 ORDER BY users.name,users.fullname,sum(trunkcost.cost) DESC";

$resquery="SELECT reseller.description,reseller.id,localrates.description,count(call.uniqueid),
                  sum(call.sessiontime),sum(trunkcost.cost),sum(resellercall.sellcost-outtax) FROM call
                LEFT OUTER JOIN trunkcost USING (uniqueid)
                LEFT OUTER JOIN localrates ON (trunkcost.price=localrates.index) 
                LEFT OUTER JOIN users ON (name=call.username)
                LEFT OUTER JOIN reseller ON (users.agentid=reseller.id)
                LEFT OUTER JOIN resellercall on (call.uniqueid=resellercall.calluid) 
              WHERE terminatecause='ANSWER' AND resellerid=0 AND 
                (starttime >= '" . $start . "' AND starttime <= '" . $end . "')
              GROUP BY reseller.description,localrates.description,reseller.id
              ORDER BY reseller.description,sum(trunkcost.cost) DESC";


$getac=pg_query($db,$acquery);
for($cnt=0;$cnt < pg_num_rows($getac);$cnt++) {
  $r=pg_fetch_array($getac,$cnt,PGSQL_NUM);
  if ($r[2] == "") {
    $r[2]="Unknown";
  }
  $fname=array_shift($r);
  $acc=array_shift($r);
  $route=array_shift($r);
  $out[$acc][$route]=$r;
  $ackey[$acc]=$fname;
}

while(list($acnt,$data) = each($out)) {
  $afile=sprintf("%s/accounts/%s_%s.csv",$file,$file,$acnt);
  $output="";
  while(list($route,$info)=each($data)) {
    $crow=array($route);
    $info[2]=sprintf("%0.2f",$info[2]/100000);
    $info[3]=sprintf("%0.2f",$info[3]/10000);
    $crow=array_merge($crow,$info);
    $output.=printcsv($crow);
  }
  $zip->addFromString($afile, $output);
}

$afile=sprintf("%s/accounts/key.csv",$file);
$output="";
while(list($acnum,$fname) = each($ackey)) {
  $ack=array($acnum,$fname);
  $output.=printcsv($ack);
}
$zip->addFromString($afile, $output);

$getres=pg_query($db,$resquery);
for($cnt=0;$cnt < pg_num_rows($getres);$cnt++) {
  $r=pg_fetch_array($getres,$cnt,PGSQL_NUM);
  if ($r[1] == 0) {
    continue;
  }
  if ($r[2] == "") {
    $r[2]="Unknown";
  }
  $rname=array_shift($r);
  $rid=array_shift($r);
  $route=array_shift($r);
  $outr[$rid][$route]=$r;
  $reskey[$rid]=$rname;
}

while(list($rid,$data) = each($outr)) {
  $afile=sprintf("%s/resellers/%s_%05d.csv",$file,$file,$rid);
  $output="";
  while(list($route,$info)=each($data)) {
    $crow=array($route);
    $info[2]=sprintf("%0.2f",$info[2]/100000);
    $info[3]=sprintf("%0.2f",$info[3]/10000);
    $crow=array_merge($crow,$info);
    $output.=printcsv($crow);
  }
  $zip->addFromString($afile, $output);
}

$afile=sprintf("%s/resellers/key.csv",$file);
$output="";
while(list($acnum,$fname) = each($reskey)) {
  $acnum=sprintf("%05d",$acnum);
  $ack=array($acnum,$fname);
  $output.=printcsv($ack);
}
$zip->addFromString($afile, $output);

$zip->close();
readfile($zfile);
unlink($zfile);
%>
