<?
session_start();
set_time_limit(5);

$API_HOST='127.0.0.1';
$API_USER='put-your-username-here';
$API_PASS='put-your-password-here';
$API_PORT=5038;

echo '<head> <title>SIP.PH.STAT.</title> </head>'."\n";
echo '<body>'."\n";

api_login();

api_sip_peers();
api_sip_channels();
print_sip();

api_logoff();

echo '</body>'."\n";
echo '</html>'."\n";

?>
<?
function api_login()
{
global $fp;
global $API_HOST;
global $API_PORT;
global $API_USER;
global $API_PASS;
global $PARKEDOK;
global $stp;

$fp=fsockopen($API_HOST,$API_PORT,$errno,$errstr,20);

if($errno>0)
    {
    echo "SocketOpen error no: ".$errno." , SocketOpen error str: " .$errstr."<BR>";
    echo $API_HOST.":".$API_PORT."<BR>";
    $fp=false;
    return;
    }

fputs($fp,"Action: login\r\n");
fputs($fp,"Username: ".$API_USER."\r\n");
fputs($fp,"Secret: ".$API_PASS."\r\n");
fputs($fp,"Events: off\r\n");
fputs($fp,"\r\n");
}


function api_sip_peers()
{
global $fp;
global $API_HOST;
global $API_PORT;
global $API_USER;
global $API_PASS;
global $SIP_PEERS;
global $SIP_PEERS_COUNT;

fputs($fp,"Action: command\r\n");
fputs($fp,"command: sip show peers\r\n");
fputs($fp,"\r\n");

$SIP_PEERS_COUNT=0;

if(!$fp)
    {
    return;
    }

    $started=false;    
    while(!feof($fp))
	{
	//0-16 name/username
	//17-33 IP
	//33-36 DYN
	//37-40 NAT
	//41-44 SYN
	//
	
        $line=fgets($fp,4096);
	//$line=str_replace(" ", "&nbsp",$line);
	//echo $line."";
        if (strpos($line,'--END COMMAND--') !== false)
	    {
		$started=false;
		break;
	    }
	else
	{
	if($started)
	    {
	    $pieces=explode('/',$line);
	    $SIP_PEERS[$SIP_PEERS_COUNT][0]=trim($pieces[0]);
	    $SIP_PEERS[$SIP_PEERS_COUNT][1]="free";
	    $SIP_PEERS[$SIP_PEERS_COUNT][2]="&nbsp";
	    $SIP_PEERS_COUNT++;
	    //echo $pieces[0]."\n";
	    }
	}
    if (strpos($line,'Response: Follows') !== false)
	{	$started=true;	}
	}
}

function api_sip_channels()
{
global $fp;
global $API_HOST;
global $API_PORT;
global $API_USER;
global $API_PASS;
global $SIP_PEERS;
global $SIP_PEERS_COUNT;

fputs($fp,"Action: command\r\n");
fputs($fp,"command: sip show channels\r\n");
fputs($fp,"\r\n");

if(!$fp)
    {
    return;
    }

    $started=false;    
    while(!feof($fp))
	{
	
        $line=fgets($fp,4096);
	//echo $line."";
        if (strpos($line,'--END COMMAND--') !== false)
	    {
		$started=false;
		break;
	    }
	else
	{
	if($started)
	    {
	    $ip   = trim(substr($line,0,16));
	    $name = trim(substr($line,17,12));
	    $enc  = trim(substr($line,56));
	    $position=get_position(trim($name));
	    if($position==-1)
		{//hmmmm strange, it is not suppose to happen
		}
		else
		{
		$SIP_PEERS[$position][1]=$ip; //this is the client IP (can be local LAN, before NAT)
		$SIP_PEERS[$position][2]=$enc; //this is the codec
		}
	    //echo $pieces[0]."\n";
	    }
	}
    if (strpos($line,'Response: Follows') !== false)
	{	$started=true;	}
	}
}

function print_sip()
{
global $SIP_PEERS;
global $SIP_PEERS_COUNT;

echo '<table border="1">'."\n";

for($i=1;$i<$SIP_PEERS_COUNT;$i++)
    {
    echo "<tr>\n";
    echo "<td>\n";
    echo $SIP_PEERS[$i][0];
    echo "</td>\n";
    echo "<td>\n";
    echo $SIP_PEERS[$i][1];
    echo "</td>\n";
    echo "<td>\n";
    echo $SIP_PEERS[$i][2];
    echo "</td>\n";
    echo "</tr>\n";
    }

echo "</table>\n";

}

function get_position($text)
{
global $SIP_PEERS;
global $SIP_PEERS_COUNT;

for($i=1;$i<$SIP_PEERS_COUNT;$i++)
    {
    if(strcmp($SIP_PEERS[$i][0],$text)==0)
	{
	return $i;
	}
    }
    return -1;
}


