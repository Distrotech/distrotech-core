<%
include "/var/spool/apache/htdocs/session.inc";

$sessid=session_id();
if ($sessid == "") {
  ob_start('ob_gzhandler');
  session_name("callshop");
  session_set_cookie_params(28800);
  session_start();
  $sessid=session_id();
}

$qlink['callshop']="cshop/";
$qlink['voip']="cshop/";
   
preg_match("/^([a-z]+)\.(.*)/i",$_SERVER['SERVER_NAME'], $matches);
if ($qlink[$matches[1]] != "") {
  $_SERVER['SCRIPT_URL']="/" . $qlink[$matches[1]];
  if (($_POST['disppage'] == "") && ($_SESSION['disppage'] == "")) {
    $_POST['disppage']=$qlink[$matches[1]];
  }
}

if (($_POST['vboxlogoff'] == $sessid) && ($_POST['vboxlogoff'] != "")) {
  session_regenerate_id(TRUE);
  unset($_SESSION['resellerid']);
  $_SESSION=array();
  $_SESSION['auth']=true;
  $_SESSION['reload']=false;
  session_write_close();
  header("Location: " . $_SERVER['SCRIPT_URL']);
  exit;
} else if ((!isset($_SESSION['auth'])) || ($_SESSION['auth'])) {
  $_SESSION['auth']=false;
  header( "WWW-Authenticate: Basic realm=\"Call Shop Admin Login\"");
  header( "HTTP/1.0 401 Unauthorized");
  print "<link rel=stylesheet type=text/css href=/netsentry.php>";
  print "<CENTER><H1>You Need To Be A Authorised User";
  exit;
} else if (((!$_SESSION['auth']) && (!isset($_SESSION['resellerid']))) || ($_SESSION['reload'])) {
  $pos=strpos($_SERVER['PHP_AUTH_USER'],"@");
  if ($pos > 0) {
    $owner=substr($_SERVER['PHP_AUTH_USER'],strpos($_SERVER['PHP_AUTH_USER'],"@")+1);
    $user=substr($_SERVER['PHP_AUTH_USER'],0,strpos($_SERVER['PHP_AUTH_USER'],"@"));
  } else {
    $user=$_SERVER['PHP_AUTH_USER'];
    $owner=$_SERVER['SERVER_NAME'];
  }
  $rdom=$matches[2];

  $plupq="SELECT admin,reseller.id,realmid,exchangerate,rlevel,owner,buyrate,realm.domain,seslimit FROM reseller
                     LEFT OUTER JOIN realm ON (realm.id=realmid AND (realm.domain = '" . $owner . "' OR realm.domain = '" . $rdom   . "'))
                       WHERE username = '" . $user . "' AND
                             userpass = '" . $_SERVER['PHP_AUTH_PW'] . "' AND (realm.domain IS NOT NULL OR reseller.id=0) ORDER BY length(domain) DESC LIMIT 1";
  $ruser=pg_query($db,$plupq);
  $num=pg_num_rows($ruser);
 
 if ($num > 0) {
    $r = pg_fetch_row($ruser,0);
    $_SESSION['resellerid']=$r[1];
    $_SESSION['realmid']=$r[2];
    $_SESSION['rexrate']=$r[3];
    $_SESSION['rlevel']=$r[4];
    $_SESSION['seslimit']=$r[8];
    if ($r[0] == "t") {
      $_SESSION['auser']="1";
      if (!isset($_SESSION['showmenu'])) {
        $_SESSION['showmenu']="users";
      }
      $_SESSION['resellownid']=$r[1];
      $_SESSION['rbuyrate']=$r[6];
      $usercntq=pg_query($db,"SELECT count(id) from reseller where admin != 't' AND owner=" . $_SESSION['resellerid']);
      $usrcntr=pg_fetch_row($usercntq,0);
      $_SESSION['usrcnt']=$usrcntr[0];
      if ($r[1] == "0") {
        if ($r[7] == "") {
          pg_query($db,"INSERT INTO realm (id,domain,description) VALUES ('" .$r[2] . "','" . $rdom . "','" . $rdom . "')");
        }
        if ($r[6] != "A") {
          $countryq="INSERT INTO tariffrate (tariffcode,countrycode,subcode,rate,trunkprefix) SELECT DISTINCT 'A',countryprefix.countrycode,countryprefix.subcode,0,countryprefix.trunkprefix 
                          FROM  countryprefix LEFT OUTER JOIN tariffrate ON (tariffrate.countrycode=countryprefix.countrycode AND tariffrate.tariffcode='A') 
                        WHERE tariffrate.id is NULL ORDER BY countryprefix.countrycode,countryprefix.subcode";
          pg_query($db,$countryq);
          pg_query($db,"INSERT INTO tariff (tariffcode, tariffname, tax, showtax, minrate, margin, switchfee) VALUES ('A', 'Master Rate', 0, false, 0, 0, 0)");
          pg_query($db,"UPDATE reseller SET buyrate = 'A',exchangerate=1 where id=0");
          $_SESSION['rbuyrate']='A';
        }
        $mrlevs=pg_query($db,"SELECT max(rlevel) from reseller");
        $mrlev=pg_fetch_row($mrlevs,0);
        for($level=1;$level<=$mrlev[0];$level++) {
          $tarifup="INSERT INTO tariffrate (startdate,countrycode,subcode,tariffcode,rate,trunkprefix) 
                      SELECT DISTINCT now(),countryprefix.countrycode,countryprefix.subcode,tariff.tariffcode,(tariffrate.rate*10000)/(10000-margin)+switchfee,countryprefix.trunkprefix 
                        FROM  countryprefix 
                          LEFT OUTER JOIN tariffrate ON (tariffrate.countrycode=countryprefix.countrycode AND tariffrate.subcode=countryprefix.subcode AND tariffrate.trunkprefix=countryprefix.trunkprefix) 
                          LEFT OUTER JOIN reseller ON (buyrate=tariffrate.tariffcode) 
                          LEFT OUTER JOIN tariff ON (tariff.tariffcode = tariff.tariffcode AND tariff.resellerid=reseller.id) 
                          LEFT OUTER JOIN tariffrate AS trcheck ON (tariff.tariffcode = trcheck.tariffcode AND tariffrate.countrycode=trcheck.countrycode AND 
                                                                    tariffrate.subcode=trcheck.subcode AND tariffrate.trunkprefix=trcheck.trunkprefix) 
                        WHERE tariffrate.rate > 0 AND buyrate=tariffrate.tariffcode AND trcheck.rate IS NULL AND reseller.rlevel= " . $level . " AND tariff.tariffcode IS NOT  NULL
                          ORDER BY countryprefix.countrycode,countryprefix.subcode";
//          print $tarifup . "<P>";
          pg_query($db,$tarifup);
        }
      }
    } else {
      if (!isset($_SESSION['showmenu'])) {
        $_SESSION['showmenu']="apps";
      }
      $_SESSION['resellownid']=$r[5];
      $_SESSION['auser']="0";
      $_SESSION['usrcnt']="0";
    }
    $_SESSION['reload']=false;
  } else {
    $_SESSION['auth']=true;
  }
}

if (isset($_COOKIE[session_name()])) {
  setcookie(session_name(), $_COOKIE[session_name()], time() + $_SESSION['seslimit'], "/");
}

%>
