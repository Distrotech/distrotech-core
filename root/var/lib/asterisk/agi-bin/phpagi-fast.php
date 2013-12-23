#!/usr/bin/php -q
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

ob_implicit_flush();
declare(ticks = 1);

$address = '127.0.0.1';
$port = 4573;

function handle_signal($sigio) {
  global $msgsock,$free;
  echo "Got Signal" . $sigio . " Free " . $free . "\n";
  switch($sigio) {
    case SIGTERM: socket_close($sock);
                  exit;
    case SIGHUP: socket_close($msgsock);
                 exit;
    case SIGCLD: $free--;
    default:
  }
}

function handle_connection($sock) {
  global $agi;

  $msgsock = socket_accept($sock);
  $pid=pcntl_fork();
  if ($pid == 0) {
    pcntl_signal(SIGHUP,SIG_IGN);
    pcntl_signal(SIGTERM,SIG_IGN);
//    pcntl_signal(SIGHUP,"handle_signal");
    require_once("/var/lib/asterisk/agi-bin/phpagi/phpfastagi.php");
    $agi=new AGI($msgsock);
    $var=$agi->request['agi_network_script'];
    $agiurl=parse_url($var);
    parse_str($agiurl['query']);
    if (isset($agiurl['path'])) {
	    require_once("/var/lib/asterisk/agi-bin/" . $agiurl['path']);
    }
    socket_close($msgsock);
    exit;
  } else {
    pcntl_signal(SIGCLD,SIG_IGN);
    pcntl_signal(SIGHUP,SIG_IGN);
  }
}

$sock=socket_create(AF_INET,SOCK_STREAM,0);

if ($_SERVER['argv'][1] != "" ) {
  socket_bind($sock,$_SERVER['argv'][1],$port) or die("Bind Failed\n");
} else {
  socket_bind ($sock,$address,$port) or die("Bind Failed\n");
}

socket_listen($sock,128);


if (pcntl_fork() == 0) {
  pcntl_signal(SIGCLD,SIG_IGN);
  while(true) {
    handle_connection($sock);
  }
} else {
  exit;
}
%>