function api_logoff()
{
global $fp;
global $API_HOST;
global $API_PORT;
global $API_USER;
global $API_PASS;
global $PARKEDOK;
global $stp;

if(!$fp)
    {
    return;
    }

fputs($fp,"Action: logoff\r\n");
fputs($fp,"\r\n");
fclose($fp);
}

function show_queue($queue_name)
{
global $fp;
global $API_HOST;
global $API_PORT;
global $API_USER;
global $API_PASS;
global $PARKEDOK;
global $stp;
global $queue_parameters;
global $queue_callers;
global $queue_members;
global $queue_members_count;
global $queue_callers_count;

if(!$fp)
    {
    return;
    }

fputs($fp,"Action: command\r\n");
fputs($fp,"Command: show queue ".$queue_name." \r\n");
fputs($fp,"\r\n");

    $exten='';
    $timeout=0;
    $queue_members_count=0;
    $queue_callers_count=0;
    $members_started=false;
    $callers_started=false;
    $started=false;
    while (!feof($fp)) 
	{
	$line=fgets($fp, 4096);
	//echo $line."<BR>";
	$line=str_replace("\n","",$line);
	$line=str_replace("\r","",$line);
	$line=trim($line);
	if (strpos($line,"--END COMMAND--") !== false)
	    {
	    //the command output has ended
	    $started=false;
	    break;
	    }
	if ($started and $line!="")
	    {
		//echo "".$line."<BR>\n";
		if(strpos($line,", C:") !== false && strpos($line,", A:") !== false && strpos($line,", SL:") !== false)
		    {//this is the line containing queue data
		    $new_line=substr($line,strpos($line,", C:")+4);
		    $queue_parameters[0]=substr($new_line,0,strpos($new_line,","));
		    $new_line=substr($line,strpos($line,", A:")+4);
		    $queue_parameters[1]=substr($new_line,0,strpos($new_line,","));
		    $new_line=substr($line,strpos($line,", SL:")+5);
		    $queue_parameters[2]=substr($new_line,0,strpos($new_line," "));
		    }
		if($members_started)
		    {
		    $queue_members[$queue_members_count][0]=substr($line,0,strpos($line," "));
		    if(strpos($line,"(dynamic)") !== false)
			{
			$queue_members[$queue_members_count][1]=1;
			}
			else
			{
			$queue_members[$queue_members_count][1]=0;
			}		
		    if(strpos($line,"has taken no calls yet") !== false)
			{
			$queue_members[$queue_members_count][2]=0;
			}
			else
			{
			$new_line=substr($line,strpos($line,"has taken ")+10);
			$queue_members[$queue_members_count][2]=substr($new_line,0,strpos($new_line," "));
			}		
		    if(strpos($line,"(last was") !== false)
			{
			$new_line=substr($line,strpos($line,"(last was ")+10);
			$queue_members[$queue_members_count][3]=substr($new_line,0,strpos($new_line," "));
			}		
		else
			{
			$queue_members[$queue_members_count][3]=-1;
			}
		    $queue_members_count++;
		    }
		if($callers_started)
		    {
		    $new_line=trim(substr($line,strpos($line," ")));
		    $queue_callers[$queue_callers_count][0]=trim(substr($new_line,0,strpos($new_line,"(wait: ")));
		    $new_line=trim(substr($new_line,strpos($new_line,"(wait: ")+7));
		    $queue_callers[$queue_callers_count][1]=substr($new_line,0,strpos($new_line,","));
		    $new_line=trim(substr($new_line,strpos($new_line,", prio: ")+8));
		    $queue_callers[$queue_callers_count][2]=substr($new_line,0,strpos($new_line,")"));
		    $queue_callers_count++;
		    }
		if(strpos($line,"No Callers") !== false)
		    {
		    $members_started=false;
		    $callers_started=false;
		    }
		if(strpos($line,"Callers:") !== false)
		    {
		    $members_started=false;
		    $callers_started=true;
		    }
		if(strpos($line,"Members:") !== false)
		    {
		    $members_started=true;
		    $callers_started=false;
		    }
		    
	    }
	if (strpos($line,"Response: Follows") !== false)
	    {
	    //the records/answer follows
	    $started=true;
	    }
	}
}

