<?

$API_HOST="127.0.0.1";
$API_PORT=5038;
$API_USER="put-your-username-here";
$API_PASS="and-password-here";


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

fputs($fp,"Action: command\r\n");
fputs($fp,"Command: sip show peers\r\n");
fputs($fp,"\r\n");

    $exten='';
    $timeout=0;
    $started=false;
    while (!feof($fp)) 
	{
	$line=(fgets($fp, 4096));
	//echo $line;
	if (strpos($line,"--END COMMAND--") !== false)
	    {
	    //this is the end
	    $started=false;
	    fputs($fp,"Action: logoff\r\n");
	    fputs($fp,"\r\n");
	    fclose($fp);
	    break;
	    }
	if($started)
	    {
	    $name=trim(substr($line,0,17));
	    $host=trim(substr($line,17,15));
	    $status=trim(substr($line,71));
	    echo "<p>".$name."#".$host."#".$status."</p>\n";
	    }
	if (strpos($line,"Response: Follows") !== false)
	    {
	    //Response will follow
	    $started=true;
	    }
	if (strpos($line,"Name/username    ") !== false)
	    {
	    //this is the header
	    }
        }
//echo '</table>'."\n";       
}
echo '</body>'."\n";
echo '</html>'."\n";
?>