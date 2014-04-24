<?

$API_HOST="127.0.0.1";
$API_PORT=5038;
$API_USER="put-your-username-here";
$API_PASS="and-password-here";


echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "xhtml11.dtd">'."\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml">'."\n";
echo '<head> <title>CTSNY QUEUES</title> </head>'."\n";
echo '<body>'."\n";

queues();

echo '</body>'."\n";
echo '</html>'."\n";

function queues()
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
fputs($fp,"Command: show queues\r\n");
fputs($fp,"\r\n");

    $exten='';
    $timeout=0;
    $started=false;
    while (!feof($fp)) 
	{
	$line=fgets($fp, 4096);
	//echo $line;
	$line=str_replace("\n","",$line);
	$line=str_replace("\r","",$line);
	if (strpos($line,"--END COMMAND--") !== false)
	    {
	    //the command output has ended
	    fputs($fp,"Action: logoff\r\n");
	    fputs($fp,"\r\n");
	    fclose($fp);
	    $started=false;
	    break;
	    }
	if ($started and $line!="")
	    {
		echo "<p>".$line."</p>\n";
	    }
	if (strpos($line,"Response: Follows") !== false)
	    {
	    //the records/answer follows
	    $started=true;
	    }
        }
}
?>