<%
/*
#    Copyright (C) 2002  <Gregory Hinton Nietsky>
#    Copyright (C) 2005  <ZA Telecomunications>
#
#    This program is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program; if not, write to the Free Software
#    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

include "apifunc.inc";

$apiinf=apiquery("QueueStatus");

for ($pkt=0;$pkt < count($apiinf);$pkt++) 
{
  	$queuename=$apiinf[$pkt]['Queue'];
 	unset($apiinf[$pkt]['Queue']);
  	if ($apiinf[$pkt]['Event'] == "QueueParams") 
	{
    		unset($apiinf[$pkt]['Event']);
    		$quarr[$queuename]=$apiinf[$pkt];
    		if ((isset($qnme[$queuename])) && ($quarr['All']['Holdtime'] < $quarr[$queuename]['Holdtime'])) 
		{
      			$quarr['All']['Holdtime']=$quarr[$queuename]['Holdtime'];
    		}
		print "Name " . $queuename . "\n";
  	} 
	else if ($apiinf[$pkt]['Event'] == "QueueMember") 
	{
    		unset($apiinf[$pkt]['Event']);
    		$memname=$apiinf[$pkt]['Name'];
		$calltaken=$apiinf[$pkt]['CallsTaken'];
		$lastcall=$apiinf[$pkt]['LastCall'];
		$state=$apiinf[$pkt]['Status'];
    		unset($apiinf[$pkt]['Name']);
		unset($apiinf[$pkt]['CallsTaken']);
		unset($apiinf[$pkt]['LastCall']);
		unset($apiinf[$pkt]['Status']);
    		$quarr[$queuename]['Members'][$memname]=$apiinf[$pkt];
    		if (isset($qnme[$queuename])) 
		{
      			$quarr['All']['Members'][$memname]=$apiinf[$pkt];
    		}
		$localtime=time();
		if ($lastcall != 0)
		{
			$difftime=($localtime-$lastcall);
		}
		else
		{
			$difftime=$lastcall;
		}
		if ($state == '1')
		{
			$status = "Not in Use"; 
		}
		else if ($state == '2')
		{
			$status = "In Use";
		}
		else if ($state == '3')
                {
                        $status = "Busy";
                }
		else if ($state == '4')
                {
                        $status = "";
                }
		else if ($state == '5')
                {
                        $status = "Unavailable";
                }
		else if ($state == '6')
                {
                        $status = "Ringing";
                }

		print "Member " . $memname . " Calls Taken " . $calltaken . " Last call was " . $difftime . " Status = " . $status . "\n";
  	} 
	else if ($apiinf[$pkt]['Event'] == "QueueEntry") 
	{
   		unset($apiinf[$pkt]['Event']);
    		$qpos=$apiinf[$pkt]['Position'];
    		unset($apiinf[$pkt]['Position']);
		$waitsec=$apiinf[$pkt]['Wait'];
		unset($apiinf[$phk]['Wait']);
		$callerid=$apiinf[$pkt]['CallerIDNum'];
		unset($apiinf[$phk]['CallerIDNum']);
    		$quarr[$queuename]['Entrys'][$qpos]=$apiinf[$pkt];
    		if (! is_array($quarr['All']['Entrys'])) 
		{
     			$quarr['All']['Entrys']=array();
    		}
    		if (isset($qnme[$queuename])) 
		{
      			array_push($quarr['All']['Entrys'],$apiinf[$pkt]);
    		} 
		print "Call Entry " . $qpos . " Wait time " . $waitsec . " CallerID " . $callerid . "\n";
  	} 
	else 
	{
//    		print_r($apiinf[$pkt]);
  	}
}
//print_r($apiinf);
%>
