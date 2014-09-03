<?php
$API_HOST="127.0.0.1";
$API_PORT=5038;
$API_USER="your-username-here";
$API_PASS="and-password-here";


echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "xhtml11.dtd">'."\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml">'."\n";
echo '<head> <title>CTSNY MEETING ROOMS</title> </head>'."\n";
echo '<body>'."\n";

meetme();

echo '</body>'."\n";
echo '</html>'."\n";

function meetme()
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
fputs($fp,"Command: meetme\r\n");
fputs($fp,"\r\n");

    $timeout=0;
    $started=false;
    $conferences=0;
    while (!feof($fp)) 
	{
	$line=fgets($fp, 4096);
	//echo $line;
	$line=str_replace("\n","",$line);
	$line=str_replace("\r","",$line);
	if (strpos($line,"--END COMMAND--") !== false)
	    {
	    $started=false;
	    break;
	    }
	if ($started)
	    {
		echo "<p>".$line."</p>\n";
		$conf_room=str_replace(" ","",substr($line,0,strpos($line," ")));
		if(ctype_digit($conf_room))
		    {
		    $conf[$conferences]=$conf_room;
		    $conferences++;
		    }
	    }
	if (strpos($line,"Response: Follows") !== false)
	    {
	    //the records/answer follows
	    $started=true;
	    }
        }

    for($i=0;$i<$conferences;$i++)
	{
        fputs($fp,"Action: command\r\n");
        fputs($fp,"Command: meetme list ".$conf[$i]."\r\n");
	fputs($fp,"\r\n");
	echo "<p>Conference room ".$conf[$i]." has the following parties in:</p>\n";
	while(!feof($fp))
	    {
	    	$line=fgets($fp, 4096);
	    	//echo $line;
	    	$line=str_replace("\n","",$line);
	    	$line=str_replace("\r","",$line);
		if (strpos($line,"--END COMMAND--") !== false)
		    {
		    $started=false;
		    break;
		    }
		if($started)
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

    //log off and closing connection
    fputs($fp,"Action: logoff\r\n");
    fputs($fp,"\r\n");
    fclose($fp);

}
?>