function display_queue($q)
{
global $queue_parameters;
global $queue_callers;
global $queue_members;
global $queue_members_count;
global $queue_callers_count;
global $TABLE_SIP;

if($_SESSION['admin'])
    echo '<form method="POST" action="./listqueues.php">';

?>

<table border="1" cellspacing="1" style="border-collapse: collapse" bordercolor="#C0C0C0" id="AutoNumber1" width="400">
  <tr>
    <td align="center" bgcolor="#000000"><font color="#FFFFFF"><b>Queue</b></font></td>
    <td align="center" bgcolor="#000000"><font color="#FFFFFF"><b>C</b></font></td>
    <td align="center" bgcolor="#000000"><font color="#FFFFFF"><b>A</b></font></td>
    <td align="center" bgcolor="#000000"><font color="#FFFFFF"><b>SL</b></font></td>
  </tr>
  <tr>
    <td align="right" bgcolor="#F0F0F0"><?=$q?></td>
    <td align="right" bgcolor="#F0F0F0"><?=$queue_parameters[0]?></td>
    <td align="right" bgcolor="#F0F0F0"><?=$queue_parameters[1]?></td>
    <td align="right" bgcolor="#F0F0F0"><?=$queue_parameters[2]?></td>
  </tr>
  <tr>
    <td align="center" bgcolor="#E0E0E0"><b>Members</b></td>
    <td align="center" bgcolor="#E0E0E0"><b>type</b></td>
    <td align="center" bgcolor="#E0E0E0"><b>No. of calls</b></td>
    <td align="center" bgcolor="#E0E0E0"><b>last answered</b></td>
  </tr>
    <?
    for($i=0;$i<$queue_members_count-1;$i++)
    {
    ?>
  <tr>
    <td align="right" bgcolor="#F0F0F0">
    <?
    if($_SESSION['admin'])
        echo '<a href="./removeagent.php?queue='.$q.'&agent='.$queue_members[$i][0].'">';
    
    echo $queue_members[$i][0];
    
    if($_SESSION['admin'])
        echo '</a>';
    ?>
    </td>
    <td align="right" bgcolor="#F0F0F0"><?=(!$queue_members[$i][1]?'static':'dynamic')?></td>
    <td align="right" bgcolor="#F0F0F0"><?=$queue_members[$i][2]?></td>
    <td align="right" bgcolor="#F0F0F0">
					<?if($queue_members[$i][3]==-1)
					    echo "never";
					    else
					    duration($queue_members[$i][3]);?></td>
  </tr>
  <?
    }
    if($_SESSION['admin'])
    {
  ?>
  <tr>
    <td colspan="2" align="center" bgcolor="#E0E0E0"><b>Add agent</b></td>
    <td bgcolor="#E0E0E0" colspan="2" align="left">
    <?
    $query="SELECT username,callerid FROM ".$TABLE_SIP." ";
    $result=mysql_query($query) or die("Cannot run query: ".$query." ==> ".mysql_error());
    ?>
    <select size="1" name="sipmembers" >
    <?while($record=mysql_fetch_array($result,MYSQL_ASSOC)){?>
        <option value="SIP/<?=$record['username']?>">"<?=$record['callerid']?>":SIP/<?=$record['username']?></option>
    <?}?>
    </select>
    <input type="hidden" name="queue" value="<?=$q?>">
    <input type="submit" value="Add" name="Add">
    </td>
  </tr>  
  <?
    }
  ?>
  <tr>
    <td colspan="2" align="center" bgcolor="#E0E0E0"><b>Waiting in Queue</b></td>
    <td align="center" bgcolor="#E0E0E0"><b>for min:sec</b></td>
    <td align="center" bgcolor="#E0E0E0"><b>priority</b></td>
  </tr>
  <?
  if($queue_callers_count==0)
    {
    ?>
  <tr>
    <td colspan="4" align="center" bgcolor="#F0F0F0"><B>---===the queue is empty===---</B></td>
  </tr>
    <?
    }
  for($i=0;$i<$queue_callers_count;$i++)
    {
  ?>
  <tr>
    <td colspan="2" align="right" bgcolor="#F0F0F0"><?=$i+1?>. <?=$queue_callers[$i][0]?></td>
    <td align="right" bgcolor="#F0F0F0"><?=$queue_callers[$i][1]?></td>
    <td align="right" bgcolor="#F0F0F0"><?=$queue_callers[$i][2]?></td>
  </tr>
  <?
    }
  ?>
</table>
<?
if($_SESSION['admin'])
    echo '</form>';
}

function duration($duration) {

   $jours = (($duration/86400));
   $duration = $duration % 86400;
   $heures = (($duration/3600));
   $duration = $duration % 3600;
   $minutes = (($duration/60));
   $duration = $duration % 60;
   
   printf('%dD %02dH %02dM %02dS',$jours,$heures,$minutes,$duration);
} 

function wait_for($string)
{
global $fp;

while(!feof($fp))
    {
    $line=fgets($fp,4096);
    $line=str_replace("\n","",$line);
    $line=str_replace("\r","",$line);
    $line=trim($line);
    if (strpos($line,$string) !== false)
	{
	break;
	}
    }

}

function format_phone($text)
{
$return='0000000000';
for($i=0;$i<strlen($text);$i++)
    {
    if($text{$i}>='0' && $text{$i}<='9')
	{
	$return.=$text{$i};
	}
    }
    return substr($return,-10);
}

?>