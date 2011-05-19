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
include "/var/spool/apache/htdocs/reception/auth.inc";
include "/var/spool/apache/htdocs/cdr/apifunc.inc";

function dialchan($channel) {
  global $db;
  $linetype=pg_query($db,"SELECT CASE WHEN (tdm.value > 0) THEN 'Zap/'||tdm.value ELSE
                                   CASE WHEN (iax.value = '1') THEN 'IAX2/'||name ELSE 'SIP/'||name
                                   END
                                 END
                            FROM users LEFT OUTER JOIN astdb AS iax ON (family = name AND key='IAXLine')
                                       LEFT OUTER JOIN astdb AS tdm ON (tdm.family=name and tdm.key='ZAPLine')
                                WHERE name='" . $channel . "'");
  if (pg_num_rows($linetype) > 0) {
    $ltype=pg_fetch_row($linetype,0);
    $channel=$ltype[0];
  } else {
    $channel="Local/" . $channel . "@userout/n";
  }
  return $channel;
}

if (isset($numtocall)) {
  $callidq=pg_query($db,"SELECT title||' '||fname||' '||sname||' <'||number||'>' from contact left outer join lead ON (contact.lead=lead.id) where contact.id=" . $numtocall);
  $callid=pg_fetch_row($callidq,0,PGSQL_NUM);
  $chaninf=agentquery($_SERVER['PHP_AUTH_USER']);

  $socket=apiopen();
  fputs($socket,"Action: Originate\r\n");
  if ((is_array($chaninf)) && ($chaninf['Status'] >= 0)){
    fputs($socket,"Channel: Agent/" . $_SERVER['PHP_AUTH_USER'] . "\r\n");
  } else {
    fputs($socket,"Channel: " . dialchan($_SERVER['PHP_AUTH_USER']) . "\r\n");
  }
  fputs($socket,"CallerID: " . $callid[0] . "\r\n");
  fputs($socket,"Context: ccdial\r\n");
  fputs($socket,"Exten: " . $numtocall . "\r\n");
  fputs($socket,"Priority: 1\r\n");
  fputs($socket,"Timeout: 12000\r\n");
  if (isset($contactnum)) {
    fputs($socket,"Variable: CONTACT=" . $contactnum . "\r\n");
  }
  fputs($socket,"\r\n");
  apiclose($socket);
} else if (isset($_POST['directdial'])) {
  $chaninf=agentquery($_SERVER['PHP_AUTH_USER']);
  $socket=apiopen();
  fputs($socket,"Action: Originate\r\n");
  if ((is_array($chaninf)) && ($chaninf['Status'] >= 0)){
    fputs($socket,"Channel: Agent/" . $_SERVER['PHP_AUTH_USER'] . "\r\n");
  } else {
    fputs($socket,"Channel: " . dialchan($_SERVER['PHP_AUTH_USER']) . "\r\n");
  }
  fputs($socket,"Context: ccddial\r\n");
  fputs($socket,"Exten: " . $_POST['directdial'] . "\r\n");
  fputs($socket,"Priority: 1\r\n");
  fputs($socket,"Timeout: 12000\r\n");
  fputs($socket,"\r\n");
  apiclose($socket);
} else if (isset($_POST['transfer'])) {
  $chaninf=chanstatus($_SERVER['PHP_AUTH_USER']);
  if ((isset($chaninf)) && ($chaninf['Link'] != "")) {
    $socket=apiopen();
    fputs($socket,"Action: Redirect\r\n");
    fputs($socket,"Channel: " . $chaninf['Link'] . "\r\n");
    fputs($socket,"Context: userout\r\n");
    fputs($socket,"Exten: " . $_POST['extento'] . "\r\n");
    fputs($socket,"Priority: 1\r\n");
    fputs($socket,"\r\n");
    apiclose($socket);
  }
}
%>
