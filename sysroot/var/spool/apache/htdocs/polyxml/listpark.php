<?
$API_HOST="127.0.0.1";
$API_PORT=5038;
$API_USER="put-your-username-here";
$API_PASS="put-your-password-here";


echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "xhtml11.dtd">'."\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml">'."\n";
echo '<head> <title>CTSNY PBX Parked</title> </head>'."\n";
echo '<body>'."\n";

parked_calls();

function parked_calls()
{
global $agi;
global $API_HOST;
global $API_PORT;
global $API_USER;
global $API_PASS;
global $PARKEDOK;
global $stp;

$fp=fsockopen($API_HOST,$API_PORT,$errno,$errstr,20);

fputs($fp,"Action: login\r\n");
fputs($fp,"Username: ".$API_USER."\r\n");
fputs($fp,"Secret: ".$API_PASS."\r\n");
fputs($fp,"Events: off\r\n");
fputs($fp,"\r\n");

fputs($fp,"Action: parkedcalls\r\n");
fputs($fp,"\r\n");

    $exten='';
    $timeout=0;
    $started=false;
    while (!feof($fp)) 
	{
	$line=fgets($fp, 128);
	//echo $line;
	$line=str_replace("\n","",$line);
	$line=str_replace("\r","",$line);
	if(strlen($line)<5)
	    {
	    //an record has ended
	    if($started)
		{
		echo '<p>'.$exten."#".$timeout."#".$callerid."#".$calleridname."</p>\n";
		}
	    $exten='';
	    $channel='';
	    $timeout='';
	    $callerid='';
	    $calleridname='';
	    }
	if(strpos($line,"Event: ParkedCall") !== false)
	    {
	    $started=true;
	    }
	if(strpos($line,"Exten: ") !== false)
	    {
	    $pieces=explode(":",$line);
	    $exten=str_replace("\n","",$pieces[1]);
	    }
	if(strpos($line,"Channel: ") !== false)
	    {
	    $pieces=explode(":",$line);
	    $channel=str_replace("\n","",$pieces[1]);
	    }
	if(strpos($line,"Timeout: ") !== false)
	    {
	    $pieces=explode(":",$line);
	    $timeout=str_replace("\n","",$pieces[1]);
	    }
	if(strpos($line,"CallerID: ") !== false)
	    {
	    $pieces=explode(":",$line);
	    $callerid=str_replace("\n","",$pieces[1]);
	    }
	if(strpos($line,"CallerIDName: ") !== false)
	    {
	    $pieces=explode(":",$line);
	    $calleridname=str_replace("\n","",$pieces[1]);
	    }
	if (strpos($line,"ParkedCallsComplete") !== false)
	    {
	    fputs($fp,"Action: logoff\r\n");
	    fputs($fp,"\r\n");
	    fclose($fp);
	    break;
	    }
        }
}
echo '</body>'."\n";
echo '</html>'."\n";
?>