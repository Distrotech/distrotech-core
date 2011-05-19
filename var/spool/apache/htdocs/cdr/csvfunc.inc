<%
function booltostr($strin) {
  $quin=strtolower($strin);
  if (($strin == "yes") || ($quin == "1") || ($quin == "y") || ($quin == "t") || ($quin == "true")) {
    return "Yes";
  } else {
    return "No";
  }
}

function nozeroout($strin) {
  if ($strin == "0") {
    return "";
  } else {
    return "\"" . $strin . "\"";
  }
}

function getcodecs($allowed) {
  $codecs=split(";",$allowed);
  $acodec=array();
  $vcodec=array();
  while($icodec=array_shift($codecs)) {
    if (strstr($icodec,"h26")) {
      array_push($vcodec,$icodec);
    } else {
      array_push($acodec,$icodec);
    }
  }
  return array($acodec,$vcodec);
}

function telformat($number) {
  if (($number[0] == "0") && (strlen($number) == "10")) {
    $number=sprintf("(%s) %s-%s",substr($number,0,3),substr($number,3,3),substr($number,6));
  } else if (($number[0] != "0") && (strlen($number) == "7")) {
    $number=sprintf("%s-%s",substr($number,0,3),substr($number,3));
  } else if (($number[0] == "0") && (($number[1] == "0") || ($number[1] == "9")) && (strlen($number) > 4)) {
    $intcode=pg_query("SELECT prefix from countryprefix where prefix=substr('" . substr($number,2) . "',1,length(prefix)) order by length(prefix) LIMIT 1");
    $icode=pg_fetch_array($intcode,0);
    if ($icode != "") {
      $onum=substr($number,2+strlen($icode[0]));
      if (strlen($onum) > "7") {
        $number="+" . $icode[0] . " (" . substr($onum,0,strlen($number)-7) . ") " . substr($onum,strlen($onum)-7,3) . "-" . substr($onum,strlen($onum)-4);
      } else if (strlen($number) == "7") {
        $number="+" . $icode[0] . " " . substr($onum,0,3) . "-" . substr($onum,3);
      } else {
        $number="+" . $icode[0] . " " . $onum;
      }
    }
  } else if (($number[0] == "0") && (strlen($number) == "7")) {
    $number=sprintf("(%s) %s",substr($number,0,3),substr($number,3));
  } else if (($number[0] == "0") && (strlen($number) > 2)) {
    $number=sprintf("(0)%s",substr($number,1));
  }
  return $number;
}
%>
