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
  $proto["0"]="IP";
  $proto["1"]="ICMP";
  $proto["2"]="IGMP";
  $proto["3"]="GGP";
  $proto["6"]="TCP";
  $proto["12"]="PUP";
  $proto["17"]="UDP";
  $proto["22"]="IDP";
  $proto["47"]="GRE";
  $proto["50"]="ESP";
  $proto["51"]="AH";
  $proto["89"]="OSPF";
  $proto["255"]="RAW";

/*
  oob_time_usec int(10) unsigned default NULL,
  oob_prefix varchar(32) default NULL,
  oob_mark int(10) unsigned default NULL,

  pwsniff_user varchar(30) default NULL,
  pwsniff_pass varchar(30) default NULL,
  ahesp_spi int(10) unsigned default NULL,
*/

$imsg["3-0"]="Network Unreachable";
$imsg["3-1"]="Host Unreachable";
$imsg["3-2"]="Protocol Unreachable";
$imsg["3-3"]="Port Unreachable";
$imsg["3-4"]="Fragmentation Needed";
$imsg["3-5"]="Source routing failed";
$imsg["3-6"]="Destination network unknown";
$imsg["3-7"]="Destination host unknown";
$imsg["3-8"]="Source host isolated";
$imsg["3-9"]="Destination network prohibited";
$imsg["3-10"]="Destination host prohibited";
$imsg["3-11"]="Network unreachable for TOS";
$imsg["3-12"]="Host unreachable for TOS";
$imsg["3-13"]="Prohibited by filtering";
$imsg["3-14"]="Host precedence violation";
$imsg["3-15"]="Precedence cutoff in effect";
$imsg["4-0"]="Source quench";
$imsg["5-0"]="Redirect for network";
$imsg["5-1"]="Redirect for host";
$imsg["5-2"]="Redirect for TOS and network";
$imsg["5-3"]="Redirect for TOS and host";
$imsg["9-0"]="Router advertisement";
$imsg["10-0"]="Route sollicitation";
$imsg["11-0"]="TTL 0 during transit";
$imsg["11-1"]="TTL 0 during reassembly";
$imsg["12-0"]="IP header bad";
$imsg["12-1"]="Required options missing";
$imsg["13-0"]="Timestamp request (obsolete)";
$imsg["14"]="Timestamp reply (obsolete)";
$imsg["15-0"]="Information request (obsolete)";
$imsg["16-0"]="Information reply (obsolete)";
$imsg["17-0"]="Address mask request";
$imsg["18-0"]="Address mask reply";
%>
